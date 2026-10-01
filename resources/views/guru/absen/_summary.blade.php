<div>
    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Guru / Absensi</p>
    <h1 class="text-xl font-bold text-slate-900">Scan Absen Siswa</h1>
    <p class="text-xs text-slate-500 mt-1">Arahkan QR kartu siswa ke kamera. Jika QR tidak terbaca, ketik kodenya secara manual.</p>
</div>

@php
    $belumCount = max($totalSiswa - $tercatatCount, 0);
    $persen = $totalSiswa ? round($tercatatCount / $totalSiswa * 100) : 0;
@endphp

<section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <h2 class="font-bold text-sm text-slate-900 flex items-center gap-2">
            <i class="fas fa-chart-pie text-[#13527D]"></i> Rekap Absen Hari Ini
        </h2>
        <div class="flex items-center gap-2">
            <span class="text-[11px] text-slate-400 font-semibold">{{ now()->translatedFormat('d M Y') }}</span>
            <a href="{{ route('guru.absensi.rekap') }}" class="text-[11px] font-bold text-[#13527D] hover:underline">Lihat rekap <i class="fas fa-arrow-right text-[10px]"></i></a>
        </div>
    </div>
    <div class="grid grid-cols-3 gap-3 text-center">
        <div class="bg-slate-50 border border-slate-100 rounded-xl py-3">
            <p class="text-xl font-black text-slate-900">{{ $totalSiswa }}</p>
            <p class="text-[10px] text-slate-500 font-semibold mt-0.5">Total Siswa</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-100 rounded-xl py-3">
            <p id="statTercatat" class="text-xl font-black text-emerald-600">{{ $tercatatCount }}</p>
            <p class="text-[10px] text-emerald-600/70 font-semibold mt-0.5">Sudah Absen</p>
            <p class="text-[10px] text-emerald-600/60 mt-0.5">Hadir {{ $hadirCount }} &bull; Izin/Sakit {{ $izinSakitCount }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl py-3">
            <p id="statBelum" class="text-xl font-black text-amber-600">{{ $belumCount }}</p>
            <p class="text-[10px] text-amber-600/70 font-semibold mt-0.5">Belum Absen</p>
        </div>
    </div>
    <div class="h-2 bg-slate-100 rounded-full overflow-hidden mt-4">
        <div id="rekapBar" class="h-full bg-[#13527D] rounded-full transition-all" style="width: {{ $persen }}%"></div>
    </div>
    <p id="rekapPersen" data-total="{{ $totalSiswa }}" class="text-[11px] text-slate-400 mt-1.5">{{ $persen }}% siswa sudah absen hari ini</p>
</section>