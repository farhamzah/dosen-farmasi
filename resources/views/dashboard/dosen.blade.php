@extends('layouts.app', ['title' => 'Dashboard Dosen'])
@section('breadcrumb', 'Beranda')

@section('content')
<div class="space-y-6">
    <x-ui.page-header title="Beranda" description="Selamat bekerja, {{ auth()->user()->name }}.">
        <x-slot:actions>
            <x-ui.button :href="route('profile.show').'#profil-publik'" variant="secondary"><x-heroicon-o-document-text class="h-4 w-4" />CV Saya</x-ui.button>
            <x-ui.button :href="route('dosen.portfolio.create')"><x-ui.icon name="plus" class="h-4 w-4" />Tambah Aktivitas</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="df-metric-strip" aria-label="Ringkasan portofolio">
        @foreach([
            ['label' => 'Total kegiatan', 'value' => $portfolioCount, 'url' => route('dosen.portfolio.index')],
            ['label' => 'Draft', 'value' => $draftCount, 'url' => route('dosen.portfolio.index', ['status' => 'DRAFT'])],
            ['label' => 'Perlu revisi', 'value' => $revisionCount, 'url' => route('dosen.portfolio.index', ['status' => 'REVISION_REQUIRED'])],
            ['label' => 'Dokumen', 'value' => $documentCount, 'url' => route('dosen.documents.index')],
        ] as $metric)
            <a href="{{ $metric['url'] }}" class="df-metric"><span>{{ $metric['label'] }}</span><strong>{{ $metric['value'] }}</strong><x-heroicon-o-arrow-up-right class="h-4 w-4" /></a>
        @endforeach
    </section>

    <div class="df-dashboard-grid">
        <div class="min-w-0 space-y-7">
            <section>
                <x-ui.section-header title="Perlu Tindakan Saya"><x-slot:actions><a class="df-text-link" href="{{ route('dosen.portfolio.index') }}">Lihat semua</a></x-slot:actions></x-ui.section-header>
                <div class="mt-3 divide-y divide-[var(--border)]">
                    @forelse($actionItems as $activity)
                        <x-academic.activity-item :activity="$activity" />
                    @empty
                        <x-ui.empty-state title="Tidak ada tindakan tertunda" description="Draft dan permintaan revisi akan tampil di sini." />
                    @endforelse
                </div>
            </section>
            <section>
                <x-ui.section-header title="Aktivitas Terbaru"><x-slot:actions><a class="df-text-link" href="{{ route('tridharma.index') }}">Lihat Tridharma</a></x-slot:actions></x-ui.section-header>
                <div class="mt-3 divide-y divide-[var(--border)]">
                    @forelse($recentActivities as $activity)
                        <x-academic.activity-item :activity="$activity" />
                    @empty
                        <x-ui.empty-state title="Belum ada aktivitas" description="Catat kegiatan akademik pertama Anda."><x-slot:actions><x-ui.button :href="route('dosen.portfolio.create')" variant="secondary">Tambah Aktivitas</x-ui.button></x-slot:actions></x-ui.empty-state>
                    @endforelse
                </div>
            </section>
        </div>
        <aside class="df-dashboard-aside">
            <section>
                <x-ui.section-header title="Agenda Mendatang"><x-slot:actions><a class="df-text-link" href="{{ route('dosen.calendar.index') }}">Semua agenda</a></x-slot:actions></x-ui.section-header>
                <div class="mt-3 divide-y divide-[var(--border)]">
                    @forelse($upcomingEvents as $event)
                        <a href="{{ route('dosen.calendar.index') }}" class="block py-4"><span class="text-xs font-semibold text-[var(--brand-700)]">{{ $event->starts_at?->locale('id')->translatedFormat('d M Y, H:i') }}</span><span class="mt-1 block text-sm font-semibold">{{ $event->title }}</span></a>
                    @empty
                        <p class="py-5 text-sm text-[var(--text-secondary)]">Belum ada jadwal mendatang.</p>
                    @endforelse
                </div>
            </section>
            <section class="mt-6 border-t border-[var(--border)] pt-5">
                <h2 class="df-section-title">Profil dan CV</h2>
                <a href="{{ route('profile.show') }}" class="df-quick-link"><x-ui.icon name="profile" /><span>Kelola profil akademik</span><x-heroicon-o-chevron-right class="h-4 w-4" /></a>
                <a href="{{ route('profile.show').'#profil-publik' }}" class="df-quick-link"><x-ui.icon name="document" /><span>Pilih template CV</span><x-heroicon-o-chevron-right class="h-4 w-4" /></a>
                <a href="{{ route('dosen.documents.create') }}" class="df-quick-link"><x-heroicon-o-arrow-up-tray class="h-5 w-5" /><span>Unggah dokumen</span><x-heroicon-o-chevron-right class="h-4 w-4" /></a>
            </section>
        </aside>
    </div>
</div>
@endsection
