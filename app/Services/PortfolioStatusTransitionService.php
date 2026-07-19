<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\PortfolioVerificationHistory;
use App\Notifications\DosenDatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PortfolioStatusTransitionService
{
    private const ALLOWED = [
        'DRAFT' => ['SUBMITTED'],
        'REVISION_REQUIRED' => ['SUBMITTED'],
        'SUBMITTED' => ['ADMIN_VERIFIED', 'REVISION_REQUIRED', 'REJECTED'],
        'ADMIN_VERIFIED' => ['ARCHIVED'],
        'SYSTEM_VERIFIED' => ['CANCELLED'],
    ];

    public function __construct(private readonly AuditLogger $audit) {}

    public function canTransition(PortfolioActivity $activity, string $toStatus, ?AppUser $actor = null): bool
    {
        $from = $activity->verification_status;

        if (! in_array($toStatus, self::ALLOWED[$from] ?? [], true)) {
            return false;
        }

        if ($actor?->isAdmin()) {
            return true;
        }

        return in_array($toStatus, ['SUBMITTED'], true)
            && $activity->isEditableByDosen()
            && (string) $activity->lecturer_core_id === (string) $actor?->core_lecturer_id;
    }

    public function transition(PortfolioActivity $activity, string $toStatus, AppUser $actor, ?string $reason = null, ?string $notes = null, array $metadata = []): PortfolioActivity
    {
        $toStatus = strtoupper($toStatus);

        if (! $this->canTransition($activity, $toStatus, $actor)) {
            throw ValidationException::withMessages(['status' => 'Transisi status tidak diizinkan.']);
        }

        if (in_array($toStatus, ['REVISION_REQUIRED', 'REJECTED'], true) && blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Alasan wajib diisi.']);
        }

        return DB::transaction(function () use ($activity, $toStatus, $actor, $reason, $notes, $metadata): PortfolioActivity {
            $locked = PortfolioActivity::query()->lockForUpdate()->findOrFail($activity->id);
            $fromStatus = $locked->verification_status;

            if (! $this->canTransition($locked, $toStatus, $actor)) {
                throw ValidationException::withMessages(['status' => 'Transisi status tidak diizinkan.']);
            }

            $updates = ['verification_status' => $toStatus];

            if ($toStatus === 'SUBMITTED') {
                $updates['revision_reason'] = null;
                $updates['rejection_reason'] = null;
            }

            if ($toStatus === 'ADMIN_VERIFIED') {
                $updates['verified_at'] = now();
                $updates['verified_by_app_user_id'] = $actor->id;
            }

            if ($toStatus === 'REVISION_REQUIRED') {
                $updates['revision_reason'] = $reason;
            }

            if ($toStatus === 'REJECTED') {
                $updates['rejection_reason'] = $reason;
            }

            if ($toStatus === 'ARCHIVED') {
                $updates['archived_at'] = now();
            }

            $locked->update($updates);

            PortfolioVerificationHistory::query()->create([
                'portfolio_activity_id' => $locked->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'actor_app_user_id' => $actor->id,
                'actor_core_user_id' => $actor->core_user_id,
                'actor_role' => $actor->role,
                'reason' => $reason,
                'notes' => $notes,
                'metadata' => $metadata ?: null,
                'created_at' => now(),
            ]);

            $this->audit->record(match ($toStatus) {
                'SUBMITTED' => 'portfolio.submitted',
                'ADMIN_VERIFIED' => 'portfolio.verified',
                'REVISION_REQUIRED' => 'portfolio.revision_requested',
                'REJECTED' => 'portfolio.rejected',
                'ARCHIVED' => 'portfolio.archived',
                'CANCELLED' => 'portfolio.cancelled',
                default => 'portfolio.status_changed',
            }, $actor, $locked, ['from_status' => $fromStatus, 'to_status' => $toStatus, 'reason' => $reason]);

            $this->notifyOwner($locked, $toStatus, $reason);

            return $locked->fresh(['category', 'histories']);
        });
    }

    private function notifyOwner(PortfolioActivity $activity, string $toStatus, ?string $reason): void
    {
        $owner = AppUser::query()
            ->where('core_lecturer_id', $activity->lecturer_core_id)
            ->where('role', 'dosen')
            ->first();

        if (! $owner) {
            return;
        }

        $title = match ($toStatus) {
            'SUBMITTED' => 'Aktivitas berhasil diajukan',
            'ADMIN_VERIFIED' => 'Aktivitas diverifikasi',
            'REVISION_REQUIRED' => 'Aktivitas perlu revisi',
            'REJECTED' => 'Aktivitas ditolak',
            default => 'Status aktivitas berubah',
        };

        $owner->notify(new DosenDatabaseNotification($title, $activity->title, [
            'portfolio_activity_id' => $activity->id,
            'status' => $toStatus,
            'reason' => $reason,
        ]));
    }
}
