<?php

namespace App\Filament\Resources\LecturerEmployments;

use App\Filament\Resources\LecturerEmployments\Pages\ListLecturerEmployments;
use App\Models\LecturerEmployment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerEmploymentResource extends Resource
{
    protected static ?string $model = LecturerEmployment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Riwayat Pekerjaan';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    protected static ?string $modelLabel = 'Riwayat Pekerjaan';

    protected static ?string $pluralModelLabel = 'Riwayat Pekerjaan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
            TextColumn::make('institution_name')->label('Institusi')->searchable()->limit(42),
            TextColumn::make('position_name')->label('Posisi')->limit(36),
            TextColumn::make('employment_type')->label('Ikatan')->badge(),
            TextColumn::make('start_date')->label('Mulai')->date('d M Y'),
            TextColumn::make('end_date')->label('Selesai')->date('d M Y'),
            TextColumn::make('verification_status')->label('Status')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerEmployments::route('/')];
    }
}
