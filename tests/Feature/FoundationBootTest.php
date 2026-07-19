<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\IntegrationClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationBootTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke Dosen Farmasi')
            ->assertSee('data-password-toggle', false);
    }

    public function test_dosen_dashboard_loads_empty_database(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)->get(route('dosen.dashboard'))->assertOk()->assertSee('Dashboard Dosen');
    }

    public function test_admin_dashboard_redirects_to_filament_control_room(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '2', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('filament.admin.pages.admin-dashboard'));
    }

    public function test_admin_sees_control_room_link_in_dosen_shell(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '2', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertSee('Ruang Kontrol Admin')
            ->assertSee(route('filament.admin.pages.admin-dashboard'), false);
    }

    public function test_dosen_dashboard_does_not_show_admin_panel_link(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertDontSee('Ruang Kontrol Admin')
            ->assertDontSee(route('filament.admin.pages.admin-dashboard'), false);
    }

    public function test_admin_can_open_filament_control_room(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '2', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('filament.admin.pages.admin-dashboard'))
            ->assertOk()
            ->assertSee('Ruang Kontrol Admin');
    }

    public function test_admin_can_open_integration_clients_panel(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '2', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);

        IntegrationClient::query()->create([
            'name' => 'KP Farmasi',
            'app_code' => 'kp-farmasi',
            'token_hash' => hash('sha256', 'not-a-real-token'),
            'abilities' => ['integration-events:create'],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('filament.admin.resources.integration-clients.index'))
            ->assertOk()
            ->assertSee('Aplikasi Terhubung')
            ->assertSee('kp-farmasi')
            ->assertDontSee('not-a-real-token');
    }

    public function test_dosen_cannot_open_integration_clients_panel(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('filament.admin.resources.integration-clients.index'))
            ->assertForbidden();
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('portfolio_categories', 7);
    }
}
