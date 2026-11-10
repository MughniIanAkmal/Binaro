@extends('layouts.guru')

@section('content')
<div class="space-y-6">

    <!-- 1. Teacher Welcome Banner: White Card with Deep Blue Accents -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#18537D] text-white flex items-center justify-center text-2xl shadow-sm shrink-0">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 leading-tight">
                    {{ $guru->nama_guru ?? session('user_name', 'Ibu Sarah Wijaya, S.Pd.') }}
                </h2>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 font-medium mt-1">
                    <span class="font-bold text-[#18537D]">Wali Kelas 4B</span>
                    <span>&bull;</span>
                    <span>SDN Harapan Jaya 01</span>
                    <span>&bull;</span>
                    <span class="text-slate-400">TH 2024/2025 Genap</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" onclick="openModalAction('tambah', 'materi')" 
                    class="bg-[#18537D] hover:bg-[#124264] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-plus text-xs"></i>
                <span>Buat Materi</span>
            </button>
            <a href="{{ route('guru.notifikasi_pr.create') }}" 
               class="border border-[#18537D] text-[#18537D] hover:bg-sky-50 text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2">
                <i class="fas fa-clipboard-check text-xs"></i>
                <span>Beri Tugas</span>
            </a>
        </div>
    </div>

    <!-- 2. Three KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- KPI 1: Mata Pelajaran -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#18537D] flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ $totalMapel ?? 4 }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Mata Pelajaran</div>
            </div>
            <a href="{{ route('guru.mapel.browse') }}" class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-[#18537D] hover:underline">
                <span>Lihat Kurikulum</span>
                <i class="fas fa-arrow-right text-[11px]"></i>
            </a>
        </div>

        <!-- KPI 2: Total Modul -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ $totalMateri ?? 38 }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Total Modul</div>
            </div>
            <a href="{{ route('guru.materi.index') }}" class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-[#18537D] hover:underline">
                <span>Buka Modul Pembelajaran</span>
                <i class="fas fa-arrow-right text-[11px]"></i>
            </a>
        </div>

        <!-- KPI 3: Pekerjaan Rumah -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-3">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight">{{ $totalPr ?? 3 }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Pekerjaan Rumah</div>
            </div>
            <a href="{{ route('guru.notifikasi_pr.index') }}" class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-700 hover:underline">
                <span>Periksa Tugas Siswa</span>
                <i class="fas fa-arrow-right text-[11px]"></i>
            </a>
        </div>
    </div>

    <!-- 3. Two Columns: Left 65% : Right 35% -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Section (8 of 12 cols = 66%) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Materi Pembelajaran -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-black text-base text-slate-900">Materi Pembelajaran</h3>
                        <span class="bg-sky-50 text-[#18537D] text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border border-sky-100">
                            38 Modul
                        </span>
                    </div>
                    <div class="relative">
                        <button type="button" class="border border-slate-200 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-xl hover:bg-slate-50 flex items-center gap-1.5 transition">
                            <span>Semua Mapel</span>
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- Modul 1: Matematika -->
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-100/70 transition flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#18537D] text-white flex items-center justify-center text-sm shadow-sm shrink-0">
                                <i class="fas fa-play"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-[#18537D]">Matematika</span>
                                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-1.5 py-0.2 rounded">Aktif</span>
                                </div>
                                <h4 class="font-extrabold text-sm text-slate-900 mt-0.5">Membandingkan Pecahan Senilai</h4>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('guru.mapel.browse') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
                                <i class="fas fa-play text-[10px]"></i> Mulai Ajar
                            </a>
                            <a href="{{ route('guru.mapel.browse') }}" class="border border-slate-200 hover:bg-white text-[#18537D] font-bold text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                <i class="far fa-eye text-xs"></i> Lihat
                            </a>
                            <button type="button" onclick="openModalAction('edit')" class="bg-[#18537D] hover:bg-[#124264] text-white font-bold text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                <i class="fas fa-pencil text-[10px]"></i> Edit
                            </button>
                        </div>
                    </div>

                    <!-- Modul 2: IPA -->
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-100/70 transition flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center text-sm shadow-sm shrink-0">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-emerald-700">IPA</span>
                                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-1.5 py-0.2 rounded">Aktif</span>
                                </div>
                                <h4 class="font-extrabold text-sm text-slate-900 mt-0.5">Siklus Air dan Kehidupan di Bumi</h4>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('guru.mapel.browse') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
                                <i class="fas fa-play text-[10px]"></i> Mulai Ajar
                            </a>
                            <a href="{{ route('guru.mapel.browse') }}" class="border border-slate-200 hover:bg-white text-[#18537D] font-bold text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                <i class="far fa-eye text-xs"></i> Lihat
                            </a>
                            <button type="button" onclick="openModalAction('edit')" class="bg-[#18537D] hover:bg-[#124264] text-white font-bold text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                <i class="fas fa-pencil text-[10px]"></i> Edit
                            </button>
                        </div>
                    </div>

                    <!-- Modul 3: IPA Ekosistem -->
                    <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-100/70 transition flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center text-sm shadow-sm shrink-0">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-emerald-700">IPA</span>
                                    <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-1.5 py-0.2 rounded">Aktif</span>
                                </div>
                                <h4 class="font-extrabold text-sm text-slate-900 mt-0.5">Siklus Air dan Kehidupan di Bumi</h4>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('guru.mapel.browse') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
                                <i class="fas fa-play text-[10px]"></i> Mulai Ajar
                            </a>
                            <a href="{{ route('guru.mapel.browse') }}" class="border border-slate-200 hover:bg-white text-[#18537D] font-bold text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                <i class="far fa-eye text-xs"></i> Lihat
                            </a>
                            <button type="button" onclick="openModalAction('edit')" class="bg-[#18537D] hover:bg-[#124264] text-white font-bold text-xs px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                <i class="fas fa-pencil text-[10px]"></i> Edit
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-2 text-right">
                    <a href="{{ route('guru.materi.index') }}" class="text-xs font-bold text-slate-500 hover:text-[#18537D] inline-flex items-center gap-1 transition">
                        <span>Lihat Semua Materi (38)</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- PR Berjalan -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                        <h3 class="font-black text-base text-slate-900">PR Berjalan</h3>
                    </div>
                    <span class="bg-amber-50 text-amber-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-amber-200">
                        3 Aktif
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- PR Card 1 -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-[#18537D]">Matematika 4B</span>
                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded">
                                Besok 17.00
                            </span>
                        </div>
                        <h4 class="font-extrabold text-xs text-slate-900">Latihan Soal Cerita Pecahan</h4>
                        
                        <div class="space-y-1">
                            <div class="flex justify-between text-[11px] text-slate-500 font-semibold">
                                <span>Pengumpulan:</span>
                                <span class="text-slate-800 font-bold">24 / 28 Siswa (85%)</span>
                            </div>
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-[#18537D] rounded-full" style="width: 85%"></div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-between items-center text-xs">
                            <span class="text-[11px] text-slate-400 font-medium">Belum Menyerahkan</span>
                            <a href="{{ route('guru.notifikasi_pr.index') }}" class="bg-[#18537D] hover:bg-[#124264] text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm">
                                Periksa (24)
                            </a>
                        </div>
                    </div>

                    <!-- PR Card 2 -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-sky-700">IPA 4B</span>
                            <span class="text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded">
                                Senin, 10 Des
                            </span>
                        </div>
                        <h4 class="font-extrabold text-xs text-slate-900">Mencatat Pengamatan Tanaman</h4>

                        <div class="space-y-1">
                            <div class="flex justify-between text-[11px] text-slate-500 font-semibold">
                                <span>Pengumpulan:</span>
                                <span class="text-slate-800 font-bold">12 / 28 Siswa (42%)</span>
                            </div>
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: 42%"></div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-between items-center text-xs">
                            <span class="text-[11px] text-slate-400 font-medium">Di Dalam Proses</span>
                            <a href="{{ route('guru.notifikasi_pr.index') }}" class="bg-sky-50 hover:bg-sky-100 text-[#18537D] border border-sky-200 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                Lihat Progres
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Section (4 of 12 cols = 34%) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Jadwal Hari Ini Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-600"></div>
                        <h3 class="font-black text-base text-slate-900">Jadwal Hari Ini</h3>
                    </div>
                    <span class="text-xs font-bold text-slate-400">
                        {{ now()->translatedFormat('l, d M') }}
                    </span>
                </div>

                <div class="space-y-4">
                    <!-- Slot 1: Selesai -->
                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">07.30 - 09.00</span>
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2 py-0.5 rounded">
                                Selesai
                            </span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Matematika (4B)</h4>
                    </div>

                    <!-- Slot 2: Berlangsung -->
                    <div class="p-3.5 rounded-xl border border-amber-200/90 bg-amber-50/40 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">09.15 - 10.45 &bull; Lab IPA</span>
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded flex items-center gap-1">
                                <i class="fas fa-star text-[9px] text-amber-600"></i> Berlangsung
                            </span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">IPA Eksperimen (4A)</h4>
                        
                        <a href="{{ route('guru.absen.index') }}" class="w-full mt-2 bg-[#18537D] hover:bg-[#124264] text-white text-xs font-bold py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm transition">
                            <i class="fas fa-chalkboard-user"></i>
                            <span>Masuk Kelas (Proyektor)</span>
                        </a>
                    </div>

                    <!-- Slot 3: Mendatang -->
                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/60 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500">11.00 - 11.45</span>
                            <span class="bg-slate-200 text-slate-700 text-[10px] font-extrabold px-2 py-0.5 rounded">
                                Mendatang
                            </span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Konsultasi Wali Murid</h4>
                    </div>
                </div>

                <div class="pt-2 text-center border-t border-slate-100">
                    <a href="{{ route('guru.jadwal.index') }}" class="text-xs font-bold text-[#18537D] hover:underline inline-flex items-center gap-1">
                        <span>Jadwal Mingguan</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
