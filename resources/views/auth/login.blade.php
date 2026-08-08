@extends('layouts.app', ['title' => 'Login Dosen Farmasi', 'authLayout' => true])

@section('content')
<div class="mx-auto grid min-h-screen w-full max-w-7xl items-center gap-8 px-4 py-6 sm:px-6 lg:grid-cols-[1fr_0.86fr] lg:px-8">
    <section class="hidden lg:block">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-3 rounded-2xl border border-white/80 bg-white/86 px-4 py-3 shadow-[var(--shadow-subtle)] backdrop-blur">
                <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="h-11 w-11 rounded-xl object-contain">
                <span>
                    <span class="block text-sm font-bold text-slate-950">Fakultas Farmasi UBP Karawang</span>
                    <span class="block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Academic Portfolio Console</span>
                </span>
            </div>
            <h1 class="mt-8 max-w-2xl text-5xl font-black leading-tight tracking-normal text-slate-950 xl:text-6xl">Satu ruang akademik untuk portofolio dosen yang siap diaudit.</h1>
            <p class="mt-5 max-w-xl text-lg leading-8 text-slate-600">Masuk dengan akun Core Farmasi untuk mengelola portofolio, dokumen, inbox, agenda, dan validasi akademik dalam alur kerja yang aman.</p>

            <div class="mt-8 grid max-w-lg grid-cols-3 gap-3">
                <div class="rounded-2xl border border-white/90 bg-white/80 p-4 shadow-[var(--shadow-subtle)] backdrop-blur">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Akses</p>
                    <p class="mt-2 text-sm font-semibold text-slate-950">Dosen dan admin</p>
                </div>
                <div class="rounded-2xl border border-white/90 bg-white/80 p-4 shadow-[var(--shadow-subtle)] backdrop-blur">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Data</p>
                    <p class="mt-2 text-sm font-semibold text-slate-950">Tersinkron</p>
                </div>
                <div class="rounded-2xl border border-white/90 bg-white/80 p-4 shadow-[var(--shadow-subtle)] backdrop-blur">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Audit</p>
                    <p class="mt-2 text-sm font-semibold text-slate-950">Terlacak</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto w-full max-w-md rounded-[28px] border border-white/90 bg-white/92 p-5 shadow-[0_24px_80px_rgba(15,23,42,0.16)] backdrop-blur sm:p-7 lg:max-w-lg">
        <div class="flex items-center gap-3 lg:hidden">
            <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="h-11 w-11 rounded-xl object-contain ring-1 ring-slate-200">
            <div>
                <p class="text-sm font-semibold text-slate-950">Dosen Farmasi UBP</p>
                <p class="text-xs text-slate-500">Akses Core Farmasi</p>
            </div>
        </div>

        <div class="mt-6 lg:mt-0">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Login aman</p>
            <h2 class="mt-2 text-3xl font-black leading-tight tracking-normal text-slate-950 sm:text-4xl">Masuk ke Dosen Farmasi</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">Gunakan email, username, NIP, atau NIDN yang terdaftar di Core Farmasi UBP.</p>
        </div>

        <form method="post" action="{{ route('login.store') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="login" class="text-sm font-semibold text-slate-900">Email, username, NIP, atau NIDN</label>
                <input
                    id="login"
                    name="login"
                    value="{{ old('login') }}"
                    autocomplete="username"
                    autofocus
                    class="mt-2 w-full rounded-2xl border border-slate-300 bg-slate-50/80 px-4 py-4 text-base text-slate-950 shadow-inner shadow-slate-200/50 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="nama@ubpkarawang.ac.id"
                >
                @error('login')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between gap-3">
                    <label for="password" class="text-sm font-semibold text-slate-900">Password</label>
                    <span class="text-xs font-medium text-slate-500">Sensitif huruf besar-kecil</span>
                </div>
                <div class="relative mt-2">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full rounded-2xl border border-slate-300 bg-slate-50/80 px-4 py-4 pr-24 text-base text-slate-950 shadow-inner shadow-slate-200/50 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        placeholder="Masukkan password"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-2 right-2 inline-flex items-center justify-center gap-2 rounded-xl px-3 text-xs font-bold text-slate-600 transition hover:bg-white hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        data-password-toggle
                        data-target="password"
                        aria-label="Tampilkan password"
                    >
                        <svg data-eye-open class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                            <path d="M2.25 12s3.5-6.5 9.75-6.5 9.75 6.5 9.75 6.5-3.5 6.5-9.75 6.5S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg data-eye-closed class="hidden h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                            <path d="m4 4 16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M9.5 5.9A10.5 10.5 0 0 1 12 5.5c6.25 0 9.75 6.5 9.75 6.5a18.4 18.4 0 0 1-2.6 3.25M6.2 7.55C3.65 9.25 2.25 12 2.25 12s3.5 6.5 9.75 6.5c1.45 0 2.76-.35 3.93-.88" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.2 9.05a3.25 3.25 0 0 1 4.75 4.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span data-password-label class="hidden sm:inline">Lihat</span>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-2xl bg-slate-950 px-4 py-4 text-base font-black text-white shadow-[0_18px_40px_rgba(15,23,42,0.24)] transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">
                Masuk
            </button>
        </form>

        <div class="mt-6 grid grid-cols-3 gap-2 text-center text-[11px] font-bold uppercase tracking-wide text-slate-500">
            <span class="rounded-full bg-slate-100 px-2 py-2">Aman</span>
            <span class="rounded-full bg-slate-100 px-2 py-2">Responsif</span>
            <span class="rounded-full bg-slate-100 px-2 py-2">Audit</span>
        </div>
    </section>
</div>
@endsection
