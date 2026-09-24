@extends('layouts.app', ['title' => 'CV Publik Dosen', 'authLayout' => true])

@php
    $displayUser = $user ?? $lecturer;
    $displayName = $displayUser?->name ?? $lecturer?->name ?? 'Dosen Farmasi';
    $displayEmail = $displayUser?->email ?? $lecturer?->email;
    $photoUrl = $displayUser?->photo_url ?? null;
    $activeFunctional = $functionalPositions->firstWhere('is_active', true) ?? $functionalPositions->first();
    $primaryExpertise = optional($expertiseAreas->first())->primary_expertise;
    $publicSections = [
        ['label' => 'Pendidikan', 'count' => $educations->count()],
        ['label' => 'Aktivitas', 'count' => $activities->count()],
        ['label' => 'Identitas', 'count' => $identifiers->count()],
    ];
    $selectedTemplate = $selectedTemplate ?? 'akademik';
    $templateConfig = [
        'akademik' => [
            'page' => 'bg-[var(--surface)]',
            'paper' => 'border border-[var(--border)] bg-white',
            'hero' => 'bg-white text-[var(--text-primary)]',
            'side' => 'bg-[var(--surface)]',
            'accent' => 'text-[var(--brand-700)]',
            'title' => 'CV Akademik',
        ],
        'impact' => [
            'page' => 'bg-[var(--surface)]',
            'paper' => 'border border-[var(--border)] bg-white',
            'hero' => 'bg-[var(--brand-900)] text-white',
            'side' => 'bg-[#edf4ef]',
            'accent' => 'text-emerald-800',
            'title' => 'Portofolio Mitra',
        ],
        'editorial' => [
            'page' => 'bg-[var(--surface)]',
            'paper' => 'border border-[var(--border)] bg-white',
            'hero' => 'bg-white text-[var(--text-primary)]',
            'side' => 'bg-white',
            'accent' => 'text-slate-700',
            'title' => 'CV Ringkas',
        ],
    ][$selectedTemplate] ?? [];
    $isEditorial = $selectedTemplate !== 'impact';
@endphp

