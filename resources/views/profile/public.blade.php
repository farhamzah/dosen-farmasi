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
@endphp

@section('content')
<div class="min-h-screen bg-[linear-gradient(135deg,#eef4fb_0%,#fbfcff_54%,#fff8ee_100%)] px-4 py-6 sm:px-6 lg:py-10">
    <div class="mx-auto max-w-6xl">
        <div class="mb-5 flex flex-col gap-3 print:hidden sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('login') }}" class="text-sm font-bold text-[var(--brand-800)]">Dosen Farmasi UBP</a>
            <button type="button" onclick="window.print()" class="df-button df-button-primary sm:w-auto">Cetak / Simpan PDF</button>
        </div>

        <main class="overflow-hidden rounded-[28px] border border-white/80 bg-white shadow-[0_30px_90px_rgba(15,23,42,0.14)] print:rounded-none print:border-0 print:shadow-none">
            <section class="relative overflow-hidden bg-[var(--brand-950)] px-6 py-8 text-white sm:px-8 lg:px-10">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-[linear-gradient(90deg,var(--brand-600),var(--research),var(--service))]"></div>
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        <x-ui.avatar :name="$displayName" :src="$photoUrl" size="h-28 w-28 sm:h-32 sm:w-32" class="rounded-3xl ring-white/20" text-class="text-4xl" />
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-white/60">Academic Portfolio Console</p>
                            <h1 class="mt-3 text-4xl font-black leading-tight sm:text-5xl">{{ $displayName }}</h1>
                            <p class="mt-3 max-w-2xl text-base font-semibold leading-7 text-white/78">
                                {{ $activeFunctional?->position_name ?: 'Dosen Program Studi Farmasi' }}
                                @if($primaryExpertise)
                                    · {{ $primaryExpertise }}
                                @endif
                            </p>
                            <div class="mt-5 flex flex-wrap gap-2">
                                @if($displayEmail)
                                    <span class="rounded-full bg-white/10 px-3 py-1.5 text-sm font-bold text-white ring-1 ring-white/15">{{ $displayEmail }}</span>
                                @endif
                                <span class="rounded-full bg-emerald-400/15 px-3 py-1.5 text-sm font-bold text-emerald-100 ring-1 ring-emerald-200/20">Profil publik aman</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-white/10 p-5 ring-1 ring-white/15">
                        <p class="text-sm font-bold text-white/70">Ringkasan Publik</p>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @foreach($publicSections as $section)
                                <div class="rounded-2xl bg-white/10 p-3 text-center">
                                    <p class="text-2xl font-black">{{ $section['count'] }}</p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-white/60">{{ $section['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-4 text-xs leading-5 text-white/58">CV ini dihasilkan dari data portofolio dosen-farmasi. Data sensitif tidak ditampilkan.</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:p-10">
                <div class="min-w-0 space-y-6">
                    <section class="rounded-3xl border border-[var(--border)] bg-white p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[var(--brand-700)]">Pendidikan</p>
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
                                <p class="text-sm text-[var(--text-muted)]">Belum ada pendidikan public.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-3xl border border-[var(--border)] bg-white p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[var(--brand-700)]">Portofolio</p>
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
                                <p class="text-sm text-[var(--text-muted)]">Belum ada aktivitas public.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="min-w-0 space-y-6">
                    <section class="rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[var(--brand-700)]">Keilmuan</p>
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
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[var(--brand-700)]">Karier</p>
                        <h2 class="mt-2 text-xl font-black text-[var(--text-primary)]">Jabatan dan Sertifikasi</h2>
                        <div class="mt-4 space-y-3">
                            @forelse($functionalPositions as $position)
                                <div class="rounded-2xl bg-[var(--surface)] p-4">
                                    <p class="font-black text-[var(--text-primary)]">{{ $position->position_name }}</p>
                                    <p class="mt-1 text-sm text-[var(--text-secondary)]">TMT {{ optional($position->effective_date)->format('Y') ?: 'belum dipublikasikan' }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-[var(--text-muted)]">Jabatan public belum tersedia.</p>
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
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[var(--brand-700)]">Identitas Ilmiah</p>
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
        </main>
    </div>
</div>
@endsection
