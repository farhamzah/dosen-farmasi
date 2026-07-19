<?php

namespace App\Http\Controllers;

use App\Models\PortfolioActivity;
use App\Models\PortfolioCategory;
use App\Services\AuditLogger;
use App\Services\PortfolioStatusTransitionService;
use App\Services\TridharmaExportService;
use App\Support\IndonesianDateFormatter;
use App\Support\PortfolioUi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class PortfolioActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredIndexQuery($request);

        $perPage = in_array($request->integer('per_page'), [10, 25, 50], true) ? $request->integer('per_page') : 10;
        $activities = $query->paginate($perPage)->withQueryString();
        $grouping = $this->grouping($request);

        return view('portfolio.index', [
            'activities' => $activities,
            'categories' => PortfolioCategory::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'sortOptions' => $this->sortOptions(),
            'currentSort' => $this->sortKey($request),
            'currentDirection' => $this->sortDirection($request),
            'viewMode' => $this->viewMode($request),
            'grouping' => $grouping,
            'groupedActivities' => $this->groupActivities($activities->getCollection(), $grouping),
        ]);
    }

    public function export(Request $request, TridharmaExportService $exporter)
    {
        $format = $request->string('format')->toString() === 'xls' ? 'xls' : 'csv';
        $filename = 'portofolio-dosen-'.now()->format('Ymd-His').'.'.$format;
        $rows = $this->filteredIndexQuery($request)->limit(1000)->get();

        return Response::streamDownload(function () use ($exporter, $rows, $format): void {
            $exporter->stream($rows, 'portfolio', $format);
        }, $filename, ['Content-Type' => $format === 'xls' ? 'application/vnd.ms-excel; charset=UTF-8' : 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        return view('portfolio.create', ['categories' => PortfolioCategory::query()->where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:portfolio_categories,id'],
            'activity_type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lecturer_role' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', 'in:PRIVATE,INTERNAL,PUBLIC'],
        ]);

        $activity = PortfolioActivity::query()->create([
            ...$data,
            'lecturer_core_id' => (string) $request->user()->core_lecturer_id,
            'verification_status' => 'DRAFT',
            'source_type' => 'MANUAL',
            'created_by_core_user_id' => (string) $request->user()->core_user_id,
        ]);

        $audit->record('portfolio.created', $request->user(), $activity, [], $request);

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function show(Request $request, PortfolioActivity $activity)
    {
        Gate::authorize('view', $activity);

        return view('portfolio.show', ['activity' => $activity->load(['category', 'documents', 'histories.actor', 'participants', 'tags', 'issueReports'])]);
    }

    public function edit(Request $request, PortfolioActivity $activity)
    {
        Gate::authorize('update', $activity);

        return view('portfolio.edit', [
            'activity' => $activity,
            'categories' => PortfolioCategory::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, PortfolioActivity $activity, AuditLogger $audit)
    {
        if ($request->user()->can('update', $activity)) {
            $data = $this->activityData($request);
        } else {
            Gate::authorize('managePersonalData', $activity);
            $officialKeys = [
                'category_id',
                'activity_type',
                'title',
                'description',
                'lecturer_role',
                'academic_year',
                'semester',
                'start_date',
                'end_date',
                'institution_name',
                'location',
            ];

            if ($request->collect()->keys()->intersect($officialKeys)->isNotEmpty()) {
                abort(403, 'Field resmi tidak dapat diubah pada status ini.');
            }

            $data = $request->validate([
                'personal_notes' => ['nullable', 'string'],
                'visibility' => ['required', 'in:PRIVATE,INTERNAL,PUBLIC'],
            ]);
        }

        $activity->update($data);
        $audit->record('portfolio.updated', $request->user(), $activity, ['fields' => array_keys($data)], $request);

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function submit(Request $request, PortfolioActivity $activity, PortfolioStatusTransitionService $transitions)
    {
        $transitions->transition($activity, 'SUBMITTED', $request->user(), notes: 'Diajukan oleh dosen.');

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function verify(Request $request, PortfolioActivity $activity, PortfolioStatusTransitionService $transitions)
    {
        Gate::authorize('verify', $activity);
        $transitions->transition($activity, 'ADMIN_VERIFIED', $request->user(), notes: $request->string('notes')->toString() ?: null);

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function requestRevision(Request $request, PortfolioActivity $activity, PortfolioStatusTransitionService $transitions)
    {
        Gate::authorize('verify', $activity);
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $transitions->transition($activity, 'REVISION_REQUIRED', $request->user(), $data['reason']);

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function reject(Request $request, PortfolioActivity $activity, PortfolioStatusTransitionService $transitions)
    {
        Gate::authorize('verify', $activity);
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $transitions->transition($activity, 'REJECTED', $request->user(), $data['reason']);

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function archive(Request $request, PortfolioActivity $activity, PortfolioStatusTransitionService $transitions)
    {
        Gate::authorize('archive', $activity);
        $transitions->transition($activity, 'ARCHIVED', $request->user(), notes: 'Diarsipkan admin.');

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function destroy(Request $request, PortfolioActivity $activity, AuditLogger $audit)
    {
        Gate::authorize('delete', $activity);

        $activity->delete();
        $audit->record('portfolio.deleted', $request->user(), $activity, [], $request);

        return redirect()->route('dosen.portfolio.index');
    }

    private function activityData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:portfolio_categories,id'],
            'activity_type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'personal_notes' => ['nullable', 'string'],
            'lecturer_role' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', 'in:PRIVATE,INTERNAL,PUBLIC'],
        ]);
    }

    private function filteredIndexQuery(Request $request)
    {
        $query = PortfolioActivity::query()
            ->with(['category', 'documents', 'tags'])
            ->withCount('documents');

        if (! $request->user()->isAdmin()) {
            $query->where('lecturer_core_id', $request->user()->core_lecturer_id);
        }

        $query
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('verification_status', $request->string('status')))
            ->when($request->filled('academic_year'), fn ($query) => $query->where('academic_year', $request->string('academic_year')))
            ->when($request->filled('semester'), fn ($query) => $query->where('semester', $request->string('semester')))
            ->when($request->filled('source_type'), fn ($query) => $query->where('source_type', $request->string('source_type')))
            ->when($request->filled('role'), fn ($query) => $query->where('lecturer_role', 'like', '%'.$request->string('role')->trim().'%'))
            ->when($request->filled('visibility'), fn ($query) => $query->where('visibility', $request->string('visibility')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('start_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('start_date', '<=', $request->date('date_to')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn ($query) => $query->where('title', 'like', $term)->orWhere('description', 'like', $term));
            });

        return $this->applySorting($query, $request);
    }

    private function applySorting($query, Request $request)
    {
        $sort = $this->sortKey($request);
        $direction = $this->sortDirection($request);

        return match ($sort) {
            'title' => $query->orderBy('title', $direction),
            'status' => $query->orderBy('verification_status', $direction)->orderByDesc('updated_at'),
            'type' => $query->orderBy('activity_type', $direction)->orderByDesc('updated_at'),
            'updated' => $query->orderBy('updated_at', $direction),
            default => $query
                ->orderByRaw('CASE WHEN start_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('start_date', $direction)
                ->orderBy('updated_at', $direction),
        };
    }

    private function sortOptions(): array
    {
        return [
            'date:desc' => 'Terbaru',
            'date:asc' => 'Terlama',
            'title:asc' => 'Judul A-Z',
            'title:desc' => 'Judul Z-A',
            'status:asc' => 'Status',
            'type:asc' => 'Jenis Kegiatan',
            'updated:desc' => 'Terakhir Diperbarui',
        ];
    }

    private function sortKey(Request $request): string
    {
        return in_array($request->string('sort')->toString(), ['date', 'title', 'status', 'type', 'updated'], true)
            ? $request->string('sort')->toString()
            : 'date';
    }

    private function sortDirection(Request $request): string
    {
        return $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
    }

    private function grouping(Request $request): string
    {
        return in_array($request->string('group')->toString(), ['none', 'year', 'month', 'status', 'type'], true)
            ? $request->string('group')->toString()
            : 'none';
    }

    private function viewMode(Request $request): string
    {
        return in_array($request->string('view')->toString(), ['table', 'card'], true)
            ? $request->string('view')->toString()
            : 'auto';
    }

    private function groupActivities($activities, string $grouping)
    {
        if ($grouping === 'none') {
            return collect(['' => $activities]);
        }

        return $activities->groupBy(function (PortfolioActivity $activity) use ($grouping): string {
            $date = PortfolioUi::activityDate($activity);

            return match ($grouping) {
                'year' => $date ? $date->format('Y') : 'Tanpa tahun',
                'month' => $date ? IndonesianDateFormatter::date($date->startOfMonth()) : 'Tanpa bulan',
                'status' => PortfolioUi::statusLabel($activity->verification_status),
                'type' => PortfolioUi::typeLabel($activity->activity_type),
            };
        });
    }
}
