<?php

namespace App\Http\Controllers;

use App\Models\PortfolioActivity;
use App\Models\PortfolioParticipant;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PortfolioParticipantController extends Controller
{
    public function store(Request $request, PortfolioActivity $activity, AuditLogger $audit)
    {
        Gate::authorize('create', [PortfolioParticipant::class, $activity]);

        $data = $request->validate([
            'participant_type' => ['required', 'in:INTERNAL_LECTURER,STUDENT,EXTERNAL_PERSON,INSTITUTION'],
            'core_dosen_id' => ['nullable', 'string', 'max:100'],
            'external_name' => ['nullable', 'string', 'max:255'],
            'student_identifier' => ['nullable', 'string', 'max:100'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $participant = $activity->participants()->create([
            ...$data,
            'is_primary' => (bool) ($data['is_primary'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'participant_fingerprint' => PortfolioParticipant::fingerprint($data),
        ]);

        $audit->record('participant.added', $request->user(), $participant, ['portfolio_activity_id' => $activity->id], $request);

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function destroy(Request $request, PortfolioParticipant $participant, AuditLogger $audit)
    {
        Gate::authorize('delete', $participant);

        $activityId = $participant->portfolio_activity_id;
        $participant->delete();
        $audit->record('participant.removed', $request->user(), $participant, ['portfolio_activity_id' => $activityId], $request);

        return redirect()->route('dosen.portfolio.show', $activityId);
    }
}
