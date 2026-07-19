@extends('layouts.app', ['title' => 'Agenda'])

@section('breadcrumb', 'Agenda')

@php
    $eventCount = $events->total();
    $overlapCount = $events->getCollection()->filter(fn ($event) => $event->overlaps())->count();
    $todayCount = $events->getCollection()->filter(fn ($event) => optional($event->starts_at)->isToday())->count();
@endphp

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Agenda Akademik"
        title="Jadwal dan Tindak Lanjut"
        description="Pantau agenda mengajar, bimbingan, rapat, verifikasi, dan aktivitas akademik lainnya."
    />

    <section class="grid gap-4 md:grid-cols-3">
        <x-ui.stat label="Total Agenda" :value="$eventCount" tone="brand" />
        <x-ui.stat label="Hari Ini" :value="$todayCount" tone="info" />
        <x-ui.stat label="Bentrok" :value="$overlapCount" tone="warning" />
    </section>

    <section class="df-card p-5 sm:p-6">
        <x-ui.section-header title="Timeline Mendatang" description="Agenda diurutkan berdasarkan waktu mulai." />
        <div class="df-timeline mt-5 space-y-4">
            @forelse($events as $event)
                <article class="relative pl-10">
                    <span class="absolute left-[0.65rem] top-1 h-3 w-3 rounded-full bg-[var(--brand-600)] ring-4 ring-white"></span>
                    <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-bold text-[var(--text-primary)]">{{ $event->title }}</h2>
                                    <x-ui.badge tone="info">{{ \App\Support\AgendaUi::typeLabel($event->event_type) }}</x-ui.badge>
                                    @if($event->overlaps())
                                        <x-ui.badge tone="warning">Bentrok waktu</x-ui.badge>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm font-semibold text-[var(--text-secondary)]">{{ \App\Support\IndonesianDateFormatter::range($event->starts_at, $event->ends_at) }}</p>
                                <p class="mt-1 text-sm text-[var(--text-muted)]">{{ $event->location ?: 'Lokasi belum diisi' }}</p>
                            </div>
                            <x-ui.badge>{{ \App\Support\AgendaUi::statusLabel($event->status) }}</x-ui.badge>
                        </div>
                    </div>
                </article>
            @empty
                <x-ui.empty-state title="Belum ada agenda" description="Agenda dari aktivitas akademik dan integrasi akan tampil di sini." icon="agenda" />
            @endforelse
        </div>
    </section>

    <div>{{ $events->links() }}</div>
</div>
@endsection
