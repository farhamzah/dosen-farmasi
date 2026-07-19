@props([
    'completeness',
])

<x-ui.card class="p-5">
    <x-ui.progress-ring :value="$completeness['percent']" label="Kesiapan Profil" :caption="$completeness['completed'].'/'.$completeness['total'].' bagian lengkap'" />
    <div class="mt-5 space-y-2">
        @forelse($completeness['actions'] as $action)
            <p class="rounded-[var(--radius-sm)] border border-amber-100 bg-amber-50 p-3 text-sm font-bold text-amber-900">{{ $action }}</p>
        @empty
            <p class="rounded-[var(--radius-sm)] border border-emerald-100 bg-emerald-50 p-3 text-sm font-bold text-emerald-900">Profil akademik inti sudah lengkap.</p>
        @endforelse
    </div>
</x-ui.card>
