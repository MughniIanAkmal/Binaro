@extends('layouts.guru')

@section('breadcrumb', 'Dashboard Pengajar')
@section('page_title', 'Ruang Kelas & Penilaian')

@section('header_actions')
    <a href="{{ route('guru.absen.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition"
       style="background: var(--binaro-navy);">
        <i class="fas fa-qrcode text-[11px]"></i> Buka Presensi QR
    </a>
    <a href="{{ route('guru.ujian.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-[#E6EBF2] hover:bg-slate-50 transition">
        <i class="fas fa-plus text-[11px]"></i> Buat Tugas Baru
    </a>
@endsection

@section('content')
@php
    $namaGuru = $guru->nama_guru ?? session('user_name', 'Guru Binaro');
    $namaPanggilan = str_starts_with(strtolower($namaGuru), 'ibu') || str_starts_with(strtolower($namaGuru), 'bapak')
        ? $namaGuru
        : (($guru->jenis_kelamin ?? 'P') === 'L' ? 'Bapak '.$namaGuru : 'Ibu '.$namaGuru);

    $sesiAktif = $sesiHariIni->firstWhere('status', 'berlangsung');
    $sesiBerikut = $sesiAktif ?? $sesiHariIni->firstWhere('status', 'terjadwal');
    $sesiSelesai = $sesiHariIni->where('status', 'selesai');

    $tanggalLabel = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d M Y');
@endphp

