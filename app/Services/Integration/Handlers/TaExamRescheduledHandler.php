<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\CalendarEvent;
use App\Models\IntegrationEvent;
use Illuminate\Support\Facades\DB;

class TaExamRescheduledHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function handle(IntegrationEvent $event): array
    {
        $data = $this->validate($event, [
            'lecturer_core_id' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($event, $data): array {
            $meetingUrl = $this->safeUrl($data['meeting_url'] ?? null);
            $calendar = CalendarEvent::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->first();

            if (! $calendar) {
                return app(TaExamScheduledHandler::class)->handle($event);
            }

            if ($this->isStale($event, $calendar) || $calendar->status === 'CANCELLED') {
                return ['status' => 'IGNORED', 'summary' => 'Stale or cancelled TA reschedule ignored.'];
            }

            $calendar->update([
                'title' => $data['title'] ?? $calendar->title,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'location' => $data['location'] ?? null,
                'meeting_url' => $meetingUrl,
                'source_revision' => $event->source_revision,
            ]);

            $this->notify($data['lecturer_core_id'], 'Perubahan jadwal TA', $calendar->title, [
                'calendar_event_id' => $calendar->id,
                'category' => 'calendar',
                'dedupe_key' => $event->event_id.':rescheduled',
            ]);

            return ['summary' => 'TA exam rescheduled processed.', 'related_records' => ['calendar_event_id' => $calendar->id]];
        });
    }
}
