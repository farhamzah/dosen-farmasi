@extends('layouts.app', ['title' => 'Dashboard Dosen'])

@section('breadcrumb', 'Dashboard Dosen')

@php
    $actionTotal = $revisionCount + $draftCount;
    $portfolioProgress = $portfolioCount > 0 ? (int) round(($verifiedCount / max(1, $portfolioCount)) * 100) : 0;
    $workload = [
        ['label' => 'Draft', 'value' => $draftCount, 'tone' => 'info'],
        ['label' => 'Revisi', 'value' => $revisionCount, 'tone' => 'warning'],
        ['label' => 'Menunggu', 'value' => $pendingCount, 'tone' => 'brand'],
        ['label' => 'Valid', 'value' => $verifiedCount, 'tone' => 'success'],
    ];
    $readiness = [
        ['label' => 'Portofolio', 'value' => $portfolioCount, 'note' => 'kegiatan tercatat'],
        ['label' => 'Dokumen', 'value' => $documentCount, 'note' => 'lampiran tersimpan'],
        ['label' => 'Agenda', 'value' => $upcomingEvents->count(), 'note' => 'jadwal mendatang'],
    ];
@endphp

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Dashboard Dosen"
        title="Selamat bekerja, {{ auth()->user()->name }}"
        description="Satu ruang kerja untuk memantau portofolio, dokumen, agenda, dan tindak lanjut akademik tanpa harus berpindah konteks."
    >
        <x-slot:actions>
            <x-ui.button :href="route('dosen.portfolio.create')">Tambah Aktivitas</x-ui.button>
            <x-ui.button :href="route('tridharma.index')" variant="secondary">Buka Tridharma</x-ui.button>
        </x-slot:actions>
        <x-slot:aside>
            @if($portfolioCount > 0)
                <div class="rounded-[var(--radius-xl)] bg-white/86 p-5 shadow-sm ring-1 ring-[var(--border)]">
                    <p class="text-sm font-bold text-[var(--text-secondary)]">Kesiapan Portofolio</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--text-primary)]">{{ $portfolioProgress }}%</p>
                    <div class="df-progress-track mt-4 h-2.5">
                        <div class="df-progress-fill bg-[var(--brand-600)]" style="width: {{ $portfolioProgress }}%"></div>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-[var(--text-secondary)]">{{ $verifiedCount }} dari {{ $portfolioCount }} kegiatan sudah valid.</p>
                </div>
            @else
                <div class="rounded-[var(--radius-xl)] bg-[linear-gradient(135deg,#ffffff,#eef4ff)] p-5 shadow-sm ring-1 ring-[var(--border)]">
                    <p class="text-sm font-bold text-[var(--brand-900)]">Mulai dari satu aktivitas</p>
                    <p class="mt-2 text-sm leading-6 text-[var(--text-secondary)]">Dashboard siap menampilkan progres begitu portofolio, dokumen, atau agenda pertama tersedia.</p>
                    <a href="{{ route('dosen.portfolio.create') }}" class="mt-4 inline-flex text-sm font-bold text-[var(--brand-700)]">Tambah aktivitas pertama -&gt;</a>
                </div>
            @endif
        </x-slot:aside>
    </x-ui.page-header>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($workload as $stat)
            <x-ui.stat :label="$stat['label']" :value="$stat['value']" :tone="$stat['tone']" />
        @endforeach
    </section>

    <section class="grid gap-4 lg:grid-cols-3">
        @foreach($readiness as $item)
            <div class="df-card flex items-center justify-between p-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{{ $item['label'] }}</p>
                    <p class="mt-2 text-sm font-semibold text-[var(--text-secondary)]">{{ $item['note'] }}</p>
                </div>
                <p class="text-3xl font-black text-[var(--brand-900)]">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
        <x-ui.card class="overflow-hidden p-5 sm:p-6">
            <x-ui.section-header
                eyebrow="Fokus"
                title="Perlu Tindakan Saya"
                description="Draft dan revisi yang paling baik diselesaikan sebelum masuk alur verifikasi."
            >
                <x-slot:actions>
                    <x-ui.button :href="route('dosen.portfolio.index')" variant="secondary">Lihat Semua</x-ui.button>
                </x-slot:actions>
            </x-ui.section-header>

            <div class="mt-5 grid gap-4 lg:grid-cols-[14rem_minmax(0,1fr)]">
                <div class="rounded-[var(--radius-xl)] bg-[linear-gradient(135deg,var(--brand-900),var(--brand-700))] p-5 text-white shadow-[var(--shadow-floating)]">
                    <p class="text-xs font-bold text-blue-100">Prioritas</p>
                    <p class="mt-4 text-5xl font-bold">{{ $actionTotal }}</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-blue-50">item membutuhkan perhatian pribadi.</p>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-white/20">
                        <div class="h-full rounded-full bg-white" style="width: {{ min(100, $actionTotal * 20) }}%"></div>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($actionItems as $activity)
                        <x-academic.activity-item :activity="$activity" />
                    @empty
                        <x-ui.empty-state
                            title="Tidak ada tindakan tertunda"
                            description="Semua draft dan revisi pribadi sedang rapi. Kegiatan baru dapat ditambahkan kapan saja."
                        >
                            <x-slot:actions>
                                <x-ui.button :href="route('dosen.portfolio.create')" variant="secondary">Tambah Aktivitas</x-ui.button>
                            </x-slot:actions>
                        </x-ui.empty-state>
                    @endforelse
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-5 sm:p-6">
            <x-ui.section-header eyebrow="Jalur Cepat" title="Ruang Kerja" description="Akses yang paling sering dipakai dosen." />
            <div class="mt-5 grid gap-3">
                <a href="{{ route('tridharma.index') }}" class="df-card-muted df-interactive flex items-center justify-between p-4">
                    <span>
                        <span class="block font-bold text-[var(--text-primary)]">Portofolio Tridharma</span>
                        <span class="mt-1 block text-sm text-[var(--text-secondary)]">Ringkasan pendidikan, penelitian, pengabdian.</span>
                    </span>
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-white font-bold text-[var(--brand-700)] ring-1 ring-[var(--border)]">-&gt;</span>
                </a>
                <a href="{{ route('profile.show') }}" class="df-card-muted df-interactive flex items-center justify-between p-4">
                    <span>
                        <span class="block font-bold text-[var(--text-primary)]">Profil Akademik</span>
                        <span class="mt-1 block text-sm text-[var(--text-secondary)]">Identitas, pendidikan, karier, dan kepakaran.</span>
                    </span>
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-white font-bold text-[var(--brand-700)] ring-1 ring-[var(--border)]">-&gt;</span>
                </a>
                <a href="{{ route('dosen.documents.index') }}" class="df-card-muted df-interactive flex items-center justify-between p-4">
                    <span>
                        <span class="block font-bold text-[var(--text-primary)]">Dokumen</span>
                        <span class="mt-1 block text-sm text-[var(--text-secondary)]">{{ $documentCount }} dokumen tercatat.</span>
                    </span>
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-white font-bold text-[var(--brand-700)] ring-1 ring-[var(--border)]">-&gt;</span>
                </a>
            </div>
        </x-ui.card>
    </section>

    <section class="grid gap-5 xl:grid-cols-3">
        <x-ui.card class="p-5">
            <x-ui.section-header title="Aktivitas Terbaru" description="Riwayat portofolio yang terakhir tercatat." />
            <div class="mt-5 space-y-3">
                @forelse($recentActivities as $activity)
                    <x-academic.activity-item :activity="$activity" />
                @empty
                    <x-ui.empty-state title="Belum ada aktivitas" description="Mulai bangun catatan akademik dari tombol Tambah Aktivitas." />
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <x-ui.section-header title="Agenda Mendatang" description="Jadwal akademik yang perlu disiapkan." />
            <div class="mt-5 space-y-3">
                @forelse($upcomingEvents as $event)
                    <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                        <p class="font-bold text-[var(--text-primary)]">{{ $event->title }}</p>
                        <p class="mt-1 text-sm font-semibold text-[var(--text-secondary)]">{{ optional($event->starts_at)->format('d M Y H:i') }}</p>
                    </div>
                @empty
                    <x-ui.empty-state title="Tidak ada agenda mendatang" description="Agenda dari integrasi akan tampil di sini setelah tersedia." icon="agenda" />
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <x-ui.section-header title="Kontribusi Semester Ini" description="Komposisi pekerjaan yang sudah tercatat." />
            <div class="mt-5 space-y-4">
                @foreach($workload as $stat)
                    @php($width = $portfolioCount > 0 ? min(100, (int) round(($stat['value'] / max(1, $portfolioCount)) * 100)) : 0)
                    <div>
                        <div class="flex items-center justify-between text-sm font-bold text-[var(--text-secondary)]">
                            <span>{{ $stat['label'] }}</span>
                            <span>{{ $stat['value'] }}</span>
                        </div>
                        <div class="df-progress-track mt-2 h-2">
                            <div class="df-progress-fill bg-[var(--brand-600)]" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </section>
</div>
@endsection
