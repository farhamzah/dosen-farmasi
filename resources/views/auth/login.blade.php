@extends('layouts.app', ['title' => 'Login Dosen Farmasi', 'authLayout' => true])

@section('content')
<div class="df-auth-page">
    <header class="df-auth-brand">
        <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" width="56" height="56">
        <div><p class="font-bold text-lg">Dosen Farmasi</p><p class="text-sm text-[var(--text-secondary)]">Universitas Buana Perjuangan Karawang</p></div>
    </header>
    <section id="konten-utama" class="df-auth-form" aria-labelledby="login-title">
        <h1 id="login-title" class="text-2xl font-bold">Masuk ke Dosen Farmasi</h1>
        <p class="mt-2 text-sm text-[var(--text-secondary)]">Gunakan akun Core Farmasi Anda.</p>
        <form method="post" action="{{ route('login.store') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="login" class="df-label">Email, username, NIP, atau NIDN</label>
                <input id="login" name="login" value="{{ old('login') }}" autocomplete="username" autofocus required class="df-field mt-2" placeholder="nama@ubpkarawang.ac.id" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror>
                @error('login')<p id="login-error" class="mt-2 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="df-label">Password</label>
                <div class="relative mt-2">
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="df-field pr-14" placeholder="Masukkan password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    <button type="button" class="df-password-toggle" data-password-toggle data-target="password" aria-label="Tampilkan password" aria-pressed="false" title="Tampilkan password">
                        <x-heroicon-o-eye data-eye-open class="h-5 w-5" />
                        <x-heroicon-o-eye-slash data-eye-closed class="hidden h-5 w-5" />
                    </button>
                </div>
                @error('password')<p id="password-error" class="mt-2 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
            </div>
            <button class="df-button df-button-primary w-full">Masuk <x-heroicon-o-arrow-right class="h-4 w-4" /></button>
        </form>
        <p class="mt-6 border-t border-[var(--border)] pt-5 text-xs leading-5 text-[var(--text-secondary)]">Akses akun dan perubahan password dikelola oleh Core Farmasi.</p>
    </section>
    <footer class="mt-8 text-center text-xs text-[var(--text-muted)]">Fakultas Farmasi UBP Karawang</footer>
</div>
@endsection
