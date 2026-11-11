<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 print:hidden">
    @foreach([
        ['fa-calendar-day', 'bg-amber-50', 'text-amber-600', 'Hari Ini', $hariIni ?? '-', '', true],
        ['fa-clock', 'bg-sky-50', 'text-[#13527D]', 'Total Sesi / Pekan', $totalSesi ?? 0, ' Sesi', false],
        ['fa-book', 'bg-emerald-50', 'text-emerald-600', 'Mata Pelajaran', $totalMapel ?? 0, ' Pelajaran', false],
        ['fa-door-open', 'bg-purple-50', 'text-purple-600', 'Kelas Diampu', $totalKelas ?? 0, ' Kelas', false],
    ] as [$ikon, $warnaLatar, $warnaTeks, $label, $nilai, $satuan, $tampilkanIndikator])
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $warnaLatar }} {{ $warnaTeks }} flex items-center justify-center text-base shrink-0">
                <i class="fas {{ $ikon }}"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide block">{{ $label }}</span>
                <div class="text-sm font-black text-slate-800 flex items-center gap-1.5">
                    <span>{{ $nilai }}{{ $satuan }}</span>
                    @if($tampilkanIndikator && ($hariIni ?? '') !== 'Minggu')
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>