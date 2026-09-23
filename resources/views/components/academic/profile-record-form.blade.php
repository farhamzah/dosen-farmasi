@props(['kind', 'record' => null])

@php
    $routes = [
        'expertise' => 'profile.expertise',
        'certification' => 'profile.certifications',
        'functional' => 'profile.functional-positions',
        'structural' => 'profile.structural-positions',
    ];
    $base = $routes[$kind];
    $editing = $record !== null;
    $label = [
        'expertise' => 'kepakaran',
        'certification' => 'sertifikasi',
        'functional' => 'jabatan fungsional',
        'structural' => 'tugas tambahan',
    ][$kind];
    $deleteFormId = $editing ? 'hapus-'.$kind.'-'.$record->id : null;
@endphp

<form method="post" action="{{ $editing ? route($base.'.update', $record) : route($base.'.store') }}" class="df-profile-record-form mt-4">
    @csrf
    @if($editing) @method('put') @endif

    @if($kind === 'expertise')
        <label class="df-profile-form-label df-profile-record-full">Bidang keahlian utama
            <input name="primary_expertise" value="{{ $record?->primary_expertise }}" class="df-field mt-1" placeholder="Contoh: Farmakologi Klinik" required>
        </label>
        <label class="df-profile-form-label">Spesialisasi <span class="font-normal">(satu per baris)</span>
            <textarea name="specializations" class="df-field mt-1 min-h-24" rows="3" placeholder="Contoh: Farmakovigilans">{{ implode("\n", $record?->specializations ?? []) }}</textarea>
        </label>
        <label class="df-profile-form-label">Topik penelitian <span class="font-normal">(satu per baris)</span>
            <textarea name="research_topics" class="df-field mt-1 min-h-24" rows="3" placeholder="Contoh: Keamanan obat">{{ implode("\n", $record?->research_topics ?? []) }}</textarea>
        </label>
    @elseif($kind === 'certification')
        <label class="df-profile-form-label">Jenis sertifikasi
            <select name="category" class="df-field mt-1" required>
                @foreach(['akademik' => 'Akademik', 'profesi' => 'Profesi', 'pelatihan' => 'Pelatihan'] as $value => $text)
                    <option value="{{ $value }}" @selected(($record?->category ?? 'profesi') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </label>
        <label class="df-profile-form-label">Status
            <select name="status" class="df-field mt-1" required>
                @foreach(['AKTIF' => 'Aktif', 'KEDALUWARSA' => 'Kedaluwarsa', 'TIDAK_AKTIF' => 'Tidak aktif'] as $value => $text)
                    <option value="{{ $value }}" @selected(($record?->status ?? 'AKTIF') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </label>
        <label class="df-profile-form-label df-profile-record-full">Nama sertifikasi
            <input name="name" value="{{ $record?->name }}" class="df-field mt-1" required>
        </label>
        <label class="df-profile-form-label df-profile-record-full">Penerbit
            <input name="issuer" value="{{ $record?->issuer }}" class="df-field mt-1">
        </label>
        <label class="df-profile-form-label">Tanggal terbit
            <input name="issued_at" type="date" value="{{ $record?->issued_at?->format('Y-m-d') }}" class="df-field mt-1">
        </label>
        <label class="df-profile-form-label">Berlaku sampai
            <input name="expires_at" type="date" value="{{ $record?->expires_at?->format('Y-m-d') }}" class="df-field mt-1">
        </label>
    @else
        <label class="df-profile-form-label df-profile-record-full">Nama {{ $kind === 'functional' ? 'jabatan' : 'tugas atau jabatan' }}
            <input name="position_name" value="{{ $record?->position_name }}" class="df-field mt-1" placeholder="{{ $kind === 'functional' ? 'Contoh: Lektor' : 'Contoh: Ketua Program Studi' }}" required>
        </label>
        <label class="df-profile-form-label">Unit kerja
            <input name="unit" value="{{ $record?->unit }}" class="df-field mt-1" placeholder="Contoh: Program Studi Farmasi">
        </label>
        @if($kind === 'functional')
            <label class="df-profile-form-label">Tanggal mulai berlaku (TMT)
                <input name="effective_date" type="date" value="{{ $record?->effective_date?->format('Y-m-d') }}" class="df-field mt-1">
            </label>
            <label class="df-profile-form-label">Angka kredit (KUM)
                <input name="credit_score" type="number" step="0.01" min="0" value="{{ $record?->credit_score }}" class="df-field mt-1">
            </label>
        @else
            <label class="df-profile-form-label">Tanggal mulai
                <input name="start_date" type="date" value="{{ $record?->start_date?->format('Y-m-d') }}" class="df-field mt-1">
            </label>
            <label class="df-profile-form-label">Tanggal selesai
                <input name="end_date" type="date" value="{{ $record?->end_date?->format('Y-m-d') }}" class="df-field mt-1">
            </label>
        @endif
        <label class="flex items-center gap-2 self-end text-sm font-semibold text-[var(--text-primary)]">
            <input type="checkbox" name="is_active" value="1" @checked($record?->is_active ?? true)>
            Masih aktif
        </label>
    @endif

    <label class="df-profile-form-label">Visibilitas
        <select name="visibility" class="df-field mt-1" required>
            @foreach(['PRIVATE' => 'Private', 'INTERNAL' => 'Internal', 'PUBLIC' => 'Public'] as $value => $text)
                <option value="{{ $value }}" @selected(($record?->visibility ?? 'INTERNAL') === $value)>{{ $text }}</option>
            @endforeach
        </select>
    </label>
    <div class="df-profile-record-full flex flex-wrap gap-2">
        <button class="df-button df-button-primary">{{ $editing ? 'Simpan perubahan' : 'Simpan '.$label }}</button>
        @if($editing)
            <button type="submit" form="{{ $deleteFormId }}" class="df-button df-button-secondary" onclick="return confirm('Hapus {{ $label }} ini?')">Hapus</button>
        @endif
    </div>
</form>
@if($editing)
    <form id="{{ $deleteFormId }}" method="post" action="{{ route($base.'.destroy', $record) }}" class="hidden">
        @csrf
        @method('delete')
    </form>
@endif
