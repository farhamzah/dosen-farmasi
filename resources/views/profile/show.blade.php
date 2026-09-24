@extends('layouts.app', ['title' => 'Profil Akademik'])

@section('breadcrumb', 'Profil Akademik')

@php
    $basicLevels = ['SD', 'SMP', 'SMA', 'SMK'];
    $higherEducations = $educations->reject(fn ($education) => in_array($education->level, $basicLevels, true));
    $basicEducations = $educations->filter(fn ($education) => in_array($education->level, $basicLevels, true));
    $activeFunctional = $functionalPositions->firstWhere('is_active', true) ?? $functionalPositions->first();
@endphp

@section('content')
<div class="df-profile-page space-y-5">
    @if($errors->any())
        <div role="alert" class="rounded-[var(--radius-sm)] border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
            <p class="font-bold">Periksa kembali data yang diisi.</p>
            <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <x-academic.academic-profile-header
        :user="$user"
        :completeness="$completeness"
        :functional-positions="$functionalPositions"
        :expertise-areas="$expertiseAreas"
    />

    <div class="df-profile-toolbar">
        <div class="min-w-0">
            <p class="text-sm font-bold text-[var(--text-primary)]">Data akademik Anda</p>
            <p class="text-xs text-[var(--text-secondary)]">Pendidikan, karier, kepakaran, dan identitas ilmiah dalam satu profil.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="#tambah-pendidikan" class="df-button df-button-secondary">Tambah pendidikan</a>
            <a href="#visibilitas" class="df-button df-button-secondary">Atur visibilitas</a>
            <a href="#profil-publik" class="df-button df-button-primary"><x-heroicon-o-document-text class="h-4 w-4" />Pilih template CV</a>
        </div>
    </div>

    <nav class="df-segmented df-profile-nav" aria-label="Navigasi Profil Akademik">
        @foreach([
            'ringkasan' => 'Ringkasan',
            'pendidikan' => 'Pendidikan',
            'karier' => 'Karier',
            'keilmuan' => 'Keilmuan',
            'sertifikasi' => 'Sertifikasi',
            'identitas-ilmiah' => 'Identitas Ilmiah',
            'profil-publik' => 'CV Saya',
            'visibilitas' => 'Visibilitas',
        ] as $anchor => $label)
            <a href="#{{ $anchor }}" class="shrink-0 rounded-[var(--radius-sm)] px-4 py-2.5 text-sm font-semibold text-[var(--text-secondary)] hover:bg-[var(--brand-50)] hover:text-[var(--brand-800)]">{{ $label }}</a>
        @endforeach
    </nav>

    <section class="df-profile-layout">
        <div class="df-profile-main">
            <div id="ringkasan" data-profile-panel="ringkasan" class="df-profile-summary">
                <div class="df-profile-summary-heading">
                    <div>
                        <p class="df-profile-eyebrow">Ringkasan</p>
                        <h2 class="text-xl font-bold text-[var(--text-primary)]">Sekilas profil</h2>
                    </div>
                    <a href="#pendidikan" class="text-sm font-semibold text-[var(--brand-700)] hover:underline">Lihat riwayat pendidikan</a>
                </div>
                <dl class="df-profile-facts">
                    <div><dt>Pendidikan tinggi</dt><dd>{{ $higherEducations->count() }} riwayat</dd></div>
                    <div><dt>Jabatan fungsional</dt><dd>{{ $activeFunctional?->position_name ?: 'Belum tercatat' }}</dd></div>
                    <div><dt>Bidang utama</dt><dd>{{ $expertiseAreas->first()?->primary_expertise ?: 'Belum diisi' }}</dd></div>
                    <div><dt>Identitas ilmiah</dt><dd>{{ $identifiers->count() }} terhubung</dd></div>
                </dl>
                <div class="df-profile-next">
                    <a href="#pendidikan" class="df-quick-link"><x-ui.icon name="tridharma" /><span>Kelola riwayat pendidikan</span><x-heroicon-o-chevron-right class="h-4 w-4" /></a>
                    <a href="#karier" class="df-quick-link"><x-ui.icon name="portfolio" /><span>Kelola karier dan jabatan</span><x-heroicon-o-chevron-right class="h-4 w-4" /></a>
                    <a href="#keilmuan" class="df-quick-link"><x-heroicon-o-beaker class="h-5 w-5" /><span>Kelola bidang kepakaran</span><x-heroicon-o-chevron-right class="h-4 w-4" /></a>
                    <a href="#profil-publik" class="df-quick-link"><x-ui.icon name="document" /><span>Pilih dan cetak CV</span><x-heroicon-o-chevron-right class="h-4 w-4" /></a>
                </div>
            </div>

            <x-ui.card id="pendidikan" data-profile-panel="pendidikan" class="df-profile-panel">
                <x-ui.section-header
                    eyebrow="Pendidikan"
                    title="Riwayat Pendidikan"
                    description="Riwayat pendidikan tinggi dan gelar Anda. Pilih riwayat untuk mengubah data atau visibilitas."
                >
                    <x-slot:actions>
                        <a href="#tambah-pendidikan" class="df-button df-button-secondary">Tambah pendidikan</a>
                    </x-slot:actions>
                </x-ui.section-header>

                <div class="mt-6">
                    @if($higherEducations->isNotEmpty())
                        <x-ui.timeline>
                            @foreach($higherEducations as $education)
                                <x-academic.education-timeline-item :education="$education" />
                            @endforeach
                        </x-ui.timeline>
                    @else
                        <x-ui.empty-state
                            title="Mulai bangun perjalanan akademik Anda"
                            description="Tambahkan pendidikan tinggi terakhir agar riwayat akademik Anda mudah dilihat."
                            icon="book"
                        >
                            <x-slot:actions>
                                <a href="#tambah-pendidikan" class="df-button df-button-primary">Tambah Pendidikan</a>
                            </x-slot:actions>
                        </x-ui.empty-state>
                    @endif
                </div>

                @if($basicEducations->isNotEmpty())
                    <details class="mt-6 rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                        <summary class="cursor-pointer text-sm font-black text-[var(--text-primary)]">Pendidikan Dasar dan Menengah</summary>
                        <div class="mt-4 space-y-3 border-l border-[var(--border)] pl-8">
                            @foreach($basicEducations as $education)
                                <x-academic.education-timeline-item :education="$education" />
                            @endforeach
                        </div>
                    </details>
                @endif

                <details id="tambah-pendidikan" class="df-profile-add-form mt-6">
                    <summary>Tambah riwayat pendidikan <span aria-hidden="true">+</span></summary>
                    <form method="post" action="{{ route('profile.educations.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                        @csrf
                        <label class="df-profile-form-label">Jenjang
                            <select name="level" class="df-field mt-1" required>@foreach($levels as $level)<option value="{{ $level }}">{{ $level }}</option>@endforeach</select>
                        </label>
                        <label class="df-profile-form-label">Nama institusi
                            <input name="institution_name" class="df-field mt-1" required>
                        </label>
                        <label class="df-profile-form-label">Program studi
                            <input name="study_program" class="df-field mt-1">
                        </label>
                        <label class="df-profile-form-label">Gelar
                            <input name="degree" class="df-field mt-1">
                        </label>
                        <label class="df-profile-form-label">Tahun mulai
                            <input name="start_year" type="number" class="df-field mt-1">
                        </label>
                        <label class="df-profile-form-label">Tahun lulus
                            <input name="end_year" type="number" class="df-field mt-1">
                        </label>
                        <label class="df-profile-form-label">Status kelulusan
                            <select name="graduation_status" class="df-field mt-1"><option value="LULUS">Lulus</option><option value="BERJALAN">Berjalan</option><option value="TIDAK_SELESAI">Tidak selesai</option></select>
                        </label>
                        <label class="df-profile-form-label">Visibilitas
                            <select name="visibility" class="df-field mt-1"><option value="">Default aman</option><option value="PRIVATE">Private</option><option value="INTERNAL">Internal</option><option value="PUBLIC">Public</option></select>
                        </label>
                        <label class="df-profile-form-label sm:col-span-2">Judul tugas akhir, tesis, atau disertasi
                            <textarea name="thesis_title" class="df-field mt-1 min-h-24" rows="3"></textarea>
                        </label>
                        <div class="sm:col-span-2"><button class="df-button df-button-primary">Simpan pendidikan</button></div>
                    </form>
                </details>
            </x-ui.card>

            <x-ui.card id="karier" data-profile-panel="karier" class="df-profile-panel">
                <x-ui.section-header
                    eyebrow="Karier"
                    title="Karier dan Jabatan"
                    description="Lihat posisi aktif, progres jabatan fungsional, dan tugas tambahan dalam satu alur."
                />

                <div class="mt-5 border-l-2 border-[var(--brand-600)] bg-[var(--brand-50)] p-4">
                    <p class="text-xs font-bold uppercase text-[var(--brand-700)]">Jabatan Fungsional Aktif</p>
                    <h3 class="mt-2 text-2xl font-black text-[var(--text-primary)]">{{ $activeFunctional?->position_name ?: 'Belum dicatat' }}</h3>
                    <p class="mt-2 text-sm font-semibold text-[var(--text-secondary)]">TMT {{ optional($activeFunctional?->effective_date)->format('d M Y') ?: 'belum diisi' }} · KUM {{ $activeFunctional?->credit_score ?: '-' }}</p>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-base font-bold">Riwayat jabatan fungsional</h3>
                    <a href="#tambah-jabatan-fungsional" class="text-sm font-bold text-[var(--brand-700)] hover:underline">Tambah jabatan</a>
                </div>
                <div class="mt-3 space-y-3">
                    @forelse($functionalPositions as $position)
                        <div class="rounded-[var(--radius-sm)] border border-[var(--border)] p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="font-bold">{{ $position->position_name }} <span class="text-xs font-medium text-[var(--text-muted)]">{{ $position->is_active ? 'Aktif' : 'Riwayat' }}</span></p>
                                    <p class="mt-1 text-sm text-[var(--text-secondary)]">TMT {{ optional($position->effective_date)->format('d M Y') ?: 'belum diisi' }}{{ $position->unit ? ' · '.$position->unit : '' }}</p>
                                </div>
                                <span class="text-xs font-semibold text-[var(--text-muted)]">{{ ucfirst(strtolower($position->visibility)) }}</span>
                            </div>
                            @if($position->source_type === 'MANUAL')
                                <details class="df-profile-edit mt-3"><summary>Edit jabatan dan visibilitas</summary><x-academic.profile-record-form kind="functional" :record="$position" /></details>
                            @else
                                <p class="mt-2 text-xs text-[var(--text-muted)]">Data terhubung dari sistem sumber.</p>
                            @endif
                        </div>
                    @empty
                        <p class="py-3 text-sm text-[var(--text-secondary)]">Belum ada jabatan fungsional. Catat riwayat Anda untuk melengkapi profil.</p>
                    @endforelse
                </div>
                <details id="tambah-jabatan-fungsional" class="df-profile-add-form mt-4"><summary>Tambah jabatan fungsional <span aria-hidden="true">+</span></summary><x-academic.profile-record-form kind="functional" /></details>

                <div class="mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3"><h3 class="text-base font-bold">Tugas tambahan</h3><a href="#tambah-tugas-tambahan" class="text-sm font-bold text-[var(--brand-700)] hover:underline">Tambah tugas</a></div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @forelse($structuralPositions as $position)
                            <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                                <p class="font-bold text-[var(--text-primary)]">{{ $position->position_name }}</p>
                                <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $position->unit ?: 'Unit belum diisi' }} · {{ $position->is_active ? 'Aktif' : 'Riwayat' }} · {{ ucfirst(strtolower($position->visibility)) }}</p>
                                @if($position->source_type === 'MANUAL')
                                    <details class="df-profile-edit mt-3"><summary>Edit tugas dan visibilitas</summary><x-academic.profile-record-form kind="structural" :record="$position" /></details>
                                @else
                                    <p class="mt-2 text-xs text-[var(--text-muted)]">Data terhubung dari sistem sumber.</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-[var(--text-secondary)]">Belum ada tugas tambahan.</p>
                        @endforelse
                    </div>
                    <details id="tambah-tugas-tambahan" class="df-profile-add-form mt-4"><summary>Tambah tugas tambahan <span aria-hidden="true">+</span></summary><x-academic.profile-record-form kind="structural" /></details>
                </div>
            </x-ui.card>

            <x-ui.card id="keilmuan" data-profile-panel="keilmuan" class="df-profile-panel">
                <x-ui.section-header
                    eyebrow="Keilmuan"
                    title="Bidang Kepakaran"
                    description="Bidang utama, spesialisasi, dan topik riset yang menggambarkan kepakaran Anda."
                ><x-slot:actions><a href="#tambah-kepakaran" class="df-button df-button-secondary">Tambah kepakaran</a></x-slot:actions></x-ui.section-header>
                <div class="mt-5 space-y-5">
                    @forelse($expertiseAreas as $area)
                        <div class="rounded-[var(--radius-lg)] border border-[var(--border)] bg-white p-4">
                            <p class="text-sm font-black text-[var(--brand-800)]">{{ $area->primary_expertise ?: 'Bidang utama belum diisi' }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach(array_filter([$area->primary_expertise, ...($area->specializations ?? []), ...($area->research_topics ?? []), ...($area->practical_skills ?? []), ...($area->collaboration_interests ?? []), ...($area->courses ?? [])]) as $chip)
                                    <span class="rounded-full border border-[var(--brand-100)] bg-[var(--brand-50)] px-3 py-1.5 text-sm font-bold text-[var(--brand-800)]">{{ $chip }}</span>
                                @endforeach
                            </div>
                            <p class="mt-3 text-xs font-semibold text-[var(--text-muted)]">{{ ucfirst(strtolower($area->visibility)) }}</p>
                            @if($area->source_type === 'MANUAL')
                                <details class="df-profile-edit mt-3"><summary>Edit kepakaran dan visibilitas</summary><x-academic.profile-record-form kind="expertise" :record="$area" /></details>
                            @else
                                <p class="mt-2 text-xs text-[var(--text-muted)]">Data terhubung dari sistem sumber.</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-[var(--text-secondary)]">Belum ada bidang kepakaran. Tambahkan bidang utama agar profil dan CV lebih informatif.</p>
                    @endforelse
                </div>
                <details id="tambah-kepakaran" class="df-profile-add-form mt-5"><summary>Tambah bidang kepakaran <span aria-hidden="true">+</span></summary><x-academic.profile-record-form kind="expertise" /></details>
            </x-ui.card>

            <x-ui.card data-profile-panel="ringkasan" class="df-profile-panel">
                <x-ui.section-header title="Aktivitas Akademik Terkini" description="Portofolio terakhir yang ikut membentuk profil akademik." />
                <div class="mt-5 space-y-3">
                    @forelse($recentAcademicActivities as $activity)
                        <x-academic.activity-item :activity="$activity" />
                    @empty
                        <p class="text-sm text-[var(--text-secondary)]">Belum ada aktivitas akademik. <a href="{{ route('tridharma.index') }}" class="font-bold text-[var(--brand-700)] underline">Buka Tridharma</a> untuk melihat atau menambah kegiatan.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <aside class="df-profile-aside">
            <x-ui.card id="profil-publik" data-profile-panel="profil-publik" class="df-profile-panel">
                <div class="border-b border-[var(--border)] p-5">
                    <p class="df-profile-eyebrow">CV dan Portofolio</p>
                    <h2 class="mt-1 text-lg font-bold text-[var(--text-primary)]">Pilih tampilan CV</h2>
                    <p class="mt-1 text-sm leading-6 text-[var(--text-secondary)]">Semua pilihan memakai data yang sama. Pilihan hanya mengubah tampilan saat dilihat atau dicetak.</p>
                </div>
                <div class="space-y-4 p-5">
                    <p class="text-xs font-semibold text-[var(--text-muted)]">{{ $visibilitySetting->public_profile_enabled ? 'Lihat CV yang dapat dibagikan' : 'Pratinjau pribadi · belum dibagikan' }}</p>
                    <div class="df-template-grid">
                        @foreach($cvTemplates as $template => $option)
                            <a href="{{ $visibilitySetting->public_profile_enabled ? route('profile.public', ['lecturerCoreId' => $user->core_lecturer_id, 'template' => $template]) : route('profile.preview', ['template' => $template]) }}" class="df-profile-template-option">
                                <span class="df-template-preview df-template-{{ $template }}" aria-hidden="true"><span class="df-template-paper"><strong>{{ $user->name }}</strong><span>Profil Akademik</span><i></i><i></i><i></i><span>Pendidikan dan karya ilmiah</span><i></i><i></i></span></span>
                                <span class="block font-bold text-[var(--text-primary)]">{{ $option['label'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-[var(--text-secondary)]">{{ $option['description'] }}</span>
                                <span class="mt-4 flex items-center gap-2 text-sm font-semibold text-[var(--brand-700)]">Pratinjau <x-heroicon-o-arrow-right class="h-4 w-4" /></span>
                            </a>
                        @endforeach
                    </div>

                    <a href="#visibilitas" class="df-button df-button-primary w-full">Atur Data yang Tampil</a>
                    <p class="text-xs leading-5 text-[var(--text-muted)]">NIK, alamat rumah, nomor HP pribadi, nomor dokumen, dan file bukti tidak ikut tampil di CV publik.</p>
                </div>
            </x-ui.card>

            <x-ui.card id="visibilitas" data-profile-panel="visibilitas" class="df-profile-panel">
                <x-ui.section-header title="Visibilitas Profil" description="Atur bagian mana yang boleh tampil untuk publik." />
                <p class="mt-3 text-xs leading-5 text-[var(--text-secondary)]">Bagian dan itemnya harus sama-sama dipilih Public agar muncul di CV yang dibagikan. Pratinjau pribadi tetap tersedia sebelum profil publik diaktifkan.</p>
                <form method="post" action="{{ route('profile.visibility.update') }}" class="mt-5 space-y-4">
                    @csrf
                    <label class="flex min-h-12 items-center gap-3 rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-3 text-sm font-black text-[var(--text-primary)]">
                        <input type="checkbox" name="public_profile_enabled" value="1" @checked($visibilitySetting->public_profile_enabled)>
                        Aktifkan profil publik
                    </label>
                    @foreach(['education' => 'Pendidikan', 'expertise' => 'Bidang ilmu', 'identifiers' => 'Identitas ilmiah'] as $key => $label)
                        <label class="block rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-3 text-sm font-black text-[var(--text-primary)]">
                            {{ $label }}
                            <select name="section_visibility[{{ $key }}]" class="df-field mt-2">
                                @foreach(['PRIVATE' => 'Private', 'INTERNAL' => 'Internal', 'PUBLIC' => 'Public'] as $value => $text)
                                    <option value="{{ $value }}" @selected($visibilitySetting->sectionVisibility($key) === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endforeach
                    <button class="df-button df-button-primary w-full">Simpan Visibilitas</button>
                    <p class="text-xs leading-5 text-[var(--text-muted)]">NIK, alamat rumah, nomor HP pribadi, dokumen ijazah/SK, dan nomor dokumen sensitif tidak pernah dipublikasikan.</p>
                </form>
            </x-ui.card>

            <x-ui.card id="identitas-ilmiah" data-profile-panel="identitas-ilmiah" class="df-profile-panel">
                <x-ui.section-header title="Identitas Ilmiah" description="Akun akademik yang terhubung dengan profil, CV, dan portofolio otomatis Anda." />
                <div class="mt-5 space-y-3">
                    @forelse($identifiers as $identifier)
                        <x-academic.scientific-identity-card :identifier="$identifier" />
                    @empty
                        <x-ui.empty-state title="Hubungkan identitas ilmiah" description="Tambahkan SINTA, ORCID, Scopus, Google Scholar, atau identitas lain yang relevan." />
                    @endforelse
                </div>
                <form method="post" action="{{ route('profile.identifiers.store') }}" class="mt-5 space-y-4 rounded-[var(--radius-md)] border border-dashed border-[var(--border)] bg-[var(--surface-muted)]/70 p-4">
                    @csrf
                    <div>
                        <p class="text-sm font-black text-[var(--text-primary)]">Tambah identitas baru</p>
                        <p class="mt-1 text-xs leading-5 text-[var(--text-muted)]">Jika jenis yang sama sudah ada, data akan diperbarui agar profil tetap rapi.</p>
                    </div>
                    <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                        Jenis identitas
                        <select name="identifier_type" class="df-field mt-2" required>
                            @foreach($identifierTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                        ID atau username
                        <input name="identifier_value" class="df-field mt-2" placeholder="Contoh: 6719210" required>
                    </label>
                    <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                        URL profil
                        <input name="profile_url" class="df-field mt-2" placeholder="https://...">
                    </label>
                    <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                        Visibilitas
                        <select name="visibility" class="df-field mt-2" required>
                            <option value="PRIVATE">Private</option>
                            <option value="INTERNAL">Internal</option>
                            <option value="PUBLIC">Public</option>
                        </select>
                    </label>
                    <p class="text-xs leading-5 text-[var(--text-muted)]">Private hanya untuk Anda dan admin, Internal untuk kebutuhan sistem kampus, Public tampil di CV publik.</p>
                    <button class="df-button df-button-secondary w-full">Simpan Identitas</button>
                </form>
            </x-ui.card>

            <x-ui.card id="sertifikasi" data-profile-panel="sertifikasi" class="df-profile-panel">
                <x-ui.section-header title="Sertifikasi" description="Sertifikasi akademik dan profesi yang tercatat." />
                <div class="mt-5 space-y-3">
                    @forelse($certifications as $certification)
                        <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                            <p class="font-black text-[var(--text-primary)]">{{ $certification->name }}</p>
                            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $certification->issuer ?: 'Penerbit belum diisi' }} · {{ $certification->status }}</p>
                            <p class="mt-1 text-xs text-[var(--text-muted)]">{{ ucfirst(strtolower($certification->visibility)) }}</p>
                            @if($certification->source_type === 'MANUAL')
                                <details class="df-profile-edit mt-3"><summary>Edit sertifikasi dan visibilitas</summary><x-academic.profile-record-form kind="certification" :record="$certification" /></details>
                            @else
                                <p class="mt-2 text-xs text-[var(--text-muted)]">Data terhubung dari sistem sumber.</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-[var(--text-secondary)]">Belum ada sertifikasi. Tambahkan sertifikasi yang relevan untuk profil atau CV.</p>
                    @endforelse
                </div>
                <details id="tambah-sertifikasi" class="df-profile-add-form mt-5"><summary>Tambah sertifikasi <span aria-hidden="true">+</span></summary><x-academic.profile-record-form kind="certification" /></details>
            </x-ui.card>

        </aside>
    </section>
</div>
@endsection
