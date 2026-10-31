@extends('layouts.siswa')

@section('content')

<div class="space-y-6 max-w-6xl mx-auto pb-12">

    {{-- ===== HEADER HALAMAN ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="bg-[#13527D]/10 text-[#13527D] font-bold text-[11px] px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                    <i class="fas fa-school text-[10px]"></i>
                    <span>
                        @if($siswa && $siswa->kelas)
                            {{ $siswa->kelas->pararel ?? 'Kelas Saya' }}
                        @else
                            SDN Kalitapen 01
                        @endif
                        &bull; SDN Kalitapen 01
                    </span>
                </span>
                <span class="bg-slate-100 text-slate-600 font-medium text-[11px] px-2.5 py-0.5 rounded-full">
                    Semester Genap TA 2026/2027
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Jadwal Mata Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-1">
                Jadwal pelajaran resmi yang telah disusun oleh pihak sekolah. Pantau jadwal harian agar siap belajar.
            </p>
        </div>

        {{-- Tombol Cetak --}}
        <div class="shrink-0 flex items-center gap-2 print:hidden">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white text-[#13527D] hover:bg-slate-50 font-bold text-xs rounded-xl border border-slate-200 shadow-sm transition">
                <i class="fas fa-print"></i>
                <span>Cetak Jadwal</span>
            </button>
        </div>
    </div>

    {{-- ===== KPI / STATS RINGKAS ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 print:hidden">
        {{-- Hari Ini --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shrink-0">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide block">Hari Ini</span>
                <div class="text-sm font-black text-slate-800 flex items-center gap-1.5">
                    <span>{{ $hariIni }}</span>
                    @if($hariIni !== 'Minggu')
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Total Sesi --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-clock"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide block">Total Jam/Pekan</span>
                <div class="text-sm font-black text-slate-800">{{ $totalSesi }} Sesi</div>
            </div>
        </div>

        {{-- Total Mapel --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base shrink-0">
                <i class="fas fa-book"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide block">Mata Pelajaran</span>
                <div class="text-sm font-black text-slate-800">{{ $totalMapel }} Pelajaran</div>
            </div>
        </div>

        {{-- Guru Pengampu --}}
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base shrink-0">
                <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide block">Guru Pengajar</span>
                <div class="text-sm font-black text-slate-800">{{ $totalGuru }} Guru</div>
            </div>
        </div>
    </div>

    {{-- ===== TABS FILTER HARI ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-2 shadow-sm print:hidden">
        <div class="flex items-center gap-1.5 overflow-x-auto py-0.5 px-0.5" id="tab-hari-wrapper">
            {{-- Tab Semua --}}
            <button type="button" onclick="pilihHari('semua')" id="tab-btn-semua"
                class="tab-btn-hari px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0"
                style="background-color:#13527D; color:white;">
                <i class="fas fa-layer-group text-[10px]"></i>
                <span>Semua Hari</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold" style="background:rgba(255,255,255,0.2); color:white;">{{ $totalSesi }}</span>
            </button>

            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                @php
                    $countSesi = isset($jadwalPerHari[$hari]) ? $jadwalPerHari[$hari]->count() : 0;
                    $isHariIni = ($hari === $hariIni);
                @endphp
                <button type="button" onclick="pilihHari('{{ $hari }}')" id="tab-btn-{{ $hari }}"
                    class="tab-btn-hari px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#13527D] hover:bg-slate-50 transition flex items-center gap-1.5 shrink-0">
                    <span>{{ $hari }}</span>
                    @if($isHariIni)
                    <span class="bg-emerald-100 text-emerald-700 text-[9px] font-extrabold px-1.5 py-0.5 rounded-md">Hari Ini</span>
                    @endif
                    <span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.5 rounded-full font-bold">{{ $countSesi }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ===== KONTEN JADWAL PER HARI ===== --}}
    @if($jadwals->isEmpty())
    {{-- State kosong: belum ada jadwal --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-16 text-center flex flex-col items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-300 text-2xl">
            <i class="fas fa-calendar-xmark"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-700">Jadwal Belum Tersedia</h3>
            <p class="text-xs text-slate-400 mt-1">Jadwal mata pelajaran untuk kelas Anda belum diinput oleh admin.</p>
            <p class="text-xs text-slate-400">Silakan hubungi wali kelas untuk informasi lebih lanjut.</p>
        </div>
    </div>
    @else
    <div class="space-y-6" id="konten-jadwal-wrapper">
        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
            @php
                $sesiHari = $jadwalPerHari[$hari] ?? collect();
                $isHariIni = ($hari === $hariIni);
            @endphp

            <div class="kartu-hari bg-white rounded-2xl border {{ $isHariIni ? 'border-[#13527D]' : 'border-slate-200' }} shadow-sm overflow-hidden"
                 data-hari="{{ $hari }}"
                 style="{{ $isHariIni ? 'box-shadow: 0 0 0 3px rgba(19,82,125,0.08);' : '' }}">

                {{-- Header Kartu Hari --}}
                <div class="px-6 py-4 {{ $isHariIni ? 'bg-gradient-to-r from-sky-50 to-white' : 'bg-slate-50/60' }} border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm"
                             style="{{ $isHariIni ? 'background:#13527D; color:white;' : 'background:white; color:#475569; border:1px solid #e2e8f0;' }}">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-black text-slate-900">{{ $hari }}</h2>
                                @if($isHariIni)
                                <span class="bg-emerald-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>Hari Ini</span>
                                </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-400">
                                @if($sesiHari->isNotEmpty())
                                    {{ $sesiHari->count() }} mata pelajaran terjadwal
                                @else
                                    Tidak ada mata pelajaran
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($siswa && $siswa->kelas)
                    <span class="text-xs font-semibold text-slate-400 hidden sm:inline-block">
                        {{ $siswa->kelas->pararel ?? '' }}
                    </span>
                    @endif
                </div>

                {{-- List Mata Pelajaran --}}
                <div class="p-5 sm:p-6">
                    @if($sesiHari->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($sesiHari as $idx => $item)
                            @php
                                $namaMapel = $item->mataPelajaran->nama_mapel ?? 'Mata Pelajaran';
                                $iconMapel  = 'fa-book-open';
                                $colorStyle = 'background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;';

                                $nm = strtolower($namaMapel);
                                if (str_contains($nm, 'matematika')) {
                                    $iconMapel  = 'fa-calculator';
                                    $colorStyle = 'background:#fffbeb; color:#b45309; border-color:#fde68a;';
                                } elseif (str_contains($nm, 'alam') || str_contains($nm, 'ipa')) {
                                    $iconMapel  = 'fa-flask';
                                    $colorStyle = 'background:#f0fdf4; color:#15803d; border-color:#bbf7d0;';
                                } elseif (str_contains($nm, 'indonesia')) {
                                    $iconMapel  = 'fa-pen-nib';
                                    $colorStyle = 'background:#fff1f2; color:#be123c; border-color:#fecdd3;';
                                } elseif (str_contains($nm, 'pancasila') || str_contains($nm, 'pkn')) {
                                    $iconMapel  = 'fa-shield-halved';
                                    $colorStyle = 'background:#faf5ff; color:#7e22ce; border-color:#e9d5ff;';
                                } elseif (str_contains($nm, 'inggris')) {
                                    $iconMapel  = 'fa-globe';
                                    $colorStyle = 'background:#fff7ed; color:#c2410c; border-color:#fed7aa;';
                                } elseif (str_contains($nm, 'agama')) {
                                    $iconMapel  = 'fa-star-and-crescent';
                                    $colorStyle = 'background:#fefce8; color:#92400e; border-color:#fde68a;';
                                } elseif (str_contains($nm, 'sosial') || str_contains($nm, 'ips')) {
                                    $iconMapel  = 'fa-earth-asia';
                                    $colorStyle = 'background:#ecfdf5; color:#065f46; border-color:#a7f3d0;';
                                } elseif (str_contains($nm, 'seni') || str_contains($nm, 'budaya')) {
                                    $iconMapel  = 'fa-palette';
                                    $colorStyle = 'background:#fdf4ff; color:#86198f; border-color:#f0abfc;';
                                } elseif (str_contains($nm, 'olahraga') || str_contains($nm, 'pjok')) {
                                    $iconMapel  = 'fa-futbol';
                                    $colorStyle = 'background:#f0fdf4; color:#166534; border-color:#86efac;';
                                }
                            @endphp

                            <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/50 transition flex flex-col gap-3 shadow-sm">
                                {{-- Baris atas: Jam & Urutan --}}
                                <div class="flex items-center justify-between">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-mono font-bold">
                                        <i class="far fa-clock text-slate-400 text-[10px]"></i>
                                        <span>{{ $item->jam }} WIB</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400">Jam Ke-{{ $idx + 1 }}</span>
                                </div>

                                {{-- Info Mapel & Guru --}}
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl border flex items-center justify-center text-sm shrink-0"
                                         style="{{ $colorStyle }}">
                                        <i class="fas {{ $iconMapel }}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-slate-900 text-sm truncate">
                                            {{ $namaMapel }}
                                        </h3>
                                        <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5 truncate">
                                            <i class="fas fa-chalkboard-user text-[10px] text-slate-400"></i>
                                            <span>{{ $item->guru->nama_guru ?? 'Guru Pengajar' }}</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Footer kartu: ruang & link materi --}}
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-[11px] text-slate-400 flex items-center gap-1">
                                        <i class="fas fa-door-open text-[10px]"></i>
                                        <span>
                                            @if($item->kelas)
                                                {{ $item->kelas->pararel ?? 'Kelas' }}
                                            @else
                                                Ruang Kelas
                                            @endif
                                        </span>
                                    </span>

                                    @if($item->id_mapel)
                                    <a href="{{ route('siswa.materi.index', $item->id_mapel) }}"
                                       class="text-[11px] font-bold text-[#13527D] hover:underline flex items-center gap-1 print:hidden">
                                        <span>Buka Materi</span>
                                        <i class="fas fa-arrow-right text-[9px]"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @else
                    {{-- Tidak ada jadwal hari ini --}}
                    <div class="py-8 text-center flex flex-col items-center gap-2 text-slate-400">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-300">
                            <i class="fas fa-mug-hot text-sm"></i>
                        </div>
                        <p class="text-xs font-medium text-slate-600">Tidak ada jadwal pelajaran di hari {{ $hari }}.</p>
                        <p class="text-[11px] text-slate-400">Waktu luang dapat dimanfaatkan untuk mengulang materi.</p>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- ===== CATATAN PENTING ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-3 print:hidden">
        <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
            <i class="fas fa-circle-info text-[#13527D]"></i>
            <span>Catatan Penting Kegiatan Belajar Mengajar</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                <span class="font-bold text-slate-800 flex items-center gap-1.5 text-xs">
                    <i class="fas fa-bell text-amber-500"></i> Jam Masuk Sekolah
                </span>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Siswa diharapkan tiba di sekolah paling lambat pukul <strong>07.15 WIB</strong> untuk pembiasaan pagi bersama wali kelas.
                </p>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                <span class="font-bold text-slate-800 flex items-center gap-1.5 text-xs">
                    <i class="fas fa-apple-whole text-emerald-500"></i> Waktu Istirahat
                </span>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Waktu istirahat pertama pukul <strong>09.00 – 09.30 WIB</strong>. Gunakan untuk makan bekal dan menyegarkan pikiran.
                </p>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                <span class="font-bold text-slate-800 flex items-center gap-1.5 text-xs">
                    <i class="fas fa-book-bookmark text-sky-500"></i> Persiapan Buku
                </span>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Siapkan buku tulis, buku paket, dan peralatan belajar malam hari sebelumnya sesuai roster di atas.
                </p>
            </div>
        </div>
    </div>

</div>

{{-- ===== JAVASCRIPT: FILTER TABS ===== --}}
<script>
    function pilihHari(hari) {
        // Reset SEMUA tombol ke style non-aktif (inline style saja, tidak pakai classList)
        document.querySelectorAll('.tab-btn-hari').forEach(function(btn) {
            btn.style.backgroundColor = '';
            btn.style.color           = '';
            btn.style.boxShadow       = '';
        });

        // Aktifkan tombol yang dipilih via inline style
        var activeBtn = document.getElementById('tab-btn-' + hari);
        if (activeBtn) {
            activeBtn.style.backgroundColor = '#13527D';
            activeBtn.style.color           = 'white';
            activeBtn.style.boxShadow       = '0 1px 3px rgba(0,0,0,0.15)';
        }

        // Tampilkan / sembunyikan kartu hari
        document.querySelectorAll('.kartu-hari').forEach(function(card) {
            card.style.display = (hari === 'semua' || card.dataset.hari === hari) ? '' : 'none';
        });
    }
</script>

{{-- Print CSS --}}
<style>
    @media print {
        aside, .print\:hidden { display: none !important; }
        main { margin-left: 0 !important; padding: 0 !important; }
        .kartu-hari { break-inside: avoid; margin-bottom: 1.5rem; border: 1px solid #cbd5e1 !important; }
    }
</style>

@endsection
