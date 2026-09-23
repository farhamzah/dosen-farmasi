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

<section {{ $attributes->merge(['class' => 'df-profile-header']) }} aria-labelledby="profile-name">
    <div class="df-profile-header-accent"></div>
    <div class="df-profile-header-body">
        <div class="df-profile-identity">
            <x-ui.avatar :name="$user->name" :src="$user->photo_url" size="h-20 w-20 sm:h-24 sm:w-24" class="shrink-0 rounded-[var(--radius-md)]" text-class="text-2xl" />
            <div class="min-w-0">
                <p class="df-profile-eyebrow">Profil Akademik <span aria-hidden="true">/</span> Program Studi Farmasi</p>
                <h1 id="profile-name" class="df-profile-name">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $activePosition?->position_name ?: 'Jabatan fungsional belum dicatat' }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    <span class="inline-flex items-center gap-2 font-semibold {{ $user->is_active ? 'text-emerald-800' : 'text-rose-700' }}"><span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    @if($primaryExpertise)
                        <span class="min-w-0 text-[var(--text-secondary)]">{{ $primaryExpertise }}</span>
                    @endif
                    @if($user->email)
                        <span class="min-w-0 break-all text-[var(--text-secondary)]">{{ $user->email }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="df-profile-progress" aria-label="Kelengkapan profil {{ $completeness['percent'] }} persen">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-[var(--text-primary)]">Kelengkapan profil</p>
                    <p class="mt-1 text-xs text-[var(--text-secondary)]">{{ $completeness['completed'] }} dari {{ $completeness['total'] }} bagian terisi</p>
                </div>
                <strong class="text-2xl text-[var(--brand-800)]">{{ $completeness['percent'] }}%</strong>
            </div>
            <div class="df-progress-track mt-3 h-2"><div class="df-progress-fill bg-[var(--brand-600)]" style="width: {{ $completeness['percent'] }}%"></div></div>
        </div>
    </div>
</section>
