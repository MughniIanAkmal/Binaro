@extends('layouts.guru')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Navigation -->
    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('guru.mapel.browse') }}" class="hover:text-[#13527D] transition">Mata Pelajaran</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-800 font-bold">{{ $mapel->nama_mapel }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900">{{ $mapel->nama_mapel }}</h1>
                    <p class="text-xs text-slate-500">Daftar Bab & Silabus Pembelajaran</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.mapel.browse') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="openModalAction('tambah', 'bab', { idMapel: {{ $mapel->id_mapel }} })" class="px-3.5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Bab di Mapel Ini
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

    <!-- Daftar Bab List -->
    <div class="space-y-4">
        @forelse($mapel->bab as $index => $bab)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold flex items-center justify-center shrink-0">
                        {{ $index + 1 }}
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $bab->nama_bab }}</h3>
                        <div class="flex items-center gap-3 mt-1 text-xs text-slate-500">
                            <span><i class="fas fa-folder text-slate-400 mr-1"></i> {{ $bab->subBab->count() }} Sub-Bab</span>
                            <span>&bull;</span>
                            <span><i class="fas fa-file-lines text-slate-400 mr-1"></i> {{ $bab->subBab->sum(fn($s) => $s->materi->count()) }} Konten Materi</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    <a href="{{ route('guru.sub_bab.index', $bab->id_bab) }}" class="px-3.5 py-1.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                        <i class="fas fa-list-ul"></i> Lihat Sub-Bab ({{ $bab->subBab->count() }})
                    </a>

                    <form action="{{ route('guru.materi.destroy.bab', $bab->id_bab) }}" method="POST" onsubmit="return confirm('Hapus Bab ini beserta seluruh sub-bab dan materi di dalamnya?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus Bab">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 text-xl mb-3">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Bab</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Mata pelajaran ini belum memiliki bab. Klik tombol Tambah Bab untuk membuat bab pembelajaran pertama.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
