<?php

namespace App\Filament\Resources\PortfolioVerificationHistories;

use App\Filament\Resources\PortfolioVerificationHistories\Pages\ListPortfolioVerificationHistories;
use App\Models\PortfolioVerificationHistory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortfolioVerificationHistoryResource extends Resource
{
    protected static ?string $model = PortfolioVerificationHistory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Riwayat Verifikasi';

    protected static string|\UnitEnum|null $navigationGroup = 'Portofolio';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('activity.title')->label('Aktivitas')->searchable()->limit(50),
            TextColumn::make('from_status')->label('Dari')->badge(),
            TextColumn::make('to_status')->label('Ke')->badge(),
            TextColumn::make('actor.name')->label('Pelaku')->limit(40),
            TextColumn::make('reason')->label('Alasan')->limit(60),
            TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPortfolioVerificationHistories::route('/')];
    }
}
