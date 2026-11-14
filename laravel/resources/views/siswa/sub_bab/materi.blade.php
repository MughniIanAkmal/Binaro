@extends('layouts.siswa')

@section('page_title', $subBab->nama_sub_bab)
@section('page_subtitle', $subBab->bab->mataPelajaran->nama_mapel.' · '.$subBab->bab->nama_bab.' · Pilih salah satu materi untuk mulai belajar.')

@section('breadcrumb_trail')
    <a href="{{ route('siswa.mapel.index') }}" class="hover:text-slate-600 transition">Pembelajaran</a>
    <i class="fas fa-chevron-right text-[8px] opacity-60"></i>
    <a href="{{ route('siswa.materi.index', $subBab->bab->mataPelajaran->id_mapel) }}" class="hover:text-slate-600 transition">{{ $subBab->bab->mataPelajaran->nama_mapel }}</a>
    <i class="fas fa-chevron-right text-[8px] opacity-60"></i>
    <span class="text-slate-500">{{ $subBab->bab->nama_bab }}</span>
@endsection

@section('header_actions')
    <a href="{{ route('siswa.materi.index', $subBab->bab->mataPelajaran->id_mapel) }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-line hover:bg-slate-50 transition">
        <i class="fas fa-arrow-left text-[11px]"></i> Kembali
    </a>
@endsection

@section('content')
<div class="space-y-5 max-w-4xl mx-auto">

    <!-- Daftar Konten Materi -->
    <div class="space-y-4">
        @forelse($subBab->materi as $mIdx => $mat)
        <div class="bg-white rounded-2xl shadow-sm border border-line hover:border-primary/50 transition p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                @if($mat->tipe_materi === 'video')
                    <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base shrink-0">
                        <i class="fas fa-circle-play"></i>
                    </div>
                @elseif($mat->tipe_materi === 'dokumen')
                    <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base shrink-0">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                @else
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base shrink-0">
                        <i class="fas fa-brain"></i>
                    </div>
                @endif

                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        @if($mat->tipe_materi === 'video')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-700 uppercase">Video</span>
                        @elseif($mat->tipe_materi === 'dokumen')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 uppercase">PDF</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">Kuis</span>
                        @endif
                        <h3 class="text-sm font-bold text-slate-800">{{ $mat->judul_materi }}</h3>
                    </div>

                    @if($mat->isi_materi)
                    <p class="text-xs text-slate-500 line-clamp-2">{{ $mat->isi_materi }}</p>
                    @endif

                    @if($mat->tipe_materi === 'kuis' && $mat->id_quiz)
                        @php
                            $hasil = \App\Models\HasilKuisSiswa::where('id_quiz', $mat->id_quiz)
                                                               ->where('id_siswa', $siswaId)
                                                               ->first();
                        @endphp
                        @if($hasil)
                            <div class="pt-1 flex items-center gap-2 text-xs font-semibold text-emerald-600">
                                <i class="fas fa-check-circle"></i>
                                <span>Sudah Selesai: Nilai <strong>{{ number_format($hasil->nilai_akhir, 0) }} / 100</strong> (Benar: {{ $hasil->jumlah_benar }}, Salah: {{ $hasil->jumlah_salah }})</span>
                            </div>
                        @else
                            <div class="pt-1 flex items-center gap-1.5 text-xs text-amber-600 font-medium">
                                <i class="fas fa-circle-exclamation"></i>
                                <span>Belum dikerjakan (5 Soal Pilihan Ganda Acak)</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="shrink-0 self-end sm:self-center">
                <a href="{{ route('siswa.materi.view', $mat->id_materi) }}" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                    @if($mat->tipe_materi === 'video')
                        <i class="fas fa-play text-[10px]"></i> Tonton Video
                    @elseif($mat->tipe_materi === 'dokumen')
                        <i class="fas fa-book-reader text-[10px]"></i> Baca PDF
                    @else
                        <i class="fas fa-pen-to-square text-[10px]"></i> Buka Kuis
                    @endif
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-line p-12 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 text-xl mb-3">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Materi</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Sub-Bab ini belum memiliki konten materi dari guru.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
