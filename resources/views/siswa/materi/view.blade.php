@extends('layouts.siswa')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Navigation -->
    <a href="{{ route('siswa.sub_bab.materi', $materi->id_sub_bab) }}" class="text-xs text-[#13527D] hover:underline flex items-center gap-1.5 font-semibold">
        <i class="fas fa-arrow-left text-[10px]"></i> Kembali ke Daftar Konten Materi
    </a>

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-semibold flex items-center gap-2">
        <i class="fas fa-circle-exclamation text-rose-500 text-sm"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

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
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-circle-play text-rose-600"></i> Video Pembelajaran
                    </h3>
                    @if($materi->youtube_id)
                    <a href="{{ $materi->youtube_watch_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#FF0000] hover:bg-[#CC0000] text-white text-xs font-semibold rounded-lg shadow-xs transition">
                        <i class="fab fa-youtube"></i>
                        <span>Buka di YouTube</span>
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                    @endif
                </div>

                @if($materi->youtube_embed_url)
                <div class="aspect-video w-full rounded-xl overflow-hidden shadow-sm border border-slate-200 bg-black">
                    <iframe 
                        class="w-full h-full border-0" 
                        src="{{ $materi->youtube_embed_url }}" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                        referrerpolicy="strict-origin-when-cross-origin" 
                        allowfullscreen>
                    </iframe>
                </div>

                <!-- Guidance for restricted videos -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <i class="fab fa-youtube text-red-600 text-base shrink-0"></i>
                        <span>Jika video menampilkan <em>"This video is unavailable"</em>, klik tombol <strong>Buka di YouTube</strong> untuk memutar langsung.</span>
                    </div>
                    <a href="{{ $materi->youtube_watch_url }}" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-1 font-bold text-red-600 hover:text-red-700 hover:underline shrink-0">
                        Buka Tautan <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                </div>
                @elseif(str_ends_with(strtolower($materi->url_video), '.mp4'))
                <video controls class="w-full rounded-xl border border-slate-200">
                    <source src="{{ $materi->url_video }}" type="video/mp4">
                    Browser Anda tidak mendukung player video ini.
                </video>
                @else
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-slate-700 truncate">
                        <i class="fas fa-link text-[#13527D]"></i>
                        <span class="truncate">{{ $materi->url_video }}</span>
                    </div>
                    <a href="{{ $materi->url_video }}" target="_blank" class="px-3 py-1 bg-[#13527D] text-white rounded-md font-semibold text-[11px] shrink-0">
                        Buka Link Video <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                </div>
                @endif
            </div>

        @elseif($materi->tipe_materi === 'dokumen')
            <div class="space-y-4">
                @if($materi->file_pdf)
                <!-- Interactive PDF Viewer Toolbar -->
                <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold shadow-2xs">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-800">Preview Dokumen PDF</h3>
                            <p class="text-[11px] text-slate-400">Gunakan tombol atau panah keyboard untuk berpindah halaman.</p>
                        </div>
                    </div>

                    <!-- Navigation & Zoom Controls -->
                    <div class="flex items-center gap-2 flex-wrap justify-center">
                        <button type="button" id="pdf-prev-btn" onclick="onPrevPage()"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                            <i class="fas fa-chevron-left text-[10px]"></i> Sebelumnya
                        </button>
                        
                        <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-800">
                            Halaman <span id="pdf-page-num" class="text-[#13527D]">1</span> / <span id="pdf-page-count">-</span>
                        </div>

                        <button type="button" id="pdf-next-btn" onclick="onNextPage()"
                                class="px-3 py-1.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-xs">
                            Berikutnya <i class="fas fa-chevron-right text-[10px]"></i>
                        </button>

                        <div class="h-4 w-px bg-slate-200 mx-1"></div>

                        <!-- Zoom Controls -->
                        <button type="button" onclick="zoomOut()" title="Perkecil"
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold transition">
                            <i class="fas fa-magnifying-glass-minus"></i>
                        </button>
                        <span id="pdf-zoom-level" class="text-xs font-bold text-slate-600 min-w-[42px] text-center">100%</span>
                        <button type="button" onclick="zoomIn()" title="Perbesar"
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold transition">
                            <i class="fas fa-magnifying-glass-plus"></i>
                        </button>

                        <div class="h-4 w-px bg-slate-200 mx-1"></div>

                        <!-- Download Option -->
                        <a href="{{ route('siswa.materi.download', $materi->id_materi) }}"
                           class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-xs">
                            <i class="fas fa-download"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>

                <!-- PDF Canvas Render Area -->
                <div class="relative w-full min-h-[500px] max-h-[750px] overflow-auto rounded-2xl border border-slate-200 bg-slate-100/80 p-4 text-center flex items-center justify-center shadow-inner">
                    <div id="pdf-loading-spinner" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100/90 z-10 text-xs font-bold text-slate-500 gap-2">
                        <i class="fas fa-circle-notch fa-spin text-2xl text-[#13527D]"></i>
                        <span>Memuat Dokumen PDF...</span>
                    </div>

                    <canvas id="pdf-render-canvas" class="mx-auto rounded-xl shadow-md border border-slate-200 bg-white"></canvas>

                    <!-- Fallback if PDF.js fails -->
                    <div id="pdf-fallback-container" class="hidden w-full h-[600px]">
                        <iframe src="{{ asset('storage/' . $materi->file_pdf) }}" class="w-full h-full rounded-xl" frameborder="0"></iframe>
                    </div>
                </div>

                <!-- PDF.js CDN & Interactive Script -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
                <script>
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                    let pdfDoc = null,
                        pageNum = 1,
                        pageRendering = false,
                        pageNumPending = null,
                        scale = 1.0,
                        canvas = document.getElementById('pdf-render-canvas'),
                        ctx = canvas.getContext('2d');

                    const pdfUrl = '{{ asset("storage/" . $materi->file_pdf) }}';

                    function renderPage(num) {
                        pageRendering = true;
                        document.getElementById('pdf-loading-spinner').classList.remove('hidden');

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
                                document.getElementById('pdf-loading-spinner').classList.add('hidden');
                                if (pageNumPending !== null) {
                                    renderPage(pageNumPending);
                                    pageNumPending = null;
                                }
                            });
                        }).catch(function(err) {
                            console.error("Error rendering page:", err);
                            document.getElementById('pdf-loading-spinner').classList.add('hidden');
                        });

                        document.getElementById('pdf-page-num').textContent = num;
                        const prevBtn = document.getElementById('pdf-prev-btn');
                        const nextBtn = document.getElementById('pdf-next-btn');
                        if (prevBtn) {
                            prevBtn.disabled = (num <= 1);
                            if (num <= 1) {
                                prevBtn.classList.add('opacity-40', 'cursor-not-allowed');
                            } else {
                                prevBtn.classList.remove('opacity-40', 'cursor-not-allowed');
                            }
                        }
                        if (nextBtn) {
                            const isLast = (pdfDoc && num >= pdfDoc.numPages);
                            nextBtn.disabled = isLast;
                            if (isLast) {
                                nextBtn.classList.add('opacity-40', 'cursor-not-allowed');
                            } else {
                                nextBtn.classList.remove('opacity-40', 'cursor-not-allowed');
                            }
                        }
                    }

                    function queueRenderPage(num) {
                        if (pageRendering) {
                            pageNumPending = num;
                        } else {
                            renderPage(num);
                        }
                    }

                    function onPrevPage() {
                        if (pageNum <= 1) return;
                        pageNum--;
                        queueRenderPage(pageNum);
                    }

                    function onNextPage() {
                        if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
                        pageNum++;
                        queueRenderPage(pageNum);
                    }

                    function zoomIn() {
                        if (scale >= 2.5) return;
                        scale += 0.2;
                        document.getElementById('pdf-zoom-level').textContent = Math.round(scale * 100) + '%';
                        queueRenderPage(pageNum);
                    }

                    function zoomOut() {
                        if (scale <= 0.6) return;
                        scale -= 0.2;
                        document.getElementById('pdf-zoom-level').textContent = Math.round(scale * 100) + '%';
                        queueRenderPage(pageNum);
                    }

                    // Load Document
                    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
                        pdfDoc = pdfDoc_;
                        document.getElementById('pdf-page-count').textContent = pdfDoc.numPages;
                        renderPage(pageNum);
                    }).catch(function(error) {
                        console.warn("PDF.js load failed, falling back to native iframe:", error);
                        document.getElementById('pdf-loading-spinner').classList.add('hidden');
                        document.getElementById('pdf-render-canvas').classList.add('hidden');
                        document.getElementById('pdf-fallback-container').classList.remove('hidden');
                    });

                    // Keyboard Navigation
                    window.addEventListener('keydown', function(e) {
                        if (e.key === 'ArrowRight') {
                            onNextPage();
                        } else if (e.key === 'ArrowLeft') {
                            onPrevPage();
                        }
                    });
                </script>
                @else
                <div class="p-8 text-center bg-slate-50 border border-slate-200 rounded-2xl text-slate-400 text-xs">
                    <i class="fas fa-file-circle-xmark text-3xl mb-2 text-slate-300"></i>
                    <p class="font-bold">File PDF belum diunggah oleh Guru Pengampu.</p>
                </div>
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
