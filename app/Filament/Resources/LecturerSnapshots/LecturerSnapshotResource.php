<?php

namespace App\Filament\Resources\LecturerSnapshots;

use App\Filament\Resources\LecturerSnapshots\Pages\ListLecturerSnapshots;
use App\Models\LecturerSnapshot;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerSnapshotResource extends Resource
{
    protected static ?string $model = LecturerSnapshot::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Data Dosen';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable(),
            TextColumn::make('email')->label('Email')->searchable(),
            TextColumn::make('lecturer_number')->label('Nomor Dosen')->toggleable(),
            TextColumn::make('study_program_name')->label('Prodi')->placeholder('-'),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
            TextColumn::make('synced_at')->label('Sinkron')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerSnapshots::route('/')];
    }
}
