<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel Admin - Binaro' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --binaro-navy: #13527D;
            --binaro-navy-soft: #1E5D88;
            --binaro-line: #E6EBF2;
            --binaro-canvas: #F8FAFC;
        }
        body { font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif; background-color: #F8FAFC; }
        .sidebar-brand-bg { background-color: #13527D; }
        .active-menu-pill { background-color: #13527D; color: #ffffff; }
    </style>
</head>
<body class="text-slate-800 flex min-h-screen bg-[#F8FAFC]">

    <!-- Sidebar 64 (256px) Fixed Left: Desain identik dengan Portal Guru -->
    <aside class="w-64 bg-white border-r border-slate-200/90 text-slate-800 flex flex-col fixed h-screen z-20 shadow-sm">

        <!-- Brand / Logo Area -->
        <div class="p-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl sidebar-brand-bg text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    <i class="fas fa-graduation-cap text-base"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-sm leading-tight text-slate-900 tracking-tight">Binaro</h1>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mt-0.5">PORTAL ADMIN SD</p>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 p-3.5 space-y-1.5 overflow-y-auto">
            <div class="text-[10px] font-bold text-slate-400 tracking-widest uppercase px-3 pt-1 pb-1">
                MENU UTAMA
            </div>

            <!-- 1. Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('admin.dashboard') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-table-cells-large w-4 text-center {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Dashboard</span>
            </a>

            <!-- 2. Akun Guru -->
            <a href="{{ route('guru.data.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.data.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-user-tie w-4 text-center {{ request()->routeIs('guru.data.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Akun Guru</span>
            </a>

            <!-- 3. Akun Siswa -->
            <a href="{{ route('siswa.data.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('siswa.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-user-graduate w-4 text-center {{ request()->routeIs('siswa.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Akun Siswa</span>
            </a>

            <!-- 4. Jadwal Pelajaran -->
            <a href="{{ route('jadwal.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('jadwal.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-calendar-days w-4 text-center {{ request()->routeIs('jadwal.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Jadwal Pelajaran</span>
            </a>

            <!-- 5. Kelola Mapel -->
            <a href="{{ route('mapel.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('mapel.*') || request()->routeIs('admin.mapel.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-book-open w-4 text-center {{ request()->routeIs('mapel.*') || request()->routeIs('admin.mapel.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Kelola Mapel</span>
            </a>

            <!-- 6. Kelola RPP -->
            <a href="{{ route('rpp.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('rpp.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-file-lines w-4 text-center {{ request()->routeIs('rpp.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Kelola RPP</span>
            </a>

            <!-- 7. Kelola Absensi -->
            <a href="{{ route('absensi.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('absensi.*') || request()->routeIs('admin.absensi.rekap') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-clipboard-check w-4 text-center {{ request()->routeIs('absensi.*') || request()->routeIs('admin.absensi.rekap') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Kelola Absensi</span>
            </a>

            <!-- 8. Pengaturan Jam Absen -->
            <a href="{{ route('admin.absensi.settings') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('admin.absensi.settings*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-clock w-4 text-center {{ request()->routeIs('admin.absensi.settings*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Pengaturan Jam Absen</span>
            </a>

            <!-- 9. QR Siswa -->
            <a href="{{ route('admin.qr.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs {{ request()->routeIs('admin.qr.*') ? 'active-menu-pill font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition' }}">
                <i class="fas fa-id-card w-4 text-center {{ request()->routeIs('admin.qr.*') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>QR Siswa</span>
            </a>
        </nav>

        <!-- Bottom User Card & Keluar Portal -->
        <div class="p-4 border-t border-slate-100 space-y-2.5">
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-2.5 flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full sidebar-brand-bg text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                    <i class="fas fa-user-shield text-xs"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-slate-900 truncate leading-tight">
                        {{ session('user_name', 'Administrator') }}
                    </div>
                    <div class="text-[10px] text-slate-500 truncate leading-tight mt-0.5">
                        {{ session('user_type') === 'guru' ? 'Guru Pengajar' : 'Administrator' }}
                    </div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Yakin ingin keluar portal?')">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 font-bold text-xs transition">
                    <i class="fas fa-arrow-right-from-bracket text-xs"></i>
                    <span>Keluar Portal</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 ml-64 flex flex-col min-w-0">
        <!-- Top Header: Desain identik dengan Portal Guru -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-3.5 flex justify-between items-center sticky top-0 z-30 shadow-xs gap-4 min-h-[64px]">
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
                        <a href="{{ route('admin.dashboard') }}" class="font-bold text-[#13527D] hover:underline shrink-0">Panel Admin</a>
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
                    <input type="text" placeholder="Cari guru, siswa, jadwal..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200/90 text-xs text-slate-700 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#13527D] transition">
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
        <div class="mx-8 mt-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div class="mx-8 mt-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-circle-exclamation text-rose-600 text-base"></i>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(session('info'))
        <div class="mx-8 mt-5 p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-xs flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-circle-info text-sky-600 text-base"></i>
                <span class="font-semibold">{{ session('info') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-sky-500 hover:text-sky-700"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="mx-8 mt-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-sm">
            <div class="font-bold mb-1.5 flex items-center gap-2"><i class="fas fa-triangle-exclamation"></i> Terjadi Kesalahan Input:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Page Content -->
        <div class="p-8 space-y-6 flex-1">
            @yield('content')
        </div>
    </main>
</body>
</html>
