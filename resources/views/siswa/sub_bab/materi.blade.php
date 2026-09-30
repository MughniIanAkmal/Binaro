@extends('layouts.siswa')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium flex-wrap">
        <a href="{{ route('siswa.mapel.index') }}" class="hover:text-[#13527D] transition">Mata Pelajaran</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('siswa.materi.index', $subBab->bab->mataPelajaran->id_mapel) }}" class="hover:text-[#13527D] transition">
            {{ $subBab->bab->mataPelajaran->nama_mapel }}
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-500">{{ $subBab->bab->nama_bab }}</span>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-800 font-bold">{{ $subBab->nama_sub_bab }}</span>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-[#13527D]/10 text-[#13527D] flex items-center justify-center text-xl font-bold">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    {{ $subBab->bab->mataPelajaran->nama_mapel }} &bull; {{ $subBab->bab->nama_bab }}
                </span>
                <h1 class="text-lg font-bold text-slate-900 mt-0.5">{{ $subBab->nama_sub_bab }}</h1>
                <p class="text-xs text-slate-500">Pilih salah satu materi di bawah ini untuk mulai belajar.</p>
            </div>
        </div>

        <div>
            <a href="{{ route('siswa.materi.index', $subBab->bab->mataPelajaran->id_mapel) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Kembali ke Sub-Bab
            </a>
        </div>
    </div>

    <!-- Daftar Konten Materi -->
    <div class="space-y-4">
        @forelse($subBab->materi as $mIdx => $mat)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-[#13527D]/50 transition p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
                <a href="{{ route('siswa.materi.view', $mat->id_materi) }}" class="px-4 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
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
