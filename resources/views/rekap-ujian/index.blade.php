@extends('layouts.guru')

@section('content')
@php
    $isQuiz = !empty($selectedQuiz?->id_sub_bab);
    $isUjian = empty($selectedQuiz?->id_sub_bab);
@endphp
<div class="p-8 space-y-6">

    {{-- ===== BREADCRUMB ===== --}}
    <nav class="flex items-center gap-2 text-[11px] text-slate-400 font-medium print:hidden">
        <i class="fas fa-home text-slate-300"></i>
        <span class="text-slate-300">/</span>
        <span class="text-slate-500">Penilaian Siswa</span>
        <span class="text-slate-300">/</span>
        <span class="text-[#13527D] font-semibold">Rekap Nilai {{ $selectedQuiz ? ($isQuiz ? 'Kuis' : 'Ujian') : '' }}</span>
    </nav>

    {{-- ===== PRINT LETTERHEAD (KOP RESMI SDN KALITAPEN 01) ===== --}}
    @if($selectedQuiz)
    <div class="hidden print:block mb-6 border-b-2 border-slate-900 pb-3">
        <div class="text-center space-y-0.5">
            <h2 class="text-base font-black uppercase tracking-wider text-slate-900">PEMERINTAH KABUPATEN BANYUMAS</h2>
            <h3 class="text-sm font-bold uppercase text-slate-800">DINAS PENDIDIKAN - SDN KALITAPEN 01</h3>
            <p class="text-[10px] text-slate-600">Jl. Kalitapen No. 01, Kec. Purwojati, Kab. Banyumas &bull; Telp: (0281) 123456</p>
        </div>
        <div class="mt-4 pt-2 border-t border-slate-300 flex items-center justify-between text-xs font-semibold text-slate-800">
            <div>
                <div><strong>Daftar Nilai:</strong> {{ $selectedQuiz->judul_quiz }}</div>
                <div><strong>Kategori:</strong> {{ $isQuiz ? 'Kuis Materi Pembelajaran' : 'Ujian Online CBT' }}</div>
                <div><strong>Durasi Waktu Ujian:</strong> {{ $selectedQuiz->durasi_menit ?? ($selectedQuiz->durasi ?? 60) }} Menit</div>
            </div>
            <div class="text-right">
                <div><strong>Mata Pelajaran:</strong> {{ $selectedQuiz->mataPelajaran?->nama_mapel ?? ($selectedQuiz->subBab?->bab?->mataPelajaran?->nama_mapel ?? '-') }}</div>
                <div><strong>Waktu Cetak:</strong> {{ now()->format('d/m/Y H:i') }} WIB</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== PAGE HEADER ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 print:hidden">
        <div>
            @if($selectedQuiz)
            <div class="flex items-center gap-2 mb-1.5">
                @if($isQuiz)
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200 flex items-center gap-1.5">
                    <i class="fas fa-book-open text-[9px]"></i> Kuis Materi Pembelajaran
                </span>
                @else
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                    <i class="fas fa-graduation-cap text-[9px]"></i> Ujian Online CBT Resmi
                </span>
                @endif
            </div>
            @endif
            <h1 class="text-xl font-black text-slate-900 leading-tight">
                Rekap Nilai:
                <span class="text-[#13527D]">
                    {{ $selectedQuiz?->judul_quiz ?? 'Pilih Kuis atau Ujian' }}
                </span>
            </h1>
            <div class="flex flex-wrap items-center gap-4 mt-2 text-[11px] text-slate-500">
                @if($selectedQuiz)
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-calendar-days text-slate-400"></i>
                    {{ $selectedQuiz->created_at?->format('d M Y') ?? '-' }}
                </span>
                <span class="flex items-center gap-1.5 font-semibold text-slate-700">
                    <i class="fas fa-stopwatch text-[#13527D]"></i>
                    Durasi Waktu Ujian: {{ $selectedQuiz->durasi_menit ?? ($selectedQuiz->durasi ?? '60') }} menit
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-chalkboard-teacher text-slate-400"></i>
                    {{ session('user_name', 'Guru Pengampu') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-book text-slate-400"></i>
                    {{ $selectedQuiz->mataPelajaran?->nama_mapel ?? ($selectedQuiz->subBab?->bab?->mataPelajaran?->nama_mapel ?? '-') }}
                </span>
                @else
                <span class="italic text-slate-400">Silakan pilih kuis atau ujian terlebih dahulu</span>
                @endif
            </div>
        </div>

        {{-- Right Controls --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Pilih Kuis / Ujian Dropdown --}}
            <form method="GET" action="{{ route('rekap_nilai.index') }}" class="flex items-center gap-2">
                <select name="quiz_id" onchange="this.form.submit()"
                    class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#13527D] shadow-sm">
                    <option value="">-- Pilih Rekap Nilai --</option>
                    @foreach($quizzes as $q)
                    @php
                        $tipeLabel = !empty($q->id_sub_bab) ? 'Kuis' : 'Ujian';
                        $mapelLabel = $q->mataPelajaran?->nama_mapel ?? ($q->subBab?->bab?->mataPelajaran?->nama_mapel ?? '-');
                    @endphp
                    <option value="{{ $q->id_quiz }}" {{ $selectedQuizId == $q->id_quiz ? 'selected' : '' }}>
                        [{{ $tipeLabel }}] {{ $q->judul_quiz }} ({{ $mapelLabel }})
                    </option>
                    @endforeach
                </select>
            </form>

            @if($selectedQuizId && $hasils->isNotEmpty())
                @if($isQuiz)
                {{-- KUIS: HANYA DAPAT MELIHAT DAN MENCETAK DAFTAR NILAI --}}
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <i class="fas fa-print"></i> Cetak Daftar Nilai
                </button>
                <a href="{{ route('guru.quiz.export', $selectedQuizId) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <i class="fas fa-file-excel"></i> Unduh Excel
                </a>
                @else
                {{-- UJIAN: CETAK REKAP NILAI, UNDUH EXCEL, DAN KIRIM NILAI KE SEMUA SISWA --}}
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <i class="fas fa-print"></i> Cetak Rekap Nilai
                </button>
                <a href="{{ route('guru.quiz.export', $selectedQuizId) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <i class="fas fa-file-excel"></i> Unduh Excel
                </a>
                <form method="POST" action="{{ route('rekap_ujian.kirim_semua', $selectedQuizId) }}" class="inline" onsubmit="return confirm('Kirimkan nilai ujian ini ke seluruh siswa?')">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#13527D] hover:bg-[#0e4064] text-white text-xs font-semibold rounded-lg transition shadow-sm">
                        <i class="fas fa-paper-plane"></i> Kirim Nilai ke Semua Siswa
                    </button>
                </form>
                @endif
            @endif
        </div>
    </div>

    {{-- ===== NO QUIZ SELECTED EMPTY STATE ===== --}}
    @if(!$selectedQuizId)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-16 flex flex-col items-center justify-center text-center gap-4 print:hidden">
        <div class="w-16 h-16 rounded-2xl bg-[#13527D]/10 flex items-center justify-center">
            <i class="fas fa-clipboard-list text-2xl text-[#13527D]/60"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-700 text-sm mb-1">Belum Ada Kuis atau Ujian Dipilih</h3>
            <p class="text-xs text-slate-400 max-w-xs">Silakan pilih kuis atau ujian dari dropdown di atas untuk melihat rekap nilai dan statistik siswa.</p>
        </div>
    </div>

    @else

    {{-- ===== KPI STAT CARDS (MURNI DARI DATABASE) ===== --}}
    @php
        $totalSiswa    = $hasils->count();
        $rataRata      = $totalSiswa ? round($hasils->avg('nilai_akhir'), 1) : 0;
        $lulus         = $hasils->where('nilai_akhir', '>=', 70)->count();
        $remedial      = $hasils->where('nilai_akhir', '<', 70)->count();
        $tertinggi     = $totalSiswa ? round($hasils->max('nilai_akhir'), 1) : 0;
        $terendah      = $totalSiswa ? round($hasils->min('nilai_akhir'), 1) : 0;
        $pctLulus      = $totalSiswa ? round(($lulus / $totalSiswa) * 100) : 0;
        $keaktifanList = $hasils->whereNotNull('nilai_keaktifan');
        $rataKeaktifan = $keaktifanList->count() ? round($keaktifanList->avg('nilai_keaktifan')) : null;
        $waktuList     = $hasils->whereNotNull('waktu_menit');
        $rataWaktu     = $waktuList->count() ? round($waktuList->avg('waktu_menit')) : ($selectedQuiz->durasi_menit ?? 60);
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5 print:hidden">
        {{-- Rata-rata Nilai --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between h-full col-span-1 hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-1 mb-2">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold uppercase tracking-wider leading-tight">Rata-rata Nilai</span>
                    <div class="w-7 h-7 rounded-lg bg-sky-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-chart-line text-sky-600 text-xs"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-[#13527D] tracking-tight leading-none">{{ $rataRata }}</div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Dari {{ $totalSiswa }} peserta</div>
        </div>

        {{-- Durasi Waktu Pengerjaan --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between h-full col-span-1 hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-1 mb-2">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold uppercase tracking-wider leading-tight">Durasi Pengerjaan</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-stopwatch text-blue-600 text-xs"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-blue-600 tracking-tight leading-none">{{ $rataWaktu }} <span class="text-xs font-bold text-slate-400">mnt</span></div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Alokasi: {{ $selectedQuiz->durasi_menit ?? ($selectedQuiz->durasi ?? 60) }} mnt</div>
        </div>

        {{-- Rata-rata Keaktifan --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between h-full col-span-1 hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-1 mb-2">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold uppercase tracking-wider leading-tight">Rata Keaktifan</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-star text-amber-500 text-xs"></i>
                    </div>
                </div>
                @if($rataKeaktifan)
                <div class="text-2xl font-black text-amber-500 tracking-tight leading-none">{{ $rataKeaktifan }}</div>
                @else
                <div class="text-2xl font-black text-amber-500 tracking-tight leading-none">–</div>
                @endif
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">
                {{ $rataKeaktifan ? 'Poin rata-rata' : 'Belum ada data' }}
            </div>
        </div>

        {{-- Tingkat Kelulusan / Ketuntasan --}}
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-4 flex flex-col justify-between h-full col-span-1 hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-1 mb-2">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold uppercase tracking-wider leading-tight">{{ $isQuiz ? 'Ketuntasan' : 'Kelulusan' }}</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-medal text-emerald-600 text-xs"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-600 tracking-tight leading-none">{{ $pctLulus }}%</div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">
                @if($isQuiz)
                    {{ $lulus }} tuntas · {{ $remedial }} blm
                @else
                    {{ $lulus }} lulus · {{ $remedial }} rem
                @endif
            </div>
        </div>

        {{-- Nilai Tertinggi --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between h-full col-span-1 hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-1 mb-2">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold uppercase tracking-wider leading-tight">Nilai Tertinggi</span>
                    <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-trophy text-violet-500 text-xs"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-violet-600 tracking-tight leading-none">{{ number_format($tertinggi, 0) }}</div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Skor maksimum</div>
        </div>

        {{-- Nilai Terendah --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between h-full col-span-1 hover:shadow-md transition">
            <div>
                <div class="flex items-start justify-between gap-1 mb-2">
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-bold uppercase tracking-wider leading-tight">Nilai Terendah</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                        <i class="fas fa-arrow-trend-down text-rose-500 text-xs"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-rose-500 tracking-tight leading-none">{{ number_format($terendah, 0) }}</div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Skor minimum</div>
        </div>
    </div>

    {{-- ===== MAIN TABLE CARD ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Tab Bar + Search + Sort --}}
        <div class="border-b border-slate-200 px-6 pt-4 print:hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                {{-- Tabs --}}
                <div class="flex items-center gap-1 overflow-x-auto" id="tab-bar">
                    <button onclick="switchTab('semua')" id="tab-semua"
                        class="tab-btn active-tab whitespace-nowrap px-4 py-2 text-[11px] font-bold rounded-t-lg border-b-2 border-[#13527D] text-[#13527D] transition">
                        Semua Siswa <span class="ml-1 bg-[#13527D]/10 text-[#13527D] px-1.5 py-0.5 rounded-full text-[10px]">{{ $totalSiswa }}</span>
                    </button>
                    <button onclick="switchTab('lulus')" id="tab-lulus"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-emerald-600 hover:border-emerald-400 transition">
                        {{ $isQuiz ? 'Tuntas KKM' : 'Lulus KKM' }} <span class="ml-1 bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded-full text-[10px]">{{ $lulus }}</span>
                    </button>
                    @if($isUjian)
                    <button onclick="switchTab('remedial')" id="tab-remedial"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-rose-600 hover:border-rose-400 transition">
                        Remedial <span class="ml-1 bg-rose-50 text-rose-500 px-1.5 py-0.5 rounded-full text-[10px]">{{ $remedial }}</span>
                    </button>
                    @endif
                    <button onclick="switchTab('rekap')" id="tab-rekap"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-[#13527D] hover:border-[#13527D] transition">
                        Rekap + Keaktifan
                    </button>
                    <button onclick="switchTab('nilai')" id="tab-nilai"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-[#13527D] hover:border-[#13527D] transition">
                        Nilai {{ $isQuiz ? 'Kuis' : 'Ujian' }} Saja
                    </button>
                </div>

                {{-- Search + Sort --}}
                <div class="flex items-center gap-2 pb-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                        <input type="text" id="search-input" placeholder="Cari siswa..."
                            oninput="filterTable()"
                            class="pl-7 pr-3 py-2 text-[11px] border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D] w-48 bg-slate-50">
                    </div>
                    <select id="sort-select" onchange="sortTable()" class="px-3 py-2 text-[11px] border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D] bg-slate-50 text-slate-600">
                        <option value="default">Urutan Default</option>
                        <option value="nilai_desc">Nilai Tertinggi</option>
                        <option value="nilai_asc">Nilai Terendah</option>
                        <option value="nama_asc">Nama A-Z</option>
                        <option value="nama_desc">Nama Z-A</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="rekap-table">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] font-bold uppercase tracking-wide">
                        <th class="px-4 py-3.5 w-10 text-center">No</th>
                        <th class="px-4 py-3.5">Identitas Siswa</th>
                        <th class="px-4 py-3.5 text-center tab-col-rekap tab-col-semua">Durasi Waktu Pengerjaan</th>
                        <th class="px-4 py-3.5 text-center tab-col-semua">Benar / Salah</th>
                        <th class="px-4 py-3.5 text-center tab-col-rekap tab-col-semua">Nilai Keaktifan</th>
                        <th class="px-4 py-3.5 text-center">Nilai Akhir & Status</th>
                        <th class="px-4 py-3.5 tab-col-rekap tab-col-semua">Umpan Balik Guru</th>
                        <th class="px-4 py-3.5 text-center print:hidden">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="rekap-tbody">
                    @forelse($hasils as $idx => $row)
                    @php
                        $isLulus = ($row->nilai_akhir ?? 0) >= 70;
                        $namaInisial = strtoupper(substr($row->siswa?->nm_siswa ?? 'S', 0, 2));
                        $colors = ['bg-sky-500', 'bg-violet-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-pink-500'];
                        $avatarColor = $colors[$idx % count($colors)];
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition rekap-row {{ $isLulus ? 'row-lulus' : 'row-remedial' }}"
                        data-nama="{{ strtolower($row->siswa?->nm_siswa ?? '') }}"
                        data-nilai="{{ $row->nilai_akhir ?? 0 }}">
                        {{-- No --}}
                        <td class="px-4 py-3.5 text-center font-bold text-slate-300 text-[11px]">{{ $idx + 1 }}</td>

                        {{-- Identitas --}}
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl {{ $avatarColor }} text-white flex items-center justify-center font-bold text-[10px] shrink-0 shadow-sm print:hidden">
                                    {{ $namaInisial }}
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900 text-xs">{{ $row->siswa?->nm_siswa ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $row->siswa?->nisn ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Durasi Waktu Pengerjaan --}}
                        <td class="px-4 py-3.5 text-center tab-col-rekap tab-col-semua">
                            <div class="flex flex-col items-center justify-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100/90 border border-slate-200 text-xs font-mono font-bold text-slate-700">
                                    <i class="fas fa-stopwatch text-slate-400 text-[10px]"></i>
                                    {{ $row->waktu_menit ? $row->waktu_menit . ' menit' : ($selectedQuiz->durasi_menit ? $selectedQuiz->durasi_menit . ' menit' : '–') }}
                                </span>
                                @if($selectedQuiz->durasi_menit && $row->waktu_menit)
                                <span class="text-[9px] text-slate-400 mt-0.5">
                                    dari alokasi {{ $selectedQuiz->durasi_menit }} mnt
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- Benar / Salah --}}
                        <td class="px-4 py-3.5 text-center tab-col-semua">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[11px] border border-emerald-100">
                                    ✓ {{ $row->jumlah_benar ?? 0 }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 font-bold text-[11px] border border-rose-100">
                                    ✗ {{ $row->jumlah_salah ?? 0 }}
                                </span>
                            </div>
                        </td>

                        {{-- Nilai Keaktifan --}}
                        <td class="px-4 py-3.5 text-center tab-col-rekap tab-col-semua">
                            @php
                                $bintang = $row->nilai_keaktifan ? min(5, max(1, (int)round($row->nilai_keaktifan / 20))) : 3;
                            @endphp
                            <div class="flex items-center justify-center gap-0.5 text-amber-400 text-xs print:hidden">
                                @for($s = 1; $s <= 5; $s++)
                                    @if($s <= $bintang)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star text-slate-300"></i>
                                    @endif
                                @endfor
                            </div>
                            <div class="text-[10px] text-slate-600 mt-0.5 font-medium">
                                {{ $row->nilai_keaktifan ? $row->nilai_keaktifan . ' poin' : '–' }}
                            </div>
                        </td>

                        {{-- Nilai Akhir & Status --}}
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-lg font-black {{ $isLulus ? 'text-emerald-600' : 'text-rose-500' }}">
                                    {{ number_format($row->nilai_akhir ?? 0, 0) }}
                                </span>

                                @if($isQuiz)
                                    @if($isLulus)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        TUNTAS KKM
                                    </span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-600 border border-rose-200">
                                        BELUM TUNTAS
                                    </span>
                                    @endif
                                @else
                                    @if($isLulus)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        LULUS KKM
                                    </span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-600 border border-rose-200">
                                        REMEDIAL
                                    </span>
                                    @endif

                                    @if($row->status_kirim)
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 print:hidden">
                                        <i class="fas fa-check text-[8px]"></i> Terkirim
                                    </span>
                                    @else
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1 print:hidden">
                                        <i class="fas fa-clock text-[8px]"></i> Belum Dikirim
                                    </span>
                                    @endif
                                @endif
                            </div>
                        </td>

                        {{-- Umpan Balik --}}
                        <td class="px-4 py-3.5 tab-col-rekap tab-col-semua">
                            <span class="text-[11px] {{ $row->catatan_guru ? 'text-slate-700 font-medium' : 'text-slate-400 italic' }}">
                                {{ $row->catatan_guru ?? 'Belum ada catatan' }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3.5 text-center print:hidden">
                            <div class="flex items-center justify-center gap-1.5">
                                <button title="Lihat Detail"
                                    onclick="openDetailModal({
                                        id: {{ $row->id_hasil }},
                                        nama: '{{ addslashes($row->siswa?->nm_siswa ?? '-') }}',
                                        nisn: '{{ $row->siswa?->nisn ?? '-' }}',
                                        nilai: '{{ number_format($row->nilai_akhir ?? 0, 0) }}',
                                        benar: {{ $row->jumlah_benar ?? 0 }},
                                        salah: {{ $row->jumlah_salah ?? 0 }},
                                        waktu: '{{ $row->waktu_menit ? $row->waktu_menit . ' menit' : '-' }}',
                                        keaktifan: '{{ $row->nilai_keaktifan ? $row->nilai_keaktifan . ' poin' : '-' }}',
                                        status: '{{ ($row->nilai_akhir ?? 0) >= 70 ? ($isQuiz ? 'TUNTAS KKM' : 'LULUS KKM') : ($isQuiz ? 'BELUM TUNTAS' : 'REMEDIAL') }}',
                                        feedback: '{{ addslashes($row->catatan_guru ?? 'Belum ada catatan') }}'
                                    })"
                                    class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 flex items-center justify-center transition border border-sky-100">
                                    <i class="fas fa-eye text-[10px]"></i>
                                </button>
                                <button title="Beri Umpan Balik"
                                    onclick="openFeedbackModal({{ $row->id_hasil }}, '{{ addslashes($row->siswa?->nm_siswa ?? '-') }}', '{{ addslashes($row->catatan_guru ?? '') }}')"
                                    class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition border border-amber-100">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </button>
                                @if($isUjian)
                                <button title="Kirim Nilai / Pesan"
                                    onclick="openKirimModal({{ $row->id_hasil }}, '{{ addslashes($row->siswa?->nm_siswa ?? '-') }}')"
                                    class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition border border-emerald-100">
                                    <i class="fas fa-paper-plane text-[10px]"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <i class="fas fa-users-slash text-lg text-slate-300"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-500">Belum ada siswa yang mengerjakan {{ $isQuiz ? 'kuis' : 'ujian' }} ini</p>
                                <p class="text-[11px] text-slate-400">Nilai akan otomatis muncul setelah siswa menyelesaikan pengerjaan {{ $isQuiz ? 'kuis' : 'ujian' }}.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer --}}
        @if($hasils->isNotEmpty())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <p class="text-[11px] text-slate-400">
                Menampilkan <strong>{{ $hasils->count() }}</strong> siswa
                <span class="mx-1">·</span>
                <span class="text-emerald-600 font-semibold">{{ $lulus }} {{ $isQuiz ? 'tuntas' : 'lulus' }}</span>
                <span class="mx-1">·</span>
                <span class="text-rose-500 font-semibold">{{ $remedial }} {{ $isQuiz ? 'belum tuntas' : 'remedial' }}</span>
                <span class="mx-1">·</span>
                <span class="text-slate-600 font-medium inline-flex items-center gap-1">
                    <i class="fas fa-stopwatch text-slate-400 text-[10px]"></i> Rata-rata durasi: <strong>{{ $rataWaktu }} menit</strong>
                </span>
            </p>
            <div class="flex items-center gap-3 text-[11px] text-slate-400">
                <span>Alokasi: <strong class="text-slate-700">{{ $selectedQuiz->durasi_menit ?? 60 }} mnt</strong></span>
                <span>·</span>
                <span>KKM: <strong class="text-slate-700">70</strong></span>
            </div>
        </div>
        @endif
    </div>

    {{-- ===== PRINT SIGNATURE BLOCK (HANYA MUNCUL SAAT CETAK) ===== --}}
    @if($selectedQuiz)
    <div class="hidden print:flex justify-between items-end mt-12 pt-8 text-xs text-slate-800">
        <div class="text-center">
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Sekolah SDN Kalitapen 01</p>
            <div class="h-20"></div>
            <p class="font-bold underline">SUGIARTO, S.Pd., M.Pd.</p>
            <p class="text-[10px] text-slate-500">NIP. 19700101 199503 1 001</p>
        </div>
        <div class="text-center">
            <p>Banyumas, {{ now()->translatedFormat('d F Y') }}</p>
            <p class="font-bold">Guru Pengampu</p>
            <div class="h-20"></div>
            <p class="font-bold underline">{{ session('user_name', 'Guru Mata Pelajaran') }}</p>
            <p class="text-[10px] text-slate-500">NIP. -</p>
        </div>
    </div>
    @endif

    {{-- ===== REMEDIAL BANNER (KHUSUS UJIAN) ===== --}}
    @if($isUjian && $remedial > 0)
    <div class="bg-gradient-to-r from-rose-500 to-rose-600 rounded-2xl shadow-md p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="fas fa-calendar-plus text-white text-base"></i>
            </div>
            <div>
                <h3 class="text-white font-bold text-sm">Jadwalkan Remedial Otomatis untuk {{ $remedial }} Siswa</h3>
                <p class="text-white/80 text-[11px] mt-0.5">
                    Ada {{ $remedial }} siswa yang belum mencapai KKM (70). Anda dapat menerbitkan jadwal remedial dan notifikasi otomatis ke siswa bersangkutan.
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('rekap_ujian.jadwalkan_remedial', $selectedQuizId) }}" onsubmit="return confirm('Jadwalkan sesi remedial untuk {{ $remedial }} siswa dan kirim notifikasi ke akun mereka?')">
            @csrf
            <button type="submit"
                class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-white text-rose-600 font-bold text-xs rounded-xl shadow hover:shadow-md transition">
                <i class="fas fa-calendar-check"></i>
                Jadwalkan Remedial
            </button>
        </form>
    </div>
    @endif

    @endif {{-- end $selectedQuizId --}}

    {{-- ===== PRINT CSS STYLES ===== --}}
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, sans-serif !important;
            }
            aside, nav, header, #tab-bar, #search-input, #sort-select, .tab-btn, button, form {
                display: none !important;
            }
            .print\:block {
                display: block !important;
            }
            .print\:flex {
                display: flex !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .shadow-sm, .shadow-md, .shadow-2xl, .shadow {
                box-shadow: none !important;
            }
            .border-slate-200, .border-slate-100 {
                border-color: #cbd5e1 !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #cbd5e1 !important;
                padding: 6px 8px !important;
            }
            th {
                background-color: #f8fafc !important;
                color: #1e293b !important;
            }
        }
    </style>

</div>

{{-- ===== TAB & TABLE FILTER JAVASCRIPT ===== --}}
<script>
    // ── Active tab state ──────────────────────────────────────────────
    let currentTab = 'semua';

    // Pre-built class sets (avoids dynamic Tailwind arbitrary-value injection)
    const TAB_INACTIVE = 'border-transparent text-slate-500 font-medium';
    const TAB_ACTIVE_DEFAULT  = 'font-bold text-[#13527D]';
    const TAB_ACTIVE_LULUS    = 'font-bold text-emerald-600';
    const TAB_ACTIVE_REMEDIAL = 'font-bold text-rose-600';

    function switchTab(tab) {
        currentTab = tab;

        // Reset ALL tab buttons to inactive state
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.className = btn.className
                .replace(/\bfont-bold\b/g, 'font-medium')
                .replace(/\btext-\[#13527D\]\b/g, 'text-slate-500')
                .replace(/\btext-emerald-600\b/g, 'text-slate-500')
                .replace(/\btext-rose-600\b/g, 'text-slate-500');
            btn.style.borderBottomColor = 'transparent';
            btn.style.borderBottomWidth = '2px';
            btn.style.fontWeight = '';
            btn.style.color = '#64748b'; // slate-500
        });

        // Apply active style to clicked tab via inline styles (works with CDN)
        const activeBtn = document.getElementById('tab-' + tab);
        if (!activeBtn) return;
        activeBtn.style.borderBottomWidth = '2px';
        activeBtn.style.fontWeight = '700';

        if (tab === 'remedial') {
            activeBtn.style.borderBottomColor = '#f87171'; // rose-400
            activeBtn.style.color = '#dc2626';              // rose-600
        } else if (tab === 'lulus') {
            activeBtn.style.borderBottomColor = '#34d399'; // emerald-400
            activeBtn.style.color = '#059669';              // emerald-600
        } else {
            activeBtn.style.borderBottomColor = '#13527D';
            activeBtn.style.color = '#13527D';
        }

        filterTable();

        // Toggle "Waktu / Keaktifan / Umpan Balik" columns
        document.querySelectorAll('.tab-col-rekap').forEach(el => {
            el.style.display = (tab === 'nilai') ? 'none' : '';
        });
    }

    // ── Search + tab filter ───────────────────────────────────────────
    function filterTable() {
        const search = (document.getElementById('search-input')?.value ?? '').toLowerCase();
        document.querySelectorAll('.rekap-row').forEach(row => {
            const nama      = (row.dataset.nama ?? '').toLowerCase();
            const matchSearch = nama.includes(search);
            const matchTab  =
                currentTab === 'semua'    ||
                currentTab === 'rekap'    ||
                currentTab === 'nilai'    ||
                (currentTab === 'lulus'    && row.classList.contains('row-lulus'))    ||
                (currentTab === 'remedial' && row.classList.contains('row-remedial'));
            row.style.display = (matchSearch && matchTab) ? '' : 'none';
        });
    }

    // ── Sort ──────────────────────────────────────────────────────────
    function sortTable() {
        const val   = document.getElementById('sort-select').value;
        const tbody = document.getElementById('rekap-tbody');
        const rows  = Array.from(tbody.querySelectorAll('.rekap-row'));

        rows.sort((a, b) => {
            if (val === 'nilai_desc') return parseFloat(b.dataset.nilai) - parseFloat(a.dataset.nilai);
            if (val === 'nilai_asc')  return parseFloat(a.dataset.nilai) - parseFloat(b.dataset.nilai);
            if (val === 'nama_asc')   return a.dataset.nama.localeCompare(b.dataset.nama);
            if (val === 'nama_desc')  return b.dataset.nama.localeCompare(a.dataset.nama);
            return 0;
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    // Init: make sure "Semua Siswa" tab looks active on load
    document.addEventListener('DOMContentLoaded', () => {
        const firstTab = document.getElementById('tab-semua');
        if (firstTab) {
            firstTab.style.borderBottomWidth = '2px';
            firstTab.style.borderBottomColor = '#13527D';
            firstTab.style.color = '#13527D';
            firstTab.style.fontWeight = '700';
        }
    });
</script>

{{-- ===== MODALS + JAVASCRIPT ===== --}}
@include('rekap-ujian.modals')

@endsection
