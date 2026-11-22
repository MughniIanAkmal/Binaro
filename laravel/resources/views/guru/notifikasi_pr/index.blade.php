@extends('layouts.guru')

@section('page_title', 'Pekerjaan Rumah (PR)')

@section('header_actions')
@endsection

@section('content')
<div class="space-y-5">

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Total PR -->
        <a href="{{ route('guru.notifikasi_pr.index', ['filter' => 'all']) }}"
           class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:border-primary/60 hover:shadow-md transition group block">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $totalPr }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Total Tugas PR</div>
                <div class="text-[11px] text-slate-400">Semua mata pelajaran</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-primary group-hover:bg-primary group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                <i class="fas fa-book-reader"></i>
            </div>
        </a>

        <!-- KPI 2: PR Aktif -->
        <a href="{{ route('guru.notifikasi_pr.index', ['filter' => 'active']) }}"
           class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:border-emerald-600/60 hover:shadow-md transition group block">
            <div>
                <div class="text-2xl font-black text-emerald-600">{{ $prAktif }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">PR Aktif Berjalan</div>
                <div class="text-[11px] text-emerald-600 font-medium">Belum lewat tenggat</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                <i class="fas fa-clock"></i>
            </div>
        </a>

        <!-- KPI 3: Notifikasi Terkirim -->
        <a href="{{ route('guru.notifikasi_pr.index', ['filter' => 'notifications']) }}"
           class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:border-amber-600/60 hover:shadow-md transition group block">
            <div>
                <div class="text-2xl font-black text-amber-500">{{ $totalNotifikasi }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Notifikasi Terkirim</div>
                <div class="text-[11px] text-slate-400">Total pesan ke siswa</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                <i class="fas fa-paper-plane"></i>
            </div>
        </a>

        <!-- KPI 4: Siswa Terjangkau -->
        <a href="{{ route('guru.notifikasi_pr.index', ['filter' => 'students']) }}"
           class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:border-indigo-600/60 hover:shadow-md transition group block">
            <div>
                <div class="text-2xl font-black text-indigo-600">{{ $siswaTerjangkau }} / {{ $totalSiswa }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Siswa Terjangkau</div>
                <div class="text-[11px] text-indigo-500 font-semibold">{{ $readRate }}% Keterbacaan</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition flex items-center justify-center text-xl font-bold">
                <i class="fas fa-user-graduate"></i>
            </div>
        </a>
    </div>

    <!-- Navigation Tabs & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Toolbar Header - Single Row Layout -->
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <!-- Search, Filter & Add Button -->
            <form method="GET" action="{{ route('guru.notifikasi_pr.index') }}" class="flex items-center gap-2 flex-wrap">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari..."
                           class="pl-8 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:border-primary w-40">
                    @if($search)
                        <a href="{{ route('guru.notifikasi_pr.index', ['tab' => $activeTab]) }}"
                           title="Reset pencarian" aria-label="Reset pencarian"
                           class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <i class="fas fa-xmark text-[11px]"></i>
                        </a>
                    @endif
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary shrink-0">
                    <i class="fas fa-search text-[11px]"></i> Cari
                </button>
                <select name="mapel_id" onchange="this.form.submit()" class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:border-primary">
                    <option value="">Semua Mapel</option>
                    @foreach($mapels as $m)
                        <option value="{{ $m->id_mapel }}" {{ $mapelFilter == $m->id_mapel ? 'selected' : '' }}>
                            {{ $m->nama_mapel }}
                        </option>
                    @endforeach
                </select>
                @if($search || $mapelFilter)
                    <a href="{{ route('guru.notifikasi_pr.index', ['tab' => $activeTab]) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-semibold" title="Reset Filter">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </form>

            <!-- Tambah PR Button -->
            <button type="button" onclick="openCreateModal()"
               class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition bg-primary shrink-0">
                <i class="fas fa-plus text-[11px]"></i> Tambah PR
            </button>
        </div>

        <!-- Tab 1: TABEL TUGAS PR -->
        @if($activeTab === 'pr')
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Tugas PR & Mata Pelajaran</th>
                        <th class="py-3 px-4">Deskripsi / Catatan</th>
                        <th class="py-3 px-4">Batas Tenggat</th>
                        <th class="py-3 px-4 text-center">Notifikasi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($prs as $idx => $pr)
                    @php
                        $isExpired = \Carbon\Carbon::parse($pr->tgl_tenggat)->isPast();
                        $diffForHumans = \Carbon\Carbon::parse($pr->tgl_tenggat)->diffForHumans();
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                            {{ $prs->firstItem() + $idx }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <a href="{{ route('guru.notifikasi_pr.show', $pr->id_pr) }}" class="hover:text-primary hover:underline">
                                    {{ $pr->nama_pr }}
                                </a>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="bg-sky-50 text-primary font-bold text-[10px] px-2 py-0.5 rounded border border-sky-100">
                                    {{ $pr->mataPelajaran->nama_mapel ?? 'Mata Pelajaran' }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    <i class="fas fa-user-tie text-[10px] mr-0.5"></i> {{ $pr->guru->nama_guru ?? 'Guru' }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4 max-w-xs text-slate-600">
                            <p class="truncate text-xs">{{ $pr->deskripsi ?: 'Tidak ada instruksi tambahan.' }}</p>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">
                                {{ \Carbon\Carbon::parse($pr->tgl_tenggat)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($pr->tgl_tenggat)->format('H:i') }} WIB
                            </div>
                            <div class="mt-0.5">
                                @if($isExpired)
                                    <span class="bg-rose-50 text-rose-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fas fa-times-circle text-[10px]"></i> Berakhir {{ $diffForHumans }}
                                    </span>
                                @else
                                    <span class="bg-emerald-50 text-emerald-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fas fa-clock text-[10px]"></i> Tenggat {{ $diffForHumans }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('guru.notifikasi_pr.show', $pr->id_pr) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 font-bold hover:bg-amber-100 transition" title="Lihat siswa penerima">
                                <i class="fas fa-paper-plane text-[10px] text-amber-500"></i>
                                <span>{{ $pr->notifikasi_count }} Terkirim</span>
                            </a>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Detail -->
                                <a href="{{ route('guru.notifikasi_pr.show', $pr->id_pr) }}"
                                   class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Detail & Siswa Penerima">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <!-- Edit -->
                                <a href="{{ route('guru.notifikasi_pr.edit', $pr->id_pr) }}"
                                   class="p-1.5 bg-sky-50 hover:bg-sky-100 text-primary rounded-lg transition" title="Edit PR">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </a>
                                <!-- Delete -->
                                <form action="{{ route('guru.notifikasi_pr.destroy', $pr->id_pr) }}" method="POST"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus PR \'{{ addslashes($pr->nama_pr) }}\'? Seluruh notifikasi yang terkirim untuk PR ini juga akan dihapus.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus PR">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <p class="font-bold text-slate-700">Belum ada Pekerjaan Rumah (PR) yang ditambahkan.</p>
                            <p class="text-slate-400 text-xs mt-1">Mulai dengan membuat PR baru dan kirimkan notifikasi kepada siswa.</p>
                            <button type="button" onclick="openCreateModal()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-primary-hover transition">
                                <i class="fas fa-plus"></i> Tambah PR Sekarang
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($prs->hasPages())
        <div class="pt-4 border-t border-slate-100">
            {{ $prs->links() }}
        </div>
        @endif

        @else
        <!-- Tab 2: TABEL LOG NOTIFIKASI (dikelompokkan per batch pengiriman) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Siswa Penerima</th>
                        <th class="py-3 px-4">Tugas PR Terkait</th>
                        <th class="py-3 px-4">Pesan Notifikasi</th>
                        <th class="py-3 px-4 text-center">Status Baca</th>
                        <th class="py-3 px-4">Waktu Dikirim</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($grupNotifikasi ?? [] as $gi => $grup)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                            {{ $gi + 1 }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                <i class="fas fa-users text-primary text-[11px]"></i>
                                {{ $grup['label'] }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $grup['sub_label'] }}
                            </div>
                            @if($grup['total'] > 1)
                            <button type="button" onclick="openSiswaModal({{ $gi }})"
                                class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 text-primary font-bold hover:bg-sky-100 transition text-[11px] border border-sky-100">
                                <i class="fas fa-list-ul text-[10px]"></i>
                                Lihat {{ $grup['total'] }} siswa
                            </button>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($grup['pr'])
                                <a href="{{ route('guru.notifikasi_pr.show', $grup['id_pr']) }}" class="font-bold text-primary hover:underline block truncate max-w-xs">
                                    {{ $grup['pr']->nama_pr }}
                                </a>
                                <span class="text-[10px] text-slate-400">{{ $grup['pr']->mataPelajaran->nama_mapel ?? '' }}</span>
                            @else
                                <span class="text-slate-400 italic">Tugas PR Dihapus</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 max-w-sm">
                            <p class="line-clamp-2 text-slate-700 leading-relaxed">{{ $grup['pesan'] }}</p>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($grup['total'] === 1)
                                @if($grup['items'][0]['status_baca'])
                                    <span class="bg-emerald-50 text-emerald-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fas fa-check-double text-[10px]"></i> Sudah Dibaca
                                    </span>
                                @else
                                    <span class="bg-amber-50 text-amber-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fas fa-envelope text-[10px]"></i> Belum Dibaca
                                    </span>
                                @endif
                            @else
                                <span class="bg-sky-50 text-primary font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fas fa-check-double text-[10px]"></i> {{ $grup['dibaca'] }} / {{ $grup['total'] }} dibaca
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                            <div class="font-semibold text-slate-700">{{ $grup['waktu_kirim'] ? $grup['waktu_kirim']->translatedFormat('d M Y') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $grup['waktu_kirim'] ? $grup['waktu_kirim']->format('H:i') : '' }} WIB</div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($grup['total'] > 1)
                                <button type="button" onclick="openSiswaModal({{ $gi }})"
                                        class="p-1.5 bg-sky-50 hover:bg-sky-100 text-primary rounded-lg transition" title="Lihat daftar siswa penerima">
                                    <i class="fas fa-users text-xs"></i>
                                </button>
                                @endif
                                <button type="button"
                                        onclick="showPesanGrup({{ $gi }})"
                                        class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Lihat Isi Pesan">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <p class="font-bold text-slate-700">Belum ada riwayat notifikasi PR yang dikirimkan.</p>
                            <p class="text-slate-400 text-xs mt-1">Kirim notifikasi untuk PR yang aktif agar siswa segera mengetahuinya.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-[11px] text-slate-400 pt-3 border-t border-slate-100">
            Menampilkan {{ count($grupNotifikasi ?? []) }} batch pengiriman (dari {{ $notifikasis->total() }} notifikasi ke siswa). Klik “Lihat N siswa” untuk rincian penerima.
        </p>
        @endif
    </div>

</div>

<!-- MODAL 1: TAMBAH PR -->
<div id="modal-create-pr" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all animate-scale-up flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="bg-primary px-6 py-4 text-white flex items-center justify-between shrink-0">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-book-open text-sky-300"></i> Tambah Pekerjaan Rumah (PR) Baru
            </h3>
            <button onclick="closeCreateModal()" class="text-white/70 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">
            <form action="{{ route('guru.notifikasi_pr.store') }}" method="POST" id="form-create-pr-modal" onsubmit="return validateBeforeSubmit()" novalidate>
                @csrf

                <!-- Card 1: Informasi Tugas PR -->
                <div class="space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-9 h-9 rounded-xl bg-sky-50 text-primary flex items-center justify-center font-bold text-sm">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Rincian Pekerjaan Rumah (PR)</h2>
                            <p class="text-[11px] text-slate-400">Isi judul tugas, pilih mata pelajaran, dan tentukan batas pengumpulannya.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Judul PR -->
                        <div class="md:col-span-2">
                            <label for="modal-nama_pr" class="block text-xs font-bold text-slate-700 mb-1">
                                Judul / Nama Tugas PR <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_pr" id="modal-nama_pr" value="" required maxlength="100"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s\-_.,()]/g, ''); document.getElementById('counter-pr-nama').textContent = this.value.length + '/100'; updateLivePreview();"
                                   placeholder="Contoh: Latihan Soal Cerita Operasi Hitung Campuran"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary">
                            <div class="flex items-center justify-between mt-1 text-[10px]">
                                <span class="text-slate-500"><i class="fas fa-shield-halved mr-1 text-primary"></i>Hanya huruf, angka, spasi, dan tanda baca (- . , _ ()). Simbol dilarang.</span>
                                <span id="counter-pr-nama" class="font-bold text-slate-600">0/100</span>
                            </div>
                        </div>

                        <!-- Mata Pelajaran -->
                        <div>
                            <label for="modal-id_mapel" class="block text-xs font-bold text-slate-700 mb-1">
                                Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <select name="id_mapel" id="modal-id_mapel" required onchange="updateLivePreview()"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapels as $mapel)
                                    <option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal & Jam Pengumpulan -->
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="modal-input_tanggal" class="block text-xs font-bold text-slate-700 mb-1">
                                        Batas Waktu Pengumpulan <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" id="modal-input_tanggal" name="tanggal_tenggat"
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ $defaultTanggal ?? date('Y-m-d', strtotime('+1 day')) }}" required
                                           onchange="syncDateTimeAndPreview()"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary">
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-xs font-bold text-slate-700">Jam Pengumpulan <span class="text-rose-500">*</span></label>
                                        <span class="text-[10px] font-bold text-slate-400">07:00 - 14:00 WIB</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <select id="modal-select_jam" onchange="syncDateTimeAndPreview()"
                                                class="w-1/2 px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-primary">
                                            @for($h = 7; $h <= 14; $h++)
                                                @php $hh = sprintf('%02d', $h); @endphp
                                                <option value="{{ $hh }}" {{ $hh == '07' ? 'selected' : '' }}>{{ $hh }}</option>
                                            @endfor
                                        </select>
                                        <span class="font-extrabold text-slate-400">:</span>
                                        <select id="modal-select_menit" onchange="syncDateTimeAndPreview()"
                                                class="w-1/2 px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-primary">
                                            @foreach(['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'] as $mm)
                                                <option value="{{ $mm }}" {{ $mm == '30' ? 'selected' : '' }}>{{ $mm }}</option>
                                            @endforeach
                                        </select>
                                        <span class="px-2 py-2.5 bg-slate-100 text-slate-600 font-extrabold text-[11px] rounded-xl border border-slate-200 shrink-0">WIB</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="tgl_tenggat" id="modal-tgl_tenggat" value="">

                            <div class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200 flex items-center gap-2">
                                <i class="fas fa-school text-primary shrink-0"></i>
                                <span><strong>Jam Operasi Sekolah:</strong> Pukul <strong>07:00 - 14:00 WIB</strong> (Hari Minggu libur). Tidak dapat memilih hari dan jam yang sudah lewat.</span>
                            </div>

                            <div id="modal-alert-tenggat-warning" class="hidden p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                                <i class="fas fa-triangle-exclamation text-rose-500 text-sm shrink-0"></i>
                                <span id="modal-alert-tenggat-text" class="font-medium"></span>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="md:col-span-2">
                            <label for="modal-deskripsi" class="block text-xs font-bold text-slate-700 mb-1">
                                Deskripsi & Petunjuk Pengerjaan
                            </label>
                            <textarea name="deskripsi" id="modal-deskripsi" rows="3" maxlength="1000"
                                      placeholder="Tuliskan petunjuk pengerjaan tugas, nomor halaman buku paket, atau instruksi khusus untuk siswa..."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Pengaturan Notifikasi Siswa -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Pengiriman Pemberitahuan ke Siswa</h2>
                                <p class="text-[11px] text-slate-400">Siswa akan menerima pesan pemberitahuan langsung di akun portal mereka.</p>
                            </div>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="kirim_notifikasi" id="modal-kirim_notifikasi" value="1" checked onchange="toggleNotifSection(this.checked)" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            <span class="ml-2.5 text-xs font-bold text-slate-700">Beritahu Siswa Sekarang</span>
                        </label>
                    </div>

                    <div id="modal-notif-settings-body" class="space-y-4">
                        <!-- Target Penerima -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Siapa yang ingin di beri tahu?</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <label class="border-2 border-primary/30 bg-sky-50/50 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-sky-50 transition" id="modal-lbl-target-semua">
                                    <input type="radio" name="target_tipe" value="semua" checked onchange="handleTargetChange(this.value)" class="text-primary">
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">Semua Siswa</div>
                                        <div class="text-[10px] text-slate-500">Kirim pesan ke seluruh siswa</div>
                                    </div>
                                </label>
                                <label class="border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition" id="modal-lbl-target-kelas">
                                    <input type="radio" name="target_tipe" value="kelas" onchange="handleTargetChange(this.value)" class="text-primary">
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">Per Kelas</div>
                                        <div class="text-[10px] text-slate-500">Pilih kelas tertentu saja</div>
                                    </div>
                                </label>
                                <label class="border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition" id="modal-lbl-target-siswa">
                                    <input type="radio" name="target_tipe" value="siswa" onchange="handleTargetChange(this.value)" class="text-primary">
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">Siswa Tertentu</div>
                                        <div class="text-[10px] text-slate-500">Centang siswa satu per satu</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Dropdown Pilih Kelas -->
                        <div id="modal-target-kelas-container" class="hidden">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kelas <span class="text-rose-500">*</span></label>
                            <select name="target_kelas" id="modal-target_kelas" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id_rooms }}">{{ $k->pararel }} ({{ $k->siswas_count }} Siswa)</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Multi-select Siswa -->
                        <div id="modal-target-siswa-container" class="hidden space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700">Pilih Siswa <span class="text-rose-500">*</span></label>
                            <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl p-2.5 space-y-1 bg-slate-50">
                                @foreach($siswas as $s)
                                <label class="flex items-center gap-2.5 p-1.5 hover:bg-white rounded-lg text-xs cursor-pointer">
                                    <input type="checkbox" name="target_siswa[]" value="{{ $s->id_siswa }}" class="rounded text-primary">
                                    <span class="font-bold text-slate-800">{{ $s->nm_siswa }}</span>
                                    <span class="text-[10px] text-slate-400">NISN: {{ $s->nisn }} &bull; {{ $s->kelas->pararel ?? 'Siswa' }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Kustomisasi Pesan -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="modal-pesan_kustom" class="text-xs font-bold text-slate-700">Isi Pesan yang Akan Dikirim ke Siswa</label>
                                <button type="button" onclick="resetDefaultPesan()" class="text-[10px] font-bold text-primary hover:underline">
                                    <i class="fas fa-magic mr-1"></i> Gunakan Format Otomatis
                                </button>
                            </div>
                            <textarea name="pesan_kustom" id="modal-pesan_kustom" rows="3"
                                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary"
                                      placeholder="Ketik pesan pemberitahuan Anda sendiri, atau gunakan format otomatis di bawah..."></textarea>
                        </div>

                        <!-- Live Preview -->
                        <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl text-xs space-y-2">
                            <div class="flex items-center gap-2 font-bold text-amber-900 text-[11px] uppercase tracking-wider">
                                <i class="fas fa-mobile-alt"></i> Contoh Pesan yang Diterima Siswa:
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-amber-200/80 shadow-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold text-primary" id="modal-preview-tag-mapel">Matematika</span>
                                    <span class="text-[10px] text-slate-400">Baru Saja</span>
                                </div>
                                <p class="text-xs font-medium text-slate-800 leading-relaxed" id="modal-preview-text-body">
                                    Halo! Ada tugas baru nih — '...'. Yuk segera disusul sesuai batas waktunya! 📚
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
            <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                Batal
            </button>
            <button type="button" onclick="document.getElementById('form-create-pr-modal').requestSubmit()" class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-extrabold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span id="modal-btn-submit-text">Simpan dan Beritahu Siswa</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL 2: DETAIL PESAN NOTIFIKASI -->
<div id="modal-detail-pesan" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all animate-scale-up">
        <div class="bg-slate-800 px-6 py-4 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fas fa-envelope-open-text text-amber-400"></i> Detail Notifikasi Terkirim
            </h3>
            <button onclick="document.getElementById('modal-detail-pesan').classList.add('hidden')" class="text-white/70 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4 text-xs">
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-400">Penerima:</span>
                    <span id="detail-siswa" class="font-bold text-slate-800"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Tugas PR:</span>
                    <span id="detail-pr" class="font-bold text-primary"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Waktu Kirim:</span>
                    <span id="detail-waktu" class="font-semibold text-slate-700"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Status Keterbacaan:</span>
                    <span id="detail-status" class="font-bold"></span>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Isi Pesan:</label>
                <div id="detail-pesan-body" class="p-3.5 bg-amber-50/50 border border-amber-200 rounded-xl text-slate-800 leading-relaxed whitespace-pre-wrap"></div>
            </div>

            <div class="text-right pt-2">
                <button type="button" onclick="document.getElementById('modal-detail-pesan').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: DAFTAR SISWA PENERIMA PER BATCH -->
<div id="modal-daftar-siswa" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-primary px-6 py-4 text-white flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-bold text-sm flex items-center gap-2">
                    <i class="fas fa-users text-sky-300"></i> <span id="siswa-modal-title">Daftar Siswa Penerima</span>
                </h3>
                <p id="siswa-modal-subtitle" class="text-[11px] text-sky-200 mt-0.5"></p>
            </div>
            <button onclick="document.getElementById('modal-daftar-siswa').classList.add('hidden')" class="text-white/70 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 border-b border-slate-100 shrink-0">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" id="siswa-modal-search" oninput="filterSiswaModal()" placeholder="Cari nama / NISN..."
                    class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:border-primary">
            </div>
        </div>
        <div class="p-4 overflow-y-auto flex-1">
            <ul id="siswa-modal-list" class="divide-y divide-slate-100"></ul>
        </div>
        <div class="p-4 border-t border-slate-100 text-right shrink-0">
            <button type="button" onclick="document.getElementById('modal-daftar-siswa').classList.add('hidden')"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-create-pr').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    syncDateTimeAndPreview();
}

function closeCreateModal() {
    document.getElementById('modal-create-pr').classList.add('hidden');
    document.body.style.overflow = '';
    // Reset form
    document.getElementById('form-create-pr-modal').reset();
    updateLivePreview();
    toggleNotifSection(true);
    handleTargetChange('semua');
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modal-create-pr');
    if (e.target === modal) {
        closeCreateModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modal-create-pr');
        if (!modal.classList.contains('hidden')) {
            closeCreateModal();
        }
    }
});

function showPesanModal(siswa, pr, pesan, waktu, status) {
    document.getElementById('detail-siswa').innerText = siswa;
    document.getElementById('detail-pr').innerText = pr;
    document.getElementById('detail-waktu').innerText = waktu;
    document.getElementById('detail-status').innerText = status;
    document.getElementById('detail-status').className = status.includes('Sudah') || /\d+\s*\/\s*\d+/.test(status) ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold';
    document.getElementById('detail-pesan-body').innerText = pesan;
    document.getElementById('modal-detail-pesan').classList.remove('hidden');
}

function showPesanGrup(idx) {
    const g = GRUP_SISWA[idx];
    if (!g) return;
    showPesanModal(g.label || '-', g.nama_pr || '-', g.pesan || '-', g.waktu_label || '-', g.dibaca + ' dari ' + g.total + ' dibaca');
}

const GRUP_SISWA = @json(($grupNotifikasi ?? collect())->values());
const CSRF_TOKEN_SISWA = '{{ csrf_token() }}';
let grupAktifIndex = null;

function escHtml(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, function (c) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
    });
}

