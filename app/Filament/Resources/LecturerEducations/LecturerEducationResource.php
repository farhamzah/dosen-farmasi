<?php

namespace App\Filament\Resources\LecturerEducations;

use App\Filament\Resources\LecturerEducations\Pages\ListLecturerEducations;
use App\Models\LecturerEducation;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerEducationResource extends Resource
{
    protected static ?string $model = LecturerEducation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Pendidikan Dosen';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    protected static ?string $slug = 'lecturer-educations';

    protected static ?string $modelLabel = 'Pendidikan Dosen';

    protected static ?string $pluralModelLabel = 'Pendidikan Dosen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
                TextColumn::make('level')->label('Jenjang')->badge(),
                TextColumn::make('institution_name')->label('Institusi')->searchable()->limit(40),
                TextColumn::make('study_program')->label('Program Studi')->limit(36),
                TextColumn::make('end_year')->label('Lulus')->sortable(),
                TextColumn::make('source_type')->label('Sumber')->badge(),
                TextColumn::make('verification_status')->label('Status')->badge(),
                TextColumn::make('visibility')->label('Visibilitas')->badge(),
            ])
            ->actions([
                Action::make('verify')
                    ->label('Verifikasi')
                    ->color('success')
                    ->visible(fn (LecturerEducation $record): bool => $record->verification_status !== 'VERIFIED')
                    ->requiresConfirmation()
                    ->action(fn (LecturerEducation $record): bool => $record->update(['verification_status' => 'VERIFIED'])),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerEducations::route('/')];
    }
}
