<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Siswa - Binaro' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-binaro.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif; background-color: var(--color-canvas); }
        .nav-item { transition: background-color .15s ease, color .15s ease; }
        .nav-item-active { background-color: var(--color-primary); color: #fff; font-weight: 700; box-shadow: 0 6px 14px -8px rgba(19, 82, 125, .8); }
        .nav-item-active i { color: #fff !important; }
        .card { background: #fff; border: 1px solid var(--color-line); border-radius: 1rem; }
        .hero-gradient { background: linear-gradient(115deg, var(--color-primary-hover) 0%, var(--color-primary) 55%, var(--color-primary-soft) 100%); }
        .scroll-thin::-webkit-scrollbar { width: 6px; }
        .scroll-thin::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 99px; }
    </style>
</head>
<body class="text-slate-800 min-h-screen">

@php
    $siswaNav = $siswa ?? null;
    $navNama = $siswaNav->nm_siswa ?? session('user_name', 'Siswa Binaro');
    $navInisial = strtoupper(substr(trim($navNama), 0, 2));
    $navKelas = $siswaNav->kelas?->pararel ?? session('user_kelas', 'Siswa');
    $navPeran = 'Siswa · '.$navKelas;

    $notifBelumBacaCount = 0;
    $activeSiswaId = session('user_id');
    if ($activeSiswaId && session('user_type') === 'siswa') {
        $notifBelumBacaCount = \App\Models\Notifikasi::where('id_siswa', $activeSiswaId)
            ->whereNotNull('id_pr')
            ->where('status_baca', 0)
            ->count();
    }

    // [0] = label breadcrumb, [1] = judul halaman, [2] = subjudul
    $judulHalaman = match (true) {
        request()->routeIs('siswa.dashboard') => ['Beranda', 'Beranda Siswa', 'Ringkasan belajar, tugas, dan jadwalmu hari ini.'],
        request()->routeIs('siswa.mapel.*') => ['Pembelajaran', 'Mata Pelajaran', 'Pilih mata pelajaran untuk membuka Bab dan materi.'],
        request()->routeIs('siswa.sub_bab.*') => ['Pembelajaran', 'Daftar Materi', 'Materi dan kuis di dalam sub-bab ini.'],
        request()->routeIs('siswa.materi.*') => ['Pembelajaran', 'Materi Pembelajaran', 'Baca materi, tonton video, atau kerjakan kuis.'],
        request()->routeIs('siswa.quiz.*') => ['Pembelajaran', 'Kuis', null],
        request()->routeIs('siswa.ujian.play', 'siswa.ujian.petunjuk', 'siswa.ujian.result') => ['Ujian Online', 'Pengerjaan Ujian', null],
        request()->routeIs('siswa.ujian.*') => ['Ujian Online', 'Ujian Online', 'Daftar ujian yang ditugaskan kepadamu.'],
        request()->routeIs('siswa.jadwal*') => ['Akademik', 'Jadwal Pelajaran', 'Jadwal mengajar kelasmu Senin sampai Sabtu.'],
        request()->routeIs('siswa.notifikasi_pr.show') => ['Notifikasi PR', 'Detail Tugas', 'Rincian tugas PR dan instruksi pengumpulan.'],
        request()->routeIs('siswa.notifikasi_pr.*') => ['Notifikasi PR', 'Notifikasi PR', 'Daftar tugas PR dari guru beserta tenggatnya.'],
        request()->routeIs('siswa.data.*') => ['Data & Akademik', 'Kelola Data Siswa', 'Kelola akun siswa, kelas, dan kontak wali.'],
        request()->routeIs('siswa.profile') => ['Akun', 'Profil Saya', 'Data diri, kartu QR absensi, dan keamanan akun.'],
        default => ['Halaman', 'Portal Siswa', 'Portal belajar Binaro.'],
    };
@endphp

<div class="flex min-h-screen">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="w-[268px] shrink-0 bg-white border-r border-line fixed inset-y-0 left-0 z-30 hidden sm:flex flex-col">
        {{-- Brand --}}
        <div class="px-5 pt-6 pb-5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-binaro.svg') }}" alt="Logo Binaro" class="w-11 h-11 rounded-2xl">
                <div class="leading-tight">
                    <div class="text-[17px] font-extrabold tracking-tight text-slate-900">Binaro</div>
                    <div class="text-[9.5px] font-bold tracking-[0.16em] text-slate-400 uppercase">Portal Siswa</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto scroll-thin px-3 pb-4">
            <div class="px-3 pt-2 pb-2 text-[10px] font-extrabold tracking-[0.14em] text-slate-400 uppercase">Menu Utama</div>
            <div class="space-y-1">
                <a href="{{ route('siswa.dashboard') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ request()->routeIs('siswa.dashboard') ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-house w-4 text-center text-[13px] {{ request()->routeIs('siswa.dashboard') ? '' : 'text-slate-400' }}"></i>
                    <span>Beranda</span>
                </a>
                <a href="{{ route('siswa.mapel.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ request()->routeIs('siswa.mapel.*') || request()->routeIs('siswa.sub_bab.*') || request()->routeIs('siswa.materi.*') || request()->routeIs('siswa.quiz.*') ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-book-open w-4 text-center text-[13px] {{ request()->routeIs('siswa.mapel.*') || request()->routeIs('siswa.sub_bab.*') || request()->routeIs('siswa.materi.*') || request()->routeIs('siswa.quiz.*') ? '' : 'text-slate-400' }}"></i>
                    <span>Pembelajaran</span>
                </a>
                <a href="{{ route('siswa.ujian.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ request()->routeIs('siswa.ujian.*') ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-clipboard-question w-4 text-center text-[13px] {{ request()->routeIs('siswa.ujian.*') ? '' : 'text-slate-400' }}"></i>
                    <span>Ujian</span>
                </a>
                <a href="{{ route('siswa.jadwal_mapel.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ request()->routeIs('siswa.jadwal*') ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-calendar-days w-4 text-center text-[13px] {{ request()->routeIs('siswa.jadwal*') ? '' : 'text-slate-400' }}"></i>
                    <span>Jadwal Pelajaran</span>
                </a>
                <a href="{{ route('siswa.notifikasi_pr.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ request()->routeIs('siswa.notifikasi_pr.*') ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-bell w-4 text-center text-[13px] {{ request()->routeIs('siswa.notifikasi_pr.*') ? '' : 'text-slate-400' }}"></i>
                    <span class="flex-1">Notifikasi PR</span>
                    @if($notifBelumBacaCount > 0)
                        <span class="bg-amber-400 text-slate-900 text-[9px] font-extrabold px-1.5 py-0.5 rounded-full min-w-[18px] text-center leading-tight">{{ $notifBelumBacaCount > 99 ? '99+' : $notifBelumBacaCount }}</span>
                    @endif
                </a>
                <a href="{{ route('siswa.profile') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ request()->routeIs('siswa.profile') ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas fa-user-circle w-4 text-center text-[13px] {{ request()->routeIs('siswa.profile') ? '' : 'text-slate-400' }}"></i>
                    <span>Profil Saya</span>
                </a>
            </div>
        </nav>

        {{-- Kartu siswa --}}
        <div class="p-3">
            <div class="rounded-2xl border border-line bg-[#FAFBFD] px-4 py-3.5">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[10px] font-extrabold tracking-[0.1em] text-slate-500 uppercase">Kelas {{ $navKelas }}</span>
                </div>
                <div class="mt-1 text-[12px] font-bold text-slate-800 truncate">{{ $navNama }}</div>
                <div class="mt-0.5 text-[11px] text-slate-400">{{ config('binaro.nama_sekolah') }}</div>
            </div>

            <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Yakin ingin keluar?')" class="mt-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-[12px] font-semibold text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition">
                    <i class="fas fa-arrow-right-from-bracket w-4 text-center text-[12px]"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ================= MAIN ================= --}}
    <div class="flex-1 sm:ml-[268px] flex flex-col min-w-0">

        {{-- ===== HEADER: satu-satunya sumber judul & aksi halaman ===== --}}

        <header class="bg-white border-b border-line sticky top-0 z-20">
            <div class="px-4 sm:px-7 h-[88px] flex items-center justify-between gap-5">
                <div class="min-w-0 flex-1">
                    <nav class="hidden sm:flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 whitespace-nowrap overflow-hidden">
                        @hasSection('breadcrumb_trail')
                            @yield('breadcrumb_trail')
                        @else
                            <span class="shrink-0">Halaman</span>
                            <i class="fas fa-chevron-right text-[8px] opacity-60 shrink-0"></i>
                            <span class="text-slate-500 truncate">@yield('breadcrumb', $judulHalaman[0])</span>
                        @endif
                    </nav>

                    <h1 class="text-[17px] sm:text-[19px] leading-[24px] sm:leading-[26px] font-extrabold text-slate-900 tracking-tight sm:mt-1 truncate">@yield('page_title', $judulHalaman[1])</h1>

                    <p class="hidden sm:block text-[11.5px] leading-[16px] text-slate-400 mt-0.5 truncate">
                        @hasSection('page_subtitle')
                            @yield('page_subtitle')
                        @else
                            {{ $judulHalaman[2] }}
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <div class="flex items-center gap-2 overflow-x-auto">
                        @yield('header_actions')
                    </div>
                    <div class="flex items-center gap-2.5 pl-3 ml-1 border-l border-line">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-[11px] font-extrabold shrink-0 bg-primary" >
                            {{ $navInisial }}
                        </div>
                        <div class="leading-tight hidden xl:block">
                            <div class="text-[12px] font-bold text-slate-900 max-w-[150px] truncate">{{ $navNama }}</div>
                            <div class="text-[10.5px] text-slate-400">{{ $navPeran }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash --}}
        <div class="px-4 sm:px-7">
            @if(session('success'))
        <x-alert type="success" class="mt-5">{{ session('success') }}</x-alert>
        @endif

            @if(session('error'))
        <x-alert type="error" class="mt-5">{{ session('error') }}</x-alert>
        @endif

            @if(session('info'))
        <x-alert type="info" class="mt-5">{{ session('info') }}</x-alert>
        @endif

            @if(isset($errors) && $errors->any())
                <div class="mt-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="font-bold mb-1.5 flex items-center gap-2"><i class="fas fa-triangle-exclamation"></i> Terjadi kesalahan input:</div>
                    <ul class="list-disc list-inside space-y-1 text-slate-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <main class="flex-1 px-4 sm:px-7 py-6">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
