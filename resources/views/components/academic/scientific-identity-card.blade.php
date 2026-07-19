@props([
    'identifier',
])

<div {{ $attributes->merge(['class' => 'rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-black text-[var(--text-primary)]">{{ $identifier->identifier_type }}</p>
            <p class="mt-1 truncate text-sm font-semibold text-[var(--text-secondary)]">{{ $identifier->identifier_value }}</p>
        </div>
        <x-ui.badge :tone="$identifier->verification_status === 'VERIFIED' ? 'success' : 'info'">{{ str($identifier->verification_status)->replace('_', ' ')->title() }}</x-ui.badge>
    </div>
    <div class="mt-3 flex items-center justify-between gap-3">
        <x-ui.badge tone="neutral">{{ $identifier->visibility }}</x-ui.badge>
        @if($identifier->profile_url)
            <a href="{{ $identifier->profile_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-extrabold text-[var(--brand-700)] hover:text-[var(--brand-900)]">Buka profil</a>
        @endif
    </div>
</div>
