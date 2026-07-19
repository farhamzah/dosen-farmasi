<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use App\Models\PortfolioActivity;
use Illuminate\Support\Facades\DB;

class TaExamCompletedHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function handle(IntegrationEvent $event): array
    {
        $data = $this->validate($event, [
            'lecturer_core_id' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'activity_type' => ['nullable', 'string', 'max:255'],
            'lecturer_role' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
        ]);

        return DB::transaction(function () use ($event, $data): array {
            $calendar = CalendarEvent::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->first();

            if ($calendar && ($this->isStale($event, $calendar) || $calendar->status === 'CANCELLED')) {
                return ['status' => 'IGNORED', 'summary' => 'Completed event ignored because source state is older or cancelled.'];
            }

            $calendar?->update(['status' => 'COMPLETED', 'source_revision' => $event->source_revision]);

            InboxItem::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->update(['status' => 'COMPLETED']);

            $activity = PortfolioActivity::query()->updateOrCreate(
                [
                    'source_app' => $event->source_app,
                    'source_entity' => 'ta.exam',
                    'source_record_id' => $event->source_record_id,
                    'lecturer_core_id' => $data['lecturer_core_id'],
                ],
                [
                    'activity_type' => $data['activity_type'] ?? 'TA_EXAM',
                    'title' => $data['title'],
                    'lecturer_role' => $data['lecturer_role'] ?? null,
                    'academic_year' => $data['academic_year'] ?? null,
                    'semester' => $data['semester'] ?? null,
                    'verification_status' => 'SYSTEM_VERIFIED',
                    'source_type' => 'SYSTEM',
                    'visibility' => 'INTERNAL',
                ],
            );

            return ['summary' => 'TA exam completed processed.', 'related_records' => ['portfolio_activity_id' => $activity->id, 'calendar_event_id' => $calendar?->id]];
        });
    }
}
