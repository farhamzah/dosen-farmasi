<?php

namespace App\Filament\Resources\IntegrationClients;

use App\Filament\Resources\IntegrationClients\Pages\ListIntegrationClients;
use App\Models\IntegrationClient;
use App\Services\IntegrationTokenService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IntegrationClientResource extends Resource
{
    protected static ?string $model = IntegrationClient::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $navigationLabel = 'Aplikasi Terhubung';

    protected static string|\UnitEnum|null $navigationGroup = 'Integrasi';

    protected static ?string $modelLabel = 'Aplikasi Terhubung';

    protected static ?string $pluralModelLabel = 'Aplikasi Terhubung';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable()->weight('bold'),
            TextColumn::make('app_code')->label('Kode Aplikasi')->badge(),
            TextColumn::make('is_active')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            TextColumn::make('token_rotated_at')->label('Token Diputar')->dateTime('d M Y H:i')->placeholder('Belum pernah'),
            TextColumn::make('token_revoked_at')->label('Token Dicabut')->dateTime('d M Y H:i')->placeholder('Belum pernah'),
            TextColumn::make('last_used_at')->label('Terakhir Dipakai')->dateTime('d M Y H:i')->placeholder('Belum pernah'),
        ])->actions([
            Action::make('activate')
                ->label('Aktifkan')
                ->visible(fn (IntegrationClient $record): bool => ! $record->is_active)
                ->action(fn (IntegrationClient $record): bool => $record->update(['is_active' => true])),
            ActionGroup::make([
                Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->color('warning')
                    ->visible(fn (IntegrationClient $record): bool => $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (IntegrationClient $record): bool => $record->update(['is_active' => false])),
                Action::make('rotateToken')
                    ->label('Putar Token')
                    ->requiresConfirmation()
                    ->action(function (IntegrationClient $record): void {
                        app(IntegrationTokenService::class)->rotate($record);

                        Notification::make()
                            ->title('Token baru dibuat')
                            ->body('Token plaintext tidak ditampilkan di panel. Gunakan prosedur operasional aman untuk distribusi secret.')
                            ->success()
                            ->send();
                    }),
                Action::make('revokeToken')
                    ->label('Cabut Token')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cabut token aplikasi?')
                    ->modalDescription('Aplikasi ini tidak dapat mengirim event baru sampai token diputar ulang.')
                    ->action(fn (IntegrationClient $record) => app(IntegrationTokenService::class)->revoke($record)),
            ])->label('Aksi lain'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListIntegrationClients::route('/')];
    }
}