<div class="space-y-5">

    {{-- ================= HERO ================= --}}
    <section class="hero-gradient rounded-2xl px-7 py-6 text-white relative overflow-hidden">
        <div class="absolute -right-16 -top-20 w-64 h-64 rounded-full bg-white/5"></div>
        <div class="absolute -right-4 -bottom-24 w-52 h-52 rounded-full bg-white/5"></div>

        <div class="relative flex items-start justify-between gap-6 flex-wrap">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/10 border border-white/15 text-[9.5px] font-extrabold tracking-[0.12em] uppercase">
                    <i class="fas fa-door-open text-[9px]"></i> Portal Pengajar Aktif
                </span>

                <h2 class="mt-3 text-[26px] leading-tight font-extrabold tracking-tight">Halo, {{ $namaPanggilan }}</h2>

                @if($sesiBerikut)
                    <p class="mt-1.5 text-[13px] text-sky-100/90 max-w-2xl leading-relaxed">
                        Sesi mengajar terdekat: <span class="font-bold text-white">{{ $sesiBerikut['mapel'] }}</span>
                        (Kelas {{ $sesiBerikut['kelas'] }}) pukul
                        <span class="font-bold text-white">{{ $sesiBerikut['mulai_label'] }} WIB</span>
                        di Ruang {{ $sesiBerikut['kelas'] }}.
                        Presensi barcode siswa siap dibuka.
                    </p>
                @elseif($sesiMendatang)
                    <p class="mt-1.5 text-[13px] text-sky-100/90 max-w-2xl leading-relaxed">
                        Semua sesi hari {{ $hariIni }} sudah tuntas. Sesi berikutnya
                        <span class="font-bold text-white">{{ $sesiMendatang['hari'] }}, {{ $sesiMendatang['tanggal'] }}</span>:
                        <span class="font-bold text-white">{{ $sesiMendatang['mapel'] }}</span>
                        (Kelas {{ $sesiMendatang['kelas'] }}) pukul
                        <span class="font-bold text-white">{{ $sesiMendatang['mulai_label'] }} WIB</span>.
                    </p>
                @else
                    <p class="mt-1.5 text-[13px] text-sky-100/90 max-w-2xl leading-relaxed">
                        Belum ada jadwal mengajar yang ditetapkan untuk Anda.
                        Waktu yang tepat untuk menyiapkan modul dan mengoreksi tugas siswa.
                    </p>
                @endif
            </div>

            <a href="{{ route('guru.absen.index') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white text-[12.5px] font-extrabold shadow-sm hover:bg-sky-50 transition shrink-0"
               style="color: var(--binaro-navy);">
                <i class="fas fa-qrcode text-[12px]"></i> Mulai Absen Sekarang
            </a>
        </div>
    </section>

    {{-- ================= 4 KPI ================= --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Jadwal hari ini --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: var(--binaro-navy);">
                    <i class="fas fa-calendar-day text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Jadwal Hari Ini</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">{{ $sesiHariIni->count() }} <span class="text-[15px] font-bold text-slate-500">Sesi</span></div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold text-slate-700">{{ $totalJp }} JP Total</span>
                <span class="text-slate-400 truncate">{{ $rombelLabel }}</span>
            </div>
        </div>

        {{-- Total siswa --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: #2563EB;">
                    <i class="fas fa-user-group text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Total Siswa Diajar</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">{{ number_format($totalSiswa, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold {{ $persenAktif >= 80 ? 'text-emerald-600' : 'text-amber-600' }}">
                    <i class="fas fa-circle text-[6px] align-middle mr-1"></i>{{ $persenAktif }}% Aktif
                </span>
                <span class="text-slate-400">{{ $rombelIds->count() }} Rombel Binaan</span>
            </div>
        </div>

        {{-- Perlu dikoreksi --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: #D97706;">
                    <i class="fas fa-pen text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Perlu Dikoreksi</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">{{ $perluDinilai }} <span class="text-[15px] font-bold text-slate-500">Berkas</span></div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold {{ $perluDinilai > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                    <i class="fas fa-circle text-[6px] align-middle mr-1"></i>{{ $perluDinilai > 0 ? 'Menunggu Koreksi' : 'Semua Terkoreksi' }}
                </span>
                <span class="text-slate-400 truncate">{{ $tugasPerluDinilai->first()['judul'] ?? 'Tidak ada antrean' }}</span>
            </div>
        </div>

        {{-- Kelengkapan RPP --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: #0D9488;">
                    <i class="fas fa-award text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Kelengkapan RPP</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">{{ $persenRpp }}%</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold text-teal-600"><i class="fas fa-circle text-[6px] align-middle mr-1"></i>Terverifikasi</span>
                <span class="text-slate-400">{{ $rppTerverifikasi }} dari {{ $rppTotal }} Mapel</span>
            </div>
        </div>
    </section>

    {{-- ================= SPLIT ================= --}}
    <section class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">

        {{-- ---------- JADWAL SESI ---------- --}}
        <div class="xl:col-span-8 card p-6">
            <div class="flex items-start justify-between gap-4 flex-wrap pb-4">
                <div>
                    <h3 class="text-[15px] font-extrabold text-slate-900">Jadwal Sesi Mengajar &amp; Presensi Kelas</h3>
                    <p class="text-[11.5px] text-slate-400 mt-0.5">Pilih kelas untuk mengaktifkan pemindaian barcode atau absensi manual</p>
                </div>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#F4F7FB] border border-[#E6EBF2] text-[11px] font-bold text-slate-600">
                    <i class="fas fa-calendar text-[10px] text-slate-400"></i> {{ $tanggalLabel }}
                </span>
            </div>

            <div class="space-y-3">
                @forelse($sesiHariIni as $sesi)
                    @php
                        $isAktif = $sesi['status'] === 'berlangsung';
                        $isSelesai = $sesi['status'] === 'selesai';
                        $isBerikut = ! $isAktif && ! $isSelesai && $sesiBerikut && $sesiBerikut['id_jadwal'] === $sesi['id_jadwal'];
                    @endphp

                    <div class="rounded-xl border transition {{ $isAktif ? 'border-[#0E3A5C] bg-[#F7FAFD] ring-1 ring-[#0E3A5C]/10' : ($isSelesai ? 'border-[#EFF2F7] bg-[#FBFCFE]' : 'border-[#E6EBF2] bg-white hover:border-slate-300') }}">
                        <div class="p-4 flex items-center gap-4 flex-wrap">

                            {{-- Blok jam --}}
                            @if($isSelesai)
                                <div class="w-14 h-14 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0">
                                    <i class="fas fa-check text-emerald-600 text-lg"></i>
                                </div>
                            @else
                                <div class="w-14 h-14 rounded-xl flex flex-col items-center justify-center shrink-0 {{ $isAktif ? 'text-white' : 'bg-[#F4F7FB] text-slate-700 border border-[#E6EBF2]' }}"
                                     style="{{ $isAktif ? 'background: var(--binaro-navy);' : '' }}">
                                    <span class="text-[12.5px] font-extrabold leading-none">{{ $sesi['mulai_label'] }}</span>
                                    <span class="text-[9.5px] font-semibold leading-none mt-1 {{ $isAktif ? 'text-sky-200' : 'text-slate-400' }}">{{ $sesi['selesai_label'] }}</span>
                                </div>
                            @endif

                            {{-- Info --}}
                            <div class="flex-1 min-w-[190px]">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-[13.5px] font-extrabold text-slate-900">{{ $sesi['mapel'] }}</h4>
                                    @if($isBerikut)
                                        <span class="px-2 py-0.5 rounded-md bg-[#EAF1F8] text-[9px] font-extrabold tracking-[0.08em] uppercase" style="color: var(--binaro-navy);">Sesi Berikutnya</span>
                                    @elseif($isAktif)
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 text-[9px] font-extrabold tracking-[0.08em] uppercase">
                                            <i class="fas fa-circle text-[5px] align-middle mr-1 animate-pulse"></i>Berlangsung
                                        </span>
                                    @elseif(! $isSelesai)
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[9px] font-extrabold tracking-[0.08em] uppercase">Terjadwal</span>
                                    @endif
                                </div>

                                <div class="mt-1 flex items-center gap-2 flex-wrap text-[11px] text-slate-500">
                                    <span class="font-semibold text-slate-600">Kelas {{ $sesi['kelas'] }}</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span>{{ $sesi['jumlah_siswa'] }} Siswa Terdaftar</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span>Ruang {{ $sesi['kelas'] }}</span>                                    @if($isSelesai)
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="font-semibold text-emerald-600">Sesi tuntas</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Aksi --}}
                            <div class="flex items-center gap-2 shrink-0">
                                @if($isSelesai)
                                    <a href="{{ route('guru.absensi.rekap') }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-[11.5px] font-bold text-slate-600 hover:bg-slate-100 transition">
                                        Lihat Rekap Absensi <i class="fas fa-arrow-right text-[9px]"></i>
                                    </a>
                                @else
                                    <a href="{{ route('guru.absen.index') }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-[11.5px] font-bold text-white transition hover:opacity-90"
                                       style="background: var(--binaro-navy);">
                                        <i class="fas fa-qrcode text-[10px]"></i> Buka Absen QR
                                    </a>
                                    <a href="{{ route('guru.rpp.index') }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-[11.5px] font-bold text-slate-600 bg-white border border-[#E6EBF2] hover:bg-slate-50 transition">
                                        Modul Ajar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-[#E6EBF2] bg-[#FBFCFE] py-14 px-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-300">
                            <i class="fas fa-mug-hot"></i>
                        </div>
                        <h4 class="mt-3 text-[13px] font-bold text-slate-700">Tidak ada sesi mengajar hari {{ $hariIni }}</h4>
                        <p class="mt-1 text-[11.5px] text-slate-400">Gunakan waktu ini untuk menyiapkan modul ajar atau mengoreksi tugas siswa.</p>
                        <a href="{{ route('guru.jadwal.index') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg text-[11.5px] font-bold text-white" style="background: var(--binaro-navy);">
                            <i class="fas fa-calendar-days text-[10px]"></i> Lihat Jadwal Mingguan
                        </a>
                    </div>
                @endforelse
            </div>

            @if($sesiSelesai->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-[#EFF2F7] flex items-center justify-between gap-3 flex-wrap">
                    <span class="text-[11px] text-slate-400">
                        <i class="fas fa-circle-check text-emerald-500 mr-1"></i>
                        {{ $sesiSelesai->count() }} sesi hari ini sudah tuntas
                    </span>
                    <a href="{{ route('guru.jadwal.index') }}" class="text-[11.5px] font-bold text-slate-500 hover:text-slate-800 transition">
                        Lihat semua jadwal <i class="fas fa-arrow-right text-[9px] ml-1"></i>
                    </a>
                </div>
            @endif
        </div>

        {{-- ---------- TUGAS PERLU DINILAI ---------- --}}
        <div class="xl:col-span-4 card p-6">
            <div class="flex items-start justify-between gap-3 pb-4">
                <div>
                    <h3 class="text-[15px] font-extrabold text-slate-900">Tugas Perlu Dinilai</h3>
                    <p class="text-[11.5px] text-slate-400 mt-0.5">Pekerjaan siswa terkumpul</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-100 text-[10.5px] font-extrabold text-amber-700 shrink-0">
                    {{ $perluDinilai }} Berkas
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse($tugasPerluDinilai as $tugas)
                    @php
                        $tgl = $tugas['tanggal'];
                        $badge = 'Belum dinilai';
                        $badgeClass = 'bg-slate-100 text-slate-500';
                        if ($tgl) {
                            if ($tgl->isToday()) {
                                $badge = 'Hari Ini';
                                $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-100';
                            } elseif ($tgl->isYesterday()) {
                                $badge = 'Kemarin';
                                $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                            } else {
                                $badge = $tgl->locale('id')->translatedFormat('d M Y');
                                $badgeClass = 'bg-slate-100 text-slate-500';
                            }
                        }
                    @endphp

                    <div class="rounded-xl border border-[#E6EBF2] bg-white p-3.5 hover:border-slate-300 transition">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-[12.5px] font-extrabold text-slate-900 truncate">{{ $tugas['judul'] }}</h4>
                                <p class="text-[10.5px] text-slate-400 mt-0.5 truncate">
                                    {{ $tugas['mapel'] }} &bull; {{ $tugas['terkumpul'] }} Terkumpul
                                </p>
                            </div>
                            <a href="{{ route('guru.ujian.show', $tugas['id_quiz']) }}"
                               class="shrink-0 px-3 py-1.5 rounded-lg text-[10.5px] font-extrabold text-white transition hover:opacity-90"
                               style="background: var(--binaro-navy);">
                                Koreksi
                            </a>
                        </div>
                        <div class="mt-2.5 flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[9.5px] font-extrabold {{ $badgeClass }}">{{ $badge }}</span>
                            <span class="text-[10px] text-slate-400">{{ $tugas['belum_dinilai'] }} belum dikoreksi</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-[#E6EBF2] bg-[#FBFCFE] py-10 px-5 text-center">
                        <div class="w-11 h-11 rounded-full bg-emerald-50 flex items-center justify-center mx-auto text-emerald-500">
                            <i class="fas fa-check"></i>
                        </div>
                        <h4 class="mt-3 text-[12.5px] font-bold text-slate-700">Tidak ada antrean koreksi</h4>
                        <p class="mt-1 text-[11px] text-slate-400">Semua pekerjaan siswa sudah dinilai.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 pt-4 border-t border-[#EFF2F7] space-y-2">
                <a href="{{ route('rekap_nilai.index') }}"
                   class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-[#E6EBF2] bg-[#FBFCFE] hover:bg-slate-50 transition group">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-slate-700">
                        <i class="fas fa-file-excel text-emerald-600 text-[12px]"></i> Ekspor Rekap Nilai ke Excel
                    </span>
                    <i class="fas fa-arrow-right text-[9px] text-slate-400 group-hover:text-slate-700 transition"></i>
                </a>
                <a href="{{ route('guru.ujian.index') }}"
                   class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-[#E6EBF2] bg-white hover:bg-slate-50 transition group">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-slate-700">
                        <i class="fas fa-list-check text-[12px]" style="color: var(--binaro-navy);"></i> Kelola Semua Tugas &amp; Kuis
                    </span>
                    <i class="fas fa-arrow-right text-[9px] text-slate-400 group-hover:text-slate-700 transition"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ================= MAPEL DIAMPU ================= --}}
    <section class="card p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap pb-4">
            <div>
                <h3 class="text-[15px] font-extrabold text-slate-900">Mata Pelajaran Diampu</h3>
                <p class="text-[11.5px] text-slate-400 mt-0.5">Modul ajar dan materi yang Anda kelola semester ini</p>
            </div>
            <a href="{{ route('guru.mapel.browse') }}" class="text-[11.5px] font-bold text-slate-500 hover:text-slate-800 transition">
                Lihat semua <i class="fas fa-arrow-right text-[9px] ml-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @forelse($mapelDiampu as $namaMapel)
                @php
                    $nm = strtolower($namaMapel);
                    $icon = 'fa-book-open';
                    $warna = ['bg' => '#EAF1F8', 'fg' => '#0E3A5C'];
                    if (str_contains($nm, 'matematika')) { $icon = 'fa-calculator'; $warna = ['bg' => '#FEF3C7', 'fg' => '#B45309']; }
                    elseif (str_contains($nm, 'ipa') || str_contains($nm, 'alam')) { $icon = 'fa-flask'; $warna = ['bg' => '#DCFCE7', 'fg' => '#15803D']; }
                    elseif (str_contains($nm, 'indonesia')) { $icon = 'fa-pen-nib'; $warna = ['bg' => '#FFE4E6', 'fg' => '#BE123C']; }
                    elseif (str_contains($nm, 'inggris')) { $icon = 'fa-globe'; $warna = ['bg' => '#FFEDD5', 'fg' => '#C2410C']; }
                    elseif (str_contains($nm, 'kimia')) { $icon = 'fa-atom'; $warna = ['bg' => '#EDE9FE', 'fg' => '#6D28D9']; }
                @endphp
                <a href="{{ route('guru.mapel.browse') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl border border-[#E6EBF2] bg-white hover:border-slate-300 hover:shadow-sm transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: {{ $warna['bg'] }}; color: {{ $warna['fg'] }};">
                        <i class="fas {{ $icon }} text-[13px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-[12.5px] font-extrabold text-slate-900 truncate">{{ $namaMapel }}</h4>
                        <p class="text-[10.5px] text-slate-400">Kelas {{ $rombelLabel }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                </a>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-xl border border-dashed border-[#E6EBF2] bg-[#FBFCFE] py-10 text-center">
                    <p class="text-[12px] text-slate-500 font-semibold">Belum ada jadwal mengajar yang ditetapkan.</p>
                    <p class="text-[11px] text-slate-400 mt-1">Hubungi bagian kurikulum untuk penugasan mata pelajaran.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
