<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreBridgeAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.core_testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        config(['dosen_farmasi.core.connection' => 'core_testing']);

        Schema::connection('core_testing')->create('users', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('identity_number')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->string('password');
        });

        Schema::connection('core_testing')->create('lecturers', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('lecturer_number')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->string('nip')->nullable();
            $table->string('nidn')->nullable();
            $table->boolean('active')->default(true);
        });

        Schema::connection('core_testing')->create('user_app_accesses', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('app_code');
            $table->string('role_slug')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function test_active_core_lecturer_can_login_without_local_password(): void
    {
        $this->seedCoreUser();

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertRedirect('/dosen/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('app_users', ['core_user_id' => '1', 'role' => 'dosen']);
        $this->assertFalse(Schema::hasColumn('app_users', 'password'));
    }

    public function test_core_user_with_admin_and_dosen_roles_must_choose_role_before_dashboard(): void
    {
        $this->seedCoreUser(role: ['dosen', 'admin']);

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertRedirect(route('role.select'));

        $this->assertAuthenticated();
        $this->get(route('dosen.dashboard'))->assertRedirect(route('role.select'));
        $this->get(route('role.select'))->assertOk()->assertSee('Pilih ruang kerja')->assertSee('Admin')->assertSee('Dosen');

        $this->post(route('role.store'), ['role' => 'admin'])
            ->assertRedirect(route('filament.admin.pages.admin-dashboard'));

        $this->assertDatabaseHas('app_users', ['core_user_id' => '1', 'role' => 'admin']);
    }

    public function test_multi_role_user_can_switch_role_from_dosen_workspace(): void
    {
        $this->seedCoreUser(role: ['dosen', 'admin']);

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertRedirect(route('role.select'));

        $this->post(route('role.store'), ['role' => 'dosen'])
            ->assertRedirect(route('dosen.dashboard'));

        $this->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertSee('Ganti Peran')
            ->assertSee(route('role.select'), false);

        $this->post(route('role.store'), ['role' => 'admin'])
            ->assertRedirect(route('filament.admin.pages.admin-dashboard'));

        $this->assertDatabaseHas('app_users', ['core_user_id' => '1', 'role' => 'admin']);
    }

    public function test_single_role_user_does_not_see_switch_role_action(): void
    {
        $this->seedCoreUser(role: 'dosen');

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertRedirect('/dosen/dashboard');

        $this->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertDontSee('Ganti Peran')
            ->assertDontSee(route('role.select'), false);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->seedCoreUser();

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'wrong'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_inactive_core_user_is_rejected(): void
    {
        $this->seedCoreUser(active: false);

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_user_without_dosen_or_admin_access_is_rejected(): void
    {
        $this->seedCoreUser(role: 'mahasiswa');

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_configured_testing_login_gets_admin_and_dosen_roles_without_core_app_access(): void
    {
        config(['dosen_farmasi.core.testing_all_role_logins' => ['dosen@example.test']]);
        $this->seedCoreUser(role: 'mahasiswa');

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertRedirect(route('role.select'));

        $this->assertAuthenticated();
        $this->get(route('role.select'))->assertOk()->assertSee('Admin')->assertSee('Dosen');
    }

    public function test_configured_testing_login_can_enter_when_core_connection_is_unavailable(): void
    {
        config([
            'dosen_farmasi.core.connection' => 'missing_core_connection',
            'dosen_farmasi.core.testing_all_role_logins' => ['testing.dosen@example.test'],
        ]);

        $this->post('/login', ['login' => 'testing.dosen@example.test', 'password' => 'anything-non-empty'])
            ->assertRedirect(route('role.select'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('app_users', [
            'core_user_id' => 'testing:testing.dosen@example.test',
            'email' => 'testing.dosen@example.test',
            'role' => 'dosen',
        ]);
        $this->get(route('role.select'))->assertOk()->assertSee('Admin')->assertSee('Dosen');
    }

    public function test_core_profile_photo_is_synced_and_rendered(): void
    {
        config(['dosen_farmasi.core.asset_base_url' => 'https://core.example.test']);
        $this->seedCoreUser(photoPath: 'profile-photos/dosen-satu.jpg');

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertRedirect('/dosen/dashboard');

        $photoUrl = 'https://core.example.test/storage/profile-photos/dosen-satu.jpg';
        $this->assertDatabaseHas('app_users', ['core_user_id' => '1', 'photo_url' => $photoUrl]);
        $this->assertDatabaseHas('lecturer_snapshots', ['core_lecturer_id' => '10', 'photo_url' => $photoUrl]);
        $this->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertSee($photoUrl, false)
            ->assertSee('Foto Dosen Satu', false);
    }

    public function test_core_user_photo_takes_precedence_over_lecturer_photo(): void
    {
        config(['dosen_farmasi.core.asset_base_url' => 'https://core.example.test']);
        $this->seedCoreUser(photoPath: 'profile-photos/terbaru.jpg');
        DB::connection('core_testing')->table('lecturers')->where('id', 10)
            ->update(['profile_photo_url' => 'https://core.example.test/storage/profile-photos/lama.jpg']);

        $this->post('/login', ['login' => 'dosen@example.test', 'password' => 'secret'])
            ->assertRedirect('/dosen/dashboard');

        $this->assertDatabaseHas('app_users', [
            'core_user_id' => '1',
            'photo_url' => 'https://core.example.test/storage/profile-photos/terbaru.jpg',
        ]);
    }

    private function seedCoreUser(bool $active = true, string|array $role = 'dosen', ?string $photoPath = null): void
    {
        DB::connection('core_testing')->table('users')->insert([
            'id' => 1,
            'name' => 'Dosen Satu',
            'email' => 'dosen@example.test',
            'username' => 'dosen.satu',
            'identity_number' => 'ID-001',
            'profile_photo_path' => $photoPath,
            'active' => $active,
            'must_change_password' => false,
            'password' => Hash::make('secret'),
        ]);

        DB::connection('core_testing')->table('lecturers')->insert([
            'id' => 10,
            'user_id' => 1,
            'lecturer_number' => 'DSN-001',
            'name' => 'Dosen Satu',
            'email' => 'dosen@example.test',
            'profile_photo_url' => null,
            'nip' => 'NIP001',
            'nidn' => 'NIDN001',
            'active' => true,
        ]);

        foreach ((array) $role as $roleSlug) {
            DB::connection('core_testing')->table('user_app_accesses')->insert([
                'user_id' => 1,
                'app_code' => 'dosen-farmasi',
                'role_slug' => $roleSlug,
                'is_active' => true,
            ]);
        }
    }
}
