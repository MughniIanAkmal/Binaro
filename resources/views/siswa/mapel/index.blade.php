@extends('layouts.siswa')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Mata Pelajaran Saya</h1>
        <p class="text-xs text-slate-500">Pilih mata pelajaran untuk mulai mengakses materi dan kuis</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($mapels as $mapel)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between hover:border-[#13527D]/50 transition">
            <div class="space-y-3">
                <div class="w-10 h-10 bg-[#13527D]/10 text-[#13527D] rounded-xl flex items-center justify-center font-bold text-sm">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ $mapel->nama_mapel }}</h2>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $mapel->deskripsi ?? 'Materi pelajaran SDN Kalitapen 01' }}</p>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] text-slate-400 font-medium">
                    <i class="fas fa-layer-group text-slate-400 mr-1"></i> {{ $mapel->bab_count ?? 0 }} Bab
                </span>
                <a href="{{ route('siswa.materi.index', $mapel->id_mapel) }}" class="px-3 py-1.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                    Buka Materi <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl p-8 border border-slate-200 text-center text-xs text-slate-400">
            Belum ada Mata Pelajaran yang tersedia.
        </div>
        @endforelse
    </div>
</div>
@endsection
