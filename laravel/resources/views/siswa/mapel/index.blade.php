@extends('layouts.siswa')

@section('header_actions')
    <a href="{{ route('siswa.ujian.index') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary"
       >
        <i class="fas fa-clipboard-question text-[11px]"></i> Ujian Online
    </a>
@endsection

@section('content')
<div class="space-y-5 max-w-5xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($mapels as $mapel)
        <div class="bg-white rounded-xl shadow-sm border border-line p-5 flex flex-col justify-between hover:border-primary/50 transition">
            <div class="space-y-3">
                <div class="w-10 h-10 bg-primary/10 text-primary rounded-xl flex items-center justify-center font-bold text-sm">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ $mapel->nama_mapel }}</h2>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $mapel->deskripsi ?? 'Materi pelajaran ' . config('binaro.nama_sekolah') }}</p>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-line flex items-center justify-between">
                <span class="text-[11px] text-slate-400 font-medium">
                    <i class="fas fa-layer-group text-slate-400 mr-1"></i> {{ $mapel->bab_count ?? 0 }} Bab
                </span>
                <a href="{{ route('siswa.materi.index', $mapel->id_mapel) }}" class="px-3 py-1.5 bg-primary hover:bg-primary-hover text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                    Buka Materi <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl p-4 border border-line">
            <x-empty-state icon="fa-book-open" title="Belum ada Mata Pelajaran" message="Mata pelajaran yang dibuat admin akan tampil di sini." />
        </div>
        @endforelse
    </div>
</div>
@endsection
