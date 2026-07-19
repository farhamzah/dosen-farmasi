@extends('layouts.app', ['title' => 'Pilih Role Masuk', 'authLayout' => true])

@php
    $roleOptions = [
        'admin' => [
            'title' => 'Admin',
            'subtitle' => 'Ruang kontrol, integrasi, audit, dan validasi operasional.',
            'badge' => 'Kontrol penuh',
        ],
        'dosen' => [
            'title' => 'Dosen',
            'subtitle' => 'Portofolio pribadi, dokumen, inbox, agenda, dan notifikasi.',
            'badge' => 'Workspace dosen',
        ],
    ];
@endphp

@section('content')
<div class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-8 sm:px-6 lg:px-8">
    <section class="w-full">
        <div class="mx-auto max-w-2xl text-center">
            <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="mx-auto h-16 w-16 rounded-2xl bg-white object-contain p-2 shadow-sm ring-1 ring-slate-200">
            <p class="mt-6 text-sm font-semibold uppercase tracking-wide text-blue-700">Pilih akses kerja</p>
            <h1 class="mt-3 text-4xl font-semibold leading-tight text-slate-950">Pilih Role Masuk</h1>
            <p class="mt-3 text-base leading-7 text-slate-600">Akun Anda memiliki lebih dari satu akses. Pilih ruang kerja yang ingin digunakan untuk sesi ini.</p>
        </div>

        <div class="mx-auto mt-9 grid max-w-4xl gap-4 md:grid-cols-2">
            @foreach($roles as $role)
                @php($option = $roleOptions[$role] ?? ['title' => ucfirst($role), 'subtitle' => 'Akses aplikasi Dosen Farmasi.', 'badge' => 'Tersedia'])
                <form method="post" action="{{ route('role.store') }}" class="group">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role }}">
                    <button class="h-full w-full rounded-xl border border-white/80 bg-white/88 p-6 text-left shadow-lg shadow-slate-200/70 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 group-hover:bg-blue-50 group-hover:text-blue-700">
                            {{ $option['badge'] }}
                        </span>
                        <span class="mt-6 block text-2xl font-semibold text-slate-950">{{ $option['title'] }}</span>
                        <span class="mt-3 block min-h-14 text-sm leading-6 text-slate-600">{{ $option['subtitle'] }}</span>
                        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-700">
                            Masuk sebagai {{ $option['title'] }}
                            <span aria-hidden="true">-&gt;</span>
                        </span>
                    </button>
                </form>
            @endforeach
        </div>
    </section>
</div>
@endsection
