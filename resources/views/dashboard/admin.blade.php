@extends('layouts.app', ['title' => 'Dashboard Admin'])

@section('content')
<h1 class="text-2xl font-semibold">Dashboard Admin</h1>
<p class="mt-1 text-slate-600">Ringkasan validasi portofolio dan operasional dosen.</p>

<div class="mt-5 flex flex-col gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="font-semibold text-blue-950">Panel administrasi lengkap</h2>
        <p class="mt-1 text-sm text-blue-800">Kelola klien integrasi, event, audit, portofolio, dokumen, dan data operasional dosen.</p>
    </div>
    <a
        href="{{ route('filament.admin.resources.integration-clients.index') }}"
        class="inline-flex shrink-0 items-center justify-center rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
    >
        Buka Panel Administrasi
    </a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach([
        'Dosen lokal' => $lecturerCount,
        'Aktivitas' => $activityCount,
        'Menunggu verifikasi' => $pendingVerificationCount,
        'Perlu ditinjau' => $revisionCount,
        'Issue report terbuka' => $openIssueReportCount,
        'System verified' => $systemVerifiedCount,
        'Dokumen' => $documentCount,
        'Event gagal' => $failedEventCount,
    ] as $label => $value)
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-semibold">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <h2 class="font-semibold">Menunggu verifikasi</h2>
        <div class="mt-3 space-y-2">
            @forelse($pendingActivities as $activity)
                <a class="block text-sm text-blue-700" href="{{ route('dosen.portfolio.show', $activity) }}">{{ $activity->title }} - {{ $activity->category?->name ?? 'Tanpa kategori' }}</a>
            @empty
                <p class="text-sm text-slate-500">Tidak ada aktivitas menunggu.</p>
            @endforelse
        </div>
    </section>
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <h2 class="font-semibold">Aktivitas per sumber</h2>
        <div class="mt-3 space-y-2">
            @forelse($activitiesBySource as $source => $count)
                <p class="text-sm">{{ $source ?: 'Tidak diketahui' }}: <span class="font-medium">{{ $count }}</span></p>
            @empty
                <p class="text-sm text-slate-500">Belum ada data.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