@section('content')
<div class="df-cv-page min-h-screen {{ $templateConfig['page'] }} px-4 py-6 sm:px-6 lg:py-10">
    <div class="mx-auto max-w-6xl">
        <div class="mb-5 flex flex-col gap-3 print:hidden sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ $preview ? route('profile.show').'#profil-publik' : route('login') }}" class="text-sm font-bold text-[var(--brand-800)]">{{ $preview ? 'Kembali ke pilihan CV' : 'Dosen Farmasi UBP' }}</a>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <nav class="flex flex-wrap gap-2" aria-label="Pilih tampilan CV">
                    @foreach($templates as $key => $template)
                        <a href="{{ $preview ? route('profile.preview', ['template' => $key]) : route('profile.public', ['lecturerCoreId' => $visibility->lecturer_core_id, 'template' => $key]) }}" aria-current="{{ $selectedTemplate === $key ? 'page' : 'false' }}" title="{{ $template['description'] }}" class="rounded-[var(--radius-sm)] border px-3 py-2 text-xs font-bold {{ $selectedTemplate === $key ? 'border-[var(--brand-700)] bg-[var(--brand-800)] text-white' : 'border-[var(--border)] bg-white text-[var(--brand-800)]' }}">
                            {{ $template['label'] }}
                        </a>
                    @endforeach
                </nav>
                <button type="button" onclick="window.print()" class="df-button df-button-primary sm:w-auto">Cetak / Simpan PDF</button>
            </div>
        </div>
        <p class="mb-4 text-sm text-[var(--text-secondary)] print:hidden"><strong>{{ $templates[$selectedTemplate]['label'] }}</strong> · {{ $templates[$selectedTemplate]['description'] }} {{ $preview ? 'Pratinjau ini hanya dapat dilihat oleh Anda.' : 'Tampilan ini hanya mengubah desain, bukan isi CV.' }}</p>

        <article class="df-cv-paper df-cv-{{ $selectedTemplate }} {{ $templateConfig['paper'] }} print:rounded-none print:border-0 print:shadow-none">
            <section class="df-cv-heading relative {{ $templateConfig['hero'] }} px-6 py-8 sm:px-8 lg:px-10">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center">
                    <div class="flex min-w-0 flex-col gap-6 sm:flex-row sm:items-center">
                        <x-ui.avatar :name="$displayName" :src="$photoUrl" size="h-20 w-20 sm:h-24 sm:w-24" class="rounded-[8px]" text-class="text-3xl" />
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[0.22em] {{ $isEditorial ? 'text-[var(--text-muted)]' : 'text-white/60' }}">{{ $templateConfig['title'] }}</p>
                            <h1 class="mt-3 break-words text-3xl font-bold leading-tight">{{ $displayName }}</h1>
                            <p class="mt-3 max-w-2xl text-base font-semibold leading-7 {{ $isEditorial ? 'text-[var(--text-secondary)]' : 'text-white/78' }}">
                                {{ $activeFunctional?->position_name ?: 'Dosen Program Studi Farmasi' }}
                                @if($primaryExpertise)
                                    · {{ $primaryExpertise }}
                                @endif
                            </p>
                            <div class="mt-5 flex flex-wrap gap-2">
                                @if($displayEmail)
                                    <span class="max-w-full break-all rounded-full {{ $isEditorial ? 'bg-white text-[var(--brand-800)] ring-[var(--border)]' : 'bg-white/10 text-white ring-white/15' }} px-3 py-1.5 text-sm font-bold ring-1">{{ $displayEmail }}</span>
                                @endif
                                <span class="rounded-full {{ $isEditorial ? 'bg-emerald-50 text-emerald-800 ring-emerald-100' : 'bg-emerald-400/15 text-emerald-100 ring-emerald-200/20' }} px-3 py-1.5 text-sm font-bold ring-1">{{ $preview ? 'Pratinjau pribadi' : 'Profil publik' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="df-cv-summary min-w-0 {{ $isEditorial ? 'bg-white p-0 text-[var(--text-primary)]' : 'p-5 text-white' }}">
                        <p class="text-sm font-bold {{ $isEditorial ? 'text-[var(--text-secondary)]' : 'text-white/70' }}">Ringkasan Publik</p>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @foreach($publicSections as $section)
                                <div class="rounded-2xl {{ $isEditorial ? 'bg-[var(--surface)]' : 'bg-white/10' }} p-3 text-center">
                                    <p class="text-2xl font-black">{{ $section['count'] }}</p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide {{ $isEditorial ? 'text-[var(--text-muted)]' : 'text-white/60' }}">{{ $section['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-4 text-xs leading-5 {{ $isEditorial ? 'text-[var(--text-secondary)]' : 'text-white/58' }}">{{ $templates[$selectedTemplate]['description'] }}</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:p-10">
                <div class="min-w-0 space-y-6">
                    <section class="rounded-3xl border border-[var(--border)] bg-white p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] {{ $templateConfig['accent'] }}">Pendidikan</p>
                        <h2 class="mt-2 text-2xl font-black text-[var(--text-primary)]">Riwayat Akademik</h2>
                        <div class="mt-5 divide-y divide-[var(--border)]">
                            @forelse($educations as $education)
                                <article class="py-4 first:pt-0 last:pb-0">
                                    <p class="text-lg font-black text-[var(--text-primary)]">{{ $education->level }} · {{ $education->institution_name }}</p>
                                    <p class="mt-1 text-sm font-semibold text-[var(--text-secondary)]">{{ $education->study_program ?: 'Program studi tidak dipublikasikan' }}{{ $education->end_year ? ' · '.$education->end_year : '' }}</p>
                                    @if($education->thesis_title)
                                        <p class="mt-2 text-sm leading-6 text-[var(--text-secondary)]">{{ $education->thesis_title }}</p>
                                    @endif
                                </article>
                            @empty
                                <p class="text-sm text-[var(--text-muted)]">Belum ada pendidikan yang ditampilkan.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-3xl border border-[var(--border)] bg-white p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] {{ $templateConfig['accent'] }}">Portofolio</p>
                        <h2 class="mt-2 text-2xl font-black text-[var(--text-primary)]">Aktivitas Akademik Pilihan</h2>
                        <div class="mt-5 grid gap-3">
                            @forelse($activities as $activity)
                                <article class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-base font-black text-[var(--text-primary)]">{{ $activity->title }}</p>
                                            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $activity->category?->name ?? $activity->activity_type }} · {{ $activity->lecturer_role ?: 'Peran belum dipublikasikan' }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-black text-[var(--brand-800)] ring-1 ring-[var(--border)]">{{ optional($activity->start_date)->format('Y') ?: $activity->academic_year }}</span>
                                    </div>
                                    @if($activity->description)
                                        <p class="mt-3 line-clamp-3 text-sm leading-6 text-[var(--text-secondary)]">{{ $activity->description }}</p>
                                    @endif
                                </article>
                            @empty
                                <p class="text-sm text-[var(--text-muted)]">Belum ada aktivitas yang ditampilkan.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="min-w-0 space-y-6">
                    <section class="rounded-3xl border border-[var(--border)] {{ $templateConfig['side'] }} p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] {{ $templateConfig['accent'] }}">Keilmuan</p>
                        <h2 class="mt-2 text-xl font-black text-[var(--text-primary)]">Kepakaran</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse($expertiseAreas as $area)
                                @foreach(array_filter([$area->primary_expertise, ...($area->specializations ?? []), ...($area->research_topics ?? [])]) as $chip)
                                    <span class="rounded-full border border-[var(--brand-100)] bg-white px-3 py-1.5 text-sm font-bold text-[var(--brand-800)]">{{ $chip }}</span>
                                @endforeach
                            @empty
                                <p class="text-sm text-[var(--text-muted)]">Bidang keilmuan belum dipublikasikan.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-3xl border border-[var(--border)] bg-white p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] {{ $templateConfig['accent'] }}">Karier</p>
                        <h2 class="mt-2 text-xl font-black text-[var(--text-primary)]">Jabatan dan Sertifikasi</h2>
                        <div class="mt-4 space-y-3">
                            @forelse($functionalPositions as $position)
                                <div class="rounded-2xl bg-[var(--surface)] p-4">
                                    <p class="font-black text-[var(--text-primary)]">{{ $position->position_name }}</p>
                                    <p class="mt-1 text-sm text-[var(--text-secondary)]">TMT {{ optional($position->effective_date)->format('Y') ?: 'belum dipublikasikan' }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-[var(--text-muted)]">Belum ada jabatan yang ditampilkan.</p>
                            @endforelse

                            @foreach($certifications as $certification)
                                <div class="rounded-2xl bg-[var(--surface)] p-4">
                                    <p class="font-black text-[var(--text-primary)]">{{ $certification->name }}</p>
                                    <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $certification->issuer ?: 'Penerbit tidak dipublikasikan' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-3xl border border-[var(--border)] bg-white p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] {{ $templateConfig['accent'] }}">Identitas Ilmiah</p>
                        <div class="mt-4 space-y-2">
                            @forelse($identifiers as $identifier)
                                @if($identifier->profile_url)
                                    <a href="{{ $identifier->profile_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between rounded-2xl bg-[var(--surface)] px-4 py-3 text-sm font-black text-[var(--brand-800)]">
                                        <span>{{ $identifier->identifier_type }}</span>
                                        <span>Buka</span>
                                    </a>
                                @else
                                    <div class="rounded-2xl bg-[var(--surface)] px-4 py-3 text-sm font-black text-[var(--text-primary)]">{{ $identifier->identifier_type }}</div>
                                @endif
                            @empty
                                <p class="text-sm text-[var(--text-muted)]">Identitas ilmiah belum dipublikasikan.</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </section>
        </article>
    </div>
</div>
@endsection
