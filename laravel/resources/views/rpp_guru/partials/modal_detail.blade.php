<!-- ================= MODAL DETAIL RPP ================= -->
<div id="modal-detail-rpp" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl my-8 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-primary text-white p-5 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold flex items-center gap-2" id="detail-title">
                    <i class="fas fa-file-lines text-amber-300"></i> Detail Modul Ajar
                </h3>
                <p class="text-xs text-sky-100 mt-0.5" id="detail-subtitle">
                    {{ config('binaro.nama_sekolah') }} &bull; Portal Guru
                </p>
            </div>
            <button type="button" onclick="closeModalDetailRpp()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Detail Content -->
        <div class="p-6 overflow-y-auto flex-1 space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-4 pb-3 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Mata Pelajaran & Kelas</span>
                    <span class="font-bold text-slate-800 text-sm" id="detail-mapel">-</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Fase & Modul</span>
                    <span class="font-bold text-slate-800 text-sm" id="detail-fase-modul">-</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alokasi Waktu (JP)</span>
                    <span class="font-semibold text-slate-700 flex items-center gap-1.5" id="detail-alokasi">-</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Target Roster Jadwal & Ruang</span>
                    <span class="font-semibold text-slate-700" id="detail-jadwal">-</span>
                </div>
            </div>

            <!-- CP Section -->
            <div id="detail-cp-section" class="hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Capaian Pembelajaran</span>
                    <span class="text-[10px] text-ink-soft" id="detail-cp-elemen"></span>
                </div>
                <div class="p-4 rounded-xl bg-primary-tint border border-primary/10 text-sm text-ink leading-relaxed" id="detail-cp-text">
                    -
                </div>
            </div>

            <!-- TP Section -->
            <div id="detail-tp-section" class="hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tujuan Pembelajaran</span>
                    <span class="text-[10px] text-ink-soft" id="detail-tp-count"></span>
                </div>
                <ol class="space-y-2 list-decimal list-inside" id="detail-tp-list">
                    <!-- Dynamic content -->
                </ol>
            </div>

            <!-- Pertanyaan Pemantik -->
            <div id="detail-pemantik-section" class="hidden">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pertanyaan Pemantik</span>
                <ul class="mt-2 space-y-1.5 list-disc list-inside text-slate-700" id="detail-pemantik-list">
                    <!-- Dynamic content -->
                </ul>
            </div>

            <!-- Langkah Pembelajaran -->
            <div id="detail-langkah-section" class="hidden">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Langkah Pembelajaran</span>
                <div class="mt-2 space-y-3" id="detail-langkah-content">
                    <!-- Dynamic content -->
                </div>
            </div>

            <!-- Media Section -->
            <div id="detail-media-section" class="hidden">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Media & Sumber Belajar</span>
                <div class="mt-2 grid grid-cols-2 gap-3" id="detail-media-content">
                    <!-- Dynamic content -->
                </div>
            </div>

            <!-- Asesmen Section -->
            <div id="detail-asesmen-section" class="hidden">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Rencana Asesmen</span>
                <div class="mt-2 space-y-3" id="detail-asesmen-content">
                    <!-- Dynamic content -->
                </div>
            </div>

            <!-- Diferensiasi Section -->
            <div id="detail-diferensiasi-section" class="hidden">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Diferensiasi</span>
                <div class="mt-2 space-y-2" id="detail-diferensiasi-content">
                    <!-- Dynamic content -->
                </div>
            </div>

            <!-- Status Supervisi -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">STATUS SUPERVISI ADMIN</span>
                        <div id="detail-status-badge">
                            <!-- Filled dynamically -->
                        </div>
                    </div>
                    <div id="detail-download-btn-container">
                        <!-- Dynamic Download Button -->
                    </div>
                </div>
                <div id="detail-status-note">
                    <!-- Status explanatory note / revision note -->
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-slate-600">
                <span>Progres isi:</span>
                <span class="font-bold text-primary" id="detail-progress">-</span>
            </div>
            <div class="flex items-center gap-2">
                <a id="detail-edit-btn" href="#" target="_blank" class="px-3 py-2 rounded-xl border border-line bg-white text-xs font-bold hover:bg-surface-alt hidden">
                    <i class="fas fa-pen mr-1"></i>Edit Wizard
                </a>
                <a id="detail-print-btn" href="#" target="_blank" class="px-3 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-hover">
                    <i class="fas fa-print mr-1"></i>Cetak PDF
                </a>
            </div>
        </div>
    </div>
</div>
