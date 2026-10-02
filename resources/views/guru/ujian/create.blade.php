@extends('layouts.guru')

@section('content')
<div class="p-8 space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.ujian.index') }}" class="hover:text-[#13527D] transition">Ujian Online</a>
                <span>/</span>
                <span class="text-[#13527D] font-semibold">Buat Ujian Baru</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Buat Ujian Online Baru</h1>
            <p class="text-xs text-slate-500">Tentukan konfigurasi ujian, target siswa, tingkat level, dan susun soal-soal ujian.</p>
        </div>
        <a href="{{ route('guru.ujian.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
        <div class="font-bold mb-1 flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-600 text-sm"></i> Periksa kembali isian formulir:
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-rose-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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

function addQuestionCard(defaultData = null) {
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
                <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                    <label class="text-[11px] font-bold text-slate-600">Bobot:</label>
                    <input type="number" name="soal[${idx}][bobot_nilai]" value="${defaultData ? defaultData.bobot : 10}" min="1" max="100" class="w-14 px-1.5 py-0.5 text-xs font-bold text-center border border-slate-200 rounded focus:outline-none focus:border-[#13527D]">
                    <span class="text-[10px] text-slate-400 font-semibold">Poin</span>
                </div>
                <div class="flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                    <label class="text-[11px] font-bold text-slate-600">Tingkat:</label>
                    <select name="soal[${idx}][tingkat_kesulitan]" class="text-xs font-bold bg-transparent border-0 focus:ring-0 text-slate-700 cursor-pointer">
                        <option value="mudah" ${defaultData && defaultData.kesulitan === 'mudah' ? 'selected' : ''}>🟢 Mudah</option>
                        <option value="sedang" ${(!defaultData || !defaultData.kesulitan || defaultData.kesulitan === 'sedang') ? 'selected' : ''}>🟡 Sedang</option>
                        <option value="sulit" ${defaultData && (defaultData.kesulitan === 'sulit' || defaultData.kesulitan === 'susah') ? 'selected' : ''}>🔴 Sulit</option>
                    </select>
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
                        <input type="radio" name="soal[${idx}][kunci_jawaban]" value="A" checked class="text-emerald-600 focus:ring-0">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">A</span>
                    </label>
                    <input type="text" name="soal[${idx}][opsi_a]" maxlength="255" placeholder="Pilihan jawaban A" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                </div>

                <!-- Opsi B -->
                <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 focus-within:border-[#13527D]">
                    <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                        <input type="radio" name="soal[${idx}][kunci_jawaban]" value="B" class="text-emerald-600 focus:ring-0">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">B</span>
                    </label>
                    <input type="text" name="soal[${idx}][opsi_b]" maxlength="255" placeholder="Pilihan jawaban B" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                </div>

                <!-- Opsi C -->
                <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 focus-within:border-[#13527D]">
                    <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                        <input type="radio" name="soal[${idx}][kunci_jawaban]" value="C" class="text-emerald-600 focus:ring-0">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">C</span>
                    </label>
                    <input type="text" name="soal[${idx}][opsi_c]" maxlength="255" placeholder="Pilihan jawaban C" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                </div>

                <!-- Opsi D -->
                <div class="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200 focus-within:border-[#13527D]">
                    <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                        <input type="radio" name="soal[${idx}][kunci_jawaban]" value="D" class="text-emerald-600 focus:ring-0">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">D</span>
                    </label>
                    <input type="text" name="soal[${idx}][opsi_d]" maxlength="255" placeholder="Pilihan jawaban D" class="w-full text-xs border-0 focus:ring-0 p-0 text-slate-800 placeholder-slate-400">
                </div>
            </div>
            <p class="text-[10px] text-slate-400 italic">Centang radio button pada opsi huruf untuk menandai kunci jawaban yang benar.</p>
        </div>
    `;

    container.appendChild(card);
    reindexCards();
}

function removeQuestionCard(idx) {
    const card = document.getElementById(`soal-card-${idx}`);
    if (card) {
        card.remove();
        reindexCards();
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

// Inisialisasi 1 soal awal jika form baru
document.addEventListener('DOMContentLoaded', function() {
    addQuestionCard();
    calculateWaktuPerSoal();
});
</script>
@endsection
