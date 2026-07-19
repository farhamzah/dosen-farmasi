@extends('layouts.app', ['title' => 'Notifikasi'])

@section('breadcrumb', 'Notifikasi')

@php
    $unreadCount = $notifications->getCollection()->whereNull('read_at')->count();
    $readCount = $notifications->getCollection()->whereNotNull('read_at')->count();
@endphp

@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Pusat Kabar"
        title="Notifikasi"
        description="Informasi terbaru dari portofolio, verifikasi, agenda, dan dokumen akademik."
    >
        <x-slot:actions>
            @if($unreadCount > 0)
                <form method="post" action="{{ route('dosen.notifications.read-all') }}">
                    @csrf
                    <button class="df-button df-button-secondary">Tandai Semua Dibaca</button>
                </form>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <section class="grid gap-4 md:grid-cols-2">
        <x-ui.stat label="Belum Dibaca" :value="$unreadCount" tone="warning" />
        <x-ui.stat label="Sudah Dibaca" :value="$readCount" tone="success" />
    </section>

    <section class="grid gap-4">
        @forelse($notifications as $notification)
            @php($isUnread = is_null($notification->read_at))
            <article class="df-card p-5 {{ $isUnread ? 'border-[var(--brand-100)] bg-[var(--brand-50)]/50' : '' }}">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
                    <div class="flex min-w-0 gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[var(--radius-md)] bg-white text-[var(--brand-700)] shadow-sm">
                            <x-ui.icon name="bell" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-[var(--text-primary)]">{{ $notification->data['title'] ?? 'Notifikasi' }}</h2>
                                <x-ui.badge :tone="$isUnread ? 'warning' : 'neutral'">{{ $isUnread ? 'Belum dibaca' : 'Dibaca' }}</x-ui.badge>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-[var(--text-secondary)]">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="mt-2 text-xs font-semibold text-[var(--text-muted)]">{{ optional($notification->created_at)->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @if($isUnread)
                        <form method="post" action="{{ route('dosen.notifications.read', $notification->id) }}">
                            @csrf
                            <button class="df-button df-button-secondary w-full lg:w-auto">Tandai Dibaca</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <x-ui.empty-state title="Belum ada notifikasi" description="Kabar penting dari sistem akan muncul di halaman ini." icon="bell" />
        @endforelse
    </section>

    <div>{{ $notifications->links() }}</div>
</div>
@endsection
