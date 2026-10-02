@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Header Title & Role Clarification Banner -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1">
                <span class="bg-[#13527D]/10 text-[#13527D] px-2.5 py-0.5 rounded-full flex items-center gap-1 font-bold">
                    <i class="fas fa-shield-halved text-[9px]"></i> Panel Administrator
                </span>
                <span class="text-slate-400">Dashboard &gt; Kelola Mata Pelajaran</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Daftar Mata Pelajaran</h2>
            <p class="text-xs text-slate-500">
                Kelola master data mata pelajaran SDN Kalitapen 01. Anda dapat menambahkan mata pelajaran baru, memonitor, dan menghapus mapel.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateModal()" class="px-4 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Tambah Mapel
            </button>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Mapel</span>
                <div class="text-3xl font-black text-slate-900 mt-1">{{ $total }}</div>
                <span class="text-[11px] text-slate-500 font-medium">Terdaftar di sistem</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center text-xl font-bold">
                <i class="fas fa-book-bookmark"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kelola Kurikulum</span>
                <div class="text-sm font-bold text-emerald-600 mt-2">Dikelola oleh Guru</div>
                <span class="text-[11px] text-slate-400">Bab, Sub-bab, & Materi</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-chalkboard-user"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pusat Data</span>
                <div class="text-sm font-bold text-slate-800 mt-2">SDN Kalitapen 01</div>
                <span class="text-[11px] text-slate-400">Tahun Ajaran Aktif</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-school"></i>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-3 bg-slate-50/50">
            <form method="GET" action="{{ route('mapel.index') }}" class="flex gap-2 flex-1 max-w-md">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama mata pelajaran atau deskripsi..."
                           class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] bg-white">
                </div>
                <button type="submit" class="px-4 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-magnifying-glass"></i> Cari
                </button>
                @if($search)
                <a href="{{ route('mapel.index') }}" class="px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Reset
                </a>
                @endif
            </form>

            <span class="text-xs text-slate-500 font-medium">
                Menampilkan {{ $mapelList->count() }} dari total {{ $total }} mapel
            </span>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Deskripsi</th>
                        <th class="py-3.5 px-4 text-center">Total Bab</th>
                        <th class="py-3.5 px-4 text-center">Siswa Mengambil</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mapelList as $index => $mapel)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 text-center text-slate-400 font-semibold">
                            {{ $mapelList->firstItem() + $index }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-xs block">{{ $mapel->nama_mapel }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">ID: MAPEL-{{ str_pad($mapel->id_mapel, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                            <p class="truncate" title="{{ $mapel->deskripsi ?? 'Tidak ada deskripsi.' }}">
                                {{ $mapel->deskripsi ?? '-' }}
                            </p>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]">
                                {{ $mapel->bab_count ?? $mapel->bab()->count() }} Bab
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px]">
                                {{ $mapel->siswas_count ?? $mapel->siswas()->count() }} Siswa
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <!-- Only Delete Action for Admin -->
                            <form action="{{ route('mapel.destroy', $mapel->id_mapel) }}" method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran {{ addslashes($mapel->nama_mapel) }}? Semua bab dan materi terkait dapat terpengaruh.');"
                                  class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        title="Hapus Mata Pelajaran"
                                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400 italic">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                <i class="fas fa-book"></i>
                            </div>
                            Tidak ada mata pelajaran yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mapelList->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $mapelList->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Mapel (Admin) -->
<div id="createModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-sky-100 text-[#13527D] flex items-center justify-center text-base font-bold">
                    <i class="fas fa-book-bookmark"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Tambah Mata Pelajaran</h3>
                    <p class="text-[11px] text-slate-400">Tambahkan mata pelajaran baru ke sistem</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('mapel.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Nama Mata Pelajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_mapel" required maxlength="100"
                       placeholder="Contoh: Matematika, IPA, PJOK, Seni Budaya..."
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s\&\-\(\)\/\.]/g, '');"
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:ring-1 focus:ring-[#13527D]/20 transition">
                <span class="text-[10px] text-slate-400 mt-1 block">Maksimal 100 karakter.</span>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Deskripsi Singkat
                </label>
                <textarea name="deskripsi" rows="3" maxlength="1000"
                          placeholder="Tuliskan cakupan mata pelajaran atau keterangan tambahan..."
                          class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:ring-1 focus:ring-[#13527D]/20 transition"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()"
                        class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-[#13527D] text-white rounded-xl font-bold hover:bg-[#0E3D5D] transition shadow-sm flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan Mapel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        const m = document.getElementById('createModal');
        if (m) {
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
    }
    function closeCreateModal() {
        const m = document.getElementById('createModal');
        if (m) {
            m.classList.remove('flex');
            m.classList.add('hidden');
        }
    }
</script>
@endsection
