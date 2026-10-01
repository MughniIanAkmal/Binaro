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
            <span class="text-slate-800 font-bold">Edit PR</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900">Perbarui Data Tugas PR</h1>
        <p class="text-xs text-slate-500">Ubah judul, tenggat, instruksi, atau kirimkan notifikasi pembaruan ke siswa.</p>
    </div>

    <!-- Error Validation Alert -->
    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl space-y-1">
        <div class="font-bold flex items-center gap-2">
            <i class="fas fa-exclamation-triangle text-rose-600"></i> Terjadi kesalahan validasi:
        </div>
        <ul class="list-disc list-inside space-y-0.5 pl-2 text-[11px]">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('guru.notifikasi_pr.update', $pr->id_pr) }}" method="POST" onsubmit="return validateEditBeforeSubmit()" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Data PR -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center font-bold text-sm">
                    <i class="fas fa-pen-to-square"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Edit Rincian Tugas</h2>
                    <p class="text-[11px] text-slate-400">ID PR #{{ $pr->id_pr }} &bull; Dibuat pada {{ $pr->created_at ? $pr->created_at->translatedFormat('d M Y, H:i') : '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Judul PR -->
                <div class="md:col-span-2">
                    <label for="nama_pr" class="block text-xs font-bold text-slate-700 mb-1">
                        Judul / Nama Tugas PR <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_pr" id="nama_pr" value="{{ old('nama_pr', $pr->nama_pr) }}" required maxlength="100"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s\-_.,()]/g, ''); document.getElementById('counter-pr-nama-edit').textContent = this.value.length + '/100';"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <div class="flex items-center justify-between mt-1 text-[10px]">
                        <span class="text-slate-500"><i class="fas fa-shield-halved mr-1 text-[#13527D]"></i>Hanya huruf, angka, spasi, dan tanda baca (- . , _ ()). Simbol dilarang.</span>
                        <span id="counter-pr-nama-edit" class="font-bold text-slate-600">{{ strlen(old('nama_pr', $pr->nama_pr)) }}/100</span>
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label for="id_mapel" class="block text-xs font-bold text-slate-700 mb-1">
                        Mata Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_mapel" id="id_mapel" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#13527D]">
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id_mapel }}" {{ old('id_mapel', $pr->id_mapel) == $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @php
                    $dt = \Carbon\Carbon::parse($pr->tgl_tenggat);
                    $currTanggal = old('tanggal_tenggat', $dt->format('Y-m-d'));
                    $currJam = $dt->format('H');
                    $currMenit = $dt->format('i');
                @endphp

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
                                   value="{{ $currTanggal }}" required
                                   onchange="syncEditDateTime()"
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
                                <select id="select_jam" onchange="syncEditDateTime()"
                                        class="w-1/2 px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#13527D]">
                                    @for($h = 7; $h <= 14; $h++)
                                        @php $hh = sprintf('%02d', $h); @endphp
                                        <option value="{{ $hh }}" {{ $hh == $currJam ? 'selected' : '' }}>
                                            {{ $hh }}
                                        </option>
                                    @endfor
                                </select>
                                <span class="font-extrabold text-slate-400">:</span>
                                <!-- Pilih Menit 00-59 -->
                                <select id="select_menit" onchange="syncEditDateTime()"
                                        class="w-1/2 px-2.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#13527D]">
                                    @foreach(['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'] as $mm)
                                        <option value="{{ $mm }}" {{ (int)$mm === (int)$currMenit || (abs((int)$mm - (int)$currMenit) < 3 && $currMenit != '55' && $currMenit != '00') ? 'selected' : '' }}>
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

                    <!-- Hidden input tgl_tenggat final -->
                    <input type="hidden" name="tgl_tenggat" id="tgl_tenggat" value="{{ old('tgl_tenggat', \Carbon\Carbon::parse($pr->tgl_tenggat)->format('Y-m-d H:i:s')) }}">


                    <!-- Alert Visual Human Error Warning -->
                    <div id="alert-edit-warning" class="hidden p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                        <i class="fas fa-triangle-exclamation text-rose-500 text-sm shrink-0"></i>
                        <span id="alert-edit-text" class="font-medium"></span>
                    </div>
                </div>

                <!-- Deskripsi / Petunjuk Pengerjaan -->
                <div class="md:col-span-2">
                    <label for="deskripsi" class="block text-xs font-bold text-slate-700 mb-1">
                        Deskripsi & Petunjuk Pengerjaan
                    </label>
                    <textarea name="deskripsi" id="deskripsi" rows="4" maxlength="1000"
                              placeholder="Tuliskan petunjuk pengerjaan tugas, nomor halaman buku paket, atau instruksi khusus untuk siswa (maksimal 1000 karakter)..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">{{ old('deskripsi', $pr->deskripsi) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Card 2: Kirim Notifikasi Pembaruan (Opsional) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Kirim Notifikasi Revisi / Pembaruan</h2>
                        <p class="text-[11px] text-slate-400">Beri tahu siswa bahwa ada perubahan batas tenggat atau instruksi tugas ini</p>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="kirim_notifikasi_update" value="1" onchange="document.getElementById('update-msg-wrap').classList.toggle('hidden', !this.checked)" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    <span class="ml-2.5 text-xs font-bold text-slate-700">Kirim Notifikasi Revisi</span>
                </label>
            </div>

            <div id="update-msg-wrap" class="hidden space-y-2">
                <label for="pesan_update" class="block text-xs font-bold text-slate-700">Pesan Pembaruan untuk Siswa</label>
                <textarea name="pesan_update" id="pesan_update" rows="3"
                          placeholder="Contoh: Perhatian: Tenggat pengumpulan tugas diperpanjang sampai... Silakan periksa kembali instruksi soal."
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]"></textarea>
                <p class="text-[10px] text-slate-400">Jika dikosongkan, format pesan otomatis akan dibuatkan sistem.</p>
            </div>
        </div>

        <!-- Tombol Aksi Simpan -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('guru.notifikasi_pr.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-extrabold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<script>
function showEditWarning(message) {
    const alertBox = document.getElementById('alert-edit-warning');
    const alertText = document.getElementById('alert-edit-text');
    if (message) {
        alertText.innerText = message;
        alertBox.classList.remove('hidden');
    } else {
        alertBox.classList.add('hidden');
        alertText.innerText = '';
    }
}

function validateEditDateTimeLive() {
    const tglVal = document.getElementById('input_tanggal').value;
    const jamVal = document.getElementById('select_jam').value || '07';
    let menitVal = document.getElementById('select_menit').value || '00';

    if (!tglVal) {
        showEditWarning('');
        return true;
    }

    // 1. Cek Hari Minggu
    const dParts = tglVal.split('-');
    const pickedDate = new Date(parseInt(dParts[0], 10), parseInt(dParts[1], 10) - 1, parseInt(dParts[2], 10));
    if (pickedDate.getDay() === 0) { // 0 = Minggu
        showEditWarning('Peringatan: Hari Minggu adalah hari libur sekolah. Silakan pilih hari operasional (Senin s/d Sabtu).');
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
        showEditWarning('Peringatan: Waktu tenggat pengumpulan yang dipilih sudah lewat dari waktu realtime saat ini. Silakan tentukan jam/tanggal di masa depan.');
        return false;
    }

    // 4. Cek Batas Jam Operasional 07:00 - 14:00
    const timeNum = parseInt(jamVal + menitVal, 10);
    if (timeNum < 700 || timeNum > 1400) {
        showEditWarning('Peringatan: Jam pengumpulan PR harus berada dalam jam operasional sekolah (07:00 - 14:00 WIB).');
        return false;
    }

    showEditWarning('');
    return true;
}

function syncEditDateTime() {
    const tgl = document.getElementById('input_tanggal').value;
    const jam = document.getElementById('select_jam').value || '07';
    const menit = document.getElementById('select_menit').value || '00';

    if (tgl) {
        document.getElementById('tgl_tenggat').value = `${tgl} ${jam}:${menit}:00`;
    }

    validateEditDateTimeLive();
}

function validateEditBeforeSubmit() {
    const isValid = validateEditDateTimeLive();
    if (!isValid) {
        const warningBox = document.getElementById('alert-edit-warning');
        warningBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    syncEditDateTime();
});
</script>
@endsection
