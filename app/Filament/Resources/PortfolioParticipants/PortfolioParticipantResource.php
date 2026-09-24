<?php

namespace App\Filament\Resources\PortfolioParticipants;

use App\Filament\Resources\PortfolioParticipants\Pages\ListPortfolioParticipants;
use App\Models\PortfolioParticipant;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortfolioParticipantResource extends Resource
{
    protected static ?string $model = PortfolioParticipant::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Peserta Portofolio';

    protected static string|\UnitEnum|null $navigationGroup = 'Portofolio';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('activity.title')->label('Aktivitas')->searchable()->limit(50),
            TextColumn::make('participant_type')->label('Tipe')->badge(),
            TextColumn::make('external_name')->label('Nama')->searchable(),
            TextColumn::make('student_name')->label('Mahasiswa')->searchable(),
            TextColumn::make('institution_name')->label('Institusi')->searchable(),
            TextColumn::make('role')->label('Peran')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPortfolioParticipants::route('/')];
    }
}
