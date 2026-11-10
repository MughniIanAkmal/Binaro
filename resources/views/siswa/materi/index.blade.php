@extends('layouts.siswa')

@section('page_title', $mapel->nama_mapel)
@section('page_subtitle', 'Pilih Bab dan Sub-Bab pembelajaran yang ingin dipelajari.')

@section('breadcrumb_trail')
    <a href="{{ route('siswa.mapel.index') }}" class="hover:text-slate-600 transition">Pembelajaran</a>
    <i class="fas fa-chevron-right text-[8px] opacity-60"></i>
    <span class="text-slate-500">{{ $mapel->nama_mapel }}</span>
@endsection

@section('header_actions')
    <a href="{{ route('siswa.mapel.index') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-[#E6EBF2] hover:bg-slate-50 transition">
        <i class="fas fa-arrow-left text-[11px]"></i> Kembali
    </a>
@endsection

@section('content')
<div class="space-y-5 max-w-4xl mx-auto">

    <!-- Bab & Sub-Bab List -->
    <div class="space-y-5">
        @forelse($babs as $bIdx => $bab)
        <div class="bg-white rounded-2xl shadow-sm border border-[#E6EBF2] overflow-hidden">
            <!-- Bab Header -->
            <div class="bg-slate-50/80 px-5 py-3.5 border-b border-[#E6EBF2] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-[#0E3A5C] text-white text-xs font-bold rounded-lg flex items-center justify-center shadow-sm">
                        {{ $bIdx + 1 }}
                    </span>
                    <h2 class="text-sm font-bold text-slate-900">{{ $bab->nama_bab }}</h2>
                </div>
                <span class="text-[11px] font-bold text-slate-500 bg-white px-2.5 py-1 rounded-full border border-[#E6EBF2]">
                    {{ $bab->subBab->count() }} Sub-Bab
                </span>
            </div>

            <!-- Sub-Bab Simple List -->
            <div class="p-4 space-y-2.5">
                @forelse($bab->subBab as $sIdx => $sub)
                <a href="{{ route('siswa.sub_bab.materi', $sub->id_sub_bab) }}"
                   class="flex items-center justify-between p-3.5 rounded-xl border border-[#E6EBF2] bg-white hover:border-[#0E3A5C] hover:bg-slate-50/70 hover:shadow-sm transition group">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-sky-50 text-[#0E3A5C] group-hover:bg-[#0E3A5C] group-hover:text-white font-bold text-xs flex items-center justify-center transition">
                            {{ $sIdx + 1 }}
                        </span>
                        <div>
                            <h3 class="text-xs font-bold text-slate-800 group-hover:text-[#0E3A5C] transition">
                                {{ $sub->nama_sub_bab }}
                            </h3>
                            <div class="flex items-center gap-3 text-[11px] text-slate-400 mt-0.5">
                                <span><i class="fas fa-layer-group text-slate-400 mr-1"></i> {{ $sub->materi->count() }} Konten Materi</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs font-bold text-[#0E3A5C] group-hover:translate-x-1 transition">
                        <span>Buka Materi</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </div>
                </a>
                @empty
                <div class="text-center py-4 text-xs text-slate-400 italic">
                    Belum ada Sub-Bab pada Bab ini.
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-[#E6EBF2] p-12 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 text-xl mb-3">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Bab</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Mata pelajaran ini belum memiliki materi belajar.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
