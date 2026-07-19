@props([
    'user',
    'completeness',
    'functionalPositions' => collect(),
    'expertiseAreas' => collect(),
])

@php
    $activePosition = $functionalPositions->firstWhere('is_active', true) ?? $functionalPositions->first();
    $primaryExpertise = optional($expertiseAreas->first())->primary_expertise;
@endphp

<section {{ $attributes->merge(['class' => 'df-card overflow-hidden']) }}>
    <div class="h-1.5 bg-[linear-gradient(90deg,var(--brand-700),var(--research),var(--service))]"></div>
    <div class="grid gap-6 p-5 sm:p-7 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-center">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <div class="relative flex h-24 w-24 shrink-0 items-center justify-center rounded-[var(--radius-xl)] bg-[var(--brand-50)] text-3xl font-bold text-[var(--brand-800)] ring-1 ring-[var(--brand-100)] sm:h-28 sm:w-28">
                {{ str($user->name)->substr(0, 1) }}
                <span class="absolute -bottom-2 -right-2 grid h-8 w-8 place-items-center rounded-full bg-white text-sm font-semibold text-[var(--brand-800)] shadow-sm ring-1 ring-[var(--border)]" aria-label="Foto profil dapat diperbarui">+</span>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-[var(--brand-700)]">Profil Akademik</p>
                <h1 class="df-page-title mt-2 truncate text-[var(--text-primary)]">{{ $user->name }}</h1>
                <p class="mt-2 text-sm font-medium text-[var(--text-secondary)]">{{ $activePosition?->position_name ?: 'Jabatan fungsional belum dicatat' }} - Program Studi Farmasi</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <x-ui.badge tone="success">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                    <x-ui.badge tone="brand">{{ $primaryExpertise ?: 'Bidang keahlian belum diisi' }}</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $user->email ?: 'Email belum tersedia' }}</x-ui.badge>
                </div>
            </div>
        </div>

        <div class="rounded-[var(--radius-lg)] bg-[var(--surface-muted)]/70 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-[var(--text-primary)]">Kelengkapan Profil</p>
                    <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $completeness['completed'] }} dari {{ $completeness['total'] }} bagian lengkap</p>
                </div>
                <p class="text-3xl font-bold text-[var(--brand-800)]">{{ $completeness['percent'] }}%</p>
            </div>
            <div class="df-progress-track mt-4 h-2">
                <div class="df-progress-fill bg-[var(--brand-600)]" style="width: {{ $completeness['percent'] }}%"></div>
            </div>
        </div>
    </div>
</section>
