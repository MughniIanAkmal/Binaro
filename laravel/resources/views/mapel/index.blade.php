@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'Mata Pelajaran Saya')

@section('content')
<!-- Content Body -->
<div class="space-y-6">

    <!-- KPI Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $total }}</div>
                <div class="text-xs text-slate-600 font-semibold mt-1">Total Mata Pelajaran</div>
                <div class="text-[11px] text-slate-400">Mapel yang Anda ampu</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center text-sm">
                <i class="fas fa-book-open"></i>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <!-- Search Bar -->
        <div class="p-4 border-b border-slate-200 flex gap-3 items-center flex-wrap">
            <form method="GET" action="{{ route('mapel.index') }}" class="flex gap-2 flex-1">
                <div class="relative flex-1 max-w-xs">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama mata pelajaran..."
                           class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-primary">
                </div>
                <button type="submit" class="px-3 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary-hover transition">
                    Cari
                </button>
                @if($search)
                <a href="{{ route('mapel.index') }}" class="px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Reset
                </a>
                @endif
            </form>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">NO</th>
                        <th class="p-3.5">NAMA MATA PELAJARAN</th>
                        <th class="p-3.5 text-center">KELAS</th>
                        <th class="p-3.5">DESKRIPSI</th>
                        <th class="p-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($mapelList as $i => $mapel)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 pl-6 text-slate-400 font-semibold">{{ $mapelList->firstItem() + $i }}</td>
                        <td class="p-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <span class="font-bold text-slate-900">{{ $mapel->nama_mapel }}</span>
                            </div>
                        </td>
                        <td class="p-3.5 text-center">
                            @if($mapel->kelas)
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold text-[10px]">
                                Kelas {{ $mapel->kelas->nama_kelas }}
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-600 font-semibold text-[10px]">
                                Belum diatur
                            </span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <p class="text-slate-600 text-xs line-clamp-2 max-w-md">{{ $mapel->deskripsi ?: '-' }}</p>
                        </td>
                        <td class="p-3.5 text-center">
                            <a href="{{ route('guru.bab.index', $mapel->id_mapel) }}"
                               title="Kelola Bab & Materi Pembelajaran"
                               class="px-2.5 py-1 rounded-lg border border-primary/30 text-primary hover:bg-sky-50 inline-flex items-center gap-1 text-[11px] font-bold transition">
                                <i class="fas fa-layer-group text-[10px]"></i>
                                <span>Kelola Bab</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-slate-400">
                            <i class="fas fa-book-open text-2xl mb-2 block text-slate-300"></i>
                            Tidak ada mata pelajaran yang Anda ampu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $mapelList->links() }}
        </div>
    </div>
</div>
@endsection
