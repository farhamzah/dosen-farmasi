<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\Document;
use App\Models\InboxItem;
use App\Models\IntegrationClient;
use App\Models\IntegrationEvent;
use App\Models\IntegrationFailure;
use App\Models\PortfolioActivity;
use App\Services\IntegrationTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IntegrationKernelM4Test extends TestCase
{
    use RefreshDatabase;

    public function test_integration_health_requires_valid_active_token_and_ability(): void
    {
        $token = $this->clientWithToken('tu-farmasi', ['events:push', 'integration:health']);

        $this->getJson('/api/internal/v1/health')->assertUnauthorized();
        $this->getJson('/api/internal/v1/health', ['Authorization' => 'Bearer invalid'])->assertUnauthorized();
        $this->getJson('/api/internal/v1/health', ['Authorization' => 'Bearer '.$token])->assertOk()->assertJsonPath('client', 'tu-farmasi');

        $oldToken = $token;
        $newToken = app(IntegrationTokenService::class)->rotate(IntegrationClient::query()->where('app_code', 'tu-farmasi')->firstOrFail());

        $this->getJson('/api/internal/v1/health', ['Authorization' => 'Bearer '.$oldToken])->assertUnauthorized();
        $this->getJson('/api/internal/v1/health', ['Authorization' => 'Bearer '.$newToken])->assertOk();
    }

    public function test_events_endpoint_rejects_wrong_ability_spoofed_source_and_unknown_event(): void
    {
        $healthOnly = $this->clientWithToken('tu-farmasi', ['integration:health']);
        $eventsToken = $this->clientWithToken('ta-farmasi', ['events:push'], 'TA Client');

        $this->postJson('/api/internal/v1/events', $this->eventPayload('tu-farmasi', 'tu.letter.assigned'), [
            'Authorization' => 'Bearer '.$healthOnly,
        ])->assertForbidden();

        $this->postJson('/api/internal/v1/events', $this->eventPayload('tu-farmasi', 'tu.letter.assigned'), [
            'Authorization' => 'Bearer '.$eventsToken,
        ])->assertForbidden();

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'unknown.event'), [
            'Authorization' => 'Bearer '.$eventsToken,
        ])->assertUnprocessable();
    }

    public function test_tu_letter_lifecycle_is_idempotent_and_preserves_history(): void
    {
        AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $token = $this->clientWithToken('tu-farmasi', ['events:push']);
        $eventId = (string) Str::uuid();

        $payload = $this->eventPayload('tu-farmasi', 'tu.letter.assigned', [
            'event_id' => $eventId,
            'source_record_id' => 'letter-1',
            'payload' => [
                'lecturer_core_id' => '10',
                'title' => 'Surat Tugas Seminar',
                'summary' => 'Anda ditugaskan.',
                'action_url' => '/dosen/dokumen',
            ],
        ]);

        $this->postJson('/api/internal/v1/events', $payload, ['Authorization' => 'Bearer '.$token])->assertAccepted();
        $this->postJson('/api/internal/v1/events', $payload, ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('status', 'duplicate');

        $this->assertSame(1, InboxItem::query()->where('source_record_id', 'letter-1')->count());

        $this->postJson('/api/internal/v1/events', $this->eventPayload('tu-farmasi', 'tu.letter.published', [
            'source_record_id' => 'letter-1',
            'source_revision' => 2,
            'payload' => [
                'lecturer_core_id' => '10',
                'title' => 'Surat Tugas Seminar',
                'filename' => 'surat.pdf',
                'path' => 'tu/letter-1/surat.pdf',
                'sha256_checksum' => str_repeat('a', 64),
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame(1, Document::query()->where('source_record_id', 'letter-1')->count());
        $this->assertNotNull(InboxItem::query()->where('source_record_id', 'letter-1')->firstOrFail()->document_id);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('tu-farmasi', 'tu.letter.cancelled', [
            'source_record_id' => 'letter-1',
            'source_revision' => 3,
            'payload' => ['lecturer_core_id' => '10', 'reason' => 'Dibatalkan TU'],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('CANCELLED', InboxItem::query()->where('source_record_id', 'letter-1')->firstOrFail()->status);
        $this->assertSame(1, Document::query()->where('source_record_id', 'letter-1')->count());
    }

    public function test_ta_exam_lifecycle_handles_reschedule_completion_cancellation_and_stale_events(): void
    {
        AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $token = $this->clientWithToken('ta-farmasi', ['events:push']);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'ta.exam.scheduled', [
            'source_record_id' => 'exam-1',
            'payload' => [
                'lecturer_core_id' => '10',
                'title' => 'Sidang TA',
                'starts_at' => now()->addDay()->toIso8601String(),
                'ends_at' => now()->addDay()->addHour()->toIso8601String(),
                'meeting_url' => 'https://meet.example.test/exam-1',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $calendar = CalendarEvent::query()->where('source_record_id', 'exam-1')->firstOrFail();
        $this->assertSame('SCHEDULED', $calendar->status);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'ta.exam.rescheduled', [
            'source_record_id' => 'exam-1',
            'source_revision' => 2,
            'payload' => [
                'lecturer_core_id' => '10',
                'title' => 'Sidang TA Revisi',
                'starts_at' => now()->addDays(2)->toIso8601String(),
                'ends_at' => now()->addDays(2)->addHour()->toIso8601String(),
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('Sidang TA Revisi', $calendar->fresh()->title);
        $this->assertSame(1, CalendarEvent::query()->where('source_record_id', 'exam-1')->count());

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'ta.exam.completed', [
            'source_record_id' => 'exam-1',
            'source_revision' => 3,
            'payload' => [
                'lecturer_core_id' => '10',
                'title' => 'Penguji Sidang TA',
                'activity_type' => 'TA_EXAM',
                'lecturer_role' => 'Penguji',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('COMPLETED', $calendar->fresh()->status);
        $this->assertSame('SYSTEM_VERIFIED', PortfolioActivity::query()->where('source_record_id', 'exam-1')->firstOrFail()->verification_status);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'ta.exam.cancelled', [
            'source_record_id' => 'exam-1',
            'source_revision' => 4,
            'payload' => ['lecturer_core_id' => '10', 'reason' => 'Dibatalkan'],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('CANCELLED', $calendar->fresh()->status);
        $this->assertSame('CANCELLED', InboxItem::query()->where('source_record_id', 'exam-1')->firstOrFail()->status);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'ta.exam.completed', [
            'source_record_id' => 'exam-1',
            'source_revision' => 3,
            'payload' => ['lecturer_core_id' => '10', 'title' => 'Event Lama'],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('IGNORED', IntegrationEvent::query()->latest('id')->firstOrFail()->status);
        $this->assertSame('CANCELLED', $calendar->fresh()->status);
    }

    public function test_failed_event_can_be_replayed_by_admin_after_payload_is_fixed(): void
    {
        AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $admin = AppUser::query()->create(['core_user_id' => '2', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);
        $token = $this->clientWithToken('ta-farmasi', ['events:push']);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'ta.exam.scheduled', [
            'source_record_id' => 'exam-bad',
            'payload' => [
                'lecturer_core_id' => '10',
                'title' => null,
                'starts_at' => now()->addDay()->toIso8601String(),
            ],
        ]), ['Authorization' => 'Bearer '.$token])
            ->assertAccepted()
            ->assertJsonPath('processing_status', 'FAILED');

        $event = IntegrationEvent::query()->where('source_record_id', 'exam-bad')->firstOrFail();
        $this->assertSame(1, IntegrationFailure::query()->where('integration_event_id', $event->id)->count());

        $event->update(['payload' => [
            'lecturer_core_id' => '10',
            'title' => 'Sidang TA Setelah Perbaikan',
            'starts_at' => now()->addDay()->toIso8601String(),
        ]]);

        $this->actingAs($admin)->post(route('admin.integration-events.retry', $event))->assertRedirect();

        $this->assertSame('PROCESSED', $event->fresh()->status);
        $this->assertSame(1, CalendarEvent::query()->where('source_record_id', 'exam-bad')->count());
    }

    private function clientWithToken(string $appCode, array $abilities, string $name = 'Client'): string
    {
        $client = IntegrationClient::query()->updateOrCreate(
            ['app_code' => $appCode],
            [
                'name' => $name,
                'code' => $appCode,
                'allowed_abilities' => $abilities,
                'abilities' => $abilities,
                'is_active' => true,
            ],
        );

        return app(IntegrationTokenService::class)->rotate($client);
    }

    private function eventPayload(string $sourceApp, string $eventType, array $overrides = []): array
    {
        return array_replace_recursive([
            'event_id' => (string) Str::uuid(),
            'event_type' => $eventType,
            'event_version' => 1,
            'source_app' => $sourceApp,
            'source_record_id' => 'record-1',
            'source_revision' => 1,
            'occurred_at' => now()->toIso8601String(),
            'payload' => [
                'lecturer_core_id' => '10',
                'title' => 'Default Event',
            ],
        ], $overrides);
    }
}
