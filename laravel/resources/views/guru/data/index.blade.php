@extends('layouts.app')

@section('page_title', 'Kelola Data Guru')
@section('page_subtitle', 'Daftar akun pendidik dan staf pengajar ' . config('binaro.nama_sekolah') . '.')

@section('breadcrumb_trail')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition shrink-0">Dashboard</a>
@endsection

@section('header_actions')
    <a href="{{ route('guru.data.create') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition" style="background: var(--binaro-navy);">
        <i class="fas fa-plus"></i> Tambah Guru
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center flex-wrap gap-3">
            <form action="{{ route('guru.data.index') }}" method="GET" class="flex gap-2 items-center">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search', request('q')) }}" placeholder="Cari nama, NIP, email..."
                           class="pl-8 pr-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-slate-50 focus:outline-none focus:border-[#13527D]">
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                    Cari
                </button>
                @if(request('search') || request('q'))
                    <a href="{{ route('guru.data.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                @endif
            </form>
            <div class="text-xs text-slate-500">
                Total Guru: <span class="font-bold text-slate-800">{{ $guru->total() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">NO</th>
                        <th class="p-3.5">NAMA GURU</th>
                        <th class="p-3.5">NIP</th>
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
                        <td class="p-3.5">
                            <div><i class="fas fa-envelope text-slate-400 mr-1 text-[10px]"></i>{{ $item->email ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500"><i class="fas fa-phone text-slate-400 mr-1 text-[10px]"></i>{{ $item->no_hp ?? '-' }}</div>
                        </td>
                        <td class="p-3.5 font-mono text-slate-600">{{ $item->username ?? '-' }}</td>
                        <td class="p-3.5 text-center">
                            <div class="inline-flex gap-1.5">
                                <a href="{{ route('guru.data.edit', $item->id_guru) }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded text-xs font-semibold transition">
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
                        <td colspan="6" class="p-8 text-center text-slate-400">Belum ada data guru terdaftar.</td>
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
@endsection
