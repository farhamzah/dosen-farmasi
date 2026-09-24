<?php

namespace App\Filament\Resources\DocumentVersions;

use App\Filament\Resources\DocumentVersions\Pages\ListDocumentVersions;
use App\Models\DocumentVersion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentVersionResource extends Resource
{
    protected static ?string $model = DocumentVersion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-numbered-list';

    protected static ?string $navigationLabel = 'Versi Dokumen';

    protected static string|\UnitEnum|null $navigationGroup = 'Dokumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('document.title')->label('Dokumen')->searchable()->limit(50),
            TextColumn::make('version_number')->label('Versi'),
            TextColumn::make('original_filename')->label('File')->limit(40),
            TextColumn::make('source_app')->label('Sumber')->badge(),
            TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListDocumentVersions::route('/')];
    }
}
