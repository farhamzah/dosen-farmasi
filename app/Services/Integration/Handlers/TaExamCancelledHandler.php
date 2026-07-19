<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use Illuminate\Support\Facades\DB;

class TaExamCancelledHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function handle(IntegrationEvent $event): array
    {
        $data = $this->validate($event, [
            'lecturer_core_id' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($event, $data): array {
            $calendar = CalendarEvent::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->first();

            if ($calendar && ! $this->isStale($event, $calendar)) {
                $calendar->update(['status' => 'CANCELLED', 'source_revision' => $event->source_revision]);
            }

            $updatedInbox = InboxItem::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->update(['status' => 'CANCELLED']);

            $this->notify($data['lecturer_core_id'], 'Agenda dibatalkan', $calendar?->title ?? $event->source_record_id, [
                'calendar_event_id' => $calendar?->id,
                'category' => 'calendar',
                'dedupe_key' => $event->event_id.':cancelled',
            ]);

            return ['summary' => "TA exam cancelled; {$updatedInbox} inbox item(s) updated.", 'related_records' => ['calendar_event_id' => $calendar?->id]];
        });
    }
}
