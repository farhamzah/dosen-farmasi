<?php

namespace App\Filament\Resources\ApplicationSettings\Pages;

use App\Filament\Resources\ApplicationSettings\ApplicationSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListApplicationSettings extends ListRecords
{
    protected static string $resource = ApplicationSettingResource::class;
}
