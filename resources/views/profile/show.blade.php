@extends('layouts.app', ['title' => 'Profil Akademik'])

@section('breadcrumb', 'Profil Akademik')

@php
    $basicLevels = ['SD', 'SMP', 'SMA', 'SMK'];
    $higherEducations = $educations->reject(fn ($education) => in_array($education->level, $basicLevels, true));
    $basicEducations = $educations->filter(fn ($education) => in_array($education->level, $basicLevels, true));
    $activeFunctional = $functionalPositions->firstWhere('is_active', true) ?? $functionalPositions->first();
    $milestones = ['S1', 'Profesi', 'S2', 'S3'];
    $completedMilestones = $educations->pluck('level')->all();
    $careerStages = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];
@endphp

@section('content')
<div class="space-y-6">
    <x-academic.academic-profile-header
        :user="$user"
        :completeness="$completeness"
        :functional-positions="$functionalPositions"
        :expertise-areas="$expertiseAreas"
    />

    <nav class="df-segmented" aria-label="Navigasi Profil Akademik">
        @foreach([
            'ringkasan' => 'Ringkasan',
            'pendidikan' => 'Pendidikan',
            'karier' => 'Karier',
            'keilmuan' => 'Keilmuan',
            'identitas-ilmiah' => 'Identitas Ilmiah',
            'visibilitas' => 'Visibilitas',
        ] as $anchor => $label)
            <a href="#{{ $anchor }}" class="shrink-0 rounded-[var(--radius-sm)] px-4 py-2.5 text-sm font-extrabold text-[var(--text-secondary)] hover:bg-white">{{ $label }}</a>
        @endforeach
    </nav>

    <section id="ringkasan" class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="min-w-0 space-y-6">
            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header
                    eyebrow="Identitas"
                    title="Tentang Dosen"
                    description="Profil ini menggabungkan identitas Core Farmasi dan data akademik yang Anda kelola sendiri."
                />
                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <x-ui.stat label="Status" :value="$user->is_active ? 'Aktif' : 'Nonaktif'" tone="success" />
                    <x-ui.stat label="Jabatan" :value="$activeFunctional?->position_name ?: '-'" caption="Fungsional aktif" />
                    <x-ui.stat label="Email" :value="$user->email ?: '-'" caption="Institusi" />
                    <x-ui.stat label="Profil" :value="$completeness['percent'].'%'" caption="Kelengkapan" tone="info" />
                </div>
            </x-ui.card>

            <x-ui.card id="pendidikan" class="p-5 sm:p-6">
                <x-ui.section-header
                    eyebrow="Journey"
                    title="Riwayat Pendidikan"
                    description="Jenjang pendidikan tinggi ditampilkan sebagai milestone utama, sementara pendidikan dasar tetap tersimpan secara aman."
                >
                    <x-slot:actions>
                        <a href="#tambah-pendidikan" class="df-button df-button-secondary">Tambah Pendidikan</a>
                    </x-slot:actions>
                </x-ui.section-header>

                <div class="mt-5 rounded-[var(--radius-lg)] bg-[var(--surface-muted)]/70 p-4">
                    <div class="grid gap-3 sm:grid-cols-4">
                        @foreach($milestones as $milestone)
                            @php($done = in_array($milestone, $completedMilestones, true))
                            <div class="rounded-[var(--radius-md)] border {{ $done ? 'border-emerald-100 bg-emerald-50 text-emerald-900' : 'border-dashed border-[var(--border)] bg-white text-[var(--text-muted)]' }} p-3 text-center">
                                <p class="text-lg font-black">{{ $milestone }}</p>
                                <p class="mt-1 text-xs font-bold">{{ $done ? 'Tercatat' : 'Belum tercatat' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

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
                            description="Tambahkan pendidikan tinggi terakhir terlebih dahulu agar profil akademik lebih siap diverifikasi."
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
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach($basicEducations as $education)
                                <div class="rounded-[var(--radius-sm)] bg-[var(--surface-muted)] p-3">
                                    <p class="font-black text-[var(--text-primary)]">{{ $education->level }} · {{ $education->institution_name }}</p>
                                    <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $education->end_year ?: 'Tahun belum diisi' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </x-ui.card>

            <x-ui.card id="karier" class="p-5 sm:p-6">
                <x-ui.section-header
                    eyebrow="Karier"
                    title="Karier dan Jabatan"
                    description="Lihat posisi aktif, progres jabatan fungsional, dan tugas tambahan dalam satu alur."
                />

                <div class="mt-5 rounded-[var(--radius-lg)] bg-[linear-gradient(135deg,var(--brand-50),white)] p-5 ring-1 ring-[var(--border)]">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[var(--brand-700)]">Jabatan Fungsional Aktif</p>
                    <h3 class="mt-2 text-2xl font-black text-[var(--text-primary)]">{{ $activeFunctional?->position_name ?: 'Belum dicatat' }}</h3>
                    <p class="mt-2 text-sm font-semibold text-[var(--text-secondary)]">TMT {{ optional($activeFunctional?->effective_date)->format('d M Y') ?: 'belum diisi' }} · KUM {{ $activeFunctional?->credit_score ?: '-' }}</p>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <div>
                        <p class="mb-4 text-sm font-black text-[var(--text-primary)]">Progression</p>
                        <div class="grid gap-2 sm:grid-cols-4 lg:grid-cols-2">
                            @foreach($careerStages as $stage)
                                @php($reached = $functionalPositions->contains(fn ($position) => str($position->position_name)->contains($stage, true)))
                                <div class="rounded-[var(--radius-md)] border {{ $reached ? 'border-emerald-100 bg-emerald-50 text-emerald-900' : 'border-dashed border-[var(--border)] bg-white text-[var(--text-muted)]' }} p-3">
                                    <p class="text-sm font-black">{{ $stage }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="mb-4 text-sm font-black text-[var(--text-primary)]">Riwayat Jabatan</p>
                        @if($functionalPositions->isNotEmpty())
                            <x-ui.timeline>
                                @foreach($functionalPositions as $position)
                                    <x-academic.career-timeline-item
                                        :title="$position->position_name"
                                        :caption="(optional($position->effective_date)->format('d M Y') ?: 'TMT belum diisi').' · '.($position->credit_score ? 'KUM '.$position->credit_score : 'KUM belum diisi')"
                                        :active="$position->is_active"
                                    />
                                @endforeach
                            </x-ui.timeline>
                        @else
                            <x-ui.empty-state title="Jabatan fungsional aktif belum dicatat" description="Data jabatan dapat dikelola admin melalui ruang kontrol." />
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <p class="mb-4 text-sm font-black text-[var(--text-primary)]">Tugas Tambahan</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse($structuralPositions as $position)
                            <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                                <p class="font-black text-[var(--text-primary)]">{{ $position->position_name }}</p>
                                <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $position->unit ?: 'Unit belum diisi' }} · {{ $position->is_active ? 'Aktif' : 'Riwayat' }}</p>
                            </div>
                        @empty
                            <x-ui.empty-state title="Belum ada jabatan struktural" description="Tugas tambahan atau jabatan struktural akan tampil di bagian ini." />
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card id="keilmuan" class="p-5 sm:p-6">
                <x-ui.section-header
                    eyebrow="Keilmuan"
                    title="Bidang Kepakaran"
                    description="Bidang utama, spesialisasi, dan topik riset divisualkan sebagai research fingerprint."
                />
                <div class="mt-5 space-y-5">
                    @forelse($expertiseAreas as $area)
                        <div class="rounded-[var(--radius-lg)] border border-[var(--border)] bg-white p-4">
                            <p class="text-sm font-black text-[var(--brand-800)]">{{ $area->primary_expertise ?: 'Bidang utama belum diisi' }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach(array_filter([$area->primary_expertise, ...($area->specializations ?? []), ...($area->research_topics ?? []), ...($area->practical_skills ?? []), ...($area->collaboration_interests ?? []), ...($area->courses ?? [])]) as $chip)
                                    <span class="rounded-full border border-[var(--brand-100)] bg-[var(--brand-50)] px-3 py-1.5 text-sm font-bold text-[var(--brand-800)]">{{ $chip }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="Bidang keilmuan belum diisi" description="Tambahkan bidang utama, spesialisasi, dan topik riset agar profil lebih mudah ditemukan untuk kolaborasi." />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Aktivitas Akademik Terkini" description="Portofolio terakhir yang ikut membentuk profil akademik." />
                <div class="mt-5 space-y-3">
                    @forelse($recentAcademicActivities as $activity)
                        <x-academic.activity-item :activity="$activity" />
                    @empty
                        <x-ui.empty-state title="Belum ada aktivitas akademik" description="Aktivitas dari Tridharma akan tampil di sini setelah tercatat." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <aside class="min-w-0 space-y-5">
            <x-ui.card class="overflow-hidden">
                <div class="bg-[radial-gradient(circle_at_85%_10%,rgba(45,212,191,0.28),transparent_28%),linear-gradient(135deg,var(--brand-950),#123f66)] p-5 text-white">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-white/65">CV dan Portofolio</p>
                    <h2 class="mt-2 text-xl font-black leading-tight">Sumber otomatis profil akademik</h2>
                    <p class="mt-2 text-sm leading-6 text-white/75">Saat ada kebutuhan CV, portofolio, atau profil dosen, data terkurasi akan diambil dari halaman ini.</p>
                </div>
                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-[var(--radius-sm)] bg-[var(--brand-50)] p-3">
                            <p class="text-lg font-black text-[var(--brand-900)]">{{ $educations->count() }}</p>
                            <p class="mt-1 text-[11px] font-bold text-[var(--text-muted)]">Pendidikan</p>
                        </div>
                        <div class="rounded-[var(--radius-sm)] bg-[var(--research-soft)] p-3">
                            <p class="text-lg font-black text-teal-800">{{ $recentAcademicActivities->count() }}</p>
                            <p class="mt-1 text-[11px] font-bold text-[var(--text-muted)]">Aktivitas</p>
                        </div>
                        <div class="rounded-[var(--radius-sm)] bg-[var(--service-soft)] p-3">
                            <p class="text-lg font-black text-amber-800">{{ $identifiers->count() }}</p>
                            <p class="mt-1 text-[11px] font-bold text-[var(--text-muted)]">ID Ilmiah</p>
                        </div>
                    </div>

                    @if($visibilitySetting->public_profile_enabled)
                        <x-ui.button :href="route('profile.public', $user->core_lecturer_id)" variant="secondary" class="w-full">Buka CV Publik</x-ui.button>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['akademik' => 'Akademik', 'impact' => 'Impact', 'editorial' => 'Editorial'] as $template => $label)
                                <a href="{{ route('profile.public', ['lecturerCoreId' => $user->core_lecturer_id, 'template' => $template]) }}" class="rounded-[var(--radius-sm)] border border-[var(--border)] bg-white px-2 py-2 text-center text-[11px] font-black text-[var(--brand-800)] hover:bg-[var(--brand-50)]">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-[var(--radius-md)] border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-950">
                            Aktifkan profil publik agar CV otomatis bisa dibagikan tanpa data sensitif.
                        </div>
                    @endif

                    <a href="#visibilitas" class="df-button df-button-primary w-full">Atur Data yang Tampil</a>
                    <p class="text-xs leading-5 text-[var(--text-muted)]">NIK, alamat rumah, nomor HP pribadi, nomor dokumen, dan file bukti tidak ikut tampil di CV publik.</p>
                </div>
            </x-ui.card>

            <x-academic.profile-completeness :completeness="$completeness" />

            <x-ui.card id="visibilitas" class="p-5">
                <x-ui.section-header title="Visibilitas Profil" description="Atur bagian mana yang boleh tampil untuk publik." />
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

            <x-ui.card id="identitas-ilmiah" class="p-5">
                <x-ui.section-header title="Identitas Ilmiah" description="Akun akademik yang terhubung dengan profil Anda." />
                <div class="mt-5 space-y-3">
                    @forelse($identifiers as $identifier)
                        <x-academic.scientific-identity-card :identifier="$identifier" />
                    @empty
                        <x-ui.empty-state title="Hubungkan identitas ilmiah" description="Tambahkan SINTA, ORCID, Scopus, Google Scholar, atau identitas lain yang relevan." />
                    @endforelse
                </div>
                <form method="post" action="{{ route('profile.identifiers.store') }}" class="mt-5 space-y-3">
                    @csrf
                    <select name="identifier_type" class="df-field" required>
                        @foreach($identifierTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                    <input name="identifier_value" class="df-field" placeholder="ID atau username" required>
                    <input name="profile_url" class="df-field" placeholder="https://...">
                    <select name="visibility" class="df-field">
                        <option value="INTERNAL">Internal</option>
                        <option value="PUBLIC">Public</option>
                        <option value="PRIVATE">Private</option>
                    </select>
                    <button class="df-button df-button-secondary w-full">Simpan Identitas</button>
                </form>
            </x-ui.card>

            <x-ui.card class="p-5">
                <x-ui.section-header title="Sertifikasi" description="Sertifikasi akademik dan profesi yang tercatat." />
                <div class="mt-5 space-y-3">
                    @forelse($certifications as $certification)
                        <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                            <p class="font-black text-[var(--text-primary)]">{{ $certification->name }}</p>
                            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $certification->issuer ?: 'Penerbit belum diisi' }} · {{ $certification->status }}</p>
                        </div>
                    @empty
                        <x-ui.empty-state title="Belum ada sertifikasi" description="Sertifikasi profesi dan akademik akan tampil setelah dicatat." />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card id="tambah-pendidikan" class="p-5">
                <x-ui.section-header title="Tambah Pendidikan" description="Data manual masuk sebagai draft dan tetap mengikuti visibilitas aman." />
                <form method="post" action="{{ route('profile.educations.store') }}" class="mt-5 space-y-3">
                    @csrf
                    <select name="level" class="df-field" required>
                        @foreach($levels as $level)
                            <option value="{{ $level }}">{{ $level }}</option>
                        @endforeach
                    </select>
                    <input name="institution_name" class="df-field" placeholder="Nama institusi" required>
                    <input name="study_program" class="df-field" placeholder="Program studi">
                    <input name="degree" class="df-field" placeholder="Gelar">
                    <div class="grid grid-cols-2 gap-3">
                        <input name="start_year" type="number" class="df-field" placeholder="Mulai">
                        <input name="end_year" type="number" class="df-field" placeholder="Lulus">
                    </div>
                    <select name="graduation_status" class="df-field">
                        <option value="LULUS">Lulus</option>
                        <option value="BERJALAN">Berjalan</option>
                        <option value="TIDAK_SELESAI">Tidak selesai</option>
                    </select>
                    <textarea name="thesis_title" class="df-field min-h-28" rows="3" placeholder="Judul tugas akhir/tesis/disertasi"></textarea>
                    <select name="visibility" class="df-field">
                        <option value="">Default aman</option>
                        <option value="PRIVATE">Private</option>
                        <option value="INTERNAL">Internal</option>
                        <option value="PUBLIC">Public</option>
                    </select>
                    <button class="df-button df-button-primary w-full">Simpan Pendidikan</button>
                </form>
            </x-ui.card>

            <x-ui.card id="profil-publik" class="p-5">
                <x-ui.section-header title="Profil Publik" description="CV live yang dapat dibagikan tanpa membuka data sensitif." />
                @if($visibilitySetting->public_profile_enabled)
                    <div class="mt-5 space-y-3">
                        <x-ui.button :href="route('profile.public', $user->core_lecturer_id)" variant="secondary" class="w-full">Lihat CV Publik</x-ui.button>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['akademik' => 'Akademik', 'impact' => 'Impact', 'editorial' => 'Editorial'] as $template => $label)
                                <a href="{{ route('profile.public', ['lecturerCoreId' => $user->core_lecturer_id, 'template' => $template]) }}" class="rounded-[var(--radius-sm)] border border-[var(--border)] bg-white px-2 py-2 text-center text-[11px] font-black text-[var(--brand-800)] hover:bg-[var(--brand-50)]">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                        <p class="text-xs leading-5 text-[var(--text-muted)]">Gunakan tombol cetak di halaman publik untuk menyimpan sebagai PDF.</p>
                    </div>
                @else
                    <x-ui.empty-state class="mt-5" title="Profil publik belum aktif" description="Aktifkan visibilitas publik sebelum profil dibagikan." />
                @endif
            </x-ui.card>
        </aside>
    </section>
</div>
@endsection
