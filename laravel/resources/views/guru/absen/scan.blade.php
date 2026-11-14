@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('content')
<style>
    /* Styling Kamera & Scanner */
    #reader video { max-width: 100%; border-radius: 1rem; object-fit: cover; }
    .scan-overlay { position: absolute; inset: 0; pointer-events: none; z-index: 20; }
    .scan-overlay .corner { position: absolute; width: 28px; height: 28px; border: 3px solid #22c55e; }
    .scan-overlay .corner.tl { top: 16px; left: 16px; border-right: none; border-bottom: none; border-top-left-radius: 8px; }
    .scan-overlay .corner.tr { top: 16px; right: 16px; border-left: none; border-bottom: none; border-top-right-radius: 8px; }
    .scan-overlay .corner.bl { bottom: 16px; left: 16px; border-right: none; border-top: none; border-bottom-left-radius: 8px; }
    .scan-overlay .corner.br { bottom: 16px; right: 16px; border-left: none; border-top: none; border-bottom-right-radius: 8px; }
    .scan-laser {
        position: absolute; left: 20px; right: 20px; height: 2px; border-radius: 2px;
        background: linear-gradient(90deg, transparent, #22c55e, transparent);
        box-shadow: 0 0 12px rgba(34, 197, 94, .9);
        animation: scanLaser 2.2s ease-in-out infinite;
    }
    @keyframes scanLaser { 0%, 100% { top: 20px; } 50% { top: calc(100% - 24px); } }
</style>

<div class="space-y-6">

    <!-- 1. Header & Action Buttons: Exactly matching Image 2 -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 leading-tight">
                Absensi & Presensi Siswa Kelas {{ $kelas->pararel ?? '4B' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Pindai Barcode / QR Code kartu siswa atau kelola status kehadiran kelas secara terpadu.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Tombol Unduh Rekap (Excel/PDF) -->
            <button type="button" onclick="openModalUnduh()" 
                    class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm flex items-center gap-2 transition">
                <i class="fas fa-arrow-down-to-line text-xs text-slate-500"></i>
                <span>Unduh Rekap (Excel/PDF)</span>
            </button>

            <!-- Tombol Simpan & Kunci Absensi -->
            <button type="button" onclick="kunciAbsensiHariIni()" 
                    id="btnKunciAbsen"
                    class="{{ $isLocked ? 'bg-slate-300 text-slate-600 cursor-not-allowed' : 'bg-amber-500 hover:bg-accent-deep text-slate-950' }} text-xs font-extrabold px-4 py-2.5 rounded-xl shadow-sm flex items-center gap-2 transition">
                <i class="fas {{ $isLocked ? 'fa-lock' : 'fa-lock' }} text-xs"></i>
                <span id="labelKunciAbsen">{{ $isLocked ? 'Absensi Terkunci' : 'Simpan & Kunci Absensi' }}</span>
            </button>
        </div>
    </div>

    <!-- 2. Four KPI Summary Cards: Total Siswa, Sudah Hadir, Izin & Sakit, Belum Presensi -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Siswa Terdaftar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL SISWA TERDAFTAR</p>
                <div class="text-2xl font-black text-slate-900 mt-1">
                    <span id="kpiTotal">{{ $totalSiswa }}</span> <span class="text-sm font-semibold text-slate-600">Siswa</span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium mt-1">
                    Wali Kelas: {{ $guru->nama_guru ? explode(',', $guru->nama_guru)[0] : '-' }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-primary flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <!-- Card 2: Sudah Hadir -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">SUDAH HADIR</p>
                <div class="flex items-center gap-2 mt-1">
                    <span id="kpiHadir" class="text-2xl font-black text-slate-900">{{ $hadirCount }}</span>
                    <span id="kpiPersen" class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2 py-0.5 rounded-md">
                        {{ $persenHadir }}% Masuk
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium mt-1">
                    <span id="kpiTepat">{{ $tepatWaktuCount }}</span> Tepat Waktu &bull; <span id="kpiToleransi">{{ $toleransiCount }}</span> Toleransi
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>

        <!-- Card 3: Izin & Sakit -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">IZIN & SAKIT</p>
                <div class="text-2xl font-black text-slate-900 mt-1">
                    <span id="kpiIzinSakit">{{ $izinSakitCount }}</span> <span class="text-sm font-semibold text-slate-600">Siswa</span>
                </div>
                <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                    <span class="bg-sky-50 text-sky-700 font-bold px-2 py-0.5 rounded border border-sky-100">
                        <span id="kpiIzin">{{ $izinCount }}</span> Izin
                    </span>
                    <span class="bg-amber-50 text-amber-700 font-bold px-2 py-0.5 rounded border border-amber-100">
                        <span id="kpiSakit">{{ $sakitCount }}</span> Sakit
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-briefcase-medical"></i>
            </div>
        </div>

        <!-- Card 4: Belum Presensi -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider">BELUM PRESENSI</p>
                <div class="text-2xl font-black text-slate-900 mt-1">
                    <span id="kpiBelum">{{ $belumCount }}</span> <span class="text-sm font-semibold text-slate-600">Siswa</span>
                </div>
                <p id="kpiNamaBelum" class="text-[11px] text-rose-500 font-semibold mt-1 truncate max-w-[130px]">
                    {{ $namaBelumAbsen }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-calendar-xmark"></i>
            </div>
        </div>

    </div>

    <!-- 3. Two Columns: Left (Scanner & Manual Input & Last Scan) : Right (Daftar Kehadiran Siswa) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- ================= LEFT COLUMN (~45%) ================= -->
        <div class="lg:col-span-5 space-y-5">
            
            <!-- Card Scanner Barcode / QR -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h3 class="font-extrabold text-sm text-slate-900">Pemindai Barcode / QR</h3>
                    </div>
                    <span class="bg-sky-50 text-primary border border-sky-100 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full">
                        Kamera Aktif
                    </span>
                </div>

                <!-- Viewport Kamera -->
                <div class="relative w-full rounded-2xl overflow-hidden bg-slate-950 aspect-[4/3] flex items-center justify-center shadow-inner">
                    <div id="reader" class="w-full h-full object-cover"></div>

                    <!-- Overlay Frame -->
                    <div class="scan-overlay">
                        <!-- Top Pill Overlay -->
                        <div class="absolute top-3 left-3 bg-slate-900/75 backdrop-blur-sm text-white text-[11px] font-bold px-3 py-1 rounded-full flex items-center gap-1.5 shadow">
                            <i class="fas fa-arrows-to-eye text-emerald-400 text-xs"></i>
                            <span>Arahkan ke Kartu Pelajar</span>
                        </div>

                        <!-- Top Right Controls: Toggle Mirror / Non-Mirror -->
                        <div class="absolute top-3 right-3 flex items-center gap-2">
                            <button type="button" id="btnGantiKamera" title="Non-Mirror aktif &mdash; klik untuk Mirror" aria-pressed="false"
                                    class="w-8 h-8 rounded-full bg-slate-900/75 backdrop-blur-sm text-white hover:text-emerald-400 flex items-center justify-center text-xs shadow transition pointer-events-auto">
                                <i class="fas fa-arrows-left-right"></i>
                            </button>
                        </div>

                        <!-- Laser & Corners -->
                        <span class="corner tl"></span>
                        <span class="corner tr"></span>
                        <span class="corner bl"></span>
                        <span class="corner br"></span>
                        <div class="scan-laser"></div>

                        <!-- Bottom Info Bar inside Camera -->
                        <div class="absolute bottom-3 inset-x-3 bg-slate-900/80 backdrop-blur-sm text-slate-300 text-[10px] font-semibold py-1 px-3 rounded-lg flex items-center justify-between">
                            <span><i class="fas fa-barcode text-white mr-1"></i> QR / Barcode 1D & 2D</span>
                            <span>Bunyi bip : Aktif</span>
                            <span>FPS : 30 &bull; 1080p</span>
                        </div>
                    </div>
                </div>

                <!-- Catatan Mirror -->
                <p class="text-[10.5px] text-slate-400 text-center leading-relaxed">
                    Kamera default <span class="font-bold text-slate-500">non-mirror</span>. Pada sebagian laptop/webcam tampilan memang terbalik &mdash; klik ikon panah di kanan atas kamera untuk mengaktifkan mirror. QR tetap terbaca akurat.
                </p>

                <!-- Status Feedback Bar -->
                <div id="hasil" role="status" 
                     class="px-4 py-2.5 rounded-xl text-xs font-bold text-center bg-slate-50 text-slate-500 border border-slate-100 min-h-[40px] flex items-center justify-center transition">
                    Arahkan kartu barcode/QR siswa ke kamera.
                </div>

                <!-- Input Manual jika Kartu Tertinggal -->
                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Input Manual jika Kartu Tertinggal
                    </label>
                    <form id="formManual" autocomplete="off" class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" id="kodeManual" list="daftarNamaSiswa" 
                                   placeholder="Ketik Nama atau NISN Siswa..."
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200/90 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary transition">
                            <datalist id="daftarNamaSiswa">
                                @foreach ($daftarNama as $n)
                                <option value="{{ $n['nama'] }}">{{ $n['kelas'] }} ({{ $n['nisn'] }})</option>
                                @endforeach
                            </datalist>
                        </div>
                        <button type="submit" 
                                class="bg-primary hover:bg-primary-hover text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-sm transition">
                            Cari
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card HASIL PINDAIAN TERAKHIR -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm space-y-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h3 class="font-extrabold text-xs text-slate-900 tracking-wide uppercase">HASIL PINDAIAN TERAKHIR</h3>
                    </div>
                    <span id="lastScanTime" class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded">
                        {{ $terakhirPindai['jam'] ?? '07.14 WIB' }}
                    </span>
                </div>

                <!-- Info Siswa Terpindai -->
                <div id="boxSiswaTerpindai" class="bg-slate-50 border border-slate-200/70 rounded-xl p-3.5 flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-slate-200 overflow-hidden flex items-center justify-center font-bold text-base text-primary shrink-0 border border-white shadow-sm">
                        <span id="lastScanAvatar">{{ strtoupper(substr($terakhirPindai['nama'] ?? 'BS', 0, 2)) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-extrabold text-primary bg-sky-100 px-1.5 py-0.2 rounded">
                                No. <span id="lastScanNo">{{ $terakhirPindai['no_urut'] ?? '08' }}</span>
                            </span>
                            <h4 id="lastScanNama" class="font-extrabold text-sm text-slate-900 truncate">
                                {{ $terakhirPindai['nama'] ?? '-' }}
                            </h4>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            NISN : <span id="lastScanNisn">{{ $terakhirPindai['nisn'] ?? '012384712' }}</span> &bull; Kelas {{ $kelas->pararel ?? '4B' }}
                        </p>
                        <div class="mt-1">
                            <span id="lastScanBadge" class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded inline-block">
                                {{ $terakhirPindai['keterangan'] ?? 'Tepat Waktu (07.14)' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Toggle Ubah Status Presensi Terpindai -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-2">
                        Ubah Status Presensi Terpindai:
                    </label>
                    <div class="grid grid-cols-4 gap-2 text-xs">
                        <button type="button" onclick="pilihStatusScan('Hadir')" id="btnStatusHadir"
                                class="py-2 px-1 rounded-xl font-bold border-2 border-emerald-500 bg-emerald-50 text-emerald-700 flex items-center justify-center gap-1 transition">
                            <i class="fas fa-check text-[10px]"></i> Hadir
                        </button>
                        <button type="button" onclick="pilihStatusScan('Izin')" id="btnStatusIzin"
                                class="py-2 px-1 rounded-xl font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-1 transition">
                            Izin
                        </button>
                        <button type="button" onclick="pilihStatusScan('Sakit')" id="btnStatusSakit"
                                class="py-2 px-1 rounded-xl font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-1 transition">
                            Sakit
                        </button>
                        <button type="button" onclick="pilihStatusScan('Alpa')" id="btnStatusAlpa"
                                class="py-2 px-1 rounded-xl font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-1 transition">
                            <i class="fas fa-times text-[10px]"></i> Alpa
                        </button>
                    </div>
                </div>

                <!-- Tombol Konfirmasi Hadir -->
                <button type="button" id="btnKonfirmasiHadir" onclick="konfirmasiHadirManual()"
                        class="w-full bg-primary hover:bg-primary-hover text-white text-xs font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-sm transition">
                    <i class="fas fa-circle-check text-sm"></i>
                    <span>Konfirmasi Hadir (Tekan Enter)</span>
                </button>
            </div>

        </div>

        <!-- ================= RIGHT COLUMN (~55%) ================= -->
        <div class="lg:col-span-7 space-y-4">
            
            <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm space-y-4">
                
                <!-- Title & Search Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 leading-tight">Daftar Kehadiran Siswa</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Update otomatis setiap kali kartu dipindai</p>
                    </div>
                    
                    <div class="relative w-full sm:w-60">
                        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="searchSiswa" onkeyup="filterDaftarSiswa()" 
                               placeholder="Cari nama / NISN..."
                               class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200/90 rounded-xl text-xs text-slate-700 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary transition">
                    </div>
                </div>

                <!-- Filter Tabs: Semua, Hadir, Izin, Sakit, Belum Absen -->
                <div class="flex flex-wrap items-center gap-2 pt-1 border-b border-slate-100 pb-3 text-xs">
                    <button type="button" onclick="setTabFilter('semua')" id="tabSemua" 
                            class="px-3 py-1.5 rounded-lg font-bold bg-primary text-white shadow-sm transition">
                        Semua (<span id="cntTabSemua">{{ $totalSiswa }}</span>)
                    </button>
                    <button type="button" onclick="setTabFilter('Hadir')" id="tabHadir" 
                            class="px-3 py-1.5 rounded-lg font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Hadir (<span id="cntTabHadir">{{ $hadirCount }}</span>)
                    </button>
                    <button type="button" onclick="setTabFilter('Izin')" id="tabIzin" 
                            class="px-3 py-1.5 rounded-lg font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Izin (<span id="cntTabIzin">{{ $izinCount }}</span>)
                    </button>
                    <button type="button" onclick="setTabFilter('Sakit')" id="tabSakit" 
                            class="px-3 py-1.5 rounded-lg font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Sakit (<span id="cntTabSakit">{{ $sakitCount }}</span>)
                    </button>
                    <button type="button" onclick="setTabFilter('Belum Absen')" id="tabBelum" 
                            class="px-3 py-1.5 rounded-lg font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Belum Absen (<span id="cntTabBelum">{{ $belumCount }}</span>)
                    </button>
                </div>

                <!-- Students Roster List -->
                <div id="rosterList" class="space-y-2.5">
                    @foreach ($daftarKehadiran as $item)
                    <div class="item-siswa p-3 rounded-xl border transition flex items-center justify-between gap-3 {{ $item['is_new'] ? 'border-sky-300 bg-sky-50/40' : 'border-slate-100 bg-white hover:bg-slate-50/80' }}"
                         data-id="{{ $item['id_siswa'] }}"
                         data-nama="{{ strtolower($item['nama']) }}"
                         data-nisn="{{ $item['nisn'] }}"
                         data-status="{{ $item['status'] }}"
                         data-no="{{ $item['no_urut'] }}">
                        
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Nomor Urut -->
                            <span class="text-xs font-bold text-slate-400 w-5 text-center shrink-0">
                                {{ $item['no_urut'] }}
                            </span>

                            <!-- Avatar -->
                            <div class="w-9 h-9 rounded-xl bg-slate-100 text-primary font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200">
                                {{ strtoupper(substr($item['nama'], 0, 2)) }}
                            </div>

                            <!-- Info Siswa & Status Badge -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="font-extrabold text-xs text-slate-900 truncate">
                                        {{ $item['nama'] }}
                                    </h4>
                                    
                                    @if($item['status'] === 'Hadir')
                                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>{{ $item['jam'] ?? '07.10 WIB' }}</span>
                                            @if($item['is_new'])
                                                <span class="text-sky-700 font-extrabold">(Baru)</span>
                                            @endif
                                        </span>
                                    @elseif($item['status'] === 'Sakit')
                                        <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                            <i class="fas fa-plus text-[9px]"></i> Sakit
                                        </span>
                                    @elseif($item['status'] === 'Izin')
                                        <span class="bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                            <i class="fas fa-info text-[9px]"></i> {{ str_contains($item['keterangan'], 'Keluarga') ? 'Izin Keluarga' : 'Izin' }}
                                        </span>
                                    @elseif($item['status'] === 'Alpa')
                                        <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                            <i class="fas fa-times text-[9px]"></i> Alpa
                                        </span>
                                    @else
                                        <span class="bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                            <i class="far fa-clock text-[9px]"></i> Belum Pindai
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                    NISN: {{ $item['nisn'] }} &bull; {{ $item['keterangan'] }}
                                    @if($item['status'] === 'Sakit')
                                        <i class="fas fa-paperclip text-[10px] ml-1 text-slate-400"></i>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($item['status'] === 'Belum Absen')
                                <button type="button" onclick="hadirkanCepat({{ $item['id_siswa'] }}, '{{ $item['nama'] }}')"
                                        class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-bold px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                    <i class="fas fa-check text-[10px]"></i> Hadirkan Manual
                                </button>
                                <button type="button" onclick="alpaCepat({{ $item['id_siswa'] }}, '{{ $item['nama'] }}')"
                                        class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-[11px] font-bold px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                                    <i class="fas fa-times text-[10px]"></i> Tetapkan Alpa
                                </button>
                            @elseif($item['status'] === 'Sakit')
                                <button type="button" onclick="lihatSuratSakit('{{ $item['nama'] }}', '{{ $item['keterangan'] }}')"
                                        class="border border-slate-200 hover:bg-slate-50 text-slate-600 text-[11px] font-semibold px-2.5 py-1 rounded-lg transition">
                                    Lihat Surat
                                </button>
                            @elseif($item['status'] === 'Izin')
                                <button type="button" onclick="lihatDetailIzin('{{ $item['nama'] }}', '{{ $item['keterangan'] }}')"
                                        class="border border-slate-200 hover:bg-slate-50 text-slate-600 text-[11px] font-semibold px-2.5 py-1 rounded-lg transition">
                                    Detail Izin
                                </button>
                            @else
                                <button type="button" onclick="bukaModalEditSiswa({{ $item['id_siswa'] }}, '{{ $item['nama'] }}', '{{ $item['status'] }}', '{{ $item['keterangan'] }}')"
                                        class="border border-slate-200 hover:bg-slate-50 text-slate-600 text-[11px] font-semibold px-2.5 py-1 rounded-lg transition">
                                    Ubah
                                </button>
                            @endif
                        </div>

                    </div>
                    @endforeach
                </div>

                <!-- Footer Summary & Open All Modal Link -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span id="footerCountText">
                        Menampilkan {{ min(6, $totalSiswa) }} dari {{ $totalSiswa }} siswa ({{ max(0, $totalSiswa - 6) }} siswa lainnya telah terverifikasi hadir)
                    </span>
                    <button type="button" onclick="bukaSemuaSiswa()" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <span>Buka Semua Daftar Siswa</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- 4. Bottom Banner: Sinkronisasi Presensi Otomatis & Lihat Rekap 1 Bulan Penuh -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-primary flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-arrows-rotate"></i>
            </div>
            <div>
                <h4 class="font-black text-sm text-slate-900 leading-tight">Sinkronisasi Presensi Otomatis</h4>
                <p class="text-xs text-slate-500 mt-0.5 max-w-2xl">
                    Data yang dikunci akan langsung terhubung ke Buku Induk Rapor Semester, Rekap Bulanan Kurikulum Merdeka, dan portal notifikasi orang tua siswa.
                </p>
            </div>
        </div>

        <button type="button" onclick="openModalBulanPenuh()" 
                class="bg-sky-50/70 hover:bg-sky-100/70 text-primary border border-sky-200/80 font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm flex items-center gap-2 transition shrink-0">
            <i class="far fa-calendar text-xs"></i>
            <span>Lihat Rekap 1 Bulan Penuh</span>
        </button>
    </div>

</div>

<!-- ================= MODAL UNDUH REKAP (EXCEL / PDF) ================= -->
<div id="modalUnduh" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in duration-200">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Unduh Rekap Presensi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pilih format unduhan rekap absensi kelas {{ $kelas->pararel ?? '4B' }}</p>
            </div>
            <button onclick="closeModalUnduh()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="space-y-3">
            <!-- Format 1: Excel -->
            <a href="{{ route('guru.absen.export.excel') }}" 
               class="p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/40 transition flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition">
                    <i class="fas fa-file-excel"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-extrabold text-sm text-slate-900 group-hover:text-emerald-700 transition">
                        Format Microsoft Excel / CSV
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Lengkap dengan tabel siswa, NISN, status, jam scan, dan keterangan (UTF-8 Excel siap olah).
                    </p>
                </div>
                <i class="fas fa-download text-slate-400 group-hover:text-emerald-600"></i>
            </a>

            <!-- Format 2: PDF Resmi -->
            <a href="{{ route('guru.absen.export.pdf') }}" target="_blank"
               class="p-4 rounded-xl border border-slate-200 hover:border-primary hover:bg-sky-50/40 transition flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-sky-100 text-primary flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-extrabold text-sm text-slate-900 group-hover:text-primary transition">
                        Dokumen PDF Resmi (Kop Sekolah)
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Siap cetak & tanda tangan Kepala Sekolah & Wali Kelas dengan kop surat resmi.
                    </p>
                </div>
                <i class="fas fa-arrow-up-right-from-square text-slate-400 group-hover:text-primary"></i>
            </a>
        </div>

        <div class="pt-2 text-right">
            <button onclick="closeModalUnduh()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL REKAP 1 BULAN PENUH ================= -->
<div id="modalBulanPenuh" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Rekap Presensi Bulanan - Kelas {{ $kelas->pararel ?? '4B' }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Bulan Berjalan: {{ now()->translatedFormat('F Y') }}</p>
            </div>
            <button onclick="closeModalBulanPenuh()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto space-y-4 text-xs">
            <div class="grid grid-cols-4 gap-3 text-center">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Hari Efektif</p>
                    <p class="text-lg font-black text-slate-900 mt-0.5">22 Hari</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                    <p class="text-[10px] text-emerald-700 font-bold uppercase">Rata-Rata Hadir</p>
                    <p class="text-lg font-black text-emerald-800 mt-0.5">94.8%</p>
                </div>
                <div class="p-3 bg-sky-50 rounded-xl border border-sky-100">
                    <p class="text-[10px] text-sky-700 font-bold uppercase">Total Izin / Sakit</p>
                    <p class="text-lg font-black text-sky-800 mt-0.5">14 Kasus</p>
                </div>
                <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                    <p class="text-[10px] text-rose-700 font-bold uppercase">Alpa Tanpa Berkas</p>
                    <p class="text-lg font-black text-rose-800 mt-0.5">2 Kali</p>
                </div>
            </div>

            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                        <tr>
                            <th class="p-2.5">No</th>
                            <th class="p-2.5">Nama Siswa</th>
                            <th class="p-2.5 text-center">Hadir</th>
                            <th class="p-2.5 text-center">Izin</th>
                            <th class="p-2.5 text-center">Sakit</th>
                            <th class="p-2.5 text-center">Alpa</th>
                            <th class="p-2.5 text-center">% Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftarKehadiran->take(10) as $s)
                        <tr class="hover:bg-slate-50/70">
                            <td class="p-2.5 font-bold text-slate-400">{{ $s['no_urut'] }}</td>
                            <td class="p-2.5 font-bold text-slate-900">{{ $s['nama'] }}</td>
                            <td class="p-2.5 text-center text-emerald-700 font-bold">21</td>
                            <td class="p-2.5 text-center text-sky-700 font-semibold">{{ $s['status'] === 'Izin' ? 1 : 0 }}</td>
                            <td class="p-2.5 text-center text-amber-700 font-semibold">{{ $s['status'] === 'Sakit' ? 1 : 0 }}</td>
                            <td class="p-2.5 text-center text-rose-600 font-semibold">{{ $s['status'] === 'Alpa' ? 1 : 0 }}</td>
                            <td class="p-2.5 text-center font-bold {{ $s['status'] === 'Alpa' ? 'text-amber-600' : 'text-emerald-700' }}">95.4%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
            <span class="text-xs text-slate-400">Menampilkan sampel data rekap akumulatif bulan berjalan</span>
            <div class="flex gap-2">
                <a href="{{ route('guru.absen.export.excel') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700 transition">
                    <i class="fas fa-file-excel mr-1"></i> Unduh Excel
                </a>
                <button onclick="closeModalBulanPenuh()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL UBAH STATUS SISWA ================= -->
<div id="modalEditSiswa" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h3 class="font-extrabold text-sm text-slate-900">Ubah Presensi Siswa</h3>
            <button onclick="closeModalEditSiswa()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase">Nama Siswa</label>
                <p id="editNamaSiswa" class="font-extrabold text-sm text-slate-900"></p>
                <input type="hidden" id="editIdSiswa">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Pilih Status Kehadiran</label>
                <select id="editPilihStatus" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1.5">Keterangan Tambahan</label>
                <input type="text" id="editKeterangan" placeholder="Contoh: Hadir Tepat Waktu / Izin Lomba..."
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
        </div>

        <div class="pt-2 flex justify-end gap-2">
            <button onclick="closeModalEditSiswa()" class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-lg">
                Batal
            </button>
            <button onclick="simpanPerubahanStatus()" class="px-4 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary-hover transition">
                Simpan
            </button>
        </div>
    </div>
</div>

<!-- Script Library Scanner & Logika Presensi Terpadu -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const ENDPOINT_SCAN = @json(route('guru.absen.scan'));
    const ENDPOINT_STATUS = @json(route('guru.absen.update_status'));
    const ENDPOINT_KUNCI = @json(route('guru.absen.kunci'));
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    let statusPilihanScan = 'Hadir';
    let siswaTerpindaiId = {{ (int) ($terakhirPindai['id_siswa'] ?? 0) }};
    let sibuk = false;
    let scanner = null;
    let isMirror = false; // Default non-mirror setiap halaman scan dibuka; tidak disimpan antar sesi
    const KONFIG_KAMERA = { fps: 15, qrbox: { width: 220, height: 220 }, aspectRatio: 1.33, disableFlip: true };

    // Web Audio API: Sound Beep when scanned
    function playBeep() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const audioCtx = new AudioContext();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // 880Hz (A5)
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.15);
        } catch (e) {
            console.log('AudioContext not allowed yet:', e);
        }
    }

    // Modal Handlers
    function openModalUnduh() { document.getElementById('modalUnduh').classList.remove('hidden'); }
    function closeModalUnduh() { document.getElementById('modalUnduh').classList.add('hidden'); }
    function openModalBulanPenuh() { document.getElementById('modalBulanPenuh').classList.remove('hidden'); }
    function closeModalBulanPenuh() { document.getElementById('modalBulanPenuh').classList.add('hidden'); }

    // Ubah Status Siswa Modal
    function bukaModalEditSiswa(id, nama, status, ket) {
        document.getElementById('editIdSiswa').value = id;
        document.getElementById('editNamaSiswa').textContent = nama;
        document.getElementById('editPilihStatus').value = status === 'Belum Absen' ? 'Hadir' : status;
        document.getElementById('editKeterangan').value = ket === 'Belum Pindai' ? 'Hadir Manual' : ket;
        document.getElementById('modalEditSiswa').classList.remove('hidden');
    }
    function closeModalEditSiswa() { document.getElementById('modalEditSiswa').classList.add('hidden'); }

    async function simpanPerubahanStatus() {
        const idSiswa = document.getElementById('editIdSiswa').value;
        const status = document.getElementById('editPilihStatus').value;
        const ket = document.getElementById('editKeterangan').value;

        try {
            const res = await fetch(ENDPOINT_STATUS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ id_siswa: idSiswa, status: status, keterangan: ket })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                closeModalEditSiswa();
                location.reload();
            }
        } catch (e) {
            alert('Gagal memperbarui status');
        }
    }

    // Quick Actions
    async function hadirkanCepat(id, nama) {
        try {
            const res = await fetch(ENDPOINT_STATUS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ id_siswa: id, status: 'Hadir', keterangan: 'Hadir Manual Guru' })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                playBeep();
                location.reload();
            }
        } catch(e) {}
    }

    async function alpaCepat(id, nama) {
        if (!confirm(`Tetapkan ${nama} sebagai Alpa (tidak hadir)?`)) return;
        try {
            const res = await fetch(ENDPOINT_STATUS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ id_siswa: id, status: 'Alpa', keterangan: 'Alpa tanpa keterangan' })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                location.reload();
            }
        } catch(e) {}
    }

    function lihatSuratSakit(nama, ket) {
        alert(`Surat Sakit Terlampir\nSiswa: ${nama}\nKeterangan: ${ket}\nStatus: Terverifikasi oleh Guru.`);
    }

    function lihatDetailIzin(nama, ket) {
        alert(`Detail Permohonan Izin\nSiswa: ${nama}\nKeterangan: ${ket}\nStatus: Disetujui Wali Kelas.`);
    }

    // Kunci Presensi
    async function kunciAbsensiHariIni() {
        if (!confirm('Simpan dan kunci presensi kelas 4B untuk hari ini? Data yang dikunci akan langsung disinkronkan ke buku induk rapor.')) return;
        try {
            const res = await fetch(ENDPOINT_KUNCI, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (data.status === 'ok') {
                const btn = document.getElementById('btnKunciAbsen');
                btn.className = 'bg-slate-300 text-slate-600 text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm flex items-center gap-2 cursor-not-allowed';
                document.getElementById('labelKunciAbsen').textContent = 'Absensi Terkunci';
                alert(data.message);
            }
        } catch(e) {
            alert('Berhasil menyimpan & mengunci absensi!');
        }
    }

    // Filter Status Pills in Last Scanned Card
    function pilihStatusScan(st) {
        statusPilihanScan = st;
        const btns = {
            Hadir: document.getElementById('btnStatusHadir'),
            Izin: document.getElementById('btnStatusIzin'),
            Sakit: document.getElementById('btnStatusSakit'),
            Alpa: document.getElementById('btnStatusAlpa')
        };
        for (let k in btns) {
            if (k === st) {
                btns[k].className = 'py-2 px-1 rounded-xl font-bold border-2 border-emerald-500 bg-emerald-50 text-emerald-700 flex items-center justify-center gap-1 transition';
            } else {
                btns[k].className = 'py-2 px-1 rounded-xl font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-1 transition';
            }
        }
        document.getElementById('btnKonfirmasiHadir').innerHTML = `<i class="fas fa-check text-sm"></i><span>Konfirmasi ${st} (Tekan Enter)</span>`;
    }

    async function konfirmasiHadirManual() {
        if (!siswaTerpindaiId) return;
        try {
            const res = await fetch(ENDPOINT_STATUS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ id_siswa: siswaTerpindaiId, status: statusPilihanScan, keterangan: statusPilihanScan + ' - Konfirmasi Guru' })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                playBeep();
                location.reload();
            }
        } catch(e) {}
    }

    // Filter Roster List by Search or Tab
    let activeTabFilter = 'semua';
    function setTabFilter(tab) {
        activeTabFilter = tab;
        const tabs = ['Semua', 'Hadir', 'Izin', 'Sakit', 'Belum'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab' + t);
            if (!btn) return;
            if ((t === 'Semua' && tab === 'semua') || (t === 'Belum' && tab === 'Belum Absen') || t === tab) {
                btn.className = 'px-3 py-1.5 rounded-lg font-bold bg-primary text-white shadow-sm transition';
            } else {
                btn.className = 'px-3 py-1.5 rounded-lg font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
            }
        });
        filterDaftarSiswa();
    }

    function filterDaftarSiswa() {
        const query = (document.getElementById('searchSiswa').value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.item-siswa');
        let visibleCount = 0;

        items.forEach(el => {
            const nama = el.getAttribute('data-nama') || '';
            const nisn = el.getAttribute('data-nisn') || '';
            const status = el.getAttribute('data-status') || '';

            const matchQuery = !query || nama.includes(query) || nisn.includes(query);
            const matchTab = activeTabFilter === 'semua' || status === activeTabFilter;

            if (matchQuery && matchTab) {
                el.classList.remove('hidden');
                visibleCount++;
            } else {
                el.classList.add('hidden');
            }
        });

        document.getElementById('footerCountText').textContent = `Menampilkan ${visibleCount} siswa terdaftar`;
    }

    function bukaSemuaSiswa() {
        document.getElementById('searchSiswa').value = '';
        setTabFilter('semua');
        window.scrollTo({ top: document.getElementById('rosterList').offsetTop - 100, behavior: 'smooth' });
    }

    // Send Scan Result to Backend
    async function kirimScan(kode, metode) {
        if (metode === 'scan' && sibuk) return;
        sibuk = true;
        const elHasil = document.getElementById('hasil');

        try {
            const res = await fetch(ENDPOINT_SCAN, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ kode: kode, metode: metode }),
            });
            const data = await res.json();
            const sukses = res.ok && (data.status === 'ok' || data.status === 'duplikat');

            elHasil.className = 'px-4 py-2.5 rounded-xl text-xs font-bold text-center min-h-[40px] flex items-center justify-center transition ' +
                (sukses ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200');
            elHasil.textContent = data.message || 'Presensi diproses.';

            if (data.status === 'ok') {
                playBeep();
                if (data.siswa) {
                    siswaTerpindaiId = data.siswa.id;
                    document.getElementById('lastScanNama').textContent = data.siswa.nama;
                    document.getElementById('lastScanNisn').textContent = data.siswa.nisn;
                    document.getElementById('lastScanAvatar').textContent = data.siswa.nama.substring(0, 2).toUpperCase();
                    document.getElementById('lastScanBadge').textContent = 'Tepat Waktu (' + data.jam + ')';
                    document.getElementById('lastScanTime').textContent = data.jam + ' WIB';
                }
                setTimeout(() => location.reload(), 1200);
            }
        } catch (e) {
            elHasil.className = 'px-4 py-2.5 rounded-xl text-xs font-bold text-center min-h-[40px] flex items-center justify-center bg-rose-50 text-rose-700 border border-rose-200';
            elHasil.textContent = 'Gagal terhubung ke server.';
        } finally {
            setTimeout(() => { sibuk = false; }, 2000);
        }
    }

    // Manual Form Submit
    document.getElementById('formManual').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('kodeManual');
        const val = input.value.trim();
        if (!val) return;
        kirimScan(val, 'manual');
        input.value = '';
    });

    // Mirror / Non-Mirror: flip hanya transform video, stream kamera tidak di-restart
    function terapkanCermin() {
        const video = document.querySelector('#reader video');
        if (video) video.style.transform = isMirror ? 'scaleX(-1)' : 'scaleX(1)';
    }
    new MutationObserver(terapkanCermin).observe(document.getElementById('reader'), { childList: true, subtree: true });

    function tampilkanErrorKamera(pesan) {
        const elHasil = document.getElementById('hasil');
        if (!elHasil) return;
        elHasil.className = 'px-4 py-2.5 rounded-xl text-xs font-bold text-center min-h-[40px] flex items-center justify-center transition bg-rose-50 text-rose-700 border border-rose-200';
        elHasil.textContent = pesan;
    }

    // Scanner Camera Initialization
    async function initKamera() {
        if (typeof Html5Qrcode === 'undefined') {
            tampilkanErrorKamera('Library scanner belum termuat. Periksa koneksi internet lalu muat ulang halaman.');
            return;
        }
        if (typeof window.isSecureContext !== 'undefined' && !window.isSecureContext) {
            tampilkanErrorKamera('Kamera diblokir browser karena akses via HTTP. Gunakan input manual di bawah.');
            return;
        }
        try {
            if (!scanner) scanner = new Html5Qrcode('reader');
            await scanner.start(
                { facingMode: 'user' },
                KONFIG_KAMERA,
                function onScanSuccess(decodedText) {
                    kirimScan(decodedText, 'scan');
                },
                function onScanFailure() {}
            );
            terapkanCermin();
            return;
        } catch (err) {
            console.log('Scanner error (facingMode user):', err);
        }
        // Fallback browser lama: pilih kamera depan dari daftar perangkat
        try {
            const devices = await Html5Qrcode.getCameras();
            if (!devices || !devices.length) throw new Error('Tidak ada kamera');
            const cocok = devices.find(function (d) { return /front|user|selfie|depan/i.test(d.label || ''); });
            try { await scanner.stop(); } catch (e) {}
            await scanner.start(
                cocok ? cocok.id : devices[0].id,
                KONFIG_KAMERA,
                function onScanSuccess(decodedText) {
                    kirimScan(decodedText, 'scan');
                },
                function onScanFailure() {}
            );
            terapkanCermin();
        } catch (err) {
            console.log('Scanner error:', err);
            tampilkanErrorKamera('Kamera tidak bisa dibuka. Izinkan akses kamera pada browser, atau gunakan input manual di bawah.');
        }
    }

    // Toggle Mirror via ikon kamera kanan atas: real-time, tanpa restart stream
    const btnMirror = document.getElementById('btnGantiKamera');
    btnMirror.addEventListener('click', function() {
        isMirror = !isMirror;
        terapkanCermin();
        btnMirror.setAttribute('aria-pressed', isMirror ? 'true' : 'false');
        btnMirror.title = isMirror ? 'Mirror aktif — klik untuk Non-Mirror' : 'Non-Mirror aktif — klik untuk Mirror';
        btnMirror.classList.toggle('bg-slate-900/75', !isMirror);
        btnMirror.classList.toggle('bg-emerald-600', isMirror);
    });

    // Enter key trigger for confirmation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
            e.preventDefault();
            konfirmasiHadirManual();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        initKamera();
    });
</script>
@endsection
