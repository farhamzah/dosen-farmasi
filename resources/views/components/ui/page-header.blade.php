@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'compact' => false,
])

<section {{ $attributes->merge(['class' => 'df-page-header']) }}>
    <div class="df-page-header-main">
        <div class="min-w-0">
            @if($eyebrow)
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--brand-700)]">{{ $eyebrow }}</p>
            @endif
            <h1 class="df-page-title mt-1 text-[var(--text-primary)]">{{ $title }}</h1>
            @if($description)
                <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--text-secondary)]">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="df-page-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
    @isset($aside)
        <div class="df-page-context">{{ $aside }}</div>
    @endisset
</section>
