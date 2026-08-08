<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminOverviewWidget;
use Filament\Actions\Action;
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

    protected function getHeaderActions(): array
    {
        $roles = (array) session('dosen_farmasi.available_roles', []);

        return [
            Action::make('switchRole')
                ->label('Ganti peran')
                ->icon('heroicon-o-arrows-right-left')
                ->url(route('role.select'))
                ->visible(count($roles) > 1),
            Action::make('lecturerWorkspace')
                ->label('Ruang dosen')
                ->icon('heroicon-o-academic-cap')
                ->url(route('dosen.dashboard'))
                ->color('gray')
                ->visible(in_array('dosen', $roles, true)),
        ];
    }
}
