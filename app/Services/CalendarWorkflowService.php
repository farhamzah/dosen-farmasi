<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\CalendarEventAttendee;
use App\Notifications\DosenDatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CalendarWorkflowService
{
    public function __construct(private readonly AuditLogger $audit, private readonly SafeUrlValidator $urls) {}

    public function createManual(array $data, AppUser $actor): CalendarEvent
    {
        $attendees = array_values(array_unique(array_map('strval', $data['lecturer_core_ids'] ?? [$data['lecturer_core_id'] ?? null])));
        $attendees = array_values(array_filter($attendees));

        if ($attendees === []) {
            throw ValidationException::withMessages(['lecturer_core_ids' => 'Peserta agenda wajib dipilih.']);
        }

        if (! empty($data['meeting_url'])) {
            $data['meeting_url'] = $this->urls->validate($data['meeting_url']);
        }

        return DB::transaction(function () use ($data, $actor, $attendees): CalendarEvent {
            $groupId = (string) Str::uuid();
            $first = null;

            foreach ($attendees as $lecturerId) {
                $event = CalendarEvent::query()->create([
                    'group_id' => $groupId,
                    'lecturer_core_id' => $lecturerId,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'event_type' => $data['event_type'],
                    'status' => $data['status'] ?? 'SCHEDULED',
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'] ?? null,
                    'location' => $data['location'] ?? null,
                    'source_type' => $data['source_type'] ?? 'MANUAL',
                    'meeting_url' => $data['meeting_url'] ?? null,
                    'inbox_item_id' => $data['inbox_item_id'] ?? null,
                    'document_id' => $data['document_id'] ?? null,
                    'source_app' => $data['source_app'] ?? null,
                    'source_record_id' => $data['source_record_id'] ?? null,
                    'source_revision' => $data['source_revision'] ?? 1,
                    'metadata' => $data['metadata'] ?? null,
                ]);

                CalendarEventAttendee::query()->create([
                    'calendar_event_id' => $event->id,
                    'lecturer_core_id' => $lecturerId,
                    'app_user_id' => AppUser::query()->where('core_lecturer_id', $lecturerId)->value('id'),
                    'status' => 'INVITED',
                ]);

                AppUser::query()->where('core_lecturer_id', $lecturerId)->first()?->notify(new DosenDatabaseNotification(
                    'Agenda baru',
                    $event->title,
                    ['calendar_event_id' => $event->id, 'category' => 'calendar']
                ));

                $first ??= $event;
            }

            $this->audit->record('calendar.created', $actor, $first, ['attendee_count' => count($attendees)]);

            return $first;
        });
    }

    public function hasOverlap(string $lecturerCoreId, mixed $start, mixed $end, ?int $ignoreId = null): bool
    {
        $end ??= now()->parse($start)->addHour();

        return CalendarEvent::query()
            ->where('lecturer_core_id', $lecturerCoreId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('status', '!=', 'CANCELLED')
            ->where('starts_at', '<', $end)
            ->where(function ($query) use ($start): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', $start);
            })
            ->exists();
    }
}
