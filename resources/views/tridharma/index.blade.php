@extends('layouts.app', ['title' => 'Portofolio Tridharma'])

@section('breadcrumb', $activeDomain ? 'Tridharma - '.$domains[$activeDomain]['short_label'] : 'Portofolio Tridharma')

@php
    $summaryCollection = collect($summaries);
    $yearly = collect();
    foreach ($summaries as $domainSummary) {
        foreach ($domainSummary['yearly'] as $year => $count) {
            $yearly[$year] = ($yearly[$year] ?? 0) + $count;
        }
    }
    $yearly = $yearly->sortKeysDesc()->take(6);
    $activeSummary = $activeDomain ? $summaries[$activeDomain] : null;
    $total = $summaryCollection->sum('total');
    $verified = $summaryCollection->sum('verified');
    $needs = $summaryCollection->sum('needs_completion');
    $systemCount = $summaryCollection->sum('system_count');
    $manualCount = $summaryCollection->sum('manual_count');
    $progress = $total > 0 ? (int) round(($verified / max(1, $total)) * 100) : 0;
    $hasData = $total > 0;
    $activeFilters = collect($filters)->filter(fn ($value) => filled($value));
    $sources = [
        ['label' => 'KP', 'status' => 'Siap menerima kegiatan'],
        ['label' => 'TA', 'status' => 'Siap menerima kegiatan'],
        ['label' => 'TU', 'status' => 'Siap menerima dokumen'],
        ['label' => 'KP PSPA', 'status' => 'Menunggu pilot'],
        ['label' => 'Lab', 'status' => 'Menunggu pilot'],
    ];
    $domainDescriptions = [
        'pendidikan' => 'Kelola pengajaran, pembimbingan, pengujian, praktikum, dan pengembangan bahan ajar.',
        'penelitian' => 'Kelola penelitian, publikasi, hibah, HKI, dan luaran ilmiah.',
        'pengabdian' => 'Kelola kegiatan pengabdian, mitra, kelompok sasaran, lokasi, luaran, dan bukti pelaksanaan.',
    ];
    $compactSubcategories = [
        'pendidikan' => ['Semua' => null, 'Pengajaran' => 'perkuliahan', 'Pembimbingan' => 'pembimbing-tugas-akhir', 'Pengujian' => 'penguji-tugas-akhir', 'Praktikum' => 'praktikum', 'Lainnya' => 'bahan-ajar'],
        'penelitian' => ['Semua' => null, 'Penelitian' => 'penelitian', 'Publikasi' => 'publikasi-jurnal', 'HKI' => 'hki-paten', 'Buku' => 'buku', 'Hibah' => 'hibah', 'Lainnya' => 'kolaborasi'],
        'pengabdian' => ['Semua' => null, 'Kegiatan' => 'kegiatan-pengabdian', 'Mitra' => 'mitra', 'Luaran' => 'luaran', 'Publikasi' => 'publikasi', 'Lainnya' => 'dokumentasi-laporan'],
    ];
    $sortValue = $currentSort.':'.$currentDirection;
    $queryWithoutView = request()->except(['view', 'page']);
    $domainRoute = fn (array $query = []) => $activeDomain
        ? route('tridharma.domain', array_merge(['domain' => $activeDomain], $query))
        : route('tridharma.index', $query);
