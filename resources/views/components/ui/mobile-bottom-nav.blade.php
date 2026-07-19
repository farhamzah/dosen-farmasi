@props([
    'items' => [],
])

<nav class="df-mobile-bottom-nav fixed inset-x-0 bottom-0 z-40 border-t border-[var(--border)] bg-white/95 px-2 pt-2 backdrop-blur lg:hidden" aria-label="Navigasi bawah">
    <div class="mx-auto grid max-w-md grid-cols-5 gap-1">
        @foreach($items as $item)
            <a href="{{ $item['href'] }}" aria-current="{{ $item['active'] ? 'page' : 'false' }}" class="flex min-h-14 flex-col items-center justify-center rounded-[var(--radius-sm)] px-1 text-[11px] font-extrabold {{ $item['active'] ? 'bg-[var(--brand-50)] text-[var(--brand-800)]' : 'text-[var(--text-muted)] hover:bg-slate-50 hover:text-[var(--brand-800)]' }}">
                <x-ui.icon :name="$item['icon'] ?? 'home'" class="h-5 w-5" />
                <span class="mt-0.5 truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
