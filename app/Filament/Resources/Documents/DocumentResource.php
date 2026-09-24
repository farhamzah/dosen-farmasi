<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Models\Document;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Dokumen';

    protected static string|\UnitEnum|null $navigationGroup = 'Dokumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Judul')->searchable()->limit(50),
            TextColumn::make('document_type')->label('Tipe')->badge(),
            TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
            TextColumn::make('visibility')->label('Visibility')->badge(),
            TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListDocuments::route('/')];
    }
}