@endphp

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Portofolio Tridharma"
        title="{{ $activeDomain ? $domains[$activeDomain]['label'] : 'Portofolio Tridharma' }}"
        description="{{ $activeDomain ? $domainDescriptions[$activeDomain] : 'Pendidikan, penelitian, dan pengabdian Anda.' }}"
        :compact="(bool) $activeDomain"
    >
        <x-slot:actions>
            <x-ui.filter-chip>{{ now()->year }}/{{ now()->year + 1 }}</x-ui.filter-chip>
            <x-ui.filter-chip>{{ $total }} kegiatan</x-ui.filter-chip>
            <x-ui.filter-chip>{{ $progress }}% lengkap</x-ui.filter-chip>
            @if($activeDomain)
                <x-ui.button :href="route('dosen.portfolio.create', ['domain' => $activeDomain])">Tambah Kegiatan</x-ui.button>
            @else
                <x-ui.filter-chip>{{ $systemCount }} otomatis</x-ui.filter-chip>
                <x-ui.filter-chip>{{ $manualCount }} manual</x-ui.filter-chip>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <nav class="df-segmented" aria-label="Navigasi Tridharma">
        <a href="{{ route('tridharma.index') }}" class="shrink-0 rounded-[var(--radius-sm)] px-4 py-2.5 text-sm font-extrabold {{ $activeDomain ? 'text-[var(--text-secondary)] hover:bg-white' : 'bg-[var(--brand-900)] text-white shadow-sm' }}">Ringkasan</a>
        @foreach($domains as $key => $domain)
            <a href="{{ route('tridharma.domain', $key) }}" class="shrink-0 rounded-[var(--radius-sm)] px-4 py-2.5 text-sm font-extrabold {{ $activeDomain === $key ? 'bg-[var(--brand-900)] text-white shadow-sm' : 'text-[var(--text-secondary)] hover:bg-white' }}">{{ $domain['short_label'] }}</a>
        @endforeach
    </nav>

    @if($activeDomain)
        <nav class="scrollbar-none flex gap-2 overflow-x-auto" aria-label="Subkategori {{ $domains[$activeDomain]['short_label'] }}">
            @foreach($compactSubcategories[$activeDomain] as $label => $slug)
                <x-ui.filter-chip :href="$slug ? route('tridharma.domain', [$activeDomain, 'subcategory' => $slug]) : route('tridharma.domain', $activeDomain)">{{ $label }}</x-ui.filter-chip>
            @endforeach
        </nav>
    @endif

    <section class="df-card p-4 sm:p-5">
        <form id="tridharma-filter-form" method="get" class="space-y-4">
            <input type="hidden" name="sort" value="{{ $currentSort }}">
            <input type="hidden" name="direction" value="{{ $currentDirection }}">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            <div class="df-list-toolbar">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"><x-ui.icon name="search" class="h-4 w-4" /></span>
                    <input name="q" value="{{ request('q') }}" aria-label="Cari kegiatan" placeholder="Cari kegiatan, peran, atau bukti" class="df-field pl-10">
                </label>
                <div>
                    <button type="button" data-dialog-open="tridharma-filters" aria-haspopup="dialog" class="df-button df-button-secondary"><x-heroicon-o-adjustments-horizontal class="h-4 w-4" />Filter{{ $activeFilters->count() ? ' ('.$activeFilters->count().')' : '' }}</button>
                    <dialog id="tridharma-filters" class="df-filter-dialog" aria-labelledby="tridharma-filter-title">
                        <div class="df-dialog-heading"><h2 id="tridharma-filter-title" class="text-lg font-bold">Filter Tridharma</h2><button type="button" class="df-menu-button" data-dialog-close aria-label="Tutup filter"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
                        <div class="grid gap-3 sm:grid-cols-2">
                <select name="year" class="df-field">
                    <option value="">Semua tahun</option>
                    @foreach($filterOptions['years'] as $year)
                        <option value="{{ $year }}" @selected(($filters['year'] ?? '') === $year)>{{ $year }}</option>
                    @endforeach
                </select>
                <select name="semester" class="df-field">
                    <option value="">Semua semester</option>
                    @foreach($filterOptions['semesters'] as $semester)
                        <option value="{{ $semester }}" @selected(($filters['semester'] ?? '') === $semester)>{{ $semester }}</option>
                    @endforeach
                </select>
                <select name="status" class="df-field">
                    <option value="">Semua status</option>
                    @foreach($filterOptions['statuses'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \App\Support\PortfolioUi::statusLabel($status) }}</option>
                    @endforeach
                </select>
                <select name="source" class="df-field">
                    <option value="">Semua sumber</option>
                    @foreach($filterOptions['sources'] as $source)
                        <option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>{{ $source }}</option>
                    @endforeach
                </select>
                @if($activeDomain)
                    <select name="subcategory" class="df-field">
                        <option value="">Semua subkategori</option>
                        @foreach($subcategories as $slug => $label)
                            <option value="{{ $slug }}" @selected(($filters['subcategory'] ?? '') === $slug)>{{ str($label)->title() }}</option>
                        @endforeach
                    </select>
                @endif
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="df-field" aria-label="Tanggal mulai">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="df-field" aria-label="Tanggal akhir">
                            <input name="role" value="{{ request('role') }}" placeholder="Peran" class="df-field sm:col-span-2">
                        </div>
                <div class="mt-4 grid grid-cols-2 gap-2 bg-white/90 py-2 backdrop-blur">
                    <a href="{{ $activeDomain ? route('tridharma.domain', $activeDomain) : route('tridharma.index') }}" class="df-button df-button-secondary">Reset</a>
                    <button class="df-button df-button-primary">Terapkan</button>
                </div>
                    </dialog>
                </div>
                @if($activeDomain)
                <select class="df-field w-full lg:w-48" aria-label="Urutkan" onchange="const [sort,direction]=this.value.split(':'); this.form.sort.value=sort; this.form.direction.value=direction; this.form.submit();">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($sortValue === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="group" class="df-field w-full lg:w-48" aria-label="Pengelompokan" onchange="this.form.submit()">
                    <option value="none" @selected($grouping === 'none')>Tanpa Pengelompokan</option>
                    <option value="year" @selected($grouping === 'year')>Per Tahun</option>
                    <option value="month" @selected($grouping === 'month')>Per Bulan</option>
                    <option value="status" @selected($grouping === 'status')>Per Status</option>
                    <option value="type" @selected($grouping === 'type')>Per Jenis</option>
                </select>
                <x-ui.view-switch :mode="$viewMode" :table-url="$domainRoute(array_merge($queryWithoutView, ['view' => 'table']))" :card-url="$domainRoute(array_merge($queryWithoutView, ['view' => 'card']))" />
                @endif
                @if($activeDomain)
                    <div class="flex gap-2">
                        <a href="{{ route('tridharma.domain.export', array_merge(['domain' => $activeDomain], request()->query(), ['format' => 'csv'])) }}" class="df-button df-button-secondary">CSV</a>
                        <a href="{{ route('tridharma.domain.export', array_merge(['domain' => $activeDomain], request()->query(), ['format' => 'xls'])) }}" class="df-button df-button-secondary">Excel</a>
                    </div>
                @endif
            </div>
        </form>

        @if($activeFilters->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($activeFilters as $key => $value)
                    <x-ui.filter-chip :href="$domainRoute(request()->except([$key, 'page']))" aria-label="Hapus filter {{ \App\Support\PortfolioUi::filterLabel($key) }}">{{ \App\Support\PortfolioUi::filterLabel($key) }}: {{ $key === 'subcategory' ? ($subcategories[$value] ?? \App\Support\PortfolioUi::filterValue($key, $value)) : \App\Support\PortfolioUi::filterValue($key, $value) }} <x-heroicon-o-x-mark class="h-3.5 w-3.5" /></x-ui.filter-chip>
                @endforeach
            </div>
        @endif
    </section>

    @if(! $hasData && ! $activeDomain)
        <x-ui.empty-state
            title="Belum ada kegiatan pada periode ini"
            description="Kegiatan dari KP, TA, TU, KP PSPA, dan Lab akan muncul otomatis setelah diselesaikan. Anda juga dapat menambahkan kegiatan manual untuk melengkapi portofolio."
            class="bg-white"
        >
            <x-slot:actions>
                <x-ui.button :href="route('dosen.portfolio.create')">Tambah Kegiatan Manual</x-ui.button>
                <x-ui.button :href="route('dosen.portfolio.index')" variant="secondary">Lihat Portofolio</x-ui.button>
            </x-slot:actions>
        </x-ui.empty-state>
    @endif

    @if($activeDomain)
        <section class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3 text-sm font-semibold text-[var(--text-secondary)]">
                <p>Menampilkan {{ $activities->firstItem() ?? 0 }}-{{ $activities->lastItem() ?? 0 }} dari {{ $activities->total() }} kegiatan.</p>
                @if($grouping !== 'none')
                    <p>Pengelompokan visual diterapkan pada hasil halaman aktif.</p>
                @endif
            </div>
            <x-academic.activity-results :activities="$activities" :grouped-activities="$groupedActivities" :domain="$activeDomain" :view-mode="$viewMode" :table-kind="$tableKind" />
            <div>{{ $activities->links() }}</div>
        </section>
    @endif

    @unless($activeDomain)
        <section class="grid gap-5 xl:grid-cols-3">
            @foreach($summaries as $summary)
                <x-academic.tridharma-domain-card :summary="$summary" />
            @endforeach
        </section>
    @endunless

    @unless($activeDomain)
        <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header eyebrow="Insight" title="Perlu Perhatian" description="Maksimal lima item yang perlu dilengkapi atau diperiksa." />
                <div class="mt-5 space-y-3">
                    @php($attentionItems = $summaryCollection->flatMap(fn ($summary) => $summary['needs_items'])->take(5))
                    @forelse($attentionItems as $activity)
                        <x-academic.activity-item :activity="$activity" />
                    @empty
                        <x-ui.empty-state title="Tidak ada kegiatan yang perlu tindakan" description="Portofolio pada filter ini tidak memiliki draft atau revisi tertunda." />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Sumber Otomatis" description="Aplikasi yang dapat mengirim kegiatan akademik." />
                <div class="mt-5 space-y-3">
                    @foreach($sources as $source)
                        <div class="flex items-center justify-between gap-3 rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-3">
                            <div>
                                <p class="font-black text-[var(--text-primary)]">{{ $source['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Aktivitas Terbaru" description="Timeline kegiatan dari sumber manual maupun otomatis." />
                <div class="mt-5 space-y-3">
                    @php($latestItems = $summaryCollection->flatMap(fn ($summary) => $summary['latest'])->sortByDesc('created_at')->take(6))
                    @forelse($latestItems as $activity)
                        <x-academic.activity-item :activity="$activity" />
                    @empty
                        <x-ui.empty-state title="Belum ada aktivitas terbaru" description="Timeline akan terisi setelah kegiatan pertama tercatat." />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Ringkasan Tahun Akademik" description="Distribusi kegiatan berdasarkan tahun agar mudah dibandingkan." />
                <div class="mt-5 space-y-3">
                    @forelse($yearly as $year => $count)
                        @php($width = $total > 0 ? min(100, (int) round(($count / max(1, $total)) * 100)) : 0)
                        <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                            <div class="flex items-center justify-between gap-4 text-sm font-bold">
                                <span class="text-[var(--text-primary)]">{{ $year }}</span>
                                <span class="text-[var(--text-secondary)]">{{ $count }} kegiatan</span>
                            </div>
                            <div class="df-progress-track mt-3 h-2">
                                <div class="df-progress-fill bg-[var(--brand-600)]" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="Belum ada ringkasan tahun" description="Ringkasan akan tersedia setelah kegiatan memiliki tahun akademik." />
                    @endforelse
                </div>
            </x-ui.card>
        </section>
    @endunless
</div>
@endsection
