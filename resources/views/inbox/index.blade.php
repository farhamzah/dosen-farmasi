@extends('layouts.app', ['title' => 'Inbox'])

@section('breadcrumb', 'Inbox')

@php
    $unreadCount = $items->getCollection()->where('status', 'UNREAD')->count();
    $highCount = $items->getCollection()->filter(fn ($item) => in_array($item->priority, ['HIGH', 'URGENT'], true))->count();
    $doneCount = $items->getCollection()->whereIn('status', ['COMPLETED', 'ARCHIVED'])->count();
@endphp

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Komunikasi"
        title="Inbox Akademik"
        description="Tindak lanjut dari portofolio, dokumen, verifikasi, dan integrasi antar-aplikasi."
    />

    <section class="grid gap-4 md:grid-cols-3">
        <x-ui.stat label="Belum Dibaca" :value="$unreadCount" tone="warning" />
        <x-ui.stat label="Prioritas Tinggi" :value="$highCount" tone="danger" />
        <x-ui.stat label="Selesai" :value="$doneCount" tone="success" />
    </section>

    <section class="df-card p-4 sm:p-5">
        <form method="get" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
            <select name="status" class="df-field">
                <option value="">Semua status</option>
                @foreach(['UNREAD', 'READ', 'ACCEPTED', 'DECLINED', 'COMPLETED', 'ARCHIVED'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
            <select name="type" class="df-field">
                <option value="">Semua tipe</option>
                @foreach(['TASK', 'INFO', 'WARNING', 'REMINDER'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ str($type)->title() }}</option>
                @endforeach
            </select>
            <button class="df-button df-button-primary">Terapkan</button>
        </form>
    </section>

    <section class="grid gap-4">
        @forelse($items as $item)
            @php
                $priorityTone = in_array($item->priority, ['HIGH', 'URGENT'], true) ? 'danger' : ($item->priority === 'MEDIUM' ? 'warning' : 'info');
                $statusTone = $item->status === 'UNREAD' ? 'warning' : (in_array($item->status, ['COMPLETED', 'ARCHIVED'], true) ? 'success' : 'neutral');
            @endphp
            <article class="df-card p-5">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
                    <div class="flex min-w-0 gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[var(--radius-md)] bg-[var(--brand-50)] text-[var(--brand-700)]">
                            <x-ui.icon name="inbox" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-[var(--text-primary)]">{{ $item->title }}</h2>
                                <x-ui.badge :tone="$priorityTone">{{ str($item->priority)->title() }}</x-ui.badge>
                                <x-ui.badge :tone="$statusTone">{{ str($item->status)->replace('_', ' ')->title() }}</x-ui.badge>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-[var(--text-secondary)]">{{ $item->summary ?: 'Tidak ada ringkasan.' }}</p>
                            <p class="mt-2 text-xs font-semibold text-[var(--text-muted)]">{{ str($item->type)->title() }} - {{ optional($item->occurred_at)->format('d M Y H:i') ?: optional($item->created_at)->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @if($item->status === 'UNREAD')
                        <form method="post" action="{{ route('dosen.inbox.read', $item) }}">
                            @csrf
                            <button class="df-button df-button-secondary w-full lg:w-auto">Tandai Dibaca</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <x-ui.empty-state title="Inbox kosong" description="Tugas dan informasi dari verifikasi akan muncul di sini." icon="inbox" />
        @endforelse
    </section>

    <div>{{ $items->links() }}</div>
</div>
@endsection
