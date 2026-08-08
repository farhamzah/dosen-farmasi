@extends('layouts.app', ['title' => 'Pilih Role Masuk', 'authLayout' => true])

@php
    $roleOptions = [
        'admin' => [
            'title' => 'Admin',
            'subtitle' => 'Ruang kontrol, integrasi, audit, dan validasi operasional.',
            'badge' => 'Kontrol penuh',
            'metric' => 'Panel administrasi',
            'icon' => 'A',
        ],
        'dosen' => [
            'title' => 'Dosen',
            'subtitle' => 'Portofolio pribadi, dokumen, inbox, agenda, dan notifikasi.',
            'badge' => 'Workspace dosen',
            'metric' => 'Portofolio akademik',
            'icon' => 'D',
        ],
    ];
@endphp

@section('content')
<div class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-6 sm:px-6 lg:px-8">
    <section class="w-full">
        <div class="mx-auto max-w-3xl text-center">
            <div class="mx-auto grid h-20 w-20 place-items-center rounded-[24px] border border-white/90 bg-white/90 shadow-[var(--shadow-subtle)]">
                <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="h-14 w-14 object-contain">
            </div>
            <p class="mt-6 text-xs font-black uppercase tracking-[0.18em] text-blue-700">Pilih akses kerja</p>
            <h1 class="mt-3 text-4xl font-black leading-tight tracking-normal text-slate-950 sm:text-5xl">Masuk sebagai apa?</h1>
            <p class="mx-auto mt-3 max-w-2xl text-base leading-7 text-slate-600">Akun Anda memiliki lebih dari satu akses. Pilih ruang kerja aktif untuk sesi ini; Anda dapat menggantinya lagi dari dashboard.</p>
        </div>

        <div class="mx-auto mt-9 grid max-w-5xl gap-4 md:grid-cols-2">
            @foreach($roles as $role)
                @php($option = $roleOptions[$role] ?? ['title' => ucfirst($role), 'subtitle' => 'Akses aplikasi Dosen Farmasi.', 'badge' => 'Tersedia', 'metric' => 'Ruang kerja', 'icon' => str($role)->substr(0, 1)->upper()])
                <form method="post" action="{{ route('role.store') }}" class="group">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role }}">
                    <button class="h-full w-full overflow-hidden rounded-[28px] border border-white/90 bg-white/92 p-5 text-left shadow-[0_18px_54px_rgba(15,23,42,0.12)] transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-[0_28px_80px_rgba(15,23,42,0.18)] focus:outline-none focus:ring-4 focus:ring-blue-100 sm:p-7">
                        <span class="flex items-start justify-between gap-4">
                            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-950 text-lg font-black text-white shadow-lg shadow-slate-300">{{ $option['icon'] }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-wide text-slate-600 group-hover:bg-blue-50 group-hover:text-blue-700">
                                {{ $option['badge'] }}
                            </span>
                        </span>
                        <span class="mt-8 block text-3xl font-black tracking-normal text-slate-950">{{ $option['title'] }}</span>
                        <span class="mt-3 block min-h-14 text-sm leading-6 text-slate-600">{{ $option['subtitle'] }}</span>
                        <span class="mt-6 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <span>
                                <span class="block text-xs font-bold uppercase tracking-wide text-slate-500">{{ $option['metric'] }}</span>
                                <span class="mt-1 block text-sm font-black text-slate-950">Masuk sebagai {{ $option['title'] }}</span>
                            </span>
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-700 font-black text-white" aria-hidden="true">-&gt;</span>
                        </span>
                    </button>
                </form>
            @endforeach
        </div>

        <form method="post" action="{{ route('logout') }}" class="mx-auto mt-6 max-w-xs">
            @csrf
            <button class="w-full rounded-2xl border border-slate-200 bg-white/82 px-4 py-3 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-rose-50 hover:text-rose-700">
                Keluar dari sesi
            </button>
        </form>
    </section>
</div>
@endsection
