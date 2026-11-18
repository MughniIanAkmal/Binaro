@extends('layouts.app')

@section('page_title', 'Kelola Data Guru')
@section('page_subtitle', 'Daftar akun pendidik dan staf pengajar ' . config('binaro.nama_sekolah') . '.')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row items-center gap-3">
            <div class="flex flex-1 flex-wrap items-center justify-center sm:justify-start gap-2.5">
                <button type="button" id="btn-tambah-guru" class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary">
                    <i class="fas fa-plus"></i> Tambah Guru
                </button>
                <form action="{{ route('guru.data.index') }}" method="GET" class="flex gap-2 items-center">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search', request('q')) }}" placeholder="Cari nama, NIP, email..."
                               class="pl-8 pr-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 focus:outline-none focus:border-primary">
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-primary hover:opacity-90 text-white text-xs font-bold rounded-lg transition">
                        Cari
                    </button>
                    @if(request('search') || request('q'))
                        <a href="{{ route('guru.data.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                    @endif
                </form>
            </div>
            <div class="flex flex-1 justify-center sm:justify-end">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-500 whitespace-nowrap">
                    <i class="fas fa-user-tie text-[10px] text-slate-400"></i>
                    Total Guru: <span class="font-bold text-slate-800">{{ $guru->total() }}</span>
                </span>
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
                                <a href="{{ route('guru.data.edit', $item->id_guru) }}" class="px-2.5 py-1 bg-amber-500 hover:bg-accent-deep text-white rounded text-xs font-semibold transition">
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
                @include('guru.data._form_guru', ['guru' => null])

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

<script>
    const modalTambahGuru = document.getElementById('modal-tambah-guru');

    function openTambahGuruModal() {
        modalTambahGuru.classList.remove('hidden');
        modalTambahGuru.classList.add('flex');
        modalTambahGuru.setAttribute('aria-hidden', 'false');
    }

    function closeTambahGuruModal() {
        modalTambahGuru.classList.add('hidden');
        modalTambahGuru.classList.remove('flex');
        modalTambahGuru.setAttribute('aria-hidden', 'true');
    }

    document.getElementById('btn-tambah-guru').addEventListener('click', function () {
        openTambahGuruModal();
    });

    document.querySelectorAll('[data-close="modal-tambah-guru"]').forEach(function (el) {
        el.addEventListener('click', function () {
            closeTambahGuruModal();
        });
    });

    modalTambahGuru.addEventListener('click', function (event) {
        if (event.target === modalTambahGuru) {
            closeTambahGuruModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modalTambahGuru.classList.contains('hidden')) {
            closeTambahGuruModal();
        }
    });

    @if($errors->any())
        openTambahGuruModal();
    @endif
</script>
@endsection
