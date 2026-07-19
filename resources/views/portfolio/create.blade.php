@extends('layouts.app', ['title' => 'Tambah Portofolio'])

@section('breadcrumb', 'Portofolio')

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Portofolio"
        title="Tambah Aktivitas Akademik"
        description="Catat kegiatan baru sebagai draft. Setelah lengkap, aktivitas dapat diajukan untuk verifikasi."
    >
        <x-slot:actions>
            <x-ui.button :href="route('dosen.portfolio.index')" variant="secondary">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="post" action="{{ route('dosen.portfolio.store') }}" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        @csrf

        <div class="space-y-5">
            @if($errors->any())
                <div class="rounded-[var(--radius-md)] border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div>
            @endif

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Informasi Kegiatan" description="Isi judul, kategori, dan deskripsi yang akan dibaca saat verifikasi." />
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Kategori
                        <select name="category_id" class="df-field">
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tipe kegiatan
                        <input name="activity_type" value="{{ old('activity_type') }}" placeholder="Contoh: Mengajar, penelitian, hibah" class="df-field" required>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)] md:col-span-2">
                        Judul
                        <input name="title" value="{{ old('title') }}" placeholder="Judul kegiatan akademik" class="df-field" required>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)] md:col-span-2">
                        Deskripsi
                        <textarea name="description" rows="5" placeholder="Tuliskan konteks, capaian, atau luaran singkat." class="df-field min-h-32">{{ old('description') }}</textarea>
                    </label>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Periode dan Keterlibatan" description="Lengkapi periode, mitra, lokasi, dan peran Anda dalam kegiatan." />
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Peran dosen
                        <input name="lecturer_role" value="{{ old('lecturer_role') }}" placeholder="Ketua, anggota, pembimbing" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tahun akademik
                        <input name="academic_year" value="{{ old('academic_year') }}" placeholder="2026/2027" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Semester
                        <input name="semester" value="{{ old('semester') }}" placeholder="Ganjil atau Genap" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Visibilitas
                        <select name="visibility" class="df-field">
                            @foreach(['PRIVATE','INTERNAL','PUBLIC'] as $visibility)
                                <option value="{{ $visibility }}" @selected(old('visibility', 'PRIVATE') === $visibility)>{{ str($visibility)->title() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tanggal mulai
                        <input name="start_date" value="{{ old('start_date') }}" type="date" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tanggal selesai
                        <input name="end_date" value="{{ old('end_date') }}" type="date" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Institusi atau mitra
                        <input name="institution_name" value="{{ old('institution_name') }}" placeholder="Nama institusi/mitra" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Lokasi
                        <input name="location" value="{{ old('location') }}" placeholder="Kota, kampus, atau daring" class="df-field">
                    </label>
                </div>
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card class="p-5">
                <x-ui.section-header title="Status Draft" description="Aktivitas disimpan sebagai draft dan belum masuk antrean verifikasi." />
                <div class="mt-5 grid gap-3">
                    <x-ui.badge tone="info">Input Mandiri</x-ui.badge>
                    <x-ui.badge>Belum diajukan</x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                    Catatan pribadi
                    <textarea name="personal_notes" rows="6" placeholder="Catatan ini membantu Anda menyiapkan bukti atau tindak lanjut." class="df-field min-h-36">{{ old('personal_notes') }}</textarea>
                </label>
                <button class="df-button df-button-primary mt-5 w-full">Simpan Draft</button>
            </x-ui.card>
        </aside>
    </form>
</div>
@endsection
