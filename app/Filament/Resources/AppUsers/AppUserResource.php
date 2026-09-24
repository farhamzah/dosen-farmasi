<?php

namespace App\Filament\Resources\AppUsers;

use App\Filament\Resources\AppUsers\Pages\ListAppUsers;
use App\Models\AppUser;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppUserResource extends Resource
{
    protected static ?string $model = AppUser::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna Aplikasi';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $modelLabel = 'Pengguna Aplikasi';

    protected static ?string $pluralModelLabel = 'Pengguna Aplikasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable(),
            TextColumn::make('email')->label('Email')->searchable(),
            TextColumn::make('role')->label('Role')->badge(),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
            TextColumn::make('last_login_at')->label('Login Terakhir')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAppUsers::route('/')];
    }
}
