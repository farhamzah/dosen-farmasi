<?php

namespace App\Support;

use App\Models\PortfolioActivity;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class PortfolioUi
{
    public static function filterLabel(string $key): string
    {
        return match ($key) {
            'q' => 'Pencarian', 'category_id' => 'Kategori', 'status' => 'Status',
            'year', 'academic_year' => 'Tahun akademik', 'semester' => 'Semester',
            'source', 'source_type' => 'Sumber', 'visibility' => 'Visibilitas',
            'date_from' => 'Dari tanggal', 'date_to' => 'Sampai tanggal',
            'role' => 'Peran', 'subcategory' => 'Subkategori',
            default => 'Filter',
        };
    }

    public static function filterValue(string $key, string $value): string
    {
        if (in_array($key, ['q', 'date_from', 'date_to', 'academic_year', 'year'], true)) {
            return $value;
        }

        if ($key === 'status') {
            return self::statusLabel($value);
        }

        return match ($value) {
            'PRIVATE' => 'Pribadi', 'INTERNAL' => 'Internal', 'PUBLIC' => 'Publik',
            'MANUAL' => 'Input Mandiri', 'SYSTEM' => 'Sistem Terhubung',
            default => Str::of($value)->replace('-', ' ')->toString(),
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'DRAFT' => 'Draft',
            'SUBMITTED', 'PENDING' => 'Menunggu Verifikasi',
            'REVISION_REQUIRED' => 'Perlu Revisi',
            'ADMIN_VERIFIED', 'VERIFIED' => 'Terverifikasi',
            'SYSTEM_VERIFIED' => 'Terverifikasi Sistem',
            'REJECTED' => 'Ditolak',
            'CANCELLED' => 'Dibatalkan',
            'ARCHIVED' => 'Diarsipkan',
            default => Str::of((string) $status)->replace('_', ' ')->title()->toString(),
        };
    }

    public static function statusTone(?string $status): string
    {
        return match ($status) {
            'ADMIN_VERIFIED', 'SYSTEM_VERIFIED' => 'success',
            'REVISION_REQUIRED' => 'warning',
            'REJECTED', 'CANCELLED' => 'danger',
            default => 'info',
        };
    }

    public static function sourceLabel(PortfolioActivity $activity): string
    {
        if ($activity->source_app === 'm8-ui-demo') {
            return 'Data Demo';
        }

        if ($activity->source_type === 'MANUAL') {
            return 'Input Mandiri';
        }

        return match ($activity->source_app) {
            'kp-farmasi' => 'KP Farmasi',
            'ta-farmasi' => 'TA Farmasi',
            'tu-farmasi' => 'TU Farmasi',
            'kp-pspa' => 'KP PSPA',
            'lab-farmasi' => 'Lab Farmasi',
            default => filled($activity->source_app) ? 'Sistem Terhubung' : 'Sistem',
        };
    }

    public static function typeLabel(?string $type, ?string $domain = null): string
    {
        $slug = Str::slug((string) $type);

        $labels = [
            'pendidikan' => [
                'perkuliahan' => 'Pengajaran',
                'koordinator-mata-kuliah' => 'Koordinator Mata Kuliah',
                'pembimbing-akademik' => 'Pembimbingan',
                'pembimbing-tugas-akhir' => 'Pembimbingan',
                'pembimbing-kp' => 'Pembimbingan',
                'pembimbing-kp-pspa' => 'Pembimbingan',
                'penguji-tugas-akhir' => 'Pengujian',
                'penguji-kp' => 'Pengujian',
                'penguji-kp-pspa' => 'Pengujian',
                'praktikum' => 'Praktikum',
                'bahan-ajar' => 'Bahan Ajar',
                'pembinaan-mahasiswa' => 'Pembinaan Mahasiswa',
            ],
            'penelitian' => [
                'penelitian' => 'Penelitian',
                'publikasi-jurnal' => 'Publikasi Jurnal',
                'prosiding' => 'Prosiding',
                'buku' => 'Buku',
                'bab-buku' => 'Bab Buku',
                'hki-paten' => 'HKI/Paten',
                'produk-prototipe' => 'Produk/Prototipe',
                'dataset' => 'Dataset',
                'seminar-ilmiah' => 'Seminar Ilmiah',
                'reviewer-editor' => 'Reviewer/Editor',
                'hibah' => 'Hibah',
                'kolaborasi' => 'Kolaborasi',
            ],
            'pengabdian' => [
                'kegiatan-pengabdian' => 'Kegiatan Pengabdian',
                'pemberdayaan' => 'Pemberdayaan',
                'pelatihan-masyarakat' => 'Pelatihan Masyarakat',
                'konsultasi' => 'Konsultasi',
                'hibah' => 'Hibah',
                'mitra' => 'Mitra',
                'luaran' => 'Luaran',
                'publikasi' => 'Publikasi',
                'dokumentasi-laporan' => 'Dokumentasi/Laporan',
            ],
        ];

        return $labels[$domain][$slug] ?? Str::of((string) $type)->replace('-', ' ')->title()->toString();
    }

    public static function activityDate(PortfolioActivity $activity): ?CarbonInterface
    {
        return $activity->start_date ?: $activity->end_date ?: $activity->verified_at ?: $activity->updated_at;
    }

    public static function tableKind(?string $domain = null, ?string $subcategory = null): string
    {
        if ($domain === 'pendidikan') {
            return 'pendidikan';
        }

        if ($domain === 'pengabdian') {
            return 'pengabdian';
        }

        $slug = Str::slug((string) $subcategory);

        if (in_array($slug, ['publikasi-jurnal', 'prosiding'], true)) {
            return 'publikasi';
        }

        if ($slug === 'hki-paten') {
            return 'hki';
        }

        if (in_array($slug, ['buku', 'bab-buku'], true)) {
            return 'buku';
        }

        return $domain === 'penelitian' ? 'penelitian' : 'portfolio';
    }

    public static function academicYearLabel(PortfolioActivity $activity): string
    {
        return trim(($activity->academic_year ?: '').' '.($activity->semester ?: '')) ?: '-';
    }

    public static function documentLabel(PortfolioActivity $activity): string
    {
        return $activity->documents_count === 0 ? '-' : $activity->documents_count.' dokumen';
    }

    public static function externalLinkLabel(PortfolioActivity $activity): string
    {
        if (filled($activity->source_url)) {
            return 'Tautan';
        }

        return '-';
    }
}
