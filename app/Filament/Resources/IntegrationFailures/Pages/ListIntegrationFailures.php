<?php

namespace App\Filament\Resources\IntegrationFailures\Pages;

use App\Filament\Resources\IntegrationFailures\IntegrationFailureResource;
use Filament\Resources\Pages\ListRecords;

class ListIntegrationFailures extends ListRecords
{
    protected static string $resource = IntegrationFailureResource::class;
}
