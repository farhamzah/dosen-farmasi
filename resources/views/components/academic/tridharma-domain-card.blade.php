@props([
    'summary',
])

@php
    $palette = [
        'pendidikan' => ['soft' => 'var(--education-soft)', 'accent' => 'var(--education)', 'icon' => 'tridharma', 'caption' => 'Pengajaran dan bimbingan'],
        'penelitian' => ['soft' => 'var(--research-soft)', 'accent' => 'var(--research)', 'icon' => 'search', 'caption' => 'Riset, publikasi, dan HKI'],
        'pengabdian' => ['soft' => 'var(--service-soft)', 'accent' => 'var(--service)', 'icon' => 'portfolio', 'caption' => 'Mitra, luaran, dan masyarakat'],
    ][$summary['key']] ?? ['soft' => 'var(--brand-50)', 'accent' => 'var(--brand-600)', 'icon' => 'portfolio', 'caption' => 'Portofolio akademik'];
    $progress = $summary['total'] > 0 ? (int) round(($summary['verified'] / max(1, $summary['total'])) * 100) : 0;
@endphp

<article {{ $attributes->merge(['class' => 'df-card df-interactive overflow-hidden p-5']) }} style="background: linear-gradient(135deg, {{ $palette['soft'] }}, rgba(255,255,255,.96) 52%);">
    <div class="flex items-start justify-between gap-4">
        <div class="flex min-w-0 gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[var(--radius-lg)] text-white shadow-sm" style="background: {{ $palette['accent'] }}">
                <x-ui.icon :name="$palette['icon']" class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold" style="color: {{ $palette['accent'] }}">{{ $summary['active_period'] }}</p>
                <h3 class="mt-1 text-xl font-bold text-[var(--text-primary)]">{{ $summary['short_label'] }}</h3>
                <p class="mt-1 text-sm font-semibold text-[var(--text-secondary)]">{{ $palette['caption'] }}</p>
            </div>
        </div>
        <a href="{{ route('tridharma.domain', $summary['key']) }}" class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white/80 font-bold text-[var(--brand-800)] shadow-sm" aria-label="Buka {{ $summary['short_label'] }}">-&gt;</a>
    </div>

    <p class="mt-4 line-clamp-2 text-sm leading-6 text-[var(--text-secondary)]">{{ $summary['description'] }}</p>

    <div class="mt-5">
        <div class="flex items-center justify-between text-xs font-extrabold text-[var(--text-secondary)]">
            <span>Progress terverifikasi</span>
            <span>{{ $progress }}%</span>
        </div>
        <div class="df-progress-track mt-2 h-2">
            <div class="df-progress-fill" style="width: {{ $progress }}%; background: {{ $palette['accent'] }}"></div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-3 gap-2">
        <x-ui.stat label="Total" :value="$summary['total']" caption="kegiatan" />
        <x-ui.stat label="Valid" :value="$summary['verified']" tone="success" />
        <x-ui.stat label="Aksi" :value="$summary['needs_completion']" tone="warning" />
    </div>

    <div class="mt-5 space-y-2">
        @forelse($summary['latest']->take(2) as $activity)
            <x-academic.activity-item :activity="$activity" />
        @empty
            <x-ui.empty-state
                title="Belum ada kegiatan pada periode ini"
                description="Kegiatan otomatis dari KP, TA, TU, KP PSPA, dan Lab akan muncul setelah diselesaikan."
                icon="book"
            />
        @endforelse
    </div>

    <div class="mt-5 flex flex-wrap gap-2">
        <x-ui.button :href="route('dosen.portfolio.create', ['domain' => $summary['key']])">Tambah Kegiatan</x-ui.button>
        <x-ui.button :href="route('tridharma.domain', $summary['key'])" variant="secondary">Lihat Detail</x-ui.button>
    </div>
</article>
