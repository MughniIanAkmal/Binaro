@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1 text-slate-400">
                <span>Dashboard &gt; Data Siswa</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Data Siswa</h2>
            <p class="text-xs text-slate-500">Daftar akun peserta didik, kelas, dan status barcode QR absen SDN Kalitapen 01.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.qr.index') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class="fas fa-qrcode"></i> Kelola QR Siswa
            </a>
            <a href="{{ route('siswa.create') }}" class="px-4 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Tambah Siswa
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-3">
            <form action="{{ route('siswa.index') }}" method="GET" class="flex gap-2 items-center flex-wrap">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search', request('q')) }}" placeholder="Cari nama, NISN, email..."
                           class="pl-8 pr-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 focus:outline-none focus:border-[#13527D]">
                </div>
                <select name="id_rooms" onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 font-semibold text-slate-700 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id_rooms }}" {{ request('id_rooms') == $k->id_rooms ? 'selected' : '' }}>
                            {{ $k->pararel }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                    Filter
                </button>
                @if(request('search') || request('q') || request('id_rooms'))
                    <a href="{{ route('siswa.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
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
                        <th class="p-3.5 pl-6">NO</th>
                        <th class="p-3.5">NAMA SISWA</th>
                        <th class="p-3.5">NISN / NIS</th>
                        <th class="p-3.5">KELAS</th>
                        <th class="p-3.5">KONTAK WALI</th>
                        <th class="p-3.5">STATUS QR</th>
                        <th class="p-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($siswa as $index => $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 pl-6 text-slate-400 font-semibold">{{ $siswa->firstItem() + $index }}</td>
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900">{{ $item->nama_siswa }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->email ?? 'Email belum diatur' }}</div>
                        </td>
                        <td class="p-3.5 font-mono text-slate-700">{{ $item->nisn ?? '-' }}</td>
                        <td class="p-3.5">
                            <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md text-xs font-semibold">
                                {{ $item->nama_kelas }}
                            </span>
                        </td>
                        <td class="p-3.5 text-slate-600">
                            <div><i class="fas fa-phone text-slate-400 mr-1 text-[10px]"></i>{{ $item->no_hp ?? '-' }}</div>
                        </td>
                        <td class="p-3.5">
                            @if($item->barcode)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 font-mono">
                                    <i class="fas fa-check text-[9px]"></i> {{ $item->barcode->kode }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                    Belum Ada QR
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="inline-flex gap-1.5">
                                <a href="{{ route('siswa.edit', $item->id_siswa) }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded text-xs font-semibold transition">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <form action="{{ route('siswa.destroy', $item->id_siswa) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
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
                        <td colspan="7" class="p-8 text-center text-slate-400">Belum ada data siswa terdaftar.</td>
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
