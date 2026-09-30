@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Title & Back Button -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="text-[11px] font-semibold text-slate-400 mb-1">
                RPP &bull; Modul Ajar &bull; Detail
            </div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $rpp->judul_rpp ?? $rpp->judul }}</h2>
            <p class="text-xs text-slate-500">Informasi lengkap kelengkapan silabus dan dokumen modul ajar.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('rpp.index') }}" class="px-3.5 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @if($rpp->file_rpp)
            <a href="{{ route('rpp.download', $rpp->id_rpp) }}" class="px-3.5 py-2 rounded-lg bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-download"></i> Unduh Berkas
            </a>
            @endif
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm p-6 space-y-6 text-xs">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pb-6 border-b border-slate-100">
            <div>
                <span class="text-slate-400 font-semibold block mb-1">Mata Pelajaran:</span>
                <span class="text-sm font-bold text-slate-800">{{ $rpp->mataPelajaran->nama_mapel ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block mb-1">Kelas:</span>
                <span class="text-sm font-bold text-slate-800">{{ $rpp->kelas->pararel ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block mb-1">Guru Pengampu:</span>
                <span class="text-sm font-bold text-slate-800">{{ $rpp->guru->nama_guru ?? '-' }}</span>
                <span class="text-[11px] text-slate-400 block">NIP. {{ $rpp->guru->nip ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold block mb-1">Status Verifikasi:</span>
                @if($rpp->status == 'terverifikasi')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <i class="fas fa-check text-[10px]"></i> Terverifikasi
                    </span>
                @elseif($rpp->status == 'menunggu_review')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">
                        <i class="fas fa-clock text-[10px]"></i> Menunggu Review
                    </span>
                @elseif($rpp->status == 'perlu_revisi')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <i class="fas fa-exclamation-triangle text-[10px]"></i> Perlu Revisi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                        <i class="fas fa-times text-[10px]"></i> Draf Belum Lengkap
                    </span>
                @endif
            </div>
        </div>

        <div>
            <h4 class="font-bold text-slate-800 mb-2">Deskripsi Modul Ajar</h4>
            <p class="text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-lg border border-slate-100 whitespace-pre-line">
                {{ $rpp->deskripsi ?: 'Tidak ada deskripsi rinci untuk modul ajar ini.' }}
            </p>
        </div>

        <div>
            <h4 class="font-bold text-slate-800 mb-2">Kelengkapan Komponen Modul</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3 rounded-lg border {{ ($rpp->komponen_checklist['tujuan'] ?? false) ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <i class="fas {{ ($rpp->komponen_checklist['tujuan'] ?? false) ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-slate-300' }} mr-1.5"></i>
                    <span class="font-semibold">Tujuan Belajar</span>
                </div>
                <div class="p-3 rounded-lg border {{ ($rpp->komponen_checklist['video'] ?? false) ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <i class="fas {{ ($rpp->komponen_checklist['video'] ?? false) ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-slate-300' }} mr-1.5"></i>
                    <span class="font-semibold">Video SD</span>
                </div>
                <div class="p-3 rounded-lg border {{ ($rpp->komponen_checklist['kktp'] ?? false) ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <i class="fas {{ ($rpp->komponen_checklist['kktp'] ?? false) ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-slate-300' }} mr-1.5"></i>
                    <span class="font-semibold">KKTP / Kriteria</span>
                </div>
                <div class="p-3 rounded-lg border {{ ($rpp->komponen_checklist['lkpd'] ?? false) ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <i class="fas {{ ($rpp->komponen_checklist['lkpd'] ?? false) ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-slate-300' }} mr-1.5"></i>
                    <span class="font-semibold">LKPD Siswa</span>
                </div>
            </div>
        </div>

        @if($rpp->catatan_revisi)
        <div>
            <h4 class="font-bold text-amber-800 mb-2">Catatan Supervisi / Revisi</h4>
            <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800">
                {{ $rpp->catatan_revisi }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
