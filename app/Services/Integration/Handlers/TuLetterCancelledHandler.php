<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;

class TuLetterCancelledHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function handle(IntegrationEvent $event): array
    {
        $updated = InboxItem::query()
            ->where('source_app', $event->source_app)
            ->where('source_record_id', $event->source_record_id)
            ->update(['status' => 'CANCELLED']);

        return ['summary' => "TU letter cancelled; {$updated} inbox item(s) updated."];
    }
}
