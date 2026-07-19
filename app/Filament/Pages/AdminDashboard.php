<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminOverviewWidget;
use Filament\Pages\Dashboard;
use Illuminate\Contracts\Support\Htmlable;

class AdminDashboard extends Dashboard
{
    protected static ?string $navigationLabel = 'Ruang Kontrol';

    protected static ?string $title = 'Ruang Kontrol Admin';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Dashboard dan panel administrasi Dosen Farmasi berada di satu tempat.';
    }

    public function getWidgets(): array
    {
        return [
            AdminOverviewWidget::class,
        ];
    }
}
