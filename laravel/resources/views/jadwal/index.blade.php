@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'Jadwal Mata Pelajaran')
@section('page_subtitle', 'Kelola alokasi waktu dan jadwal pembelajaran rombel kelas ' . config('binaro.nama_sekolah') . '.')

@section('content')
<div class="space-y-6">

    <!-- Tabel Data Jadwal -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-calendar-days text-primary"></i> Daftar Jadwal Pelajaran
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Total {{ $jadwals->count() }} jadwal terdaftar.</p>
            </div>
            <a href="{{ route('jadwal.create') }}" class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary shrink-0">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <th class="p-3.5 pl-6">No</th>
                        <th class="p-3.5">Hari</th>
                        <th class="p-3.5">Jam</th>
                        <th class="p-3.5">Mata Pelajaran</th>
                        <th class="p-3.5">Guru</th>
                        <th class="p-3.5">Kelas</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($jadwals as $jadwal)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 pl-6 font-medium text-slate-700">{{ $loop->iteration }}</td>
                            <td class="p-3.5 font-medium text-slate-700">{{ $jadwal->hari }}</td>
                            <td class="p-3.5 font-mono text-xs text-slate-700">{{ $jadwal->jam }}</td>
                            <td class="p-3.5 font-bold text-slate-900">
                                {{ $jadwal->mataPelajaran->nama_mapel ?? $jadwal->nama_mapel ?? '-' }}
                            </td>
                            <td class="p-3.5 font-medium text-slate-700">
                                {{ $jadwal->guru->nama_guru ?? $jadwal->nama_guru ?? '-' }}
                            </td>
                            <td class="p-3.5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-primary">
                                    {{ $jadwal->kelas->pararel ?? $jadwal->nama_kelas ?? 'Belum Set' }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-nowrap">
                                    <a href="{{ route('jadwal.show', $jadwal->id_jadwal) }}" class="bg-sky-600 hover:bg-sky-700 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition inline-flex items-center gap-1 shadow-sm" title="Lihat Detail">
                                        <i class="fas fa-eye text-[11px]"></i> Detail
                                    </a>
                                    <a href="{{ route('jadwal.edit', $jadwal->id_jadwal) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition inline-flex items-center gap-1 shadow-sm" title="Edit Jadwal">
                                        <i class="fas fa-pen text-[11px]"></i> Edit
                                    </a>
                                    <form action="{{ route('jadwal.destroy', $jadwal->id_jadwal) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal mata pelajaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold transition inline-flex items-center gap-1 shadow-sm" title="Hapus Jadwal">
                                            <i class="fas fa-trash text-[11px]"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-2"><x-empty-state icon="fa-calendar-days" title="Belum ada data jadwal." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
