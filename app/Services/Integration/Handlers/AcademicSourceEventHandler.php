<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Exceptions\IntegrationProcessingException;
use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use App\Models\PortfolioActivity;
use App\Services\AuditLogger;
use App\Services\CalendarWorkflowService;
use App\Services\InboxWorkflowService;
use App\Services\LecturerIdentityResolver;
use App\Services\SourceDocumentReferenceValidator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

abstract class AcademicSourceEventHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    protected string $operation = 'assignment';

    protected string $domain = 'KP';

    protected string $portfolioEntity = 'academic.activity';

    protected string $eventType = 'ACADEMIC';

    protected string $defaultRole = 'DOSEN';

    public function __construct(
        protected readonly InboxWorkflowService $inbox,
        protected readonly CalendarWorkflowService $calendar,
        protected readonly LecturerIdentityResolver $resolver,
        protected readonly SourceDocumentReferenceValidator $documents,
        AuditLogger $audit,
    ) {
        parent::__construct($audit);
    }

    public function handle(IntegrationEvent $event): array
    {
        return match ($this->operation) {
            'assignment' => $this->handleAssignment($event),
            'assignment_changed' => $this->handleAssignmentChanged($event),
            'schedule', 'reschedule' => $this->handleSchedule($event),
            'completed' => $this->handleCompleted($event),
            'cancelled' => $this->handleCancelled($event),
            default => throw new IntegrationProcessingException('Operasi event tidak didukung.', 'UNSUPPORTED_SOURCE_STATE', false),
        };
    }

    protected function handleAssignmentChanged(IntegrationEvent $event): array
    {
        $data = $this->commonPayload($event, [
            'old_lecturer_core_id' => ['nullable', 'string', 'max:100'],
            'new_lecturer_core_id' => ['nullable', 'string', 'max:100'],
            'lecturer_role' => ['nullable', 'string', 'max:100'],
            'changed_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldLecturerCoreId = $data['old_lecturer_core_id'] ?? null;
        $newLecturerCoreId = $data['new_lecturer_core_id'] ?? $data['lecturer_core_id'];

        return DB::transaction(function () use ($event, $data, $oldLecturerCoreId, $newLecturerCoreId): array {
            $closedOldInbox = 0;
            if ($oldLecturerCoreId && $oldLecturerCoreId !== $newLecturerCoreId) {
                $oldItems = InboxItem::query()
                    ->where('source_app', $event->source_app)
                    ->where('source_record_id', $event->source_record_id)
                    ->where('lecturer_core_id', $oldLecturerCoreId)
                    ->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'ARCHIVED'])
                    ->get();

                $oldItems->each(function (InboxItem $item) use ($event): void {
                    $item->update([
                        'status' => 'CANCELLED',
                        'metadata' => array_merge($item->metadata ?: [], ['cancelled_by_revision' => (int) $event->source_revision]),
                    ]);
                });

                $closedOldInbox = $oldItems->count();
            }

            $existing = $this->inboxItem($event, $data['lecturer_core_id']);
            if ($existing && $this->inboxRevision($existing) > (int) $event->source_revision) {
                return ['status' => 'IGNORED', 'summary' => 'Stale assignment change event ignored.'];
            }

            $title = $data['title'] ?? $this->assignmentTitle($data);
            if ($existing) {
                $existing->update([
                    'title' => $title,
                    'summary' => $this->summary($data),
                    'status' => 'UNREAD',
                    'action_url' => $data['action_url'] ?? null,
                    'safe_action_url' => $this->safeUrl($data['action_url'] ?? null),
                    'metadata' => array_merge($existing->metadata ?: [], $this->metadata($event, $data), [
                        'old_lecturer_core_id' => $oldLecturerCoreId,
                        'new_lecturer_core_id' => $newLecturerCoreId,
                    ]),
                ]);
            } else {
                $existing = $this->inbox->createManual([
                    'lecturer_core_ids' => [$data['lecturer_core_id']],
                    'type' => 'INVITATION',
                    'title' => $title,
                    'summary' => $this->summary($data),
                    'priority' => 'NORMAL',
                    'action_url' => $data['action_url'] ?? null,
                    'source_app' => $event->source_app,
                    'source_record_id' => $event->source_record_id,
                    'metadata' => $this->metadata($event, $data) + [
                        'old_lecturer_core_id' => $oldLecturerCoreId,
                        'new_lecturer_core_id' => $newLecturerCoreId,
                    ],
                ], $this->systemActor());
            }

            $this->notify($data['lecturer_core_id'], 'Perubahan penugasan '.$this->domain, $title, [
                'inbox_item_id' => $existing->id,
                'category' => 'assignment',
                'email_category' => 'assignment',
                'dedupe_key' => $event->event_id.':assignment_changed',
            ]);

            $this->audit->record('integration.assignment.changed', $this->systemActor(), $existing, ['event_id' => $event->event_id, 'source_app' => $event->source_app, 'closed_old_inbox' => $closedOldInbox]);

            return ['summary' => $this->domain.' assignment change processed.', 'related_records' => ['inbox_item_id' => $existing->id, 'closed_old_inbox' => $closedOldInbox]];
        });
    }

    protected function handleAssignment(IntegrationEvent $event): array
    {
        $data = $this->commonPayload($event, [
            'lecturer_role' => ['nullable', 'string', 'max:100'],
            'assigned_at' => ['nullable', 'date'],
            'assignment_number' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($event, $data): array {
            $existing = $this->inboxItem($event, $data['lecturer_core_id']);
            if ($existing && $this->inboxRevision($existing) > (int) $event->source_revision) {
                return ['status' => 'IGNORED', 'summary' => 'Stale assignment event ignored.'];
            }

            $title = $data['title'] ?? $this->assignmentTitle($data);
            if ($existing) {
                $existing->update([
                    'title' => $title,
                    'summary' => $this->summary($data),
                    'status' => $existing->status === 'CANCELLED' && $this->inboxRevision($existing) >= (int) $event->source_revision ? 'CANCELLED' : 'UNREAD',
                    'action_url' => $data['action_url'] ?? null,
                    'safe_action_url' => $this->safeUrl($data['action_url'] ?? null),
                    'metadata' => array_merge($existing->metadata ?: [], $this->metadata($event, $data)),
                ]);
            } else {
                $existing = $this->inbox->createManual([
                    'lecturer_core_ids' => [$data['lecturer_core_id']],
                    'type' => 'INVITATION',
                    'title' => $title,
                    'summary' => $this->summary($data),
                    'priority' => 'NORMAL',
                    'action_url' => $data['action_url'] ?? null,
                    'source_app' => $event->source_app,
                    'source_record_id' => $event->source_record_id,
                    'metadata' => $this->metadata($event, $data),
                ], $this->systemActor());
            }

            $this->notify($data['lecturer_core_id'], 'Penugasan '.$this->domain, $title, [
                'inbox_item_id' => $existing->id,
                'category' => 'assignment',
                'email_category' => 'assignment',
                'dedupe_key' => $event->event_id.':assignment',
            ]);

            $this->audit->record('integration.assignment', $this->systemActor(), $existing, ['event_id' => $event->event_id, 'source_app' => $event->source_app]);

            return ['summary' => $this->domain.' assignment processed.', 'related_records' => ['inbox_item_id' => $existing->id]];
        });
    }

    protected function handleSchedule(IntegrationEvent $event): array
    {
        $data = $this->commonPayload($event, [
            'starts_at' => ['nullable', 'date'],
            'start_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'string', 'max:1000'],
            'activity_type' => ['nullable', 'string', 'max:100'],
        ]);

        $startsAt = $data['starts_at'] ?? $data['start_at'] ?? null;
        if (! $startsAt) {
            throw new IntegrationProcessingException('Tanggal mulai agenda wajib diisi.', 'INVALID_PAYLOAD', false);
        }

        return DB::transaction(function () use ($event, $data, $startsAt): array {
            $calendar = $this->calendarEvent($event, $data['lecturer_core_id']);
            if ($calendar && $this->isStale($event, $calendar)) {
                return ['status' => 'IGNORED', 'summary' => 'Stale schedule event ignored.'];
            }

            if ($calendar && $calendar->status === 'CANCELLED') {
                return ['status' => 'IGNORED', 'summary' => 'Cancelled schedule cannot be reactivated by schedule event.'];
            }

            $inbox = $this->inboxItem($event, $data['lecturer_core_id']);
            $title = $data['title'] ?? $this->scheduleTitle($data);
            $payload = [
                'title' => $title,
                'description' => $this->summary($data),
                'event_type' => $data['activity_type'] ?? $this->eventType,
                'starts_at' => $startsAt,
                'ends_at' => $data['ends_at'] ?? $data['end_at'] ?? Carbon::parse($startsAt)->addHour(),
                'location' => $data['location'] ?? null,
                'meeting_url' => $this->safeUrl($data['meeting_url'] ?? null),
                'inbox_item_id' => $inbox?->id,
                'source_type' => 'SYSTEM',
                'source_app' => $event->source_app,
                'source_record_id' => $event->source_record_id,
                'source_revision' => $event->source_revision,
                'metadata' => $this->metadata($event, $data),
            ];

            if ($calendar) {
                $calendar->update($payload + ['status' => 'SCHEDULED']);
            } else {
                $calendar = $this->calendar->createManual($payload + [
                    'lecturer_core_ids' => [$data['lecturer_core_id']],
                ], $this->systemActor());
            }

            $this->notify($data['lecturer_core_id'], $this->domain.' jadwal baru', $title, [
                'calendar_event_id' => $calendar->id,
                'inbox_item_id' => $inbox?->id,
                'category' => 'schedule',
                'email_category' => 'schedule',
                'dedupe_key' => $event->event_id.':schedule',
            ]);

            $this->audit->record('integration.schedule', $this->systemActor(), $calendar, ['event_id' => $event->event_id, 'source_app' => $event->source_app]);

            return ['summary' => $this->domain.' schedule processed.', 'related_records' => ['calendar_event_id' => $calendar->id, 'inbox_item_id' => $inbox?->id]];
        });
    }

    protected function handleCompleted(IntegrationEvent $event): array
    {
        $data = $this->commonPayload($event, [
            'completed_at' => ['nullable', 'date'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
            'document_references' => ['nullable', 'array'],
        ]);

        if (! empty($data['document_references'])) {
            $this->documents->validateMany($data['document_references']);
        }

        return DB::transaction(function () use ($event, $data): array {
            $calendar = $this->calendarEvent($event, $data['lecturer_core_id']);
            if ($calendar && ($this->isStale($event, $calendar) || $calendar->status === 'CANCELLED')) {
                return ['status' => 'IGNORED', 'summary' => 'Completed event ignored because source is stale or cancelled.'];
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
                    'source_entity' => $this->portfolioEntity,
                    'source_record_id' => $event->source_record_id,
                    'lecturer_core_id' => $data['lecturer_core_id'],
                ],
                [
                    'activity_type' => $data['activity_type'] ?? $this->eventType,
                    'title' => $data['title'] ?? $this->completedTitle($data),
                    'description' => $this->summary($data),
                    'lecturer_role' => $data['lecturer_role'] ?? $this->defaultRole,
                    'academic_year' => $data['academic_year'] ?? null,
                    'semester' => $data['semester'] ?? null,
                    'start_date' => isset($data['completed_at']) ? Carbon::parse($data['completed_at'])->toDateString() : null,
                    'verification_status' => 'SYSTEM_VERIFIED',
                    'source_type' => 'SYSTEM',
                    'visibility' => 'INTERNAL',
                ],
            );

            $this->notify($data['lecturer_core_id'], $this->domain.' selesai', $activity->title, [
                'portfolio_activity_id' => $activity->id,
                'category' => 'assignment',
                'email_category' => 'assignment',
                'dedupe_key' => $event->event_id.':completed',
            ]);

            $this->audit->record('integration.completed', $this->systemActor(), $activity, ['event_id' => $event->event_id, 'source_app' => $event->source_app]);

            return ['summary' => $this->domain.' completion processed.', 'related_records' => ['portfolio_activity_id' => $activity->id, 'calendar_event_id' => $calendar?->id]];
        });
    }

    protected function handleCancelled(IntegrationEvent $event): array
    {
        $data = $this->commonPayload($event, [
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($event, $data): array {
            $calendar = $this->calendarEvent($event, $data['lecturer_core_id']);
            if ($calendar && ! $this->isStale($event, $calendar)) {
                $calendar->update(['status' => 'CANCELLED', 'source_revision' => $event->source_revision]);
            }

            $updatedInbox = InboxItem::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->update(['status' => 'CANCELLED']);

            $this->notify($data['lecturer_core_id'], $this->domain.' dibatalkan', $data['title'] ?? $event->source_record_id, [
                'calendar_event_id' => $calendar?->id,
                'category' => 'schedule',
                'email_category' => 'schedule',
                'dedupe_key' => $event->event_id.':cancelled',
            ]);

            $this->audit->record('integration.cancelled', $this->systemActor(), $calendar, ['event_id' => $event->event_id, 'source_app' => $event->source_app, 'updated_inbox' => $updatedInbox]);

            return ['summary' => $this->domain.' cancellation processed.', 'related_records' => ['calendar_event_id' => $calendar?->id]];
        });
    }

    protected function commonPayload(IntegrationEvent $event, array $extraRules): array
    {
        $data = $this->validate($event, array_merge([
            'lecturer_core_id' => ['nullable', 'string', 'max:100'],
            'core_dosen_id' => ['nullable', 'string', 'max:100'],
            'core_lecturer_id' => ['nullable', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:100'],
            'nidn' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'lecturer_number' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:100'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'program_name' => ['nullable', 'string', 'max:255'],
            'activity_type' => ['nullable', 'string', 'max:100'],
            'action_url' => ['nullable', 'string', 'max:1000'],
        ], $extraRules));

        $resolution = $this->resolver->resolve($data);
        if (! $resolution->resolved()) {
            throw new IntegrationProcessingException($resolution->message ?? 'Identitas dosen gagal dipetakan.', $resolution->status === 'ambiguous' ? 'LECTURER_AMBIGUOUS' : 'LECTURER_NOT_FOUND', false);
        }

        $data['lecturer_core_id'] = $resolution->lecturerCoreId;

        return $data;
    }

    protected function inboxItem(IntegrationEvent $event, string $lecturerCoreId): ?InboxItem
    {
        return InboxItem::query()
            ->where('source_app', $event->source_app)
            ->where('source_record_id', $event->source_record_id)
            ->where('lecturer_core_id', $lecturerCoreId)
            ->first();
    }

    protected function calendarEvent(IntegrationEvent $event, string $lecturerCoreId): ?CalendarEvent
    {
        return CalendarEvent::query()
            ->where('source_app', $event->source_app)
            ->where('source_record_id', $event->source_record_id)
            ->where('lecturer_core_id', $lecturerCoreId)
            ->first();
    }

    protected function inboxRevision(InboxItem $item): int
    {
        return (int) data_get($item->metadata, 'source_revision', 0);
    }

    protected function metadata(IntegrationEvent $event, array $data): array
    {
        return [
            'source_revision' => $event->source_revision,
            'lecturer_role' => $data['lecturer_role'] ?? $this->defaultRole,
            'student_id' => $data['student_id'] ?? null,
            'student_name' => $data['student_name'] ?? null,
            'program_name' => $data['program_name'] ?? $this->domain,
        ];
    }

    protected function summary(array $data): string
    {
        $student = $data['student_name'] ?? $data['student_id'] ?? 'mahasiswa terkait';
        $role = $data['lecturer_role'] ?? $this->defaultRole;

        return "{$this->domain}: {$role} untuk {$student}.";
    }

    protected function assignmentTitle(array $data): string
    {
        return $this->domain.' - Penugasan '.($data['lecturer_role'] ?? $this->defaultRole);
    }

    protected function scheduleTitle(array $data): string
    {
        return $this->domain.' - Jadwal '.($data['student_name'] ?? $data['student_id'] ?? 'kegiatan');
    }

    protected function completedTitle(array $data): string
    {
        return $this->domain.' - '.($data['lecturer_role'] ?? $this->defaultRole).' '.($data['student_name'] ?? $data['student_id'] ?? 'kegiatan');
    }

    protected function systemActor(): AppUser
    {
        return AppUser::query()->where('role', 'admin')->first() ?? new AppUser(['role' => 'system']);
    }
}
