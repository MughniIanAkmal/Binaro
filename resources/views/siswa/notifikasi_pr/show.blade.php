@extends('layouts.siswa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('siswa.notifikasi_pr.index') }}"
                   class="hover:text-[#13527D] font-medium flex items-center gap-1">
                    <i class="fas fa-arrow-left text-[10px]"></i> Notifikasi PR
                </a>
                <span>&bull;</span>
                <span class="text-slate-800 font-bold">Detail Notifikasi</span>
            </div>
            <h1 class="text-xl font-black text-slate-900">Rincian Pemberitahuan Tugas PR</h1>
        </div>
        <a href="{{ route('siswa.notifikasi_pr.index') }}"
           class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 self-start">
            <i class="fas fa-list text-xs"></i> Semua Notifikasi
        </a>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-700">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-600 text-sm"></i>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif

    @if(session('info'))
    <div class="p-4 bg-sky-50 border border-sky-200 text-sky-800 text-xs rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-info-circle text-sky-600 text-sm"></i>
            <span class="font-semibold">{{ session('info') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-sky-400 hover:text-sky-700">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
    @endif

    {{-- Card Utama: Detail Notifikasi --}}
    @php
        $pr = $notifikasi->pr;
        $isExpired = $pr ? \Carbon\Carbon::parse($pr->tgl_tenggat)->isPast() : true;
        $tenggatCarbon = $pr ? \Carbon\Carbon::parse($pr->tgl_tenggat) : null;
        $tenggatFormatted = $tenggatCarbon ? $tenggatCarbon->translatedFormat('l, d F Y') . ' — Pukul ' . $tenggatCarbon->format('H:i') . ' WIB' : '-';
        $tenggatDiff = $tenggatCarbon ? $tenggatCarbon->diffForHumans() : '-';
    @endphp

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Header Card --}}
        <div class="bg-gradient-to-r from-[#0E385D] to-[#165B96] px-6 py-5 text-white">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @if($pr)
                <span class="bg-white/20 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-lg">
                    {{ $pr->mataPelajaran->nama_mapel ?? 'Mapel' }}
                </span>
                @endif
                <span class="bg-emerald-400/20 text-emerald-200 text-[10px] font-extrabold px-2.5 py-0.5 rounded-lg flex items-center gap-1">
                    <i class="fas fa-check-double text-[9px]"></i> Sudah Dibaca
                </span>
            </div>
            <h2 class="text-lg font-black tracking-tight">{{ $pr->nama_pr ?? 'Tugas PR' }}</h2>
            <p class="text-[11px] text-sky-200 mt-1">
                Dikirim oleh: <strong class="text-white">{{ $notifikasi->guru->nama_guru ?? 'Guru' }}</strong>
                &bull; {{ $notifikasi->created_at ? $notifikasi->created_at->translatedFormat('d M Y') . ', ' . $notifikasi->created_at->format('H:i') . ' WIB' : '-' }}
            </p>
        </div>

        {{-- Body Card --}}
        <div class="p-6 space-y-5">

            {{-- Info Tenggat --}}
            @if($pr)
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 {{ $isExpired ? 'bg-rose-50 border border-rose-200' : 'bg-emerald-50 border border-emerald-200' }} rounded-xl">
                <div class="w-10 h-10 rounded-xl {{ $isExpired ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }} flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-calendar-{{ $isExpired ? 'xmark' : 'check' }}"></i>
                </div>
                <div class="flex-1">
                    <div class="text-[10px] font-bold {{ $isExpired ? 'text-rose-500' : 'text-emerald-600' }} uppercase tracking-wider mb-0.5">
                        {{ $isExpired ? 'Tenggat Sudah Berakhir' : 'Batas Pengumpulan PR' }}
                    </div>
                    <div class="font-extrabold text-sm {{ $isExpired ? 'text-rose-800' : 'text-emerald-800' }}">
                        {{ $tenggatFormatted }}
                    </div>
                    <div class="text-[11px] {{ $isExpired ? 'text-rose-500' : 'text-emerald-600' }} font-medium mt-0.5">
                        {{ ucfirst($tenggatDiff) }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Isi Pesan --}}
            <div>
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <i class="fas fa-envelope-open-text text-[#13527D]"></i> Isi Pesan dari Guru
                </h3>
                <div class="p-4 bg-amber-50/60 border border-amber-200 rounded-xl text-sm text-slate-800 leading-relaxed whitespace-pre-wrap font-normal">
                    {{ $notifikasi->pesan }}
                </div>
            </div>

            {{-- Instruksi PR (jika ada deskripsi) --}}
            @if($pr && $pr->deskripsi)
            <div>
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-[#13527D]"></i> Instruksi & Petunjuk Soal
                </h3>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed whitespace-pre-wrap">
                    {{ $pr->deskripsi }}
                </div>
            </div>
            @endif

            {{-- Meta Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-0.5">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Dikirimkan kepada</div>
                    <div class="font-extrabold text-slate-900">{{ $siswa->nm_siswa }}</div>
                    <div class="text-slate-500">Kelas {{ $siswa->kelas->pararel ?? '-' }}</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-0.5">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Waktu Penerimaan</div>
                    <div class="font-extrabold text-slate-900">
                        {{ $notifikasi->created_at ? $notifikasi->created_at->translatedFormat('d M Y') : '-' }}
                    </div>
                    <div class="text-slate-500">
                        Pukul {{ $notifikasi->created_at ? $notifikasi->created_at->format('H:i') . ' WIB' : '-' }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Notifikasi Lain untuk PR yang Sama --}}
    @if($notifikasiLain->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-history text-[#13527D]"></i> Riwayat Notifikasi Lain untuk PR Ini
        </h3>
        <div class="space-y-2">
            @foreach($notifikasiLain as $lain)
            <a href="{{ route('siswa.notifikasi_pr.show', $lain->id_notifikasi) }}"
               class="flex items-center gap-3 p-3 hover:bg-slate-50 rounded-xl border border-slate-100 transition group">
                <div class="w-8 h-8 rounded-lg {{ $lain->status_baca ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center text-sm shrink-0">
                    <i class="fas fa-{{ $lain->status_baca ? 'envelope-open' : 'envelope' }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] text-slate-700 truncate font-semibold">{{ $lain->pesan }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">
                        {{ $lain->created_at ? $lain->created_at->translatedFormat('d M Y') . ', ' . $lain->created_at->format('H:i') . ' WIB' : '-' }}
                    </p>
                </div>
                <span class="text-[10px] font-bold {{ $lain->status_baca ? 'text-emerald-600' : 'text-amber-600' }} shrink-0">
                    {{ $lain->status_baca ? 'Dibaca' : 'Belum Dibaca' }}
                </span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tombol Aksi Bawah --}}
    <div class="flex items-center justify-between gap-3 pb-4">
        <a href="{{ route('siswa.notifikasi_pr.index') }}"
           class="px-4 py-2.5 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fas fa-arrow-left text-xs"></i> Kembali ke Daftar
        </a>
        <span class="text-xs text-emerald-600 font-bold flex items-center gap-1.5">
            <i class="fas fa-check-double"></i> Notifikasi ini sudah ditandai sebagai dibaca
        </span>
    </div>

</div>
@endsection
