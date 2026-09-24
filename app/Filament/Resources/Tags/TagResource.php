<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Portofolio';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable(),
            TextColumn::make('normalized_name')->label('Normalisasi')->searchable(),
            TextColumn::make('activities_count')->counts('activities')->label('Dipakai'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListTags::route('/')];
    }
}
