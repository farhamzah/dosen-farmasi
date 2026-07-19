@props([
    'title',
    'caption' => null,
    'active' => false,
])

<div {{ $attributes->merge(['class' => 'relative rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4']) }}>
    <span class="absolute -left-[2.06rem] top-5 h-4 w-4 rounded-full border-4 border-white {{ $active ? 'bg-emerald-600' : 'bg-[var(--brand-600)]' }}"></span>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="font-black text-[var(--text-primary)]">{{ $title }}</p>
            @if($caption)
                <p class="mt-1 text-sm leading-6 text-[var(--text-secondary)]">{{ $caption }}</p>
            @endif
        </div>
        @if($active)
            <x-ui.badge tone="success">Aktif</x-ui.badge>
        @endif
    </div>
</div>
