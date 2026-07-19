<?php

namespace App\Services;

use App\Data\LecturerResolutionResult;
use App\Models\AppUser;
use App\Models\LecturerSnapshot;

class LecturerIdentityResolver
{
    public function resolve(array $payload): LecturerResolutionResult
    {
        $direct = $payload['core_dosen_id'] ?? $payload['lecturer_core_id'] ?? $payload['core_lecturer_id'] ?? null;

        if (filled($direct)) {
            $id = (string) $direct;
            $known = AppUser::query()->where('core_lecturer_id', $id)->where('is_active', true)->exists()
                || LecturerSnapshot::query()->where('core_lecturer_id', $id)->where('is_active', true)->exists();

            return $known
                ? new LecturerResolutionResult('resolved', $id)
                : new LecturerResolutionResult('not_found', null, 'Core lecturer ID tidak ditemukan atau tidak aktif.');
        }

        foreach (['nip', 'nidn', 'email', 'lecturer_number'] as $field) {
            if (blank($payload[$field] ?? null)) {
                continue;
            }

            $result = $this->resolveByField($field, (string) $payload[$field]);
            if ($result->status !== 'not_found') {
                return $result;
            }
        }

        return new LecturerResolutionResult('not_found', null, 'Identitas dosen tidak ditemukan.');
    }

    private function resolveByField(string $field, string $value): LecturerResolutionResult
    {
        $normalized = str($value)->lower()->trim()->toString();
        $users = AppUser::query()
            ->where('role', 'dosen')
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM('.$field.')) = ?', [$normalized])
            ->pluck('core_lecturer_id')
            ->filter()
            ->values();

        $snapshots = LecturerSnapshot::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM('.$field.')) = ?', [$normalized])
            ->pluck('core_lecturer_id')
            ->filter()
            ->values();

        $ids = $users->merge($snapshots)->unique()->values();

        return match ($ids->count()) {
            0 => new LecturerResolutionResult('not_found', null, "Dosen dengan {$field} tersebut tidak ditemukan."),
            1 => new LecturerResolutionResult('resolved', (string) $ids->first()),
            default => new LecturerResolutionResult('ambiguous', null, "Dosen dengan {$field} tersebut ambigu.", $ids->all()),
        };
    }
}
