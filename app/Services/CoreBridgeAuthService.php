<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\LecturerSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class CoreBridgeAuthService
{
    private ?string $failureReason = null;

    private array $availableRoles = [];

    public function attempt(string $login, string $password): ?AppUser
    {
        $this->failureReason = null;
        $this->availableRoles = [];
        $coreUser = $this->findCoreUser($login);

        if (! $coreUser) {
            if ($fallbackUser = $this->attemptTestingFallback($login, $password)) {
                return $fallbackUser;
            }

            $this->failureReason = 'Login gagal. Periksa akun, password, dan akses aplikasi.';

            return null;
        }

        if (! (bool) ($coreUser['active'] ?? false)) {
            $this->failureReason = 'Akun Core tidak aktif.';

            return null;
        }

        if ((bool) ($coreUser['must_change_password'] ?? false)) {
            $this->failureReason = 'Password Core wajib diganti dulu melalui Profile Portal Core.';

            return null;
        }

        if (! Hash::check($password, (string) ($coreUser['password'] ?? ''))) {
            $this->failureReason = 'Login gagal. Periksa akun, password, dan akses aplikasi.';

            return null;
        }

        $lecturer = $this->lecturerProfile((string) $coreUser['id']);
        $roles = $this->resolveRoles((string) $coreUser['id'], $lecturer);
        $roles = $this->applyTestingRoleOverride($coreUser, $lecturer, $roles);

        if ($roles === []) {
            $this->failureReason = 'Akun belum memiliki akses dosen-farmasi.';

            return null;
        }

        $this->availableRoles = $roles;
        $role = $this->initialRole((string) $coreUser['id'], $roles);

        return $this->provisionLocalUser($coreUser, $lecturer, $role);
    }

    public function failureReason(): ?string
    {
        return $this->failureReason;
    }

    public function availableRoles(): array
    {
        return $this->availableRoles;
    }

    private function findCoreUser(string $login): ?array
    {
        $login = strtolower(trim($login));

        if ($login === '') {
            return null;
        }

        try {
            $connection = DB::connection($this->coreConnection());
            $query = $connection->table('users')
                ->where(function ($query) use ($connection, $login): void {
                    $query
                        ->whereRaw('LOWER(TRIM(email)) = ?', [$login])
                        ->orWhereRaw('LOWER(TRIM(username)) = ?', [$login])
                        ->orWhereRaw('LOWER(TRIM(identity_number)) = ?', [$login]);

                    if ($this->coreHasTable('lecturers')) {
                        $query->orWhereIn('id', $connection->table('lecturers')
                            ->whereRaw('LOWER(TRIM(lecturer_number)) = ?', [$login])
                            ->orWhereRaw('LOWER(TRIM(nip)) = ?', [$login])
                            ->orWhereRaw('LOWER(TRIM(nidn)) = ?', [$login])
                            ->pluck('user_id'));
                    }
                });

            $user = $query->first();

            return $user ? (array) $user : null;
        } catch (Throwable) {
            $this->failureReason = 'Koneksi Core belum tersedia.';

            return null;
        }
    }

    private function lecturerProfile(string $coreUserId): ?array
    {
        if (! $this->coreHasTable('lecturers')) {
            return null;
        }

        $lecturer = DB::connection($this->coreConnection())->table('lecturers')->where('user_id', $coreUserId)->first();

        return $lecturer ? (array) $lecturer : null;
    }

    private function resolveRoles(string $coreUserId, ?array $lecturer): array
    {
        $roles = [];

        if (in_array($coreUserId, config('dosen_farmasi.core.admin_core_user_ids', []), true)) {
            $roles[] = 'admin';
        }

        $appRoles = $this->appRoles($coreUserId);

        if (collect($appRoles)->contains(fn (string $role): bool => in_array($role, ['admin', 'admin-dosen', 'admin-dosen-farmasi'], true))) {
            $roles[] = 'admin';
        }

        if ($lecturer && collect($appRoles)->contains(fn (string $role): bool => in_array($role, ['dosen', 'lecturer', 'admin-dosen', 'admin-dosen-farmasi'], true))) {
            $roles[] = 'dosen';
        }

        return collect(['dosen', 'admin'])
            ->filter(fn (string $role): bool => in_array($role, $roles, true))
            ->values()
            ->all();
    }

    private function applyTestingRoleOverride(array $coreUser, ?array $lecturer, array $roles): array
    {
        if (app()->environment('production')) {
            return $roles;
        }

        $allowList = config('dosen_farmasi.core.testing_all_role_logins', []);
        if ($allowList === []) {
            return $roles;
        }

        $identifiers = collect([
            $coreUser['email'] ?? null,
            $coreUser['username'] ?? null,
            $coreUser['identity_number'] ?? null,
            $lecturer['lecturer_number'] ?? null,
            $lecturer['nip'] ?? null,
            $lecturer['nidn'] ?? null,
        ])
            ->filter()
            ->map(fn (mixed $value): string => str((string) $value)->lower()->trim()->toString());

        if ($identifiers->intersect($allowList)->isEmpty()) {
            return $roles;
        }

        return collect($roles)
            ->merge($lecturer ? ['dosen', 'admin'] : ['admin'])
            ->unique()
            ->sortBy(fn (string $role): int => $role === 'dosen' ? 0 : 1)
            ->values()
            ->all();
    }

    private function attemptTestingFallback(string $login, string $password): ?AppUser
    {
        if (app()->environment('production') || blank($password) || ! $this->isTestingLoginAllowed($login)) {
            return null;
        }

        $normalizedLogin = str($login)->lower()->trim()->toString();
        $coreUser = [
            'id' => 'testing:'.$normalizedLogin,
            'name' => 'Testing '.$normalizedLogin,
            'email' => $normalizedLogin,
        ];
        $lecturer = [
            'id' => 'testing-lecturer:'.$normalizedLogin,
            'name' => 'Testing '.$normalizedLogin,
            'email' => $normalizedLogin,
            'lecturer_number' => 'TEST-'.$this->stableTestingSuffix($normalizedLogin),
            'active' => true,
        ];

        $this->availableRoles = ['dosen', 'admin'];
        $this->failureReason = null;

        return $this->provisionLocalUser($coreUser, $lecturer, $this->initialRole((string) $coreUser['id'], $this->availableRoles));
    }

    private function isTestingLoginAllowed(string $login): bool
    {
        $allowList = config('dosen_farmasi.core.testing_all_role_logins', []);

        return $allowList !== [] && in_array(str($login)->lower()->trim()->toString(), $allowList, true);
    }

    private function stableTestingSuffix(string $login): string
    {
        return strtoupper(substr(hash('sha1', $login), 0, 8));
    }

    private function initialRole(string $coreUserId, array $roles): string
    {
        $existing = AppUser::query()->where('core_user_id', $coreUserId)->value('role');

        if ($existing && in_array($existing, $roles, true)) {
            return $existing;
        }

        return $roles[0];
    }

    private function appRoles(string $coreUserId): array
    {
        if (! $this->coreHasTable('user_app_accesses')) {
            return [];
        }

        return DB::connection($this->coreConnection())->table('user_app_accesses')
            ->where('user_id', $coreUserId)
            ->where('app_code', config('dosen_farmasi.core.app_code', 'dosen-farmasi'))
            ->where('is_active', true)
            ->whereNotNull('role_slug')
            ->pluck('role_slug')
            ->map(fn ($role): string => str((string) $role)->lower()->trim()->replace('_', '-')->toString())
            ->unique()
            ->values()
            ->all();
    }

    private function provisionLocalUser(array $coreUser, ?array $lecturer, string $role): AppUser
    {
        $snapshot = [
            'core_user_id' => (string) $coreUser['id'],
            'core_lecturer_id' => isset($lecturer['id']) ? (string) $lecturer['id'] : null,
            'name' => (string) ($lecturer['name'] ?? $coreUser['name'] ?? 'Pengguna Core'),
            'email' => (string) ($lecturer['email'] ?? $coreUser['email'] ?? ''),
            'photo_url' => $this->corePhotoUrl($coreUser, $lecturer),
            'lecturer_number' => isset($lecturer['lecturer_number']) ? (string) $lecturer['lecturer_number'] : null,
            'nip' => isset($lecturer['nip']) ? (string) $lecturer['nip'] : null,
            'nidn' => isset($lecturer['nidn']) ? (string) $lecturer['nidn'] : null,
            'role' => $role,
            'is_active' => true,
            'last_login_at' => now(),
            'last_synced_at' => now(),
        ];

        $user = AppUser::query()->updateOrCreate(
            ['core_user_id' => (string) $coreUser['id']],
            $snapshot,
        );

        if ($lecturer) {
            LecturerSnapshot::query()->updateOrCreate(
                ['core_lecturer_id' => (string) $lecturer['id']],
                collect($snapshot)->except(['role', 'last_login_at'])->merge([
                    'sister_id_sdm' => null,
                    'study_program_id' => isset($lecturer['study_program_id']) ? (string) $lecturer['study_program_id'] : null,
                    'is_active' => (bool) ($lecturer['active'] ?? true),
                    'synced_at' => now(),
                ])->all(),
            );
        }

        return $user;
    }

    private function corePhotoUrl(array $coreUser, ?array $lecturer): ?string
    {
        $candidateKeys = [
            'profile_photo_url',
            'photo_url',
            'avatar_url',
            'picture',
            'profile_photo_path',
            'photo_path',
            'avatar_path',
            'foto',
            'photo',
            'avatar',
        ];

        foreach ([$coreUser, $lecturer ?? []] as $source) {
            foreach ($candidateKeys as $key) {
                $url = $this->normalizeCorePhotoUrl($source[$key] ?? null);

                if ($url) {
                    return $url;
                }
            }
        }

        return null;
    }

    private function normalizeCorePhotoUrl(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str($value)->startsWith(['http://', 'https://'])) {
            return $value;
        }

        if (str($value)->startsWith(['javascript:', 'data:'])) {
            return null;
        }

        $baseUrl = trim((string) config('dosen_farmasi.core.asset_base_url', ''), '/');

        if ($baseUrl === '') {
            return null;
        }

        if (! str($baseUrl)->startsWith(['http://', 'https://'])) {
            return null;
        }

        $path = ltrim($value, '/');

        if (! str($path)->startsWith(['storage/', 'uploads/', 'images/'])) {
            $path = 'storage/'.$path;
        }

        return $baseUrl.'/'.$path;
    }

    private function coreHasTable(string $table): bool
    {
        try {
            return DB::connection($this->coreConnection())->getSchemaBuilder()->hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function coreConnection(): string
    {
        return (string) config('dosen_farmasi.core.connection', 'core_mysql');
    }
}
