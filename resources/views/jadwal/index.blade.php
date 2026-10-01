@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Header Halaman & Breadcrumb -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1 text-slate-400">
                <a href="{{ session('user_type') === 'guru' ? route('guru.dashboard') : route('admin.dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-[#13527D] font-bold">Jadwal Pelajaran</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Jadwal Mata Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola alokasi waktu dan jadwal pembelajaran rombel kelas SDN Kalitapen 01.</p>
        </div>
        <div class="flex items-center gap-2.5">
            @if(session('user_type') === 'admin')
            <a href="{{ route('admin.absensi.settings') }}" class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5 shadow-xs">
                <i class="fas fa-clock text-slate-500"></i>
                <span>Setting Jam Absen</span>
            </a>
            @endif
            <a href="{{ route('jadwal.create') }}" class="bg-[#13527D] hover:bg-[#0E3D5D] text-white px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>
    </div>

    <!-- Alert Sukses -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

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
                                    <a href="{{ route('jadwal.edit', $jadwal->id_jadwal) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1.5 rounded-md text-xs font-medium transition inline-flex items-center gap-1 shadow-sm" title="Edit Jadwal">
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