<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\LecturerCertification;
use App\Models\LecturerEducation;
use App\Models\LecturerExpertiseArea;
use App\Models\LecturerExternalIdentifier;
use App\Models\LecturerFunctionalPosition;
use App\Models\ProfileVisibilitySetting;

class ProfileCompletenessService
{
    public function build(AppUser $user): array
    {
        $lecturerId = (string) $user->core_lecturer_id;
        $checks = [
            'Identitas dasar' => filled($user->name) && filled($user->email),
            'Riwayat pendidikan' => LecturerEducation::query()->where('lecturer_core_id', $lecturerId)->exists(),
            'Jabatan aktif' => LecturerFunctionalPosition::query()->where('lecturer_core_id', $lecturerId)->where('is_active', true)->exists(),
            'Bidang ilmu' => LecturerExpertiseArea::query()->where('lecturer_core_id', $lecturerId)->exists(),
            'Sertifikasi' => LecturerCertification::query()->where('lecturer_core_id', $lecturerId)->exists(),
            'Identitas ilmiah' => LecturerExternalIdentifier::query()->where('lecturer_core_id', $lecturerId)->exists(),
            'Profil publik' => ProfileVisibilitySetting::query()->where('lecturer_core_id', $lecturerId)->where('public_profile_enabled', true)->exists(),
        ];

        $completed = collect($checks)->filter()->count();
        $total = count($checks);

        return [
            'completed' => $completed,
            'total' => $total,
            'percent' => $total === 0 ? 0 : (int) round(($completed / $total) * 100),
            'checks' => $checks,
            'actions' => $this->actions($checks),
        ];
    }

    private function actions(array $checks): array
    {
        $actions = [];

        if (! $checks['Riwayat pendidikan']) {
            $actions[] = 'Tambahkan riwayat S2 atau pendidikan terakhir Anda.';
        }

        if (! $checks['Jabatan aktif']) {
            $actions[] = 'Unggah atau catat SK jabatan fungsional terbaru.';
        }

        if (! $checks['Bidang ilmu']) {
            $actions[] = 'Lengkapi bidang keahlian utama dan topik penelitian.';
        }

        if (! $checks['Identitas ilmiah']) {
            $actions[] = 'Lengkapi ORCID, SINTA, atau Google Scholar.';
        }

        if (! $checks['Profil publik']) {
            $actions[] = 'Aktifkan profil publik setelah memilih bagian yang aman ditampilkan.';
        }

        return $actions;
    }
}
