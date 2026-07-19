@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'compact' => false,
])

<section {{ $attributes->merge(['class' => $compact ? 'df-card overflow-hidden p-4 sm:p-5 lg:p-6' : 'df-card overflow-hidden p-6 sm:p-8']) }}>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center">
        <div>
            @if($eyebrow)
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--brand-700)]">{{ $eyebrow }}</p>
            @endif
            <h1 class="{{ $compact ? 'mt-2 text-2xl font-bold leading-tight sm:text-3xl' : 'df-page-title mt-3' }} max-w-4xl text-[var(--text-primary)]">{{ $title }}</h1>
            @if($description)
                <p class="{{ $compact ? 'mt-3' : 'mt-4' }} max-w-3xl text-sm leading-6 text-[var(--text-secondary)] sm:text-base">{{ $description }}</p>
            @endif
            @isset($actions)
                <div class="{{ $compact ? 'mt-4' : 'mt-6' }} flex flex-wrap gap-3">
                    {{ $actions }}
                </div>
            @endisset
        </div>
        @isset($aside)
            <div>
                {{ $aside }}
            </div>
        @endisset
    </div>
</section>
