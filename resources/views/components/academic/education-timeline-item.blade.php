@props([
    'education',
])

@php
    $statusTone = str_contains((string) $education->verification_status, 'VERIFIED') ? 'success' : (($education->verification_status === 'DRAFT') ? 'info' : 'warning');
    $statusLabels = [
        'DRAFT' => 'Belum diverifikasi',
        'SUBMITTED' => 'Diajukan',
        'ADMIN_VERIFIED' => 'Diverifikasi Admin',
        'SYSTEM_VERIFIED' => 'Terverifikasi Sistem',
        'REVISION_REQUIRED' => 'Perlu Revisi',
        'REJECTED' => 'Ditolak',
    ];
    $visibilityLabels = [
        'PRIVATE' => 'Private',
        'INTERNAL' => 'Internal',
        'PUBLIC' => 'Public',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'relative rounded-[var(--radius-lg)] border border-[var(--border)] bg-white p-4 shadow-sm']) }}>
    <span class="absolute -left-[2.06rem] top-5 h-4 w-4 rounded-full border-4 border-white bg-[var(--brand-600)] shadow-sm"></span>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge tone="brand">{{ $education->level }}</x-ui.badge>
                <x-ui.badge :tone="$statusTone">Status audit: {{ $statusLabels[$education->verification_status] ?? str($education->verification_status)->replace('_', ' ')->title() }}</x-ui.badge>
                <x-ui.badge tone="neutral">Visibilitas: {{ $visibilityLabels[$education->visibility] ?? $education->visibility }}</x-ui.badge>
            </div>
            <h3 class="mt-3 text-lg font-black text-[var(--text-primary)]">{{ $education->institution_name }}</h3>
            <p class="mt-1 text-sm font-semibold text-[var(--text-secondary)]">{{ $education->study_program ?: 'Program studi belum diisi' }} · {{ $education->degree ?: 'Gelar belum diisi' }}</p>
            @if($education->thesis_title)
                <p class="mt-3 text-sm leading-6 text-[var(--text-secondary)]">{{ $education->thesis_title }}</p>
            @endif
        </div>
        <p class="shrink-0 rounded-full bg-[var(--surface-muted)] px-3 py-1 text-xs font-extrabold text-[var(--text-secondary)]">
            {{ $education->start_year ?: 'Mulai' }} - {{ $education->end_year ?: 'Berjalan' }}
        </p>
    </div>
    @if($education->document_id)
        <p class="mt-4 rounded-[var(--radius-sm)] bg-rose-50 p-3 text-xs font-bold text-rose-800">Dokumen pendidikan terhubung dan tetap private.</p>
    @endif
    <details class="mt-4 rounded-[var(--radius-sm)] border border-[var(--border)] bg-[var(--surface-muted)]/70 p-3">
        <summary class="cursor-pointer text-sm font-black text-[var(--brand-800)]">Edit pendidikan, visibilitas, dan data</summary>
        <form method="post" action="{{ route('profile.educations.update', $education) }}" class="mt-4 space-y-3">
            @csrf
            @method('put')
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                    Jenjang
                    <select name="level" class="df-field mt-2" required>
                        @foreach(\App\Models\LecturerEducation::LEVELS as $level)
                            <option value="{{ $level }}" @selected($education->level === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                    Status kelulusan
                    <select name="graduation_status" class="df-field mt-2" required>
                        @foreach(['LULUS' => 'Lulus', 'BERJALAN' => 'Berjalan', 'TIDAK_SELESAI' => 'Tidak selesai'] as $value => $label)
                            <option value="{{ $value }}" @selected($education->graduation_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                Nama institusi
                <input name="institution_name" value="{{ $education->institution_name }}" class="df-field mt-2" required>
            </label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                    Program studi
                    <input name="study_program" value="{{ $education->study_program }}" class="df-field mt-2">
                </label>
                <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                    Gelar
                    <input name="degree" value="{{ $education->degree }}" class="df-field mt-2">
                </label>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                    Mulai
                    <input name="start_year" type="number" value="{{ $education->start_year }}" class="df-field mt-2">
                </label>
                <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                    Lulus
                    <input name="end_year" type="number" value="{{ $education->end_year }}" class="df-field mt-2">
                </label>
            </div>
            <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                Judul tugas akhir/tesis/disertasi
                <textarea name="thesis_title" class="df-field mt-2 min-h-24" rows="3">{{ $education->thesis_title }}</textarea>
            </label>
            <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                Visibilitas
                <select name="visibility" class="df-field mt-2" required>
                    @foreach($visibilityLabels as $value => $label)
                        <option value="{{ $value }}" @selected($education->visibility === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <p class="text-xs leading-5 text-[var(--text-muted)]">Status audit diverifikasi oleh admin akademik atau sistem integrasi resmi. Dosen dapat mengubah data dan visibilitasnya.</p>
            <div class="grid gap-2 sm:grid-cols-[1fr_auto]">
                <button class="df-button df-button-primary">Simpan Perubahan</button>
                <button type="submit" form="delete-education-{{ $education->id }}" class="df-button df-button-secondary" onclick="return confirm('Hapus riwayat pendidikan ini?')">Hapus</button>
            </div>
        </form>
        <form id="delete-education-{{ $education->id }}" method="post" action="{{ route('profile.educations.destroy', $education) }}" class="hidden">
            @csrf
            @method('delete')
        </form>
    </details>
</div>
