<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use App\Models\PortfolioActivity;
use App\Models\PortfolioCategory;
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
            'student_id' => ['nullable', 'string', 'max:100'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'evidence_links' => ['nullable', 'array'],
            'evidence_links.*.type' => ['required_with:evidence_links', 'string', 'max:100'],
            'evidence_links.*.title' => ['required_with:evidence_links', 'string', 'max:255'],
            'evidence_links.*.url' => ['required_with:evidence_links', 'string', 'max:2000'],
        ]);

        $evidenceLinks = collect($data['evidence_links'] ?? [])
            ->map(fn (array $link): array => array_merge($link, ['url' => $this->safeUrl($link['url'])]))
            ->values()
            ->all();

        return DB::transaction(function () use ($event, $data, $evidenceLinks): array {
            $category = PortfolioCategory::query()->firstOrCreate(
                ['slug' => 'pendidikan-dan-pengajaran'],
                ['name' => 'Pendidikan dan Pengajaran', 'sort_order' => 1, 'is_active' => true],
            );
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
                    'category_id' => $category->id,
                    'activity_type' => $data['activity_type'] ?? 'TA_EXAM',
                    'title' => $data['title'],
                    'lecturer_role' => $data['lecturer_role'] ?? null,
                    'student_identifier' => $data['student_id'] ?? null,
                    'student_name' => $data['student_name'] ?? null,
                    'academic_year' => $data['academic_year'] ?? null,
                    'semester' => $data['semester'] ?? null,
                    'verification_status' => 'SYSTEM_VERIFIED',
                    'source_type' => 'SYSTEM',
                    'source_url' => data_get(collect($evidenceLinks)->firstWhere('type', 'proposal_report'), 'url')
                        ?? data_get(collect($evidenceLinks)->firstWhere('type', 'final_report'), 'url'),
                    'evidence_links' => $evidenceLinks,
                    'visibility' => 'INTERNAL',
                ],
            );

            return ['summary' => 'TA exam completed processed.', 'related_records' => ['portfolio_activity_id' => $activity->id, 'calendar_event_id' => $calendar?->id]];
        });
    }
}
