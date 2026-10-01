<section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h2 class="font-bold text-sm text-slate-900 mb-3 flex items-center gap-2">
        <i class="fas fa-clipboard-check text-emerald-500"></i> Sudah Absen
        <span id="logCount" class="ml-auto text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 px-2.5 py-1 rounded-full">{{ $tercatatCount }} siswa</span>
    </h2>
    <ul id="log" class="divide-y divide-slate-50">
        @forelse ($hariIni as $a)
        @php
            $st = $a->status ?? 'Hadir';
            $box = $st === 'Izin' ? 'bg-sky-50 text-sky-600' : ($st === 'Sakit' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600');
            $jamCls = $st === 'Izin' ? 'text-sky-600' : ($st === 'Sakit' ? 'text-amber-600' : 'text-emerald-600');
        @endphp
        <li class="flex justify-between items-center gap-3 py-2.5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 shrink-0 {{ $box }} rounded-xl flex items-center justify-center text-sm">
                    <i class="fas fa-user-check"></i>
                </span>
                <div>
                    <div class="font-bold text-xs text-slate-900">{{ $a->siswa->nama_siswa }}</div>
                    <div class="text-[11px] text-slate-400">Kelas {{ $a->siswa->nama_kelas }} &bull; {{ strtolower($st) }}</div>
                </div>
            </div>
            <div class="text-xs font-bold {{ $jamCls }} tabular-nums">{{ $a->waktu_absen?->format('H:i') }}</div>
        </li>
        @empty
        <li class="py-6 text-center text-xs text-slate-400">Belum ada yang absen hari ini.</li>
        @endforelse
    </ul>
    <p id="logEmpty" @if ($hariIni->isNotEmpty()) hidden @endif class="text-[11px] text-slate-400 mt-1">Hasil scan akan muncul di sini secara otomatis.</p>
</section>