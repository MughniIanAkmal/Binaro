@extends('layouts.app')

@php
    // Saat update gagal validasi, Laravel kembali ke halaman ini dengan old input.
    // Hidden edit_id menandai baris mana yang harus dibuka kembali modal editnya.
    $editGagalId = (string) old('edit_id', '');
    $editGagalAda = $editGagalId !== '' && $guru->contains(fn ($item) => (string) $item->id_guru === $editGagalId);
    $tambahGagal = $errors->any() && $editGagalId === '';
@endphp

@section('page_title', 'Kelola Data Guru')
@section('page_subtitle', 'Daftar akun pendidik dan staf pengajar ' . config('binaro.nama_sekolah') . '.')

{{-- Error validasi tampil di dalam modal; alert global hanya dipakai bila modalnya tidak bisa dibuka. --}}
@if($editGagalId === '' || $editGagalAda)
    @section('hide_global_errors', '1')
@endif

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row items-center gap-3 bg-slate-50/50">
            <div class="flex flex-1 flex-wrap items-center justify-center sm:justify-start gap-2.5">
                <form action="{{ route('guru.data.index') }}" method="GET" class="flex gap-2 items-center flex-1 max-w-md">
                    @php $kataKunci = request('search', request('q')); @endphp
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $kataKunci }}" placeholder="Cari nama, NIP, email..."
                               class="w-full {{ $kataKunci ? 'pr-8' : 'pr-3' }} pl-8 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                        @if($kataKunci)
                        {{-- Reset dibuat tipis, lalu menguat saat diarahkan agar tetap terlihat sebagai aksi sekunder. --}}
                        <a href="{{ route('guru.data.index') }}" title="Reset pencarian" aria-label="Reset pencarian"
                           class="absolute right-2 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 opacity-45 hover:opacity-100 hover:bg-slate-100 hover:text-slate-600 transition">
                            <i class="fas fa-xmark text-[11px]"></i>
                        </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-magnifying-glass"></i> Cari
                    </button>
                </form>
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-500 whitespace-nowrap">
                    <i class="fas fa-user-tie text-[10px] text-slate-400"></i>
                    Total Guru: <span class="font-bold text-slate-800">{{ $guru->total() }}</span>
                </span>
            </div>
            <div class="flex justify-center sm:justify-end shrink-0">
                <button type="button" id="btn-tambah-guru" class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary">
                    <i class="fas fa-plus"></i> Tambah Guru
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">NO</th>
                        <th class="p-3.5">NAMA GURU</th>
                        <th class="p-3.5">NIP</th>
                        <th class="p-3.5">KELAS (LOCK)</th>
                        <th class="p-3.5">MAPEL DIAJAR</th>
                        <th class="p-3.5">KONTAK (EMAIL / HP)</th>
                        <th class="p-3.5">USERNAME</th>
                        <th class="p-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($guru as $index => $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 pl-6 text-slate-400 font-semibold">{{ $guru->firstItem() + $index }}</td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900">{{ $item->nama_guru }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->alamat ? Str::limit($item->alamat, 30) : 'Alamat belum diatur' }}</div>
                        </td>
                        <td class="p-3.5 font-mono text-slate-700">{{ $item->nip ?? '-' }}</td>
                        <td class="p-3.5">{{ $item->kelas ? $item->kelas->nama_kelas : '-' }}</td>
                        <td class="p-3.5">{{ $item->mapel->isNotEmpty() ? $item->mapel->pluck('nama_mapel')->join(', ') : '-' }}</td>
                        <td class="p-3.5">
                            <div><i class="fas fa-envelope text-slate-400 mr-1 text-[10px]"></i>{{ $item->email ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500"><i class="fas fa-phone text-slate-400 mr-1 text-[10px]"></i>{{ $item->no_hp ?? '-' }}</div>
                        </td>
                        <td class="p-3.5 font-mono text-slate-600">{{ $item->username ?? '-' }}</td>
                        <td class="p-3.5 text-center">
                            <div class="inline-flex gap-1.5">
                                <a href="{{ route('guru.data.edit', $item->id_guru) }}" data-open-edit-guru="{{ $item->id_guru }}" class="px-2.5 py-1 bg-amber-500 hover:bg-accent-deep text-white rounded text-xs font-semibold transition">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <form action="{{ route('guru.data.destroy', $item->id_guru) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-semibold transition">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-2"><x-empty-state icon="fa-inbox" title="Belum ada data guru terdaftar." /></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $guru->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div id="modal-tambah-guru" class="fixed inset-0 hidden items-center justify-center bg-slate-900/50 z-50 p-4 md:p-6" aria-hidden="true">
    <div class="w-[min(768px,94vw)] max-h-[90vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200" role="dialog" aria-modal="true" aria-labelledby="modalTambahGuruTitle">
        <div class="px-6 py-4 border-b border-slate-200 flex items-start justify-between gap-4 shrink-0">
            <div>
                <div id="modalTambahGuruTitle" class="font-extrabold text-base text-slate-900">Tambah Data Guru</div>
                <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi di bawah untuk menambahkan guru baru.</p>
            </div>
            <button type="button" class="text-3xl leading-none text-slate-400 hover:text-slate-600 transition" aria-label="Tutup" data-close="modal-tambah-guru">&times;</button>
        </div>
        <div class="overflow-y-auto px-6 py-5">
            <form action="{{ route('guru.data.store') }}" method="POST" class="space-y-6">
                @csrf
                {{-- guru => null: di halaman daftar $guru adalah paginator, bukan satu guru. --}}
                @include('guru.data._form_guru', ['guru' => null, 'pakaiOld' => $tambahGagal])

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                    <button type="button" data-close="modal-tambah-guru" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-bold transition shadow-sm">
                        Simpan Data Guru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Guru: satu modal per baris agar data yang diubah sudah terisi. --}}
