@extends('layouts.app', ['title' => 'Profil Publik', 'authLayout' => true])

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">
    <section class="rounded-xl border border-white/80 bg-white/90 p-6 shadow-xl shadow-slate-200/70">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Profil Publik Dosen</p>
        <h1 class="mt-2 text-3xl font-semibold text-slate-950">Profil akademik terpilih</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Halaman ini hanya menampilkan data yang dipilih sebagai public. NIK, alamat rumah, nomor HP pribadi, dokumen ijazah/SK, dan nomor dokumen sensitif tidak ditampilkan.</p>
    </section>

    <section class="mt-6 rounded-xl border border-white/80 bg-white/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-950">Riwayat Pendidikan</h2>
        <div class="mt-4 space-y-3">
            @forelse($educations as $education)
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="font-semibold text-slate-950">{{ $education->level }} - {{ $education->institution_name }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $education->study_program ?: 'Program studi tidak dipublikasikan' }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada pendidikan public.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-6 rounded-xl border border-white/80 bg-white/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-950">Bidang Keilmuan dan Identitas Ilmiah</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($expertiseAreas as $area)
                @foreach(array_filter([$area->primary_expertise, ...($area->specializations ?? [])]) as $chip)
                    <span class="rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-800">{{ $chip }}</span>
                @endforeach
            @endforeach
            @foreach($identifiers as $identifier)
                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700">{{ $identifier->identifier_type }}</span>
            @endforeach
        </div>
    </section>
</div>
@endsection
