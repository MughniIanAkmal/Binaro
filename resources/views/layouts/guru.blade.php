<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Guru - Binaro SD' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Instrument Sans', sans-serif; background-color: #F8FAFC; }
        .sidebar-bg { background-color: #13527D; }
        .active-item { background-color: #1E5D88; color: #ffffff; font-weight: 700; }
    </style>
</head>
<body class="text-slate-800 flex min-h-screen bg-slate-50">

    <!-- Sidebar 64 (256px) Fixed Left -->
    <aside class="w-64 sidebar-bg text-white flex flex-col fixed h-screen z-20 shadow-xl">
        <div class="p-5 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white text-[#13527D] rounded-xl flex items-center justify-center font-black text-lg shadow-sm">BN</div>
                <div>
                    <h1 class="font-bold text-sm leading-tight tracking-wide">PORTAL GURU SD</h1>
                    <p class="text-[10px] text-white/70">SDN Kalitapen 01</p>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation: Hanya Mata Pelajaran & Kelola Materi -->
        <nav class="flex-1 p-3 space-y-1.5 overflow-y-auto">
            <!-- 1. Menu Mata Pelajaran (Hierarki: Grid Mapel -> Bab -> Sub-Bab -> Materi) -->
            <a href="{{ route('guru.mapel.browse') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.mapel.*') || request()->routeIs('guru.bab.*') || request()->routeIs('guru.sub_bab.*') || request()->routeIs('guru.materi.*') || request()->routeIs('guru.dashboard') ? 'active-item shadow-sm' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-book-bookmark w-4"></i> Mata Pelajaran
            </a>

            <!-- 2. Menu Kelola Materi (Dropdown Modal: Tambah & Edit) -->
            <div class="relative">
                <button type="button" onclick="document.getElementById('submenu-tambah-materi').classList.toggle('hidden'); document.getElementById('arrow-tambah-materi').classList.toggle('rotate-180');"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-white/80 hover:bg-white/10 transition">
                    <span class="flex items-center gap-3">
                        <i class="fas fa-layer-group w-4 text-emerald-300"></i> Kelola Materi
                    </span>
                    <i id="arrow-tambah-materi" class="fas fa-chevron-down text-[10px] transition-transform duration-200"></i>
                </button>
                <div id="submenu-tambah-materi" class="mt-1 pl-4 space-y-1">
                    <button type="button" onclick="openModalAction('tambah')" class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-lg text-[11px] text-white/90 hover:bg-white/15 transition font-medium">
                        <i class="fas fa-plus text-[10px] text-emerald-400"></i> Tambah Materi / Bab
                    </button>
                    <button type="button" onclick="openModalAction('edit')" class="w-full text-left flex items-center gap-2.5 px-3 py-2 rounded-lg text-[11px] text-white/90 hover:bg-white/15 transition font-medium">
                        <i class="fas fa-pen-to-square text-[10px] text-amber-300"></i> Edit Materi Eksisting
                    </button>
                </div>
            </div>

            <!-- 3. Menu Rekap Absensi Siswa -->
            <a href="{{ route('guru.absensi.rekap') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.absensi.*') ? 'active-item shadow-sm' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-chart-pie w-4 text-sky-300"></i> Rekap Absensi
            </a>

            <!-- 3b. Menu Scan Absensi (QR) -->
            <a href="{{ route('guru.absen.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.absen.*') ? 'active-item shadow-sm' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-qrcode w-4 text-emerald-300"></i> Scan Absensi
            </a>

            <!-- 4. Menu Kelola Notifikasi PR -->
            <a href="{{ route('guru.notifikasi_pr.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.notifikasi_pr.*') ? 'active-item shadow-sm' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-bell w-4 text-amber-300"></i> Notifikasi PR
            </a>

            <!-- 5. Menu RPP & Modul Ajar (Kurikulum Merdeka) -->
            <a href="{{ route('guru.rpp.index') }}"
               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs {{ request()->routeIs('guru.rpp.*') || request()->is('rpp-guru*') || (request()->routeIs('rpp.*') && session('user_type') === 'guru') ? 'active-item shadow-sm' : 'text-white/80 hover:bg-white/10' }}">
                <span class="flex items-center gap-3">
                    <i class="fas fa-file-signature w-4 text-amber-300"></i> RPP & Modul Ajar
                </span>
            </a>
        </nav>

        <div class="p-4 border-t border-white/10 text-xs">
            <div class="flex items-center gap-2 text-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Binaro LMS v2.4
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 ml-64 flex flex-col min-w-0">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-30 shadow-sm">
            <div>
                <div class="text-[11px] font-semibold text-slate-500">
                    SDN Kalitapen 01 &bull; Portal Guru
                </div>
                <h2 class="text-base font-bold text-slate-900">Binaro Learning & Assessment</h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-[#13527D]/10 text-[#13527D] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">
                    Tahun Ajaran Aktif
                </span>
                <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                    <div class="text-right">
                        <div class="text-xs font-bold text-slate-900">{{ $guru->nama_guru ?? session('user_name', 'Guru Binaro') }}</div>
                        <div class="text-[10px] text-slate-500">NIP: {{ session('user_identifier', '198501012010012001') }}</div>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-[#13527D] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ strtoupper(substr($guru->nama_guru ?? session('user_name', 'GB'), 0, 2)) }}
                    </div>
                    <a href="{{ route('logout.get') }}" class="ml-2 px-3 py-1.5 text-xs text-rose-600 border border-rose-200 rounded-lg hover:bg-rose-50 font-semibold transition" title="Logout">
                        <i class="fas fa-sign-out-alt mr-1"></i> Keluar
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash Alerts -->
        @if(session('success'))
        <div class="mx-8 mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div class="mx-8 mt-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-circle-exclamation text-rose-600 text-base"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="mx-8 mt-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-sm">
            <div class="font-bold mb-1.5 flex items-center gap-2 text-rose-700">
                <i class="fas fa-triangle-exclamation"></i>
                <span>Terjadi Kesalahan Input:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1 text-slate-700">
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

    <!-- Include Pop-Up Modal Action PRD 4.2 -->
    @include('guru.materi.modal_action')

</body>
</html>
