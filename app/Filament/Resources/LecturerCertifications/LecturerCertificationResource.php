<?php

namespace App\Filament\Resources\LecturerCertifications;

use App\Filament\Resources\LecturerCertifications\Pages\ListLecturerCertifications;
use App\Models\LecturerCertification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturerCertificationResource extends Resource
{
    protected static ?string $model = LecturerCertification::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Sertifikasi';

    protected static string|\UnitEnum|null $navigationGroup = 'Profil Akademik';

    protected static ?string $modelLabel = 'Sertifikasi';

    protected static ?string $pluralModelLabel = 'Sertifikasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('lecturer_core_id')->label('Dosen Core')->searchable(),
            TextColumn::make('category')->label('Kategori')->badge(),
            TextColumn::make('name')->label('Nama')->searchable()->limit(42),
            TextColumn::make('issuer')->label('Penerbit')->limit(36),
            TextColumn::make('issued_at')->label('Terbit')->date('d M Y'),
            TextColumn::make('expires_at')->label('Berlaku Sampai')->date('d M Y'),
            TextColumn::make('status')->label('Status')->badge(),
            TextColumn::make('verification_status')->label('Verifikasi')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLecturerCertifications::route('/')];
    }
}
