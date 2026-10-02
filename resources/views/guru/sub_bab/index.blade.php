@extends('layouts.guru')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium flex-wrap">
        <a href="{{ route('guru.mapel.browse') }}" class="hover:text-[#13527D] transition">Mata Pelajaran</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('guru.bab.index', $bab->mataPelajaran->id_mapel) }}" class="hover:text-[#13527D] transition">
            {{ $bab->mataPelajaran->nama_mapel }}
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-800 font-bold">{{ $bab->nama_bab }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-folder"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900">{{ $bab->nama_bab }}</h1>
                    <p class="text-xs text-slate-500">Mata Pelajaran: {{ $bab->mataPelajaran->nama_mapel }} &bull; Daftar Sub-Bab</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.bab.index', $bab->mataPelajaran->id_mapel) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Kembali ke Bab
            </a>
            <button onclick="openModalAction('tambah', 'sub_bab', { idMapel: {{ $bab->mataPelajaran->id_mapel }}, idBab: {{ $bab->id_bab }} })" class="px-3.5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Sub-Bab
            </button>
        </div>
    </div>


    <!-- Daftar Sub-Bab List -->
    <div class="space-y-4">
        @forelse($bab->subBab as $index => $subBab)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <span class="w-8 h-8 rounded-lg bg-[#13527D]/10 text-[#13527D] text-xs font-bold flex items-center justify-center shrink-0">
                        {{ $index + 1 }}
                    </span>
                    <div>
                        <a href="{{ route('guru.materi.sub_bab', $subBab->id_sub_bab) }}" class="block group/link">
                            <h3 class="text-sm font-bold text-slate-900 group-hover/link:text-[#13527D] group-hover/link:underline transition">{{ $subBab->nama_sub_bab }}</h3>
                        </a>
                        <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-500">
                            <span class="flex items-center gap-1 font-semibold text-slate-700">
                                <i class="fas fa-layer-group text-slate-400"></i> {{ $subBab->materi->count() }} Materi:
                            </span>
                            <span class="text-slate-500">
                                <i class="fas fa-video text-sky-500 mr-0.5"></i> {{ $subBab->materi->where('tipe_materi', 'video')->count() }} Video
                            </span>
                            <span>&bull;</span>
                            <span class="text-slate-500">
                                <i class="fas fa-file-pdf text-rose-500 mr-0.5"></i> {{ $subBab->materi->where('tipe_materi', 'dokumen')->count() }} PDF
                            </span>
                            <span>&bull;</span>
                            <span class="text-slate-500">
                                <i class="fas fa-circle-question text-emerald-500 mr-0.5"></i> {{ $subBab->materi->where('tipe_materi', 'kuis')->count() }} Kuis
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    <button onclick="openModalAction('tambah', 'materi', { idMapel: {{ $bab->mataPelajaran->id_mapel }}, idBab: {{ $bab->id_bab }}, idSubBab: {{ $subBab->id_sub_bab }} })" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                        <i class="fas fa-plus"></i> Tambah Materi
                    </button>

                    <a href="{{ route('guru.materi.sub_bab', $subBab->id_sub_bab) }}" class="px-3.5 py-1.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                        <i class="fas fa-eye"></i> Buka Materi ({{ $subBab->materi->count() }})
                    </a>

                    <form action="{{ route('guru.materi.destroy.sub-bab', $subBab->id_sub_bab) }}" method="POST" onsubmit="return confirm('Hapus Sub-Bab ini beserta semua materinya?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus Sub-Bab">
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
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Sub-Bab</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Bab ini belum memiliki sub-bab. Klik tombol Tambah Sub-Bab untuk memulai mengorganisasi materi.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
