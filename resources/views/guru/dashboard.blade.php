@extends('layouts.guru')

@section('content')
<!-- Profile Banner -->
<div class="bg-gradient-to-r from-[#0E385D] to-[#165B96] rounded-xl p-6 text-white shadow-md flex justify-between items-center flex-wrap gap-4">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-white/20 border-2 border-white/40 flex items-center justify-center font-black text-xl text-white">
            {{ strtoupper(substr($guru->nama_guru ?? session('user_name', 'Ibu Sarah Wijaya'), 0, 2)) }}
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-black">{{ $guru->nama_guru ?? session('user_name', 'Ibu Sarah Wijaya, S.Pd.') }}</h2>
                <span class="bg-amber-400 text-slate-900 text-[10px] font-extrabold px-2 py-0.5 rounded-md">Wali Kelas 4B</span>
            </div>
            <p class="text-xs text-sky-100 mt-1">SDN Kalitapen 01 &bull; NIP. {{ $guru->nip ?? '198503152010012003' }}</p>
        </div>
    </div>
    <div class="flex gap-2">
        <button class="bg-white/10 hover:bg-white/20 text-white border border-white/30 text-xs font-bold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
            <i class="fas fa-plus text-xs"></i> Buat Materi
        </button>
        <button class="bg-[#F59E0B] hover:bg-amber-600 text-slate-900 font-bold text-xs px-4 py-2 rounded-lg transition flex items-center gap-1.5 shadow">
            <i class="fas fa-tasks text-xs"></i> Beri Tugas
        </button>
    </div>
</div>

