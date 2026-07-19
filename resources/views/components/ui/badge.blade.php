@props([
    'tone' => 'neutral',
])

@php
    $tones = [
        'brand' => 'border-[var(--brand-100)] bg-[var(--brand-50)] text-[var(--brand-800)]',
        'success' => 'border-emerald-100 bg-emerald-50 text-emerald-800',
        'warning' => 'border-amber-100 bg-amber-50 text-amber-800',
        'danger' => 'border-rose-100 bg-rose-50 text-rose-800',
        'info' => 'border-blue-100 bg-blue-50 text-blue-800',
        'neutral' => 'border-[var(--border)] bg-white text-[var(--text-secondary)]',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.$tones[$tone]]) }}>
    {{ $slot }}
</span>
