<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\AppUser;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use App\Services\AuditLogger;
use App\Services\InboxWorkflowService;

class TuLetterAssignedHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function __construct(private readonly InboxWorkflowService $inbox, AuditLogger $audit)
    {
        parent::__construct($audit);
    }

    public function handle(IntegrationEvent $event): array
    {
        $data = $this->validate($event, [
            'lecturer_core_id' => ['required_without:lecturer_core_ids', 'string'],
            'lecturer_core_ids' => ['nullable', 'array'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'action_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $lecturerIds = $data['lecturer_core_ids'] ?? [$data['lecturer_core_id']];
        $created = 0;

        foreach ($lecturerIds as $lecturerId) {
            $existing = InboxItem::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $lecturerId)
                ->where('type', 'DOCUMENT')
                ->first();

            if ($existing) {
                $existing->update(['title' => $data['title'], 'summary' => $data['summary'] ?? $existing->summary]);

                continue;
            }

            $this->inbox->createManual([
                'lecturer_core_ids' => [$lecturerId],
                'type' => 'DOCUMENT',
                'title' => $data['title'],
                'summary' => $data['summary'] ?? null,
                'priority' => 'NORMAL',
                'action_url' => $data['action_url'] ?? null,
                'source_app' => $event->source_app,
                'source_record_id' => $event->source_record_id,
                'metadata' => ['source_revision' => $event->source_revision],
            ], AppUser::query()->where('role', 'admin')->first() ?? new AppUser(['role' => 'system']));
            $created++;
        }

        return ['summary' => "TU letter assigned processed for {$created} new inbox item(s)."];
    }
}
