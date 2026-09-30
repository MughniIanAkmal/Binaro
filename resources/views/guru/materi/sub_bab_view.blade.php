@extends('layouts.guru')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium flex-wrap">
        <a href="{{ route('guru.mapel.browse') }}" class="hover:text-[#13527D] transition">Mata Pelajaran</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('guru.bab.index', $subBab->bab->mataPelajaran->id_mapel) }}" class="hover:text-[#13527D] transition">
            {{ $subBab->bab->mataPelajaran->nama_mapel }}
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('guru.sub_bab.index', $subBab->bab->id_bab) }}" class="hover:text-[#13527D] transition">
            {{ $subBab->bab->nama_bab }}
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-800 font-bold">{{ $subBab->nama_sub_bab }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900">{{ $subBab->nama_sub_bab }}</h1>
                    <p class="text-xs text-slate-500">
                        {{ $subBab->bab->mataPelajaran->nama_mapel }} &bull; {{ $subBab->bab->nama_bab }}
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.sub_bab.index', $subBab->bab->id_bab) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Kembali ke Sub-Bab
            </a>
            <button onclick="openModalAction('tambah', 'materi', { idMapel: {{ $subBab->bab->mataPelajaran->id_mapel }}, idBab: {{ $subBab->bab->id_bab }}, idSubBab: {{ $subBab->id_sub_bab }} })" class="px-3.5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Konten Materi
            </button>
        </div>
    </div>

    @if(session('error'))
    <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
        <i class="fas fa-exclamation-circle text-rose-500"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(session('success'))
    <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle text-emerald-500"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Render Daftar Konten Materi (3 Tipe) -->
    <div class="space-y-6">
        @forelse($subBab->materi as $materi)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Materi Header Bar -->
            <div class="bg-slate-50/80 px-5 py-3.5 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($materi->tipe_materi === 'video')
                        <span class="px-2.5 py-1 bg-sky-100 text-sky-700 text-[11px] font-bold rounded-md flex items-center gap-1.5">
                            <i class="fas fa-video"></i> Video Pembelajaran
                        </span>
                    @elseif($materi->tipe_materi === 'dokumen')
                        <span class="px-2.5 py-1 bg-rose-100 text-rose-700 text-[11px] font-bold rounded-md flex items-center gap-1.5">
                            <i class="fas fa-file-pdf"></i> Dokumen PDF
                        </span>
                    @elseif($materi->tipe_materi === 'kuis')
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-[11px] font-bold rounded-md flex items-center gap-1.5">
                            <i class="fas fa-circle-question"></i> Kuis Evaluasi
                        </span>
                    @endif
                    <h3 class="text-sm font-bold text-slate-800">{{ $materi->judul_materi }}</h3>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="editMateriDirect({{ $materi->id_materi }})" class="p-1.5 text-slate-500 hover:text-[#13527D] hover:bg-slate-100 rounded-lg transition" title="Edit Materi">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <form action="{{ route('guru.materi.destroy', $materi->id_materi) }}" method="POST" onsubmit="return confirm('Hapus materi ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus Materi">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Content Body per Tipe -->
            <div class="p-5">
                @if($materi->isi_materi)
                <div class="mb-4 text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                    {{ $materi->isi_materi }}
                </div>
                @endif

                @if($materi->tipe_materi === 'video')
                    <!-- Video Embed / Player -->
                    @php
                        $videoUrl = $materi->url_video;
                        $embedUrl = null;
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $videoUrl, $matches)) {
                            $embedUrl = "https://www.youtube-nocookie.com/embed/" . $matches[1] . "?rel=0";
                        }
                    @endphp

                    @if($embedUrl)
                    <div class="relative w-full aspect-video rounded-xl overflow-hidden bg-black shadow-inner border border-slate-200">
                        <iframe src="{{ $embedUrl }}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    @elseif(str_ends_with(strtolower($videoUrl), '.mp4'))
                    <div class="rounded-xl overflow-hidden bg-black shadow-inner">
                        <video controls class="w-full aspect-video">
                            <source src="{{ $videoUrl }}" type="video/mp4">
                            Browser Anda tidak mendukung pemutar video HTML5.
                        </video>
                    </div>
                    @else
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-slate-700 truncate">
                            <i class="fas fa-link text-[#13527D]"></i>
                            <span class="truncate">{{ $videoUrl }}</span>
                        </div>
                        <a href="{{ $videoUrl }}" target="_blank" class="px-3 py-1 bg-[#13527D] text-white rounded-md font-semibold text-[11px] shrink-0">
                            Buka Link Video <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                    @endif

                @elseif($materi->tipe_materi === 'dokumen')
                    <!-- PDF Viewer -->
                    @if($materi->file_pdf)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file-pdf text-rose-500 text-lg"></i>
                                <span class="font-medium text-slate-700">Dokumen Materi Pembelajaran (PDF)</span>
                            </div>
                            <a href="{{ asset('storage/' . $materi->file_pdf) }}" target="_blank" download class="px-3 py-1.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-lg font-semibold text-xs flex items-center gap-1.5 transition">
                                <i class="fas fa-download"></i> Unduh PDF
                            </a>
                        </div>
                        <div class="w-full h-[500px] rounded-xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                            <iframe src="{{ asset('storage/' . $materi->file_pdf) }}#toolbar=0" class="w-full h-full border-0"></iframe>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-slate-400 italic">File PDF belum diunggah.</p>
                    @endif

                @elseif($materi->tipe_materi === 'kuis')
                    <!-- Info Kuis & Bank Soal -->
                    @if($materi->quiz)
                    <div class="bg-emerald-50/50 border border-emerald-200/80 rounded-xl p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Kuis Interaktif</span>
                                <h4 class="text-sm font-bold text-slate-900">{{ $materi->quiz->judul_quiz }}</h4>
                                <div class="flex items-center gap-4 text-xs text-slate-600 pt-1">
                                    <span><i class="fas fa-list-check text-emerald-600 mr-1"></i> {{ $materi->quiz->soal?->count() ?? 0 }} Soal di Bank</span>
                                    <span>&bull;</span>
                                    <span><i class="fas fa-users text-sky-600 mr-1"></i> {{ ($materi->quiz->hasil ?? $materi->quiz->hasilSiswa)?->count() ?? 0 }} Siswa Telah Mengerjakan</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('guru.quiz.bank', $materi->quiz->id_quiz) }}" class="px-3.5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                                    <i class="fas fa-tasks"></i> Kelola Bank Soal
                                </a>
                                <a href="{{ route('guru.quiz.rekap', ['quiz_id' => $materi->quiz->id_quiz]) }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                                    <i class="fas fa-chart-column"></i> Rekap Nilai
                                </a>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-slate-400 italic">Kuis belum dikonfigurasi.</p>
                    @endif
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 text-xl mb-3">
                <i class="fas fa-layer-group"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Materi</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Sub-Bab ini belum memiliki konten materi. Klik Tambah Konten Materi untuk menambahkan Video, Dokumen PDF, atau Kuis.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
