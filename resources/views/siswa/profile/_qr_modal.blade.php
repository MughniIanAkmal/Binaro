<div id="modal-qr" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full overflow-hidden">
        <div class="bg-[#13527D] px-5 py-4 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm"><i class="fas fa-qrcode mr-2"></i>QR Siswa</h3>
            <button type="button" onclick="toggleModal('modal-qr')" class="text-white/70 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6 text-center">
            <p class="font-bold text-slate-900">{{ $namaSiswa }}</p>
            <p class="text-[11px] text-slate-500 mb-4">{{ $namaKelas }} &bull; {{ $nisn }}</p>
            @if(!empty($qrSvg))
            <img src="{{ $qrSvg }}" alt="QR besar" class="w-64 h-64 mx-auto">
            @endif
            <p class="font-mono font-black tracking-widest mt-3">{{ $siswa->barcode->kode_barcode ?? '-' }}</p>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button type="button" onclick="toggleModal('modal-qr')" class="py-2.5 bg-slate-100 text-xs font-bold rounded-xl">Tutup</button>
                <button type="button" onclick="cetakQR()" class="py-2.5 bg-[#13527D] text-white text-xs font-bold rounded-xl">Cetak</button>
            </div>
        </div>
    </div>
</div>