@extends('layouts.siswa')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header Welcome (sama gaya dengan halaman siswa lain) -->
    <div class="bg-[#13527D] rounded-2xl shadow-sm p-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
            <i class="fas fa-graduation-cap text-9xl"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-amber-400 text-amber-900 font-bold text-[10px] px-2.5 py-0.5 rounded-full shadow-sm">
                    <i class="fas fa-school mr-1"></i> {{ $kelas ?? 'Siswa' }} &bull; {{ $sekolah ?? 'SDN Kalitapen 01' }}
                </span>
                <a href="{{ route('siswa.notifikasi_pr.index') }}" class="bg-white/15 hover:bg-white/25 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full transition" title="Notifikasi PR">
                    <i class="fas fa-bell mr-1"></i> Notifikasi
                </a>
            </div>
            <h1 class="text-2xl font-black tracking-tight mb-2">
                Halo, {{ $nama ?? session('user_name', 'Siswa') }}!
            </h1>
            <p class="text-xs text-sky-100 max-w-lg">Selamat datang di portal belajar Binaro. Pilih mata pelajaran di menu untuk mulai membaca materi dan mengerjakan kuis.</p>
        </div>
    </div>

    <!-- Menu Cepat -->
    <div>
        <h2 class="text-sm font-bold text-slate-900 mb-3">Menu</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('siswa.mapel.index') }}" class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-[#13527D]/50 transition flex items-center gap-4 group">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Mapel</h3>
                    <p class="text-[11px] text-slate-500">Materi pembelajaran</p>
                </div>
            </a>

            <a href="{{ route('siswa.ujian.index') }}" class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-[#13527D]/50 transition flex items-center gap-4 group">
                <div class="w-12 h-12 bg-sky-50 text-[#13527D] rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-clipboard-question"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Ujian</h3>
                    <p class="text-[11px] text-slate-500">Ujian online</p>
                </div>
            </a>

            <a href="{{ route('siswa.jadwal_mapel.index') }}" class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 hover:border-[#13527D]/50 transition flex items-center gap-4 group">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Jadwal</h3>
                    <p class="text-[11px] text-slate-500">Jadwal pelajaran</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Tugas & PR Mendatang -->
    <div>
        <h2 class="text-sm font-bold text-slate-900 mb-3">Tugas &amp; PR Mendatang</h2>
        <div class="space-y-3">
            @forelse ($tugas ?? [] as $item)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#13527D]/10 text-[#13527D] flex items-center justify-center">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[10px] font-extrabold uppercase text-[#13527D]">{{ $item['mapel'] ?? '-' }}</div>
                            <div class="text-sm font-bold text-slate-900 truncate">{{ $item['judul'] ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="px-4 py-2.5 flex items-center justify-between {{ ($item['warna'] ?? 'blue') === 'orange' ? 'bg-amber-500' : 'bg-[#13527D]' }} text-white">
                        <span class="text-[11px] font-semibold">
                            <i class="fas fa-clock mr-1"></i> Tenggat: {{ $item['deadline'] ?? '-' }}
                        </span>
                        <a href="{{ route('siswa.notifikasi_pr.index') }}" class="bg-white text-[#13527D] rounded-full px-3 py-1 text-[11px] font-extrabold">
                            Buka ›
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl p-6 border border-slate-200 text-center text-xs text-slate-400">
                    Belum ada tugas mendatang.
                </div>
            @endforelse
        </div>
        <div class="mt-3">
            <a href="{{ route('siswa.notifikasi_pr.index') }}" class="text-xs font-bold text-[#13527D] hover:underline">
                Lihat Semua Tugas ({{ $tugas_total ?? 0 }}) →
            </a>
        </div>
    </div>

    <!-- Mata Pelajaran -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-slate-900">Mata Pelajaran</h2>
            <a href="{{ route('siswa.mapel.index') }}" class="text-xs font-bold text-[#13527D] hover:underline">Semua</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($mapel ?? [] as $item)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ ($item['warna'] ?? 'blue') === 'orange' ? 'bg-amber-50 text-amber-600' : 'bg-[#13527D]/10 text-[#13527D]' }}">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ ($item['warna'] ?? 'blue') === 'orange' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-[#13527D]' }}">
                            {{ $item['badge'] ?? 'Aktif' }}
                        </span>
                    </div>
                    <div class="text-sm font-bold text-slate-900">{{ $item['nama'] ?? '-' }}</div>
                    <div class="text-[11px] text-slate-500 mb-3">{{ $item['materi'] ?? '' }}</div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ ($item['warna'] ?? 'blue') === 'orange' ? 'bg-amber-500' : 'bg-[#13527D]' }}" style="width: {{ $item['progress'] ?? 0 }}%;"></div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl p-6 border border-slate-200 text-center text-xs text-slate-400">
                    Belum ada mata pelajaran.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
