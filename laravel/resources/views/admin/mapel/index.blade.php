@extends('layouts.app')

@section('page_title', 'Kelola Mata Pelajaran')
@section('page_subtitle', 'Kelola master data mata pelajaran ' . config('binaro.nama_sekolah'))

@section('content')
<div class="space-y-6">

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-3 bg-slate-50/50">
            <div class="flex flex-1 flex-wrap items-center gap-2.5 min-w-0">
                <form method="GET" action="{{ route('mapel.index') }}" class="flex gap-2 flex-1 max-w-md">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama mata pelajaran atau deskripsi..."
                               class="w-full pl-9 {{ $search ? 'pr-9' : 'pr-3' }} py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-primary bg-white">
                        @if($search)
                        {{-- Reset dibuat tipis, lalu menguat saat diarahkan agar tetap terlihat sebagai aksi sekunder. --}}
                        <a href="{{ route('mapel.index') }}" title="Reset pencarian" aria-label="Reset pencarian"
                           class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 opacity-45 hover:opacity-100 hover:bg-slate-100 hover:text-slate-600 transition">
                            <i class="fas fa-xmark text-[11px]"></i>
                        </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-magnifying-glass"></i> Cari
                    </button>
                </form>

                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-500 whitespace-nowrap">
                    <i class="fas fa-book-bookmark text-[10px] text-slate-400"></i>
                    Total Mapel: <span class="font-bold text-slate-800">{{ $total }}</span>
                </span>
            </div>

            <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary shrink-0">
                <i class="fas fa-plus text-[11px]"></i> Tambah Mapel
            </button>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Mata Pelajaran</th>
                        <th class="py-3.5 px-4 text-center w-24">Kelas</th>
                        <th class="py-3.5 px-4">Guru Pengampu</th>
                        <th class="py-3.5 px-4">Deskripsi</th>
                        <th class="py-3.5 px-4 text-center">Total Bab</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
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
                                <div class="w-9 h-9 rounded-xl bg-sky-50 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-xs block">{{ $mapel->nama_mapel }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">ID: MAPEL-{{ str_pad($mapel->id_mapel, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($mapel->kelas)
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold text-[10px]">
                                Kelas {{ $mapel->kelas->nama_kelas }}
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-600 font-semibold text-[10px]" title="Kelas belum diatur — mapel tidak akan muncul di aplikasi siswa">
                                Belum diatur
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            @php $pengampu = $mapel->gurus->first(); @endphp
                            @if($pengampu)
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-sky-100 text-primary flex items-center justify-center text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr($pengampu->nama_guru, 0, 2)) }}
                                </span>
                                <span class="font-semibold text-slate-700">{{ $pengampu->nama_guru }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 italic">Belum ada guru</span>
                            @endif
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
                            <div class="inline-flex items-center gap-1.5">
                                <!-- Edit (kelas, guru, nama, deskripsi) -->
                                {{-- @json di-escape untuk HTML (HEX_TAG/AMP/APOS/QUOT), aman di atribut single-quote --}}
                                <button type="button" data-mapel='@json($mapel->toArray())' onclick="openEditModal(JSON.parse(this.dataset.mapel))"
                                        title="Edit Mata Pelajaran"
                                        class="px-3 py-1.5 bg-sky-50 hover:bg-sky-100 border border-sky-200 text-sky-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                                    <i class="fas fa-pen text-[11px]"></i>
                                    <span>Edit</span>
                                </button>
                                <!-- Delete Action -->
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
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400 italic">
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

{{-- Kelas & guru hanya relevan untuk form admin; tidak diload untuk role lain. --}}
@php
    $kelasOptions = $kelasList->map(fn ($k) => ['id' => $k->id_rooms, 'label' => 'Kelas '.$k->nama_kelas])->values();
    $guruOptions = $guruList->map(fn ($g) => ['id' => $g->id_guru, 'label' => $g->nama_guru])->values();
@endphp

{{-- Modal Tambah / Edit Mapel (Admin) --}}
<div id="mapelModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-sky-100 text-primary flex items-center justify-center text-base font-bold">
                    <i class="fas fa-book-bookmark"></i>
                </div>
                <div>
                    <h3 id="mapelModalTitle" class="font-bold text-sm text-slate-900">Tambah Mata Pelajaran</h3>
                    <p class="text-[11px] text-slate-400" id="mapelModalSubtitle">Tambahkan mata pelajaran baru ke sistem</p>
                </div>
            </div>
            <button type="button" onclick="closeMapelModal()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="mapelModalForm" action="{{ route('mapel.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="_method" value="POST" id="mapelModalMethod">
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Nama Mata Pelajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_mapel" id="mapelNama" required maxlength="100"
                       placeholder="Contoh: Matematika, IPA, PJOK, Seni Budaya..."
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s\&\-\(\)\/\.]/g, '');"
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition">
                <span class="text-[10px] text-slate-400 mt-1 block">Maksimal 100 karakter.</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Kelas <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_rooms" id="mapelKelas" required
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition">
                        <option value="" disabled>Pilih kelas...</option>
                        @foreach($kelasOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Guru Pengampu <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_guru" id="mapelGuru" required
                            class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition">
                        <option value="" disabled>Pilih guru...</option>
                        @foreach($guruOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 -mt-2">
                Satu mata pelajaran untuk satu kelas dengan satu guru pengampu. Mapel yang sama di kelas lain dibuat sebagai entri terpisah.
            </p>
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Deskripsi Singkat
                </label>
                <textarea name="deskripsi" id="mapelDeskripsi" rows="3" maxlength="1000"
                          placeholder="Tuliskan cakupan mata pelajaran atau keterangan tambahan..."
                          class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 transition"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeMapelModal()"
                        class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" id="mapelModalSubmit"
                        class="px-4 py-2 bg-primary text-white rounded-xl font-bold hover:bg-primary-hover transition shadow-sm flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan Mapel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const formMapel = document.getElementById('mapelModalForm');
    const modalMapel = document.getElementById('mapelModal');
    const titleMapel = document.getElementById('mapelModalTitle');
    const subtitleMapel = document.getElementById('mapelModalSubtitle');
    const methodMapel = document.getElementById('mapelModalMethod');
    const submitBtnMapel = document.getElementById('mapelModalSubmit');

    function openMapelModal() {
        modalMapel.classList.remove('hidden');
        modalMapel.classList.add('flex');
    }

    function closeMapelModal() {
        modalMapel.classList.add('hidden');
        modalMapel.classList.remove('flex');
    }

    function resetMapelForm() {
        formMapel.reset();
        formMapel.action = "{{ route('mapel.store') }}";
        methodMapel.setAttribute('value', 'POST');
    }

    function openCreateModal() {
        resetMapelForm();
        titleMapel.textContent = 'Tambah Mata Pelajaran';
        subtitleMapel.textContent = 'Tambahkan mata pelajaran baru ke sistem';
        submitBtnMapel.innerHTML = '<i class="fas fa-save"></i> Simpan Mapel';
        openMapelModal();
    }

    function openEditModal(mapel) {
        resetMapelForm();
        formMapel.action = "{{ url('mapel') }}/" + mapel.id_mapel;
        methodMapel.setAttribute('value', 'PUT');
        document.getElementById('mapelNama').value = mapel.nama_mapel ?? '';
        document.getElementById('mapelKelas').value = mapel.id_rooms ?? '';
        document.getElementById('mapelGuru').value = mapel.gurus?.[0]?.id_guru ?? '';
        document.getElementById('mapelDeskripsi').value = mapel.deskripsi ?? '';
        titleMapel.textContent = 'Edit Mata Pelajaran';
        subtitleMapel.textContent = 'Perbarui data mata pelajaran';
        submitBtnMapel.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
        openMapelModal();
    }
</script>
@endsection