<!-- 3 KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white p-5 rounded-xl border border-[#E2E8F0] shadow-sm flex justify-between items-center">
        <div>
            <div class="text-3xl font-black text-slate-900">4</div>
            <div class="text-xs font-bold text-slate-600 mt-1">Mata Pelajaran</div>
            <div class="text-[11px] text-slate-400">Kelas 4B &bull; Semester Ganjil</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#165B96] flex items-center justify-center text-xl font-bold">
            <i class="fas fa-book-open"></i>
        </div>
    </div>

    <div class="bg-white p-5 rounded-xl border border-[#E2E8F0] shadow-sm flex justify-between items-center">
        <div>
            <div class="text-3xl font-black text-slate-900">38</div>
            <div class="text-xs font-bold text-slate-600 mt-1">Total Modul</div>
            <div class="text-[11px] text-emerald-600 font-semibold">32 Terverifikasi</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#10B981] flex items-center justify-center text-xl font-bold">
            <i class="fas fa-layer-group"></i>
        </div>
    </div>

    <div class="bg-white p-5 rounded-xl border border-[#E2E8F0] shadow-sm flex justify-between items-center">
        <div>
            <div class="text-3xl font-black text-slate-900">3</div>
            <div class="text-xs font-bold text-slate-600 mt-1">Pekerjaan Rumah (PR)</div>
            <div class="text-[11px] text-amber-600 font-semibold">2 Aktif Berjalan</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-[#F59E0B] flex items-center justify-center text-xl font-bold">
            <i class="fas fa-pencil-alt"></i>
        </div>
    </div>
</div>

<!-- Content Split 65% : 35% -->
<div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
    <!-- Left Section (65%) -->
    <div class="lg:col-span-6 bg-white rounded-xl border border-[#E2E8F0] p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Materi Pembelajaran</h3>
                <p class="text-xs text-slate-500">Daftar modul aktif semester ini</p>
            </div>
            <button class="text-xs font-bold text-[#165B96] hover:underline">+ Tambah Bab</button>
        </div>

        <div class="space-y-3">
            <!-- Item 1: Matematika -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100/80 transition flex justify-between items-center flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-sky-100 text-[#165B96] flex items-center justify-center text-base font-bold">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">Matematika - Pecahan & Desimal</h4>
                        <div class="flex gap-2 items-center text-[11px] text-slate-500 mt-0.5">
                            <span class="font-semibold text-slate-700">Bab 3</span> &bull; 8 Sub-Bab &bull; Kelas 4B
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button class="bg-[#165B96] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-sky-800 transition">
                        Mulai Ajar
                    </button>
                    <button class="border border-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-white transition">
                        Lihat
                    </button>
                    <button class="border border-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-white transition">
                        Edit
                    </button>
                </div>
            </div>

            <!-- Item 2: IPA -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100/80 transition flex justify-between items-center flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-base font-bold">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">IPA - Fotosintesis & Ekosistem</h4>
                        <div class="flex gap-2 items-center text-[11px] text-slate-500 mt-0.5">
                            <span class="font-semibold text-slate-700">Bab 2</span> &bull; 5 Sub-Bab &bull; Kelas 4B
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button class="bg-[#165B96] text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-sky-800 transition">
                        Mulai Ajar
                    </button>
                    <button class="border border-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-white transition">
                        Lihat
                    </button>
                    <button class="border border-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-white transition">
                        Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Section (35%) -->
    <div class="lg:col-span-4 bg-white rounded-xl border border-[#E2E8F0] p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-base text-slate-900">Jadwal Hari Ini</h3>
            <span class="text-xs text-slate-400 font-medium">Hari Ini</span>
        </div>

        <div class="space-y-4 relative before:absolute before:inset-0 before:left-3 before:w-0.5 before:bg-slate-200">
            <!-- Time Slot 1 -->
            <div class="relative pl-7 space-y-1">
                <div class="absolute left-1.5 top-1.5 w-3 h-3 rounded-full bg-[#10B981] ring-4 ring-emerald-50"></div>
                <div class="flex justify-between items-start">
                    <span class="text-xs font-bold text-slate-900">07:30 - 09:00</span>
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2 py-0.5 rounded">Selesai</span>
                </div>
                <div class="font-bold text-xs text-slate-800">Matematika - Kelas 4B</div>
                <div class="text-[11px] text-slate-500">Ruang 4B &bull; 32 Siswa Hadir</div>
            </div>

            <!-- Time Slot 2 -->
            <div class="relative pl-7 space-y-1">
                <div class="absolute left-1.5 top-1.5 w-3 h-3 rounded-full bg-[#165B96] ring-4 ring-sky-50 animate-pulse"></div>
                <div class="flex justify-between items-start">
                    <span class="text-xs font-bold text-slate-900">09:30 - 11:00</span>
                    <span class="bg-sky-100 text-[#165B96] text-[10px] font-extrabold px-2 py-0.5 rounded">Berlangsung</span>
                </div>
                <div class="font-bold text-xs text-slate-800">IPA - Kelas 4A</div>
                <div class="text-[11px] text-slate-500">Lab IPA SD &bull; 30 Siswa</div>
                <button class="w-full mt-2 bg-[#165B96] text-white text-xs font-bold py-1.5 rounded-lg hover:bg-sky-800 transition shadow-sm">
                    <i class="fas fa-door-open mr-1"></i> Masuk Kelas
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: PR Berjalan -->
<div class="bg-white rounded-xl border border-[#E2E8F0] p-6 shadow-sm space-y-4">
    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
        <div>
            <h3 class="font-extrabold text-base text-slate-900">PR Berjalan</h3>
            <p class="text-xs text-slate-500">Status pengumpulan tugas siswa</p>
        </div>
        <button class="text-xs font-bold text-[#165B96] hover:underline">Kelola Semua PR</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- PR Card 1 -->
        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-3">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold uppercase text-sky-700 bg-sky-100 px-2 py-0.5 rounded">Matematika</span>
                    <h4 class="font-bold text-xs text-slate-900 mt-1">Latihan Soal Cerita Pecahan</h4>
                </div>
                <span class="text-xs font-extrabold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">Tenggat: Besok</span>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs text-slate-600 font-semibold">
                    <span>Pengumpulan</span>
                    <span class="text-[#10B981] font-bold">27 / 32 Siswa (85%)</span>
                </div>
                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full bg-[#10B981] rounded-full" style="width: 85%"></div>
                </div>
            </div>
        </div>

        <!-- PR Card 2 -->
        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-3">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold uppercase text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">IPA</span>
                    <h4 class="font-bold text-xs text-slate-900 mt-1">Mencatat Pengamatan Tanaman</h4>
                </div>
                <span class="text-xs font-extrabold text-slate-600 bg-slate-200 px-2 py-0.5 rounded">Tenggat: Senin</span>
            </div>
            <div class="space-y-1">
                <div class="flex justify-between text-xs text-slate-600 font-semibold">
                    <span>Pengumpulan</span>
                    <span class="text-[#F59E0B] font-bold">13 / 31 Siswa (42%)</span>
                </div>
                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full bg-[#F59E0B] rounded-full" style="width: 42%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
