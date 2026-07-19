<?php

namespace App\Filament\Resources\PortfolioActivities;

use App\Filament\Resources\PortfolioActivities\Pages\ListPortfolioActivities;
use App\Models\PortfolioActivity;
use App\Services\PortfolioStatusTransitionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortfolioActivityResource extends Resource
{
    protected static ?string $model = PortfolioActivity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Kegiatan Portofolio';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Judul')->searchable()->limit(60),
            TextColumn::make('category.name')->label('Kategori'),
            TextColumn::make('verification_status')->label('Status')->badge(),
            TextColumn::make('source_type')->label('Sumber')->badge(),
            TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
        ])->actions([
            Action::make('viewPortal')
                ->label('Lihat')
                ->url(fn (PortfolioActivity $record): string => route('dosen.portfolio.show', $record))
                ->openUrlInNewTab(),
            Action::make('verify')
                ->label('Verifikasi')
                ->color('success')
                ->visible(fn (PortfolioActivity $record): bool => $record->verification_status === 'SUBMITTED')
                ->requiresConfirmation()
                ->action(fn (PortfolioActivity $record): PortfolioActivity => app(PortfolioStatusTransitionService::class)->transition($record, 'ADMIN_VERIFIED', auth()->user())),
            Action::make('revision')
                ->label('Minta Revisi')
                ->color('warning')
                ->visible(fn (PortfolioActivity $record): bool => $record->verification_status === 'SUBMITTED')
                ->schema([
                    Textarea::make('reason')->label('Alasan')->required(),
                ])
                ->action(fn (array $data, PortfolioActivity $record): PortfolioActivity => app(PortfolioStatusTransitionService::class)->transition($record, 'REVISION_REQUIRED', auth()->user(), $data['reason'])),
            Action::make('reject')
                ->label('Tolak')
                ->color('danger')
                ->visible(fn (PortfolioActivity $record): bool => $record->verification_status === 'SUBMITTED')
                ->schema([
                    Textarea::make('reason')->label('Alasan')->required(),
                ])
                ->action(fn (array $data, PortfolioActivity $record): PortfolioActivity => app(PortfolioStatusTransitionService::class)->transition($record, 'REJECTED', auth()->user(), $data['reason'])),
            Action::make('archive')
                ->label('Arsipkan')
                ->color('gray')
                ->visible(fn (PortfolioActivity $record): bool => $record->verification_status === 'ADMIN_VERIFIED')
                ->requiresConfirmation()
                ->action(fn (PortfolioActivity $record): PortfolioActivity => app(PortfolioStatusTransitionService::class)->transition($record, 'ARCHIVED', auth()->user())),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPortfolioActivities::route('/')];
    }
}
