@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1 text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600 transition">Jadwal Pelajaran</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-[#13527D] font-bold">Detail Jadwal</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Detail Jadwal Mata Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Informasi lengkap jadwal mata pelajaran dan guru pengampu.</p>
        </div>
        <a href="{{ route('jadwal.index') }}" class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5 shadow-xs">
            <i class="fas fa-arrow-left text-slate-400"></i>
            <span>Kembali ke Jadwal</span>
        </a>
    </div>

    <!-- Alert Sukses / Error -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Card Detail Utama -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50/70 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-800">
                        {{ $jadwal->mataPelajaran->nama_mapel ?? 'Mata Pelajaran Tidak Ditemukan' }}
                    </h2>
                    <span class="text-xs text-gray-500">
                        Kelas: <strong class="text-blue-700">{{ $jadwal->kelas->pararel ?? 'Belum ditentukan' }}</strong>
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5">
                    <i class="fas fa-clock text-[10px]"></i> {{ $jadwal->hari }}, {{ $jadwal->jam }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Informasi Mata Pelajaran & Jadwal -->
                <div class="space-y-4">
                    <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/30">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Mata Pelajaran</span>
                        <div class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-graduation-cap text-blue-500"></i>
                            {{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}
                        </div>
                        @if(!empty($jadwal->mataPelajaran->deskripsi))
                            <p class="text-xs text-gray-600 mt-2 bg-white p-2.5 rounded-lg border border-gray-100">
                                {{ $jadwal->mataPelajaran->deskripsi }}
                            </p>
                        @endif
                    </div>

                    <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/30">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Hari & Jam Pelajaran</span>
                        <div class="flex items-center gap-4 mt-2">
                            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-gray-100">
                                <i class="fas fa-calendar-day text-amber-500 text-sm"></i>
                                <div>
                                    <span class="text-[10px] text-gray-400 block leading-tight">Hari</span>
                                    <span class="text-sm font-bold text-gray-800">{{ $jadwal->hari }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-gray-100">
                                <i class="fas fa-clock text-sky-500 text-sm"></i>
                                <div>
                                    <span class="text-[10px] text-gray-400 block leading-tight">Waktu</span>
                                    <span class="text-sm font-bold font-mono text-gray-800">{{ $jadwal->jam }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Informasi Guru & Kelas -->
                <div class="space-y-4">
                    <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/30">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Guru Pengampu</span>
                        <div class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-user-tie text-emerald-600"></i>
                            {{ $jadwal->guru->nama_guru ?? 'Belum ditentukan' }}
                        </div>
                        @if(!empty($jadwal->guru->nip))
                            <p class="text-xs text-gray-500 mt-1">
                                NIP: <span class="font-mono text-gray-700">{{ $jadwal->guru->nip }}</span>
                            </p>
                        @endif
                        @if(!empty($jadwal->guru->email))
                            <p class="text-xs text-gray-500 mt-1">
                                Email: <span class="text-gray-700">{{ $jadwal->guru->email }}</span>
                            </p>
                        @endif
                    </div>

                    <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/30">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Ruang / Kelas</span>
                        <div class="flex items-center gap-2 text-base font-bold text-gray-800 mt-1">
                            <i class="fas fa-chalkboard-user text-purple-600"></i>
                            <span>{{ $jadwal->kelas->pararel ?? 'Belum ada kelas' }}</span>
                        </div>
                        @if(!empty($jadwal->kelas->tingkat))
                            <p class="text-xs text-gray-500 mt-1">
                                Tingkat: <span class="font-medium text-gray-700">Tingkat {{ $jadwal->kelas->tingkat }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi di Bawah Card -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-6 mt-6 border-t border-gray-100">
                <a href="{{ route('jadwal.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Kembali ke Daftar Jadwal
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ route('jadwal.edit', $jadwal->id_jadwal) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2 shadow-sm">
                        <i class="fas fa-pen text-xs"></i> Edit Jadwal
                    </a>

                    <form action="{{ route('jadwal.destroy', $jadwal->id_jadwal) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal mata pelajaran ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2 shadow-sm">
                            <i class="fas fa-trash text-xs"></i> Hapus Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
