<?php

namespace App\Filament\Resources\LecturerFunctionalPositions;

use App\Filament\Resources\LecturerFunctionalPositions\Pages\ListLecturerFunctionalPositions;
use App\Models\LecturerFunctionalPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerFunctionalPositionResource extends Resource
{
    protected static ?string $model = LecturerFunctionalPosition::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Jabatan Fungsional';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    protected static ?string $modelLabel = 'Jabatan Fungsional';

    protected static ?string $pluralModelLabel = 'Jabatan Fungsional';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
            TextColumn::make('position_name')->label('Jabatan')->searchable()->limit(40),
            TextColumn::make('effective_date')->label('TMT')->date('d M Y'),
            TextColumn::make('credit_score')->label('KUM'),
            TextColumn::make('is_active')->label('Aktif')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Riwayat'),
            TextColumn::make('verification_status')->label('Status')->badge(),
            TextColumn::make('source_type')->label('Sumber')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerFunctionalPositions::route('/')];
    }
}
