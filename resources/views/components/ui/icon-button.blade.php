@props([
    'href' => null,
    'label',
])

@php($classes = 'inline-flex h-11 w-11 items-center justify-center rounded-[var(--radius-sm)] border border-[var(--border)] bg-white text-[var(--text-secondary)] transition hover:bg-[var(--brand-50)] hover:text-[var(--brand-800)]')

@if($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
