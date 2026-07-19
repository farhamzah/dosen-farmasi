<?php

namespace App\Filament\Resources\PortfolioIssueReports;

use App\Filament\Resources\PortfolioIssueReports\Pages\ListPortfolioIssueReports;
use App\Models\PortfolioIssueReport;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortfolioIssueReportResource extends Resource
{
    protected static ?string $model = PortfolioIssueReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Laporan Kesalahan Data';

    protected static ?string $modelLabel = 'Laporan Kesalahan Data';

    protected static ?string $pluralModelLabel = 'Laporan Kesalahan Data';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('activity.title')->label('Aktivitas')->searchable()->limit(50),
            TextColumn::make('issue_type')->label('Jenis')->searchable(),
            TextColumn::make('status')->label('Status')->badge(),
            TextColumn::make('reporter.name')->label('Pelapor')->limit(40),
            TextColumn::make('description')->label('Deskripsi')->limit(80),
            TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPortfolioIssueReports::route('/')];
    }
}
