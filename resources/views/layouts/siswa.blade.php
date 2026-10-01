<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa - Binaro Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        .active-item { background-color: rgba(255, 255, 255, 0.15); border-left: 3px solid #38bdf8; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <!-- Sidebar Statis w-64 fixed (desktop) -->
    <aside class="fixed top-0 left-0 h-full w-64 bg-[#13527D] text-white hidden sm:flex flex-col z-30 shadow-lg">
        <div class="p-5 border-b border-white/10 flex items-center gap-3">
            <div class="w-9 h-9 bg-white text-[#13527D] font-black rounded-lg flex items-center justify-center shadow-sm">
                BN
            </div>
            <div>
                <h1 class="font-bold text-sm leading-tight">Binaro Siswa</h1>
                <p class="text-[10px] text-white/70">SDN Kalitapen 01</p>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-1 overflow-y-auto text-xs">
            <a href="{{ route('siswa.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('siswa.dashboard') ? 'active-item' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-home w-4"></i> Dashboard
            </a>
            <a href="{{ route('siswa.mapel.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('siswa.mapel.*') || request()->routeIs('siswa.sub_bab.*') || request()->routeIs('siswa.materi.*') || request()->routeIs('siswa.quiz.*') ? 'active-item' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-book-open w-4"></i> Pembelajaran
            </a>
            <a href="{{ route('siswa.ujian.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('siswa.ujian.*') ? 'active-item' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-clipboard-question w-4"></i> Ujian Online
            </a>
            <a href="{{ route('siswa.jadwal_mapel.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('siswa.jadwal*') ? 'active-item' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-calendar-alt w-4"></i> Jadwal Pelajaran
            </a>
            @php

                $notifBelumBacaCount = 0;
                $activeSiswaId = session('user_id');
                if ($activeSiswaId && session('user_type') === 'siswa') {
                    $notifBelumBacaCount = \App\Models\Notifikasi::where('id_siswa', $activeSiswaId)->whereNotNull('id_pr')->where('status_baca', 0)->count();
                } else {
                    $demoSiswa = \App\Models\Siswa::first();
                    if ($demoSiswa) {
                        $notifBelumBacaCount = \App\Models\Notifikasi::where('id_siswa', $demoSiswa->id_siswa)->whereNotNull('id_pr')->where('status_baca', 0)->count();
                    }
                }
            @endphp
            <a href="{{ route('siswa.notifikasi_pr.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('siswa.notifikasi_pr.*') ? 'active-item' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-bell w-4"></i>
                <span class="flex-1">Notifikasi PR</span>
                @if($notifBelumBacaCount > 0)
                <span class="bg-amber-400 text-slate-900 text-[9px] font-extrabold px-1.5 py-0.5 rounded-full min-w-[18px] text-center leading-tight">
                    {{ $notifBelumBacaCount > 99 ? '99+' : $notifBelumBacaCount }}
                </span>
                @endif
            </a>
            <a href="{{ route('siswa.profile') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('siswa.profile') ? 'active-item' : 'text-white/80 hover:bg-white/10' }}">
                <i class="fas fa-user-circle w-4"></i>
                <span class="flex-1">Profil Saya</span>
            </a>
        </nav>

        <div class="p-3 border-t border-white/10">
            <div class="px-3 py-2 bg-white/10 rounded-lg text-xs mb-2">
                <p class="font-bold text-white truncate">{{ session('user_name', 'Siswa') }}</p>
                <p class="text-[10px] text-white/70">Role: Siswa</p>
            </div>
            <a href="{{ route('logout.get') }}" class="w-full flex items-center gap-2 px-3 py-2 text-rose-300 hover:bg-rose-500/20 rounded-lg text-xs transition">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="sm:ml-64 p-4 sm:p-8">
        @yield('content')
    </main>
</body>
</html>
