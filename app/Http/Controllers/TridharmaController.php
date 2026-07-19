<?php

namespace App\Http\Controllers;

use App\Models\PortfolioActivity;
use App\Services\TridharmaExportService;
use App\Services\TridharmaPortfolioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class TridharmaController extends Controller
{
    public function index(Request $request, TridharmaPortfolioService $tridharma, ?string $domain = null)
    {
        abort_if($domain && ! $tridharma->domain($domain), 404);

        $filters = $request->only(['q', 'year', 'semester', 'status', 'source', 'subcategory', 'date_from', 'date_to', 'role']);
        $lecturerId = (string) $request->user()->core_lecturer_id;
        $activities = $domain ? $tridharma->activities($request, $lecturerId, $domain) : null;

        return view('tridharma.index', [
            'domains' => $tridharma->domains(),
            'activeDomain' => $domain,
            'summaries' => $domain
                ? [$domain => $tridharma->summary($lecturerId, $domain, $filters)]
                : $tridharma->summaries($lecturerId, $filters),
            'filterOptions' => $tridharma->filterOptions($lecturerId),
            'filters' => $filters,
            'subcategories' => $domain ? collect($tridharma->domain($domain)['subcategories'])->mapWithKeys(fn ($label) => [Str::slug($label) => $label]) : collect(),
            'activities' => $activities,
            'sortOptions' => $tridharma->sortOptions(),
            'currentSort' => $tridharma->sortKey($request),
            'currentDirection' => $tridharma->sortDirection($request),
            'viewMode' => $tridharma->viewMode($request),
            'grouping' => $tridharma->grouping($request),
            'tableKind' => $tridharma->tableKind($domain, $request),
            'groupedActivities' => $activities ? $tridharma->groupActivities($activities->getCollection(), $tridharma->grouping($request), $domain) : collect(),
        ]);
    }

    public function export(Request $request, TridharmaPortfolioService $tridharma, TridharmaExportService $exporter, string $domain)
    {
        abort_if(! $tridharma->domain($domain), 404);

        $format = $request->string('format')->toString() === 'xls' ? 'xls' : 'csv';
        $rows = $tridharma
            ->sortedActivityQuery($request, (string) $request->user()->core_lecturer_id, $domain)
            ->limit(1000)
            ->get();
        $filename = 'tridharma-'.$domain.'-'.now()->format('Ymd-His').'.'.$format;

        return Response::streamDownload(function () use ($exporter, $rows, $tridharma, $request, $domain, $format): void {
            $exporter->stream($rows, $tridharma->tableKind($domain, $request), $format);
        }, $filename, ['Content-Type' => $format === 'xls' ? 'application/vnd.ms-excel; charset=UTF-8' : 'text/csv; charset=UTF-8']);
    }

    public function create(Request $request, string $domain)
    {
        return redirect()->route('dosen.portfolio.create', ['domain' => $domain]);
    }

    public function show(Request $request, PortfolioActivity $activity)
    {
        abort_if((string) $activity->lecturer_core_id !== (string) $request->user()->core_lecturer_id && ! $request->user()->isAdmin(), 403);

        return redirect()->route('dosen.portfolio.show', $activity);
    }
}
