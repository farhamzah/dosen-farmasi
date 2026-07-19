@extends('layouts.app', ['title' => $activity->title])

@section('breadcrumb', 'Detail Portofolio')

@php
    $status = $activity->verification_status;
    $statusTone = str_contains($status, 'VERIFIED') ? 'success' : (str_contains($status, 'REVISION') ? 'warning' : (str_contains($status, 'REJECTED') ? 'danger' : 'info'));
    $sourceLabel = match (true) {
        $activity->source_app === 'm8-ui-demo' => 'Data Demo',
        $activity->source_type === 'MANUAL' => 'Input Mandiri',
        filled($activity->source_app) => 'Sistem Terhubung',
        default => 'Sistem',
    };
    $period = (optional($activity->start_date)->format('d M Y') ?: 'Tanggal mulai belum diisi').' - '.(optional($activity->end_date)->format('d M Y') ?: 'Selesai belum diisi');
@endphp

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="{{ $activity->category?->name ?? 'Tanpa kategori' }}"
        title="{{ $activity->title }}"
        description="{{ $activity->description ?: 'Deskripsi belum diisi. Lengkapi narasi singkat agar aktivitas mudah diverifikasi.' }}"
    >
        <x-slot:actions>
            <x-ui.badge :tone="$statusTone">{{ str($status)->replace('_', ' ')->title() }}</x-ui.badge>
            @can('update', $activity)
                <x-ui.button :href="route('dosen.portfolio.edit', $activity)" variant="secondary">Edit</x-ui.button>
            @endcan
        </x-slot:actions>
        <x-slot:aside>
            <div class="rounded-[var(--radius-lg)] bg-white/86 p-5 ring-1 ring-[var(--border)]">
                <p class="text-sm font-bold text-[var(--text-secondary)]">Sumber</p>
                <p class="mt-2 text-xl font-bold text-[var(--text-primary)]">{{ $sourceLabel }}</p>
                <p class="mt-3 text-sm leading-6 text-[var(--text-secondary)]">{{ $period }}</p>
            </div>
        </x-slot:aside>
    </x-ui.page-header>

    @if($activity->revision_reason || $activity->rejection_reason)
        <div class="rounded-[var(--radius-lg)] border border-rose-200 bg-rose-50 p-5 text-sm font-semibold leading-6 text-rose-800">
            {{ $activity->revision_reason ?: $activity->rejection_reason }}
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Tipe" :value="$activity->activity_type ?: '-'" tone="brand" />
        <x-ui.stat label="Peran" :value="$activity->lecturer_role ?: '-'" tone="info" />
        <x-ui.stat label="Tahun Akademik" :value="$activity->academic_year ?: '-'" tone="neutral" />
        <x-ui.stat label="Dokumen" :value="$activity->documents->count()" tone="success" />
    </section>

    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="space-y-5">
            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Ringkasan Kegiatan" description="Informasi inti yang digunakan dalam proses validasi portofolio." />
                <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="df-card-muted p-4">
                        <dt class="text-xs font-bold text-[var(--text-muted)]">Semester</dt>
                        <dd class="mt-1 font-bold text-[var(--text-primary)]">{{ $activity->semester ?: '-' }}</dd>
                    </div>
                    <div class="df-card-muted p-4">
                        <dt class="text-xs font-bold text-[var(--text-muted)]">Visibilitas</dt>
                        <dd class="mt-1 font-bold text-[var(--text-primary)]">{{ str($activity->visibility)->title() }}</dd>
                    </div>
                    <div class="df-card-muted p-4">
                        <dt class="text-xs font-bold text-[var(--text-muted)]">Institusi/Mitra</dt>
                        <dd class="mt-1 font-bold text-[var(--text-primary)]">{{ $activity->institution_name ?: '-' }}</dd>
                    </div>
                    <div class="df-card-muted p-4">
                        <dt class="text-xs font-bold text-[var(--text-muted)]">Lokasi</dt>
                        <dd class="mt-1 font-bold text-[var(--text-primary)]">{{ $activity->location ?: '-' }}</dd>
                    </div>
                </dl>
                @if($activity->personal_notes)
                    <div class="mt-5 rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                        <p class="text-sm font-bold text-[var(--text-muted)]">Catatan pribadi</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[var(--text-secondary)]">{{ $activity->personal_notes }}</p>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Peserta dan Kontributor" description="Dosen, mahasiswa, mitra, atau institusi yang terlibat dalam kegiatan." />
                <div class="mt-5 space-y-3">
                    @forelse($activity->participants as $participant)
                        <div class="flex items-center justify-between gap-4 rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                            <div class="min-w-0">
                                <p class="truncate font-bold text-[var(--text-primary)]">{{ $participant->external_name ?: $participant->student_name ?: $participant->institution_name ?: $participant->core_dosen_id }}</p>
                                <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ str($participant->participant_type)->replace('_', ' ')->title() }}</p>
                            </div>
                            <x-ui.badge>{{ $participant->role }}</x-ui.badge>
                        </div>
                    @empty
                        <x-ui.empty-state title="Belum ada kontributor" description="Tambahkan peserta jika aktivitas melibatkan mahasiswa, dosen lain, atau mitra." />
                    @endforelse
                </div>

                @if(auth()->user()->can('update', $activity) || auth()->user()->isAdmin())
                    <form method="post" action="{{ route('dosen.portfolio.participants.store', $activity) }}" class="mt-5 grid gap-3 rounded-[var(--radius-md)] border border-[var(--border)] bg-[var(--surface-muted)]/45 p-4 md:grid-cols-[12rem_minmax(0,1fr)_12rem_auto]">
                        @csrf
                        <select name="participant_type" class="df-field">
                            <option value="EXTERNAL_PERSON">Orang eksternal</option>
                            <option value="INTERNAL_LECTURER">Dosen internal</option>
                            <option value="STUDENT">Mahasiswa</option>
                            <option value="INSTITUTION">Institusi</option>
                        </select>
                        <input name="external_name" placeholder="Nama peserta atau mitra" class="df-field">
                        <input name="role" placeholder="Peran" class="df-field" required>
                        <button class="df-button df-button-secondary">Tambah</button>
                    </form>
                @endif
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card class="p-5">
                <x-ui.section-header title="Aksi" description="Tindakan yang tersedia sesuai status dan peran Anda." />
                <div class="mt-5 grid gap-3">
                    @if(in_array($activity->verification_status, ['DRAFT', 'REVISION_REQUIRED'], true))
                        <form method="post" action="{{ route('dosen.portfolio.submit', $activity) }}">
                            @csrf
                            <button class="df-button df-button-primary w-full">Ajukan Verifikasi</button>
                        </form>
                    @endif
                    @can('verify', $activity)
                        <form method="post" action="{{ route('dosen.portfolio.verify', $activity) }}">
                            @csrf
                            <button class="df-button df-button-primary w-full">Verifikasi</button>
                        </form>
                    @endcan
                    @can('archive', $activity)
                        <form method="post" action="{{ route('dosen.portfolio.archive', $activity) }}">
                            @csrf
                            <button class="df-button df-button-secondary w-full">Arsipkan</button>
                        </form>
                    @endcan
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <x-ui.section-header title="Dokumen Pendukung" description="Bukti yang terhubung dengan aktivitas ini." />
                <div class="mt-5 space-y-3">
                    @forelse($activity->documents as $document)
                        <a href="{{ route('documents.download', $document) }}" class="df-card-muted df-interactive block p-4">
                            <p class="font-bold text-[var(--text-primary)]">{{ $document->title }}</p>
                            <p class="mt-1 truncate text-xs font-semibold text-[var(--text-muted)]">{{ $document->original_filename }}</p>
                        </a>
                    @empty
                        <x-ui.empty-state title="Belum ada dokumen" description="Unggah dokumen pendukung dari menu Dokumen." icon="document" />
                    @endforelse
                </div>
            </x-ui.card>
        </aside>
    </section>

    @can('verify', $activity)
        <section class="grid gap-5 lg:grid-cols-2">
            <form method="post" action="{{ route('dosen.portfolio.revision', $activity) }}" class="df-card p-5">
                @csrf
                <x-ui.section-header title="Minta Revisi" description="Berikan alasan yang spesifik agar dosen dapat memperbaiki data." />
                <textarea name="reason" required class="df-field mt-4 min-h-28"></textarea>
                <button class="df-button df-button-secondary mt-4">Kirim Revisi</button>
            </form>
            <form method="post" action="{{ route('dosen.portfolio.reject', $activity) }}" class="df-card p-5">
                @csrf
                <x-ui.section-header title="Tolak Aktivitas" description="Gunakan hanya jika aktivitas tidak dapat diterima sebagai portofolio." />
                <textarea name="reason" required class="df-field mt-4 min-h-28"></textarea>
                <button class="df-button df-button-primary mt-4">Tolak</button>
            </form>
        </section>
    @endcan

    <section class="df-card p-5 sm:p-6">
        <x-ui.section-header title="Riwayat Verifikasi" description="Jejak perubahan status untuk audit internal." />
        <div class="df-timeline mt-5 space-y-4">
            @forelse($activity->histories as $history)
                <div class="relative pl-10">
                    <span class="absolute left-[0.65rem] top-1 h-3 w-3 rounded-full bg-[var(--brand-600)] ring-4 ring-[var(--brand-50)]"></span>
                    <div class="rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4">
                        <p class="font-bold text-[var(--text-primary)]">{{ $history->from_status ?: '-' }} ke {{ $history->to_status }}</p>
                        <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ optional($history->created_at)->format('d M Y H:i') }} oleh {{ $history->actor?->name ?? $history->actor_role ?? 'Sistem' }}</p>
                        @if($history->reason || $history->notes)
                            <p class="mt-2 text-sm leading-6 text-[var(--text-secondary)]">{{ $history->reason ?: $history->notes }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="Belum ada riwayat" description="Riwayat akan dibuat saat status aktivitas berubah." />
            @endforelse
        </div>
    </section>

    @if($activity->isSystemVerified())
        <section class="df-card p-5 sm:p-6">
            <x-ui.section-header title="Laporan Kesalahan Data" description="Gunakan bila data otomatis berbeda dari kondisi sebenarnya." />
            <form method="post" action="{{ route('dosen.portfolio.issue-reports.store', $activity) }}" class="mt-5 grid gap-3">
                @csrf
                <input name="issue_type" placeholder="Jenis masalah" class="df-field" required>
                <textarea name="description" placeholder="Deskripsi masalah" class="df-field min-h-28" required></textarea>
                <textarea name="expected_value" placeholder="Nilai yang diharapkan" class="df-field min-h-24"></textarea>
                <button class="df-button df-button-primary justify-self-start">Kirim Laporan</button>
            </form>
        </section>
    @endif
</div>
@endsection
