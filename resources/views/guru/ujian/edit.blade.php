@extends('layouts.guru')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.ujian.index') }}" class="hover:text-[#13527D] transition">Ujian Online</a>
                <span>/</span>
                <a href="{{ route('guru.ujian.show', $quiz->id_quiz) }}" class="hover:text-[#13527D] transition">{{ $quiz->judul_quiz }}</a>
                <span>/</span>
                <span class="text-[#13527D] font-semibold">Edit Ujian</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Sunting Pengaturan Ujian</h1>
            <p class="text-xs text-slate-500">Ubah konfigurasi ujian, tingkatan level, durasi, dan target peserta ujian.</p>
        </div>
        <a href="{{ route('guru.ujian.show', $quiz->id_quiz) }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Edit Soal
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

    <form action="{{ route('guru.ujian.update', $quiz->id_quiz) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: Informasi Dasar Ujian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center font-bold text-xs"><i class="fas fa-sliders"></i></span>
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
                    <input type="text" name="judul_quiz" value="{{ old('judul_quiz', $quiz->judul_quiz) }}" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition font-medium">
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Mata Pelajaran <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_mapel" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition font-medium">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id_mapel }}" {{ old('id_mapel', $quiz->id_mapel) == $mapel->id_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Durasi Ujian -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Durasi Ujian (Menit) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="durasi_menit" value="{{ old('durasi_menit', $quiz->durasi_menit ?? 60) }}" min="5" max="300" required class="w-full pl-3.5 pr-14 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition font-medium">
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-400 font-semibold pointer-events-none">Menit</span>
                    </div>
                </div>

                <!-- Tingkatan Level Ujian (Mudah, Sedang, Susah) -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Tingkatan Level Ujian <span class="text-rose-500">*</span>
                    </label>
                    @php
                        $curLevel = old('tingkat_level', $quiz->tingkat_level ?? 'sedang');
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-400 cursor-pointer transition">
                            <input type="radio" name="tingkat_level" value="mudah" class="w-4 h-4 text-emerald-600 focus:ring-0 mr-3 accent-emerald-600" {{ $curLevel === 'mudah' ? 'checked' : '' }} required>
                            <div>
                                <span class="block text-xs font-bold text-emerald-800">🟢 Level Mudah</span>
                                <span class="block text-[10px] text-slate-500">Pemahaman konsep dasar</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:border-amber-400 cursor-pointer transition">
                            <input type="radio" name="tingkat_level" value="sedang" class="w-4 h-4 text-amber-600 focus:ring-0 mr-3 accent-amber-600" {{ $curLevel === 'sedang' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-xs font-bold text-amber-800">🟡 Level Sedang</span>
                                <span class="block text-[10px] text-slate-500">Aplikasi & pemahaman standar</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:border-rose-400 cursor-pointer transition">
                            <input type="radio" name="tingkat_level" value="susah" class="w-4 h-4 text-rose-600 focus:ring-0 mr-3 accent-rose-600" {{ $curLevel === 'susah' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-xs font-bold text-rose-800">🔴 Level Susah (HOTS)</span>
                                <span class="block text-[10px] text-slate-500">Analisis tingkat tinggi</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Deskripsi / Petunjuk -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Deskripsi / Petunjuk Pengerjaan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="deskripsi" rows="2" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition">{{ old('deskripsi', $quiz->deskripsi) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Target Peserta Ujian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center font-bold text-xs"><i class="fas fa-users"></i></span>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Target Peserta Ujian</h2>
                    <p class="text-[11px] text-slate-400">Pilih apakah ujian ditujukan untuk seluruh siswa atau hanya siswa-siswa tertentu.</p>
                </div>
            </div>

            @php
                $curTargetTipe = old('target_tipe', $quiz->target_tipe ?? 'semua');
                $curSelectedSiswa = old('target_siswa', $selectedSiswaIds);
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-center p-3.5 rounded-xl border border-slate-200 bg-white hover:border-[#13527D] cursor-pointer transition">
                    <input type="radio" name="target_tipe" value="semua" class="w-4 h-4 text-[#13527D] focus:ring-0 mr-3 accent-[#13527D] target-radio" {{ $curTargetTipe === 'semua' ? 'checked' : '' }} onchange="toggleTargetSiswa(false)">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Semua Siswa</span>
                        <span class="block text-[10px] text-slate-500">Seluruh siswa aktif dapat mengerjakan ujian ini</span>
                    </div>
                </label>

                <label class="flex items-center p-3.5 rounded-xl border border-slate-200 bg-white hover:border-[#13527D] cursor-pointer transition">
                    <input type="radio" name="target_tipe" value="pilihan" class="w-4 h-4 text-[#13527D] focus:ring-0 mr-3 accent-[#13527D] target-radio" {{ $curTargetTipe === 'pilihan' ? 'checked' : '' }} onchange="toggleTargetSiswa(true)">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Pilih Siswa Tertentu</span>
                        <span class="block text-[10px] text-slate-500">Hanya siswa-siswa terpilih yang dapat mengakses</span>
                    </div>
                </label>
            </div>

            <!-- Panel Pemilihan Siswa Tertentu -->
            <div id="panel-pilih-siswa" class="{{ $curTargetTipe === 'pilihan' ? '' : 'hidden' }} space-y-3 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Daftar Pilihan Siswa:</span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="checkAllSiswa(true)" class="text-[11px] text-[#13527D] hover:underline font-semibold">Pilih Semua</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="checkAllSiswa(false)" class="text-[11px] text-rose-600 hover:underline font-semibold">Batal Pilih</button>
                    </div>
                </div>

                <!-- Per Kelas -->
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
                                <input type="checkbox" name="target_siswa[]" value="{{ $siswa->id_siswa }}" class="rounded text-[#13527D] focus:ring-0 siswa-cb kelas-{{ $kelas->id_kelas }}" {{ in_array($siswa->id_siswa, $curSelectedSiswa) ? 'checked' : '' }}>
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

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('guru.ujian.show', $quiz->id_quiz) }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-xl transition shadow-md hover:shadow-lg flex items-center gap-2">
                <i class="fas fa-check"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<script>
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
</script>
@endsection
