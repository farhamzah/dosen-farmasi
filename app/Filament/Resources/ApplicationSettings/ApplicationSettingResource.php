<?php

namespace App\Filament\Resources\ApplicationSettings;

use App\Filament\Resources\ApplicationSettings\Pages\ListApplicationSettings;
use App\Models\ApplicationSetting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApplicationSettingResource extends Resource
{
    protected static ?string $model = ApplicationSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('key')->label('Kunci')->searchable(),
            TextColumn::make('description')->label('Deskripsi')->limit(80),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListApplicationSettings::route('/')];
    }
}
