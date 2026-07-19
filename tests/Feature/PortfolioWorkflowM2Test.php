<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\PortfolioIssueReport;
use App\Models\PortfolioVerificationHistory;
use App\Services\PortfolioStatusTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PortfolioWorkflowM2Test extends TestCase
{
    use RefreshDatabase;

    public function test_status_transitions_create_history_notification_and_audit(): void
    {
        [$dosen, $admin, $activity] = $this->fixtureActivity('DRAFT');

        $this->actingAs($dosen)->post(route('dosen.portfolio.submit', $activity))->assertRedirect();
        $this->assertSame('SUBMITTED', $activity->fresh()->verification_status);

        $this->actingAs($admin)->post(route('dosen.portfolio.revision', $activity), [
            'reason' => 'Lengkapi bukti kegiatan.',
        ])->assertRedirect();
        $this->assertSame('REVISION_REQUIRED', $activity->fresh()->verification_status);
        $this->assertSame('Lengkapi bukti kegiatan.', $activity->fresh()->revision_reason);

        $this->assertCount(2, PortfolioVerificationHistory::query()->where('portfolio_activity_id', $activity->id)->get());
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $dosen->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'portfolio.revision_requested']);
    }

    public function test_revision_required_can_be_resubmitted_and_verified(): void
    {
        [$dosen, $admin, $activity] = $this->fixtureActivity('REVISION_REQUIRED');

        $this->actingAs($dosen)->put(route('dosen.portfolio.update', $activity), [
            'activity_type' => 'Penelitian',
            'title' => 'Judul Revisi',
            'visibility' => 'PRIVATE',
        ])->assertRedirect();

        $this->actingAs($dosen)->post(route('dosen.portfolio.submit', $activity))->assertRedirect();
        $this->actingAs($admin)->post(route('dosen.portfolio.verify', $activity))->assertRedirect();

        $this->assertSame('ADMIN_VERIFIED', $activity->fresh()->verification_status);
    }

    public function test_revision_and_reject_require_reason(): void
    {
        [, $admin, $activity] = $this->fixtureActivity('SUBMITTED');

        $this->actingAs($admin)->post(route('dosen.portfolio.revision', $activity))->assertSessionHasErrors('reason');
        $this->actingAs($admin)->post(route('dosen.portfolio.reject', $activity))->assertSessionHasErrors('reason');
    }

    public function test_invalid_transition_is_rejected(): void
    {
        [$dosen, $admin, $activity] = $this->fixtureActivity('DRAFT');

        $this->expectException(ValidationException::class);
        app(PortfolioStatusTransitionService::class)->transition($activity, 'ADMIN_VERIFIED', $admin);

        $this->assertFalse($dosen->can('verify', $activity));
    }

    public function test_submitted_activity_cannot_be_edited_by_dosen(): void
    {
        [$dosen,, $activity] = $this->fixtureActivity('SUBMITTED');

        $this->actingAs($dosen)->put(route('dosen.portfolio.update', $activity), [
            'activity_type' => 'Penelitian',
            'title' => 'Tidak Boleh',
            'visibility' => 'PRIVATE',
        ])->assertForbidden();
    }

    public function test_system_verified_official_fields_are_protected_but_personal_notes_allowed(): void
    {
        [$dosen,, $activity] = $this->fixtureActivity('SYSTEM_VERIFIED', ['source_type' => 'SYSTEM', 'source_app' => 'ta-farmasi']);

        $this->actingAs($dosen)->put(route('dosen.portfolio.update', $activity), [
            'title' => 'Diubah',
            'activity_type' => 'Diubah',
            'visibility' => 'INTERNAL',
        ])->assertForbidden();

        $this->actingAs($dosen)->put(route('dosen.portfolio.update', $activity), [
            'personal_notes' => 'Catatan tambahan dosen.',
            'visibility' => 'INTERNAL',
        ])->assertRedirect();

        $fresh = $activity->fresh();
        $this->assertSame('Kegiatan', $fresh->title);
        $this->assertSame('Catatan tambahan dosen.', $fresh->personal_notes);
        $this->assertSame('INTERNAL', $fresh->visibility);
        $this->assertFalse($dosen->can('delete', $fresh));
    }

    public function test_system_verified_issue_report_can_only_be_created_by_owner(): void
    {
        [$dosen,, $activity] = $this->fixtureActivity('SYSTEM_VERIFIED', ['source_type' => 'SYSTEM']);
        $other = AppUser::query()->create(['core_user_id' => '3', 'core_lecturer_id' => '99', 'name' => 'Other', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($dosen)->post(route('dosen.portfolio.issue-reports.store', $activity), [
            'issue_type' => 'Tanggal',
            'description' => 'Tanggal tidak sesuai.',
        ])->assertRedirect();

        $this->assertSame(1, PortfolioIssueReport::query()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'issue_report.created']);

        $this->actingAs($other)->post(route('dosen.portfolio.issue-reports.store', $activity), [
            'issue_type' => 'Tanggal',
            'description' => 'Tidak boleh.',
        ])->assertForbidden();
    }

    public function test_admin_can_update_issue_report_status(): void
    {
        [$dosen, $admin, $activity] = $this->fixtureActivity('SYSTEM_VERIFIED', ['source_type' => 'SYSTEM']);
        $report = PortfolioIssueReport::query()->create([
            'portfolio_activity_id' => $activity->id,
            'reporter_app_user_id' => $dosen->id,
            'issue_type' => 'Tanggal',
            'description' => 'Tanggal salah.',
            'status' => 'OPEN',
        ]);

        $this->actingAs($admin)->patch(route('dosen.issue-reports.update', $report), [
            'status' => 'RESOLVED',
            'admin_response' => 'Diperiksa.',
        ])->assertRedirect();

        $this->assertSame('RESOLVED', $report->fresh()->status);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $dosen->id]);
    }

    private function fixtureActivity(string $status, array $overrides = []): array
    {
        $dosen = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $admin = AppUser::query()->create(['core_user_id' => '2', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);
        $activity = PortfolioActivity::query()->create([
            'lecturer_core_id' => '10',
            'activity_type' => 'Penelitian',
            'title' => 'Kegiatan',
            'verification_status' => $status,
            'source_type' => 'MANUAL',
            'visibility' => 'PRIVATE',
            ...$overrides,
        ]);

        return [$dosen, $admin, $activity];
    }
}
