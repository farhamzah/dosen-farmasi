<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\PortfolioIssueReport;
use App\Notifications\DosenDatabaseNotification;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PortfolioIssueReportController extends Controller
{
    public function store(Request $request, PortfolioActivity $activity, AuditLogger $audit)
    {
        Gate::authorize('create', [PortfolioIssueReport::class, $activity]);

        $data = $request->validate([
            'issue_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:3000'],
            'expected_value' => ['nullable', 'string', 'max:3000'],
            'supporting_document_id' => ['nullable', 'exists:documents,id'],
        ]);

        $report = PortfolioIssueReport::query()->create([
            ...$data,
            'portfolio_activity_id' => $activity->id,
            'reporter_app_user_id' => $request->user()->id,
            'status' => 'OPEN',
        ]);

        $audit->record('issue_report.created', $request->user(), $report, ['portfolio_activity_id' => $activity->id], $request);

        AppUser::query()->where('role', 'admin')->get()->each->notify(new DosenDatabaseNotification(
            'Issue report dibuat',
            $activity->title,
            ['issue_report_id' => $report->id, 'portfolio_activity_id' => $activity->id]
        ));

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function update(Request $request, PortfolioIssueReport $issueReport, AuditLogger $audit)
    {
        Gate::authorize('update', $issueReport);

        $data = $request->validate([
            'status' => ['required', 'in:OPEN,IN_REVIEW,RESOLVED,REJECTED'],
            'admin_response' => ['nullable', 'string', 'max:3000', 'required_if:status,REJECTED'],
        ]);

        $issueReport->update([
            ...$data,
            'resolved_by_app_user_id' => in_array($data['status'], ['RESOLVED', 'REJECTED'], true) ? $request->user()->id : null,
            'resolved_at' => in_array($data['status'], ['RESOLVED', 'REJECTED'], true) ? now() : null,
        ]);

        $audit->record('issue_report.updated', $request->user(), $issueReport, ['status' => $data['status']], $request);
        $issueReport->reporter?->notify(new DosenDatabaseNotification(
            'Issue report diperbarui',
            $issueReport->activity?->title ?? 'Laporan data',
            ['issue_report_id' => $issueReport->id, 'status' => $data['status']]
        ));

        return redirect()->route('dosen.portfolio.show', $issueReport->portfolio_activity_id);
    }
}
