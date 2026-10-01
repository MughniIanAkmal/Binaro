@extends('layouts.guru')

@section('content')
<div class="p-8 space-y-6 pb-12">

    <!-- Top Bar / Breadcrumb & Header Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <span>Portal Guru</span>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-[#13527D] font-bold">RPP & Modul Ajar</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Rencana Pelaksanaan Pembelajaran (RPP / Modul Ajar)
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Panduan mengajar terpadu kurikulum merdeka terintegrasi jadwal kelas & materi interaktif proyektor SD Merdeka 01.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <button type="button" onclick="openModalTambahRpp()"
                    class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Buat Modul Ajar Baru</span>
            </button>
            <button type="button" onclick="window.print()"
                    class="px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2">
                <i class="fas fa-print text-slate-400"></i>
                <span>Cetak Rekap</span>
            </button>
        </div>
    </div>

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
               class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ ($tab ?? 'semua') === 'semua' ? 'bg-[#13527D] text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                Semua RPP ({{ $kpiGuru['total'] }})
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'menunggu'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ ($tab ?? '') === 'menunggu' ? 'bg-[#13527D] text-white font-bold shadow-xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
                <i class="fas fa-clock text-[11px] {{ ($tab ?? '') === 'menunggu' ? 'text-white' : 'text-amber-500' }}"></i>
                <span>Menunggu Persetujuan Admin ({{ $kpiGuru['menunggu_review'] }})</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'disetujui'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ ($tab ?? '') === 'disetujui' ? 'bg-[#13527D] text-white font-bold shadow-xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200' }}">
                <i class="fas fa-check-circle text-[11px] {{ ($tab ?? '') === 'disetujui' ? 'text-white' : 'text-emerald-500' }}"></i>
                <span>Disetujui Admin / Siap Ajar ({{ $kpiGuru['siap_ajar'] }})</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'revisi'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ ($tab ?? '') === 'revisi' ? 'bg-[#13527D] text-white font-bold shadow-xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200' }}">
                <i class="fas fa-triangle-exclamation text-[11px] {{ ($tab ?? '') === 'revisi' ? 'text-white' : 'text-rose-500' }}"></i>
                <span>Perlu Revisi ({{ $kpiGuru['perlu_revisi'] }})</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'draft'])) }}"
               class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ ($tab ?? '') === 'draft' ? 'bg-[#13527D] text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                Draf ({{ $kpiGuru['draft'] }})
            </a>
        </div>

        <!-- Kurikulum Merdeka Badge -->
        <div class="shrink-0">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-white text-slate-700 border border-slate-200/80 shadow-xs">
                <i class="fas fa-shield-halved text-emerald-500 text-sm"></i>
                <span>Kurikulum Merdeka 2024/2025 &bull; Fase B</span>
            </span>
        </div>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('guru.rpp.index') }}" method="GET" class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
        <input type="hidden" name="tab" value="{{ $tab ?? 'semua' }}">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <!-- Search Input -->
            <div class="md:col-span-4 relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari judul RPP, KD, topik materi pembelajaran..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-[#13527D] transition">
            </div>

            <!-- Dropdown Filter Jadwal -->
            <div class="md:col-span-3">
                <div class="relative">
                    <i class="fas fa-calendar-alt absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <select name="hari" onchange="this.form.submit()"
                            class="w-full pl-8 pr-8 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-[#13527D] transition appearance-none font-medium text-slate-700 cursor-pointer">
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
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-[#13527D] transition appearance-none font-medium text-slate-700 cursor-pointer">
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
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-[#13527D] transition appearance-none font-medium text-slate-700 cursor-pointer">
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
                <button type="submit" title="Terapkan Filter" class="p-2 w-9 h-9 rounded-xl bg-[#13527D] text-white hover:bg-[#0E3D5D] transition flex items-center justify-center text-xs">
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

                $checklist = $rpp->komponen_checklist ?? [];
                $tags = $checklist['tags'] ?? [];
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
                        <span class="text-xs font-bold text-[#13527D]">
                            {{ $rpp->mataPelajaran->nama_mapel ?? 'Mata Pelajaran' }} &bull; {{ $rpp->kelas->pararel ?? 'Kelas 4B' }}
                        </span>
                    </div>

                    <!-- Sub-header: Fase & Modul / Target Date + 3-dots Menu -->
                    <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                        <span class="font-medium">
                            {{ $rpp->fase ?? 'Fase B' }} &bull; {{ $rpp->modul_ke ?? 'Modul #' . $rpp->id_rpp }}
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
                                <button type="button" onclick="openModalEditRpp({{ json_encode($rpp) }})" class="w-full text-left px-3.5 py-1.5 hover:bg-slate-50 flex items-center gap-2">
                                    <i class="fas fa-pen text-slate-400"></i> Edit Modul
                                </button>
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
                            {{ $rpp->ruang ?? 'Ruang Kelas 4B (Gedung Melati)' }}
                        </div>
                    </div>
                </div>

                <!-- 3. Content Details: Thumbnail + Title & Teacher Meta -->
                <div class="flex items-start gap-4">
                    <!-- Thumbnail Box -->
                    <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 flex items-center justify-center border border-slate-200 shadow-xs
                        {{ $isMatematika ? 'bg-amber-50 text-amber-600' : ($isIpa ? 'bg-emerald-50 text-emerald-600' : ($isBindo ? 'bg-sky-50 text-sky-600' : 'bg-indigo-50 text-indigo-600')) }}">
                        @if($isMatematika)
                            <div class="text-center p-1">
                                <i class="fas fa-pizza-slice text-xl block mb-0.5"></i>
                                <span class="text-[9px] font-black uppercase">Pecahan</span>
                            </div>
                        @elseif($isIpa)
                            <div class="text-center p-1">
                                <i class="fas fa-worm text-xl block mb-0.5"></i>
                                <span class="text-[9px] font-black uppercase">Metamorf</span>
                            </div>
                        @elseif($isBindo)
                            <div class="text-center p-1">
                                <i class="fas fa-book-open-reader text-xl block mb-0.5"></i>
                                <span class="text-[9px] font-black uppercase">Cerita</span>
                            </div>
                        @elseif($isPancasila)
                            <div class="text-center p-1">
                                <i class="fas fa-scale-balanced text-xl block mb-0.5"></i>
                                <span class="text-[9px] font-black uppercase">{{ $rpp->modul_ke ?? 'Bab 4' }}</span>
                            </div>
                        @else
                            <div class="text-center p-1">
                                <i class="fas fa-file-lines text-xl block mb-0.5"></i>
                                <span class="text-[9px] font-black uppercase">Modul</span>
                            </div>
                        @endif
                    </div>

                    <!-- Title & Meta -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 leading-snug hover:text-[#13527D] transition cursor-pointer" onclick="openModalDetailRpp({{ $rpp->id_rpp }})">
                            {{ $rpp->judul_rpp }}
                        </h3>
                        <p class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-clock text-slate-400 text-[10px]"></i>
                                <span>{{ $rpp->alokasi_waktu ?? '2 JP (2 x 35 Menit)' }}</span>
                            </span>
                            <span>&bull;</span>
                            <span>{{ $rpp->guru->nama_guru ?? 'Ibu Sarah Wijaya, S.Pd.' }}</span>
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

                <!-- 5. Checklist Components & Tags -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    @if(!empty($tags) && count($tags) > 0)
                        @foreach($tags as $tag)
                            @php
                                $isMissing = str_contains(strtolower($tag), 'belum') || str_contains(strtolower($tag), 'soal latihan proyektor');
                                $isProcess = str_contains(strtolower($tag), 'proses');
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold flex items-center gap-1
                                {{ $isMissing ? 'bg-rose-50 text-rose-600 border border-rose-200' : ($isProcess ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/70') }}">
                                @if($isMissing)
                                    <i class="fas fa-times text-[9px]"></i>
                                @elseif($isProcess)
                                    <i class="fas fa-clock text-[9px]"></i>
                                @else
                                    <i class="fas fa-check text-[9px]"></i>
                                @endif
                                <span>{{ $tag }}</span>
                            </span>
                        @endforeach
                    @else
                        <!-- Default Badges from Boolean Checklist -->
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold flex items-center gap-1 {{ ($checklist['tujuan'] ?? true) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fas fa-check text-[9px]"></i> Tujuan Pembelajaran
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold flex items-center gap-1 {{ ($checklist['video'] ?? false) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fas fa-play text-[9px]"></i> Video Interaktif
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold flex items-center gap-1 {{ ($checklist['kktp'] ?? true) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fas fa-chart-simple text-[9px]"></i> Kriteria KKTP
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold flex items-center gap-1 {{ ($checklist['lkpd'] ?? true) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-rose-50 text-rose-600 border border-rose-200' }}">
                            <i class="fas fa-file-pen text-[9px]"></i> {{ ($checklist['lkpd'] ?? true) ? 'LKPD Siap Cetak' : 'LKPD Belum Diunggah' }}
                        </span>
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

                        <a href="{{ route('guru.rpp.download', $rpp->id_rpp) }}" title="Cetak PDF / Unduh"
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
                                        class="px-4 py-2 rounded-xl bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
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
                <button type="button" onclick="openModalTambahRpp()" class="px-4 py-2 bg-[#13527D] text-white rounded-xl text-xs font-bold">
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

    {{-- <!-- 3 Bottom Feature Integration Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4">
        <!-- 1. Sinkronisasi Roster Kelas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-900 leading-snug">Sinkronisasi Roster Kelas</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    RPP terhubung otomatis dengan jam mengajar mingguan wali kelas 4B tanpa perlu input manual.
                </p>
            </div>
            <a href="{{ route('guru.jadwal.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-800 flex items-center gap-1.5 transition">
                <span>Buka Jadwal Mengajar</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- 2. Bank Modul Ajar Kemendikbud -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-900 leading-snug">Bank Modul Ajar Kemendikbud</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Salin ribuan modul ajar SD terverifikasi Kurikulum Merdeka resmi langsung ke koleksi RPP Anda.
                </p>
            </div>
            <a href="https://guru.kemdikbud.go.id" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-amber-600 hover:text-amber-800 flex items-center gap-1.5 transition">
                <span>Jelajahi Bank Modul</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- 3. Ekspor Bundle Supervisi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#13527D] flex items-center justify-center text-base">
                    <i class="fas fa-box-archive"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-900 leading-snug">Ekspor Bundle Supervisi</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Unduh seluruh RPP 1 semester format ZIP/DOCX lengkap dengan lembar pengesahan Kepala Sekolah.
                </p>
            </div>
            <button type="button" onclick="alert('Bundle RPP 1 Semester sedang dikompilasi ke arsip ZIP.');" class="text-xs font-bold text-[#13527D] hover:text-[#0E3D5D] flex items-center gap-1.5 transition text-left">
                <span>Unduh Dokumen Bundle</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </button>
        </div>
    </div>

</div> --}}

    <!-- Include Modals RPP (Tambah, Edit, Detail & Script Interaktif) -->
    @include('rpp-guru.modals')

@endsection
