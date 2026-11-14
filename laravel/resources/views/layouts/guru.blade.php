<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Guru - Binaro' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-binaro.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif; background-color: #F8FAFC; }
        .sidebar-brand-bg { background-color: var(--color-primary); }
        .active-menu-pill { background-color: var(--color-primary); color: #ffffff; }
    </style>
</head>
<body class="text-slate-800 flex min-h-screen bg-canvas">

    <!-- Sidebar 64 (256px) Fixed Left: White Background as in Reference Images -->
    <aside id="portal-sidebar" class="w-64 bg-white border-r border-slate-200/90 text-slate-800 flex flex-col fixed h-screen z-40 shadow-sm -translate-x-full lg:translate-x-0 transition-transform duration-200">
        
        <!-- Brand / Logo Area -->
        <div class="p-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-binaro.svg') }}" alt="Logo Binaro" class="w-10 h-10 rounded-xl">
                <div>
                <h1 class="font-extrabold text-sm leading-tight text-slate-900 tracking-tight">Binaro</h1>
                <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mt-0.5">PORTAL GURU SD</p>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation: Sesuai Referensi Gambar Guru -->
        <nav class="flex-1 p-3.5 space-y-1.5 overflow-y-auto">
            <div class="text-[10px] font-bold text-slate-400 tracking-widest uppercase px-3 pt-1 pb-1">
                MENU UTAMA
            </div>

            <!-- 1. Dashboard -->
            <a href="{{ route('guru.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.dashboard') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-table-cells-large w-4 text-center {{ request()->routeIs('guru.dashboard') ? 'text-white' : 'text-slate-400' }}"></i> 
                <span>Dashboard</span>
            </a>

            <!-- 2. Mapel & Kustomisasi (1 Item Utama dengan Flyout Popover ke Samping Kanan) -->
            @php
                $isMapelChildActive = request()->routeIs('guru.mapel.*') || request()->routeIs('guru.bab.*') || request()->routeIs('guru.sub_bab.*');
                $isMateriChildActive = request()->routeIs('guru.materi.*');
                $isMapelActive = $isMapelChildActive || $isMateriChildActive;
            @endphp
            <div>
                <button type="button" 
                        id="btn-mapel-kustomisasi"
                        onclick="toggleMapelFlyout(this)" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition {{ $isMapelActive ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-book-open-reader w-4 text-center {{ $isMapelActive ? 'text-white' : 'text-slate-400' }}"></i> 
                        <span>Mapel & Kustomisasi</span>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] {{ $isMapelActive ? 'text-white/80' : 'text-slate-400' }}"></i>
                </button>
            </div>

            <!-- 3. Pekerjaan Rumah (PR) -->
            <a href="{{ route('guru.notifikasi_pr.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.notifikasi_pr.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-clipboard-list w-4 text-center {{ request()->routeIs('guru.notifikasi_pr.*') ? 'text-white' : 'text-slate-400' }}"></i> 
                <span>Pekerjaan Rumah (PR)</span>
            </a>

            <!-- 4. Absensi Siswa (Unified Scan + Rekap) -->
            <a href="{{ route('guru.absen.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.absen.*') || request()->routeIs('guru.absensi.*') || request()->is('guru/absen*') || request()->is('guru/absensi*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-id-card-clip w-4 text-center {{ request()->routeIs('guru.absen.*') || request()->routeIs('guru.absensi.*') || request()->is('guru/absen*') || request()->is('guru/absensi*') ? 'text-white' : 'text-slate-400' }}"></i> 
                <span>Absensi Siswa</span>
            </a>

            <!-- 5. Ujian Online -->
            <a href="{{ route('guru.ujian.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.ujian.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-laptop-code w-4 text-center {{ request()->routeIs('guru.ujian.*') ? 'text-white' : 'text-slate-400' }}"></i> 
                <span>Ujian</span>
            </a>

            <!-- 6. Rencana Pelaksanaan Pembelajaran (RPP) -->
            <a href="{{ route('guru.rpp.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.rpp.*') || request()->is('rpp-guru*') || (request()->routeIs('rpp.*') && session('user_type') === 'guru') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-file-lines w-4 text-center {{ request()->routeIs('guru.rpp.*') || request()->is('rpp-guru*') ? 'text-white' : 'text-slate-400' }}"></i> 
                <span class="truncate">Rencana Pelaksanaan Pembelajaran (RPP)</span>
            </a>
        </nav>

        <!-- Bottom User Card & Keluar Portal -->
        <div class="p-4 border-t border-slate-100 space-y-2.5">
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-2.5 flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full sidebar-brand-bg text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                    <i class="fas fa-user-graduate text-xs"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-slate-900 truncate leading-tight">
                        {{ $guru->nama_guru ?? session('user_name', 'Guru') }}
                    </div>
                    <div class="text-[10px] text-slate-500 truncate leading-tight mt-0.5">
                        {{ ($guru->nip ?? session('user_identifier')) ? 'NIP ' . ($guru->nip ?? session('user_identifier')) : 'Portal Guru' }}
                    </div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Yakin ingin keluar?')">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 font-bold text-xs transition">
                    <i class="fas fa-arrow-right-from-bracket text-xs"></i> 
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

        <div id="portal-sidebar-backdrop" onclick="togglePortalSidebar(false)" class="fixed inset-0 bg-slate-900/50 z-[35] hidden" aria-hidden="true"></div>

    <!-- Main Content Area -->
    <main class="flex-1 lg:ml-64 flex flex-col min-w-0">
        <!-- Top Header: Clean, Elegant, Single-Row Layout -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-3.5 flex justify-between items-center sticky top-0 z-30 shadow-xs gap-4 min-h-[64px]">
                <button type="button" onclick="togglePortalSidebar()" aria-label="Buka menu navigasi" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 shrink-0">
                    <i class="fas fa-bars"></i>
                </button>
            <!-- Left: Breadcrumb, Page Title & Subtitle -->
            <div class="flex flex-col justify-center gap-0.5 min-w-0 flex-1 truncate">
                <nav class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 whitespace-nowrap overflow-hidden">
                    @hasSection('breadcrumb_trail')
                        @yield('breadcrumb_trail')
                        @if(View::hasSection('page_title'))
                        <i class="fas fa-chevron-right text-[8px] opacity-60 shrink-0"></i>
                        <span class="text-slate-600 truncate">@yield('page_title')</span>
                        @endif
                    @else
                        <span class="shrink-0 hidden md:inline">{{ config('binaro.nama_sekolah') }}</span>
                        <i class="fas fa-chevron-right text-[8px] opacity-60 shrink-0 hidden md:inline"></i>
                        <a href="{{ route('guru.dashboard') }}" class="font-bold text-primary hover:underline shrink-0">Portal Ruang Guru</a>
                        @if(View::hasSection('page_title'))
                        <i class="fas fa-chevron-right text-[8px] opacity-60 shrink-0"></i>
                        <span class="text-slate-500 truncate">@yield('page_title')</span>
                        @endif
                    @endif
                </nav>
                @hasSection('page_subtitle')
                <p class="hidden md:block text-[10.5px] leading-[14px] text-slate-400 truncate">@yield('page_subtitle')</p>
                @endif
            </div>

            <!-- Right Controls: Header Actions (Jika ada), Search, Semester Badge, Bell, User Avatar -->
            <div class="flex items-center gap-3.5 ml-auto shrink-0 flex-nowrap">
                @yield('header_actions')

                <!-- Search Box -->
                <div class="relative w-44 md:w-56 hidden sm:block">
                    <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" placeholder="Cari materi, PR, siswa..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200/90 text-xs text-slate-700 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary transition">
                </div>

                <!-- Semester Badge -->
                <div class="hidden lg:flex bg-amber-50 text-amber-900 border border-amber-200/90 px-3 py-1.5 rounded-xl text-xs font-bold items-center gap-1.5 shadow-xs whitespace-nowrap">
                    <i class="far fa-calendar-days text-amber-600 text-xs"></i>
                    <span>Semester {{ config('binaro.semester') }} {{ config('binaro.tahun_ajaran') }}</span>
                </div>

                <!-- Notification Bell -->
                <div class="relative w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-600 text-sm transition cursor-pointer shrink-0">
                    <i class="far fa-bell text-sm"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </div>

                <!-- User Profile Circle -->
                <div class="w-9 h-9 rounded-xl sidebar-brand-bg text-white flex items-center justify-center font-bold text-xs shadow-xs cursor-pointer shrink-0">
                    <i class="fas fa-user-tie text-xs"></i>
                </div>
            </div>
        </header>

        <!-- Flash Alerts -->
        @if(session('success'))
        <x-alert type="success" class="mx-8 mt-5">{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
        <x-alert type="error" class="mx-8 mt-5">{{ session('error') }}</x-alert>
        @endif

        @if(session('info'))
        <x-alert type="info" class="mx-8 mt-5">{{ session('info') }}</x-alert>
        @endif

        <!-- Page Content -->
        <div class="p-8 space-y-6 flex-1">
            @yield('content')
        </div>
    </main>

    <!-- Include Pop-Up Modal Action PRD 4.2 -->
    @include('guru.materi.modal_action')

    <!-- Flyout Popover Menu ke Samping Kanan (Muncul saat tombol Mapel & Kustomisasi diklik) -->
    <div id="flyout-mapel-kustomisasi" 
         class="hidden fixed z-50 w-72 bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-2.5 transition-all duration-150 ease-out"
         style="top: 0; left: 0;">
        
        <!-- Header Popover -->
        <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase">PILIHAN AKSES</span>
            <span class="text-[10px] bg-slate-100 text-slate-500 font-semibold px-2 py-0.5 rounded-md">2 Opsi</span>
        </div>

        <div class="p-1 space-y-1.5 mt-1.5">
            <!-- Opsi 1: Lihat Mapel -->
            <a href="{{ route('guru.mapel.browse') }}"
               class="group flex items-start gap-3 p-2.5 rounded-xl transition {{ $isMapelChildActive ? 'bg-primary text-white shadow-sm' : 'hover:bg-slate-50 text-slate-700' }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $isMapelChildActive ? 'bg-white/20 text-white' : 'bg-sky-50 text-primary group-hover:bg-primary group-hover:text-white transition' }}">
                    <i class="fas fa-book-open text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold leading-tight {{ $isMapelChildActive ? 'text-white' : 'text-slate-800' }}">
                        Lihat Mapel
                    </div>
                    <div class="text-[10.5px] mt-0.5 leading-tight {{ $isMapelChildActive ? 'text-white/80' : 'text-slate-400' }}">
                        Daftar mata pelajaran & struktur bab
                    </div>
                </div>
                <i class="fas fa-arrow-right text-[10px] self-center {{ $isMapelChildActive ? 'text-white' : 'text-slate-300 group-hover:text-slate-600 transition' }}"></i>
            </a>

            <!-- Opsi 2: Kustomisasi / Tambah Materi (Langsung Membuka Pop-Up Tambah Materi) -->
            <button type="button"
                    onclick="openTambahMateriFromFlyout()"
                    class="w-full text-left group flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 text-slate-700 transition">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-emerald-50 text-emerald-600 group-hover:bg-primary group-hover:text-white transition">
                    <i class="fas fa-plus text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold leading-tight text-slate-800 group-hover:text-primary transition">
                        Kustomisasi / Tambah Materi
                    </div>
                    <div class="text-[10.5px] mt-0.5 leading-tight text-slate-400">
                        Buka form tambah materi pelajaran baru
                    </div>
                </div>
                <i class="fas fa-arrow-up-right-from-square text-[10px] self-center text-slate-300 group-hover:text-slate-600 transition"></i>
            </button>
        </div>

        <!-- Footer Link: Kelola Silabus & Urutan Bab -->
        <div class="pt-2 mt-1 border-t border-slate-100 px-1">
            <a href="{{ route('guru.materi.index') }}"
               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-[11px] font-semibold text-slate-500 hover:text-primary hover:bg-slate-50 transition">
                <span class="flex items-center gap-2">
                    <i class="fas fa-layer-group text-[10px] text-slate-400"></i>
                    <span>Kelola Silabus, Bab & Modul</span>
                </span>
                <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            </a>
        </div>
    </div>

    <script>
        function toggleMapelFlyout(button) {
            const popover = document.getElementById('flyout-mapel-kustomisasi');
            if (!popover || !button) return;
            
            const isHidden = popover.classList.contains('hidden');
            if (isHidden) {
                const rect = button.getBoundingClientRect();
                popover.style.top = `${rect.top}px`;
                popover.style.left = `${rect.right + 12}px`;
                popover.classList.remove('hidden');
            } else {
                popover.classList.add('hidden');
            }
        }

        // Buka Pop-Up Tambah Materi dari Flyout
        function openTambahMateriFromFlyout() {
            const popover = document.getElementById('flyout-mapel-kustomisasi');
            if (popover) popover.classList.add('hidden');
            if (typeof openModalAction === 'function') {
                openModalAction('tambah', 'materi');
            }
        }

        // Tutup flyout saat mengklik di luar area flyout / tombol
        document.addEventListener('click', function(e) {
            const popover = document.getElementById('flyout-mapel-kustomisasi');
            const trigger = document.getElementById('btn-mapel-kustomisasi');
            if (!popover || !trigger) return;
            
            if (!popover.classList.contains('hidden')) {
                if (!popover.contains(e.target) && !trigger.contains(e.target)) {
                    popover.classList.add('hidden');
                }
            }
        });

        // Tutup dengan tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const popover = document.getElementById('flyout-mapel-kustomisasi');
                if (popover) popover.classList.add('hidden');
            }
        });
    </script>
    <script>
        function togglePortalSidebar(force) {
            const sb = document.getElementById('portal-sidebar');
            const bd = document.getElementById('portal-sidebar-backdrop');
            if (!sb || !bd) return;
            const willOpen = typeof force === 'boolean' ? force : bd.classList.contains('hidden');
            sb.classList.toggle('-translate-x-full', !willOpen);
            bd.classList.toggle('hidden', !willOpen);
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') togglePortalSidebar(false);
        });
        // Tandai item nav aktif untuk pembaca layar
        document.querySelectorAll('aside nav a').forEach((a) => {
            if (a.querySelector('.active-menu-pill, .nav-item-active')) a.setAttribute('aria-current', 'page');
        });
    </script></body>
</html>
