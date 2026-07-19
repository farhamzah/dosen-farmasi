@extends('layouts.app', ['title' => $title])

@section('content')
<div class="rounded-lg border border-slate-200 bg-white p-6">
    <h1 class="text-xl font-semibold">{{ $title }}</h1>
    <p class="mt-2 text-slate-600">Halaman ini sudah terhubung ke navigasi M1 dan akan diisi pada milestone berikutnya.</p>
</div>
@endsection
