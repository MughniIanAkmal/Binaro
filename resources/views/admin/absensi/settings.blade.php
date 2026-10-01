@extends('layouts.app')

@section('content')
<div class="p-8 space-y-6">
    <!-- Header Halaman & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold mb-1 text-slate-400">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <a href="{{ route('absensi.index') }}" class="hover:text-slate-600 transition">Kelola Absensi</a>
                <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                <span class="text-[#13527D] font-bold">Pengaturan Jam Absen</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Jam Absensi & Operasional</h1>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi ambang batas waktu presensi kehadiran siswa serta jam tutup operasional sekolah.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('absensi.index') }}" class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition flex items-center gap-1.5 shadow-xs">
                <i class="fas fa-clipboard-user text-slate-500"></i>
                <span>Lihat Rekap Absensi</span>
            </a>
            <a href="{{ route('jadwal.index') }}" class="px-4 py-2 text-xs font-bold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-lg transition flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-calendar-alt text-sky-300"></i>
                <span>Kelola Jadwal</span>
            </a>
        </div>
    </div>

    <!-- Visual 4-Zone Timeline Diagram -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#13527D] flex items-center justify-center text-sm font-bold shadow-xs">
                    <i class="fas fa-timeline"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Alur Logika Ambang Batas Kehadiran Siswa</h3>
                    <p class="text-xs text-slate-500">Aturan pengelompokan status saat siswa melakukan scan barcode QR presensi</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold self-start sm:self-center flex items-center gap-1.5">
                <i class="fas fa-bolt text-[10px]"></i> Tersinkronisasi Otomatis
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
            <!-- Zona 1: Lebih Awal -->
            <div class="p-4 rounded-xl bg-sky-50/70 border border-sky-200/80 space-y-2 hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-sky-800 tracking-wider">Zona 1: Lebih Awal</span>
                    <i class="fas fa-sun text-sky-500 text-sm"></i>
                </div>
                <div class="text-xl font-black text-sky-900 tracking-tight">
                    &lt; <span id="display-awal">{{ $batasAwal }}</span> WIB
                </div>
                <p class="text-xs text-sky-800 leading-relaxed font-medium">
                    Siswa scan sebelum jam ini dikategorikan status <strong class="text-sky-950 font-bold">"Datang Lebih Awal"</strong>.
                </p>
            </div>

            <!-- Zona 2: Tepat Waktu -->
            <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 space-y-2 hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-emerald-800 tracking-wider">Zona 2: Tepat Waktu</span>
                    <i class="fas fa-circle-check text-emerald-500 text-sm"></i>
                </div>
                <div class="text-xl font-black text-emerald-900 tracking-tight">
                    <span id="display-awal-range">{{ $batasAwal }}</span> - <span id="display-tepat">{{ $batasTepat }}</span>
                </div>
                <p class="text-xs text-emerald-800 leading-relaxed font-medium">
                    Siswa scan pada rentang jam ini tercatat hadir dengan status <strong class="text-emerald-950 font-bold">"Tepat Waktu"</strong>.
                </p>
            </div>

            <!-- Zona 3: Terlambat -->
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 space-y-2 hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-amber-800 tracking-wider">Zona 3: Terlambat</span>
                    <i class="fas fa-clock text-amber-500 text-sm"></i>
                </div>
                <div class="text-xl font-black text-amber-900 tracking-tight">
                    <span id="display-tepat-range">{{ $batasTepat }}</span> - <span id="display-tutup">{{ $batasTutup }}</span>
                </div>
                <p class="text-xs text-amber-800 leading-relaxed font-medium">
                    Siswa scan setelah batas tepat waktu hingga jam tutup sekolah ditandai <strong class="text-amber-950 font-bold">"Terlambat"</strong>.
                </p>
            </div>

            <!-- Zona 4: Tutup Sekolah -->
            <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200/80 space-y-2 hover:shadow-xs transition">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-rose-800 tracking-wider">Zona 4: Sekolah Tutup</span>
                    <i class="fas fa-door-closed text-rose-500 text-sm"></i>
                </div>
                <div class="text-xl font-black text-rose-900 tracking-tight">
                    &gt; <span id="display-tutup-limit">{{ $batasTutup }}</span> WIB
                </div>
                <p class="text-xs text-rose-800 leading-relaxed font-medium">
                    Sekolah tutup. Siswa tanpa presensi otomatis tercatat <strong class="text-rose-950 font-bold">"Alpa"</strong> & jam selesai jadwal tidak boleh melebihi ini.
                </p>
            </div>
        </div>
    </div>

    <!-- Setting Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-sliders text-[#13527D]"></i> Formulir Konfigurasi Waktu Presensi
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Ubah parameter jam sesuai tata tertib operasional SDN Kalitapen 01.</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-white px-3 py-1.5 rounded-lg border border-slate-200 flex items-center gap-1.5 self-start sm:self-center">
                <i class="fas fa-server text-slate-400"></i>
                <span>Waktu Server:</span>
                <strong class="text-slate-800 font-mono">{{ now()->format('H:i') }} WIB</strong>
            </span>
        </div>

        <form action="{{ route('admin.absensi.settings.update') }}" method="POST" id="form-settings" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. Jam Batas Awal -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">
                        1. Jam Datang Lebih Awal <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="time" name="batas_awal" id="input_batas_awal" required value="{{ old('batas_awal', $batasAwal) }}"
                               oninput="syncPreview()"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-bold text-slate-800 focus:outline-none focus:border-[#13527D] focus:bg-white focus:ring-1 focus:ring-[#13527D] transition">
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Default: <strong>07:00</strong>. Kedatangan sebelum jam ini dikategorikan datang lebih awal.
                    </p>
                </div>

                <!-- 2. Jam Batas Tepat Waktu -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">
                        2. Batas Hadir Tepat Waktu <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="time" name="batas_tepat" id="input_batas_tepat" required value="{{ old('batas_tepat', $batasTepat) }}"
                               oninput="syncPreview()"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-bold text-slate-800 focus:outline-none focus:border-[#13527D] focus:bg-white focus:ring-1 focus:ring-[#13527D] transition">
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Default: <strong>08:00</strong>. Antara Jam Awal hingga jam ini dihitung hadir tepat waktu.
                    </p>
                </div>

                <!-- 3. Jam Tutup Sekolah -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">
                        3. Jam Tutup Sekolah <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="time" name="batas_tutup" id="input_batas_tutup" required value="{{ old('batas_tutup', $batasTutup) }}"
                               oninput="syncPreview()"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-bold text-slate-800 focus:outline-none focus:border-[#13527D] focus:bg-white focus:ring-1 focus:ring-[#13527D] transition">
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Presensi setelah Jam Tepat s/d jam ini adalah <strong>Terlambat</strong>. Menjadi <strong>batas maksimal jam selesai di Jadwal Pelajaran</strong>.
                    </p>
                </div>
            </div>

            <!-- Live Error Warning -->
            <div id="live-error-box" class="hidden p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2.5">
                <i class="fas fa-triangle-exclamation text-rose-600 text-base shrink-0"></i>
                <span id="live-error-text">Jam tidak valid</span>
            </div>

            <!-- Info Relasi Jadwal Pelajaran -->
            <div class="p-4 rounded-lg bg-sky-50 border border-sky-200 text-sky-900 text-xs flex items-start gap-3">
                <i class="fas fa-circle-info text-sky-600 mt-0.5 text-base shrink-0"></i>
                <div class="space-y-1 leading-relaxed">
                    <p class="font-bold">Keterkaitan dengan Modul Jadwal Pelajaran:</p>
                    <p class="text-xs text-sky-800">
                        Jam tutup sekolah di atas secara otomatis menjadi batas maksimal waktu selesai saat menambahkan atau menyunting Jadwal Pelajaran di admin maupun guru. Jika jadwal berakhir melebihi jam tutup sekolah, sistem otomatis menolak input untuk menjamin kepatuhan jam operasional sekolah.
                    </p>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    Kembali ke Dashboard
                </a>
                <button type="submit" id="btn-save-settings" class="px-5 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-floppy-disk"></i>
                    <span>Simpan Pengaturan Jam</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function syncPreview() {
    const awal = document.getElementById('input_batas_awal').value;
    const tepat = document.getElementById('input_batas_tepat').value;
    const tutup = document.getElementById('input_batas_tutup').value;

    const errBox = document.getElementById('live-error-box');
    const errText = document.getElementById('live-error-text');
    const btnSave = document.getElementById('btn-save-settings');

    if (awal) {
        document.getElementById('display-awal').textContent = awal;
        document.getElementById('display-awal-range').textContent = awal;
    }
    if (tepat) {
        document.getElementById('display-tepat').textContent = tepat;
        document.getElementById('display-tepat-range').textContent = tepat;
    }
    if (tutup) {
        document.getElementById('display-tutup').textContent = tutup;
        document.getElementById('display-tutup-limit').textContent = tutup;
    }

    if (awal && tepat && awal >= tepat) {
        errText.textContent = 'Jam tepat waktu (' + tepat + ') harus lebih lambat dari jam awal (' + awal + ').';
        errBox.classList.remove('hidden');
        btnSave.disabled = true;
        btnSave.classList.add('opacity-50', 'cursor-not-allowed');
        return false;
    }

    if (tepat && tutup && tepat >= tutup) {
        errText.textContent = 'Jam tutup sekolah (' + tutup + ') harus lebih lambat dari jam tepat waktu (' + tepat + ').';
        errBox.classList.remove('hidden');
        btnSave.disabled = true;
        btnSave.classList.add('opacity-50', 'cursor-not-allowed');
        return false;
    }

    errBox.classList.add('hidden');
    btnSave.disabled = false;
    btnSave.classList.remove('opacity-50', 'cursor-not-allowed');
    return true;
}

document.addEventListener('DOMContentLoaded', syncPreview);
</script>
@endsection
