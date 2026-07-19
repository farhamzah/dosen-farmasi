<?php

namespace App\Filament\Resources\LecturerExpertiseAreas;

use App\Filament\Resources\LecturerExpertiseAreas\Pages\ListLecturerExpertiseAreas;
use App\Models\LecturerExpertiseArea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerExpertiseAreaResource extends Resource
{
    protected static ?string $model = LecturerExpertiseArea::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Bidang Keilmuan';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    protected static ?string $modelLabel = 'Bidang Keilmuan';

    protected static ?string $pluralModelLabel = 'Bidang Keilmuan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
            TextColumn::make('primary_expertise')->label('Keahlian Utama')->searchable()->limit(42),
            TextColumn::make('knowledge_group')->label('Kelompok Ilmu')->limit(36),
            TextColumn::make('is_primary')->label('Utama')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Utama' : 'Pendukung'),
            TextColumn::make('verification_status')->label('Status')->badge(),
            TextColumn::make('visibility')->label('Visibilitas')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerExpertiseAreas::route('/')];
    }
}
