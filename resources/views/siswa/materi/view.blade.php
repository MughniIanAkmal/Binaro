@extends('layouts.siswa')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Navigation -->
    <a href="{{ route('siswa.sub_bab.materi', $materi->id_sub_bab) }}" class="text-xs text-[#13527D] hover:underline flex items-center gap-1.5 font-semibold">
        <i class="fas fa-arrow-left text-[10px]"></i> Kembali ke Daftar Konten Materi
    </a>

    <!-- Card Container -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                    {{ $materi->tipe_materi }}
                </span>
                <h1 class="text-lg font-bold text-slate-900 mt-1">{{ $materi->judul_materi }}</h1>
                <p class="text-xs text-slate-500">
                    {{ $materi->subBab?->bab?->mataPelajaran?->nama_mapel }} &bull; {{ $materi->subBab?->bab?->nama_bab }}
                </p>
            </div>
        </div>

        @if($materi->isi_materi)
        <div class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-lg border border-slate-100">
            {!! nl2br(e($materi->isi_materi)) !!}
        </div>
        @endif

        <!-- Content Renderer based on Tipe -->
        @if($materi->tipe_materi === 'video')
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-circle-play text-rose-600"></i> Video Pembelajaran
                </h3>
                @php
                    $url = $materi->url_video;
                    $embedUrl = null;
                    if (str_contains($url, 'youtube.com/watch?v=')) {
                        $videoId = explode('v=', $url)[1] ?? '';
                        $videoId = explode('&', $videoId)[0];
                        $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                    } elseif (str_contains($url, 'youtu.be/')) {
                        $videoId = explode('youtu.be/', $url)[1] ?? '';
                        $videoId = explode('?', $videoId)[0];
                        $embedUrl = "https://www.youtube.com/embed/" . $videoId;
                    }
                @endphp

                @if($embedUrl)
                <div class="aspect-video w-full rounded-xl overflow-hidden shadow-sm border border-slate-200">
                    <iframe class="w-full h-full" src="{{ $embedUrl }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                @else
                <video controls class="w-full rounded-xl border border-slate-200">
                    <source src="{{ $materi->url_video }}" type="video/mp4">
                    Browser Anda tidak mendukung player video ini.
                </video>
                @endif
            </div>

        @elseif($materi->tipe_materi === 'dokumen')
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-file-pdf text-rose-600"></i> Dokumen PDF
                    </h3>
                    @if($materi->file_pdf)
                    <a href="{{ asset('storage/' . $materi->file_pdf) }}" target="_blank" download class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition flex items-center gap-1">
                        <i class="fas fa-download text-[10px]"></i> Unduh PDF
                    </a>
                    @endif
                </div>

                @if($materi->file_pdf)
                <div class="w-full h-[600px] rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                    <iframe src="{{ asset('storage/' . $materi->file_pdf) }}" class="w-full h-full" frameborder="0"></iframe>
                </div>
                @else
                <p class="text-xs text-slate-400 italic">File PDF tidak ditemukan.</p>
                @endif
            </div>

        @elseif($materi->tipe_materi === 'kuis')
            <div class="p-6 bg-slate-50 border border-slate-200 rounded-xl space-y-4 text-center">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl mx-auto flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-brain"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Kuis Evaluasi Sub-Bab</h3>
                    <p class="text-xs text-slate-500 mt-1">Sistem akan menampilkan 5 soal acak pilihan ganda.</p>
                </div>

                @if($hasilKuis)
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg max-w-sm mx-auto space-y-2 text-xs">
                    <p class="font-bold"><i class="fas fa-check-circle text-emerald-600 mr-1"></i> Anda telah menyelesaikan kuis ini!</p>
                    <p class="text-sm font-black text-emerald-900">Skor Akhir: {{ $hasilKuis->nilai_akhir }} / 100</p>
                    <p class="text-[11px] text-emerald-700">Benar: {{ $hasilKuis->jumlah_benar }} &bull; Salah: {{ $hasilKuis->jumlah_salah }}</p>
                    <div class="pt-2">
                        <a href="{{ route('siswa.quiz.result', $materi->id_quiz) }}" class="inline-block px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg">
                            Lihat Rincian Hasil
                        </a>
                    </div>
                </div>
                @else
                @if($materi->id_quiz)
                <a href="{{ route('siswa.quiz.play', $materi->id_quiz) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <i class="fas fa-play"></i> Mulai Kerjakan Kuis Sekarang
                </a>
                @else
                <p class="text-xs text-slate-400 italic">Kuis belum dikonfigurasi oleh Guru.</p>
                @endif
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
