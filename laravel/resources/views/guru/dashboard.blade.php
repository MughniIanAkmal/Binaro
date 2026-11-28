@extends('layouts.guru')

@section('page_title', 'Dashboard Guru')

@section('content')
<div class="space-y-6">
    <!-- Header Welcome Banner -->
    <div class="bg-primary rounded-2xl shadow-sm p-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
            <i class="fas fa-chalkboard-user text-9xl"></i>
        </div>

        <div class="relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-emerald-400 text-emerald-900 font-bold text-[10px] px-2.5 py-0.5 rounded-full shadow-sm">
                        <i class="fas fa-chalkboard mr-1"></i> Portal Guru &bull; {{ config('binaro.nama_sekolah') }}
                    </span>
                    <span class="text-xs text-sky-200">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                </div>
                <h1 class="text-2xl font-black tracking-tight">
                    Selamat Datang, {{ $guru->nama_guru ?? session('user_name', 'Guru') }}!
                </h1>
                <p class="text-xs text-sky-100 max-w-xl mt-1">
                    NIP: {{ $guru->nip ?? '-' }} — Kelola materi pembelajaran, PR, ujian, dan pantau kehadiran siswa dalam satu dasbor terpadu.
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Action Shortcut Hub -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
        <div>
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-bolt text-amber-500"></i> Pintasan Akses Utama
            </h3>
            <p class="text-xs text-slate-500">Akses cepat ke modul pembelajaran dan administrasi kelas.</p>
        </div>

        {{-- Urutan pintasan mengikuti urutan fitur di sidebar guru. --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="{{ route('guru.mapel.browse') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-primary group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-book-bookmark"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Mata Pelajaran</span>
            </a>

            <a href="{{ route('guru.jadwal.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-fuchsia-50 text-fuchsia-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-calendar-days"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Jadwal Mengajar</span>
            </a>

            <a href="{{ route('guru.notifikasi_pr.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-tasks"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Kelola PR</span>
            </a>

            <a href="{{ route('guru.absen.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-qrcode"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Absensi Siswa</span>
            </a>

            <a href="{{ route('guru.ujian.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-file-circle-question"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Ujian Online</span>
            </a>

            <a href="{{ route('rekap_nilai.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Rekap Nilai</span>
            </a>
        </div>
    </div>

    @if(($prAktif ?? 0) > 0)
    <!-- PR Aktif Banner -->
    <a href="{{ route('guru.notifikasi_pr.index') }}"
       class="flex items-center gap-3.5 bg-amber-50 border border-amber-200/90 rounded-2xl px-5 py-4 hover:bg-amber-100/80 transition group">
        <div class="w-10 h-10 rounded-xl bg-amber-400 text-amber-900 flex items-center justify-center text-base font-bold shrink-0">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-amber-900">
                {{ $prAktif }} PR masih berjalan
            </p>
            <p class="text-[11px] text-amber-700 mt-0.5">
                Jangan lupa pantau pengumpulan tugas siswa sebelum tenggat waktu.
            </p>
        </div>
        <span class="text-[11px] font-bold text-amber-900 inline-flex items-center gap-1 shrink-0 group-hover:translate-x-0.5 transition">
            Lihat Detail <i class="fas fa-arrow-right text-[10px]"></i>
        </span>
    </a>
    @endif

    <!-- Dual Column Recent Overview: Jadwal & PR -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Jadwal Hari Ini -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-primary flex items-center justify-center text-xs font-bold">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Jadwal Mengajar Hari Ini</h4>
                        <p class="text-[10px] text-slate-400">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <a href="{{ route('guru.jadwal.index') }}" class="text-[11px] font-bold text-primary hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($jadwalHariIni as $jadwal)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">{{ $mapelById[$jadwal->id_mapel] ?? 'Mata Pelajaran' }}</p>
                        <p class="text-[10px] text-slate-400">{{ $jadwal->jam }} &bull; {{ $jadwal->hari }}</p>
                    </div>
                    <a href="{{ route('guru.absen.index') }}" class="px-2.5 py-1 text-[10px] font-bold bg-primary text-white rounded-lg hover:opacity-90 transition">
                        Absen
                    </a>
                </div>
                @empty
                <x-empty-state icon="fa-calendar-day" title="Tidak ada jadwal hari ini." message="Silakan cek kembali jadwal mengajarmu." />
                @endforelse
            </div>
        </div>

        <!-- PR Terbaru -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">PR Terbaru</h4>
                        <p class="text-[10px] text-slate-400">Daftar pekerjaan rumah aktif</p>
                    </div>
                </div>
                <a href="{{ route('guru.notifikasi_pr.index') }}" class="text-[11px] font-bold text-primary hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($prs as $pr)
                <div class="py-2.5 flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-800 truncate">{{ $pr->nama_pr ?? $pr->judul ?? 'Tugas PR' }}</p>
                        <p class="text-[10px] text-slate-400">
                            {{ $pr->mataPelajaran?->nama_mapel ?? 'PR' }} &bull;
                            {{ $pr->tgl_tenggat ? \Carbon\Carbon::parse($pr->tgl_tenggat)->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <span class="ml-2 px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-700 rounded-full">
                        Aktif
                    </span>
                </div>
                @empty
                <x-empty-state icon="fa-tasks" title="Belum ada PR." message="Buat PR baru untuk siswamu." />
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
