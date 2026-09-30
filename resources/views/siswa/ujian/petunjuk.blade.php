@extends('layouts.siswa')

@section('content')
<div class="max-w-xl mx-auto space-y-5 pb-16">
    <!-- Top Header Bar with Back Button (Sesuai Gambar 2) -->
    <div class="bg-[#13527D] -mx-4 -mt-4 sm:mx-0 sm:mt-0 sm:rounded-2xl px-4 py-3.5 text-white shadow-md flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.ujian.index') }}" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black tracking-tight">Petunjuk Ujian</h1>
        </div>
        <div class="w-9 h-9 rounded-full border-2 border-white/40 overflow-hidden bg-white/20 flex items-center justify-center">
            <img src="https://api.dicebear.com/7.x/bottts/svg?seed={{ urlencode(session('user_name', 'Siswa')) }}" alt="Avatar" class="w-full h-full object-cover">
        </div>
    </div>

    <!-- Exam Header Card (Sesuai Gambar 2) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-[#13527D] text-white flex items-center justify-center text-2xl shrink-0 shadow-sm">
            <i class="fas fa-calculator"></i>
        </div>
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-[#13527D] border border-sky-100 uppercase tracking-wide">
                UJIAN TENGAH SEMESTER
            </span>
            <h2 class="text-base font-black text-slate-900 mt-1 leading-snug">
                {{ $quiz->judul_quiz }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Guru: {{ $quiz->subBab->bab->mataPelajaran->guru->nama_guru ?? 'Ibu Sarah Wijaya, S.Pd.' }}
            </p>
        </div>
    </div>

    <!-- Ringkasan Ujian (4 Grid Cards, Sesuai Gambar 2) -->
    <div class="space-y-2">
        <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">RINGKASAN UJIAN</span>
        <div class="grid grid-cols-2 gap-3">
            <!-- 1. Waktu -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Waktu</span>
                    <span class="text-sm font-black text-slate-900">60 Menit</span>
                </div>
            </div>

            <!-- 2. Soal -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-list-check"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Soal</span>
                    <span class="text-sm font-black text-slate-900">{{ $totalSoal }} Butir</span>
                </div>
            </div>

            <!-- 3. Target KKM -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Target KKM</span>
                    <span class="text-sm font-black text-slate-900">75</span>
                </div>
            </div>

            <!-- 4. Koreksi -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Koreksi</span>
                    <span class="text-sm font-black text-slate-900">Otomatis</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips Semangat Belajar Banner (Sesuai Gambar 2) -->
    <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
            <i class="fas fa-face-smile"></i>
        </div>
        <div>
            <h4 class="text-xs font-black text-amber-900">Tips Semangat Belajar</h4>
            <p class="text-xs text-amber-800/90 mt-0.5 font-medium">Tenang, teliti, dan jangan lupa berdoa ya!</p>
        </div>
    </div>

    <!-- Petunjuk Pengerjaan Card (Sesuai Gambar 2) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 text-slate-900 font-extrabold text-xs">
            <i class="fas fa-book-open-reader text-[#13527D]"></i>
            <span>Petunjuk Pengerjaan</span>
        </div>

        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-sky-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                    1
                </div>
                <p class="text-xs font-semibold text-slate-700">Berdoa sebelum mulai.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-amber-500 text-white font-black text-xs flex items-center justify-center shrink-0">
                    2
                </div>
                <p class="text-xs font-semibold text-slate-700">Pilih salah satu jawaban A, B, C, atau D.</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                    3
                </div>
                <p class="text-xs font-semibold text-slate-700">Periksa kembali lalu kumpulkan.</p>
            </div>
        </div>
    </div>

    <!-- Ready Checkbox & Action Button (Sesuai Gambar 2) -->
    <div class="space-y-3">
        <label class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3 cursor-pointer hover:border-[#13527D]/40 transition select-none">
            <input type="checkbox" id="check-ready" onchange="toggleMulaiButton(this.checked)"
                   class="w-5 h-5 rounded-lg text-[#13527D] focus:ring-[#13527D] border-slate-300">
            <span class="text-xs font-extrabold text-slate-800">Saya sudah siap ujian!</span>
        </label>

        <a id="btn-mulai-ujian" href="{{ route('siswa.quiz.play', $quiz->id_quiz) }}"
           class="w-full py-3.5 rounded-2xl text-xs font-black transition flex items-center justify-center gap-2 shadow-sm pointer-events-none opacity-50 bg-slate-300 text-slate-500">
            <span>Mulai Ujian Sekarang</span>
            <i class="fas fa-arrow-right text-[11px]"></i>
        </a>

        <p class="text-[11px] text-center text-slate-400">
            Ada kendala saat membuka soal? Beritahu guru pengawasmu.
        </p>
    </div>
</div>

<script>
    function toggleMulaiButton(isChecked) {
        const btn = document.getElementById('btn-mulai-ujian');
        if (isChecked) {
            btn.classList.remove('pointer-events-none', 'opacity-50', 'bg-slate-300', 'text-slate-500');
            btn.classList.add('bg-[#13527D]', 'hover:bg-[#0E3D5D]', 'text-white', 'shadow-md');
        } else {
            btn.classList.add('pointer-events-none', 'opacity-50', 'bg-slate-300', 'text-slate-500');
            btn.classList.remove('bg-[#13527D]', 'hover:bg-[#0E3D5D]', 'text-white', 'shadow-md');
        }
    }
</script>
@endsection
