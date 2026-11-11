<div class="bg-white rounded-2xl border border-slate-200 p-2 shadow-sm print:hidden">
    <div class="flex items-center gap-1.5 overflow-x-auto py-0.5 px-0.5" id="tab-hari-wrapper">
        <button type="button" onclick="pilihHari('semua')" id="tab-btn-semua" class="tab-btn-hari px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0" style="background-color:#13527D; color:white;">
            <i class="fas fa-layer-group text-[10px]"></i><span>Semua Hari</span>
            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold" style="background:rgba(255,255,255,0.2); color:white;">{{ $totalSesi ?? 0 }}</span>
        </button>
        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
            @php
                $countSesi = isset($jadwalPerHari[$hari]) ? $jadwalPerHari[$hari]->count() : 0;
                $isHariIni = (($hariIni ?? '') === $hari);
            @endphp
            <button type="button" onclick="pilihHari('{{ $hari }}')" id="tab-btn-{{ $hari }}" class="tab-btn-hari px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-[#13527D] hover:bg-slate-50 transition flex items-center gap-1.5 shrink-0">
                <span>{{ $hari }}</span>
                @if($isHariIni)<span class="bg-emerald-100 text-emerald-700 text-[9px] font-extrabold px-1.5 py-0.5 rounded-md">Hari Ini</span>@endif
                <span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.5 rounded-full font-bold">{{ $countSesi }}</span>
            </button>
        @endforeach
    </div>
</div>