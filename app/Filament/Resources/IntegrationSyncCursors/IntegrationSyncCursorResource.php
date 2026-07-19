<?php

namespace App\Filament\Resources\IntegrationSyncCursors;

use App\Filament\Resources\IntegrationSyncCursors\Pages\ListIntegrationSyncCursors;
use App\Models\IntegrationSyncCursor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IntegrationSyncCursorResource extends Resource
{
    protected static ?string $model = IntegrationSyncCursor::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Posisi Sinkronisasi';

    protected static ?string $modelLabel = 'Posisi Sinkronisasi';

    protected static ?string $pluralModelLabel = 'Posisi Sinkronisasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('source_app')->label('Sumber')->badge()->searchable(),
            TextColumn::make('cursor_key')->label('Penanda'),
            TextColumn::make('status')->label('Status')->badge(),
            TextColumn::make('last_source_id')->label('Sumber Terakhir'),
            TextColumn::make('last_updated_at')->label('Terakhir Diperbarui')->dateTime('d M Y H:i'),
            TextColumn::make('last_successful_sync_at')->label('Sukses')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListIntegrationSyncCursors::route('/')];
    }
}
