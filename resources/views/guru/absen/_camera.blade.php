<section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h2 class="font-bold text-sm text-slate-900 mb-1 flex items-center gap-2">
        <i class="fas fa-camera text-[#13527D]"></i> Kamera Scan
    </h2>
    <p class="text-[11px] text-slate-400 mb-4">Arahkan QR di kartu siswa ke dalam bingkai hijau.</p>
    <div class="w-full max-w-[320px] mx-auto mb-2 relative">
        <div id="reader" class="w-full h-[260px] rounded-xl overflow-hidden bg-slate-900 relative"></div>
        <div class="scan-overlay" aria-hidden="true">
            <span class="corner tl"></span>
            <span class="corner tr"></span>
            <span class="corner bl"></span>
            <span class="corner br"></span>
            <div class="scan-laser"></div>
        </div>
    </div>
    <button type="button" id="btnGantiKamera"
        class="w-full max-w-[320px] mx-auto mb-4 flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-slate-500 border border-slate-200 rounded-lg hover:border-[#13527D] hover:text-[#13527D] transition">
        <i class="fas fa-camera-rotate"></i> Ganti kamera
    </button>
    <div id="hasil" role="status" aria-live="polite"
        class="px-3 py-2.5 rounded-xl text-xs font-bold text-center bg-slate-50 text-slate-400 min-h-[44px] flex items-center justify-center">Arahkan QR ke kamera.</div>
</section>