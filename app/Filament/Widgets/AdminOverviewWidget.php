<?php

namespace App\Filament\Widgets;

use App\Models\AppUser;
use App\Models\Document;
use App\Models\IntegrationEvent;
use App\Models\PortfolioActivity;
use App\Models\PortfolioIssueReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Ringkasan Operasional';

    protected ?string $description = 'Validasi portofolio, integrasi, dokumen, dan issue report aktif.';

    protected function getStats(): array
    {
        return [
            Stat::make('Dosen lokal', AppUser::query()->whereNotNull('core_lecturer_id')->count())
                ->description('Akun dosen tersinkron')
                ->color('primary'),
            Stat::make('Aktivitas', PortfolioActivity::query()->count())
                ->description('Total portofolio tercatat')
                ->color('success'),
            Stat::make('Menunggu verifikasi', PortfolioActivity::query()->where('verification_status', 'SUBMITTED')->count())
                ->description('Perlu keputusan admin')
                ->color('warning'),
            Stat::make('Perlu revisi', PortfolioActivity::query()->where('verification_status', 'REVISION_REQUIRED')->count())
                ->description('Dikembalikan ke dosen')
                ->color('warning'),
            Stat::make('Issue report terbuka', PortfolioIssueReport::query()->whereIn('status', ['OPEN', 'IN_REVIEW'])->count())
                ->description('Masih aktif')
                ->color('danger'),
            Stat::make('System verified', PortfolioActivity::query()->where('verification_status', 'SYSTEM_VERIFIED')->count())
                ->description('Terverifikasi otomatis')
                ->color('success'),
            Stat::make('Dokumen', Document::query()->count())
                ->description('Lampiran dan bukti')
                ->color('primary'),
            Stat::make('Event gagal', IntegrationEvent::query()->where('status', 'FAILED')->count())
                ->description('Butuh tindak lanjut integrasi')
                ->color('danger'),
        ];
    }
}
