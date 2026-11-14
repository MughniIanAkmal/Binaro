@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'Jadwal Mata Pelajaran')
@section('page_subtitle', 'Kelola alokasi waktu dan jadwal pembelajaran rombel kelas ' . config('binaro.nama_sekolah') . '.')

@section('header_actions')
    <a href="{{ route('jadwal.create') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary" >
        <i class="fas fa-plus"></i> Tambah Jadwal
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tabel Data Jadwal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Hari</th>
                        <th class="px-6 py-4">Jam</th>
                        <th class="px-6 py-4">Mata Pelajaran</th>
                        <th class="px-6 py-4">Guru</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($jadwals as $jadwal)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-medium">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $jadwal->hari }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $jadwal->jam }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $jadwal->mataPelajaran->nama_mapel ?? $jadwal->nama_mapel ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $jadwal->guru->nama_guru ?? $jadwal->nama_guru ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md text-xs font-medium">
                                    {{ $jadwal->kelas->pararel ?? $jadwal->nama_kelas ?? 'Belum Set' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-nowrap">
                                    <a href="{{ route('jadwal.show', $jadwal->id_jadwal) }}" class="bg-sky-600 hover:bg-sky-700 text-white px-2.5 py-1.5 rounded-md text-xs font-medium transition inline-flex items-center gap-1 shadow-sm" title="Lihat Detail">
                                        <i class="fas fa-eye text-[11px]"></i> Detail
                                    </a>
                                    <a href="{{ route('jadwal.edit', $jadwal->id_jadwal) }}" class="bg-amber-500 hover:bg-accent-deep text-white px-2.5 py-1.5 rounded-md text-xs font-medium transition inline-flex items-center gap-1 shadow-sm" title="Edit Jadwal">
                                        <i class="fas fa-pen text-[11px]"></i> Edit
                                    </a>
                                    <form action="{{ route('jadwal.destroy', $jadwal->id_jadwal) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal mata pelajaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white px-2.5 py-1.5 rounded-md text-xs font-medium transition inline-flex items-center gap-1 shadow-sm" title="Hapus Jadwal">
                                            <i class="fas fa-trash text-[11px]"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                Belum ada data jadwal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection