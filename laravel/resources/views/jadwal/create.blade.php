@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1 text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <a href="{{ route('jadwal.index') }}" class="hover:text-slate-600 transition">Jadwal Pelajaran</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-[#13527D] font-bold">Tambah Jadwal</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Jadwal Pelajaran</h1>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi formulir di bawah untuk menambahkan jadwal mata pelajaran baru.</p>
        </div>
        <a href="{{ route('jadwal.index') }}" class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5 shadow-xs">
            <i class="fas fa-arrow-left text-slate-400"></i>
            <span>Kembali ke Jadwal</span>
        </a>
    </div>

    <!-- Alert Error Validasi -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card Utama (Satu Kotak Utuh) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Judul Sub-Header -->
        <div class="bg-gray-50/50 px-6 py-2.5 border-b border-gray-100">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">FORMULIR INPUT JADWAL</h2>
        </div>

        <!-- Form Tambah -->
        <form action="{{ route('jadwal.store') }}" method="POST" class="px-6 pt-2 pb-6 space-y-4">
            @csrf

            <!-- Input Hari -->
            <div>
                <label for="hari" class="block text-sm font-semibold text-gray-700 mb-1.5">Hari</label>
                <select name="hari" id="hari" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Hari --</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                        <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>
                            {{ $hari }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Input Jam Pelajaran -->
            <div class="space-y-2 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-800">
                        Jam Pelajaran (Waktu Mulai s/d Selesai) <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] font-semibold text-slate-500 bg-white px-2.5 py-0.5 rounded-full border border-slate-200">
                        <i class="fas fa-school mr-1 text-[#13527D]"></i> Tutup Sekolah: <strong class="text-slate-800" id="badge-jam-tutup">{{ $jamTutup ?? '12:00' }}</strong> WIB
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="picker_jam_mulai" class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Mulai</label>
                        <input
                            type="time"
                            id="picker_jam_mulai"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-[#13527D] text-xs font-bold text-slate-800"
                            required
                        >
                    </div>
                    <div>
                        <label for="picker_jam_selesai" class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Selesai</label>
                        <input
                            type="time"
                            id="picker_jam_selesai"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-[#13527D] text-xs font-bold text-slate-800"
                            required
                        >
                    </div>
                </div>

                <!-- Input Terformat untuk Controller -->
                <div class="pt-1">
                    <input
                        type="text"
                        name="jam"
                        id="jam"
                        value="{{ old('jam') }}"
                        placeholder="Contoh: 07.00-08.30"
                        pattern="[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}"
                        title="Format jam: HH.MM-HH.MM"
                        class="w-full px-3.5 py-2 bg-white rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-700 focus:outline-none focus:border-[#13527D]"
                        required
                    >
                    <p class="text-[10px] text-slate-400 mt-1 flex items-center justify-between">
                        <span>*Otomatis terisi saat memilih Jam Mulai & Selesai di atas (format HH.MM-HH.MM).</span>
                        <a href="{{ route('admin.absensi.settings') }}" class="text-[#13527D] hover:underline font-semibold">Ubah Jam Tutup Sekolah</a>
                    </p>
                </div>

                <div id="jam-error-warning" class="hidden mt-2 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation text-rose-500 text-base shrink-0"></i>
                    <span id="jam-error-text">Jam pelajaran tidak boleh terbalik atau mundur!</span>
                </div>
            </div>

            <!-- Select Mata Pelajaran -->
            <div>
                <label for="id_mapel" class="block text-sm font-semibold text-gray-700 mb-1.5">Mata Pelajaran</label>
                <select name="id_mapel" id="id_mapel" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach ($mapel as $m)
                        <option value="{{ $m->id_mapel }}" {{ old('id_mapel') == $m->id_mapel ? 'selected' : '' }}>
                            {{ $m->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Guru -->
            <div>
                <label for="id_guru" class="block text-sm font-semibold text-gray-700 mb-1.5">Guru Pengampu</label>
                <select name="id_guru" id="id_guru" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Guru --</option>
                    @foreach ($guru as $g)
                        <option value="{{ $g->id_guru }}" {{ old('id_guru') == $g->id_guru ? 'selected' : '' }}>
                            {{ $g->nama_guru }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Kelas -->
            <div>
                <label for="id_kelas" class="block text-sm font-semibold text-gray-700 mb-1.5">Kelas</label>
                <select name="id_kelas" id="id_kelas" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id_rooms }}" {{ old('id_kelas') == $k->id_rooms ? 'selected' : '' }}>
                            {{ $k->pararel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                <a href="{{ route('jadwal.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" id="btn-submit-jadwal" class="bg-[#13527D] hover:bg-[#0E3D5D] text-white px-5 py-2.5 rounded-xl text-sm font-bold transition flex items-center gap-2 shadow-sm">
                    💾 Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const jamInput = document.getElementById('jam');
    const pickerMulai = document.getElementById('picker_jam_mulai');
    const pickerSelesai = document.getElementById('picker_jam_selesai');
    const warningBox = document.getElementById('jam-error-warning');
    const warningText = document.getElementById('jam-error-text');
    const btnSubmit = document.getElementById('btn-submit-jadwal');
    const form = document.querySelector('form');

    const jamTutupStr = '{{ $jamTutup ?? "12:00" }}';
    const [hTutupMax, mTutupMax] = jamTutupStr.split(':').map(n => parseInt(n, 10));
    const totalTutupMax = (hTutupMax * 60) + (mTutupMax || 0);

    function syncFromPickers() {
        if (pickerMulai.value && pickerSelesai.value) {
            const m = pickerMulai.value.replace(':', '.');
            const s = pickerSelesai.value.replace(':', '.');
            jamInput.value = `${m}-${s}`;
        }
        checkJamValidity();
    }

    function syncToPickers() {
        const val = jamInput.value.trim().replace(/:/g, '.');
        const match = val.match(/^(\d{2})\.(\d{2})-(\d{2})\.(\d{2})$/);
        if (match) {
            pickerMulai.value = `${match[1]}:${match[2]}`;
            pickerSelesai.value = `${match[3]}:${match[4]}`;
        }
        checkJamValidity();
    }

    function checkJamValidity() {
        const val = jamInput.value.trim().replace(/:/g, '.');
        const pattern = /^(\d{2})\.(\d{2})-(\d{2})\.(\d{2})$/;
        const match = val.match(pattern);

        if (!match) {
            if (val.length > 0) {
                warningText.textContent = 'Format jam tidak sesuai! Gunakan format HH.MM-HH.MM (contoh: 07.00-09.00).';
                warningBox.classList.remove('hidden');
                if (btnSubmit) btnSubmit.disabled = true;
                return false;
            }
            warningBox.classList.add('hidden');
            if (btnSubmit) btnSubmit.disabled = false;
            return true;
        }

        const hMulai = parseInt(match[1], 10);
        const mMulai = parseInt(match[2], 10);
        const hSelesai = parseInt(match[3], 10);
        const mSelesai = parseInt(match[4], 10);

        if (hMulai > 23 || mMulai > 59 || hSelesai > 23 || mSelesai > 59) {
            warningText.textContent = 'Nilai jam (00-23) atau menit (00-59) tidak valid.';
            warningBox.classList.remove('hidden');
            if (btnSubmit) btnSubmit.disabled = true;
            return false;
        }

        const totalMulai = (hMulai * 60) + mMulai;
        const totalSelesai = (hSelesai * 60) + mSelesai;
        const strMulai = `${match[1]}.${match[2]}`;
        const strSelesai = `${match[3]}.${match[4]}`;

        // 1. Cek jam terbalik
        if (totalSelesai <= totalMulai) {
            warningText.textContent = `Jam pelajaran terbalik! Jam mulai (${strMulai}) tidak boleh lebih lambat atau sama dengan jam selesai (${strSelesai}).`;
            warningBox.classList.remove('hidden');
            if (btnSubmit) btnSubmit.disabled = true;
            return false;
        }

        // 2. Cek jam tutup sekolah
        if (totalSelesai > totalTutupMax) {
            warningText.textContent = `Jam selesai (${strSelesai}) melebihi jam tutup sekolah (${jamTutupStr.replace(':', '.')})! Maksimal jadwal hingga jam tutup sekolah.`;
            warningBox.classList.remove('hidden');
            if (btnSubmit) btnSubmit.disabled = true;
            return false;
        }

        warningBox.classList.add('hidden');
        if (btnSubmit) btnSubmit.disabled = false;
        return true;
    }

    pickerMulai.addEventListener('input', syncFromPickers);
    pickerSelesai.addEventListener('input', syncFromPickers);
    jamInput.addEventListener('input', syncToPickers);
    jamInput.addEventListener('blur', checkJamValidity);

    if (jamInput.value) {
        syncToPickers();
    }

    form.addEventListener('submit', function (e) {
        if (!checkJamValidity()) {
            e.preventDefault();
            jamInput.focus();
        }
    });
});
</script>
@endsection
