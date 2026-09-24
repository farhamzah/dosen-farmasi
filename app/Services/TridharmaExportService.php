<?php

namespace App\Services;

use App\Support\IndonesianDateFormatter;
use App\Support\PortfolioUi;
use Illuminate\Support\Collection;

class TridharmaExportService
{
    public function stream(Collection $activities, string $tableKind, string $format = 'csv'): void
    {
        $handle = fopen('php://output', 'w');
        $separator = $format === 'xls' ? "\t" : ',';

        foreach ($this->rows($activities, $tableKind) as $row) {
            fputcsv($handle, $row, $separator);
        }

        fclose($handle);
    }

    private function rows(Collection $activities, string $tableKind): array
    {
        $headers = $this->headers($tableKind);

        return [
            $headers,
            ...$activities->map(fn ($activity): array => $this->row($activity, $tableKind))->all(),
        ];
    }

    private function headers(string $tableKind): array
    {
        return match ($tableKind) {
            'pendidikan' => ['Periode', 'Kegiatan', 'Jenis', 'Kelas/Mahasiswa', 'Peran', 'SKS/Beban', 'Status', 'Sumber'],
            'penelitian' => ['Tanggal', 'Judul', 'Skema', 'Peran', 'Sumber Dana', 'Luaran', 'Status'],
            'publikasi' => ['Tahun', 'Judul', 'Jenis', 'Jurnal/Prosiding', 'Penulis', 'DOI/URL', 'Status', 'Indeksasi'],
            'pengabdian' => ['Tanggal', 'Judul', 'Jenis', 'Mitra/Sasaran', 'Lokasi', 'Peran', 'Luaran', 'Status'],
            'hki' => ['Tahun', 'Judul', 'Jenis', 'Nomor Permohonan', 'Nomor Pencatatan', 'Pemegang Hak', 'Status', 'Dokumen'],
            'buku' => ['Tahun', 'Judul', 'Peran', 'Penerbit', 'ISBN', 'Jenis', 'Status'],
            default => ['Tanggal', 'Judul', 'Kategori', 'Jenis', 'Peran', 'Status', 'Sumber'],
        };
    }

    private function row($activity, string $tableKind): array
    {
        $date = PortfolioUi::activityDate($activity);
        $year = $activity->start_date?->format('Y') ?: ($activity->academic_year ?: '-');
        $status = PortfolioUi::statusLabel($activity->verification_status);
        $domain = match ($tableKind) {
            'pendidikan' => 'pendidikan',
            'pengabdian' => 'pengabdian',
            'penelitian', 'publikasi', 'hki', 'buku' => 'penelitian',
            default => null,
        };
        $type = PortfolioUi::typeLabel($activity->activity_type, $domain);

        return match ($tableKind) {
            'pendidikan' => [
                PortfolioUi::academicYearLabel($activity),
                $activity->title,
                $type,
                $activity->institution_name ?: '-',
                $activity->lecturer_role ?: '-',
                '-',
                $status,
                PortfolioUi::sourceLabel($activity),
            ],
            'penelitian' => [
                IndonesianDateFormatter::date($date),
                $activity->title,
                $type,
                $activity->lecturer_role ?: '-',
                $activity->institution_name ?: '-',
                PortfolioUi::documentLabel($activity),
                $status,
            ],
            'publikasi' => [
                $year,
                $activity->title,
                $type,
                $activity->institution_name ?: '-',
                $activity->lecturer_role ?: '-',
                PortfolioUi::externalLinkLabel($activity),
                $status,
                '-',
            ],
            'pengabdian' => [
                IndonesianDateFormatter::date($date),
                $activity->title,
                $type,
                $activity->institution_name ?: '-',
                $activity->location ?: '-',
                $activity->lecturer_role ?: '-',
                PortfolioUi::documentLabel($activity),
                $status,
            ],
            'hki' => [
                $year,
                $activity->title,
                $type,
                '-',
                '-',
                $activity->lecturer_role ?: '-',
                $status,
                PortfolioUi::documentLabel($activity),
            ],
            'buku' => [
                $year,
                $activity->title,
                $activity->lecturer_role ?: '-',
                $activity->institution_name ?: '-',
                '-',
                $type,
                $status,
            ],
            default => [
                IndonesianDateFormatter::date($date),
                $activity->title,
                $activity->category?->name ?: '-',
                $type,
                $activity->lecturer_role ?: '-',
                $status,
                PortfolioUi::sourceLabel($activity),
            ],
        };
    }
}
