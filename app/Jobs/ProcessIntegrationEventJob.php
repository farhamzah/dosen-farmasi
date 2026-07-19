<?php

namespace App\Jobs;

use App\Models\IntegrationEvent;
use App\Services\IntegrationEventProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessIntegrationEventJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $integrationEventId) {}

    public function handle(IntegrationEventProcessor $processor): void
    {
        $event = IntegrationEvent::query()->findOrFail($this->integrationEventId);
        $processor->process($event);
    }
}
