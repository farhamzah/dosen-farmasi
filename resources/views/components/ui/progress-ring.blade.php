@props([
    'value' => 0,
    'label' => null,
    'caption' => null,
    'size' => 'h-32 w-32',
])

@php
    $value = max(0, min(100, (int) $value));
    $background = "conic-gradient(var(--brand-600) {$value}%, rgba(219, 227, 238, 0.9) 0)";
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-4']) }}>
    <div class="{{ $size }} grid place-items-center rounded-full p-2" style="background: {{ $background }}">
        <div class="grid h-full w-full place-items-center rounded-full bg-white text-center shadow-inner">
            <span class="text-3xl font-black text-[var(--brand-900)]">{{ $value }}%</span>
        </div>
    </div>
    @if($label || $caption)
        <div>
            @if($label)
                <p class="text-sm font-extrabold text-[var(--text-primary)]">{{ $label }}</p>
            @endif
            @if($caption)
                <p class="mt-1 text-sm leading-6 text-[var(--text-secondary)]">{{ $caption }}</p>
            @endif
        </div>
    @endif
</div>
