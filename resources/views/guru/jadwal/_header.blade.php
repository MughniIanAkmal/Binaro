<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 text-[11px] text-slate-500 mb-1.5">
            <span>Portal Guru</span><span>/</span>
            <span class="text-[#13527D] font-bold">Jadwal Mengajar</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Jadwal Mengajar</h1>
        <p class="text-xs text-slate-500 mt-0.5">
            Daftar mengajar Anda hari Senin – Sabtu: mengajar apa saja, jam ke-berapa, beserta keterangan jamnya.
            @if(isset($guru) && $guru)
                <span class="font-semibold text-slate-700">{{ $guru->nama_guru }}</span>
            @endif
        </p>
    </div>
    <div class="shrink-0 flex items-center gap-2 print:hidden">
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-[#13527D] hover:bg-slate-50 font-bold text-xs rounded-xl border border-slate-200 shadow-sm transition">
            <i class="fas fa-print"></i><span>Cetak Jadwal</span>
        </button>
    </div>
</div>