@extends('layouts.app', ['title' => 'Login Dosen Farmasi', 'authLayout' => true])

@section('content')
<div class="mx-auto grid min-h-screen w-full max-w-7xl items-center gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
    <section class="hidden lg:block">
        <div class="max-w-xl">
            <div class="inline-flex items-center gap-3 rounded-lg border border-slate-200 bg-white/76 px-3 py-2 shadow-sm">
                <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="h-10 w-10 rounded-lg object-contain">
                <span class="text-sm font-semibold text-slate-900">Fakultas Farmasi UBP Karawang</span>
            </div>
            <h1 class="mt-8 text-5xl font-semibold leading-tight text-slate-950">Ruang kerja dosen yang rapi, aman, dan siap diverifikasi.</h1>
            <p class="mt-5 max-w-lg text-base leading-7 text-slate-600">Masuk dengan akun Core Farmasi untuk mengelola portofolio, dokumen, inbox, agenda, dan validasi akademik dalam satu alur kerja.</p>

            <div class="mt-8 grid max-w-lg grid-cols-3 gap-3">
                <div class="rounded-lg border border-white/80 bg-white/72 p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Akses</p>
                    <p class="mt-2 text-sm font-semibold text-slate-950">Dosen dan admin</p>
                </div>
                <div class="rounded-lg border border-white/80 bg-white/72 p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Data</p>
                    <p class="mt-2 text-sm font-semibold text-slate-950">Tersinkron</p>
                </div>
                <div class="rounded-lg border border-white/80 bg-white/72 p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Audit</p>
                    <p class="mt-2 text-sm font-semibold text-slate-950">Terlacak</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto w-full max-w-md rounded-xl border border-white/80 bg-white/90 p-6 shadow-xl shadow-slate-200/80 backdrop-blur sm:p-8">
        <div class="flex items-center gap-3 lg:hidden">
            <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="h-11 w-11 rounded-xl object-contain ring-1 ring-slate-200">
            <div>
                <p class="text-sm font-semibold text-slate-950">Dosen Farmasi UBP</p>
                <p class="text-xs text-slate-500">Akses Core Farmasi</p>
            </div>
        </div>

        <div class="mt-6 lg:mt-0">
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Login aman</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950">Masuk ke Dosen Farmasi</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan email, username, NIP, atau NIDN yang terdaftar di Core Farmasi UBP.</p>
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
                    class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
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
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 pr-14 text-base text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        placeholder="Masukkan password"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-1.5 right-1.5 inline-flex w-11 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-lg bg-slate-950 px-4 py-3 text-base font-semibold text-white shadow-lg shadow-slate-300/70 transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">
                Masuk
            </button>
        </form>
    </section>
</div>
@endsection