@foreach($guru as $item)
<div id="modal-edit-guru-{{ $item->id_guru }}" class="fixed inset-0 hidden items-center justify-center bg-slate-900/50 z-50 p-4 md:p-6" aria-hidden="true">
    <div class="w-[min(768px,94vw)] max-h-[90vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200" role="dialog" aria-modal="true" aria-labelledby="modalEditGuruTitle-{{ $item->id_guru }}">
        <div class="px-6 py-4 border-b border-slate-200 flex items-start justify-between gap-4 shrink-0">
            <div>
                <div id="modalEditGuruTitle-{{ $item->id_guru }}" class="font-extrabold text-base text-slate-900">Edit Data Guru</div>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi guru {{ $item->nama_guru }}.</p>
            </div>
            <button type="button" class="text-3xl leading-none text-slate-400 hover:text-slate-600 transition" aria-label="Tutup" data-close="modal-edit-guru-{{ $item->id_guru }}">&times;</button>
        </div>
        <div class="overflow-y-auto px-6 py-5">
            <form action="{{ route('guru.data.update', $item->id_guru) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_id" value="{{ $item->id_guru }}">
                @include('guru.data._form_guru', [
                    'guru' => $item,
                    'pakaiOld' => (string) $editGagalId === (string) $item->id_guru,
                ])

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
                    <button type="button" data-close="modal-edit-guru-{{ $item->id_guru }}" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-hover text-white rounded-lg text-xs font-bold transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    function bukaModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
    }

    function tutupModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    }

    function openTambahGuruModal() {
        bukaModal('modal-tambah-guru');
    }

    function closeTambahGuruModal() {
        tutupModal('modal-tambah-guru');
    }

    document.getElementById('btn-tambah-guru').addEventListener('click', function () {
        openTambahGuruModal();
    });

    document.querySelectorAll('[data-open-edit-guru]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            // href tetap ada sebagai fallback bila JavaScript tidak berjalan.
            event.preventDefault();
            bukaModal('modal-edit-guru-' + button.dataset.openEditGuru);
        });
    });

    document.querySelectorAll('[data-close]').forEach(function (el) {
        el.addEventListener('click', function () {
            tutupModal(el.dataset.close);
        });
    });

    document.querySelectorAll('[id^="modal-tambah-guru"], [id^="modal-edit-guru-"]').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                tutupModal(modal.id);
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('[id^="modal-tambah-guru"], [id^="modal-edit-guru-"]').forEach(function (modal) {
            if (!modal.classList.contains('hidden')) {
                tutupModal(modal.id);
            }
        });
    });

    @if($errors->any() && $editGagalAda)
        // Update gagal: buka kembali modal edit milik data yang divalidasi.
        bukaModal('modal-edit-guru-{{ $editGagalId }}');
    @elseif($tambahGagal)
        // Tambah guru gagal: buka kembali modal tambah.
        openTambahGuruModal();
    @endif
</script>
@endsection
