@extends('layouts.guru')

@section('page_title', 'Buat Ujian Online Baru')

@section('breadcrumb_trail')
    <a href="{{ route('guru.ujian.index') }}" class="hover:text-slate-600 transition">Tugas & Ujian</a>
@endsection

@section('header_actions')
    <a href="{{ route('guru.ujian.index') }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-slate-700 bg-white border border-[#E6EBF2] hover:bg-slate-50 transition">
        <i class="fas fa-arrow-left text-[11px]"></i> Kembali
    </a>
@endsection

@section('content')
<div class="space-y-5 pb-6">
    @if(session('error') || $errors->has('total_bobot'))
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 flex items-start gap-3 shadow-xs">
        <i class="fas fa-circle-exclamation text-rose-600 mt-0.5 text-base shrink-0"></i>
        <div>
            <strong class="font-bold block mb-0.5">Peringatan Validasi:</strong>
            <p>{{ $errors->first('total_bobot') ?: session('error') }}</p>
        </div>
    </div>
    @endif

    <form action="{{ route('guru.ujian.store') }}" method="POST" enctype="multipart/form-data" id="form-ujian" class="space-y-6">
        @csrf

        <!-- SECTION 1: Informasi Dasar Ujian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center font-bold text-xs">1</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Informasi & Pengaturan Ujian</h2>
                    <p class="text-[11px] text-slate-400">Atur judul ujian, mata pelajaran, tingkat kesulitan, dan alokasi waktu.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Judul Ujian -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Judul Ujian <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="judul_quiz" id="ujian-judul-quiz" value="{{ old('judul_quiz') }}" required maxlength="100" placeholder="Contoh: Penilaian Harian Matematika Operasi Hitung" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); document.getElementById('counter-ujian-judul').textContent = this.value.length + '/100';" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition font-medium">
                    <div class="flex items-center justify-between mt-1 text-[10px]">
                        <span class="text-slate-500"><i class="fas fa-shield-halved mr-1 text-[#13527D]"></i>Hanya huruf dan spasi (angka dan simbol tidak diperbolehkan).</span>
                        <span id="counter-ujian-judul" class="font-bold text-slate-600">{{ strlen(old('judul_quiz', '')) }}/100</span>
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Mata Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_mapel" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition font-medium">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id_mapel }}" {{ old('id_mapel') == $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    @if($mapels->isEmpty())
                        <p class="mt-1.5 text-[11px] font-semibold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-2.5 py-1.5">
                            <i class="fas fa-triangle-exclamation mr-1"></i> Belum ada mata pelajaran yang Anda ampu. Hubungi admin untuk penjadwalan.
                        </p>
                    @else
                        <p class="mt-1 text-[10px] text-slate-400">Hanya menampilkan mata pelajaran yang Anda ampu.</p>
                    @endif
                </div>

                <!-- Durasi Ujian -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Durasi Ujian (Menit) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" id="input-durasi-menit" name="durasi_menit" value="{{ old('durasi_menit', 60) }}" min="5" max="300" required oninput="calculateWaktuPerSoal()" class="w-full pl-3.5 pr-14 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition font-medium">
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 font-semibold pointer-events-none">Menit</span>
                    </div>
                </div>

                <!-- KALKULASI OTOMATIS WAKTU PER SOAL -->
                <div class="md:col-span-2">
                    <div class="p-4 bg-gradient-to-r from-sky-50 to-indigo-50/60 rounded-2xl border border-sky-200 shadow-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-sky-200/60">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-stopwatch text-[#13527D]"></i>
                                <span>Kalkulasi Otomatis Alokasi Waktu per Soal</span>
                            </span>
                            <span class="text-[10px] font-semibold text-sky-800 bg-sky-100 px-2 py-0.5 rounded-full border border-sky-200">
                                Sistem Otomatis
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 items-center">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Durasi Total Ujian:</label>
                                <div class="text-xs font-extrabold text-[#13527D] bg-white px-3 py-2 rounded-xl border border-sky-200 flex items-center justify-between">
                                    <span id="badge-durasi-menit">60</span>
                                    <span class="text-[10px] font-semibold text-slate-400">Menit</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Target / Jumlah Soal:</label>
                                <div class="relative">
                                    <input type="number" id="calc-input-soal" min="1" max="200" value="20" oninput="calculateWaktuPerSoal()" class="w-full pl-3 pr-12 py-2 text-xs font-bold text-slate-800 bg-white border border-sky-200 rounded-xl focus:outline-none focus:border-[#13527D]">
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[10px] text-slate-400 font-semibold pointer-events-none">Soal</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Rata-rata Waktu Tiap Soal:</label>
                                <div class="p-2 bg-white rounded-xl border-2 border-[#13527D] shadow-xs flex items-center justify-between">
                                    <div>
                                        <span id="result-waktu-per-soal" class="text-xs font-black text-[#13527D]">3 Menit</span>
                                        <span id="result-detik-per-soal" class="block text-[10px] font-bold text-slate-500">(180 Detik / Soal)</span>
                                    </div>
                                    <div class="w-8 h-8 rounded-lg bg-[#13527D] text-white flex items-center justify-center text-xs">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2.5 pt-2 border-t border-sky-200/50 flex flex-col sm:flex-row sm:items-center justify-between text-[11px] text-slate-600 gap-1.5">
                            <span id="calc-formula-desc">
                                <i class="fas fa-circle-check text-emerald-600 mr-1"></i>
                                Rumus: <strong class="text-slate-800">60 Menit ÷ 20 Soal = 3 Menit per butir soal (180 Detik)</strong>
                            </span>
                            <button type="button" onclick="syncWithQuestionCards()" class="text-[10px] font-bold text-[#13527D] hover:underline flex items-center gap-1 self-start sm:self-auto">
                                <i class="fas fa-arrows-rotate"></i>
                                <span>Samakan dengan <span id="sync-card-count">1</span> kartu soal di bawah</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tingkatan Level Ujian (Mudah, Sedang, Susah) -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Tingkatan Level Ujian <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-400 cursor-pointer transition">
                            <input type="radio" name="tingkat_level" value="mudah" class="w-4 h-4 text-emerald-600 focus:ring-0 mr-3 accent-emerald-600" {{ old('tingkat_level') === 'mudah' ? 'checked' : '' }} required>
                            <div>
                                <span class="block text-xs font-bold text-emerald-800">🟢 Level Mudah</span>
                                <span class="block text-[10px] text-slate-500">Pemahaman konsep dasar, pengenalan materi</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:border-amber-400 cursor-pointer transition">
                            <input type="radio" name="tingkat_level" value="sedang" class="w-4 h-4 text-amber-600 focus:ring-0 mr-3 accent-amber-600" {{ old('tingkat_level', 'sedang') === 'sedang' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-xs font-bold text-amber-800">🟡 Level Sedang</span>
                                <span class="block text-[10px] text-slate-500">Aplikasi rumus & pemahaman logika standar</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:border-rose-400 cursor-pointer transition">
                            <input type="radio" name="tingkat_level" value="susah" class="w-4 h-4 text-rose-600 focus:ring-0 mr-3 accent-rose-600" {{ old('tingkat_level') === 'susah' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-xs font-bold text-rose-800">🔴 Level Susah (HOTS)</span>
                                <span class="block text-[10px] text-slate-500">Analisis tingkat tinggi & penalaran mendalam</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Deskripsi / Petunjuk -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Deskripsi / Petunjuk Pengerjaan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="deskripsi" rows="2" maxlength="1000" placeholder="Tuliskan petunjuk pengerjaan ujian bagi siswa (maksimal 1000 karakter)..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition">{{ old('deskripsi') }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Target Peserta Ujian (Semua vs Siswa Tertentu) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center font-bold text-xs">2</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Target Peserta Ujian</h2>
                    <p class="text-[11px] text-slate-400">Pilih apakah ujian ditujukan untuk seluruh siswa atau hanya siswa-siswa tertentu.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-center p-3.5 rounded-xl border border-slate-200 bg-white hover:border-[#13527D] cursor-pointer transition">
                    <input type="radio" name="target_tipe" value="semua" class="w-4 h-4 text-[#13527D] focus:ring-0 mr-3 accent-[#13527D] target-radio" {{ old('target_tipe', 'semua') === 'semua' ? 'checked' : '' }} onchange="toggleTargetSiswa(false)">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Semua Siswa</span>
                        <span class="block text-[10px] text-slate-500">Seluruh siswa aktif di sekolah dapat mengerjakan ujian ini</span>
                    </div>
                </label>

                <label class="flex items-center p-3.5 rounded-xl border border-slate-200 bg-white hover:border-[#13527D] cursor-pointer transition">
                    <input type="radio" name="target_tipe" value="pilihan" class="w-4 h-4 text-[#13527D] focus:ring-0 mr-3 accent-[#13527D] target-radio" {{ old('target_tipe') === 'pilihan' ? 'checked' : '' }} onchange="toggleTargetSiswa(true)">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Pilih Siswa Tertentu</span>
                        <span class="block text-[10px] text-slate-500">Tentukan nama siswa yang berhak mengerjakan (misal: Remedial / Pengayaan)</span>
                    </div>
                </label>
            </div>

            <!-- Panel Pemilihan Siswa Tertentu -->
            <div id="panel-pilih-siswa" class="{{ old('target_tipe') === 'pilihan' ? '' : 'hidden' }} space-y-3 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Daftar Pilihan Siswa:</span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="checkAllSiswa(true)" class="text-[11px] text-[#13527D] hover:underline font-semibold">Pilih Semua</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="checkAllSiswa(false)" class="text-[11px] text-rose-600 hover:underline font-semibold">Batal Pilih</button>
                    </div>
                </div>

                <!-- Filter Kelas -->
                <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                    @foreach($kelasList as $kelas)
                    <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-200">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200">
                            <span class="font-bold text-xs text-slate-800">Kelas {{ $kelas->nama_kelas }} ({{ $kelas->siswas->count() }} Siswa)</span>
                            <button type="button" onclick="checkClassSiswa('kelas-{{ $kelas->id_kelas }}')" class="text-[10px] font-semibold text-[#13527D] hover:underline">
                                Pilih 1 Kelas Ini
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @forelse($kelas->siswas as $siswa)
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-100 hover:border-slate-300 cursor-pointer text-xs transition">
                                <input type="checkbox" name="target_siswa[]" value="{{ $siswa->id_siswa }}" class="rounded text-[#13527D] focus:ring-0 siswa-cb kelas-{{ $kelas->id_kelas }}" {{ in_array($siswa->id_siswa, old('target_siswa', [])) ? 'checked' : '' }}>
                                <span class="truncate font-medium text-slate-700" title="{{ $siswa->nm_siswa }}">{{ $siswa->nm_siswa }}</span>
                            </label>
                            @empty
                            <span class="text-[11px] text-slate-400 italic col-span-3">Tidak ada data siswa di kelas ini.</span>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- SECTION 3: Input Soal Ujian & Bobot Nilai -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center font-bold text-xs">3</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Input Soal Ujian, Gambar Pendukung & Bobot Nilai</h2>
                        <p class="text-[11px] text-slate-400">Anda dapat langsung menambahkan soal di sini atau menambahkannya nanti pada halaman detail ujian.</p>
                    </div>
                </div>
                <button type="button" onclick="addQuestionCard()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl transition border border-emerald-200">
                    <i class="fas fa-plus"></i> Tambah Soal Baru
                </button>
            </div>

            <!-- INDIKATOR TOTAL BOBOT SOAL (MAKSIMAL 100 POINT) -->
            <div id="bobot-indicator-card" class="p-4 rounded-2xl border transition-all duration-300 bg-gradient-to-r from-slate-50 to-sky-50/50 border-slate-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200/80">
                    <div class="flex items-center gap-2.5">
                        <div id="bobot-icon-badge" class="w-9 h-9 rounded-xl bg-[#13527D] text-white flex items-center justify-center text-sm shadow-xs transition shrink-0">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-slate-800">Indikator Total Bobot Soal</span>
                                <span id="bobot-status-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                    0 / 100 Poin
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">Batas akumulasi bobot seluruh butir soal maksimal adalah <strong class="text-slate-700">100 Poin</strong>.</p>
                        </div>
                    </div>

                    <!-- Metrik Bobot -->
                    <div class="flex items-center gap-3 self-end sm:self-auto bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-xs">
                        <div class="text-right">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Total Terkumpul</span>
                            <span class="text-sm font-black text-slate-800" id="bobot-total-display">0 <span class="text-[10px] font-bold text-slate-400">/ 100</span></span>
                        </div>
                        <div class="h-6 w-px bg-slate-200"></div>
                        <div class="text-right">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Kuota</span>
                            <span class="text-sm font-black text-emerald-600" id="bobot-sisa-display">100 <span class="text-[10px] font-bold text-slate-400">Poin</span></span>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="pt-3 space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] font-bold">
                        <span class="text-slate-600 flex items-center gap-1.5">
                            <i class="fas fa-chart-pie text-slate-400 text-[10px]"></i>
                            <span>Akumulasi Bobot Soal:</span>
                        </span>
                        <span id="bobot-percent-text" class="text-slate-700">0% dari 100 Poin</span>
                    </div>
                    <div class="w-full h-3 bg-slate-200/80 rounded-full overflow-hidden p-0.5 shadow-inner">
                        <div id="bobot-progress-bar" class="h-full rounded-full transition-all duration-300 bg-[#13527D]" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Pesan / Alert Box (Limit 100 Point & Try-Catch Feedback) -->
                <div id="bobot-alert-box" class="mt-3 hidden transition-all duration-300">
                    <!-- Ditampilkan dinamis via JavaScript -->
                </div>
            </div>

            <!-- Wadah Kartu Soal -->
            <div id="soal-container" class="space-y-4">
                <!-- Template soal awal default -->
            </div>

            <div class="text-center pt-2">
                <button type="button" onclick="addQuestionCard()" class="inline-flex items-center gap-2 px-4 py-2 border-2 border-dashed border-slate-300 hover:border-[#13527D] hover:text-[#13527D] text-slate-500 text-xs font-semibold rounded-xl transition">
                    <i class="fas fa-plus-circle"></i> Tambah Satu Soal Lagi
                </button>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('guru.ujian.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-xl transition shadow-md hover:shadow-lg flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Simpan Ujian Online</span>
            </button>
        </div>
    </form>
</div>

<script>
let questionCount = 0;

function toggleTargetSiswa(isPilihan) {
    const panel = document.getElementById('panel-pilih-siswa');
    if (isPilihan) {
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
    }
}

function checkAllSiswa(status) {
    document.querySelectorAll('.siswa-cb').forEach(cb => cb.checked = status);
}

function checkClassSiswa(className) {
    const cbs = document.querySelectorAll('.' + className);
    const anyUnchecked = Array.from(cbs).some(cb => !cb.checked);
    cbs.forEach(cb => cb.checked = anyUnchecked);
}

function calculateTotalBobot() {
    const inputs = document.querySelectorAll('.soal-bobot-input');
    let total = 0;
    inputs.forEach(input => {
        let val = parseFloat(input.value);
        if (isNaN(val) || val < 0) val = 0;
        total += val;
    });
    return Math.round(total * 100) / 100;
}

function updateBobotIndicator(total) {
    const indicatorCard = document.getElementById('bobot-indicator-card');
    const iconBadge = document.getElementById('bobot-icon-badge');
    const statusBadge = document.getElementById('bobot-status-badge');
    const totalDisplay = document.getElementById('bobot-total-display');
    const sisaDisplay = document.getElementById('bobot-sisa-display');
    const percentText = document.getElementById('bobot-percent-text');
    const progressBar = document.getElementById('bobot-progress-bar');
    const alertBox = document.getElementById('bobot-alert-box');

    if (!indicatorCard) return;

    const sisa = Math.max(0, 100 - total);
    const percent = Math.min(100, Math.max(0, Math.round((total / 100) * 100)));

    totalDisplay.innerHTML = `${total} <span class="text-[10px] font-bold text-slate-400">/ 100</span>`;
    sisaDisplay.innerHTML = `${sisa} <span class="text-[10px] font-bold text-slate-400">Poin</span>`;
    percentText.textContent = `${percent}% dari 100 Poin`;
    progressBar.style.width = `${percent}%`;

    // Reset container style
    indicatorCard.className = 'p-4 rounded-2xl border transition-all duration-300 ';
    iconBadge.className = 'w-9 h-9 rounded-xl flex items-center justify-center text-sm shadow-xs transition shrink-0 ';
    progressBar.className = 'h-full rounded-full transition-all duration-300 ';

    if (total === 100) {
        // LIMIT TERCAPAI 100 POINT
        indicatorCard.className += 'bg-emerald-50/60 border-emerald-300 shadow-xs';
        iconBadge.className += 'bg-emerald-600 text-white';
        statusBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200';
        statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Limit 100 Poin Tercapai (Maksimal)';
        sisaDisplay.className = 'text-sm font-black text-emerald-700';
        progressBar.className += 'bg-emerald-600';

        alertBox.className = 'mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-start gap-2.5';
        alertBox.innerHTML = `
            <i class="fas fa-circle-check text-emerald-600 mt-0.5 text-base shrink-0"></i>
            <div>
                <strong class="font-bold block mb-0.5">Limit 100 Poin Telah Tercapai!</strong>
                <p class="text-emerald-700 leading-relaxed">Total bobot seluruh butir soal telah mencapai batas maksimal <strong>100 Poin</strong>. Distribusi bobot nilai sudah pas dan siap untuk diujikan ke siswa.</p>
            </div>
        `;
        alertBox.classList.remove('hidden');
    } else if (total > 100) {
        // MELEBIHI BATAS LIMIT 100 POINT
        const kelebihan = Math.round((total - 100) * 100) / 100;
        indicatorCard.className += 'bg-rose-50/60 border-rose-300 shadow-xs';
        iconBadge.className += 'bg-rose-600 text-white';
        statusBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200';
        statusBadge.innerHTML = `<i class="fas fa-triangle-exclamation mr-1"></i> Melebihi Limit (${total}/100)`;
        sisaDisplay.className = 'text-sm font-black text-rose-600';
        sisaDisplay.innerHTML = `-${kelebihan} <span class="text-[10px] font-bold text-rose-400">Poin (Lebih)</span>`;
        progressBar.className += 'bg-rose-600';

        alertBox.className = 'mt-3 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start gap-2.5';
        alertBox.innerHTML = `
            <i class="fas fa-circle-exclamation text-rose-600 mt-0.5 text-base shrink-0"></i>
            <div>
                <strong class="font-bold block mb-0.5">Peringatan: Total Bobot Melebihi Batas Maksimal 100 Poin!</strong>
                <p class="text-rose-700 leading-relaxed">Total bobot soal saat ini adalah <strong>${total} poin</strong>, melebihi limit maksimal 100 poin. Kurangi bobot soal sebanyak <strong>${kelebihan} poin</strong> sebelum menyimpan ujian.</p>
            </div>
        `;
        alertBox.classList.remove('hidden');
    } else {
        // NORMAL / BELUM MENCAPAI 100 POINT
        indicatorCard.className += 'bg-gradient-to-r from-slate-50 to-sky-50/50 border-slate-200';
        iconBadge.className += 'bg-[#13527D] text-white';
        statusBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700';
        statusBadge.textContent = `${total} / 100 Poin`;
        sisaDisplay.className = 'text-sm font-black text-emerald-600';
        progressBar.className += 'bg-[#13527D]';

        if (total > 0) {
            alertBox.className = 'mt-3 p-2.5 bg-blue-50/70 border border-blue-200 rounded-xl text-[11px] text-blue-800 flex items-center justify-between gap-2';
            alertBox.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fas fa-info-circle text-[#13527D]"></i>
                    <span>Tersisa <strong>${sisa} Poin</strong> untuk mencapai bobot ideal 100 poin.</span>
                </div>
                <span class="text-[10px] font-semibold text-blue-600 bg-white px-2 py-0.5 rounded-md border border-blue-200">Maksimal 100 Poin</span>
            `;
            alertBox.classList.remove('hidden');
        } else {
            alertBox.classList.add('hidden');
        }
    }
}

function handleBobotChange(input) {
    try {
        let val = parseFloat(input.value);
        if (isNaN(val) || val < 0) {
            input.value = 0;
            val = 0;
        }

        if (val > 100) {
            input.value = 100;
            throw new Error("Bobot untuk satu butir soal tidak boleh melebihi 100 poin. Nilai telah disesuaikan menjadi 100 poin.");
        }

        const total = calculateTotalBobot();
        updateBobotIndicator(total);

        if (total > 100) {
            const kelebihan = Math.round((total - 100) * 100) / 100;
            throw new Error(`Total akumulasi bobot soal (${total} poin) melebihi batas maksimal 100 poin! Harap kurangi bobot soal sebesar ${kelebihan} poin.`);
        }
    } catch (error) {
        const alertBox = document.getElementById('bobot-alert-box');
        if (alertBox) {
            alertBox.className = 'mt-3 p-3 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900 flex items-start gap-2.5 animate-pulse';
            alertBox.innerHTML = `
                <i class="fas fa-triangle-exclamation text-amber-600 mt-0.5 text-base shrink-0"></i>
                <div>
                    <strong class="font-bold block mb-0.5">Peringatan Validasi Bobot:</strong>
                    <p class="text-amber-800 leading-relaxed">${error.message}</p>
                </div>
            `;
            alertBox.classList.remove('hidden');
        }
    }
}

function addQuestionCard(defaultData = null) {
    try {
        const currentTotal = calculateTotalBobot();
        if (currentTotal >= 100 && !defaultData) {
            throw new Error("Total bobot soal sudah mencapai batas limit maksimal 100 poin! Anda tidak dapat menambah butir soal baru sebelum mengurangi bobot soal yang sudah ada.");
        }

        // Tentukan bobot default agar tidak langsung melebihi limit 100
        let defaultBobot = 10;
        if (defaultData && typeof defaultData.bobot !== 'undefined') {
            defaultBobot = defaultData.bobot;
        } else {
            const sisa = Math.max(0, 100 - currentTotal);
            defaultBobot = Math.min(10, Math.max(1, sisa));
        }

        questionCount++;
        const idx = questionCount - 1;
        const container = document.getElementById('soal-container');

        const card = document.createElement('div');
        card.className = 'soal-card bg-slate-50/70 rounded-2xl p-5 border border-slate-200 space-y-4 transition';
        card.id = `soal-card-${idx}`;

        card.innerHTML = `
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-[#13527D] text-white flex items-center justify-center font-bold text-xs soal-number">${questionCount}</span>
                    <span class="text-xs font-bold text-slate-800">Pertanyaan Soal</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                    <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-slate-200 focus-within:border-[#13527D]">
                        <label class="text-[11px] font-bold text-slate-600">Bobot:</label>
                        <input type="number" name="soal[${idx}][bobot_nilai]" value="${defaultBobot}" min="1" max="100" oninput="handleBobotChange(this)" onchange="handleBobotChange(this)" class="w-14 px-1.5 py-0.5 text-xs font-bold text-center border border-slate-200 rounded focus:outline-none focus:border-[#13527D] soal-bobot-input">
                        <span class="text-[10px] text-slate-400 font-semibold">Poin</span>
                    </div>
                    <button type="button" onclick="removeQuestionCard(${idx})" class="text-slate-400 hover:text-rose-600 text-xs transition p-1" title="Hapus Soal Ini">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Pertanyaan <span class="text-rose-500">*</span></label>
                <textarea name="soal[${idx}][pertanyaan]" rows="2" maxlength="2000" placeholder="Tuliskan pertanyaan soal (maksimal 2000 karakter)..." class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D]">${defaultData ? defaultData.pertanyaan : ''}</textarea>
            </div>

            <!-- Gambar Pendukung Soal (Opsional) -->
            <div class="bg-white p-3 rounded-xl border border-slate-200">
                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                    <i class="far fa-image text-slate-400 mr-1"></i> Gambar Pendukung Soal <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <div class="flex items-center gap-3">
                    <input type="file" name="soal[${idx}][gambar]" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" class="text-xs text-slate-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer" onchange="previewImage(this, 'preview-${idx}')">
                </div>
                <div id="preview-${idx}" class="mt-2 hidden">
                    <img src="" alt="Preview Gambar" class="max-h-36 rounded-lg border border-slate-200 object-contain">
                </div>
            </div>

            <!-- Pilihan Jawaban A, B, C, D dan Kunci Jawaban -->
            <div class="space-y-2">
                <label class="block text-[11px] font-bold text-slate-700">Pilihan Jawaban (Pilih Kunci Jawaban yang Benar):</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                    <!-- Opsi A -->
                    <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 focus-within:border-[#13527D]">
                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                            <input type="radio" name="soal[${idx}][kunci_jawaban]" value="A" ${!defaultData || defaultData.kunci === 'A' ? 'checked' : ''} class="text-emerald-600 focus:ring-0">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">A</span>
                        </label>
                        <input type="text" name="soal[${idx}][opsi_a]" value="${defaultData && defaultData.opsi_a ? defaultData.opsi_a : ''}" maxlength="255" placeholder="Pilihan jawaban A" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                    </div>

                    <!-- Opsi B -->
                    <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 focus-within:border-[#13527D]">
                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                            <input type="radio" name="soal[${idx}][kunci_jawaban]" value="B" ${defaultData && defaultData.kunci === 'B' ? 'checked' : ''} class="text-emerald-600 focus:ring-0">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">B</span>
                        </label>
                        <input type="text" name="soal[${idx}][opsi_b]" value="${defaultData && defaultData.opsi_b ? defaultData.opsi_b : ''}" maxlength="255" placeholder="Pilihan jawaban B" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                    </div>

                    <!-- Opsi C -->
                    <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 focus-within:border-[#13527D]">
                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                            <input type="radio" name="soal[${idx}][kunci_jawaban]" value="C" ${defaultData && defaultData.kunci === 'C' ? 'checked' : ''} class="text-emerald-600 focus:ring-0">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">C</span>
                        </label>
                        <input type="text" name="soal[${idx}][opsi_c]" value="${defaultData && defaultData.opsi_c ? defaultData.opsi_c : ''}" maxlength="255" placeholder="Pilihan jawaban C" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                    </div>

                    <!-- Opsi D -->
                    <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 focus-within:border-[#13527D]">
                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                            <input type="radio" name="soal[${idx}][kunci_jawaban]" value="D" ${defaultData && defaultData.kunci === 'D' ? 'checked' : ''} class="text-emerald-600 focus:ring-0">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">D</span>
                        </label>
                        <input type="text" name="soal[${idx}][opsi_d]" value="${defaultData && defaultData.opsi_d ? defaultData.opsi_d : ''}" maxlength="255" placeholder="Pilihan jawaban D" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 italic">Centang radio button pada opsi huruf untuk menandai kunci jawaban yang benar.</p>
            </div>
        `;

        container.appendChild(card);
        reindexCards();
        const newTotal = calculateTotalBobot();
        updateBobotIndicator(newTotal);
    } catch (error) {
        const alertBox = document.getElementById('bobot-alert-box');
        if (alertBox) {
            alertBox.className = 'mt-3 p-3 bg-rose-50 border border-rose-300 rounded-xl text-xs text-rose-900 flex items-start gap-2.5';
            alertBox.innerHTML = `
                <i class="fas fa-ban text-rose-600 mt-0.5 text-base shrink-0"></i>
                <div>
                    <strong class="font-bold block mb-0.5">Tidak Dapat Menambah Soal:</strong>
                    <p class="text-rose-800 leading-relaxed">${error.message}</p>
                </div>
            `;
            alertBox.classList.remove('hidden');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        alert(error.message);
    }
}

function removeQuestionCard(idx) {
    const card = document.getElementById(`soal-card-${idx}`);
    if (card) {
        card.remove();
        reindexCards();
        const total = calculateTotalBobot();
        updateBobotIndicator(total);
    }
}

function reindexCards() {
    const cards = document.querySelectorAll('.soal-card');
    cards.forEach((c, i) => {
        const num = c.querySelector('.soal-number');
        if (num) num.textContent = i + 1;
    });
    const syncCount = document.getElementById('sync-card-count');
    if (syncCount) syncCount.textContent = cards.length;
}

function calculateWaktuPerSoal() {
    const durasiInput = document.getElementById('input-durasi-menit');
    const soalInput = document.getElementById('calc-input-soal');
    if (!durasiInput || !soalInput) return;

    let durasi = parseInt(durasiInput.value) || 0;
    let totalSoal = parseInt(soalInput.value) || 0;

    const badgeDurasi = document.getElementById('badge-durasi-menit');
    if (badgeDurasi) badgeDurasi.textContent = durasi;

    const resWaktu = document.getElementById('result-waktu-per-soal');
    const resDetik = document.getElementById('result-detik-per-soal');
    const formulaDesc = document.getElementById('calc-formula-desc');

    if (durasi <= 0 || totalSoal <= 0) {
        if (resWaktu) resWaktu.textContent = '-';
        if (resDetik) resDetik.textContent = '(0 Detik / Soal)';
        if (formulaDesc) formulaDesc.innerHTML = '<i class="fas fa-circle-info text-amber-500 mr-1"></i> Masukkan durasi dan jumlah soal valid untuk kalkulasi.';
        return;
    }

    const totalSeconds = durasi * 60;
    const secPerSoal = Math.round(totalSeconds / totalSoal);
    const m = Math.floor(secPerSoal / 60);
    const s = secPerSoal % 60;

    let timeText = '';
    if (m > 0 && s > 0) {
        timeText = `${m} Menit ${s} Detik`;
    } else if (m > 0) {
        timeText = `${m} Menit`;
    } else {
        timeText = `${s} Detik`;
    }

    if (resWaktu) resWaktu.textContent = timeText;
    if (resDetik) resDetik.textContent = `(${secPerSoal} Detik / Soal)`;
    if (formulaDesc) {
        formulaDesc.innerHTML = `<i class="fas fa-circle-check text-emerald-600 mr-1"></i> Rumus: <strong class="text-slate-800">${durasi} Menit ÷ ${totalSoal} Soal = ${timeText} per butir soal (${secPerSoal} Detik)</strong>`;
    }
}

function syncWithQuestionCards() {
    const count = document.querySelectorAll('.soal-card').length;
    const soalInput = document.getElementById('calc-input-soal');
    if (soalInput && count > 0) {
        soalInput.value = count;
        calculateWaktuPerSoal();
    }
}

function previewImage(input, previewId) {
    const previewContainer = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = previewContainer.querySelector('img');
            img.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('hidden');
    }
}

// Inisialisasi 1 soal awal jika form baru & proteksi form submit
document.addEventListener('DOMContentLoaded', function() {
    addQuestionCard();
    calculateWaktuPerSoal();
    const initialTotal = calculateTotalBobot();
    updateBobotIndicator(initialTotal);

    const formUjian = document.getElementById('form-ujian');
    if (formUjian) {
        formUjian.addEventListener('submit', function(e) {
            try {
                const total = calculateTotalBobot();
                if (total > 100) {
                    const kelebihan = Math.round((total - 100) * 100) / 100;
                    throw new Error(`Gagal menyimpan ujian: Total bobot soal (${total} poin) melebihi batas maksimal 100 poin! Harap kurangi bobot soal sebanyak ${kelebihan} poin.`);
                }
            } catch (error) {
                e.preventDefault();
                const indicatorCard = document.getElementById('bobot-indicator-card');
                if (indicatorCard) {
                    indicatorCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                alert(error.message);
                return false;
            }
        });
    }
});
</script>
@endsection
