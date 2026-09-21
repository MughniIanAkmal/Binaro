@extends('layouts.app')

@section('content')
<!-- Content Body -->
<div class="p-8 space-y-6">
    <!-- Page Title -->
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900">Manajemen Mata Pelajaran</h2>
            <p class="text-xs text-slate-500">Dashboard &gt; Kelola Mata Pelajaran</p>
        </div>
                <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50">
                <i class="fas fa-download mr-1.5"></i> Export
            </button>
        <button onclick="openCreateModal()" class="px-4 py-2 bg-[#F59E0B] text-white rounded-lg text-xs font-semibold hover:bg-amber-600 transition shadow-sm">
            <i class="fas fa-plus mr-1.5"></i> Tambah Mapel
        </button>
    </div>
    </div>

    <!-- KPI Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 flex justify-between items-start">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $total }}</div>
                <div class="text-xs text-slate-600 font-semibold mt-1">Total Mata Pelajaran</div>
                <div class="text-[11px] text-slate-400">Terdaftar di sistem</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-[#13527D] text-white flex items-center justify-center text-sm">
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
                           class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D]">
                </div>
                <button type="submit" class="px-3 py-2 bg-[#13527D] text-white rounded-lg text-xs font-semibold hover:bg-[#0E3D5D] transition">
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
                                <div class="w-7 h-7 rounded-full bg-[#13527D]/10 text-[#13527D] flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <span class="font-bold text-slate-900">{{ $mapel->nama_mapel }}</span>
                            </div>
                        </td>
                        <td class="p-3.5">
                            <p class="text-slate-600 text-xs line-clamp-2 max-w-md">{{ $mapel->deskripsi ?: '-' }}</p>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="inline-flex gap-1">
                                <button onclick="openEditModal({{ $mapel->id_mapel }}, {{ json_encode($mapel->nama_mapel) }}, {{ json_encode($mapel->deskripsi ?? '') }})"
                                        title="Edit"
                                        class="w-7 h-7 rounded border border-slate-200 text-amber-600 hover:bg-amber-50 flex items-center justify-center transition">
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                </button>
                                <button onclick="openDeleteModal({{ $mapel->id_mapel }}, {{ json_encode($mapel->nama_mapel) }})"
                                        title="Hapus"
                                        class="w-7 h-7 rounded border border-slate-200 text-rose-500 hover:bg-rose-50 flex items-center justify-center transition">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-slate-400">
                            <i class="fas fa-book-open text-2xl mb-2 block text-slate-300"></i>
                            Tidak ada data mata pelajaran ditemukan.
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

<!-- ===== Modal Tambah Mapel ===== -->
<div id="createModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Tambah Mata Pelajaran</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Masukkan nama mata pelajaran baru</p>
            </div>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('mapel.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Nama Mata Pelajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_mapel" required
                       placeholder="Contoh: Matematika, Bahasa Indonesia, IPA..."
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D] focus:ring-1 focus:ring-[#13527D]/20 transition">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Deskripsi Singkat Mapel
                </label>
                <textarea name="deskripsi" rows="3"
                          placeholder="Tuliskan deskripsi singkat mengenai cakupan materi atau tujuan mapel..."
                          class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D] focus:ring-1 focus:ring-[#13527D]/20 transition"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()"
                        class="px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-[#13527D] text-white rounded-lg font-bold hover:bg-[#0E3D5D] transition">
                    <i class="fas fa-save mr-1.5"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Modal Edit Mapel ===== -->
<div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Edit Mata Pelajaran</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Ubah nama dan deskripsi mata pelajaran</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editForm" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Nama Mata Pelajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="editNamaMapel" name="nama_mapel" required
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D] focus:ring-1 focus:ring-[#13527D]/20 transition">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Deskripsi Singkat Mapel
                </label>
                <textarea id="editDeskripsi" name="deskripsi" rows="3"
                          placeholder="Tuliskan deskripsi singkat mengenai cakupan materi atau tujuan mapel..."
                          class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#13527D] focus:ring-1 focus:ring-[#13527D]/20 transition"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-amber-500 text-white rounded-lg font-bold hover:bg-amber-600 transition">
                    <i class="fas fa-save mr-1.5"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Modal Konfirmasi Hapus ===== -->
<div id="deleteModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200">
        <div class="text-center mb-5">
            <div class="w-14 h-14 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-trash-alt text-rose-500 text-xl"></i>
            </div>
            <h3 class="font-bold text-sm text-slate-900 mb-1">Konfirmasi Hapus</h3>
            <p class="text-xs text-slate-500">Anda akan menghapus mata pelajaran:</p>
            <p id="deleteNamaMapel" class="text-xs font-bold text-slate-900 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200 mt-2"></p>
            <p class="text-[11px] text-rose-500 mt-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Data yang sudah terhapus tidak dapat dikembalikan.
            </p>
        </div>
        <form id="deleteForm" method="POST" class="flex gap-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2 border border-slate-200 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 text-xs">
                Batal
            </button>
            <button type="submit"
                    class="flex-1 px-4 py-2 bg-rose-500 text-white rounded-lg font-bold hover:bg-rose-600 transition text-xs">
                <i class="fas fa-trash-alt mr-1.5"></i> Hapus
            </button>
        </form>
    </div>
</div>

<script>
    // ===== Create Modal =====
    function openCreateModal() {
        const m = document.getElementById('createModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeCreateModal() {
        const m = document.getElementById('createModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    // ===== Edit Modal =====
    function openEditModal(id, nama, deskripsi) {
        document.getElementById('editForm').action = `/mapel/${id}`;
        document.getElementById('editNamaMapel').value = nama;
        document.getElementById('editDeskripsi').value = deskripsi || '';
        const m = document.getElementById('editModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeEditModal() {
        const m = document.getElementById('editModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    // ===== Delete Modal =====
    function openDeleteModal(id, nama) {
        document.getElementById('deleteForm').action = `/mapel/${id}`;
        document.getElementById('deleteNamaMapel').textContent = nama;
        const m = document.getElementById('deleteModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeDeleteModal() {
        const m = document.getElementById('deleteModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    // Close modals on backdrop click
    ['createModal', 'editModal', 'deleteModal'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });
    });
</script>
@endsection
