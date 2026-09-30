@extends('layouts.guru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">
                E-Learning &bull; SDN Kalitapen 01
            </div>
            <h1 class="text-xl font-bold text-slate-900">Mata Pelajaran & Materi Belajar</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pilih mata pelajaran untuk menjelajahi hierarki Bab, Sub-Bab, dan Materi Pembelajaran.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openModalAction('tambah', 'bab')" class="px-3.5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Bab Baru
            </button>
        </div>
    </div>

    @if(session('error'))
    <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
        <i class="fas fa-exclamation-circle text-rose-500"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(session('success'))
    <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle text-emerald-500"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Grid Card Mata Pelajaran -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($mapels as $mapel)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 hover:border-[#13527D] hover:shadow-md transition group p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-[#13527D]/10 text-[#13527D] group-hover:bg-[#13527D] group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                        <i class="fas fa-book-bookmark"></i>
                    </div>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 group-hover:bg-[#13527D]/10 group-hover:text-[#13527D] rounded-full text-[11px] font-bold transition">
                        {{ $mapel->bab_count ?? $mapel->bab()->count() }} Bab
                    </span>
                </div>

                <h3 class="text-base font-bold text-slate-900 group-hover:text-[#13527D] transition">
                    {{ $mapel->nama_mapel }}
                </h3>
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                    {{ $mapel->deskripsi ?? 'Kurikulum pembelajaran terintegrasi SDN Kalitapen 01.' }}
                </p>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] text-slate-400 font-medium">
                    <i class="fas fa-layer-group text-slate-400 mr-1"></i> Struktur Materi
                </span>
                <a href="{{ route('guru.bab.index', $mapel->id_mapel) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#13527D] group-hover:translate-x-0.5 transition">
                    Buka Bab <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 text-2xl mb-3">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Mata Pelajaran</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Silakan tambahkan data mata pelajaran terlebih dahulu di menu Mapel & Kustomisasi.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
