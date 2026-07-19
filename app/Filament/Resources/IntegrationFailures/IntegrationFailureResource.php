<?php

namespace App\Filament\Resources\IntegrationFailures;

use App\Filament\Resources\IntegrationFailures\Pages\ListIntegrationFailures;
use App\Models\IntegrationFailure;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IntegrationFailureResource extends Resource
{
    protected static ?string $model = IntegrationFailure::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Kegagalan Sinkronisasi';

    protected static ?string $modelLabel = 'Kegagalan Sinkronisasi';

    protected static ?string $pluralModelLabel = 'Kegagalan Sinkronisasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('event.source_app')->label('Sumber')->badge()->searchable(),
            TextColumn::make('event.event_type')->label('Event')->searchable(),
            TextColumn::make('failure_category')->label('Kategori')->badge(),
            IconColumn::make('retryable')->label('Dapat Diulang')->boolean(),
            TextColumn::make('attempt_count')->label('Percobaan'),
            TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListIntegrationFailures::route('/')];
    }
}
