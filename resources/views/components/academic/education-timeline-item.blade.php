@props([
    'education',
])

@php
    $statusTone = str_contains((string) $education->verification_status, 'VERIFIED') ? 'success' : (($education->verification_status === 'DRAFT') ? 'info' : 'warning');
@endphp

<div {{ $attributes->merge(['class' => 'relative rounded-[var(--radius-lg)] border border-[var(--border)] bg-white p-4 shadow-sm']) }}>
    <span class="absolute -left-[2.06rem] top-5 h-4 w-4 rounded-full border-4 border-white bg-[var(--brand-600)] shadow-sm"></span>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge tone="brand">{{ $education->level }}</x-ui.badge>
                <x-ui.badge :tone="$statusTone">{{ str($education->verification_status)->replace('_', ' ')->title() }}</x-ui.badge>
                <x-ui.badge tone="neutral">{{ $education->visibility }}</x-ui.badge>
            </div>
            <h3 class="mt-3 text-lg font-black text-[var(--text-primary)]">{{ $education->institution_name }}</h3>
            <p class="mt-1 text-sm font-semibold text-[var(--text-secondary)]">{{ $education->study_program ?: 'Program studi belum diisi' }} · {{ $education->degree ?: 'Gelar belum diisi' }}</p>
            @if($education->thesis_title)
                <p class="mt-3 text-sm leading-6 text-[var(--text-secondary)]">{{ $education->thesis_title }}</p>
            @endif
        </div>
        <p class="shrink-0 rounded-full bg-[var(--surface-muted)] px-3 py-1 text-xs font-extrabold text-[var(--text-secondary)]">
            {{ $education->start_year ?: 'Mulai' }} - {{ $education->end_year ?: 'Berjalan' }}
        </p>
    </div>
    @if($education->document_id)
        <p class="mt-4 rounded-[var(--radius-sm)] bg-rose-50 p-3 text-xs font-bold text-rose-800">Dokumen pendidikan terhubung dan tetap private.</p>
    @endif
</div>
