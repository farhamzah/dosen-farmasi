@extends('layouts.app', ['title' => 'Dokumen Saya'])

@section('breadcrumb', 'Dokumen')


@section('content')
<div class="space-y-6">
    <x-ui.page-header
        eyebrow="Dokumen Akademik"
        title="Dokumen Saya"
        description="Kelola bukti kegiatan, sertifikat, surat tugas, dan arsip akademik yang terhubung ke portofolio."
    >
        <x-slot:actions>
            <x-ui.button :href="route('dosen.documents.create')">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Unggah Dokumen
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <section class="grid gap-4 md:grid-cols-3">
        <x-ui.stat label="Total Dokumen" :value="$documentCount" tone="brand" />
        <x-ui.stat label="Menunggu" :value="$pendingCount" tone="warning" />
        <x-ui.stat label="Terverifikasi" :value="$verifiedCount" tone="success" />
    </section>

    <section class="df-card overflow-hidden">
        <form method="get" class="flex flex-wrap items-center gap-2 border-b border-[var(--border)] py-4">
            <input type="search" name="q" value="{{ request('q') }}" aria-label="Cari dokumen" placeholder="Cari judul dokumen" class="df-field min-w-0 flex-1">
            <button class="df-button df-button-secondary"><x-heroicon-o-magnifying-glass class="h-4 w-4" />Cari</button>
            @if(request()->filled('q'))<a href="{{ route('dosen.documents.index') }}" class="df-text-link">Reset</a>@endif
        </form>
        <div class="border-b border-[var(--border)] p-5">
            <x-ui.section-header title="Arsip Terbaru" description="{{ $documents->total() }} dokumen ditemukan." />
        </div>

        <div class="divide-y divide-[var(--border)]">
            @forelse($documents as $document)
                @php
                    $status = (string) $document->verification_status;
                    $tone = str_contains($status, 'VERIFIED') ? 'success' : (str_contains($status, 'REJECTED') ? 'danger' : 'warning');
                @endphp
                <div class="grid gap-4 p-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                    <div class="flex min-w-0 gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[var(--radius-md)] bg-[var(--brand-50)] text-[var(--brand-700)]">
                            <x-ui.icon name="document" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-bold text-[var(--text-primary)]">{{ $document->title }}</p>
                                <x-ui.badge :tone="$tone">{{ \App\Support\PortfolioUi::statusLabel($status) }}</x-ui.badge>
                            </div>
                            <p class="mt-1 truncate text-sm font-semibold text-[var(--text-secondary)]">{{ $document->document_type }} - {{ $document->original_filename }}</p>
                            <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $document->issuer ?: 'Penerbit belum diisi' }} - {{ optional($document->document_date)->format('d M Y') ?: 'Tanggal belum diisi' }} - {{ str($document->visibility)->title() }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        <x-ui.button :href="route('documents.download', $document)" variant="secondary">Unduh</x-ui.button>
                        @can('delete', $document)
                            <form method="post" action="{{ route('dosen.documents.destroy', $document) }}">
                                @csrf
                                @method('DELETE')
                                <button class="df-button df-button-secondary text-rose-700">Hapus</button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="p-5">
                    <x-ui.empty-state :title="request()->filled('q') ? 'Dokumen tidak ditemukan' : 'Belum ada dokumen'" :description="request()->filled('q') ? 'Tidak ada dokumen yang sesuai dengan pencarian Anda.' : 'Dokumen pendukung portofolio Anda akan tampil di sini.'" icon="document">
                        <x-slot:actions>
                            @if(request()->filled('q'))
                                <x-ui.button :href="route('dosen.documents.index')">Hapus pencarian</x-ui.button>
                            @else
                                <x-ui.button :href="route('dosen.documents.create')">Unggah dokumen</x-ui.button>
                            @endif
                        </x-slot:actions>
                    </x-ui.empty-state>
                </div>
            @endforelse
        </div>
    </section>

    <div>{{ $documents->links() }}</div>
</div>
@endsection
