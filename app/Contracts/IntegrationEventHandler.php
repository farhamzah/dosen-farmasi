<?php

namespace App\Contracts;

use App\Models\IntegrationEvent;

interface IntegrationEventHandler
{
    public function handle(IntegrationEvent $event): array;
}
