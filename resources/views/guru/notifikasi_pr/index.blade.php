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
            <button type="button" onclick="openKirimModal()" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fas fa-paper-plane text-xs"></i> Kirim Notifikasi Cepat
            </button>
            <a href="{{ route('guru.notifikasi_pr.create') }}" class="px-4 py-2.5 bg-white text-[#13527D] hover:bg-slate-100 font-extrabold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> Tambah PR Baru
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                <i class="fas fa-check-circle"></i>
            </div>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-xs">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-xs">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

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
                                <!-- Tombol Kirim Notifikasi -->
                                <button type="button"
                                        onclick="openKirimModalForPr({{ $pr->id_pr }}, '{{ addslashes($pr->nama_pr) }}', '{{ addslashes($pr->mataPelajaran->nama_mapel ?? '') }}', '{{ \Carbon\Carbon::parse($pr->tgl_tenggat)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($pr->tgl_tenggat)->format('H:i') }} WIB')"
                                        class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg transition text-[11px] flex items-center gap-1 shadow-sm"
                                        title="Kirim / Broadcast Notifikasi ke Siswa">
                                    <i class="fas fa-paper-plane text-[10px]"></i> Kirim
                                </button>
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
        <!-- Tab 2: TABEL LOG NOTIFIKASI -->
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
                    @forelse($notifikasis as $idx => $notif)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-bold text-slate-400">
                            {{ $notifikasis->firstItem() + $idx }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $notif->siswa->nm_siswa ?? 'Siswa' }}</div>
                            <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                <span>NISN: {{ $notif->siswa->nisn ?? '-' }}</span>
                                &bull;
                                <span class="font-medium text-slate-600">{{ $notif->siswa->kelas->pararel ?? 'Kelas' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($notif->pr)
                                <a href="{{ route('guru.notifikasi_pr.show', $notif->pr->id_pr) }}" class="font-bold text-[#13527D] hover:underline block truncate max-w-xs">
                                    {{ $notif->pr->nama_pr }}
                                </a>
                                <span class="text-[10px] text-slate-400">{{ $notif->pr->mataPelajaran->nama_mapel ?? '' }}</span>
                            @else
                                <span class="text-slate-400 italic">Tugas PR Dihapus</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 max-w-sm">
                            <p class="line-clamp-2 text-slate-700 leading-relaxed">{{ $notif->pesan }}</p>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($notif->status_baca)
                                <span class="bg-emerald-50 text-emerald-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fas fa-check-double text-[9px]"></i> Sudah Dibaca
                                </span>
                            @else
                                <span class="bg-amber-50 text-amber-700 font-extrabold text-[10px] px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                    <i class="fas fa-envelope text-[9px]"></i> Belum Dibaca
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                            <div class="font-semibold text-slate-700">{{ $notif->created_at ? $notif->created_at->translatedFormat('d M Y') : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $notif->created_at ? $notif->created_at->format('H:i') : '' }} WIB</div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button"
                                        onclick="showPesanModal('{{ addslashes($notif->siswa->nm_siswa ?? 'Siswa') }}', '{{ addslashes($notif->pr->nama_pr ?? 'Tugas PR') }}', '{{ addslashes($notif->pesan) }}', '{{ $notif->created_at ? $notif->created_at->translatedFormat('d M Y') . ', ' . $notif->created_at->format('H:i') . ' WIB' : '-' }}', '{{ $notif->status_baca ? 'Sudah Dibaca' : 'Belum Dibaca' }}')"
                                        class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Lihat Isi Pesan">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <form action="{{ route('guru.notifikasi_pr.destroy_notifikasi', $notif->id_notifikasi) }}" method="POST"
                                      onsubmit="return confirm('Hapus riwayat notifikasi ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus Notifikasi">
                                        <i class="fas fa-trash-can text-xs"></i>
                                    </button>
                                </form>
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

        @if($notifikasis->hasPages())
        <div class="pt-4 border-t border-slate-100">
            {{ $notifikasis->links() }}
        </div>
        @endif
        @endif
    </div>

</div>

<!-- MODAL 1: KIRIM NOTIFIKASI PR -->
<div id="modal-kirim-notif" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all animate-scale-up">
        <div class="bg-gradient-to-r from-[#0E385D] to-[#165B96] px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-400 text-slate-950 flex items-center justify-center font-black text-sm">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">Kirim Notifikasi PR ke Siswa</h3>
                    <p class="text-[11px] text-sky-200">Kirimkan pemberitahuan tugas langsung ke akun siswa</p>
                </div>
            </div>
            <button onclick="closeKirimModal()" class="text-white/70 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('guru.notifikasi_pr.kirim') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Pilih PR -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Tugas PR <span class="text-rose-500">*</span></label>
                <select name="id_pr" id="modal_id_pr" required onchange="handleModalPrChange()"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <option value="">-- Pilih Tugas PR --</option>
                    @foreach($allPrs as $p)
                        <option value="{{ $p->id_pr }}"
                                data-nama="{{ $p->nama_pr }}"
                                data-mapel="{{ $p->mataPelajaran->nama_mapel ?? '' }}"
                                data-tenggat="{{ \Carbon\Carbon::parse($p->tgl_tenggat)->translatedFormat('d M Y') }}, {{ \Carbon\Carbon::parse($p->tgl_tenggat)->format('H:i') }} WIB">
                            {{ $p->nama_pr }} ({{ $p->mataPelajaran->nama_mapel ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Target Penerima -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Target Siswa Penerima <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <label class="border border-slate-200 rounded-xl p-2.5 flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="target_tipe" value="semua" checked onchange="toggleModalTarget(this.value)" class="text-[#13527D]">
                        <span class="font-bold text-slate-800 text-[11px]">Semua Siswa</span>
                    </label>
                    <label class="border border-slate-200 rounded-xl p-2.5 flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="target_tipe" value="kelas" onchange="toggleModalTarget(this.value)" class="text-[#13527D]">
                        <span class="font-bold text-slate-800 text-[11px]">Per Kelas</span>
                    </label>
                    <label class="border border-slate-200 rounded-xl p-2.5 flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="target_tipe" value="siswa" onchange="toggleModalTarget(this.value)" class="text-[#13527D]">
                        <span class="font-bold text-slate-800 text-[11px]">Pilih Siswa</span>
                    </label>
                </div>
            </div>

            <!-- Target Kelas Selector (Hidden by default) -->
            <div id="modal_target_kelas_wrap" class="hidden">
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kelas</label>
                <select name="target_kelas" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id_rooms }}">{{ $k->pararel }} ({{ $k->siswas_count }} Siswa)</option>
                    @endforeach
                </select>
            </div>

            <!-- Target Siswa Multi-Select (Hidden by default) -->
            <div id="modal_target_siswa_wrap" class="hidden">
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Nama Siswa</label>
                <div class="max-h-36 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1 bg-slate-50">
                    @foreach($siswas as $s)
                    <label class="flex items-center gap-2 p-1.5 hover:bg-white rounded-lg text-xs cursor-pointer">
                        <input type="checkbox" name="target_siswa[]" value="{{ $s->id_siswa }}" class="rounded text-[#13527D]">
                        <span class="font-semibold text-slate-800">{{ $s->nm_siswa }}</span>
                        <span class="text-[10px] text-slate-400">({{ $s->kelas->pararel ?? 'Siswa' }})</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Pesan Notifikasi -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-bold text-slate-700">Isi Pesan Notifikasi <span class="text-rose-500">*</span></label>
                    <button type="button" onclick="generateDefaultMessage()" class="text-[10px] font-bold text-[#13527D] hover:underline">
                        Gunakan Format Default
                    </button>
                </div>
                <textarea name="pesan" id="modal_pesan" rows="3" required
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]"
                          placeholder="Tulis pesan pengingat untuk siswa..."></textarea>
                <p class="text-[10px] text-slate-400 mt-1">Pesan ini akan langsung muncul di halaman portal siswa penerima.</p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeKirimModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow">
                    <i class="fas fa-paper-plane text-xs"></i> Kirim Notifikasi
                </button>
            </div>
        </form>
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

<script>
function openKirimModal() {
    document.getElementById('modal-kirim-notif').classList.remove('hidden');
}

function closeKirimModal() {
    document.getElementById('modal-kirim-notif').classList.add('hidden');
}

function openKirimModalForPr(idPr, namaPr, namaMapel, tenggat) {
    const select = document.getElementById('modal_id_pr');
    select.value = idPr;
    document.getElementById('modal_pesan').value = `Tugas PR Baru: '${namaPr}' (${namaMapel}). Batas pengumpulan: ${tenggat}. Silakan periksa instruksi tugas dan kumpulkan tepat waktu.`;
    openKirimModal();
}

function handleModalPrChange() {
    generateDefaultMessage();
}

function generateDefaultMessage() {
    const select = document.getElementById('modal_id_pr');
    const selected = select.options[select.selectedIndex];
    if (selected && selected.value) {
        const nama = selected.getAttribute('data-nama');
        const mapel = selected.getAttribute('data-mapel');
        const tenggat = selected.getAttribute('data-tenggat');
        document.getElementById('modal_pesan').value = `Tugas PR Baru: '${nama}' (${mapel}). Batas pengumpulan: ${tenggat}. Silakan periksa instruksi tugas dan kumpulkan tepat waktu.`;
    }
}

function toggleModalTarget(tipe) {
    document.getElementById('modal_target_kelas_wrap').classList.toggle('hidden', tipe !== 'kelas');
    document.getElementById('modal_target_siswa_wrap').classList.toggle('hidden', tipe !== 'siswa');
}

function showPesanModal(siswa, pr, pesan, waktu, status) {
    document.getElementById('detail-siswa').innerText = siswa;
    document.getElementById('detail-pr').innerText = pr;
    document.getElementById('detail-waktu').innerText = waktu;
    document.getElementById('detail-status').innerText = status;
    document.getElementById('detail-status').className = status.includes('Sudah') ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold';
    document.getElementById('detail-pesan-body').innerText = pesan;
    document.getElementById('modal-detail-pesan').classList.remove('hidden');
}
</script>
@endsection
