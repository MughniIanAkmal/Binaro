@extends('layouts.guru')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="bg-gradient-to-r from-[#0E385D] to-[#165B96] rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="bg-amber-400 text-slate-900 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                    <i class="fas fa-bell mr-1"></i> Notifikasi Akademik
                </span>
                <span class="text-xs text-sky-200">SDN Kalitapen 01</span>
            </div>
            <h1 class="text-xl md:text-2xl font-black tracking-tight">Kelola Notifikasi Pekerjaan Rumah (PR)</h1>
            <p class="text-xs text-sky-100 max-w-xl">
                Tambah tugas PR, atur tenggat waktu, dan kirimkan notifikasi pengingat langsung ke akun portal siswa.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('guru.notifikasi_pr.create') }}" class="px-4 py-2.5 bg-white text-[#13527D] hover:bg-slate-100 font-extrabold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> Tambah PR Baru
            </a>
        </div>
    </div>



    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Total PR -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-2xl font-black text-slate-900">{{ $totalPr }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Total Tugas PR</div>
                <div class="text-[11px] text-slate-400">Semua mata pelajaran</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center text-xl font-bold">
                <i class="fas fa-book-reader"></i>
            </div>
        </div>

        <!-- KPI 2: PR Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-2xl font-black text-emerald-600">{{ $prAktif }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">PR Aktif Berjalan</div>
                <div class="text-[11px] text-emerald-600 font-medium">Belum lewat tenggat</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <!-- KPI 3: Notifikasi Terkirim -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-2xl font-black text-amber-500">{{ $totalNotifikasi }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Notifikasi Terkirim</div>
                <div class="text-[11px] text-slate-400">Total pesan ke siswa</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-paper-plane"></i>
            </div>
        </div>

        <!-- KPI 4: Siswa Terjangkau -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-2xl font-black text-indigo-600">{{ $siswaTerjangkau }} / {{ $totalSiswa }}</div>
                <div class="text-xs font-bold text-slate-600 mt-0.5">Siswa Terjangkau</div>
                <div class="text-[11px] text-indigo-500 font-semibold">{{ $readRate }}% Keterbacaan</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-slate-100 pb-3">
            <!-- Tabs -->
            <div class="flex items-center gap-2">
                <a href="{{ route('guru.notifikasi_pr.index', array_merge(request()->query(), ['tab' => 'pr'])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'pr' ? 'bg-[#13527D] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fas fa-list-check"></i>
                    <span>Daftar Tugas PR ({{ $prs->total() }})</span>
                </a>
                <a href="{{ route('guru.notifikasi_pr.index', array_merge(request()->query(), ['tab' => 'notifikasi'])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'notifikasi' ? 'bg-[#13527D] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fas fa-bell"></i>
                    <span>Riwayat Notifikasi Terkirim ({{ $notifikasis->total() }})</span>
                </a>
            </div>

            <!-- Search & Filter Form -->
            <form method="GET" action="{{ route('guru.notifikasi_pr.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari PR, mapel, atau pesan..."
                           class="pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:border-[#13527D] w-48 sm:w-60">
                </div>
                <select name="mapel_id" onchange="this.form.submit()" class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:border-[#13527D]">
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
                                <a href="{{ route('guru.notifikasi_pr.show', $pr->id_pr) }}" class="hover:text-[#13527D] hover:underline">
                                    {{ $pr->nama_pr }}
                                </a>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="bg-sky-50 text-[#13527D] font-bold text-[10px] px-2 py-0.5 rounded border border-sky-100">
                                    {{ $pr->mataPelajaran->nama_mapel ?? 'Mata Pelajaran' }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    <i class="fas fa-user-tie text-[9px] mr-0.5"></i> {{ $pr->guru->nama_guru ?? 'Guru' }}
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
                                        <i class="fas fa-times-circle text-[9px]"></i> Berakhir {{ $diffForHumans }}
                                    </span>
                                @else
                                    <span class="bg-emerald-50 text-emerald-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fas fa-clock text-[9px]"></i> Tenggat {{ $diffForHumans }}
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
                                   class="p-1.5 bg-sky-50 hover:bg-sky-100 text-[#13527D] rounded-lg transition" title="Edit PR">
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
                            <a href="{{ route('guru.notifikasi_pr.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-[#13527D] text-white text-xs font-bold rounded-xl hover:bg-[#0E3D5D] transition">
                                <i class="fas fa-plus"></i> Tambah PR Sekarang
                            </a>
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
                                <i class="fas fa-users text-[#13527D] text-[11px]"></i>
                                {{ $grup['label'] }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $grup['sub_label'] }}
                            </div>
                            @if($grup['total'] > 1)
                            <button type="button" onclick="openSiswaModal({{ $gi }})"
                                class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 text-[#13527D] font-bold hover:bg-sky-100 transition text-[11px] border border-sky-100">
                                <i class="fas fa-list-ul text-[10px]"></i>
                                Lihat {{ $grup['total'] }} siswa
                            </button>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($grup['pr'])
                                <a href="{{ route('guru.notifikasi_pr.show', $grup['id_pr']) }}" class="font-bold text-[#13527D] hover:underline block truncate max-w-xs">
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
                                        <i class="fas fa-check-double text-[9px]"></i> Sudah Dibaca
                                    </span>
                                @else
                                    <span class="bg-amber-50 text-amber-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                        <i class="fas fa-envelope text-[9px]"></i> Belum Dibaca
                                    </span>
                                @endif
                            @else
                                <span class="bg-sky-50 text-[#13527D] font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fas fa-check-double text-[9px]"></i> {{ $grup['dibaca'] }} / {{ $grup['total'] }} dibaca
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
                                        class="p-1.5 bg-sky-50 hover:bg-sky-100 text-[#13527D] rounded-lg transition" title="Lihat daftar siswa penerima">
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
                    <span id="detail-pr" class="font-bold text-[#13527D]"></span>
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
        <div class="bg-[#13527D] px-6 py-4 text-white flex items-center justify-between shrink-0">
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
                    class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:border-[#13527D]">
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
            + '<div class="w-8 h-8 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center text-xs font-black shrink-0">' + escHtml((s.nama || '?').substring(0, 2).toUpperCase()) + '</div>'
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
</script>
@endsection
