@props([
    'activities',
    'groupedActivities',
    'domain' => null,
    'viewMode' => 'auto',
    'tableKind' => null,
])

@php
    $tableClass = $viewMode === 'card' ? 'hidden' : 'hidden md:block';
    $cardClass = $viewMode === 'table' ? 'md:hidden' : 'block';
    $dateLabel = fn ($activity) => \App\Support\IndonesianDateFormatter::date(\App\Support\PortfolioUi::activityDate($activity));
    $statusLabel = fn ($activity) => \App\Support\PortfolioUi::statusLabel($activity->verification_status);
    $statusTone = fn ($activity) => \App\Support\PortfolioUi::statusTone($activity->verification_status);
    $typeLabel = fn ($activity) => \App\Support\PortfolioUi::typeLabel($activity->activity_type, $domain);
    $sourceLabel = fn ($activity) => \App\Support\PortfolioUi::sourceLabel($activity);
    $periodLabel = fn ($activity) => \App\Support\PortfolioUi::academicYearLabel($activity);
    $documentLabel = fn ($activity) => \App\Support\PortfolioUi::documentLabel($activity);
    $linkLabel = fn ($activity) => \App\Support\PortfolioUi::externalLinkLabel($activity);
    $kind = $tableKind ?: \App\Support\PortfolioUi::tableKind($domain, request('subcategory'));
    $groups = $groupedActivities->isNotEmpty() ? $groupedActivities : collect(['' => $activities->getCollection()]);
@endphp

