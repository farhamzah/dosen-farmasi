@props([
    'label',
    'value',
    'caption' => null,
    'tone' => 'brand',
])

@php
    $accent = [
        'brand' => 'text-[var(--brand-800)]',
        'success' => 'text-emerald-700',
        'warning' => 'text-amber-700',
        'danger' => 'text-rose-700',
        'info' => 'text-blue-700',
    ][$tone] ?? 'text-[var(--brand-800)]';
@endphp

<div {{ $attributes->merge(['class' => 'df-card-muted min-w-0 p-4']) }}>
    <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[var(--text-muted)]">{{ $label }}</p>
    <p class="mt-2 break-words text-2xl font-bold [overflow-wrap:anywhere] {{ $accent }}">{{ $value }}</p>
    @if($caption)
        <p class="mt-1 text-sm leading-5 text-[var(--text-secondary)]">{{ $caption }}</p>
    @endif
</div>
