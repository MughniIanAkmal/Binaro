@extends('layouts.guru')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.notifikasi_pr.index') }}" class="hover:text-[#13527D] font-medium flex items-center gap-1">
                    <i class="fas fa-arrow-left text-[10px]"></i> Kelola Notifikasi PR
                </a>
                <span>&bull;</span>
                <span class="text-slate-800 font-bold">Detail Tugas</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Rincian PR & Riwayat Siswa Penerima</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.notifikasi_pr.edit', $pr->id_pr) }}" class="px-3.5 py-2 bg-sky-50 hover:bg-sky-100 text-[#13527D] font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-sky-100">
                <i class="fas fa-pen-to-square text-xs"></i> Edit PR
            </a>
        </div>
    </div>


    <!-- Card 1: Banner Rincian Tugas PR -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="bg-sky-100 text-[#13527D] font-extrabold text-xs px-2.5 py-0.5 rounded-lg">
                        {{ $pr->mataPelajaran->nama_mapel ?? 'Mata Pelajaran' }}
                    </span>
                    @php
                        $isExpired = \Carbon\Carbon::parse($pr->tgl_tenggat)->isPast();
                        $diffForHumans = \Carbon\Carbon::parse($pr->tgl_tenggat)->diffForHumans();
                    @endphp
                    @if($isExpired)
                        <span class="bg-rose-100 text-rose-800 font-extrabold text-xs px-2.5 py-0.5 rounded-lg flex items-center gap-1">
                            <i class="fas fa-circle-exclamation text-[10px]"></i> Berakhir {{ $diffForHumans }}
                        </span>
                    @else
                        <span class="bg-emerald-100 text-emerald-800 font-extrabold text-xs px-2.5 py-0.5 rounded-lg flex items-center gap-1">
                            <i class="fas fa-clock text-[10px]"></i> Tenggat {{ $diffForHumans }}
                        </span>
                    @endif
                </div>
                <h2 class="text-lg md:text-xl font-black text-slate-900 pt-1">{{ $pr->nama_pr }}</h2>
                <p class="text-xs text-slate-500">
                    Dibuat oleh: <span class="font-semibold text-slate-700">{{ $pr->guru->nama_guru ?? 'Guru' }}</span>
                    &bull; {{ $pr->created_at ? $pr->created_at->translatedFormat('d M Y') . ', ' . $pr->created_at->format('H:i') . ' WIB' : '-' }}
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl text-xs space-y-1 shrink-0">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Batas Waktu Pengumpulan</div>
                <div class="text-sm font-black text-slate-900 flex items-center gap-1.5 text-rose-600">
                    <i class="fas fa-calendar-alt text-xs"></i>
                    {{ \Carbon\Carbon::parse($pr->tgl_tenggat)->translatedFormat('l, d F Y') }} - Pukul {{ \Carbon\Carbon::parse($pr->tgl_tenggat)->format('H:i') }} WIB
                </div>
            </div>
        </div>

        <!-- Deskripsi / Petunjuk Soal -->
        <div class="space-y-1.5">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Instruksi & Petunjuk Soal:</h3>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-800 leading-relaxed whitespace-pre-wrap font-normal">
                {{ $pr->deskripsi ?: 'Tidak ada instruksi khusus yang dituliskan oleh guru.' }}
            </div>
        </div>
    </div>

    <!-- Card 2: Riwayat Notifikasi Siswa Penerima -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        @php
            $totalNotif = $pr->notifikasi->count();
            $dibaca = $pr->notifikasi->where('status_baca', true)->count();
            $belumDibaca = $totalNotif - $dibaca;
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fas fa-users-line text-[#13527D]"></i>
                    Siswa Penerima Notifikasi ({{ $totalNotif }})
                </h3>
                <p class="text-xs text-slate-500">Status keterbacaan pesan tugas ini pada akun siswa</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="bg-emerald-50 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-lg border border-emerald-200 flex items-center gap-1.5">
                    <i class="fas fa-check-double text-[10px] text-emerald-600"></i> {{ $dibaca }} Dibaca
                </span>
                <span class="bg-amber-50 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-lg border border-amber-200 flex items-center gap-1.5">
                    <i class="fas fa-envelope text-[10px] text-amber-500"></i> {{ $belumDibaca }} Belum Dibaca
                </span>
            </div>
        </div>

        <!-- Tabel Siswa Penerima -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">NISN & Kelas</th>
                        <th class="py-3 px-4">Isi Notifikasi</th>
                        <th class="py-3 px-4 text-center">Status Baca</th>
                        <th class="py-3 px-4">Waktu Kirim</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pr->notifikasi as $idx => $notif)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                            {{ $idx + 1 }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-extrabold text-slate-900">{{ $notif->siswa->nm_siswa ?? 'Siswa' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $notif->siswa->email ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-600">
                            <div>NISN: {{ $notif->siswa->nisn ?? '-' }}</div>
                            <span class="bg-slate-100 text-slate-700 text-[10px] px-2 py-0.5 rounded font-bold mt-0.5 inline-block">
                                {{ $notif->siswa->kelas->pararel ?? 'Kelas' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 max-w-xs">
                            <p class="truncate text-slate-700">{{ $notif->pesan }}</p>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($notif->status_baca)
                                <span class="bg-emerald-50 text-emerald-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fas fa-check-double text-[9px]"></i> Sudah Dibaca
                                </span>
                            @else
                                <span class="bg-amber-50 text-amber-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fas fa-envelope text-[9px]"></i> Belum Dibaca
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                            <div class="font-semibold text-slate-700">{{ $notif->created_at ? $notif->created_at->translatedFormat('d M Y') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $notif->created_at ? $notif->created_at->format('H:i') : '' }} WIB</div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <form action="{{ route('guru.notifikasi_pr.destroy_notifikasi', $notif->id_notifikasi) }}" method="POST"
                                  onsubmit="return confirm('Hapus notifikasi ini dari akun siswa tersebut?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus Notifikasi">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center">
                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-base">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <p class="font-bold text-slate-700 text-xs">Belum ada notifikasi yang dikirimkan untuk PR ini.</p>
                            <p class="text-slate-400 text-[11px] mt-0.5">Notifikasi akan terkirim otomatis saat PR dibuat atau diperbarui melalui menu Edit.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
