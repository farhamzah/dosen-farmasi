<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use App\Services\AuditLogger;
use App\Services\CalendarWorkflowService;
use App\Services\InboxWorkflowService;
use Illuminate\Support\Facades\DB;

class TaExamScheduledHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function __construct(private readonly InboxWorkflowService $inbox, private readonly CalendarWorkflowService $calendar, AuditLogger $audit)
    {
        parent::__construct($audit);
    }

    public function handle(IntegrationEvent $event): array
    {
        $data = $this->validate($event, [
            'lecturer_core_id' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'string', 'max:1000'],
            'role' => ['nullable', 'string', 'max:100'],
        ]);

        $admin = AppUser::query()->where('role', 'admin')->first() ?? new AppUser(['role' => 'system']);

        return DB::transaction(function () use ($event, $data, $admin): array {
            $meetingUrl = $this->safeUrl($data['meeting_url'] ?? null);
            $inbox = InboxItem::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->first();

            if (! $inbox) {
                $inbox = $this->inbox->createManual([
                    'lecturer_core_ids' => [$data['lecturer_core_id']],
                    'type' => 'INVITATION',
                    'title' => $data['title'],
                    'summary' => 'Undangan agenda TA.',
                    'priority' => 'NORMAL',
                    'source_app' => $event->source_app,
                    'source_record_id' => $event->source_record_id,
                    'metadata' => ['source_revision' => $event->source_revision, 'role' => $data['role'] ?? null],
                ], $admin);
            }

            $calendar = CalendarEvent::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->first();

            if ($calendar && $this->isStale($event, $calendar)) {
                return ['status' => 'IGNORED', 'summary' => 'Stale TA scheduled event ignored.'];
            }

            if ($calendar) {
                if ($calendar->status === 'CANCELLED' && $event->source_revision < (int) $calendar->source_revision) {
                    return ['status' => 'IGNORED', 'summary' => 'Older event cannot reactivate cancelled agenda.'];
                }

                $calendar->update([
                    'title' => $data['title'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'] ?? null,
                    'location' => $data['location'] ?? null,
                    'meeting_url' => $meetingUrl,
                    'inbox_item_id' => $inbox->id,
                    'source_revision' => $event->source_revision,
                    'status' => $calendar->status === 'CANCELLED' ? 'CANCELLED' : 'SCHEDULED',
                ]);
            } else {
                $calendar = $this->calendar->createManual([
                    'lecturer_core_ids' => [$data['lecturer_core_id']],
                    'title' => $data['title'],
                    'description' => 'Agenda dari TA Farmasi.',
                    'event_type' => 'TA_EXAM',
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'] ?? null,
                    'location' => $data['location'] ?? null,
                    'meeting_url' => $meetingUrl,
                    'inbox_item_id' => $inbox->id,
                    'source_type' => 'SYSTEM',
                    'source_app' => $event->source_app,
                    'source_record_id' => $event->source_record_id,
                    'source_revision' => $event->source_revision,
                ], $admin);
            }

            $this->notify($data['lecturer_core_id'], 'Agenda TA baru', $calendar->title, [
                'calendar_event_id' => $calendar->id,
                'inbox_item_id' => $inbox->id,
                'category' => 'calendar',
                'dedupe_key' => $event->event_id.':scheduled',
            ]);

            return ['summary' => 'TA exam scheduled processed.', 'related_records' => ['inbox_item_id' => $inbox->id, 'calendar_event_id' => $calendar->id]];
        });
    }
}
