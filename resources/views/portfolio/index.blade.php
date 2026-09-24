@extends('layouts.app', ['title' => 'Portofolio Saya'])

@section('breadcrumb', 'Portofolio Saya')

@php
    $totalOnPage = $activities->total();
    $activeFilterKeys = ['q', 'category_id', 'status', 'academic_year', 'semester', 'source_type', 'visibility', 'date_from', 'date_to', 'role'];
    $activeFilterCount = collect(request()->only($activeFilterKeys))->filter(fn ($value) => filled($value))->count();
    $sortValue = $currentSort.':'.$currentDirection;
    $queryWithoutView = request()->except(['view', 'page']);
@endphp

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        title="Portofolio Saya"
        description="Kelola kegiatan akademik manual dan otomatis dalam satu daftar yang siap dilengkapi, diajukan, dan diverifikasi."
        :compact="true"
    >
        <x-slot:actions>
            <x-ui.button :href="route('dosen.portfolio.create')">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Tambah Kegiatan
            </x-ui.button>
            <x-ui.button :href="route('tridharma.index')" variant="secondary">Buka Tridharma</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="df-card p-4">
        <form method="get" class="space-y-4">
            <input type="hidden" name="sort" value="{{ $currentSort }}">
            <input type="hidden" name="direction" value="{{ $currentDirection }}">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            <input type="hidden" name="group" value="{{ $grouping }}">
            <div class="df-list-toolbar">
            <label class="relative block">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"><x-ui.icon name="search" class="h-4 w-4" /></span>
                <input name="q" value="{{ request('q') }}" aria-label="Cari kegiatan" placeholder="Cari kegiatan, peran, atau deskripsi" class="df-field pl-10">
            </label>
            <div>
                <button type="button" data-dialog-open="portfolio-filters" aria-haspopup="dialog" class="df-button df-button-secondary"><x-heroicon-o-adjustments-horizontal class="h-4 w-4" />Filter{{ $activeFilterCount ? ' ('.$activeFilterCount.')' : '' }}</button>
                <dialog id="portfolio-filters" class="df-filter-dialog" aria-labelledby="portfolio-filter-title">
                    <div class="df-dialog-heading"><h2 id="portfolio-filter-title" class="text-lg font-bold">Filter portofolio</h2><button type="button" class="df-menu-button" data-dialog-close aria-label="Tutup filter"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <select name="category_id" class="df-field">
                            <option value="">Semua kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="df-field">
                            <option value="">Semua status</option>
                            @foreach(['DRAFT','SUBMITTED','ADMIN_VERIFIED','SYSTEM_VERIFIED','REVISION_REQUIRED','REJECTED','CANCELLED','ARCHIVED'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Support\PortfolioUi::statusLabel($status) }}</option>
                            @endforeach
                        </select>
                        <input name="academic_year" value="{{ request('academic_year') }}" placeholder="Tahun akademik" class="df-field">
                        <input name="semester" value="{{ request('semester') }}" placeholder="Semester" class="df-field">
                        <select name="source_type" class="df-field">
                            <option value="">Semua sumber</option>
                            @foreach(['MANUAL', 'SYSTEM'] as $sourceType)
                                <option value="{{ $sourceType }}" @selected(request('source_type') === $sourceType)>{{ $sourceType === 'MANUAL' ? 'Input Mandiri' : 'Sistem Terhubung' }}</option>
                            @endforeach
                        </select>
                        <select name="visibility" class="df-field">
                            <option value="">Semua visibilitas</option>
                            @foreach(['PRIVATE','INTERNAL','PUBLIC'] as $visibility)
                                <option value="{{ $visibility }}" @selected(request('visibility') === $visibility)>{{ $visibility }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="df-field" aria-label="Tanggal mulai">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="df-field" aria-label="Tanggal akhir">
                        <input name="role" value="{{ request('role') }}" placeholder="Peran" class="df-field sm:col-span-2">
                    </div>
                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                        <a href="{{ route('dosen.portfolio.index') }}" class="df-button df-button-secondary">Reset</a>
                        <button class="df-button df-button-primary">Terapkan</button>
                    </div>
                </dialog>
            </div>
            <select class="df-field w-full lg:w-48" aria-label="Urutkan" onchange="const [sort,direction]=this.value.split(':'); this.form.sort.value=sort; this.form.direction.value=direction; this.form.submit();">
                @foreach($sortOptions as $value => $label)
                    <option value="{{ $value }}" @selected($sortValue === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="per_page" class="df-field w-full lg:w-32" aria-label="Jumlah per halaman" onchange="this.form.submit()">
                @foreach([10,25,50] as $size)
                    <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}/hal</option>
                @endforeach
            </select>
            <x-ui.view-switch :mode="$viewMode" :table-url="route('dosen.portfolio.index', [...$queryWithoutView, 'view' => 'table'])" :card-url="route('dosen.portfolio.index', [...$queryWithoutView, 'view' => 'card'])" />
            <div class="flex gap-2">
                <a href="{{ route('dosen.portfolio.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="df-button df-button-secondary">CSV</a>
                <a href="{{ route('dosen.portfolio.export', array_merge(request()->query(), ['format' => 'xls'])) }}" class="df-button df-button-secondary">Excel</a>
            </div>
            </div>
        </form>

        @if($activeFilterCount)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach(collect(request()->only($activeFilterKeys))->filter(fn ($value) => filled($value)) as $key => $value)
                    <x-ui.filter-chip :href="route('dosen.portfolio.index', request()->except([$key, 'page']))" aria-label="Hapus filter {{ \App\Support\PortfolioUi::filterLabel($key) }}">{{ \App\Support\PortfolioUi::filterLabel($key) }}: {{ $key === 'category_id' ? ($categories->firstWhere('id', $value)?->name ?? 'Kategori') : \App\Support\PortfolioUi::filterValue($key, $value) }} <x-heroicon-o-x-mark class="h-3.5 w-3.5" /></x-ui.filter-chip>
                @endforeach
            </div>
        @endif
    </section>

    <section class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm font-semibold text-[var(--text-secondary)]">
            <p>Menampilkan {{ $activities->firstItem() ?? 0 }}-{{ $activities->lastItem() ?? 0 }} dari {{ $activities->total() }} kegiatan.</p>
            @if($grouping !== 'none')
                <p>Pengelompokan visual diterapkan pada hasil halaman aktif.</p>
            @endif
        </div>

        <x-academic.activity-results :activities="$activities" :grouped-activities="$groupedActivities" :view-mode="$viewMode" />
    </section>

    <div>{{ $activities->links() }}</div>
</div>
@endsection
