@if(($jadwals ?? collect())->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-16 text-center flex flex-col items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-300 text-2xl"><i class="fas fa-calendar-xmark"></i></div>
        <div>
            <h3 class="text-sm font-bold text-slate-700">Belum Ada Jadwal Mengajar</h3>
            <p class="text-xs text-slate-400 mt-1">Jadwal mengajar Anda belum diinput oleh admin.</p>
            <p class="text-xs text-slate-400">Silakan hubungi bagian kurikulum untuk informasi lebih lanjut.</p>
        </div>
    </div>
@else
    <div class="space-y-6" id="konten-jadwal-wrapper">
        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
            @php
                $sesiHari = $jadwalPerHari[$hari] ?? collect();
                $isHariIni = (($hariIni ?? '') === $hari);
            @endphp
            <div class="kartu-hari bg-white rounded-2xl border {{ $isHariIni ? 'border-[#13527D]' : 'border-slate-200' }} shadow-sm overflow-hidden" data-hari="{{ $hari }}" style="{{ $isHariIni ? 'box-shadow: 0 0 0 3px rgba(19,82,125,0.08);' : '' }}">
                <div class="px-6 py-4 {{ $isHariIni ? 'bg-gradient-to-r from-sky-50 to-white' : 'bg-slate-50/60' }} border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm" style="{{ $isHariIni ? 'background:#13527D; color:white;' : 'background:white; color:#475569; border:1px solid #e2e8f0;' }}"><i class="fas fa-calendar-day"></i></div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-black text-slate-900">{{ $hari }}</h2>
                                @if($isHariIni)<span class="bg-emerald-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span><span>Hari Ini</span></span>@endif
                            </div>
                            <p class="text-[11px] text-slate-400">{{ $sesiHari->isNotEmpty() ? $sesiHari->count() . ' sesi mengajar terjadwal' : 'Tidak ada jadwal mengajar' }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 hidden sm:inline-block">{{ $sesiHari->isNotEmpty() ? $sesiHari->count() . ' Jam Pelajaran' : '—' }}</span>
                </div>
                <div class="p-5 sm:p-6">
                    @if($sesiHari->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($sesiHari as $idx => $item)
                                @php
                                    $namaMapel = $item->mataPelajaran->nama_mapel ?? '-';
                                    $namaKelas = $item->kelas->pararel ?? $item->kelas->nama_kelas ?? '-';
                                    $jamRange = $item->jam ?? '-';
                                    $iconMapel = 'fa-book-open';
                                    $colorStyle = 'background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;';
                                    $nm = strtolower($namaMapel);
                                    if (str_contains($nm, 'matematika')) { $iconMapel = 'fa-calculator'; $colorStyle = 'background:#fffbeb; color:#b45309; border-color:#fde68a;'; }
                                    elseif (str_contains($nm, 'alam') || str_contains($nm, 'ipa')) { $iconMapel = 'fa-flask'; $colorStyle = 'background:#f0fdf4; color:#15803d; border-color:#bbf7d0;'; }
                                    elseif (str_contains($nm, 'indonesia')) { $iconMapel = 'fa-pen-nib'; $colorStyle = 'background:#fff1f2; color:#be123c; border-color:#fecdd3;'; }
                                    elseif (str_contains($nm, 'pancasila') || str_contains($nm, 'pkn')) { $iconMapel = 'fa-shield-halved'; $colorStyle = 'background:#faf5ff; color:#7e22ce; border-color:#e9d5ff;'; }
                                    elseif (str_contains($nm, 'inggris')) { $iconMapel = 'fa-globe'; $colorStyle = 'background:#fff7ed; color:#c2410c; border-color:#fed7aa;'; }
                                    elseif (str_contains($nm, 'agama')) { $iconMapel = 'fa-star-and-crescent'; $colorStyle = 'background:#fefce8; color:#92400e; border-color:#fde68a;'; }
                                    elseif (str_contains($nm, 'sosial') || str_contains($nm, 'ips')) { $iconMapel = 'fa-earth-asia'; $colorStyle = 'background:#ecfdf5; color:#065f46; border-color:#a7f3d0;'; }
                                    elseif (str_contains($nm, 'seni') || str_contains($nm, 'budaya')) { $iconMapel = 'fa-palette'; $colorStyle = 'background:#fdf4ff; color:#86198f; border-color:#f0abfc;'; }
                                    elseif (str_contains($nm, 'olahraga') || str_contains($nm, 'pjok')) { $iconMapel = 'fa-futbol'; $colorStyle = 'background:#f0fdf4; color:#166534; border-color:#86efac;'; }
                                @endphp
                                <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/50 transition flex flex-col gap-3 shadow-sm">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-mono font-bold"><i class="far fa-clock text-slate-400 text-[10px]"></i><span>{{ $jamRange }} WIB</span></div>
                                        <span class="text-[10px] font-bold text-white bg-[#13527D] px-2 py-1 rounded-md">Jam Ke-{{ $idx + 1 }}</span>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl border flex items-center justify-center text-sm shrink-0" style="{{ $colorStyle }}"><i class="fas {{ $iconMapel }}"></i></div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-slate-900 text-sm truncate">{{ $namaMapel }}</h3>
                                            <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5 truncate"><i class="fas fa-door-open text-[10px] text-slate-400"></i><span>Kelas {{ $namaKelas }}</span></p>
                                        </div>
                                    </div>
                                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <span class="text-[11px] text-slate-500 flex items-center gap-1"><i class="fas fa-hourglass-half text-[10px] text-slate-400"></i><span>Keterangan: pukul <strong class="text-slate-700">{{ $jamRange }}</strong></span></span>
                                        <span class="text-[11px] font-bold text-slate-400">{{ $hari }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center flex flex-col items-center gap-2 text-slate-400">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-300"><i class="fas fa-mug-hot text-sm"></i></div>
                            <p class="text-xs font-medium text-slate-600">Tidak ada jadwal mengajar di hari {{ $hari }}.</p>
                            <p class="text-[11px] text-slate-400">Manfaatkan waktu luang untuk menyiapkan materi & penilaian.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif