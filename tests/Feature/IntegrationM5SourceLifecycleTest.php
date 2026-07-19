<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\IntegrationClient;
use App\Models\IntegrationEvent;
use App\Models\IntegrationFailure;
use App\Models\IntegrationSyncCursor;
use App\Models\LecturerSnapshot;
use App\Models\NotificationPreference;
use App\Models\PortfolioActivity;
use App\Notifications\DosenDatabaseNotification;
use App\Services\IntegrationTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class IntegrationM5SourceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_kp_lifecycle_assignment_schedule_reschedule_complete_cancel_and_duplicate_email_guard(): void
    {
        Notification::fake();
        $user = $this->lecturer('10');
        NotificationPreference::query()->create([
            'app_user_id' => $user->id,
            'email_enabled' => true,
            'email_for_assignments' => true,
            'email_for_schedule_changes' => true,
            'email_for_documents' => true,
        ]);
        $token = $this->clientWithToken('kp-farmasi');

        $assignment = $this->eventPayload('kp-farmasi', 'kp.supervisor.assigned', [
            'source_record_id' => 'kp-1',
            'payload' => [
                'lecturer_core_id' => '10',
                'lecturer_role' => 'PEMBIMBING_DALAM',
                'student_id' => 'S-1',
                'student_name' => 'Andi',
                'action_url' => '/kp/assignments/1',
            ],
        ]);
        $this->postJson('/api/internal/v1/events', $assignment, ['Authorization' => 'Bearer '.$token])->assertAccepted();
        $this->postJson('/api/internal/v1/events', $assignment, ['Authorization' => 'Bearer '.$token])->assertOk()->assertJsonPath('status', 'duplicate');

        $this->assertSame(1, InboxItem::query()->where('source_record_id', 'kp-1')->count());

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.exam.scheduled', [
            'source_record_id' => 'kp-1',
            'source_revision' => 2,
            'payload' => [
                'lecturer_core_id' => '10',
                'student_name' => 'Andi',
                'starts_at' => now()->addDay()->toIso8601String(),
                'ends_at' => now()->addDay()->addHour()->toIso8601String(),
                'location' => 'Ruang Sidang',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $calendar = CalendarEvent::query()->where('source_record_id', 'kp-1')->firstOrFail();
        $this->assertSame('SCHEDULED', $calendar->status);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.exam.rescheduled', [
            'source_record_id' => 'kp-1',
            'source_revision' => 3,
            'payload' => [
                'lecturer_core_id' => '10',
                'student_name' => 'Andi',
                'starts_at' => now()->addDays(2)->toIso8601String(),
                'ends_at' => now()->addDays(2)->addHour()->toIso8601String(),
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame(1, CalendarEvent::query()->where('source_record_id', 'kp-1')->count());
        $this->assertSame(3, (int) $calendar->fresh()->source_revision);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.exam.completed', [
            'source_record_id' => 'kp-1',
            'source_revision' => 4,
            'payload' => [
                'lecturer_core_id' => '10',
                'lecturer_role' => 'PENGUJI_1',
                'student_name' => 'Andi',
                'completed_at' => now()->toIso8601String(),
                'academic_year' => '2026/2027',
                'semester' => 'GANJIL',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('COMPLETED', $calendar->fresh()->status);
        $this->assertSame('SYSTEM_VERIFIED', PortfolioActivity::query()->where('source_record_id', 'kp-1')->firstOrFail()->verification_status);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.exam.cancelled', [
            'source_record_id' => 'kp-1',
            'source_revision' => 5,
            'payload' => ['lecturer_core_id' => '10', 'reason' => 'Batal'],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('CANCELLED', $calendar->fresh()->status);
        Notification::assertSentTo($user, DosenDatabaseNotification::class, fn ($notification): bool => in_array('mail', $notification->via($user), true));
    }

    public function test_kp_pspa_and_lab_completion_create_only_relevant_system_verified_portfolio(): void
    {
        $this->lecturer('10');
        $pspaToken = $this->clientWithToken('kp-pspa');
        $labToken = $this->clientWithToken('lab-farmasi');

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-pspa', 'kpspa.activity.completed', [
            'source_record_id' => 'pkpa-1',
            'payload' => [
                'lecturer_core_id' => '10',
                'lecturer_role' => 'PEMBIMBING_PKPA',
                'student_name' => 'Budi',
                'completed_at' => now()->toIso8601String(),
            ],
        ]), ['Authorization' => 'Bearer '.$pspaToken])->assertAccepted();

        $this->postJson('/api/internal/v1/events', $this->eventPayload('lab-farmasi', 'lab.activity.completed', [
            'source_record_id' => 'lab-1',
            'payload' => [
                'lecturer_core_id' => '10',
                'lecturer_role' => 'KOORDINATOR_PRAKTIKUM',
                'student_name' => 'Kelas Praktikum A',
                'completed_at' => now()->toIso8601String(),
            ],
        ]), ['Authorization' => 'Bearer '.$labToken])->assertAccepted();

        $this->assertSame(1, PortfolioActivity::query()->where('source_entity', 'kpspa.activity')->count());
        $this->assertSame(1, PortfolioActivity::query()->where('source_entity', 'lab.activity')->count());
    }

    public function test_ta_assignment_events_create_inbox_without_portfolio_before_completion(): void
    {
        $this->lecturer('10');
        $token = $this->clientWithToken('ta-farmasi');

        $this->postJson('/api/internal/v1/events', $this->eventPayload('ta-farmasi', 'ta.supervisor.assigned', [
            'source_record_id' => 'ta-assignment-1',
            'payload' => [
                'lecturer_core_id' => '10',
                'lecturer_role' => 'PEMBIMBING_1',
                'student_name' => 'Citra',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame(1, InboxItem::query()->where('source_record_id', 'ta-assignment-1')->count());
        $this->assertSame(0, PortfolioActivity::query()->where('source_record_id', 'ta-assignment-1')->count());
    }

    public function test_identity_resolution_fallback_and_ambiguity_failure_categories(): void
    {
        $this->lecturer('10', ['nip' => 'NIP-10']);
        $this->lecturer('20', ['nip' => 'NIP-DUP']);
        $this->lecturer('30', ['nip' => 'NIP-DUP']);
        $token = $this->clientWithToken('kp-farmasi');

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.examiner.assigned', [
            'source_record_id' => 'kp-nip',
            'payload' => [
                'lecturer_core_id' => null,
                'nip' => 'NIP-10',
                'lecturer_role' => 'PENGUJI',
                'student_name' => 'Dina',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();
        $this->assertSame('PROCESSED', IntegrationEvent::query()->where('source_record_id', 'kp-nip')->firstOrFail()->status);

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.examiner.assigned', [
            'source_record_id' => 'kp-ambiguous',
            'payload' => [
                'lecturer_core_id' => null,
                'nip' => 'NIP-DUP',
                'lecturer_role' => 'PENGUJI',
                'student_name' => 'Dina',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $event = IntegrationEvent::query()->where('source_record_id', 'kp-ambiguous')->firstOrFail();
        $this->assertSame('FAILED', $event->status);
        $this->assertSame('LECTURER_AMBIGUOUS', IntegrationFailure::query()->where('integration_event_id', $event->id)->firstOrFail()->failure_category);
    }

    public function test_kp_changed_event_closes_old_lecturer_inbox_and_creates_new_assignment(): void
    {
        $this->lecturer('10');
        $this->lecturer('20');
        $token = $this->clientWithToken('kp-farmasi');

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.supervisor.assigned', [
            'source_record_id' => 'KP-ASSIGNMENT-77',
            'source_revision' => 1,
            'payload' => [
                'lecturer_core_id' => '10',
                'lecturer_role' => 'PEMBIMBING_DALAM',
                'student_name' => 'Mahasiswa Pilot',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.supervisor.changed', [
            'source_record_id' => 'KP-ASSIGNMENT-77',
            'source_revision' => 2,
            'payload' => [
                'lecturer_core_id' => '20',
                'old_lecturer_core_id' => '10',
                'new_lecturer_core_id' => '20',
                'lecturer_role' => 'PEMBIMBING_DALAM',
                'student_name' => 'Mahasiswa Pilot',
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $this->assertSame('CANCELLED', InboxItem::query()
            ->where('source_record_id', 'KP-ASSIGNMENT-77')
            ->where('lecturer_core_id', '10')
            ->firstOrFail()
            ->status);
        $this->assertSame('UNREAD', InboxItem::query()
            ->where('source_record_id', 'KP-ASSIGNMENT-77')
            ->where('lecturer_core_id', '20')
            ->firstOrFail()
            ->status);
    }

    public function test_document_reference_path_traversal_fails_safely(): void
    {
        $this->lecturer('10');
        $token = $this->clientWithToken('kp-farmasi');

        $this->postJson('/api/internal/v1/events', $this->eventPayload('kp-farmasi', 'kp.exam.completed', [
            'source_record_id' => 'kp-doc',
            'payload' => [
                'lecturer_core_id' => '10',
                'student_name' => 'Eka',
                'document_references' => [[
                    'storage_disk_alias' => 'shared_private',
                    'relative_path' => '../secret.pdf',
                    'sha256' => str_repeat('a', 64),
                ]],
            ],
        ]), ['Authorization' => 'Bearer '.$token])->assertAccepted();

        $event = IntegrationEvent::query()->where('source_record_id', 'kp-doc')->firstOrFail();
        $this->assertSame('FAILED', $event->status);
        $this->assertSame('DOCUMENT_NOT_FOUND', $event->last_error_code);
    }

    public function test_pull_adapter_dry_run_does_not_write_and_normal_run_updates_cursor(): void
    {
        $this->lecturer('10');
        config([
            'database.connections.kp_source_testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'dosen_farmasi.integration.source_connections.kp-farmasi' => 'kp_source_testing',
        ]);

        Schema::connection('kp_source_testing')->create('dosen_integration_outbox', function ($table): void {
            $table->id();
            $table->uuid('event_id');
            $table->string('event_type');
            $table->integer('event_version')->default(1);
            $table->string('source_app');
            $table->string('source_record_id');
            $table->integer('source_revision')->default(1);
            $table->timestamp('occurred_at')->nullable();
            $table->json('payload');
            $table->timestamps();
        });

        DB::connection('kp_source_testing')->table('dosen_integration_outbox')->insert([
            'event_id' => (string) Str::uuid(),
            'event_type' => 'kp.supervisor.assigned',
            'event_version' => 1,
            'source_app' => 'kp-farmasi',
            'source_record_id' => 'kp-pull-1',
            'source_revision' => 1,
            'occurred_at' => now(),
            'payload' => json_encode(['lecturer_core_id' => '10', 'student_name' => 'Fajar']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('dosen:sync-integrations', ['source' => 'kp-farmasi', '--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, IntegrationEvent::query()->where('source_record_id', 'kp-pull-1')->count());

        $this->artisan('dosen:sync-integrations', ['source' => 'kp-farmasi'])->assertSuccessful();
        $this->assertSame(1, IntegrationEvent::query()->where('source_record_id', 'kp-pull-1')->count());
        $this->assertSame('IDLE', IntegrationSyncCursor::query()->where('source_app', 'kp-farmasi')->firstOrFail()->status);
    }

    public function test_kp_integration_audit_reports_missing_consumer_and_portfolio_objects(): void
    {
        config([
            'database.connections.kp_audit_testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        Schema::connection('kp_audit_testing')->create('integration_outbox_events', function ($table): void {
            $table->id();
            $table->uuid('event_id');
            $table->string('event_type');
            $table->string('source_app');
            $table->string('source_record_id');
            $table->string('status');
            $table->timestamps();
        });

        DB::connection('kp_audit_testing')->table('integration_outbox_events')->insert([
            'event_id' => (string) Str::uuid(),
            'event_type' => 'kp.exam.completed',
            'source_app' => 'kp-farmasi',
            'source_record_id' => 'KP-EXAM-MISSING',
            'status' => 'SENT',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        IntegrationEvent::query()->create([
            'event_id' => (string) Str::uuid(),
            'event_type' => 'kp.exam.completed',
            'event_version' => 1,
            'source_app' => 'kp-farmasi',
            'source_record_id' => 'KP-EXAM-NO-PORTFOLIO',
            'source_revision' => 1,
            'lecturer_core_id' => '10',
            'payload' => ['lecturer_core_id' => '10'],
            'payload_hash' => hash('sha256', 'KP-EXAM-NO-PORTFOLIO'),
            'status' => 'PROCESSED',
            'processed_at' => now(),
            'related_records' => ['portfolio_activity_id' => 999],
        ]);

        $this->artisan('dosen:audit-kp-integration', ['--source-connection' => 'kp_audit_testing'])
            ->expectsOutputToContain('Mode: read-only')
            ->expectsOutputToContain('KP outbox table: available')
            ->expectsOutputToContain('SENT without consumer event: 1')
            ->expectsOutputToContain('Completed event missing SYSTEM_VERIFIED portfolio: 1')
            ->assertSuccessful();
    }

    private function lecturer(string $coreLecturerId, array $overrides = []): AppUser
    {
        $user = AppUser::query()->create(array_merge([
            'core_user_id' => 'user-'.$coreLecturerId,
            'core_lecturer_id' => $coreLecturerId,
            'name' => 'Dosen '.$coreLecturerId,
            'email' => 'dosen'.$coreLecturerId.'@example.test',
            'role' => 'dosen',
            'is_active' => true,
        ], $overrides));

        LecturerSnapshot::query()->create([
            'core_user_id' => $user->core_user_id,
            'core_lecturer_id' => $coreLecturerId,
            'name' => $user->name,
            'email' => $user->email,
            'nip' => $user->nip,
            'nidn' => $user->nidn,
            'lecturer_number' => $user->lecturer_number,
            'is_active' => true,
        ]);

        return $user;
    }

    private function clientWithToken(string $appCode): string
    {
        $client = IntegrationClient::query()->updateOrCreate(
            ['app_code' => $appCode],
            [
                'name' => $appCode,
                'code' => $appCode,
                'allowed_abilities' => ['events:push', 'integration:health'],
                'abilities' => ['events:push', 'integration:health'],
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
            'payload' => ['lecturer_core_id' => '10', 'student_name' => 'Mahasiswa'],
        ], $overrides);
    }
}