@forelse($groups as $group => $items)
    @if(filled($group))
        <h2 class="mt-5 text-sm font-extrabold uppercase tracking-[0.12em] text-[var(--text-muted)]">{{ $group }}</h2>
    @endif

    <div class="{{ $tableClass }} overflow-hidden rounded-[var(--radius-lg)] border border-[var(--border)] bg-white shadow-sm">
        <div class="max-h-[620px] overflow-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="sticky top-0 z-10 bg-slate-50 text-xs font-extrabold uppercase tracking-[0.08em] text-[var(--text-muted)]">
                    <tr>
                        @if($kind === 'pendidikan')
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Kegiatan</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Kelas/Mahasiswa</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="hidden px-4 py-3 lg:table-cell">SKS/Beban</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Sumber</th>
                        @elseif($kind === 'publikasi')
                            <th class="px-4 py-3">Tahun</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Jurnal/Prosiding</th>
                            <th class="px-4 py-3">Penulis</th>
                            <th class="hidden px-4 py-3 xl:table-cell">DOI/URL</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Indeksasi</th>
                        @elseif($kind === 'pengabdian')
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Mitra/Sasaran</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Lokasi</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Luaran</th>
                            <th class="px-4 py-3">Status</th>
                        @elseif($kind === 'hki')
                            <th class="px-4 py-3">Tahun</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Nomor Permohonan</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Nomor Pencatatan</th>
                            <th class="px-4 py-3">Pemegang Hak</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Dokumen</th>
                        @elseif($kind === 'buku')
                            <th class="px-4 py-3">Tahun</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Penerbit</th>
                            <th class="hidden px-4 py-3 xl:table-cell">ISBN</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Status</th>
                        @elseif($kind === 'penelitian')
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Skema</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Sumber Dana</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Luaran</th>
                            <th class="px-4 py-3">Status</th>
                        @else
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Peran</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Sumber</th>
                        @endif
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border)]">
                    @foreach($items as $activity)
                        <tr class="min-h-16 cursor-pointer transition hover:bg-[var(--brand-50)]" onclick="if (! event.target.closest('a, button, details, summary')) window.location='{{ route('dosen.portfolio.show', $activity) }}'">
                            @if($kind === 'pendidikan')
                                <td class="px-4 py-4 align-top font-semibold text-[var(--text-secondary)]">{{ $periodLabel($activity) }}</td>
                                <td class="max-w-xs px-4 py-4 align-top">
                                    <a href="{{ route('dosen.portfolio.show', $activity) }}" class="line-clamp-2 font-extrabold text-[var(--text-primary)] hover:text-[var(--brand-700)]">{{ $activity->title }}</a>
                                    <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $activity->category?->name }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">{{ $typeLabel($activity) }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $activity->institution_name ?: '-' }}</td>
                                <td class="px-4 py-4 align-top">{{ $activity->lecturer_role ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top lg:table-cell">{{ $activity->personal_notes ?: '-' }}</td>
                                <td class="px-4 py-4 align-top"><x-ui.badge :tone="$statusTone($activity)"><span aria-hidden="true">●</span> {{ $statusLabel($activity) }}</x-ui.badge></td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $sourceLabel($activity) }}</td>
                            @elseif($kind === 'publikasi')
                                <td class="px-4 py-4 align-top font-semibold text-[var(--text-secondary)]">{{ $activity->start_date?->format('Y') ?: ($activity->academic_year ?: '-') }}</td>
                                <td class="max-w-xs px-4 py-4 align-top">
                                    <a href="{{ route('dosen.portfolio.show', $activity) }}" class="line-clamp-2 font-extrabold text-[var(--text-primary)] hover:text-[var(--brand-700)]">{{ $activity->title }}</a>
                                    <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $periodLabel($activity) }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">{{ $typeLabel($activity) }}</td>
                                <td class="hidden px-4 py-4 align-top lg:table-cell">{{ $activity->institution_name ?: '-' }}</td>
                                <td class="px-4 py-4 align-top">{{ $activity->lecturer_role ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $linkLabel($activity) }}</td>
                                <td class="px-4 py-4 align-top"><x-ui.badge :tone="$statusTone($activity)"><span aria-hidden="true">●</span> {{ $statusLabel($activity) }}</x-ui.badge></td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $activity->personal_notes ?: '-' }}</td>
                            @elseif($kind === 'pengabdian')
                                <td class="px-4 py-4 align-top font-semibold text-[var(--text-secondary)]">{{ $dateLabel($activity) }}</td>
                                <td class="max-w-xs px-4 py-4 align-top">
                                    <a href="{{ route('dosen.portfolio.show', $activity) }}" class="line-clamp-2 font-extrabold text-[var(--text-primary)] hover:text-[var(--brand-700)]">{{ $activity->title }}</a>
                                    <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $periodLabel($activity) }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">{{ $typeLabel($activity) }}</td>
                                <td class="hidden px-4 py-4 align-top lg:table-cell">{{ $activity->institution_name ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $activity->location ?: '-' }}</td>
                                <td class="px-4 py-4 align-top">{{ $activity->lecturer_role ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $documentLabel($activity) }}</td>
                                <td class="px-4 py-4 align-top"><x-ui.badge :tone="$statusTone($activity)"><span aria-hidden="true">●</span> {{ $statusLabel($activity) }}</x-ui.badge></td>
                            @elseif($kind === 'hki')
                                <td class="px-4 py-4 align-top font-semibold text-[var(--text-secondary)]">{{ $activity->start_date?->format('Y') ?: ($activity->academic_year ?: '-') }}</td>
                                <td class="max-w-xs px-4 py-4 align-top">
                                    <a href="{{ route('dosen.portfolio.show', $activity) }}" class="line-clamp-2 font-extrabold text-[var(--text-primary)] hover:text-[var(--brand-700)]">{{ $activity->title }}</a>
                                    <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $sourceLabel($activity) }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">{{ $typeLabel($activity) }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $activity->source_entity ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $activity->personal_notes ?: '-' }}</td>
                                <td class="px-4 py-4 align-top">{{ $activity->lecturer_role ?: '-' }}</td>
                                <td class="px-4 py-4 align-top"><x-ui.badge :tone="$statusTone($activity)"><span aria-hidden="true">●</span> {{ $statusLabel($activity) }}</x-ui.badge></td>
                                <td class="hidden px-4 py-4 align-top lg:table-cell">{{ $documentLabel($activity) }}</td>
                            @elseif($kind === 'buku')
                                <td class="px-4 py-4 align-top font-semibold text-[var(--text-secondary)]">{{ $activity->start_date?->format('Y') ?: ($activity->academic_year ?: '-') }}</td>
                                <td class="max-w-xs px-4 py-4 align-top">
                                    <a href="{{ route('dosen.portfolio.show', $activity) }}" class="line-clamp-2 font-extrabold text-[var(--text-primary)] hover:text-[var(--brand-700)]">{{ $activity->title }}</a>
                                    <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $periodLabel($activity) }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">{{ $activity->lecturer_role ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $activity->institution_name ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $activity->source_entity ?: '-' }}</td>
                                <td class="px-4 py-4 align-top">{{ $typeLabel($activity) }}</td>
                                <td class="px-4 py-4 align-top"><x-ui.badge :tone="$statusTone($activity)"><span aria-hidden="true">●</span> {{ $statusLabel($activity) }}</x-ui.badge></td>
                            @elseif($kind === 'penelitian')
                                <td class="px-4 py-4 align-top font-semibold text-[var(--text-secondary)]">{{ $dateLabel($activity) }}</td>
                                <td class="max-w-xs px-4 py-4 align-top">
                                    <a href="{{ route('dosen.portfolio.show', $activity) }}" class="line-clamp-2 font-extrabold text-[var(--text-primary)] hover:text-[var(--brand-700)]">{{ $activity->title }}</a>
                                    <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $periodLabel($activity) }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">{{ $typeLabel($activity) }}</td>
                                <td class="px-4 py-4 align-top">{{ $activity->lecturer_role ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top lg:table-cell">{{ $activity->institution_name ?: '-' }}</td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $documentLabel($activity) }}</td>
                                <td class="px-4 py-4 align-top"><x-ui.badge :tone="$statusTone($activity)"><span aria-hidden="true">●</span> {{ $statusLabel($activity) }}</x-ui.badge></td>
                            @else
                                <td class="px-4 py-4 align-top font-semibold text-[var(--text-secondary)]">{{ $dateLabel($activity) }}</td>
                                <td class="max-w-xs px-4 py-4 align-top">
                                    <a href="{{ route('dosen.portfolio.show', $activity) }}" class="line-clamp-2 font-extrabold text-[var(--text-primary)] hover:text-[var(--brand-700)]">{{ $activity->title }}</a>
                                    <p class="mt-1 text-xs font-semibold text-[var(--text-muted)]">{{ $periodLabel($activity) }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">{{ $activity->category?->name ?: '-' }}</td>
                                <td class="px-4 py-4 align-top">{{ $typeLabel($activity) }}</td>
                                <td class="hidden px-4 py-4 align-top lg:table-cell">{{ $activity->lecturer_role ?: '-' }}</td>
                                <td class="px-4 py-4 align-top"><x-ui.badge :tone="$statusTone($activity)"><span aria-hidden="true">●</span> {{ $statusLabel($activity) }}</x-ui.badge></td>
                                <td class="hidden px-4 py-4 align-top xl:table-cell">{{ $sourceLabel($activity) }}</td>
                            @endif
                            <td class="px-4 py-4 text-right align-top">
                                <details class="relative inline-block">
                                    <summary class="grid h-9 w-9 cursor-pointer list-none place-items-center rounded-full border border-[var(--border)] bg-white font-black text-[var(--brand-800)]" aria-label="Aksi {{ $activity->title }}">...</summary>
                                    <div class="absolute right-0 z-20 mt-2 w-40 rounded-[var(--radius-sm)] border border-[var(--border)] bg-white p-1 text-left shadow-[var(--shadow-floating)]">
                                        <a href="{{ route('dosen.portfolio.show', $activity) }}" class="block rounded-md px-3 py-2 font-semibold hover:bg-[var(--brand-50)]">Detail</a>
                                        @can('update', $activity)
                                            <a href="{{ route('dosen.portfolio.edit', $activity) }}" class="block rounded-md px-3 py-2 font-semibold hover:bg-[var(--brand-50)]">Edit</a>
                                        @endcan
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="{{ $cardClass }} space-y-3">
        @foreach($items as $activity)
            <article class="df-card df-interactive p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[var(--text-muted)]">{{ $dateLabel($activity) }}</p>
                        <h3 class="mt-1 line-clamp-2 text-base font-extrabold text-[var(--text-primary)]">
                            <a href="{{ route('dosen.portfolio.show', $activity) }}">{{ $activity->title }}</a>
                        </h3>
                    </div>
                    <details class="relative shrink-0">
                        <summary class="grid h-9 w-9 cursor-pointer list-none place-items-center rounded-full border border-[var(--border)] bg-white font-black text-[var(--brand-800)]" aria-label="Aksi">...</summary>
                        <div class="absolute right-0 z-20 mt-2 w-36 rounded-[var(--radius-sm)] border border-[var(--border)] bg-white p-1 shadow-[var(--shadow-floating)]">
                            <a href="{{ route('dosen.portfolio.show', $activity) }}" class="block rounded-md px-3 py-2 font-semibold">Detail</a>
                            @can('update', $activity)
                                <a href="{{ route('dosen.portfolio.edit', $activity) }}" class="block rounded-md px-3 py-2 font-semibold">Edit</a>
                            @endcan
                        </div>
                    </details>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <x-ui.badge tone="brand">{{ $typeLabel($activity) }}</x-ui.badge>
                    <x-ui.badge :tone="$statusTone($activity)">{{ $statusLabel($activity) }}</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $sourceLabel($activity) }}</x-ui.badge>
                </div>
                <dl class="mt-4 grid gap-2 text-sm text-[var(--text-secondary)]">
                    <div><dt class="inline font-bold text-[var(--text-primary)]">Peran:</dt> <dd class="inline">{{ $activity->lecturer_role ?: '-' }}</dd></div>
                    <div><dt class="inline font-bold text-[var(--text-primary)]">Periode:</dt> <dd class="inline">{{ $periodLabel($activity) }}</dd></div>
                </dl>
            </article>
        @endforeach
    </div>
@empty
    <x-ui.empty-state
        title="Belum ada kegiatan pada daftar ini"
        description="Gunakan tombol tambah kegiatan atau ubah filter untuk melihat data lain."
        class="bg-white"
    >
        <x-slot:actions>
            <x-ui.button :href="route('dosen.portfolio.create')">Tambah Kegiatan</x-ui.button>
        </x-slot:actions>
    </x-ui.empty-state>
@endforelse
