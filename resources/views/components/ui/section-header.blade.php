@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        @if($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--brand-700)]">{{ $eyebrow }}</p>
        @endif
        <h2 class="df-section-title mt-1 text-[var(--text-primary)]">{{ $title }}</h2>
        @if($description)
            <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--text-secondary)]">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
