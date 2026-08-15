@props([
    'name' => 'Pengguna',
    'src' => null,
    'size' => 'h-10 w-10',
    'textClass' => 'text-sm',
])

@php
    $safeSrc = filled($src) && str($src)->startsWith(['http://', 'https://']) ? $src : null;
    $initial = str($name ?: 'P')->trim()->substr(0, 1)->upper();
@endphp

@if($safeSrc)
    <img
        src="{{ $safeSrc }}"
        alt="Foto {{ $name }}"
        loading="lazy"
        referrerpolicy="no-referrer"
        {{ $attributes->merge(['class' => $size.' shrink-0 rounded-full bg-white object-cover object-center ring-1 ring-[var(--border)]']) }}
    >
@else
    <span
        aria-label="Inisial {{ $name }}"
        {{ $attributes->merge(['class' => 'grid '.$size.' shrink-0 place-items-center rounded-full bg-[var(--brand-800)] font-bold text-white '.$textClass]) }}
    >
        {{ $initial }}
    </span>
@endif
