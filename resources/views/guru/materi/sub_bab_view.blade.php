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
                        $embedUrl = $materi->youtube_embed_url;
                        $watchUrl = $materi->youtube_watch_url;
                    @endphp

                    @if($embedUrl)
                    <div class="space-y-3">
                        <div class="relative w-full aspect-video rounded-xl overflow-hidden bg-black shadow-inner border border-slate-200">
                            <iframe 
                                src="{{ $embedUrl }}" 
                                class="w-full h-full border-0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                referrerpolicy="strict-origin-when-cross-origin" 
                                allowfullscreen>
                            </iframe>
                        </div>

                        <!-- Direct YouTube Action Bar & Notice for Restricted Embeds -->
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                            <div class="flex items-start sm:items-center gap-2.5 text-slate-600">
                                <i class="fab fa-youtube text-red-600 text-lg shrink-0 mt-0.5 sm:mt-0"></i>
                                <span class="leading-relaxed">
                                    Jika video YouTube menampilkan <em>"This video is unavailable"</em>, pemilik video membatasi pemutaran di situs lain. Klik tombol di samping untuk membukanya langsung.
                                </span>
                            </div>
                            <a href="{{ $watchUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#FF0000] hover:bg-[#CC0000] text-white font-bold rounded-lg shadow-sm transition shrink-0">
                                <i class="fab fa-youtube text-sm"></i>
                                <span>Buka di YouTube</span>
                                <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        </div>
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
                    <div class="space-y-4">
                        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold shadow-2xs">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800">Preview Dokumen PDF</h4>
                                    <p class="text-[11px] text-slate-400">Klik tombol berikutnya/sebelumnya untuk berpindah halaman dokumen.</p>
                                </div>
                            </div>

                            <!-- Navigation & Zoom Controls -->
                            <div class="flex items-center gap-2 flex-wrap justify-center">
                                <button type="button" id="pdf-prev-{{ $materi->id_materi }}"
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                                    <i class="fas fa-chevron-left text-[10px]"></i> Sebelumnya
                                </button>
                                
                                <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-800">
                                    Halaman <span id="pdf-page-num-{{ $materi->id_materi }}" class="text-[#13527D]">1</span> / <span id="pdf-page-count-{{ $materi->id_materi }}">-</span>
                                </div>

                                <button type="button" id="pdf-next-{{ $materi->id_materi }}"
                                        class="px-3 py-1.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-xs">
                                    Berikutnya <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>

                                <div class="h-4 w-px bg-slate-200 mx-1"></div>

                                <!-- Zoom Controls -->
                                <button type="button" id="pdf-zoomout-{{ $materi->id_materi }}" title="Perkecil"
                                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold transition">
                                    <i class="fas fa-magnifying-glass-minus"></i>
                                </button>
                                <span id="pdf-zoom-level-{{ $materi->id_materi }}" class="text-xs font-bold text-slate-600 min-w-[42px] text-center">100%</span>
                                <button type="button" id="pdf-zoomin-{{ $materi->id_materi }}" title="Perbesar"
                                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold transition">
                                    <i class="fas fa-magnifying-glass-plus"></i>
                                </button>

                                <div class="h-4 w-px bg-slate-200 mx-1"></div>

                                <a href="{{ asset('storage/' . $materi->file_pdf) }}" target="_blank" download
                                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center gap-1.5 shadow-xs transition">
                                    <i class="fas fa-download"></i>
                                    <span>Unduh PDF</span>
                                </a>
                            </div>
                        </div>

                        <!-- Canvas Viewport -->
                        <div class="relative w-full min-h-[500px] max-h-[750px] overflow-auto rounded-2xl border border-slate-200 bg-slate-100/80 p-4 text-center flex items-center justify-center shadow-inner">
                            <div id="pdf-spinner-{{ $materi->id_materi }}" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100/90 z-10 text-xs font-bold text-slate-500 gap-2">
                                <i class="fas fa-circle-notch fa-spin text-2xl text-[#13527D]"></i>
                                <span>Memuat Dokumen PDF...</span>
                            </div>

                            <canvas id="pdf-canvas-{{ $materi->id_materi }}" class="mx-auto rounded-xl shadow-md border border-slate-200 bg-white"></canvas>

                            <div id="pdf-fallback-{{ $materi->id_materi }}" class="hidden w-full h-[600px]">
                                <iframe src="{{ asset('storage/' . $materi->file_pdf) }}" class="w-full h-full rounded-xl" frameborder="0"></iframe>
                            </div>
                        </div>

                        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
                        <script>
                            (function() {
                                if (window.pdfjsLib) {
                                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                                }

                                const matId = '{{ $materi->id_materi }}';
                                const pdfUrl = '{{ asset("storage/" . $materi->file_pdf) }}';
                                let pdfDoc = null,
                                    pageNum = 1,
                                    pageRendering = false,
                                    pageNumPending = null,
                                    scale = 1.0;

                                const canvas = document.getElementById('pdf-canvas-' + matId);
                                if (!canvas) return;
                                const ctx = canvas.getContext('2d');
                                const spinner = document.getElementById('pdf-spinner-' + matId);
                                const fallback = document.getElementById('pdf-fallback-' + matId);
                                const pageNumSpan = document.getElementById('pdf-page-num-' + matId);
                                const pageCountSpan = document.getElementById('pdf-page-count-' + matId);
                                const prevBtn = document.getElementById('pdf-prev-' + matId);
                                const nextBtn = document.getElementById('pdf-next-' + matId);
                                const zoomLevel = document.getElementById('pdf-zoom-level-' + matId);

                                function renderPage(num) {
                                    pageRendering = true;
                                    if (spinner) spinner.classList.remove('hidden');

                                    pdfDoc.getPage(num).then(function(page) {
                                        let viewport = page.getViewport({ scale: scale });
                                        canvas.height = viewport.height;
                                        canvas.width = viewport.width;

                                        let renderContext = {
                                            canvasContext: ctx,
                                            viewport: viewport
                                        };
                                        let renderTask = page.render(renderContext);

                                        renderTask.promise.then(function() {
                                            pageRendering = false;
                                            if (spinner) spinner.classList.add('hidden');
                                            if (pageNumPending !== null) {
                                                renderPage(pageNumPending);
                                                pageNumPending = null;
                                            }
                                        });
                                    }).catch(function(err) {
                                        console.error("Error rendering PDF page:", err);
                                        if (spinner) spinner.classList.add('hidden');
                                    });

                                    if (pageNumSpan) pageNumSpan.textContent = num;
                                    if (prevBtn) prevBtn.disabled = (num <= 1);
                                    if (nextBtn) nextBtn.disabled = (pdfDoc && num >= pdfDoc.numPages);
                                }

                                function queueRenderPage(num) {
                                    if (pageRendering) {
                                        pageNumPending = num;
                                    } else {
                                        renderPage(num);
                                    }
                                }

                                if (prevBtn) {
                                    prevBtn.addEventListener('click', function() {
                                        if (pageNum <= 1) return;
                                        pageNum--;
                                        queueRenderPage(pageNum);
                                    });
                                }

                                if (nextBtn) {
                                    nextBtn.addEventListener('click', function() {
                                        if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
                                        pageNum++;
                                        queueRenderPage(pageNum);
                                    });
                                }

                                const zoomInBtn = document.getElementById('pdf-zoomin-' + matId);
                                if (zoomInBtn) {
                                    zoomInBtn.addEventListener('click', function() {
                                        if (scale >= 2.5) return;
                                        scale += 0.2;
                                        if (zoomLevel) zoomLevel.textContent = Math.round(scale * 100) + '%';
                                        queueRenderPage(pageNum);
                                    });
                                }

                                const zoomOutBtn = document.getElementById('pdf-zoomout-' + matId);
                                if (zoomOutBtn) {
                                    zoomOutBtn.addEventListener('click', function() {
                                        if (scale <= 0.6) return;
                                        scale -= 0.2;
                                        if (zoomLevel) zoomLevel.textContent = Math.round(scale * 100) + '%';
                                        queueRenderPage(pageNum);
                                    });
                                }

                                if (window.pdfjsLib) {
                                    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
                                        pdfDoc = pdfDoc_;
                                        if (pageCountSpan) pageCountSpan.textContent = pdfDoc.numPages;
                                        renderPage(pageNum);
                                    }).catch(function(error) {
                                        console.warn("PDF.js load failed:", error);
                                        if (spinner) spinner.classList.add('hidden');
                                        if (canvas) canvas.classList.add('hidden');
                                        if (fallback) fallback.classList.remove('hidden');
                                    });
                                }
                            })();
                        </script>
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
