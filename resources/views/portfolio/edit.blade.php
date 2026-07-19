@extends('layouts.app', ['title' => 'Edit Portofolio'])

@section('breadcrumb', 'Portofolio')

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Portofolio"
        title="Edit Aktivitas"
        description="Perbarui data kegiatan sebelum dikirim atau dikembalikan ke alur verifikasi."
    >
        <x-slot:actions>
            <x-ui.button :href="route('dosen.portfolio.show', $activity)" variant="secondary">Lihat Detail</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="post" action="{{ route('dosen.portfolio.update', $activity) }}" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            @if($errors->any())
                <div class="rounded-[var(--radius-md)] border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div>
            @endif

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Informasi Kegiatan" description="Data resmi kegiatan yang akan muncul di portofolio akademik." />
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Kategori
                        <select name="category_id" class="df-field">
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $activity->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tipe kegiatan
                        <input name="activity_type" value="{{ old('activity_type', $activity->activity_type) }}" placeholder="Contoh: Mengajar, penelitian, hibah" class="df-field" required>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)] md:col-span-2">
                        Judul
                        <input name="title" value="{{ old('title', $activity->title) }}" placeholder="Judul kegiatan akademik" class="df-field" required>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)] md:col-span-2">
                        Deskripsi
                        <textarea name="description" rows="5" placeholder="Tuliskan konteks, capaian, atau luaran singkat." class="df-field min-h-32">{{ old('description', $activity->description) }}</textarea>
                    </label>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Periode dan Keterlibatan" description="Perjelas kapan kegiatan berlangsung dan peran Anda di dalamnya." />
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Peran dosen
                        <input name="lecturer_role" value="{{ old('lecturer_role', $activity->lecturer_role) }}" placeholder="Ketua, anggota, pembimbing" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tahun akademik
                        <input name="academic_year" value="{{ old('academic_year', $activity->academic_year) }}" placeholder="2026/2027" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Semester
                        <input name="semester" value="{{ old('semester', $activity->semester) }}" placeholder="Ganjil atau Genap" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Visibilitas
                        <select name="visibility" class="df-field">
                            @foreach(['PRIVATE','INTERNAL','PUBLIC'] as $visibility)
                                <option value="{{ $visibility }}" @selected(old('visibility', $activity->visibility) === $visibility)>{{ str($visibility)->title() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tanggal mulai
                        <input name="start_date" value="{{ old('start_date', optional($activity->start_date)->format('Y-m-d')) }}" type="date" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tanggal selesai
                        <input name="end_date" value="{{ old('end_date', optional($activity->end_date)->format('Y-m-d')) }}" type="date" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Institusi atau mitra
                        <input name="institution_name" value="{{ old('institution_name', $activity->institution_name) }}" placeholder="Nama institusi/mitra" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Lokasi
                        <input name="location" value="{{ old('location', $activity->location) }}" placeholder="Kota, kampus, atau daring" class="df-field">
                    </label>
                </div>
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card class="p-5">
                <x-ui.section-header title="Status Saat Ini" description="Perubahan disimpan tanpa mengubah status otomatis kecuali Anda mengajukan verifikasi." />
                <div class="mt-5 flex flex-wrap gap-2">
                    <x-ui.badge tone="info">{{ str($activity->verification_status)->replace('_', ' ')->title() }}</x-ui.badge>
                    <x-ui.badge>{{ $activity->source_type === 'MANUAL' ? 'Input Mandiri' : 'Sistem' }}</x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                    Catatan pribadi
                    <textarea name="personal_notes" rows="6" placeholder="Catatan ini hanya untuk membantu tindak lanjut Anda." class="df-field min-h-36">{{ old('personal_notes', $activity->personal_notes) }}</textarea>
                </label>
                <button class="df-button df-button-primary mt-5 w-full">Simpan Perubahan</button>
            </x-ui.card>
        </aside>
    </form>
</div>
@endsection
