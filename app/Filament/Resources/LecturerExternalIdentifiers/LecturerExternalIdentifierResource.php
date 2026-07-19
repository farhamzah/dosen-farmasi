<?php

namespace App\Filament\Resources\LecturerExternalIdentifiers;

use App\Filament\Resources\LecturerExternalIdentifiers\Pages\ListLecturerExternalIdentifiers;
use App\Models\LecturerExternalIdentifier;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerExternalIdentifierResource extends Resource
{
    protected static ?string $model = LecturerExternalIdentifier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Identitas Ilmiah';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    protected static ?string $modelLabel = 'Identitas Ilmiah';

    protected static ?string $pluralModelLabel = 'Identitas Ilmiah';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
            TextColumn::make('identifier_type')->label('Jenis')->badge(),
            TextColumn::make('identifier_value')->label('Identifier')->searchable()->limit(44),
            TextColumn::make('profile_url')->label('URL')->limit(48),
            TextColumn::make('verification_status')->label('Status')->badge(),
            TextColumn::make('visibility')->label('Visibilitas')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerExternalIdentifiers::route('/')];
    }
}
