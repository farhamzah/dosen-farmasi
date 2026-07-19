<?php

namespace App\Filament\Resources\LecturerStructuralPositions;

use App\Filament\Resources\LecturerStructuralPositions\Pages\ListLecturerStructuralPositions;
use App\Models\LecturerStructuralPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerStructuralPositionResource extends Resource
{
    protected static ?string $model = LecturerStructuralPosition::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Jabatan Struktural';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    protected static ?string $modelLabel = 'Jabatan Struktural';

    protected static ?string $pluralModelLabel = 'Jabatan Struktural';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
            TextColumn::make('position_name')->label('Jabatan')->searchable()->limit(40),
            TextColumn::make('unit')->label('Unit')->limit(32),
            TextColumn::make('start_date')->label('Mulai')->date('d M Y'),
            TextColumn::make('end_date')->label('Selesai')->date('d M Y'),
            TextColumn::make('is_active')->label('Aktif')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Riwayat'),
            TextColumn::make('verification_status')->label('Status')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerStructuralPositions::route('/')];
    }
}
