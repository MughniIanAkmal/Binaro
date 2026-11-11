@extends('layouts.siswa')

@section('page_title', 'Beranda Siswa')

@section('header_actions')
    <a href="{{ route('siswa.mapel.index') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition"
       style="background: var(--binaro-navy);">
        <i class="fas fa-book-open text-[11px]"></i> Mulai Belajar
    </a>
@endsection

@section('content')
@php
    $namaSiswa = $nama ?? session('user_name', 'Siswa');
    $kelasSiswa = $kelas ?? 'Siswa';
    $tugasAktif = collect($tugas ?? []);
    $jumlahTugasAktif = $tugasAktif->count();
    $jumlahMapel = count($mapel ?? []);
@endphp

<div class="space-y-5 max-w-6xl mx-auto">

    {{-- ================= HERO ================= --}}
    <section class="hero-gradient rounded-2xl px-7 py-6 text-white relative overflow-hidden">
        <div class="absolute -right-16 -top-20 w-64 h-64 rounded-full bg-white/5"></div>
        <div class="absolute -right-4 -bottom-24 w-52 h-52 rounded-full bg-white/5"></div>

        <div class="relative flex items-start justify-between gap-6 flex-wrap">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/10 border border-white/15 text-[9.5px] font-extrabold tracking-[0.12em] uppercase">
                    <i class="fas fa-graduation-cap text-[9px]"></i> Portal Belajar Siswa
                </span>

                <h2 class="mt-3 text-[26px] leading-tight font-extrabold tracking-tight">Halo, {{ $namaSiswa }}!</h2>

                <p class="mt-1.5 text-[13px] text-sky-100/90 max-w-2xl leading-relaxed">
                    Kamu punya <span class="font-bold text-white">{{ $jumlahTugasAktif }} tugas</span> yang belum lewat tenggat
                    di <span class="font-bold text-white">{{ $jumlahMapel }} mata pelajaran</span>.
                    Jelajahi materi, kerjakan kuis, dan pantau jadwalmu di sini.
                </p>
            </div>

            <a href="{{ route('siswa.ujian.index') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white text-[12.5px] font-extrabold shadow-sm hover:bg-sky-50 transition shrink-0"
               style="color: var(--binaro-navy);">
                <i class="fas fa-clipboard-question text-[12px]"></i> Buka Ujian Online
            </a>
        </div>
    </section>

    {{-- ================= 4 KPI ================= --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Tugas aktif --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: var(--binaro-navy);">
                    <i class="fas fa-clipboard-list text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Tugas Aktif</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">{{ $jumlahTugasAktif }} <span class="text-[15px] font-bold text-slate-500">PR</span></div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold text-slate-700">Total {{ $tugas_total ?? 0 }} Tugas</span>
                <span class="text-slate-400 truncate">Semester ini</span>
            </div>
        </div>

        {{-- Mata pelajaran --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: #2563EB;">
                    <i class="fas fa-book-bookmark text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Mata Pelajaran</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">{{ $jumlahMapel }}</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold text-emerald-600"><i class="fas fa-circle text-[6px] align-middle mr-1"></i>Tersedia</span>
                <span class="text-slate-400 truncate">Kelas {{ $kelasSiswa }}</span>
            </div>
        </div>

        {{-- Ujian --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: #D97706;">
                    <i class="fas fa-pen-to-square text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Ujian Online</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">&nbsp;</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold text-amber-600"><i class="fas fa-circle text-[6px] align-middle mr-1"></i>Siap Dikerjakan</span>
                <span class="text-slate-400 truncate">Lihat daftar</span>
            </div>
        </div>

        {{-- Kelas --}}
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background: #0D9488;">
                    <i class="fas fa-user-graduate text-[13px]"></i>
                </div>
                <div class="text-right min-w-0">
                    <div class="text-[9.5px] font-extrabold tracking-[0.1em] text-slate-400 uppercase">Kelas Saya</div>
                    <div class="text-[26px] leading-tight font-extrabold text-slate-900 mt-0.5">{{ str_replace('Kelas ', '', $kelasSiswa) }}</div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#EFF2F7] flex items-center justify-between gap-2 text-[11px]">
                <span class="font-bold text-teal-600"><i class="fas fa-circle text-[6px] align-middle mr-1"></i>Aktif</span>
                <span class="text-slate-400 truncate">TA {{ now()->month >= 7 ? now()->year.'/'.(now()->year + 1) : (now()->year - 1).'/'.now()->year }}</span>
            </div>
        </div>
    </section>

    {{-- ================= SPLIT: TUGAS & MAPEL ================= --}}
    <section class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">

        {{-- ---------- TUGAS & PR ---------- --}}
        <div class="xl:col-span-8 card p-6">
            <div class="flex items-start justify-between gap-4 flex-wrap pb-4">
                <div>
                    <h3 class="text-[15px] font-extrabold text-slate-900">Tugas &amp; PR Mendatang</h3>
                    <p class="text-[11.5px] text-slate-400 mt-0.5">Tugas yang belum melewati tenggat, diurutkan dari yang terdekat</p>
                </div>
                <span class="inline-flex items-center gap-2 px-3 h-10 rounded-xl bg-[#F4F7FB] border border-[#E6EBF2] text-[11px] font-bold text-slate-600">
                    <i class="fas fa-calendar text-[10px] text-slate-400"></i> {{ $jumlahTugasAktif }} Tugas Aktif
                </span>
            </div>

            <div class="space-y-3">
                @forelse($tugasAktif as $item)
                    <div class="rounded-xl border border-[#E6EBF2] bg-white p-4 flex items-center gap-4 hover:border-slate-300 transition">
                        <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center shrink-0 bg-[#F4F7FB] text-slate-700 border border-[#E6EBF2]">
                            <i class="fas fa-book text-[15px]"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-[13.5px] font-extrabold text-slate-900 truncate">{{ $item['judul'] ?? '-' }}</h4>
                                <span class="px-2 py-0.5 rounded-md bg-[#EAF1F8] text-[9px] font-extrabold tracking-[0.08em] uppercase" style="color: var(--binaro-navy);">{{ $item['mapel'] ?? '-' }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-2 flex-wrap text-[11px] text-slate-500">
                                <span class="font-semibold text-slate-600"><i class="fas fa-clock text-[10px] mr-1"></i>Tenggat: {{ $item['deadline'] ?? '-' }}</span>
                            </div>
                        </div>

                        <a href="{{ route('siswa.notifikasi_pr.index') }}"
                           class="inline-flex items-center gap-1.5 px-4 h-10 rounded-xl text-[12px] font-bold text-white transition hover:opacity-90 shrink-0"
                           style="background: var(--binaro-navy);">
                            Buka <i class="fas fa-arrow-right text-[9px]"></i>
                        </a>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-[#E6EBF2] bg-[#FBFCFE] py-14 px-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mx-auto text-emerald-500">
                            <i class="fas fa-check"></i>
                        </div>
                        <h4 class="mt-3 text-[13px] font-bold text-slate-700">Tidak ada tugas mendatang</h4>
                        <p class="mt-1 text-[11.5px] text-slate-400">Semua tugas sudah lewat tenggat. Manfaatkan waktu untuk membaca materi.</p>
                        <a href="{{ route('siswa.mapel.index') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 h-10 rounded-xl text-[12px] font-bold text-white" style="background: var(--binaro-navy);">
                            <i class="fas fa-book-open text-[10px]"></i> Mulai Belajar
                        </a>
                    </div>
                @endforelse
            </div>

            @if($tugas_total ?? 0 > $jumlahTugasAktif)
                <div class="mt-4 pt-4 border-t border-[#EFF2F7] flex items-center justify-between gap-3 flex-wrap">
                    <span class="text-[11px] text-slate-400">
                        <i class="fas fa-clock-rotate-left text-slate-400 mr-1"></i>
                        {{ ($tugas_total ?? 0) - $jumlahTugasAktif }} tugas lainnya sudah lewat tenggat
                    </span>
                    <a href="{{ route('siswa.notifikasi_pr.index') }}" class="text-[11.5px] font-bold text-slate-500 hover:text-slate-800 transition">
                        Lihat semua tugas <i class="fas fa-arrow-right text-[9px] ml-1"></i>
                    </a>
                </div>
            @endif
        </div>

        {{-- ---------- MAPEL ---------- --}}
        <div class="xl:col-span-4 card p-6">
            <div class="flex items-start justify-between gap-3 pb-4">
                <div>
                    <h3 class="text-[15px] font-extrabold text-slate-900">Mata Pelajaran</h3>
                    <p class="text-[11.5px] text-slate-400 mt-0.5">Materi yang tersedia untukmu</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-sky-50 border border-sky-100 text-[10.5px] font-extrabold shrink-0" style="color: var(--binaro-navy);">
                    {{ $jumlahMapel }} Mapel
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse(array_slice($mapel ?? [], 0, 5) as $item)
                    <div class="rounded-xl border border-[#E6EBF2] bg-white p-3.5 hover:border-slate-300 transition">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-[12.5px] font-extrabold text-slate-900 truncate">{{ $item['nama'] ?? '-' }}</h4>
                                <p class="text-[10.5px] text-slate-400 mt-0.5 truncate">{{ $item['materi'] ?? '' }}</p>
                            </div>
                            <a href="{{ route('siswa.materi.index', $item['id_mapel']) }}"
                               class="shrink-0 inline-flex items-center px-3 h-10 rounded-xl text-[10.5px] font-extrabold text-white transition hover:opacity-90"
                               style="background: var(--binaro-navy);">
                                Buka
                            </a>
                        </div>
                        <div class="mt-2.5 flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[9.5px] font-extrabold">{{ $item['badge'] ?? 'Aktif' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-[#E6EBF2] bg-[#FBFCFE] py-10 px-5 text-center">
                        <div class="w-11 h-11 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-300">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4 class="mt-3 text-[12.5px] font-bold text-slate-700">Belum ada mata pelajaran</h4>
                        <p class="mt-1 text-[11px] text-slate-400">Hubungi guru atau admin sekolah.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 pt-4 border-t border-[#EFF2F7] space-y-2">
                <a href="{{ route('siswa.jadwal_mapel.index') }}"
                   class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-[#E6EBF2] bg-[#FBFCFE] hover:bg-slate-50 transition group">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-slate-700">
                        <i class="fas fa-calendar-days text-[12px]" style="color: var(--binaro-navy);"></i> Lihat Jadwal Pelajaran
                    </span>
                    <i class="fas fa-arrow-right text-[9px] text-slate-400 group-hover:text-slate-700 transition"></i>
                </a>
                <a href="{{ route('siswa.notifikasi_pr.index') }}"
                   class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-[#E6EBF2] bg-white hover:bg-slate-50 transition group">
                    <span class="flex items-center gap-2 text-[11.5px] font-bold text-slate-700">
                        <i class="fas fa-bell text-[12px] text-amber-500"></i> Semua Notifikasi PR
                    </span>
                    <i class="fas fa-arrow-right text-[9px] text-slate-400 group-hover:text-slate-700 transition"></i>
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
