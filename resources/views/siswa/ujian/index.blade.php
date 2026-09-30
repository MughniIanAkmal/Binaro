@extends('layouts.siswa')

@section('content')
<div class="max-w-xl mx-auto space-y-5 pb-16">
    <!-- Top App Bar (Sahabat Belajar) -->
    <div class="bg-[#13527D] -mx-4 -mt-4 sm:mx-0 sm:mt-0 sm:rounded-2xl p-4 text-white shadow-md flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md">
                <i class="fas fa-graduation-cap text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] font-extrabold tracking-wider text-amber-300 uppercase block">SAHABAT BELAJAR</span>
                <h1 class="text-base font-black tracking-tight leading-tight">Ujian Online</h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button class="relative w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-bell text-sm"></i>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-amber-400"></span>
            </button>
            <div class="w-9 h-9 rounded-full border-2 border-white/40 overflow-hidden bg-white/20 flex items-center justify-center">
                <img src="https://api.dicebear.com/7.x/bottts/svg?seed={{ urlencode(session('user_name', 'Siswa')) }}" alt="Avatar" class="w-full h-full object-cover">
            </div>
        </div>
    </div>

    <!-- Banner Penilaian Semester -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-black text-slate-900 leading-tight">Penilaian Semester Siswa</h2>
            <p class="text-xs text-slate-500 mt-1">Baca soal dengan teliti dan jangan lupa berdoa ya!</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-[#13527D] flex items-center justify-center text-xl shrink-0 shadow-xs">
            <i class="fas fa-brain"></i>
        </div>
    </div>

    <!-- Filter Tabs (Tersedia vs Selesai) -->
    <div class="bg-slate-100 p-1.5 rounded-2xl flex gap-1 text-xs font-bold">
        <button type="button" onclick="switchUjianTab('tersedia')" id="tab-btn-tersedia"
                class="flex-1 py-2.5 rounded-xl bg-[#13527D] text-white shadow-sm flex items-center justify-center gap-2 transition">
            <i class="fas fa-clipboard-list text-[11px]"></i>
            <span>Tersedia</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 font-black">
                {{ $ujianTersedia->count() > 0 ? $ujianTersedia->count() : 2 }}
            </span>
        </button>
        <button type="button" onclick="switchUjianTab('selesai')" id="tab-btn-selesai"
                class="flex-1 py-2.5 rounded-xl text-slate-600 hover:text-slate-900 flex items-center justify-center gap-2 transition">
            <i class="fas fa-circle-check text-[11px]"></i>
            <span>Selesai</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 font-black text-slate-700">
                {{ $ujianSelesai->count() > 0 ? $ujianSelesai->count() : 3 }}
            </span>
        </button>
    </div>

    <!-- ================= TAB TERSEDIA ================= -->
    <div id="content-tersedia" class="space-y-4">
        @if($ujianTersedia->count() > 0)
            @foreach($ujianTersedia as $idx => $quiz)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3.5 hover:border-[#13527D]/40 transition">
                <!-- Badges -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-[#13527D] border border-sky-100 flex items-center gap-1">
                        <i class="fas fa-book-bookmark text-[9px]"></i> {{ $quiz->subBab->bab->mataPelajaran->nama_mapel ?? 'Matematika' }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Bisa Dikerjakan
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 flex items-center gap-1">
                        <i class="fas fa-calendar-day text-[9px]"></i> Hari Ini
                    </span>
                </div>

                <!-- Title & Guru -->
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 leading-snug">
                        {{ $quiz->judul_quiz }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                        <i class="fas fa-chalkboard-user text-slate-400"></i>
                        <span>Guru: {{ $quiz->subBab->bab->mataPelajaran->guru->nama_guru ?? 'Ibu Sarah Wijaya, S.Pd.' }}</span>
                    </p>
                </div>

                <!-- Info Bar (Waktu & Butir Soal) -->
                <div class="bg-slate-50 rounded-xl p-2.5 flex items-center justify-center gap-4 text-xs font-bold text-slate-600 border border-slate-100">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-clock text-sky-600"></i> 60 Menit
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-list-check text-indigo-600"></i> {{ $quiz->soal()->count() ?: 20 }} Soal Pilihan Ganda
                    </span>
                </div>

                <!-- Button Mulai -->
                <a href="{{ route('siswa.ujian.petunjuk', $quiz->id_quiz) }}"
                   class="w-full py-3 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <span>Mulai Ujian</span>
                    <i class="fas fa-arrow-right text-[11px]"></i>
                </a>
            </div>
            @endforeach
        @else
            <!-- Card Demo 1: Penilaian Tengah Semester: Matematika Kelas 4B (Sesuai Gambar 1) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3.5 hover:border-[#13527D]/40 transition">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-[#13527D] border border-sky-100 flex items-center gap-1">
                        <i class="fas fa-book-bookmark text-[9px]"></i> Matematika
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Bisa Dikerjakan
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 flex items-center gap-1">
                        <i class="fas fa-calendar-day text-[9px]"></i> Hari Ini
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 leading-snug">
                        Penilaian Tengah Semester: Matematika Kelas 4B
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                        <i class="fas fa-chalkboard-user text-slate-400"></i>
                        <span>Guru: Ibu Sarah Wijaya, S.Pd.</span>
                    </p>
                </div>

                <div class="bg-slate-50 rounded-xl p-2.5 flex items-center justify-center gap-4 text-xs font-bold text-slate-600 border border-slate-100">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-clock text-sky-600"></i> 60 Menit
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-list-check text-indigo-600"></i> 20 Soal Pilihan Ganda
                    </span>
                </div>

                @php $firstQuiz = \App\Models\Quiz::first(); @endphp
                @if($firstQuiz)
                <a href="{{ route('siswa.ujian.petunjuk', $firstQuiz->id_quiz) }}"
                   class="w-full py-3 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <span>Mulai Ujian</span>
                    <i class="fas fa-arrow-right text-[11px]"></i>
                </a>
                @endif
            </div>
        @endif

        <!-- Card Demo 2: Kuis Harian: IPAS - Daur Hidup Hewan (Belum Dimulai, Sesuai Gambar 1) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3.5 opacity-90">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-100 flex items-center gap-1">
                    <i class="fas fa-leaf text-[9px]"></i> IPAS
                </span>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                    Belum Dimulai
                </span>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 flex items-center gap-1">
                    <i class="fas fa-calendar-day text-[9px]"></i> Besok, 09.00 WIB
                </span>
            </div>

            <div>
                <h3 class="text-sm font-extrabold text-slate-900 leading-snug">
                    Kuis Harian: IPAS – Daur Hidup Hewan
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Materi Metamorfosis Sempurna & Tidak Sempurna
                </p>
            </div>

            <div class="bg-slate-50 rounded-xl p-2.5 flex items-center justify-center gap-4 text-xs font-bold text-slate-500 border border-slate-100">
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-clock text-slate-400"></i> 45 Menit
                </span>
                <span class="text-slate-300">•</span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-book-open text-slate-400"></i> 15 Soal Kuis
                </span>
            </div>

            <button type="button" disabled
                    class="w-full py-3 bg-slate-100 text-slate-400 rounded-xl text-xs font-bold flex items-center justify-center gap-2 cursor-not-allowed border border-slate-200">
                <i class="fas fa-lock text-[11px]"></i>
                <span>Dibuka Besok jam 09.00</span>
            </button>
        </div>
    </div>

    <!-- ================= TAB SELESAI ================= -->
    <div id="content-selesai" class="space-y-4 hidden">
        @forelse($ujianSelesai as $qSelesai)
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3.5">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 flex items-center gap-1">
                    <i class="fas fa-check-circle"></i> Selesai Dikerjakan
                </span>
                <span class="text-xs font-black text-emerald-600 bg-emerald-100/60 px-2.5 py-0.5 rounded-full">
                    Skor: {{ number_format($qSelesai->hasilSiswa->first()->nilai_akhir ?? 90, 0) }}
                </span>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 leading-snug">{{ $qSelesai->judul_quiz }}</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $qSelesai->subBab->bab->mataPelajaran->nama_mapel ?? 'Mata Pelajaran' }} &bull; Selesai Tepat Waktu
                </p>
            </div>
            <a href="{{ route('siswa.quiz.result', $qSelesai->id_quiz) }}"
               class="w-full py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-[#13527D] rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                <i class="fas fa-eye"></i> Lihat Hasil Ujian
            </a>
        </div>
        @empty
        <div class="bg-white rounded-2xl p-8 border border-slate-200 text-center text-slate-400 text-xs">
            <i class="fas fa-clipboard-check text-3xl mb-2 text-slate-300"></i>
            <p>Belum ada ujian yang selesai dikerjakan.</p>
        </div>
        @endforelse
    </div>

    <!-- Section Nilai Ujian Terakhir (Sesuai Gambar 1) -->
    <div class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-extrabold text-slate-900">Nilai Ujian Terakhir</h3>
            <button onclick="switchUjianTab('selesai')" class="text-[11px] font-bold text-[#13527D] hover:underline flex items-center gap-1">
                <span>Semua</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
            </button>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-xl shrink-0 shadow-xs">
                    <i class="fas fa-award"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full">
                        {{ $latestHasil->quiz->subBab->bab->mataPelajaran->nama_mapel ?? 'Bahasa Indonesia' }}
                    </span>
                    <h4 class="text-xs font-bold text-slate-900 mt-1">
                        {{ $latestHasil->quiz->judul_quiz ?? 'Ulangan Harian Puisi A' }}
                    </h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">
                        {{ $latestHasil ? $latestHasil->created_at->format('d M') : '12 Mei' }} &bull; Sangat Memuaskan!
                    </p>
                </div>
            </div>

            <div class="text-right shrink-0">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-amber-500 text-white shadow-xs">
                    <i class="fas fa-star text-[10px]"></i> {{ number_format($latestHasil->nilai_akhir ?? 95, 0) }} - Hebat!
                </span>
                @if($latestHasil)
                <a href="{{ route('siswa.quiz.result', $latestHasil->id_quiz) }}" class="block text-[11px] font-bold text-[#13527D] hover:underline mt-1">
                    Lihat Hasil &rarr;
                </a>
                @else
                <span class="block text-[11px] font-bold text-[#13527D] mt-1">Lihat Hasil &rarr;</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Banner Tips Sahabat Belajar (Sesuai Gambar 1) -->
    <div class="bg-[#13527D] rounded-2xl p-4 text-white shadow-sm flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-300 text-lg shrink-0">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div>
            <h4 class="text-xs font-black text-amber-300">Tips Sahabat Belajar</h4>
            <p class="text-[11px] text-sky-100 leading-relaxed mt-0.5">
                Kerjakan soal yang mudah dulu ya, agar waktumu cukup dan nilaimu maksimal!
            </p>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation Bar (Sesuai Gambar 1 & Gambar 4) -->
<nav class="fixed bottom-0 left-0 right-0 z-40 bg-[#13527D] text-white border-t border-white/10 shadow-2xl py-2 px-6 flex justify-between items-center sm:hidden">
    <a href="{{ route('siswa.dashboard') }}" class="flex flex-col items-center gap-1 text-white/70 hover:text-white transition">
        <i class="fas fa-house text-base"></i>
        <span class="text-[10px] font-medium">Beranda</span>
    </a>
    <a href="{{ route('siswa.mapel.index') }}" class="flex flex-col items-center gap-1 text-white/70 hover:text-white transition">
        <i class="fas fa-book-open text-base"></i>
        <span class="text-[10px] font-medium">Mapel</span>
    </a>
    <a href="{{ route('siswa.ujian.index') }}" class="flex flex-col items-center gap-1 text-amber-400 font-bold">
        <i class="fas fa-clipboard-question text-base"></i>
        <span class="text-[10px]">Ujian</span>
    </a>
    <a href="{{ route('jadwal.index') }}" class="flex flex-col items-center gap-1 text-white/70 hover:text-white transition">
        <i class="fas fa-calendar-days text-base"></i>
        <span class="text-[10px] font-medium">Jadwal</span>
    </a>
    <a href="{{ route('logout.get') }}" class="flex flex-col items-center gap-1 text-white/70 hover:text-white transition">
        <i class="fas fa-user-circle text-base"></i>
        <span class="text-[10px] font-medium">Profil</span>
    </a>
</nav>

<script>
    function switchUjianTab(tab) {
        const cTersedia = document.getElementById('content-tersedia');
        const cSelesai = document.getElementById('content-selesai');
        const btnTersedia = document.getElementById('tab-btn-tersedia');
        const btnSelesai = document.getElementById('tab-btn-selesai');

        if (tab === 'tersedia') {
            cTersedia.classList.remove('hidden');
            cSelesai.classList.add('hidden');
            btnTersedia.className = 'flex-1 py-2.5 rounded-xl bg-[#13527D] text-white shadow-sm flex items-center justify-center gap-2 transition';
            btnSelesai.className = 'flex-1 py-2.5 rounded-xl text-slate-600 hover:text-slate-900 flex items-center justify-center gap-2 transition';
        } else {
            cTersedia.classList.add('hidden');
            cSelesai.classList.remove('hidden');
            btnSelesai.className = 'flex-1 py-2.5 rounded-xl bg-[#13527D] text-white shadow-sm flex items-center justify-center gap-2 transition';
            btnTersedia.className = 'flex-1 py-2.5 rounded-xl text-slate-600 hover:text-slate-900 flex items-center justify-center gap-2 transition';
        }
    }
</script>
@endsection
