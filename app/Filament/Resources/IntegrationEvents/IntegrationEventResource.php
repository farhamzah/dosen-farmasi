<?php

namespace App\Filament\Resources\IntegrationEvents;

use App\Filament\Resources\IntegrationEvents\Pages\ListIntegrationEvents;
use App\Models\IntegrationEvent;
use App\Services\IntegrationEventProcessor;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IntegrationEventResource extends Resource
{
    protected static ?string $model = IntegrationEvent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Event Integrasi';

    protected static string|\UnitEnum|null $navigationGroup = 'Integrasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('event_id')->label('Kode Event')->copyable()->searchable()->limit(18),
            TextColumn::make('event_type')->label('Tipe')->searchable(),
            TextColumn::make('source_app')->label('Sumber')->badge(),
            TextColumn::make('status')->label('Status')->badge(),
            TextColumn::make('attempt_count')->label('Percobaan'),
            TextColumn::make('last_error_code')->label('Error')->badge(),
            TextColumn::make('processed_at')->label('Diproses')->dateTime('d M Y H:i'),
        ])->actions([
            Action::make('retry')
                ->label('Ulangi')
                ->visible(fn (IntegrationEvent $record): bool => $record->status === 'FAILED')
                ->requiresConfirmation()
                ->action(fn (IntegrationEvent $record): IntegrationEvent => app(IntegrationEventProcessor::class)->process($record)),
            Action::make('ignore')
                ->label('Abaikan')
                ->color('gray')
                ->visible(fn (IntegrationEvent $record): bool => in_array($record->status, ['FAILED', 'QUEUED'], true))
                ->schema([
                    Textarea::make('reason')->label('Alasan')->maxLength(1000),
                ])
                ->action(function (array $data, IntegrationEvent $record): void {
                    $record->update([
                        'status' => 'IGNORED',
                        'processed_at' => now(),
                        'result_summary' => $data['reason'] ?? 'Diabaikan oleh admin.',
                    ]);
                }),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListIntegrationEvents::route('/')];
    }
}
