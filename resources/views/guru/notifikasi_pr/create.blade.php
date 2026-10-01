@extends('layouts.guru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Title -->
    <div>
        <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('guru.notifikasi_pr.index') }}" class="hover:text-[#13527D] font-medium flex items-center gap-1">
                <i class="fas fa-arrow-left text-[10px]"></i> Kelola Notifikasi PR
            </a>
            <span>&bull;</span>
            <span class="text-slate-800 font-bold">Tambah Tugas PR Baru</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Buat Tugas PR & Kirimkan Notifikasi</h1>
        <p class="text-xs text-slate-500">Isi rincian tugas PR di bawah dan kirimkan notifikasi pengingat ke siswa.</p>
    </div>

    <!-- Error Validation Alert (Laravel Validation) -->
    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl space-y-1">
        <div class="font-bold flex items-center gap-2">
            <i class="fas fa-exclamation-triangle text-rose-600"></i> Mohon periksa inputan berikut:
        </div>
        <ul class="list-disc list-inside space-y-0.5 pl-2 text-[11px]">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Try-Catch Error Alert (Duplikasi / Exception dari Controller) -->
    @if(session('error'))
    <div id="create-error-alert" class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 text-xs rounded-xl flex items-start gap-3 shadow-sm animate-pulse-once">
        <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm shrink-0">
            <i class="fas fa-circle-xmark"></i>
        </div>
        <div class="flex-1">
            <div class="font-extrabold text-rose-700 mb-0.5 flex items-center gap-1.5">
                <i class="fas fa-triangle-exclamation text-[10px]"></i>
                Gagal Memproses — Terjadi Kesalahan
            </div>
            <p class="text-[11px] leading-relaxed text-rose-700">{{ session('error') }}</p>
        </div>
        <button onclick="document.getElementById('create-error-alert').remove()" class="text-rose-400 hover:text-rose-700 text-sm shrink-0" title="Tutup">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <form action="{{ route('guru.notifikasi_pr.store') }}" method="POST" id="form-create-pr" onsubmit="return validateBeforeSubmit()" class="space-y-6">
        @csrf

        <!-- Card 1: Informasi Tugas PR -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center font-bold text-sm">
                    <i class="fas fa-book-open"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Rincian Pekerjaan Rumah (PR)</h2>
                    <p class="text-[11px] text-slate-400">Atribut standar database: nama_pr, id_mapel, tgl_tenggat, deskripsi</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Judul PR -->
                <div class="md:col-span-2">
                    <label for="nama_pr" class="block text-xs font-bold text-slate-700 mb-1">
                        Judul / Nama Tugas PR <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_pr" id="nama_pr" value="{{ old('nama_pr') }}" required
                           oninput="updateLivePreview()"
                           placeholder="Contoh: Latihan Soal Cerita Operasi Hitung Campuran"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label for="id_mapel" class="block text-xs font-bold text-slate-700 mb-1">
                        Mata Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_mapel" id="id_mapel" required onchange="updateLivePreview()"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id_mapel }}" {{ old('id_mapel') == $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal & Jam Pengumpulan (Jam Operasional 07:00 - 14:00) -->
                <div class="space-y-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Tanggal Pengumpulan -->
                        <div>
                            <label for="input_tanggal" class="block text-xs font-bold text-slate-700 mb-1">
                                Tanggal Tenggat <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="input_tanggal" name="tanggal_tenggat"
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ old('tanggal_tenggat', $defaultTanggal ?? date('Y-m-d', strtotime('+1 day'))) }}" required
                                   onchange="syncDateTimeAndPreview()"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                        </div>

                        <!-- Jam Pengumpulan (07:00 - 14:00 WIB) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">
                                    Jam Tenggat <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] font-bold text-slate-400">07:00 - 14:00 WIB</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <!-- Pilih Jam 07 s/d 14 -->
                                <select id="select_jam" onchange="syncDateTimeAndPreview()"
                                        class="w-1/2 px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#13527D]">
                                    @for($h = 7; $h <= 14; $h++)
                                        @php $hh = sprintf('%02d', $h); @endphp
                                        <option value="{{ $hh }}" {{ $hh == '07' ? 'selected' : '' }}>
                                            {{ $hh }}
                                        </option>
                                    @endfor
                                </select>
                                <span class="font-extrabold text-slate-400">:</span>
                                <!-- Pilih Menit 00-59 -->
                                <select id="select_menit" onchange="syncDateTimeAndPreview()"
                                        class="w-1/2 px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#13527D]">
                                    @foreach(['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'] as $mm)
                                        <option value="{{ $mm }}" {{ $mm == '30' ? 'selected' : '' }}>
                                            {{ $mm }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="px-2 py-2.5 bg-slate-100 text-slate-600 font-extrabold text-[11px] rounded-xl border border-slate-200 shrink-0">
                                    WIB
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden input tgl_tenggat final (Format YYYY-MM-DD HH:mm:ss) -->
                    <input type="hidden" name="tgl_tenggat" id="tgl_tenggat" value="{{ old('tgl_tenggat') }}">

                    <!-- Info Ketentuan Jam Operasi Sekolah -->
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-200 flex items-center gap-2">
                        <i class="fas fa-school text-[#13527D] shrink-0"></i>
                        <span><strong>Jam Operasi Sekolah:</strong> Pukul <strong>07:00 - 14:00 WIB</strong> (Hari Minggu libur). Tidak dapat memilih hari dan jam yang sudah lewat.</span>
                    </div>

                    <!-- Alert Visual Human Error Warning -->
                    <div id="alert-tenggat-warning" class="hidden p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                        <i class="fas fa-triangle-exclamation text-rose-500 text-sm shrink-0"></i>
                        <span id="alert-tenggat-text" class="font-medium"></span>
                    </div>
                </div>

                <!-- Deskripsi / Petunjuk Pengerjaan -->
                <div class="md:col-span-2">
                    <label for="deskripsi" class="block text-xs font-bold text-slate-700 mb-1">
                        Deskripsi & Petunjuk Pengerjaan
                    </label>
                    <textarea name="deskripsi" id="deskripsi" rows="4"
                              placeholder="Tuliskan petunjuk pengerjaan tugas, nomor halaman buku paket, atau instruksi khusus untuk siswa..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">{{ old('deskripsi') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Card 2: Pengaturan Notifikasi Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Kirimkan Notifikasi ke Siswa</h2>
                        <p class="text-[11px] text-slate-400">Notifikasi akan otomatis masuk ke akun portal siswa yang dituju</p>
                    </div>
                </div>

                <!-- Toggle Kirim Notifikasi -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="kirim_notifikasi" id="kirim_notifikasi" value="1" checked onchange="toggleNotifSection(this.checked)" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#13527D]"></div>
                    <span class="ml-2.5 text-xs font-bold text-slate-700">Kirim Notifikasi Sekarang</span>
                </label>
            </div>

            <div id="notif-settings-body" class="space-y-4">
                <!-- Target Penerima -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Sasaran Siswa Penerima</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <label class="border-2 border-[#13527D]/30 bg-sky-50/50 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-sky-50 transition" id="lbl-target-semua">
                            <input type="radio" name="target_tipe" value="semua" checked onchange="handleTargetChange(this.value)" class="text-[#13527D]">
                            <div>
                                <div class="font-bold text-slate-900 text-xs">Semua Siswa</div>
                                <div class="text-[10px] text-slate-500">Kirim ke seluruh siswa terdaftar</div>
                            </div>
                        </label>
                        <label class="border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition" id="lbl-target-kelas">
                            <input type="radio" name="target_tipe" value="kelas" onchange="handleTargetChange(this.value)" class="text-[#13527D]">
                            <div>
                                <div class="font-bold text-slate-900 text-xs">Berdasarkan Kelas</div>
                                <div class="text-[10px] text-slate-500">Pilih rombel / kelas spesifik</div>
                            </div>
                        </label>
                        <label class="border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition" id="lbl-target-siswa">
                            <input type="radio" name="target_tipe" value="siswa" onchange="handleTargetChange(this.value)" class="text-[#13527D]">
                            <div>
                                <div class="font-bold text-slate-900 text-xs">Pilih Siswa Tertentu</div>
                                <div class="text-[10px] text-slate-500">Pilih individu siswa secara manual</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Dropdown Pilih Kelas (Tampil jika target 'kelas') -->
                <div id="target-kelas-container" class="hidden">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kelas Sasaran <span class="text-rose-500">*</span></label>
                    <select name="target_kelas" id="target_kelas" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id_rooms }}">{{ $k->pararel }} ({{ $k->siswas_count }} Siswa)</option>
                        @endforeach
                    </select>
                </div>

                <!-- Multi-select Siswa (Tampil jika target 'siswa') -->
                <div id="target-siswa-container" class="hidden space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Pilih Siswa Sasaran <span class="text-rose-500">*</span></label>
                    <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl p-2.5 space-y-1 bg-slate-50">
                        @foreach($siswas as $s)
                        <label class="flex items-center gap-2.5 p-1.5 hover:bg-white rounded-lg text-xs cursor-pointer">
                            <input type="checkbox" name="target_siswa[]" value="{{ $s->id_siswa }}" class="rounded text-[#13527D]">
                            <span class="font-bold text-slate-800">{{ $s->nm_siswa }}</span>
                            <span class="text-[10px] text-slate-400">NISN: {{ $s->nisn }} &bull; {{ $s->kelas->pararel ?? 'Siswa' }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Kustomisasi Pesan Notifikasi -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="pesan_kustom" class="text-xs font-bold text-slate-700">Pesan Notifikasi yang Dikirimkan</label>
                        <button type="button" onclick="resetDefaultPesan()" class="text-[10px] font-bold text-[#13527D] hover:underline">
                            <i class="fas fa-magic mr-1"></i> Format Otomatis
                        </button>
                    </div>
                    <textarea name="pesan_kustom" id="pesan_kustom" rows="3"
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]"
                              placeholder="Pesan otomatis akan dibentuk sesuai judul PR dan tenggat waktu..."></textarea>
                </div>

                <!-- Live Preview Notifikasi Siswa -->
                <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl text-xs space-y-2">
                    <div class="flex items-center gap-2 font-bold text-amber-900 text-[11px] uppercase tracking-wider">
                        <i class="fas fa-mobile-alt"></i> Pratinjau Tampilan Notifikasi di Akun Siswa:
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-amber-200/80 shadow-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold text-[#13527D]" id="preview-tag-mapel">[Mata Pelajaran]</span>
                            <span class="text-[9px] text-slate-400">Baru Saja</span>
                        </div>
                        <p class="text-xs font-medium text-slate-800 leading-relaxed" id="preview-text-body">
                            Tugas PR Baru: '...' Silakan periksa instruksi tugas dan kumpulkan tepat waktu.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Simpan -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('guru.notifikasi_pr.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-extrabold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span id="btn-submit-text">Simpan Tugas PR & Kirimkan</span>
            </button>
        </div>
    </form>
</div>

<script>
function toggleNotifSection(isChecked) {
    document.getElementById('notif-settings-body').style.display = isChecked ? 'block' : 'none';
    document.getElementById('btn-submit-text').innerText = isChecked ? 'Simpan Tugas PR & Kirimkan' : 'Simpan Tugas PR Saja';
}

function handleTargetChange(tipe) {
    document.getElementById('target-kelas-container').classList.toggle('hidden', tipe !== 'kelas');
    document.getElementById('target-siswa-container').classList.toggle('hidden', tipe !== 'siswa');

    // Styling borders
    ['semua', 'kelas', 'siswa'].forEach(t => {
        const lbl = document.getElementById('lbl-target-' + t);
        if (t === tipe) {
            lbl.className = 'border-2 border-[#13527D]/40 bg-sky-50/50 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer';
        } else {
            lbl.className = 'border border-slate-200 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition';
        }
    });
}

function showTenggatWarning(message) {
    const alertBox = document.getElementById('alert-tenggat-warning');
    const alertText = document.getElementById('alert-tenggat-text');
    if (message) {
        alertText.innerText = message;
        alertBox.classList.remove('hidden');
    } else {
        alertBox.classList.add('hidden');
        alertText.innerText = '';
    }
}

function validateDateTimeLive() {
    const tglVal = document.getElementById('input_tanggal').value;
    const jamVal = document.getElementById('select_jam').value || '07';
    let menitVal = document.getElementById('select_menit').value || '00';

    if (!tglVal) {
        showTenggatWarning('');
        return true;
    }

    // 1. Cek Hari Minggu
    const dParts = tglVal.split('-');
    const pickedDate = new Date(parseInt(dParts[0], 10), parseInt(dParts[1], 10) - 1, parseInt(dParts[2], 10));
    if (pickedDate.getDay() === 0) { // 0 = Minggu
        showTenggatWarning('Peringatan: Hari Minggu adalah hari libur sekolah. Silakan pilih hari operasional (Senin s/d Sabtu).');
        return false;
    }

    // 2. Jika jam 14, batas akhir operasional adalah 14:00 WIB
    if (jamVal === '14' && menitVal !== '00') {
        document.getElementById('select_menit').value = '00';
        menitVal = '00';
    }

    // 3. Cek Waktu Realtime (Masa Depan)
    const selectedDate = new Date(parseInt(dParts[0], 10), parseInt(dParts[1], 10) - 1, parseInt(dParts[2], 10), parseInt(jamVal, 10), parseInt(menitVal, 10), 0);
    const now = new Date();

    if (selectedDate <= now) {
        showTenggatWarning('Peringatan: Waktu tenggat pengumpulan yang dipilih sudah lewat dari waktu realtime saat ini. Silakan tentukan jam/tanggal di masa depan.');
        return false;
    }

    // 4. Cek Batas Jam Operasional 07:00 - 14:00
    const timeNum = parseInt(jamVal + menitVal, 10);
    if (timeNum < 700 || timeNum > 1400) {
        showTenggatWarning('Peringatan: Jam pengumpulan PR harus berada dalam jam operasional sekolah (07:00 - 14:00 WIB).');
        return false;
    }

    showTenggatWarning('');
    return true;
}

function syncDateTimeAndPreview() {
    const tgl = document.getElementById('input_tanggal').value;
    const jam = document.getElementById('select_jam').value || '07';
    const menit = document.getElementById('select_menit').value || '00';

    if (tgl) {
        document.getElementById('tgl_tenggat').value = `${tgl} ${jam}:${menit}:00`;
    }

    validateDateTimeLive();
    updateLivePreview();
}

function validateBeforeSubmit() {
    const isValid = validateDateTimeLive();
    if (!isValid) {
        const warningBox = document.getElementById('alert-tenggat-warning');
        warningBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    return true;
}

function updateLivePreview() {
    const namaPr = document.getElementById('nama_pr').value.trim() || 'Judul PR';
    const mapelSelect = document.getElementById('id_mapel');
    const mapelText = mapelSelect.options[mapelSelect.selectedIndex]?.text.trim() || 'Mata Pelajaran';
    const tglVal = document.getElementById('input_tanggal').value;
    const jamVal = document.getElementById('select_jam').value || '07';
    const menitVal = document.getElementById('select_menit').value || '00';

    let tenggatFormatted = 'Tenggat Waktu';
    if (tglVal) {
        const parts = tglVal.split('-');
        if (parts.length === 3) {
            const bulanList = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const blnIdx = parseInt(parts[1], 10) - 1;
            const blnNama = bulanList[blnIdx] || parts[1];
            tenggatFormatted = `${parts[2]} ${blnNama} ${parts[0]}, ${jamVal}:${menitVal} WIB`;
        }
    }

    const defaultMsg = `Tugas PR Baru: '${namaPr}' (${mapelText}). Batas pengumpulan: ${tenggatFormatted}. Silakan periksa instruksi tugas dan kumpulkan tepat waktu.`;

    const pesanKustom = document.getElementById('pesan_kustom');
    if (!pesanKustom.value || pesanKustom.dataset.autoGenerated === 'true') {
        pesanKustom.value = defaultMsg;
        pesanKustom.dataset.autoGenerated = 'true';
    }

    document.getElementById('preview-tag-mapel').innerText = mapelText;
    document.getElementById('preview-text-body').innerText = pesanKustom.value || defaultMsg;
}

function resetDefaultPesan() {
    const pesanKustom = document.getElementById('pesan_kustom');
    pesanKustom.dataset.autoGenerated = 'true';
    pesanKustom.value = '';
    updateLivePreview();
}

document.getElementById('pesan_kustom').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
    document.getElementById('preview-text-body').innerText = this.value;
});

// Run initial preview and sync
document.addEventListener('DOMContentLoaded', function() {
    syncDateTimeAndPreview();
});
</script>
@endsection
