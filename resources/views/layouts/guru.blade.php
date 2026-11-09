<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Pengajar - Binaro' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --binaro-navy: #0E3A5C;
            --binaro-navy-soft: #17557F;
            --binaro-line: #E6EBF2;
            --binaro-canvas: #F5F7FB;
        }
        body { font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif; background-color: var(--binaro-canvas); }
        .nav-item { transition: background-color .15s ease, color .15s ease; }
        .nav-item-active { background-color: var(--binaro-navy); color: #fff; font-weight: 700; box-shadow: 0 6px 14px -8px rgba(14, 58, 92, .8); }
        .nav-item-active i { color: #fff !important; }
        .card { background: #fff; border: 1px solid var(--binaro-line); border-radius: 1rem; }
        .hero-gradient { background: linear-gradient(115deg, #0E3A5C 0%, #14496F 55%, #1A5C88 100%); }
        .scroll-thin::-webkit-scrollbar { width: 6px; }
        .scroll-thin::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 99px; }
    </style>
</head>
<body class="text-slate-800 min-h-screen">

@php
    $navGuru = $guru ?? null;
    $navNama = $navGuru->nama_guru ?? session('user_name', 'Guru Binaro');
    $navInisial = strtoupper(substr(trim($navNama), 0, 2));
    $navPeran = 'Guru';
    $navSemester = (int) now()->month >= 7 ? 'GANJIL' : 'GENAP';
    $navTahun = (int) now()->month >= 7
        ? now()->year.'/'.(now()->year + 1)
        : (now()->year - 1).'/'.now()->year;

    // Jumlah sesi hari ini: dipakai dashboard dari controller, halaman lain hitung sendiri.
    if (isset($sesiHariIni)) {
        $navSesiHariIni = $sesiHariIni->count();
    } elseif ($navGuru) {
        $navSesiHariIni = \App\Models\JadwalMataPelajaran::where('id_guru', $navGuru->id_guru)
            ->whereRaw('LOWER(TRIM(hari)) = ?', [strtolower(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][now()->dayOfWeekIso - 1])])
            ->count();
    } else {
        $navSesiHariIni = 0;
    }

    $menuUtama = [
        ['label' => 'Beranda Guru', 'icon' => 'fa-house', 'route' => 'guru.dashboard', 'aktif' => request()->routeIs('guru.dashboard')],
        ['label' => 'Presensi Siswa', 'icon' => 'fa-clipboard-check', 'route' => 'guru.absensi.rekap', 'aktif' => request()->routeIs('guru.absensi.*') || request()->routeIs('guru.absen.*')],
        ['label' => 'RPP & Modul', 'icon' => 'fa-file-lines', 'route' => 'guru.rpp.index', 'aktif' => request()->routeIs('guru.rpp.*') || request()->routeIs('rpp.*') || request()->routeIs('rpp_guru.*')],
        ['label' => 'Tugas & Kuis', 'icon' => 'fa-pen-to-square', 'route' => 'guru.ujian.index', 'aktif' => request()->routeIs('guru.ujian.*') || request()->routeIs('guru.quiz.*')],
        ['label' => 'Buku Nilai', 'icon' => 'fa-chart-simple', 'route' => 'rekap_nilai.index', 'aktif' => request()->routeIs('rekap_nilai.*') || request()->routeIs('rekap_ujian.*')],
    ];

    // [0] = label breadcrumb, [1] = judul halaman, [2] = subjudul
    $judulHalaman = match (true) {
        request()->routeIs('guru.dashboard') => ['Dashboard Pengajar', 'Ruang Kelas & Penilaian', 'Ringkasan sesi mengajar, presensi, dan penilaian hari ini.'],
        request()->routeIs('guru.jadwal.*') => ['Akademik', 'Jadwal Mengajar', 'Daftar sesi mengajar Senin sampai Sabtu beserta jam dan ruang kelas.'],
        request()->routeIs('guru.mapel.*') => ['Akademik', 'Mata Pelajaran', 'Pilih mata pelajaran untuk menjelajahi Bab, Sub-Bab, dan materi.'],
        request()->routeIs('guru.bab.*') => ['Akademik', 'Daftar Bab', 'Struktur bab pada mata pelajaran yang dipilih.'],
        request()->routeIs('guru.sub_bab.*') => ['Akademik', 'Daftar Sub-Bab', 'Rincian sub-bab di dalam bab yang dipilih.'],
        request()->routeIs('guru.materi.*') => ['Akademik', 'Kelola Materi', 'Kelola Bab, Sub-Bab, dan tipe konten: video, PDF, atau kuis.'],
        request()->routeIs('guru.notifikasi_pr.create') => ['Akademik', 'Buat Tugas PR', 'Isi rincian tugas PR di bawah dan kirimkan notifikasi pengingat ke siswa.'],
        request()->routeIs('guru.notifikasi_pr.edit') => ['Akademik', 'Perbarui Data Tugas PR', 'Ubah judul, tenggat, instruksi, atau kirimkan notifikasi pembaruan ke siswa.'],
        request()->routeIs('guru.notifikasi_pr.show') => ['Akademik', 'Rincian PR & Riwayat Siswa', 'Detail tugas, tenggat, dan status pengumpulan tiap siswa.'],
        request()->routeIs('guru.notifikasi_pr.*') => ['Akademik', 'Notifikasi PR', 'Atur tugas PR, tenggat waktu, dan kirim pengingat ke portal siswa.'],
        request()->routeIs('guru.absensi.*', 'guru.absen.*') => ['Utama', 'Presensi Siswa', 'Monitoring dan rekapitulasi kehadiran siswa.'],
        request()->routeIs('guru.rpp.*', 'rpp.*', 'rpp_guru.*') => ['Utama', 'RPP & Modul Ajar', 'Rencana pelaksanaan pembelajaran terpadu kurikulum merdeka.'],
        request()->routeIs('guru.ujian.create') => ['Utama', 'Buat Ujian Online Baru', 'Tentukan konfigurasi ujian, target siswa, tingkat level, dan susun soal-soal ujian.'],
        request()->routeIs('guru.ujian.edit') => ['Utama', 'Sunting Pengaturan Ujian', 'Ubah konfigurasi ujian, tingkatan level, durasi, dan target peserta ujian.'],
        request()->routeIs('guru.ujian.show') => ['Utama', 'Detail & Bank Soal', 'Rincian ujian, bobot nilai, dan daftar butir soal.'],
        request()->routeIs('guru.ujian.*') => ['Utama', 'Tugas & Ujian', 'Susun ujian online, bank soal, bobot nilai, dan target siswa.'],
        request()->routeIs('guru.quiz.*') => ['Utama', 'Bank Soal', 'Kelola butir soal dan kunci jawaban kuis.'],
        request()->routeIs('rekap_nilai.*', 'rekap_ujian.*') => ['Utama', 'Buku Nilai', 'Rekapitulasi nilai siswa dan pengumuman hasil ujian.'],
        default => ['Halaman', 'Portal Pengajar', 'Ringkasan aktivitas mengajar Anda.'],
    };

    $menuAkademik = [
        ['label' => 'Jadwal Mengajar', 'icon' => 'fa-calendar-days', 'route' => 'guru.jadwal.index', 'aktif' => request()->routeIs('guru.jadwal.*')],
        ['label' => 'Mata Pelajaran', 'icon' => 'fa-book-bookmark', 'route' => 'guru.mapel.browse', 'aktif' => request()->routeIs('guru.mapel.*') || request()->routeIs('guru.bab.*') || request()->routeIs('guru.sub_bab.*')],
        ['label' => 'Kelola Materi', 'icon' => 'fa-layer-group', 'route' => 'guru.materi.index', 'aktif' => request()->routeIs('guru.materi.*')],
        ['label' => 'Notifikasi PR', 'icon' => 'fa-bell', 'route' => 'guru.notifikasi_pr.index', 'aktif' => request()->routeIs('guru.notifikasi_pr.*')],
    ];
@endphp

<div class="flex min-h-screen">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="w-[268px] shrink-0 bg-white border-r border-[#E6EBF2] fixed inset-y-0 left-0 z-30 flex flex-col">
        {{-- Brand --}}
        <div class="px-5 pt-6 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-sm" style="background: var(--binaro-navy);">
                    <i class="fas fa-graduation-cap text-white text-lg"></i>
                </div>
                <div class="leading-tight">
                    <div class="text-[17px] font-extrabold tracking-tight text-slate-900">BINARO</div>
                    <div class="text-[9.5px] font-bold tracking-[0.16em] text-slate-400 uppercase">Portal Pengajar</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto scroll-thin px-3 pb-4">
            <div class="px-3 pt-2 pb-2 text-[10px] font-extrabold tracking-[0.14em] text-slate-400 uppercase">Utama</div>
            <div class="space-y-1">
                @foreach($menuUtama as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ $menu['aktif'] ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fas {{ $menu['icon'] }} w-4 text-center text-[13px] {{ $menu['aktif'] ? '' : 'text-slate-400' }}"></i>
                        <span>{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="px-3 pt-6 pb-2 text-[10px] font-extrabold tracking-[0.14em] text-slate-400 uppercase">Akademik</div>
            <div class="space-y-1">
                @foreach($menuAkademik as $menu)
                    <a href="{{ route($menu['route']) }}"
                       class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] {{ $menu['aktif'] ? 'nav-item-active' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fas {{ $menu['icon'] }} w-4 text-center text-[13px] {{ $menu['aktif'] ? '' : 'text-slate-400' }}"></i>
                        <span>{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>

        {{-- Semester card --}}
        <div class="p-3">
            <div class="rounded-2xl border border-[#E6EBF2] bg-[#FAFBFD] px-4 py-3.5">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[10px] font-extrabold tracking-[0.1em] text-slate-500 uppercase">Semester {{ $navSemester }}</span>
                </div>
                <div class="mt-1 text-[12px] font-bold text-slate-800">TA {{ $navTahun }}</div>
                <div class="mt-0.5 text-[11px] text-slate-400">{{ $navSesiHariIni }} Sesi Terjadwal Hari Ini</div>
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
    <div class="flex-1 ml-[268px] flex flex-col min-w-0">

        {{-- ===== HEADER: satu-satunya sumber judul & aksi halaman ===== --}}
        {{-- Tinggi dikunci 88px agar identik di semua halaman: breadcrumb 1 baris,
             judul 1 baris, subjudul 1 baris (selalu ada, di-truncate). --}}
        <header class="bg-white border-b border-[#E6EBF2] sticky top-0 z-20">
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
                    <div class="flex items-center gap-2.5 pl-3 ml-1 border-l border-[#E6EBF2]">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-[11px] font-extrabold shrink-0" style="background: var(--binaro-navy);">
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
        <div class="px-7">
            @if(session('success'))
                <div class="mt-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-circle-check text-emerald-600"></i>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-xmark"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="mt-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-circle-exclamation text-rose-600"></i>
                        <span class="font-semibold">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-xmark"></i></button>
                </div>
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

        <main class="flex-1 px-7 py-6">
            @yield('content')
        </main>
    </div>
</div>

@include('guru.materi.modal_action')

</body>
</html>