function openSiswaModal(idx) {
    grupAktifIndex = idx;
    const g = GRUP_SISWA[idx];
    if (!g) return;
    document.getElementById('siswa-modal-title').innerText = g.label || 'Daftar Siswa Penerima';
    document.getElementById('siswa-modal-subtitle').innerText = (g.pr ? g.pr.nama_pr + ' • ' : '') + g.total + ' penerima • ' + g.dibaca + ' sudah dibaca';
    document.getElementById('siswa-modal-search').value = '';
    renderSiswaList('');
    document.getElementById('modal-daftar-siswa').classList.remove('hidden');
}

function renderSiswaList(keyword) {
    const g = GRUP_SISWA[grupAktifIndex];
    const ul = document.getElementById('siswa-modal-list');
    if (!g) { ul.innerHTML = ''; return; }
    const kw = (keyword || '').toLowerCase();
    const filtered = (g.items || []).filter(function (s) {
        return !kw || (s.nama || '').toLowerCase().includes(kw) || (s.nisn || '').toLowerCase().includes(kw) || (s.kelas || '').toLowerCase().includes(kw);
    });
    if (!filtered.length) {
        ul.innerHTML = '<li class="py-8 text-center text-xs text-slate-400">Tidak ada siswa yang cocok.</li>';
        return;
    }
    ul.innerHTML = filtered.map(function (s) {
        const badge = s.status_baca
            ? '<span class="bg-emerald-50 text-emerald-700 font-bold text-[10px] px-2 py-0.5 rounded-full">Sudah dibaca</span>'
            : '<span class="bg-amber-50 text-amber-700 font-bold text-[10px] px-2 py-0.5 rounded-full">Belum dibaca</span>';
        return '<li class="flex items-center justify-between gap-3 py-2.5">'
            + '<div class="flex items-center gap-2.5 min-w-0">'
            + '<div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-black shrink-0">' + escHtml((s.nama || '?').substring(0, 2).toUpperCase()) + '</div>'
            + '<div class="min-w-0"><div class="font-bold text-xs text-slate-900 truncate">' + escHtml(s.nama) + '</div>'
            + '<div class="text-[10px] text-slate-400">NISN: ' + escHtml(s.nisn) + ' • Kelas ' + escHtml(s.kelas) + ' • ' + escHtml(s.waktu) + '</div></div></div>'
            + '<div class="flex items-center gap-1.5 shrink-0">' + badge
            + '<form action="/guru/notifikasi-pr/notifikasi/' + s.id_notifikasi + '" method="POST" onsubmit="return confirm(\'Hapus riwayat notifikasi untuk ' + escHtml(s.nama).replace(/'/g, '') + '?\')">'
            + '<input type="hidden" name="_token" value="' + CSRF_TOKEN_SISWA + '">'
            + '<input type="hidden" name="_method" value="DELETE">'
            + '<button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus notifikasi ini"><i class="fas fa-trash-can text-xs"></i></button>'
            + '</form></div></li>';
    }).join('');
}

function filterSiswaModal() {
    renderSiswaList(document.getElementById('siswa-modal-search').value);
}

// ===== FORM MODAL FUNCTIONS =====
function toggleNotifSection(isChecked) {
    const body = document.getElementById('modal-notif-settings-body');
    if (body) body.style.display = isChecked ? 'block' : 'none';
    const btnText = document.getElementById('modal-btn-submit-text');
    if (btnText) btnText.innerText = isChecked ? 'Simpan Tugas PR & Kirimkan' : 'Simpan Tugas PR Saja';
}

function handleTargetChange(tipe) {
    const kelasContainer = document.getElementById('modal-target-kelas-container');
    const siswaContainer = document.getElementById('modal-target-siswa-container');
    if (kelasContainer) kelasContainer.classList.toggle('hidden', tipe !== 'kelas');
    if (siswaContainer) siswaContainer.classList.toggle('hidden', tipe !== 'siswa');

    ['semua', 'kelas', 'siswa'].forEach(function(t) {
        var lbl = document.getElementById('modal-lbl-target-' + t);
        if (lbl) {
            lbl.className = t === tipe
                ? 'border-2 border-primary/40 bg-sky-50/50 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer'
                : 'border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition';
        }
    });
}

function showTenggatWarning(message) {
    var alertBox = document.getElementById('modal-alert-tenggat-warning');
    var alertText = document.getElementById('modal-alert-tenggat-text');
    if (!alertBox || !alertText) return;
    if (message) {
        alertText.innerText = message;
        alertBox.classList.remove('hidden');
    } else {
        alertBox.classList.add('hidden');
        alertText.innerText = '';
    }
}

function validateDateTimeLive() {
    var tglVal = document.getElementById('modal-input_tanggal').value;
    var jamVal = document.getElementById('modal-select_jam').value || '07';
    var menitVal = document.getElementById('modal-select_menit').value || '00';

    if (!tglVal) {
        showTenggatWarning('');
        return true;
    }

    var dParts = tglVal.split('-');
    var pickedDate = new Date(parseInt(dParts[0], 10), parseInt(dParts[1], 10) - 1, parseInt(dParts[2], 10));
    if (pickedDate.getDay() === 0) {
        showTenggatWarning('Hari Minggu tidak bisa dipilih karena hari itu libur sekolah. Silakan pilih hari Senin–Sabtu.');
        return false;
    }

    if (jamVal === '14' && menitVal !== '00') {
        document.getElementById('modal-select_menit').value = '00';
        menitVal = '00';
    }

    var selectedDate = new Date(parseInt(dParts[0], 10), parseInt(dParts[1], 10) - 1, parseInt(dParts[2], 10), parseInt(jamVal, 10), parseInt(menitVal, 10), 0);
    var now = new Date();

    if (selectedDate <= now) {
        showTenggatWarning('Waktu yang dipilih sudah lewat, silakan atur batas waktu ke masa depan.');
        return false;
    }

    var timeNum = parseInt(jamVal + menitVal, 10);
    if (timeNum < 700 || timeNum > 1400) {
        showTenggatWarning('Jam pengumpulan harus antara pukul 07.00 - 14.00 WIB sesuai jam operasional sekolah.');
        return false;
    }

    showTenggatWarning('');
    return true;
}

function syncDateTimeAndPreview() {
    var tgl = document.getElementById('modal-input_tanggal').value;
    var jam = document.getElementById('modal-select_jam').value || '07';
    var menit = document.getElementById('modal-select_menit').value || '00';

    if (tgl) {
        document.getElementById('modal-tgl_tenggat').value = tgl + ' ' + jam + ':' + menit + ':00';
    }

    validateDateTimeLive();
    updateLivePreview();
}

function validateBeforeSubmit() {
    var isValid = validateDateTimeLive();
    if (!isValid) {
        var warningBox = document.getElementById('modal-alert-tenggat-warning');
        if (warningBox) warningBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    return true;
}

function updateLivePreview() {
    var namaPr = document.getElementById('modal-nama_pr').value.trim() || 'Judul PR';
    var mapelSelect = document.getElementById('modal-id_mapel');
    var mapelText = mapelSelect && mapelSelect.options[mapelSelect.selectedIndex] ? mapelSelect.options[mapelSelect.selectedIndex].text.trim() : 'Mata Pelajaran';
    var tglVal = document.getElementById('modal-input_tanggal').value;
    var jamVal = document.getElementById('modal-select_jam').value || '07';
    var menitVal = document.getElementById('modal-select_menit').value || '00';

    var tenggatFormatted = 'Tenggat Waktu';
    if (tglVal) {
        var parts = tglVal.split('-');
        if (parts.length === 3) {
            var bulanList = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            var blnIdx = parseInt(parts[1], 10) - 1;
            var blnNama = bulanList[blnIdx] || parts[1];
            tenggatFormatted = parts[2] + ' ' + blnNama + ' ' + parts[0] + ', ' + jamVal + ':' + menitVal + ' WIB';
        }
    }

    var defaultMsg = "Halo! Ada tugas baru nih — '" + namaPr + "' (" + mapelText + "). Batasnya sudah ditetapkan pada " + tenggatFormatted + ". Yuk segera disusul!";

    var pesanKustom = document.getElementById('modal-pesan_kustom');
    if (!pesanKustom.value || pesanKustom.dataset.autoGenerated === 'true') {
        pesanKustom.value = defaultMsg;
        pesanKustom.dataset.autoGenerated = 'true';
    }

    var previewTag = document.getElementById('modal-preview-tag-mapel');
    var previewText = document.getElementById('modal-preview-text-body');
    if (previewTag) previewTag.innerText = mapelText;
    if (previewText) previewText.innerText = pesanKustom.value || defaultMsg;
}

function resetDefaultPesan() {
    var pesanKustom = document.getElementById('modal-pesan_kustom');
    pesanKustom.dataset.autoGenerated = 'true';
    pesanKustom.value = '';
    updateLivePreview();
}

document.addEventListener('DOMContentLoaded', function() {
    syncDateTimeAndPreview();
});

document.getElementById('modal-pesan_kustom') && document.getElementById('modal-pesan_kustom').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
    document.getElementById('modal-preview-text-body').innerText = this.value;
});
</script>
@endsection
