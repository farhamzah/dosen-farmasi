@props([
    'title',
    'description' => null,
    'icon' => 'spark',
])

<div {{ $attributes->merge(['class' => 'rounded-[var(--radius-lg)] border border-dashed border-[var(--border)] bg-[var(--surface-muted)]/60 p-5']) }}>
    <div class="flex gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] bg-white text-[var(--brand-700)] shadow-sm">
            <x-ui.icon :name="$icon === 'agenda' ? 'calendar' : ($icon === 'book' ? 'tridharma' : 'portfolio')" class="h-5 w-5" />
        </div>
        <div class="min-w-0">
            <p class="font-bold text-[var(--text-primary)]">{{ $title }}</p>
            @if($description)
                <p class="mt-1 text-sm leading-6 text-[var(--text-secondary)]">{{ $description }}</p>
            @endif
            @isset($actions)
                <div class="mt-4 flex flex-wrap gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
</div>
