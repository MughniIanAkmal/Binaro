@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Header Welcome Banner -->
    <div class="bg-[#13527D] rounded-2xl shadow-sm p-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
            <i class="fas fa-shield-halved text-9xl"></i>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-amber-400 text-amber-900 font-bold text-[10px] px-2.5 py-0.5 rounded-full shadow-sm">
                        <i class="fas fa-lock mr-1"></i> Panel Administrator &bull; SDN Kalitapen 01
                    </span>
                    <span class="text-xs text-sky-200">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>
                <h1 class="text-2xl font-black tracking-tight">
                    Selamat Datang, {{ $admin->nama_admin ?? session('user_name', 'Administrator') }}!
                </h1>
                <p class="text-xs text-sky-100 max-w-xl mt-1">
                    Kelola data akademik, akun guru & siswa, silabus mata pelajaran, serta pantau rekapitulasi kehadiran sekolah dalam satu dasbor terpadu.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start md:self-center">
                <a href="{{ route('jadwal.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-xs font-bold text-white transition flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i> Jadwal Pelajaran
                </a>
                <a href="{{ route('admin.qr.index') }}" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 rounded-xl text-xs font-bold text-white transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-qrcode"></i> QR Siswa
                </a>
            </div>
        </div>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Siswa -->
        <a href="{{ route('siswa.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#13527D]/60 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Siswa</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_siswa'] }}</div>
                    <span class="text-[11px] text-[#13527D] font-semibold mt-1 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition">
                        Kelola Siswa <i class="fas fa-arrow-right text-[9px]"></i>
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#13527D] group-hover:bg-[#13527D] group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </a>

        <!-- Guru -->
        <a href="{{ route('guru.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#13527D]/60 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Guru</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_guru'] }}</div>
                    <span class="text-[11px] text-[#13527D] font-semibold mt-1 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition">
                        Kelola Guru <i class="fas fa-arrow-right text-[9px]"></i>
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
            </div>
        </a>

        <!-- Mapel -->
        <a href="{{ route('mapel.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#13527D]/60 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Mata Pelajaran</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_mapel'] }}</div>
                    <span class="text-[11px] text-[#13527D] font-semibold mt-1 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition">
                        Kelola Mapel <i class="fas fa-arrow-right text-[9px]"></i>
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                    <i class="fas fa-book-bookmark"></i>
                </div>
            </div>
        </a>

        <!-- Jadwal Pelajaran -->
        <a href="{{ route('jadwal.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#13527D]/60 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Jadwal Pelajaran</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_jadwal'] ?? $stats['total_kelas'] }}</div>
                    <span class="text-[11px] text-[#13527D] font-semibold mt-1 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition">
                        Kelola Jadwal <i class="fas fa-arrow-right text-[9px]"></i>
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Quick Action Shortcut Hub -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-bolt text-amber-500"></i> Pintasan Akses Utama
                </h3>
                <p class="text-xs text-slate-500">Akses cepat ke modul data induk dan administrasi sekolah.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('guru.create') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-[#13527D] hover:bg-slate-50 transition text-center flex flex-col items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#13527D] group-hover:scale-105 transition flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-user-plus"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Tambah Guru</span>
            </a>

            <a href="{{ route('siswa.create') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-[#13527D] hover:bg-slate-50 transition text-center flex flex-col items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-105 transition flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Tambah Siswa</span>
            </a>

            <a href="{{ route('mapel.index') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-[#13527D] hover:bg-slate-50 transition text-center flex flex-col items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 group-hover:scale-105 transition flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-book-open"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Kelola Mapel</span>
            </a>

            <a href="{{ route('rpp.index') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-[#13527D] hover:bg-slate-50 transition text-center flex flex-col items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 group-hover:scale-105 transition flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-file-lines"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Verifikasi RPP</span>
            </a>

            <a href="{{ route('jadwal.index') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-[#13527D] hover:bg-slate-50 transition text-center flex flex-col items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 group-hover:scale-105 transition flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Jadwal Pelajaran</span>
            </a>

            <a href="{{ route('admin.qr.index') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-[#13527D] hover:bg-slate-50 transition text-center flex flex-col items-center gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 group-hover:scale-105 transition flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-qrcode"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Barcode Siswa</span>
            </a>
        </div>
    </div>

    <!-- Dual Column Recent Overview: Guru & Siswa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Guru -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-[#13527D] flex items-center justify-center text-xs font-bold">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Daftar Guru Terdaftar</h4>
                        <p class="text-[10px] text-slate-400">Tenaga pengajar aktif di SDN Kalitapen 01</p>
                    </div>
                </div>
                <a href="{{ route('guru.index') }}" class="text-[11px] font-bold text-[#13527D] hover:underline">
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
                    <a href="{{ route('guru.edit', $g->id_guru) }}" class="p-1.5 text-slate-400 hover:text-[#13527D] rounded-lg">
                        <i class="fas fa-pen text-[10px]"></i>
                    </a>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic py-3 text-center">Belum ada data guru.</p>
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
                <a href="{{ route('siswa.index') }}" class="text-[11px] font-bold text-[#13527D] hover:underline">
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
                    <a href="{{ route('siswa.edit', $s->id_siswa) }}" class="p-1.5 text-slate-400 hover:text-[#13527D] rounded-lg">
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
