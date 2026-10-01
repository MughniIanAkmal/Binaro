@extends('layouts.guru')

@section('content')
<div class="p-8 space-y-6">

    {{-- ===== BREADCRUMB ===== --}}
    <nav class="flex items-center gap-2 text-[11px] text-slate-400 font-medium">
        <i class="fas fa-home text-slate-300"></i>
        <span class="text-slate-300">/</span>
        <span class="text-slate-500">Ujian Online</span>
        <span class="text-slate-300">/</span>
        <span class="text-[#13527D] font-semibold">Hasil & Rekap Nilai</span>
    </nav>

    {{-- ===== PAGE HEADER ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 leading-tight">
                Hasil Ujian:
                <span class="text-[#13527D]">
                    {{ $selectedQuiz?->judul_quiz ?? 'Pilih Ujian' }}
                </span>
            </h1>
            <div class="flex flex-wrap items-center gap-4 mt-2 text-[11px] text-slate-500">
                @if($selectedQuiz)
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-calendar-days text-slate-400"></i>
                    {{ $selectedQuiz->created_at?->format('d M Y') ?? '-' }}
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-clock text-slate-400"></i>
                    Durasi: {{ $selectedQuiz->durasi ?? '60' }} menit
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-chalkboard-teacher text-slate-400"></i>
                    {{ session('user_name', 'Guru Binaro') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-book text-slate-400"></i>
                    {{ $selectedQuiz->subBab?->bab?->mataPelajaran?->nama_mapel ?? '-' }}
                </span>
                @else
                <span class="italic text-slate-400">Silakan pilih ujian terlebih dahulu</span>
                @endif
            </div>
        </div>

        {{-- Right Controls --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Pilih Kuis Dropdown --}}
            <form method="GET" action="{{ route('rekap_ujian.index') }}" class="flex items-center gap-2">
                <select name="quiz_id" onchange="this.form.submit()"
                    class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#13527D] shadow-sm">
                    <option value="">-- Pilih Ujian --</option>
                    @foreach($quizzes as $q)
                    <option value="{{ $q->id_quiz }}" {{ $selectedQuizId == $q->id_quiz ? 'selected' : '' }}>
                        {{ $q->judul_quiz }} ({{ $q->subBab?->bab?->mataPelajaran?->nama_mapel ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </form>

            @if($selectedQuizId && $hasils->isNotEmpty())
            {{-- Export CSV --}}
            <a href="{{ route('guru.quiz.export', $selectedQuizId) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                <i class="fas fa-file-excel"></i> Unduh Excel
            </a>
            {{-- Kirim Nilai ke Seluruh Siswa --}}
            <form method="POST" action="{{ route('rekap_ujian.kirim_semua', $selectedQuizId) }}" class="inline" onsubmit="return confirm('Kirimkan nilai kuis ini ke seluruh siswa?')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#13527D] hover:bg-[#0e4064] text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <i class="fas fa-paper-plane"></i> Kirim Nilai ke Semua Siswa
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- ===== NO QUIZ SELECTED EMPTY STATE ===== --}}
    @if(!$selectedQuizId)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-16 flex flex-col items-center justify-center text-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-[#13527D]/10 flex items-center justify-center">
            <i class="fas fa-clipboard-list text-2xl text-[#13527D]/60"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-700 text-sm mb-1">Belum Ada Ujian Dipilih</h3>
            <p class="text-xs text-slate-400 max-w-xs">Silakan pilih ujian dari dropdown di atas untuk melihat rekap nilai dan statistik siswa.</p>
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
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Rata-rata Nilai --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Rata-rata Nilai</span>
                <div class="w-8 h-8 rounded-xl bg-sky-100 flex items-center justify-center">
                    <i class="fas fa-chart-line text-sky-500 text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-[#13527D]">{{ $rataRata }}</div>
            <div class="text-[11px] text-slate-400">Dari {{ $totalSiswa }} peserta</div>
        </div>

        {{-- Rata-rata Keaktifan --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Rata-rata Keaktifan</span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-star text-amber-400 text-xs"></i>
                </div>
            </div>
            @if($rataKeaktifan)
            <div class="text-3xl font-black text-amber-500">{{ $rataKeaktifan }}</div>
            <div class="text-[11px] text-slate-400">Poin rata-rata kelas</div>
            @else
            <div class="text-3xl font-black text-amber-500">–</div>
            <div class="text-[11px] text-slate-400">Belum ada data</div>
            @endif
        </div>

        {{-- Tingkat Kelulusan --}}
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-5 flex flex-col gap-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Tingkat Kelulusan</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-medal text-emerald-500 text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-emerald-600">{{ $pctLulus }}%</div>
            <div class="text-[11px] text-slate-400">{{ $lulus }} lulus · {{ $remedial }} remedial</div>
        </div>

        {{-- Nilai Tertinggi --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Nilai Tertinggi</span>
                <div class="w-8 h-8 rounded-xl bg-violet-100 flex items-center justify-center">
                    <i class="fas fa-trophy text-violet-500 text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-violet-600">{{ number_format($tertinggi, 0) }}</div>
            <div class="text-[11px] text-slate-400">Skor maksimum</div>
        </div>

        {{-- Nilai Terendah --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Nilai Terendah</span>
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center">
                    <i class="fas fa-arrow-trend-down text-rose-500 text-xs"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-rose-500">{{ number_format($terendah, 0) }}</div>
            <div class="text-[11px] text-slate-400">Skor minimum</div>
        </div>
    </div>

    {{-- ===== MAIN TABLE CARD ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Tab Bar + Search + Sort --}}
        <div class="border-b border-slate-200 px-6 pt-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                {{-- Tabs --}}
                <div class="flex items-center gap-1 overflow-x-auto" id="tab-bar">
                    <button onclick="switchTab('semua')" id="tab-semua"
                        class="tab-btn active-tab whitespace-nowrap px-4 py-2 text-[11px] font-bold rounded-t-lg border-b-2 border-[#13527D] text-[#13527D] transition">
                        Semua Siswa <span class="ml-1 bg-[#13527D]/10 text-[#13527D] px-1.5 py-0.5 rounded-full text-[10px]">{{ $totalSiswa }}</span>
                    </button>
                    <button onclick="switchTab('lulus')" id="tab-lulus"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-emerald-600 hover:border-emerald-400 transition">
                        Lulus KKM <span class="ml-1 bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded-full text-[10px]">{{ $lulus }}</span>
                    </button>
                    <button onclick="switchTab('remedial')" id="tab-remedial"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-rose-600 hover:border-rose-400 transition">
                        Remedial <span class="ml-1 bg-rose-50 text-rose-500 px-1.5 py-0.5 rounded-full text-[10px]">{{ $remedial }}</span>
                    </button>
                    <button onclick="switchTab('rekap')" id="tab-rekap"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-[#13527D] hover:border-[#13527D] transition">
                        Rekap + Keaktifan
                    </button>
                    <button onclick="switchTab('nilai')" id="tab-nilai"
                        class="tab-btn whitespace-nowrap px-4 py-2 text-[11px] font-medium rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-[#13527D] hover:border-[#13527D] transition">
                        Nilai Ujian Saja
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
                        <th class="px-4 py-3.5 text-center tab-col-rekap tab-col-semua">Waktu Pengerjaan</th>
                        <th class="px-4 py-3.5 text-center tab-col-semua">Benar / Salah</th>
                        <th class="px-4 py-3.5 text-center tab-col-rekap tab-col-semua">Nilai Keaktifan</th>
                        <th class="px-4 py-3.5 text-center">Nilai Akhir & Status</th>
                        <th class="px-4 py-3.5 tab-col-rekap tab-col-semua">Umpan Balik Guru</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
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
                                <div class="w-8 h-8 rounded-xl {{ $avatarColor }} text-white flex items-center justify-center font-bold text-[10px] shrink-0 shadow-sm">
                                    {{ $namaInisial }}
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900 text-xs">{{ $row->siswa?->nm_siswa ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $row->siswa?->nisn ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Waktu Pengerjaan --}}
                        <td class="px-4 py-3.5 text-center tab-col-rekap tab-col-semua">
                            <span class="text-xs font-mono text-slate-600">
                                {{ $row->waktu_menit ? $row->waktu_menit . ' menit' : '–' }}
                            </span>
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
                            <div class="flex items-center justify-center gap-0.5 text-amber-400 text-xs">
                                @for($s = 1; $s <= 5; $s++)
                                    @if($s <= $bintang)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star text-slate-300"></i>
                                    @endif
                                @endfor
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $row->nilai_keaktifan ? $row->nilai_keaktifan . ' poin' : '–' }}
                            </div>
                        </td>

                        {{-- Nilai Akhir & Status --}}
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-lg font-black {{ $isLulus ? 'text-emerald-600' : 'text-rose-500' }}">
                                    {{ number_format($row->nilai_akhir ?? 0, 0) }}
                                </span>
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
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                    <i class="fas fa-check text-[8px]"></i> Terkirim
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1">
                                    <i class="fas fa-clock text-[8px]"></i> Belum Dikirim
                                </span>
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
                        <td class="px-4 py-3.5 text-center">
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
                                        status: '{{ ($row->nilai_akhir ?? 0) >= 70 ? 'LULUS KKM' : 'REMEDIAL' }}',
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
                                <button title="Kirim Pesan"
                                    onclick="openKirimModal({{ $row->id_hasil }}, '{{ addslashes($row->siswa?->nm_siswa ?? '-') }}')"
                                    class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition border border-emerald-100">
                                    <i class="fas fa-paper-plane text-[10px]"></i>
                                </button>
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
                                <p class="text-xs font-medium text-slate-500">Belum ada siswa yang mengerjakan ujian ini</p>
                                <p class="text-[11px] text-slate-400">Nilai akan otomatis muncul setelah siswa menyelesaikan pengerjaan ujian.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer --}}
        @if($hasils->isNotEmpty())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <p class="text-[11px] text-slate-400">
                Menampilkan <strong>{{ $hasils->count() }}</strong> siswa
                <span class="mx-1">·</span>
                <span class="text-emerald-600 font-semibold">{{ $lulus }} lulus</span>
                <span class="mx-1">·</span>
                <span class="text-rose-500 font-semibold">{{ $remedial }} remedial</span>
            </p>
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                <span>KKM: <strong class="text-slate-700">70</strong></span>
            </div>
        </div>
        @endif
    </div>

    {{-- ===== REMEDIAL BANNER ===== --}}
    @if($remedial > 0)
    <div class="bg-gradient-to-r from-rose-500 to-rose-600 rounded-2xl shadow-md p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
