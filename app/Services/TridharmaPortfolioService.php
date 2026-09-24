<?php

namespace App\Services;

use App\Models\PortfolioActivity;
use App\Models\PortfolioCategory;
use App\Support\IndonesianDateFormatter;
use App\Support\PortfolioUi;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TridharmaPortfolioService
{
    public function domains(): array
    {
        return [
            'pendidikan' => [
                'label' => 'Pendidikan dan Pengajaran',
                'short_label' => 'Pendidikan',
                'category_slug' => 'pendidikan-dan-pengajaran',
                'description' => 'Kelola pengajaran, pembimbingan, pengujian, praktikum, dan pengembangan bahan ajar.',
                'subcategories' => [
                    'perkuliahan',
                    'koordinator mata kuliah',
                    'persiapan pembelajaran',
                    'pembimbing akademik',
                    'pembimbing tugas akhir',
                    'penguji tugas akhir',
                    'pembimbing KP',
                    'penguji KP',
                    'pembimbing KP PSPA',
                    'penguji KP PSPA',
                    'praktikum',
                    'bahan ajar',
                    'dosen tamu',
                    'pembinaan mahasiswa',
                ],
            ],
            'penelitian' => [
                'label' => 'Penelitian dan Pengembangan',
                'short_label' => 'Penelitian',
                'category_slug' => 'penelitian-dan-pengembangan',
                'description' => 'Kelola penelitian, publikasi, hibah, HKI, dan luaran ilmiah.',
                'subcategories' => [
                    'penelitian',
                    'publikasi jurnal',
                    'prosiding',
                    'buku',
                    'bab buku',
                    'HKI/paten',
                    'produk/prototipe',
                    'dataset',
                    'seminar ilmiah',
                    'reviewer/editor',
                    'hibah',
                    'kolaborasi',
                ],
            ],
            'pengabdian' => [
                'label' => 'Pengabdian kepada Masyarakat',
                'short_label' => 'Pengabdian',
                'category_slug' => 'pengabdian-kepada-masyarakat',
                'description' => 'Kelola kegiatan pengabdian, mitra, kelompok sasaran, lokasi, luaran, dan bukti pelaksanaan.',
                'subcategories' => [
                    'kegiatan pengabdian',
                    'pemberdayaan',
                    'pelatihan masyarakat',
                    'konsultasi',
                    'hibah',
                    'mitra',
                    'luaran',
                    'publikasi',
                    'dokumentasi/laporan',
                ],
            ],
        ];
    }

    public function domain(?string $domain): ?array
    {
        return $this->domains()[$domain] ?? null;
    }

    public function summaries(string $lecturerCoreId, array $filters = []): array
    {
        return collect($this->domains())
            ->map(fn (array $domain, string $key): array => $this->summary($lecturerCoreId, $key, $filters))
            ->all();
    }

    public function summary(string $lecturerCoreId, string $domainKey, array $filters = []): array
    {
        $domain = $this->domain($domainKey);
        abort_if(! $domain, 404);

        $category = PortfolioCategory::query()->where('slug', $domain['category_slug'])->first();
        $filteredQuery = $this->activityQuery(new Request($filters), $lecturerCoreId, $domainKey);

        $latest = (clone $filteredQuery)->latest()->limit(6)->get();
        $verifiedStatuses = ['ADMIN_VERIFIED', 'SYSTEM_VERIFIED'];

        return [
            'key' => $domainKey,
            ...$domain,
            'category' => $category,
            'total' => (clone $filteredQuery)->count(),
            'verified' => (clone $filteredQuery)->whereIn('verification_status', $verifiedStatuses)->count(),
            'needs_completion' => (clone $filteredQuery)->whereIn('verification_status', ['DRAFT', 'REVISION_REQUIRED'])->count(),
            'system_count' => (clone $filteredQuery)->where('source_type', 'SYSTEM')->count(),
            'manual_count' => (clone $filteredQuery)->where('source_type', 'MANUAL')->count(),
            'latest' => $latest,
            'needs_items' => (clone $filteredQuery)->whereIn('verification_status', ['DRAFT', 'REVISION_REQUIRED'])->latest()->limit(5)->get(),
            'system_items' => (clone $filteredQuery)->where('source_type', 'SYSTEM')->latest()->limit(5)->get(),
            'manual_items' => (clone $filteredQuery)->where('source_type', 'MANUAL')->latest()->limit(5)->get(),
            'yearly' => $this->yearlySummary($filteredQuery),
            'active_period' => $this->activePeriod($latest),
        ];
    }

    public function filterOptions(string $lecturerCoreId): array
    {
        $query = PortfolioActivity::query()->where('lecturer_core_id', $lecturerCoreId);

        return [
            'years' => (clone $query)->whereNotNull('academic_year')->distinct()->orderByDesc('academic_year')->pluck('academic_year'),
            'semesters' => (clone $query)->whereNotNull('semester')->distinct()->orderBy('semester')->pluck('semester'),
            'statuses' => (clone $query)->whereNotNull('verification_status')->distinct()->orderBy('verification_status')->pluck('verification_status'),
            'sources' => (clone $query)->whereNotNull('source_type')->distinct()->orderBy('source_type')->pluck('source_type'),
        ];
    }

    public function activities(Request $request, string $lecturerCoreId, string $domainKey)
    {
        $perPage = in_array($request->integer('per_page'), [10, 25, 50], true) ? $request->integer('per_page') : 10;

        return $this->sortedActivityQuery($request, $lecturerCoreId, $domainKey)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function sortedActivityQuery(Request $request, string $lecturerCoreId, string $domainKey)
    {
        return $this->applySorting($this->activityQuery($request, $lecturerCoreId, $domainKey), $request);
    }

    public function activityQuery(Request $request, string $lecturerCoreId, string $domainKey)
    {
        $domain = $this->domain($domainKey);
        abort_if(! $domain, 404);

        $category = PortfolioCategory::query()->where('slug', $domain['category_slug'])->first();

        return PortfolioActivity::query()
            ->with(['category', 'documents', 'tags'])
            ->withCount('documents')
            ->where('lecturer_core_id', $lecturerCoreId)
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when(! $category, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn ($query) => $query->where('title', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->when($request->filled('year'), fn ($query) => $query->where('academic_year', $request->string('year')))
            ->when($request->filled('semester'), fn ($query) => $query->where('semester', $request->string('semester')))
            ->when($request->filled('status'), fn ($query) => $query->where('verification_status', $request->string('status')))
            ->when($request->filled('source'), fn ($query) => $query->where('source_type', $request->string('source')))
            ->when($request->filled('subcategory'), fn ($query) => $query->where('activity_type', Str::slug($request->string('subcategory'))))
            ->when($request->filled('role'), fn ($query) => $query->where('lecturer_role', 'like', '%'.$request->string('role')->trim().'%'))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('start_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('start_date', '<=', $request->date('date_to')));
    }

    public function sortOptions(): array
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

    public function sortKey(Request $request): string
    {
        return in_array($request->string('sort')->toString(), ['date', 'title', 'status', 'type', 'updated'], true)
            ? $request->string('sort')->toString()
            : 'date';
    }

    public function sortDirection(Request $request): string
    {
        return $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
    }

    public function grouping(Request $request): string
    {
        return in_array($request->string('group')->toString(), ['none', 'year', 'month', 'status', 'type'], true)
            ? $request->string('group')->toString()
            : 'none';
    }

    public function viewMode(Request $request): string
    {
        return in_array($request->string('view')->toString(), ['table', 'card'], true)
            ? $request->string('view')->toString()
            : 'auto';
    }

    public function tableKind(?string $domain, Request $request): string
    {
        return PortfolioUi::tableKind($domain, $request->string('subcategory')->toString());
    }

    public function groupActivities(Collection $activities, string $grouping, ?string $domain = null): Collection
    {
        if ($grouping === 'none') {
            return collect(['' => $activities]);
        }

        return $activities->groupBy(function (PortfolioActivity $activity) use ($domain, $grouping): string {
            $date = PortfolioUi::activityDate($activity);

            return match ($grouping) {
                'year' => $date ? $date->format('Y') : 'Tanpa tahun',
                'month' => $date ? IndonesianDateFormatter::date($date->copy()->startOfMonth()) : 'Tanpa bulan',
                'status' => PortfolioUi::statusLabel($activity->verification_status),
                'type' => PortfolioUi::typeLabel($activity->activity_type, $domain),
            };
        });
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

    private function yearlySummary($baseQuery): Collection
    {
        return (clone $baseQuery)
            ->select([])
            ->selectRaw("coalesce(academic_year, 'Tanpa tahun') as year_label, count(*) as aggregate")
            ->groupBy('year_label')
            ->orderByDesc('year_label')
            ->limit(6)
            ->pluck('aggregate', 'year_label');
    }

    private function activePeriod(Collection $latest): string
    {
        $activity = $latest->first();

        if (! $activity) {
            return now()->year.'/'.(now()->year + 1).' - Aktif';
        }

        return trim(($activity->academic_year ?: now()->year.'/'.(now()->year + 1)).' '.($activity->semester ?: ''));
    }
}
