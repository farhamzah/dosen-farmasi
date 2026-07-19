@props([
    'activity',
    'href' => null,
])

@php
    $status = $activity->verification_status ?? 'DRAFT';
    $tone = str_contains($status, 'VERIFIED') ? 'success' : (str_contains($status, 'REVISION') ? 'warning' : 'info');
    $sourceLabel = match (true) {
        $activity->source_app === 'm8-ui-demo' => 'Data Demo',
        ($activity->source_type ?? null) === 'MANUAL' => 'Input Mandiri',
        filled($activity->source_app) => 'Sistem Terhubung',
        default => 'Manual',
    };
@endphp

<a href="{{ $href ?? route('dosen.portfolio.show', $activity) }}" {{ $attributes->merge(['class' => 'group block rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4 transition hover:border-[var(--brand-100)] hover:bg-[var(--brand-50)]']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-extrabold text-[var(--text-primary)]">{{ $activity->title }}</p>
            <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $activity->academic_year ?: 'Tanpa tahun' }} · {{ $activity->semester ?: 'Tanpa semester' }}</p>
        </div>
        <x-ui.badge :tone="$tone">{{ str($status)->replace('_', ' ')->title() }}</x-ui.badge>
    </div>
    <p class="mt-3 text-xs font-bold text-[var(--brand-700)]">{{ $sourceLabel }}</p>
</a>
