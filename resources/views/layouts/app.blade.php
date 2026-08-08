@php
    $user = auth()->user();
    $isAuthLayout = $authLayout ?? false;
    $academicPeriod = now()->year.'/'.(now()->year + 1).' - '.(now()->month >= 8 || now()->month <= 1 ? 'Ganjil' : 'Genap');
    $unreadInbox = 0;
    $upcomingAgenda = 0;
    $availableRoles = array_values(array_filter((array) session('dosen_farmasi.available_roles', $user ? [$user->role] : [])));
    $canSwitchRole = count($availableRoles) > 1;
    $roleLabel = $user?->isAdmin() ? 'Admin' : 'Dosen';

    if ($user) {
        $lecturerId = (string) $user->core_lecturer_id;
        $unreadInbox = \App\Models\InboxItem::query()->where('lecturer_core_id', $lecturerId)->where('status', 'UNREAD')->count();
        $upcomingAgenda = \App\Models\CalendarEvent::query()->where('lecturer_core_id', $lecturerId)->where('starts_at', '>=', now())->count();
    }

    $navGroups = $user ? [
        'Ruang Kerja' => [
            ['label' => 'Beranda', 'icon' => 'home', 'href' => route('dosen.dashboard'), 'active' => request()->routeIs('dosen.dashboard')],
            ['label' => 'Tridharma', 'icon' => 'tridharma', 'href' => route('tridharma.index'), 'active' => request()->routeIs('tridharma.*')],
            ['label' => 'Portofolio', 'icon' => 'portfolio', 'href' => route('dosen.portfolio.index'), 'active' => request()->routeIs('dosen.portfolio.*')],
            ['label' => 'Dokumen', 'icon' => 'document', 'href' => route('dosen.documents.index'), 'active' => request()->routeIs('dosen.documents.*')],
        ],
        'Komunikasi' => [
            ['label' => 'Inbox', 'icon' => 'inbox', 'href' => route('dosen.inbox.index'), 'active' => request()->routeIs('dosen.inbox.*'), 'badge' => $unreadInbox],
            ['label' => 'Agenda', 'icon' => 'calendar', 'href' => route('dosen.calendar.index'), 'active' => request()->routeIs('dosen.calendar.*'), 'badge' => $upcomingAgenda],
            ['label' => 'Notifikasi', 'icon' => 'bell', 'href' => route('dosen.notifications.index'), 'active' => request()->routeIs('dosen.notifications.*')],
        ],
        'Akademik' => [
            ['label' => 'Profil Akademik', 'icon' => 'profile', 'href' => route('profile.show'), 'active' => request()->routeIs('profile.show')],
        ],
    ] : [];

    if ($user?->isAdmin()) {
        $navGroups['Administrasi'] = [
            ['label' => 'Ruang Kontrol Admin', 'icon' => 'portfolio', 'href' => route('filament.admin.pages.admin-dashboard'), 'active' => request()->is('admin*')],
        ];
    }

    $bottomNav = $user ? [
        ['label' => 'Beranda', 'icon' => 'home', 'href' => route('dosen.dashboard'), 'active' => request()->routeIs('dosen.dashboard')],
        ['label' => 'Tridharma', 'icon' => 'tridharma', 'href' => route('tridharma.index'), 'active' => request()->routeIs('tridharma.*')],
        ['label' => 'Agenda', 'icon' => 'calendar', 'href' => route('dosen.calendar.index'), 'active' => request()->routeIs('dosen.calendar.*')],
        ['label' => 'Inbox', 'icon' => 'inbox', 'href' => route('dosen.inbox.index'), 'active' => request()->routeIs('dosen.inbox.*')],
        ['label' => 'Profil', 'icon' => 'profile', 'href' => route('profile.show'), 'active' => request()->routeIs('profile.show')],
    ] : [];

    $breadcrumb = trim($__env->yieldContent('breadcrumb')) ?: ($title ?? 'Dosen Farmasi');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dosen Farmasi UBP' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans text-[var(--text-primary)] antialiased">
    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-[var(--radius-sm)] focus:bg-white focus:px-4 focus:py-3 focus:text-sm focus:font-semibold focus:text-[var(--brand-900)] focus:shadow-[var(--shadow-floating)]">
        Lewati ke konten utama
    </a>

    @if($isAuthLayout)
        <main class="min-h-screen bg-[linear-gradient(135deg,#eef4fb_0%,#fbfcff_48%,#fff8ee_100%)]">
            @yield('content')
        </main>
    @else
        <div class="df-shell lg:flex">
            @auth
                <aside class="df-sidebar sticky top-0 hidden h-screen shrink-0 flex-col px-4 py-5 lg:flex">
                    <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-3 rounded-[var(--radius-md)] p-2 transition hover:bg-[var(--brand-50)]">
                        <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="h-12 w-12 rounded-[var(--radius-md)] bg-white object-contain p-1.5 shadow-sm ring-1 ring-[var(--border)]">
                        <span>
                            <span class="block text-sm font-bold tracking-normal text-[var(--brand-950)]">Dosen Farmasi</span>
                            <span class="block text-xs font-semibold uppercase tracking-[0.12em] text-[var(--text-muted)]">UBP Karawang</span>
                        </span>
                    </a>

                    <div class="mt-5 rounded-[var(--radius-lg)] border border-[var(--border)] bg-[linear-gradient(135deg,#ffffff,#f4f7fb)] p-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-[var(--radius-md)] bg-[var(--brand-900)] text-sm font-bold text-white shadow-sm">
                                {{ str($user->name)->substr(0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[var(--text-primary)]">{{ $user->name }}</p>
                                <p class="truncate text-xs text-[var(--text-secondary)]">{{ $user->isAdmin() ? 'Administrator Akademik' : 'Program Studi Farmasi' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="df-pill min-h-8 flex-1 justify-center bg-white text-[var(--brand-900)]">{{ $roleLabel }}</span>
                            @if($canSwitchRole)
                                <a href="{{ route('role.select') }}" class="df-pill min-h-8 justify-center text-[var(--brand-700)] hover:bg-[var(--brand-50)]">
                                    Ganti
                                </a>
                            @endif
                        </div>
                    </div>

                    <nav class="scrollbar-none mt-7 flex-1 space-y-5 overflow-y-auto pr-1" aria-label="Navigasi utama">
                        @foreach($navGroups as $group => $items)
                            <div>
                                <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--text-muted)]">{{ $group }}</p>
                                <div class="mt-2 space-y-1">
                                    @foreach($items as $item)
                                        <a href="{{ $item['href'] }}" class="df-nav-link" aria-current="{{ $item['active'] ? 'page' : 'false' }}">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-[var(--radius-sm)] bg-white text-[var(--brand-700)] ring-1 ring-[var(--border)]">
                                                <x-ui.icon :name="$item['icon']" class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                            @if(($item['badge'] ?? 0) > 0)
                                                <span class="rounded-full bg-[var(--danger)] px-2 py-0.5 text-[11px] font-bold text-white">{{ $item['badge'] }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>

                    <div class="mt-5 px-2">
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="inline-flex min-h-10 w-full items-center justify-center rounded-[var(--radius-sm)] text-sm font-semibold text-[var(--text-secondary)] transition hover:bg-rose-50 hover:text-rose-700">
                                Keluar
                            </button>
                        </form>
                    </div>
                </aside>
            @endauth

            <div class="min-w-0 flex-1">
                <header class="df-topbar sticky top-0 z-30">
                    <div class="mx-auto flex max-w-[1440px] items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <a href="{{ auth()->check() ? route('dosen.dashboard') : route('login') }}" class="flex min-w-0 flex-1 items-center gap-3 lg:hidden">
                            <img src="{{ asset('images/logo-fakultas-farmasi-ubp.png') }}" alt="Logo Fakultas Farmasi UBP" class="h-10 w-10 shrink-0 rounded-[var(--radius-sm)] bg-white object-contain p-1 ring-1 ring-[var(--border)]">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold text-[var(--brand-950)]">Dosen Farmasi UBP</span>
                                <span class="block truncate text-xs text-[var(--text-muted)]">{{ $roleLabel }} - {{ $academicPeriod }}</span>
                            </span>
                        </a>

                        <div class="hidden min-w-0 flex-1 lg:block">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--text-muted)]">Ruang Akademik</p>
                            <p class="truncate text-sm font-semibold text-[var(--text-primary)]">{{ $breadcrumb }}</p>
                        </div>

                        @auth
                            <form method="get" action="{{ route('dosen.portfolio.index') }}" class="hidden w-full max-w-xs xl:block">
                                <label for="global-search" class="sr-only">Cari portofolio</label>
                                <input id="global-search" name="q" class="df-field" placeholder="Cari aktivitas atau dokumen" value="{{ request('q') }}">
                            </form>

                            <div class="hidden items-center gap-2 md:flex">
                                <x-ui.filter-chip>{{ $academicPeriod }}</x-ui.filter-chip>
                                <x-ui.icon-button :href="route('dosen.calendar.index')" label="Agenda mendatang">
                                    <span class="relative">
                                        <x-ui.icon name="calendar" />
                                        @if($upcomingAgenda)
                                            <span class="absolute -right-2 -top-2 rounded-full bg-[var(--danger)] px-1.5 text-[10px] font-bold text-white">{{ $upcomingAgenda }}</span>
                                        @endif
                                    </span>
                                </x-ui.icon-button>
                                <x-ui.icon-button :href="route('dosen.notifications.index')" label="Notifikasi">
                                    <span class="relative">
                                        <x-ui.icon name="bell" />
                                        @if($unreadInbox)
                                            <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-[var(--danger)]"></span>
                                        @endif
                                    </span>
                                </x-ui.icon-button>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                @if($canSwitchRole)
                                    <a href="{{ route('role.select') }}" class="hidden df-button df-button-secondary lg:inline-flex">Ganti Peran</a>
                                @endif
                                @if($user->isAdmin())
                                    <a href="{{ route('filament.admin.pages.admin-dashboard') }}" class="hidden df-button df-button-primary sm:inline-flex">Ruang Kontrol</a>
                                @endif
                                <a href="{{ route('profile.show') }}" class="grid h-10 w-10 place-items-center rounded-full bg-[var(--brand-800)] text-sm font-bold text-white" aria-label="Buka profil">
                                    {{ str($user->name)->substr(0, 1) }}
                                </a>
                            </div>
                        @endauth
                    </div>
                </header>

                <main id="konten-utama" class="df-content">
                    @if(session('status'))
                        <div class="mb-5 rounded-[var(--radius-md)] border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900" role="status">
                            {{ session('status') }}
                        </div>
                    @endif
                    @yield('content')
                </main>

                @auth
                    @if($canSwitchRole)
                        <a href="{{ route('role.select') }}" class="fixed bottom-[5.35rem] right-4 z-40 inline-flex min-h-11 items-center justify-center rounded-full bg-[var(--brand-900)] px-4 text-sm font-bold text-white shadow-[var(--shadow-floating)] lg:hidden">
                            Ganti Peran
                        </a>
                    @endif
                    <x-ui.mobile-bottom-nav :items="$bottomNav" />
                @endauth
            </div>
        </div>
    @endif
</body>
</html>
