@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'Jadwal Mata Pelajaran')
@section('page_subtitle', 'Kelola alokasi waktu dan jadwal pembelajaran rombel kelas ' . config('binaro.nama_sekolah') . '.')

@section('content')
<div class="space-y-6">

    <!-- Grid Jadwal Mingguan -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-table-cells-large text-primary"></i> Grid Jadwal Mingguan
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Baris = slot jam pelajaran, kolom = hari. Kosong berarti belum ada jadwal.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                        <th class="p-2.5 border-b border-r border-slate-200 w-32 text-center">Slot Jam</th>
                        @foreach ($daftarHari as $hari)
                            <th class="p-2.5 border-b border-r border-slate-200 last:border-r-0">{{ $hari }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slots as $slot)
                        <tr class="{{ $slot->is_break ? 'bg-amber-50/40' : '' }}">
                            <td class="p-2 border-b border-r border-slate-200 bg-slate-50 text-center align-middle">
                                <div class="text-[10px] font-black text-slate-700">{{ $slot->nama }}</div>
                                <div class="text-[9px] text-slate-400 font-mono">{{ $slot->jam_mulai }}-{{ $slot->jam_selesai }}</div>
                            </td>
                            @foreach ($daftarHari as $hari)
                                <td class="p-1.5 border-b border-r border-slate-100 last:border-r-0 align-top">
                                    @if ($slot->is_break)
                                        <div class="text-[10px] italic text-amber-700 text-center py-2">
                                            <i class="fas fa-mug-hot mr-1"></i> Istirahat
                                        </div>
                                    @else
                                        @forelse (($grid[$hari][$slot->id_slot] ?? []) as $item)
                                            <div class="mb-1 rounded-lg border border-primary/20 bg-[#EFF6FF] p-2">
                                                <div class="font-bold text-[11px] text-navy-950 leading-tight">
                                                    {{ $item->mataPelajaran->nama_mapel ?? '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-600 mt-0.5">
                                                    {{ $item->kelas->pararel ?? '-' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500 truncate">
                                                    {{ $item->guru->nama_guru ?? '-' }}
                                                </div>
                                            </div>
                                        @empty
                                            <div class="h-full"></div>
                                        @endforelse
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($daftarHari) + 1 }}" class="p-6 text-center text-slate-400 italic">
                                Belum ada slot waktu. Tambahkan slot terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
                            <td class="p-3.5 font-mono text-xs text-slate-700">{{ $jadwal->jam_rentang }}</td>
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
                                @if ($jadwal->ruang)
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        <i class="fas fa-door-open mr-1"></i>{{ $jadwal->ruang }}
                                    </div>
                                @endif
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
