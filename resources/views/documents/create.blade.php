@extends('layouts.app', ['title' => 'Unggah Dokumen'])

@section('breadcrumb', 'Dokumen')

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Dokumen Akademik"
        title="Unggah Dokumen"
        description="Tambahkan bukti pendukung dan hubungkan ke aktivitas portofolio bila diperlukan."
    >
        <x-slot:actions>
            <x-ui.button :href="route('dosen.documents.index')" variant="secondary">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="post" action="{{ route('dosen.documents.store') }}" enctype="multipart/form-data" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        @csrf

        <div class="space-y-5">
            @if($errors->any())
                <div class="rounded-[var(--radius-md)] border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div>
            @endif

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Informasi Dokumen" description="Nama, jenis, penerbit, dan tanggal dokumen untuk memudahkan pencarian." />
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Jenis dokumen
                        <select name="document_type" class="df-field" required>
                            @foreach(config('dosen_farmasi.documents.types') as $type)
                                <option value="{{ $type }}" @selected(old('document_type') === $type)>{{ str($type)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Visibilitas
                        <select name="visibility" class="df-field">
                            <option value="PRIVATE" @selected(old('visibility', 'PRIVATE') === 'PRIVATE')>Private</option>
                            <option value="INTERNAL" @selected(old('visibility') === 'INTERNAL')>Internal</option>
                            <option value="PUBLIC" @selected(old('visibility') === 'PUBLIC')>Public</option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)] md:col-span-2">
                        Judul dokumen
                        <input name="title" value="{{ old('title') }}" placeholder="Contoh: Surat Tugas Pengabdian Masyarakat" class="df-field" required>
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Nomor dokumen
                        <input name="document_number" value="{{ old('document_number') }}" placeholder="Opsional" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                        Tanggal dokumen
                        <input name="document_date" value="{{ old('document_date') }}" type="date" class="df-field">
                    </label>
                    <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)] md:col-span-2">
                        Penerbit
                        <input name="issuer" value="{{ old('issuer') }}" placeholder="Unit, fakultas, mitra, atau lembaga penerbit" class="df-field">
                    </label>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 sm:p-6">
                <x-ui.section-header title="Kaitan Portofolio" description="Pilih aktivitas bila dokumen ini menjadi bukti langsung kegiatan tertentu." />
                <label class="mt-5 grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                    Aktivitas terkait
                    <select name="portfolio_activity_id" class="df-field">
                        <option value="">Tidak dikaitkan</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}" @selected(old('portfolio_activity_id') == $activity->id)>{{ $activity->title }}</option>
                        @endforeach
                    </select>
                </label>
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card class="p-5">
                <x-ui.section-header title="File" description="Pastikan file jelas terbaca sebelum diunggah." />
                <label class="mt-5 grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
                    Pilih file
                    <input name="file" type="file" class="df-field" required>
                </label>
                <p class="mt-3 text-xs font-semibold leading-5 text-[var(--text-muted)]">Ekstensi diizinkan: {{ implode(', ', config('dosen_farmasi.documents.allowed_extensions')) }}. Maksimal {{ config('dosen_farmasi.documents.max_kb') }} KB.</p>
                <button class="df-button df-button-primary mt-5 w-full">
                    <x-ui.icon name="plus" class="h-4 w-4" />
                    Unggah Dokumen
                </button>
            </x-ui.card>
        </aside>
    </form>
</div>
@endsection
