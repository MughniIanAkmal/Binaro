@extends(session('user_type') === 'siswa' ? 'layouts.siswa' : 'layouts.app')

@section('page_title', 'Kelola Data Siswa')
@section('page_subtitle', 'Daftar akun siswa ' . config('binaro.nama_sekolah') . ' beserta kontak dan kelasnya.')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row items-center gap-3">
            <div class="flex flex-1 flex-wrap items-center justify-center sm:justify-start gap-2.5">
                <form action="{{ route('siswa.data.index') }}" method="GET" class="flex gap-2 items-center">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search', request('q')) }}" placeholder="Cari nama, NIS, email..."
                               class="pl-8 pr-3 py-2 bg-slate-50 border border-line rounded-xl text-xs font-medium focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                    </div>
                    <button type="submit" class="px-3 py-2 bg-primary hover:opacity-90 text-white text-xs font-bold rounded-xl transition">
                        Cari
                    </button>
                    @if(request('search') || request('q'))
                        <a href="{{ route('siswa.data.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                    @endif
                </form>
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 border border-line text-xs text-slate-500 whitespace-nowrap">
                    <i class="fas fa-user-graduate text-[10px] text-slate-400"></i>
                    Total Siswa: <span class="font-bold text-slate-800">{{ $siswa->total() }}</span>
                </span>
            </div>
            <div class="flex justify-center sm:justify-end shrink-0">
                <button type="button" id="btn-tambah-siswa"
                   class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary">
                    <i class="fas fa-plus text-[11px]"></i> Tambah Siswa
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">No</th>
                        <th class="p-3.5">Nama Siswa</th>
                        <th class="p-3.5">NISN</th>
                        <th class="p-3.5">Kelas</th>
                        <th class="p-3.5">Kontak (Email / HP)</th>
                        <th class="p-3.5">Username</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($siswa as $index => $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 pl-6 text-slate-400 font-semibold">{{ $siswa->firstItem() + $index }}</td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900">{{ $item->nm_siswa }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->alamat ? Str::limit($item->alamat, 30) : 'Alamat belum diatur' }}</div>
                        </td>
                        <td class="p-3.5 font-mono text-slate-700">{{ $item->nisn ?? '-' }}</td>
                        <td class="p-3.5">{{ $item->nama_kelas }}</td>
                        <td class="p-3.5">
                            <div><i class="fas fa-envelope text-slate-400 mr-1 text-[10px]"></i>{{ $item->email ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500"><i class="fas fa-phone text-slate-400 mr-1 text-[10px]"></i>{{ $item->no_hp ?? '-' }}</div>
                        </td>
                        <td class="p-3.5 font-mono text-slate-600">{{ $item->username ?? '-' }}</td>
                        <td class="p-3.5 text-center">
                            <div class="inline-flex gap-1.5">
                                <a href="{{ route('siswa.data.edit', $item->id_siswa) }}"
                                   class="px-2.5 py-1 bg-amber-500 hover:bg-accent-deep text-white rounded-lg text-xs font-semibold transition">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <form action="{{ route('siswa.data.destroy', $item->id_siswa) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-2"><x-empty-state icon="fa-inbox" title="Belum ada data siswa terdaftar." /></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $siswa->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div id="modal-tambah-siswa" class="fixed inset-0 hidden items-center justify-center bg-slate-900/50 z-50 p-4 md:p-6" aria-hidden="true">
    <div class="w-[min(768px,94vw)] max-h-[90vh] flex flex-col bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200" role="dialog" aria-modal="true" aria-labelledby="modalTambahSiswaTitle">
        <div class="px-6 py-4 border-b border-slate-200 flex items-start justify-between gap-4 shrink-0">
            <div>
                <div id="modalTambahSiswaTitle" class="font-extrabold text-base text-slate-900">Tambah Data Siswa</div>
                <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi berikut. Kode QR barcode akan dibuat secara otomatis.</p>
            </div>
            <button type="button" class="text-3xl leading-none text-slate-400 hover:text-slate-600 transition" aria-label="Tutup" data-close="modal-tambah-siswa">&times;</button>
        </div>
        <div class="overflow-y-auto px-6 py-5">
            <form action="{{ route('siswa.data.store') }}" method="POST" class="space-y-6">
                @csrf
                {{-- siswa => null: di halaman daftar $siswa adalah paginator, bukan satu siswa. --}}
                @include('siswa.data._form', ['siswa' => null])

                <div class="flex justify-end gap-2 pt-4 border-t border-line">
                    <button type="button" data-close="modal-tambah-siswa"
                        class="inline-flex items-center px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-line hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary">
                        <i class="fas fa-floppy-disk text-[11px]"></i> Simpan Data Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modalTambahSiswa = document.getElementById('modal-tambah-siswa');

    function openTambahSiswaModal() {
        modalTambahSiswa.classList.remove('hidden');
        modalTambahSiswa.classList.add('flex');
        modalTambahSiswa.setAttribute('aria-hidden', 'false');
    }

    function closeTambahSiswaModal() {
        modalTambahSiswa.classList.add('hidden');
        modalTambahSiswa.classList.remove('flex');
        modalTambahSiswa.setAttribute('aria-hidden', 'true');
    }

    document.getElementById('btn-tambah-siswa').addEventListener('click', function () {
        openTambahSiswaModal();
    });

    document.querySelectorAll('[data-close="modal-tambah-siswa"]').forEach(function (el) {
        el.addEventListener('click', function () {
            closeTambahSiswaModal();
        });
    });

    modalTambahSiswa.addEventListener('click', function (event) {
        if (event.target === modalTambahSiswa) {
            closeTambahSiswaModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modalTambahSiswa.classList.contains('hidden')) {
            closeTambahSiswaModal();
        }
    });

    @if($errors->any())
        openTambahSiswaModal();
    @endif
</script>
@endsection
