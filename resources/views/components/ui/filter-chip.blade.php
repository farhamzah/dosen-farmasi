@props([
    'href' => null,
])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'df-pill hover:border-[var(--brand-100)] hover:bg-[var(--brand-50)] hover:text-[var(--brand-800)]']) }}>
        {{ $slot }}
    </a>
@else
    <span {{ $attributes->merge(['class' => 'df-pill']) }}>
        {{ $slot }}
    </span>
@endif
