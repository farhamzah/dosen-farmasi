<?php

namespace App\Filament\Resources\IntegrationEvents\Pages;

use App\Filament\Resources\IntegrationEvents\IntegrationEventResource;
use Filament\Resources\Pages\ListRecords;

class ListIntegrationEvents extends ListRecords
{
    protected static string $resource = IntegrationEventResource::class;
}
