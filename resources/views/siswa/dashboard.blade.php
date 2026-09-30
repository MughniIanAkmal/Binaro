@extends('layouts.siswa')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header Welcome -->
    <div class="bg-[#13527D] rounded-2xl shadow-sm p-8 text-white relative overflow-hidden">
        <!-- Decorative pattern -->
        <div class="absolute top-0 right-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
            <i class="fas fa-graduation-cap text-9xl"></i>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-amber-400 text-amber-900 font-bold text-[10px] px-2.5 py-0.5 rounded-full shadow-sm">
                    <i class="fas fa-school mr-1"></i> {{ $siswa->kelas->pararel ?? 'Siswa SD' }} &bull; SDN Kalitapen 01
                </span>
            </div>
            <h1 class="text-2xl font-black tracking-tight mb-2">
                Halo, {{ $siswa->nm_siswa ?? session('user_name', 'Budi Santoso') }}!
            </h1>
            <p class="text-xs text-sky-100 max-w-lg">Selamat datang di portal belajar Binaro. Pilih mata pelajaran di menu untuk mulai membaca materi dan mengerjakan kuis.</p>
        </div>
    </div>

    <!-- Quick Access / Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('siswa.mapel.index') }}" class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-[#13527D]/50 transition flex items-center gap-4 group">
            <div class="w-12 h-12 bg-sky-50 text-[#13527D] rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <h3 class="font-bold text-sm text-slate-900">Mulai Belajar</h3>
                <p class="text-[11px] text-slate-500">Akses materi mapel</p>
            </div>
        </a>

        <a href="{{ route('siswa.ujian.index') }}" class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-[#13527D]/50 transition flex items-center gap-4 group">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-sm text-slate-900">Ujian & Kuis</h3>
                <p class="text-[11px] text-slate-500">Akses ujian online</p>
            </div>
        </a>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <h3 class="font-bold text-sm text-slate-900">Jadwal Kelas</h3>
                <p class="text-[11px] text-slate-500">Lihat jadwal harian</p>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-sm text-slate-900 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-[#13527D]"></i> Informasi Akademik
        </h3>
        <div class="text-xs text-slate-600 bg-slate-50 p-4 rounded-lg border border-slate-100">
            <p>Sistem E-Learning SDN Kalitapen 01 (Binaro) telah diperbarui. Kini Anda dapat mengakses materi berupa Video Pembelajaran, Dokumen PDF, dan Kuis Interaktif langsung dari satu pintu.</p>
            <p class="mt-2 text-slate-400 font-medium">Navigasi ke menu "Pembelajaran & Kuis" di sidebar untuk mulai.</p>
        </div>
    </div>
</div>
@endsection
