<section class="border border-slate-200 rounded-2xl p-5 bg-slate-50/50">
    <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2 mb-4">
        <span class="w-8 h-8 bg-[#13527D]/10 text-[#13527D] rounded-lg flex items-center justify-center text-sm"><i class="fas fa-qrcode"></i></span>
        QR Siswa
    </h3>
    <div class="bg-white border border-dashed border-slate-300 rounded-xl py-5 flex justify-center">
        @if(!empty($qrSvg))
        <img id="qrImage" src="{{ $qrSvg }}" alt="QR {{ $namaSiswa }}" class="w-48 h-48">
        @else
        <p class="text-xs text-slate-400 py-10">QR belum tersedia.</p>
        @endif
    </div>
    <p class="text-center text-[11px] text-slate-400 mt-3">Kode unik</p>
    <p class="text-center font-mono font-black tracking-widest text-slate-900">{{ $siswa->barcode->kode_barcode ?? '-' }}</p>
    <div class="grid grid-cols-2 gap-2 mt-4">
        <button type="button" onclick="toggleModal('modal-qr')" class="py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl transition">
            <i class="fas fa-expand mr-1.5"></i>Perbesar
        </button>
        <button type="button" onclick="cetakQR()" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition">
            <i class="fas fa-print mr-1.5"></i>Cetak
        </button>
    </div>
</section>