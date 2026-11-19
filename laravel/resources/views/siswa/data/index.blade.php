@extends(session('user_type') === 'siswa' ? 'layouts.siswa' : 'layouts.app')

@section('breadcrumb_trail')
    <span class="shrink-0">Halaman</span>
    <i class="fas fa-chevron-right text-[8px] opacity-60 shrink-0"></i>
    <span class="text-slate-500">Data Siswa</span>
@endsection

@section('page_title', 'Kelola Data Siswa')
@section('page_subtitle', 'Daftar akun siswa ' . config('binaro.nama_sekolah') . ' beserta kontak dan kelasnya.')

@section('header_actions')
    <a href="{{ route('siswa.data.create') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary"
       >
        <i class="fas fa-plus text-[11px]"></i> Tambah Siswa
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-3">
            <form action="{{ route('siswa.data.index') }}" method="GET" class="flex gap-2 items-center">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search', request('q')) }}" placeholder="Cari nama, NIS, email..."
                           class="pl-8 pr-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 focus:outline-none focus:border-primary">
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                    Cari
                </button>
                @if(request('search') || request('q'))
                    <a href="{{ route('siswa.data.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                @endif
            </form>
            <div class="text-xs text-slate-500">
                Total Siswa: <span class="font-bold text-slate-800">{{ $siswa->total() }}</span>
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
                        <th class="p-3.5">Mapel</th>
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
                        <td class="p-3.5">{{ $item->mataPelajaran?->nama_mapel ?? '-' }}</td>
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
                        <td colspan="8" class="p-2"><x-empty-state icon="fa-inbox" title="Belum ada data siswa terdaftar." /></td>
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
@endsection
