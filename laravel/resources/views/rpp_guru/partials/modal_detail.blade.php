<!-- ================= MODAL DETAIL RPP ================= -->
<div id="modal-detail-rpp" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#13527D] text-white p-5 flex items-center justify-between shrink-0">
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

            <!-- Capaian Pembelajaran -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-1.5">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">CAPAIAN PEMBELAJARAN (TP)</span>
                <p class="text-slate-800 text-xs leading-relaxed" id="detail-tp">
                    -
                </p>
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

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="button" onclick="closeModalDetailRpp()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

