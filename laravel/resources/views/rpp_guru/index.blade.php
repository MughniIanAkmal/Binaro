@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'RPP & Modul Ajar')

@section('header_actions')
    <a href="{{ route('guru.rpp.wizard.create') }}"
            class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary"
            >
        <i class="fas fa-plus text-[11px]"></i> Buat Modul Ajar
    </a>
    <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-line hover:bg-slate-50 transition print:hidden">
        <i class="fas fa-print text-[11px]"></i> Cetak Rekap
    </button>
@endsection

@section('content')
<div class="space-y-5 pb-6">

    <!-- 4 KPI / Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total RPP Tersedia -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between transition hover:shadow-sm">
            <div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">TOTAL MODUL RPP</span>
                <div class="text-3xl font-black text-slate-900 mt-1 leading-none">{{ $kpiGuru['total'] }}</div>
                <p class="text-[11px] text-slate-500 font-medium mt-1.5 flex items-center gap-1.5">
                    <span>Modul Ajar Diajukan</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>

        <!-- 2. Siap Ajar (Disetujui Admin) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between transition hover:shadow-sm">
            <div>
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-wider block">DISETUJUI & SIAP AJAR</span>
                <div class="text-3xl font-black text-emerald-700 mt-1 leading-none">{{ $kpiGuru['siap_ajar'] }}</div>
                <p class="text-[11px] text-emerald-600 font-medium mt-1.5 flex items-center gap-1.5">
                    <i class="fas fa-check-double text-[10px]"></i>
                    <span>Telah di-Accept Admin</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>

        <!-- 3. Menunggu Persetujuan Admin -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between transition hover:shadow-sm">
            <div>
                <span class="text-[10px] font-black text-amber-600 uppercase tracking-wider block">MENUNGGU APPROVAL ADMIN</span>
                <div class="text-3xl font-black text-amber-700 mt-1 leading-none">{{ $kpiGuru['menunggu_review'] }}</div>
                <p class="text-[11px] text-amber-600 font-medium mt-1.5 flex items-center gap-1.5">
                    <i class="fas fa-hourglass-half text-[10px]"></i>
                    <span>Belum Aktif (Pending)</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <!-- 4. Perlu Dilengkapi / Revisi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between transition hover:shadow-sm">
            <div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">PERLU REVISI / DRAF</span>
                <div class="text-3xl font-black text-slate-900 mt-1 leading-none">{{ $kpiGuru['perlu_dilengkapi'] }}</div>
                <p class="text-[11px] text-rose-600 font-medium mt-1.5 flex items-center gap-1.5">
                    <span>Revisi: {{ $kpiGuru['perlu_revisi'] }} &bull; Draf: {{ $kpiGuru['draft'] }}</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-pen-to-square"></i>
            </div>
        </div>
    </div>

    <!-- Tab Navigation & Kurikulum Merdeka Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <!-- Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto text-xs font-semibold py-1">
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'semua'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ ($tab ?? 'semua') === 'semua' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                Semua RPP ({{ $kpiGuru['total'] }})
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'menunggu'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ ($tab ?? '') === 'menunggu' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
                <i class="fas fa-clock text-[11px] {{ ($tab ?? '') === 'menunggu' ? 'text-white' : 'text-amber-500' }}"></i>
                <span>Menunggu Persetujuan Admin ({{ $kpiGuru['menunggu_review'] }})</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'disetujui'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ ($tab ?? '') === 'disetujui' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200' }}">
                <i class="fas fa-check-circle text-[11px] {{ ($tab ?? '') === 'disetujui' ? 'text-white' : 'text-emerald-500' }}"></i>
                <span>Disetujui Admin / Siap Ajar ({{ $kpiGuru['siap_ajar'] }})</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'revisi'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ ($tab ?? '') === 'revisi' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200' }}">
                <i class="fas fa-triangle-exclamation text-[11px] {{ ($tab ?? '') === 'revisi' ? 'text-white' : 'text-rose-500' }}"></i>
                <span>Perlu Revisi ({{ $kpiGuru['perlu_revisi'] }})</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'draft'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ ($tab ?? '') === 'draft' ? 'bg-primary text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                Draf ({{ $kpiGuru['draft'] }})
            </a>
        </div>

        <!-- Kurikulum Merdeka Badge -->
        <div class="shrink-0">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-white text-slate-700 border border-slate-200/80 shadow-xs">
                <i class="fas fa-shield-halved text-emerald-500 text-sm"></i>
                <span>Kurikulum Merdeka &bull; RPP disusun per bab</span>
            </span>
        </div>
    </div>

    {{-- Cakupan bab: mana yang sudah punya RPP dan mana yang belum. --}}
    @if(isset($babList) && $babList->isNotEmpty())
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
            <div class="flex items-center gap-2">
                <i class="fas fa-list-check text-primary text-xs"></i>
                <span class="text-xs font-bold text-slate-800">Cakupan Bab</span>
            </div>
            <span class="text-[10px] text-slate-400">
                {{ $rppPerBab->count() }} dari {{ $babList->count() }} bab sudah punya RPP
            </span>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach($babList as $bab)
                @php $sudah = $rppPerBab->get($bab->id_bab); @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-semibold border
                    {{ $sudah ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200' }}"
                    title="{{ $sudah ? 'RPP: '.$sudah->judul_rpp.' ('.$sudah->status.')' : 'Belum ada RPP untuk bab ini' }}">
                    <i class="fas {{ $sudah ? 'fa-circle-check' : 'fa-circle-dashed' }} text-[9px]"></i>
                    <span class="truncate max-w-[220px]">{{ $bab->nama_bab }}</span>
                </span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filter Bar -->
    <form action="{{ route('guru.rpp.index') }}" method="GET" class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
        <input type="hidden" name="tab" value="{{ $tab ?? 'semua' }}">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <!-- Search Input -->
            <div class="md:col-span-4 relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari judul RPP, KD, topik materi pembelajaran..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition">
            </div>

            <!-- Dropdown Filter Jadwal -->
            <div class="md:col-span-3">
                <div class="relative">
                    <i class="fas fa-calendar-alt absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <select name="hari" onchange="this.form.submit()"
                            class="w-full pl-8 pr-8 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition appearance-none font-medium text-slate-700 cursor-pointer">
                        <option value="semua">Filter Jadwal: Semua Hari Aktif</option>
                        <option value="Senin" {{ request('hari') == 'Senin' ? 'selected' : '' }}>Senin (Jam Pagi & Siang)</option>
                        <option value="Selasa" {{ request('hari') == 'Selasa' ? 'selected' : '' }}>Selasa (Jam Sains)</option>
                        <option value="Rabu" {{ request('hari') == 'Rabu' ? 'selected' : '' }}>Rabu (Hari Aktif)</option>
                        <option value="Kamis" {{ request('hari') == 'Kamis' ? 'selected' : '' }}>Kamis (Jam Bahasa)</option>
                        <option value="Jumat" {{ request('hari') == 'Jumat' ? 'selected' : '' }}>Jumat (Jam Singkat)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>
            </div>

            <!-- Dropdown Filter Kelas -->
            <div class="md:col-span-2">
                <div class="relative">
                    <select name="id_rooms" onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition appearance-none font-medium text-slate-700 cursor-pointer">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id_rooms }}" {{ request('id_rooms') == $kelas->id_rooms ? 'selected' : '' }}>
                                {{ $kelas->pararel ?? 'Kelas ' . $kelas->id_rooms }} (Aktif)
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>
            </div>

            <!-- Dropdown Filter Mapel -->
            <div class="md:col-span-2">
                <div class="relative">
                    <select name="id_mapel" onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-primary transition appearance-none font-medium text-slate-700 cursor-pointer">
                        <option value="">Semua Mapel</option>
                        @foreach($mapelList as $mapel)
                            <option value="{{ $mapel->id_mapel }}" {{ request('id_mapel') == $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>
            </div>

            <!-- Action Buttons: Reset / Submit -->
            <div class="md:col-span-1 flex items-center gap-1.5 justify-end">
                <button type="submit" title="Terapkan Filter" class="p-2 w-9 h-9 rounded-xl bg-primary text-white hover:bg-primary-hover transition flex items-center justify-center text-xs">
                    <i class="fas fa-filter"></i>
                </button>
                @if(request()->hasAny(['search', 'hari', 'id_rooms', 'id_mapel']))
                <a href="{{ route('guru.rpp.index') }}" title="Reset Filter" class="p-2 w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition flex items-center justify-center text-xs">
                    <i class="fas fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </div>
    </form>

    <!-- Main Grid: 2 Columns of RPP Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($rppList as $rpp)
            @php
                $status = $rpp->status;
                $isDraft = $status === 'draft';
                $isPending = $status === 'menunggu_review';
                $isApproved = $status === 'terverifikasi';
                $isRevision = $status === 'perlu_revisi';

                $isMatematika = str_contains(strtolower($rpp->mataPelajaran->nama_mapel ?? ''), 'matematika');
                $isIpa = str_contains(strtolower($rpp->mataPelajaran->nama_mapel ?? ''), 'ipa') || str_contains(strtolower($rpp->mataPelajaran->nama_mapel ?? ''), 'alam');
                $isBindo = str_contains(strtolower($rpp->mataPelajaran->nama_mapel ?? ''), 'indonesia');
                $isPancasila = str_contains(strtolower($rpp->mataPelajaran->nama_mapel ?? ''), 'pancasila');

                // 1 Ikon Tetap & Konsisten Berdasarkan Mata Pelajaran (Identitas Visual Dashboard):
                if ($isMatematika) {
                    $topikIcon = 'fas fa-calculator'; // Matematika: Kalkulator
                } elseif ($isIpa) {
                    $topikIcon = 'fas fa-flask'; // IPA: Tabung Lab Sains
                } elseif ($isBindo) {
                    $topikIcon = 'fas fa-book-open'; // Bahasa Indonesia: Buku Edukasi
                } elseif ($isPancasila) {
                    $topikIcon = 'fas fa-scale-balanced'; // Pendidikan Pancasila: Timbangan Norma, Aturan & Keadilan Sosial
                } else {
                    $topikIcon = 'fas fa-file-lines'; // Mapel Lainnya
                }

                // Label dinamis: Gunakan Nomor Modul jika ada, atau singkatan mapel resmi
                if (!empty($rpp->modul_ke)) {
                    $topikLabel = Str::limit(strtoupper($rpp->modul_ke), 9, '');
                } else {
                    $topikLabel = $isIpa ? 'IPA' : ($isMatematika ? 'MTK' : ($isBindo ? 'B.INDO' : ($isPancasila ? 'PPKN' : 'MODUL')));
                }
            @endphp

            <div class="bg-white rounded-2xl border {{ $isPending ? 'border-amber-300 shadow-sm' : ($isApproved ? 'border-emerald-200/80 shadow-xs' : ($isRevision ? 'border-rose-300 shadow-sm' : 'border-slate-200/80 shadow-xs')) }} p-6 flex flex-col justify-between hover:shadow-md transition space-y-4 relative">

                <!-- 1. Card Top Row: Status Badges & Subject Header -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <!-- Left Status Badge -->
                        @if($isPending)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <i class="fas fa-clock text-[10px] text-amber-600"></i> Menunggu Persetujuan Admin
                            </span>
                        @elseif($isApproved)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                <i class="fas fa-circle-check text-[10px] text-emerald-600"></i> Disetujui Admin &bull; Siap Ajar
                            </span>
                        @elseif($isRevision)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-300">
                                <i class="fas fa-triangle-exclamation text-[10px] text-rose-600"></i> Perlu Revisi Admin
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                <i class="fas fa-pencil text-[10px] text-slate-500"></i> Draf Belum Dikirim
                            </span>
                        @endif

                        <!-- Right Subject & Class Badge -->
                        <span class="text-xs font-bold text-primary">
                            {{ $rpp->mataPelajaran->nama_mapel ?? 'Mata Pelajaran' }} &bull; {{ $rpp->kelas->pararel ?? '-' }}
                        </span>
                    </div>

                    <!-- Sub-header: Bab & Fase + 3-dots Menu -->
                    <div class="flex items-center justify-between text-xs text-slate-500 pt-1 gap-2">
                        <span class="font-medium truncate flex items-center gap-1.5 min-w-0">
                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold shrink-0">{{ $rpp->fase ?? 'Fase -' }}</span>
                            <span class="truncate">{{ $rpp->bab->nama_bab ?? 'Bab belum dipilih' }}</span>
                        </span>

                        <!-- Action Dropdown Trigger -->
                        <div class="relative group">
                            <button type="button" class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-700 transition">
                                <i class="fas fa-ellipsis-vertical text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-1 w-40 bg-white border border-slate-200 rounded-xl shadow-lg hidden group-hover:block z-20 py-1.5 text-xs text-slate-700">
                                <button type="button" onclick="openModalDetailRpp({{ $rpp->id_rpp }})" class="w-full text-left px-3.5 py-1.5 hover:bg-slate-50 flex items-center gap-2">
                                    <i class="fas fa-eye text-slate-400"></i> Lihat Detail
                                </button>
                                @if(in_array($rpp->status, ['draft', 'perlu_revisi'], true))
                                <a href="{{ url('/guru/rpp/'.$rpp->id_rpp.'/wizard') }}" class="w-full text-left px-3.5 py-1.5 hover:bg-slate-50 flex items-center gap-2">
                                    <i class="fas fa-pen text-slate-400"></i> Edit Modul Ajar (Wizard)
                                </a>
                                @else
                                <span class="w-full text-left px-3.5 py-1.5 text-slate-400 cursor-not-allowed flex items-center gap-2 select-none">
                                    <i class="fas fa-pen text-slate-300"></i> Edit (sudah ditinjau)
                                </span>
                                @endif
                                <a href="{{ route('guru.rpp.download', $rpp->id_rpp) }}" class="w-full text-left px-3.5 py-1.5 hover:bg-slate-50 flex items-center gap-2">
                                    <i class="fas fa-download text-slate-400"></i> Unduh / Cetak
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <form action="{{ route('guru.rpp.destroy', $rpp->id_rpp) }}" method="POST" onsubmit="return confirm('Hapus RPP ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-left px-3.5 py-1.5 hover:bg-rose-50 text-rose-600 flex items-center gap-2 font-medium">
                                        <i class="fas fa-trash-can text-rose-400"></i> Hapus Modul
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Schedule Banner Box -->
                <div class="p-3.5 rounded-xl flex items-start gap-3 {{ $isDraft ? 'bg-amber-50/70 border border-amber-200/80' : 'bg-sky-50/70 border border-sky-100' }}">
                    <div class="w-7 h-7 rounded-lg {{ $isDraft ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700' }} flex items-center justify-center shrink-0 text-xs">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold {{ $isDraft ? 'text-amber-900' : 'text-sky-950' }} leading-tight">
                            {{ $rpp->target_jadwal ?? 'Senin, 08.00 - 09.30 WIB - Jam ke-1 & 2' }}
                            @if($isDraft)
                                <span class="text-amber-600 font-semibold block text-[11px] mt-0.5">&bull; Perlu Verifikasi Jam</span>
                            @endif
                        </div>
                        <div class="text-[11px] {{ $isDraft ? 'text-amber-700' : 'text-sky-700' }} mt-0.5 truncate">
                            {{ $rpp->ruang ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- 3. Content Details: Thumbnail + Title & Teacher Meta -->
                <div class="flex items-start gap-4">
                    <!-- Thumbnail Box Cerdas (Dinamis Sesuai Topik Pembelajaran) -->
                    <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 flex items-center justify-center border border-slate-200 shadow-xs text-center p-1
                        {{ $isMatematika ? 'bg-amber-50 text-amber-600' : ($isIpa ? 'bg-emerald-50 text-emerald-600' : ($isBindo ? 'bg-sky-50 text-sky-600' : ($isPancasila ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-50 text-slate-600'))) }}">
                        <div class="w-full">
                            <i class="{{ $topikIcon }} text-xl block mb-0.5"></i>
                            <span class="text-[10px] font-black uppercase tracking-tight block truncate max-w-[54px] mx-auto leading-none">
                                {{ $topikLabel }}
                            </span>
                        </div>
                    </div>

                    <!-- Title & Meta -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 leading-snug hover:text-primary transition cursor-pointer" onclick="openModalDetailRpp({{ $rpp->id_rpp }})">
                            {{ $rpp->judul_rpp }}
                        </h3>
                        <p class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-clock text-slate-400 text-[10px]"></i>
                                <span>{{ $rpp->alokasi_waktu ?? '2 JP' }}</span>
                            </span>
                            <span>&bull;</span>
                            <span>{{ $rpp->guru->nama_guru ?? 'Guru' }}</span>
                        </p>
                    </div>
                </div>

                <!-- 4. Capaian Pembelajaran (TP) Box -->
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 space-y-1">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
                        CAPAIAN PEMBELAJARAN (TP)
                    </span>
                    <p class="text-xs text-slate-700 leading-relaxed line-clamp-3">
                        {{ $rpp->deskripsi ?? 'Peserta didik mampu memahami dan mendemonstrasikan capaian pembelajaran materi ini melalui aktivitas kontekstual di kelas.' }}
                    </p>
                </div>

                <!-- 5. Kelengkapan: komponen wajib vs disarankan (desain ringkasan) -->
                @php
                    $wajibKurang = $rpp->komponenWajibKurang();
                    $wajibTerisi = count(\App\Models\Rpp::KOMPONEN_WAJIB) - $wajibKurang->count();
                    $disarankanTerisi = $rpp->jumlahDisarankanTerisi();
                    $totalDisarankan = count(\App\Models\Rpp::KOMPONEN_DISARANKAN);
                @endphp
                <div class="rounded-xl border {{ $wajibKurang->isEmpty() ? 'border-emerald-200/70 bg-emerald-50/40' : 'border-rose-200 bg-rose-50/40' }} p-3.5">
                    <div class="flex items-center justify-between text-[10px] font-bold mb-1.5">
                        <span class="{{ $wajibKurang->isEmpty() ? 'text-emerald-700' : 'text-rose-700' }} flex items-center gap-1">
                            <i class="fas {{ $wajibKurang->isEmpty() ? 'fa-circle-check' : 'fa-triangle-exclamation' }} text-[10px]"></i>
                            {{ $wajibTerisi }}/{{ count(\App\Models\Rpp::KOMPONEN_WAJIB) }} wajib
                        </span>
                        <span class="text-slate-500">{{ $disarankanTerisi }}/{{ $totalDisarankan }} disarankan</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-white/80 overflow-hidden">
                        <div class="h-full {{ $wajibKurang->isEmpty() ? 'bg-emerald-500' : 'bg-rose-500' }}"
                             style="width: {{ $rpp->skorKelengkapan() }}%"></div>
                    </div>
                    @if($wajibKurang->isNotEmpty())
                        <p class="text-[10px] font-semibold text-rose-700 mt-1.5">
                            {{ collect($wajibKurang)->map(fn ($k) => \App\Models\Rpp::KOMPONEN_WAJIB[$k])->first() }} belum lengkap
                        </p>
                    @endif
                </div>

                <!-- 6. Bottom Action Footer -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap">
                    <!-- Left Action Buttons Group -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="openModalDetailRpp({{ $rpp->id_rpp }})"
                                class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fas fa-file-lines text-[11px] text-slate-400"></i>
                            <span>Detail RPP</span>
                        </button>

                        <a href="{{ route('guru.mapel.browse') }}"
                           class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fas fa-calendar-week text-[11px] text-slate-400"></i>
                            <span>Jadwal Mingguan</span>
                        </a>

                        <button type="button" onclick="openModalEditRpp({{ json_encode($rpp) }})" title="Edit RPP"
                                class="w-8 h-8 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 flex items-center justify-center transition">
                            <i class="fas fa-pen text-xs"></i>
                        </button>

                        <a href="{{ route('guru.rpp.download', $rpp->id_rpp) }}" target="_blank" title="Cetak PDF / Unduh"
                           class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fas fa-print text-[11px] text-slate-400"></i>
                            <span>Cetak PDF</span>
                        </a>
                    </div>

                    <!-- Right Primary Action Button -->
                    <div>
                        @if($isApproved)
                            <a href="{{ route('guru.mapel.browse') }}"
                               class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-black flex items-center gap-1.5 shadow-sm transition">
                                <i class="fas fa-chalkboard-user"></i>
                                <span>Mulai Mengajar</span>
                            </a>
                        @elseif($isPending)
                            <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold select-none" title="RPP belum dapat diaplikasikan mengajar karena masih menunggu di-accept oleh Admin">
                                <i class="fas fa-hourglass-half text-amber-600 text-[11px]"></i>
                                <span>Menunggu di-Accept Admin</span>
                            </div>
                        @elseif($isRevision)
                            <button type="button" onclick="openModalEditRpp({{ json_encode($rpp) }})"
                                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
                                <i class="fas fa-pen-to-square"></i>
                                <span>Perbaiki & Kirim Ulang</span>
                            </button>
                        @else
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="openModalEditRpp({{ json_encode($rpp) }})"
                                        class="px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
                                    <i class="fas fa-paper-plane text-[11px]"></i>
                                    <span>Lanjutkan & Kirim ke Admin</span>
                                </button>
                                <form action="{{ route('guru.rpp.destroy', $rpp->id_rpp) }}" method="POST" onsubmit="return confirm('Hapus draf ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold transition">
                                        <i class="fas fa-trash-can mr-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-800">Tidak ada Modul Ajar RPP ditemukan</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Coba sesuaikan kata kunci pencarian atau buat rencana pembelajaran baru untuk semester ini.
                    </p>
                </div>
                <button type="button" onclick="openModalTambahRpp()" class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold">
                    <i class="fas fa-plus mr-1"></i> Buat Modul Ajar Baru
                </button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($rppList->hasPages())
    <div class="pt-4 flex justify-center">
        {{ $rppList->links() }}
    </div>
    @endif


    <!-- Include Modals RPP (Tambah, Edit, Detail & Script Interaktif) -->
    @include('rpp_guru.modals')

@endsection
