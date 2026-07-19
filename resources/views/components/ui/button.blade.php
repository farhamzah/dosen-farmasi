@props([
    'href' => null,
    'variant' => 'primary',
])

@php
    $classes = 'df-button '.($variant === 'secondary' ? 'df-button-secondary' : 'df-button-primary');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
