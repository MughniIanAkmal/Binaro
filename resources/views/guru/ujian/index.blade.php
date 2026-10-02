@extends('layouts.guru')

@section('content')
<div class="p-8 space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Portal Guru</span>
                <span>/</span>
                <span class="text-[#13527D] font-semibold">Ujian Online</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Kelola Ujian Online</h1>
            <p class="text-xs text-slate-500">Buat ujian online baru, susun bank soal dengan bobot nilai, serta tentukan target siswa & tingkat kesulitan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('guru.ujian.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-xl transition shadow-sm hover:shadow">
                <i class="fas fa-plus"></i>
                <span>Buat Ujian Baru</span>
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#13527D] flex items-center justify-center text-lg font-bold">
                <i class="fas fa-file-signature"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Ujian</p>
                <h3 class="text-xl font-bold text-slate-800">{{ number_format($totalUjian) }}</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                <i class="fas fa-list-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Bank Soal</p>
                <h3 class="text-xl font-bold text-slate-800">{{ number_format($totalSoal) }}</h3>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Siswa Sudah Mengerjakan</p>
                <h3 class="text-xl font-bold text-slate-800">{{ number_format($totalHasil) }}</h3>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('guru.ujian.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <!-- Search Text -->
            <div class="md:col-span-4">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari Judul / Keterangan</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fas fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" id="search-ujian" value="{{ $search }}" placeholder="Ketik kata kunci ujian..." maxlength="100" oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '');" class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition">
                </div>
            </div>

            <!-- Filter Level -->
            <div class="md:col-span-3">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Tingkat Kesulitan</label>
                <select name="level" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition">
                    <option value="">Semua Level</option>
                    <option value="mudah" {{ $level === 'mudah' ? 'selected' : '' }}>🟢 Mudah</option>
                    <option value="sedang" {{ $level === 'sedang' ? 'selected' : '' }}>🟡 Sedang</option>
                    <option value="susah" {{ $level === 'susah' ? 'selected' : '' }}>🔴 Susah</option>
                </select>
            </div>

            <!-- Filter Mapel -->
            <div class="md:col-span-3">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Mata Pelajaran</label>
                <select name="mapel_id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition">
                    <option value="">Semua Mapel</option>
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id_mapel }}" {{ (string)$mapelId === (string)$mapel->id_mapel ? 'selected' : '' }}>
                            {{ $mapel->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-xl transition text-center shadow-sm">
                    Filter
                </button>
                @if($search || $level || $mapelId)
                <a href="{{ route('guru.ujian.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition" title="Reset filter">
                    <i class="fas fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Ujian -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Ujian Online ({{ $ujians->total() }})</h2>
            <span class="text-[11px] text-slate-400">Menampilkan {{ $ujians->firstItem() ?? 0 }} - {{ $ujians->lastItem() ?? 0 }} dari {{ $ujians->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/70 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama / Judul Ujian</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4 text-center">Tingkat Level</th>
                        <th class="py-3.5 px-4 text-center">Durasi</th>
                        <th class="py-3.5 px-4 text-center">Target Siswa</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Soal</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ujians as $index => $ujian)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4 text-center text-slate-400 font-semibold">
                            {{ $ujians->firstItem() + $index }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $ujian->judul_quiz }}</div>
                            @if($ujian->deskripsi)
                                <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $ujian->deskripsi }}</p>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">
                            {{ $ujian->mataPelajaran->nama_mapel ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($ujian->tingkat_level === 'mudah')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Mudah
                                </span>
                            @elseif($ujian->tingkat_level === 'sedang')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Sedang
                                </span>
                            @elseif($ujian->tingkat_level === 'susah')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Susah
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-semibold text-slate-700">
                            <span class="inline-flex items-center gap-1">
                                <i class="far fa-clock text-slate-400"></i>
                                {{ $ujian->durasi_menit ?? 60 }} Menit
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(($ujian->target_tipe ?? 'semua') === 'semua')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fas fa-users text-[9px]"></i> Semua Siswa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200" title="{{ $ujian->targetSiswa->pluck('nm_siswa')->join(', ') }}">
                                    <i class="fas fa-user-tag text-[9px]"></i> {{ $ujian->targetSiswa->count() }} Siswa Pilihan
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md text-[11px]">
                                {{ $ujian->soal_count }} Soal
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('guru.ujian.show', $ujian->id_quiz) }}" class="p-1.5 px-2.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-[11px] transition flex items-center gap-1" title="Lihat & Kelola Soal">
                                    <i class="fas fa-eye"></i> Detail / Soal
                                </a>
                                <a href="{{ route('guru.ujian.edit', $ujian->id_quiz) }}" class="p-1.5 px-2 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition" title="Edit Ujian">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('guru.ujian.destroy', $ujian->id_quiz) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ujian \'{{ $ujian->judul_quiz }}\'? Seluruh soal dan data terkait akan ikut terhapus.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 px-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition" title="Hapus Ujian">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fas fa-file-circle-question"></i>
                            </div>
                            <p class="font-bold text-slate-700 text-sm">Belum Ada Ujian Online</p>
                            <p class="text-xs text-slate-400 mt-1">Mulai buat ujian baru untuk menguji kemampuan dan pemahaman siswa.</p>
                            <a href="{{ route('guru.ujian.create') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-[#13527D] text-white text-xs font-semibold rounded-xl hover:bg-[#0E3D5D] transition">
                                <i class="fas fa-plus"></i> Buat Ujian Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ujians->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $ujians->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
