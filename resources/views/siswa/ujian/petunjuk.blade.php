@extends('layouts.siswa')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16">
    @php
        $mapelName = $quiz->mataPelajaran->nama_mapel ?? ($quiz->subBab->bab->mataPelajaran->nama_mapel ?? 'Mata Pelajaran');
        $guruName = $quiz->guru->nama_guru ?? ($quiz->mataPelajaran->guru->nama_guru ?? ($quiz->subBab->bab->mataPelajaran->guru->nama_guru ?? 'Guru Pengampu'));
        $durasi = $quiz->durasi_menit ?? 60;
        $level = ucfirst($quiz->tingkat_level ?? 'Sedang');
    @endphp

    <!-- Top Header Bar with Back Button -->
    <div class="bg-[#13527D] rounded-3xl px-6 py-5 text-white shadow-lg flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.ujian.index') }}" class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <span class="text-[10px] font-black uppercase text-amber-300 block tracking-wider">CBT Siswa</span>
                <h1 class="text-base sm:text-lg font-black tracking-tight leading-tight">Petunjuk & Konfirmasi Ujian</h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <div class="text-xs font-bold">{{ session('user_name', 'Siswa') }}</div>
                <div class="text-[10px] text-white/70">Peserta Ujian</div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-amber-300 font-bold">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>
    </div>

    <!-- Exam Header Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-[#13527D] text-white flex items-center justify-center text-2xl shrink-0 shadow-md">
            <i class="fas fa-file-signature"></i>
        </div>
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-sky-50 text-[#13527D] border border-sky-100 uppercase tracking-wide">
                {{ $mapelName }}
            </span>
            <h2 class="text-lg sm:text-xl font-black text-slate-900 mt-1 leading-snug">
                {{ $quiz->judul_quiz }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                <span>Guru: <strong>{{ $guruName }}</strong></span> &bull;
                <span>Tingkat Soal: <strong>{{ $level }}</strong></span>
            </p>
        </div>
    </div>

    <!-- Ringkasan Ujian (4 Grid Cards Desktop) -->
    <div class="space-y-3">
        <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">RINGKASAN PARAMETER UJIAN</span>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- 1. Waktu / Status -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Waktu</span>
                    <span class="text-sm font-black text-slate-900">Hari Ini</span>
                </div>
            </div>

            <!-- 2. Durasi -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-stopwatch"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Durasi</span>
                    <span class="text-sm font-black text-slate-900">{{ $durasi }} Menit</span>
                </div>
            </div>

            <!-- 3. Kesusahan Soal -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-gauge-high"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Kesusahan</span>
                    <span class="text-sm font-black text-slate-900">{{ $level }}</span>
                </div>
            </div>

            <!-- 4. Jumlah Soal -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-list-ol"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Jumlah Soal</span>
                    <span class="text-sm font-black text-slate-900">{{ $totalSoal }} Butir</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips Semangat Belajar Banner -->
    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-xs">
            <i class="fas fa-face-smile"></i>
        </div>
        <div>
            <h4 class="text-sm font-black text-amber-950">Tips Semangat Belajar</h4>
            <p class="text-xs text-amber-900 mt-0.5 font-medium leading-relaxed">
                Tenang, teliti, baca setiap butir pertanyaan dengan cermat, dan jangan lupa berdoa sebelum memulai ya!
            </p>
        </div>
    </div>

    <!-- Pilihan Kesusahan Soal Ujian -->
    <div class="bg-gradient-to-r from-sky-50 to-indigo-50/70 border border-sky-200/80 rounded-3xl p-6 space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-black text-[#13527D] flex items-center gap-2">
                <i class="fas fa-sliders"></i>
                <span>Pilih Tingkat Kesusahan Soal yang Ingin Dikerjakan</span>
            </h4>
            <span class="text-[10px] font-extrabold text-sky-800 bg-sky-100 px-3 py-1 rounded-full border border-sky-200">
                Pilihan Fleksibel
            </span>
        </div>
        @php
            $defKes = ($countSedang > 0) ? 'sedang' : (($countMudah > 0) ? 'mudah' : 'sulit');
        @endphp
        <p class="text-xs text-slate-600 leading-relaxed">
            Guru telah menyiapkan butir soal dengan berbagai tingkat kesulitan. Pilih tingkat kesulitan yang ingin Anda kerjakan:
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
            <label class="p-4 rounded-2xl border {{ $defKes === 'mudah' ? 'border-2 border-[#13527D] bg-sky-50/50' : 'border-slate-200 bg-white' }} cursor-pointer flex flex-col justify-between transition hover:border-emerald-400 shadow-xs select-kes-card" id="petunjuk-card-mudah">
                <div class="flex items-center justify-between">
                    <input type="radio" name="pilihan_kesulitan" value="mudah" {{ $defKes === 'mudah' ? 'checked' : '' }} onchange="changePetunjukKesulitan(this.value)" class="text-emerald-600 focus:ring-0">
                    <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">Mudah</span>
                </div>
                <div class="mt-2">
                    <span class="text-xs font-black text-slate-900 block">🟢 Level Mudah</span>
                    <span class="text-[11px] text-slate-500 font-semibold mt-0.5 block">{{ $countMudah ?? 0 }} Butir Soal</span>
                </div>
            </label>

            <label class="p-4 rounded-2xl border {{ $defKes === 'sedang' ? 'border-2 border-[#13527D] bg-sky-50/50' : 'border-slate-200 bg-white' }} cursor-pointer flex flex-col justify-between transition hover:border-amber-400 shadow-xs select-kes-card" id="petunjuk-card-sedang">
                <div class="flex items-center justify-between">
                    <input type="radio" name="pilihan_kesulitan" value="sedang" {{ $defKes === 'sedang' ? 'checked' : '' }} onchange="changePetunjukKesulitan(this.value)" class="text-amber-600 focus:ring-0">
                    <span class="text-[10px] font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">Sedang</span>
                </div>
                <div class="mt-2">
                    <span class="text-xs font-black text-slate-900 block">🟡 Level Sedang</span>
                    <span class="text-[11px] text-slate-500 font-semibold mt-0.5 block">{{ $countSedang ?? 0 }} Butir Soal</span>
                </div>
            </label>

            <label class="p-4 rounded-2xl border {{ $defKes === 'sulit' ? 'border-2 border-[#13527D] bg-sky-50/50' : 'border-slate-200 bg-white' }} cursor-pointer flex flex-col justify-between transition hover:border-rose-400 shadow-xs select-kes-card" id="petunjuk-card-sulit">
                <div class="flex items-center justify-between">
                    <input type="radio" name="pilihan_kesulitan" value="sulit" {{ $defKes === 'sulit' ? 'checked' : '' }} onchange="changePetunjukKesulitan(this.value)" class="text-rose-600 focus:ring-0">
                    <span class="text-[10px] font-extrabold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md">Sulit</span>
                </div>
                <div class="mt-2">
                    <span class="text-xs font-black text-slate-900 block">🔴 Level Sulit</span>
                    <span class="text-[11px] text-slate-500 font-semibold mt-0.5 block">{{ $countSulit ?? 0 }} Butir Soal</span>
                </div>
            </label>
        </div>
    </div>

    <!-- Petunjuk Pengerjaan Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <div class="flex items-center gap-2 text-slate-900 font-extrabold text-sm pb-2 border-b border-slate-100">
            <i class="fas fa-book-open-reader text-[#13527D]"></i>
            <span>Petunjuk Pengerjaan & Ketentuan Ujian</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-sky-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                    1
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Berdoa & Siapkan Perangkat</h5>
                    <p class="text-[11px] text-slate-500 mt-1">Pastikan koneksi internet stabil sebelum menekan tombol mulai.</p>
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white font-black text-xs flex items-center justify-center shrink-0">
                    2
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Pilih Opsi & Simpan Otomatis</h5>
                    <p class="text-[11px] text-slate-500 mt-1">Jawaban Anda akan langsung tersimpan secara aman setiap kali diklik.</p>
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                    3
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Periksa & Kumpulkan</h5>
                    <p class="text-[11px] text-slate-500 mt-1">Gunakan panel nomor soal di sebelah kanan untuk mengecek kelengkapan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ready Checkbox & Action Button -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-4">
        <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" id="check-ready" onchange="toggleMulaiButton(this.checked)"
                   class="w-5 h-5 rounded-lg text-[#13527D] focus:ring-[#13527D] border-slate-300">
            <span class="text-sm font-black text-slate-800">Saya memahami peraturan ujian dan siap mengerjakannya sekarang.</span>
        </label>

        <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
            <a href="{{ route('siswa.ujian.index') }}"
               class="w-full sm:w-auto px-6 py-3.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition text-center">
                Batal / Kembali
            </a>
            <a id="btn-mulai-ujian" href="{{ route('siswa.ujian.play', ['idQuiz' => $quiz->id_quiz, 'kesulitan' => $defKes]) }}"
               class="w-full sm:flex-1 py-3.5 rounded-xl text-xs font-black transition flex items-center justify-center gap-2 shadow-sm pointer-events-none opacity-50 bg-slate-200 text-slate-400">
                <span>Mulai Kerjakan Ujian Sekarang</span>
                <i class="fas fa-arrow-right text-[11px]"></i>
            </a>
        </div>
    </div>
</div>

<script>
    let basePlayUrl = '{{ route('siswa.ujian.play', $quiz->id_quiz) }}';
    let currentSelectedKesulitan = '{{ $defKes }}';

    function changePetunjukKesulitan(val) {
        currentSelectedKesulitan = val;
        document.querySelectorAll('.select-kes-card').forEach(el => {
            el.classList.remove('border-2', 'border-[#13527D]', 'bg-sky-50/50');
            el.classList.add('border-slate-200', 'bg-white');
        });

        const activeCard = document.getElementById('petunjuk-card-' + val);
        if (activeCard) {
            activeCard.classList.remove('border-slate-200', 'bg-white');
            activeCard.classList.add('border-2', 'border-[#13527D]', 'bg-sky-50/50');
        }

        const btn = document.getElementById('btn-mulai-ujian');
        if (btn) {
            btn.href = basePlayUrl + '?kesulitan=' + val;
        }
    }

    function toggleMulaiButton(isChecked) {
        const btn = document.getElementById('btn-mulai-ujian');
        if (isChecked) {
            btn.classList.remove('pointer-events-none', 'opacity-50', 'bg-slate-200', 'text-slate-400');
            btn.classList.add('bg-[#13527D]', 'hover:bg-[#0E3D5D]', 'text-white', 'shadow-md');
        } else {
            btn.classList.add('pointer-events-none', 'opacity-50', 'bg-slate-200', 'text-slate-400');
            btn.classList.remove('bg-[#13527D]', 'hover:bg-[#0E3D5D]', 'text-white', 'shadow-md');
        }
    }
</script>
@endsection
