@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Welcome Banner -->
    <div class="bg-primary rounded-2xl shadow-sm p-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
            <i class="fas fa-shield-halved text-9xl"></i>
        </div>

        <div class="relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-amber-400 text-amber-900 font-bold text-[10px] px-2.5 py-0.5 rounded-full shadow-sm">
                        <i class="fas fa-lock mr-1"></i> Panel Administrator &bull; {{ config('binaro.nama_sekolah') }}
                    </span>
                    <span class="text-xs text-sky-200">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                </div>
                <h1 class="text-2xl font-black tracking-tight">
                    Selamat Datang, {{ $admin->nama_admin ?? session('user_name', 'Administrator') }}!
                </h1>
                <p class="text-xs text-sky-100 max-w-xl mt-1">
                    Kelola data akademik, akun guru & siswa, silabus mata pelajaran, serta pantau rekapitulasi kehadiran sekolah dalam satu dasbor terpadu.
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
            <p class="text-xs text-slate-500">Akses cepat ke modul data induk dan administrasi sekolah.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
            <a href="{{ route('guru.data.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-primary group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-user-tie"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Akun Guru</span>
            </a>

            <a href="{{ route('siswa.data.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Akun Siswa</span>
            </a>

            <a href="{{ route('jadwal.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-fuchsia-50 text-fuchsia-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-calendar-days"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Jadwal</span>
            </a>

            <a href="{{ route('mapel.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-book-open"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Kelola Mapel</span>
            </a>

            <a href="{{ route('admin.qr_absensi.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-qrcode"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">QR &amp; Absensi</span>
            </a>

            <a href="{{ route('absensi.index') }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Kelola Absensi</span>
            </a>

            <a href="{{ route('admin.qr_absensi.index', ['open_settings' => 1]) }}" class="py-5 px-3 rounded-2xl border border-slate-200 hover:border-primary hover:shadow-md transition text-center flex flex-col items-center gap-3 group">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 group-hover:scale-105 transition flex items-center justify-center text-lg">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-xs font-bold text-slate-700 leading-tight">Jam Absen</span>
            </a>
        </div>
    </div>

    <!-- Rangkuman Absensi Hari Ini -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-primary flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Rangkuman Absensi Hari Ini</h3>
                    <p class="text-xs text-slate-500">Rekap kehadiran siswa pada hari {{ now()->locale('id')->translatedFormat('l, d F Y') }}.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('absensi.rekap') }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fas fa-table-list text-xs"></i>
                    <span>Lihat Rekap</span>
                </a>
                <a href="{{ route('absensi.index') }}" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fas fa-sliders text-xs"></i>
                    <span>Kelola Absensi</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider">Hadir</span>
                    <i class="fas fa-user-check text-emerald-500 text-xs"></i>
                </div>
                <div class="text-2xl font-black text-emerald-900 mt-1">
                    {{ $stats['hadir_hari_ini'] }} <span class="text-xs font-bold text-emerald-600">/ {{ $stats['total_siswa'] }} siswa</span>
                </div>
                <p class="text-[11px] text-emerald-700 font-medium mt-0.5">Tingkat kehadiran {{ $stats['persentase_hadir'] }}%</p>
            </div>

            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black text-amber-700 uppercase tracking-wider">Izin & Sakit</span>
                    <i class="fas fa-file-medical text-amber-500 text-xs"></i>
                </div>
                <div class="text-2xl font-black text-amber-900 mt-1">
                    {{ $stats['izin_sakit_hari_ini'] }} <span class="text-xs font-bold text-amber-600">siswa</span>
                </div>
                <p class="text-[11px] text-amber-700 font-medium mt-0.5">Dengan surat keterangan</p>
            </div>

            <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200/80">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black text-rose-700 uppercase tracking-wider">Alpa</span>
                    <i class="fas fa-user-times text-rose-500 text-xs"></i>
                </div>
                <div class="text-2xl font-black text-rose-900 mt-1">
                    {{ $stats['alpa_hari_ini'] }} <span class="text-xs font-bold text-rose-600">siswa</span>
                </div>
                <p class="text-[11px] text-rose-700 font-medium mt-0.5">Belum melakukan presensi</p>
            </div>
        </div>
    </div>

    <!-- Dual Column Recent Overview: Guru & Siswa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Guru -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-primary flex items-center justify-center text-xs font-bold">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Daftar Guru Terdaftar</h4>
                        <p class="text-[10px] text-slate-400">Tenaga pengajar aktif di {{ config('binaro.nama_sekolah') }}</p>
                    </div>
                </div>
                <a href="{{ route('guru.data.index') }}" class="text-[11px] font-bold text-primary hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentGuru as $g)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">{{ $g->nama_guru }}</p>
                        <p class="text-[10px] text-slate-400">NIP: {{ $g->nip ?? '-' }} &bull; {{ $g->no_hp ?? '-' }}</p>
                    </div>
                    <a href="{{ route('guru.data.edit', $g->id_guru) }}" class="p-1.5 text-slate-400 hover:text-primary rounded-lg">
                        <i class="fas fa-pen text-[10px]"></i>
                    </a>
                </div>
                @empty
                <x-empty-state icon="fa-user-tie" title="Belum ada data guru." message="Akun guru yang dibuat akan tampil di sini." />
                @endforelse
            </div>
        </div>

        <!-- Recent Siswa -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Daftar Siswa Terdaftar</h4>
                        <p class="text-[10px] text-slate-400">Peserta didik aktif</p>
                    </div>
                </div>
                <a href="{{ route('siswa.data.index') }}" class="text-[11px] font-bold text-primary hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentSiswa as $s)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">{{ $s->nm_siswa }}</p>
                        <p class="text-[10px] text-slate-400">NISN: {{ $s->nisn }} &bull; {{ $s->kelas?->pararel ?? 'Kelas Aktif' }}</p>
                    </div>
                    <a href="{{ route('siswa.data.edit', $s->id_siswa) }}" class="p-1.5 text-slate-400 hover:text-primary rounded-lg">
                        <i class="fas fa-pen text-[10px]"></i>
                    </a>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic py-3 text-center">Belum ada data siswa.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
