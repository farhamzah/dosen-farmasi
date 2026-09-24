@extends('layouts.app', ['title' => 'Pilih Role Masuk', 'authLayout' => true])

@section('content')
<div class="df-auth-page">
    <header class="df-auth-brand">
        <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" width="56" height="56">
        <div><p class="font-bold text-lg">Dosen Farmasi</p><p class="text-sm text-[var(--text-secondary)]">UBP Karawang</p></div>
    </header>
    <section id="konten-utama" class="df-role-picker" aria-labelledby="role-title">
        <h1 id="role-title" class="text-2xl font-bold">Pilih ruang kerja</h1>
        <p class="mt-2 break-words text-sm text-[var(--text-secondary)]">{{ auth()->user()->name }}</p>
        <div class="mt-6 grid gap-3">
            @foreach($roles as $role)
                <form method="post" action="{{ route('role.store') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role }}">
                    <button class="df-role-option">
                        <span class="df-role-icon">@if($role === 'admin')<x-heroicon-o-adjustments-horizontal class="h-6 w-6" />@else<x-heroicon-o-academic-cap class="h-6 w-6" />@endif</span>
                        <span class="min-w-0 flex-1"><span class="block text-base font-bold">{{ $role === 'admin' ? 'Admin' : 'Dosen' }}</span><span class="mt-1 block text-sm text-[var(--text-secondary)]">{{ $role === 'admin' ? 'Administrasi, data dosen, dan aplikasi terhubung.' : 'Profil akademik, portofolio, dan CV pribadi.' }}</span><span class="mt-3 block text-sm font-semibold text-[var(--brand-700)]">Masuk sebagai {{ $role === 'admin' ? 'Admin' : 'Dosen' }}</span></span>
                        <x-heroicon-o-arrow-right class="h-5 w-5 shrink-0" />
                    </button>
                </form>
            @endforeach
        </div>
        <form method="post" action="{{ route('logout') }}" class="mt-5">@csrf<button class="df-button df-button-secondary w-full">Keluar dari sesi</button></form>
    </section>
</div>
@endsection
