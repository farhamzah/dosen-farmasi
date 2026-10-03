@extends('layouts.app', ['title' => 'Portofolio Dosen', 'authLayout' => true])

@php
    $displayUser = $user ?? $lecturer;
    $displayName = $displayUser?->name ?? $lecturer?->name ?? 'Dosen Farmasi';
    $displayEmail = $displayUser?->email ?? $lecturer?->email;
    $photoUrl = $displayUser?->photo_url ?? null;
    $activeFunctional = $functionalPositions->firstWhere('is_active', true) ?? $functionalPositions->first();
    $templateUrl = fn (string $template) => $preview
        ? route('profile.preview', ['template' => $template])
        : ($visibility->public_slug
            ? route('profile.share', ['slug' => $visibility->public_slug, 'template' => $template])
            : route('profile.public', ['lecturerCoreId' => $visibility->lecturer_core_id, 'template' => $template]));
@endphp

@section('content')
<div class="df-cv-web min-h-screen bg-white text-[var(--text-primary)]">
    <header class="df-cv-web-header print:hidden">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-5 py-3 sm:px-8">
            <a href="#atas" class="inline-flex items-center gap-3 font-bold text-[var(--text-primary)]" aria-label="Ke awal portofolio">
                <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="" class="h-9 w-9 object-contain">
                <span>Portofolio Dosen</span>
            </a>
            <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-semibold text-[var(--text-secondary)]" aria-label="Navigasi portofolio">
                <a href="#tentang">Profil</a>
                <a href="#pendidikan">Pendidikan</a>
                <a href="#karya">Karya</a>
                <a href="#kontak">Kontak</a>
            </nav>
        </div>
    </header>

    <div id="atas">
        <section class="df-cv-web-intro border-b border-[var(--border)]">
            <div class="mx-auto grid max-w-6xl gap-8 px-5 py-12 sm:px-8 md:grid-cols-[minmax(0,1fr)_11rem] md:items-center lg:py-16">
                <div class="min-w-0">
                    <p class="df-cv-web-kicker">Fakultas Farmasi · UBP Karawang</p>
                    <h1 class="mt-4 break-words text-3xl font-bold leading-tight sm:text-5xl">{{ $displayName }}</h1>
                    <p class="mt-4 max-w-2xl text-lg leading-8 text-[var(--text-secondary)]">{{ $activeFunctional?->position_name ?: 'Dosen Program Studi Farmasi' }}@if($expertiseAreas->first()?->primary_expertise) · {{ $expertiseAreas->first()->primary_expertise }}@endif</p>
                    <div class="mt-7 flex flex-wrap gap-3 print:hidden">
                        <a href="#karya" class="df-button df-button-primary">Lihat Karya</a>
                        @if($displayEmail)<a href="mailto:{{ $displayEmail }}" class="df-button df-button-secondary">Hubungi</a>@endif
                    </div>
                </div>
                <x-ui.avatar :name="$displayName" :src="$photoUrl" size="h-36 w-36 sm:h-44 sm:w-44" class="df-cv-web-photo" text-class="text-5xl" />
            </div>
        </section>

        <section id="tentang" class="df-cv-web-band">
            <div class="mx-auto grid max-w-6xl gap-8 px-5 py-10 sm:px-8 md:grid-cols-[14rem_minmax(0,1fr)] lg:py-14">
                <div><p class="df-cv-web-kicker">01 / Profil</p><h2 class="mt-2 text-2xl font-bold">Bidang Akademik</h2></div>
                <div class="space-y-5">
                    @if($expertiseAreas->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($expertiseAreas as $area)
                                @foreach(array_filter([$area->primary_expertise, ...($area->specializations ?? [])]) as $expertise)
                                    <span class="df-cv-web-tag">{{ $expertise }}</span>
                                @endforeach
                            @endforeach
                        </div>
                    @else
                        <p class="text-[var(--text-secondary)]">Bidang keilmuan belum dipublikasikan.</p>
                    @endif
                    @if($functionalPositions->isNotEmpty() || $certifications->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($functionalPositions->take(2) as $position)
                                <div class="df-cv-web-fact"><span>Jabatan</span><strong>{{ $position->position_name }}</strong></div>
                            @endforeach
                            @foreach($certifications->take(2) as $certification)
                                <div class="df-cv-web-fact"><span>Sertifikasi</span><strong>{{ $certification->name }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section id="pendidikan" class="df-cv-web-band df-cv-web-muted">
            <div class="mx-auto grid max-w-6xl gap-8 px-5 py-10 sm:px-8 md:grid-cols-[14rem_minmax(0,1fr)] lg:py-14">
                <div><p class="df-cv-web-kicker">02 / Pendidikan</p><h2 class="mt-2 text-2xl font-bold">Perjalanan Studi</h2></div>
                <div class="df-cv-web-list">
                    @forelse($educations as $education)
                        <article class="df-cv-web-list-item">
                            <span class="text-sm font-semibold text-[var(--brand-700)]">{{ $education->end_year ?: $education->start_year ?: 'Pendidikan' }}</span>
                            <div><h3 class="font-bold">{{ $education->level }} · {{ $education->institution_name }}</h3><p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $education->study_program ?: $education->degree }}</p></div>
                        </article>
                    @empty
                        <p class="text-[var(--text-secondary)]">Riwayat pendidikan belum dipublikasikan.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="karya" class="df-cv-web-band">
            <div class="mx-auto grid max-w-6xl gap-8 px-5 py-10 sm:px-8 md:grid-cols-[14rem_minmax(0,1fr)] lg:py-14">
                <div><p class="df-cv-web-kicker">03 / Portofolio</p><h2 class="mt-2 text-2xl font-bold">Karya dan Kegiatan</h2></div>
                <div class="df-cv-web-list">
                    @forelse($activities as $activity)
                        <article class="df-cv-web-list-item">
                            <span class="text-sm font-semibold text-[var(--brand-700)]">{{ $activity->end_date?->format('Y') ?: $activity->start_date?->format('Y') ?: $activity->academic_year ?: 'Karya' }}</span>
                            <div>
                                <p class="text-xs font-bold uppercase text-[var(--brand-700)]">{{ $activity->category?->name ?? 'Portofolio' }}</p>
                                <h3 class="mt-1 text-lg font-bold">{{ $activity->title }}</h3>
                                @if($activity->lecturer_role)<p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $activity->lecturer_role }}</p>@endif
                                @if(\App\Support\PortfolioUi::isHki($activity->activity_type))<p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $activity->hki_type ?: 'HKI' }} · {{ \App\Support\PortfolioUi::hkiStatusLabel($activity->hki_status) }}</p>@endif
                                @if($activity->description)<p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--text-secondary)]">{{ $activity->description }}</p>@endif
                            </div>
                        </article>
                    @empty
                        <p class="text-[var(--text-secondary)]">Karya dan kegiatan belum dipublikasikan.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="kontak" class="df-cv-web-band df-cv-web-contact">
            <div class="mx-auto grid max-w-6xl gap-6 px-5 py-10 sm:px-8 md:grid-cols-[14rem_minmax(0,1fr)] lg:py-14">
                <div><p class="df-cv-web-kicker">04 / Kontak</p><h2 class="mt-2 text-2xl font-bold">Mari Terhubung</h2></div>
                <div class="min-w-0 space-y-4">
                    @if($displayEmail)<a href="mailto:{{ $displayEmail }}" class="block break-all text-lg font-bold underline">{{ $displayEmail }}</a>@endif
                    @if($identifiers->isNotEmpty())
                        <div class="flex flex-wrap gap-3">
                            @foreach($identifiers as $identifier)
                                @if($identifier->profile_url && str_starts_with($identifier->profile_url, 'https://'))
                                    <a href="{{ $identifier->profile_url }}" target="_blank" rel="noopener noreferrer" class="df-cv-web-tag">{{ $identifier->identifier_type }} ↗</a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <p class="text-sm text-[var(--text-secondary)]">Data pribadi dan dokumen bukti tidak ditampilkan di halaman publik.</p>
                </div>
            </div>
        </section>
    </div>

    <footer class="df-cv-web-footer print:hidden">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-5 py-5 sm:px-8">
            <span>Fakultas Farmasi UBP Karawang</span>
            <div class="flex flex-wrap gap-3">
                @if($preview)<a href="{{ route('profile.show').'#profil-publik' }}">Kembali ke profil</a>@endif
                <a href="{{ $templateUrl('akademik') }}">Lihat CV cetak</a>
                <button type="button" onclick="window.print()">Cetak / PDF</button>
            </div>
        </div>
    </footer>
    @if($preview)<div class="df-cv-web-preview print:hidden">Pratinjau pribadi · hanya Anda yang dapat melihat halaman ini.</div>@endif
</div>
@endsection
