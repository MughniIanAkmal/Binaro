@extends('layouts.siswa')

@section('content')
<div class="max-w-xl mx-auto space-y-4 pb-20">
    <!-- Top Header Bar with Back Button (Sesuai Gambar 4) -->
    <div class="bg-[#13527D] -mx-4 -mt-4 sm:mx-0 sm:mt-0 sm:rounded-2xl px-4 py-3.5 text-white shadow-md flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.ujian.index') }}" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-base font-black tracking-tight">Hasil Ujian Siswa</h1>
        </div>
        <div class="w-9 h-9 rounded-full border-2 border-white/40 overflow-hidden bg-white/20 flex items-center justify-center">
            <img src="https://api.dicebear.com/7.x/bottts/svg?seed={{ urlencode(session('user_name', 'Siswa')) }}" alt="Avatar" class="w-full h-full object-cover">
        </div>
    </div>

    <!-- Top Celebration Banner (Sesuai Gambar 4) -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-2xl shrink-0 shadow-xs">
            <i class="fas fa-bullhorn"></i>
        </div>
        <div>
            <h2 class="text-sm font-black text-slate-900 leading-snug">
                Hebat, {{ session('user_name', 'Budi Santoso') }}!
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Ujian telah selesai dikumpulkan tepat waktu!
            </p>
        </div>
    </div>

    <!-- Main Score Circular Gauge Card (Sesuai Gambar 4) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm text-center space-y-4">
        <div>
            @if($hasil->nilai_akhir >= 75)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                <i class="fas fa-check-circle text-emerald-600"></i> LULUS KKM (Target: 75)
            </span>
            @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                <i class="fas fa-circle-exclamation text-rose-600"></i> Perlu Remedial (Target: 75)
            </span>
            @endif
        </div>

        <!-- Circular Score Ring Gauge -->
        <div class="relative w-44 h-44 mx-auto flex items-center justify-center">
            <svg viewBox="0 0 120 120" class="w-full h-full transform -rotate-90">
                <!-- Background Circle -->
                <circle cx="60" cy="60" r="48" fill="transparent" stroke="#E2E8F0" stroke-width="10" />
                <!-- Progress Circle -->
                @php
                    $circumference = 2 * pi() * 48;
                    $scorePercent = max(min($hasil->nilai_akhir, 100), 0);
                    $strokeOffset = $circumference - ($scorePercent / 100 * $circumference);
                    $gaugeColor = $hasil->nilai_akhir >= 75 ? '#13527D' : '#E11D48';
                @endphp
                <circle cx="60" cy="60" r="48" fill="transparent" stroke="{{ $gaugeColor }}" stroke-width="10"
                        stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $strokeOffset }}" stroke-linecap="round" />
            </svg>

            <!-- Center Score Number -->
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-4xl font-black text-[#13527D] tracking-tight leading-none">
                    {{ round($hasil->nilai_akhir) }}
                </span>
                <span class="text-[11px] font-bold text-slate-400 mt-1">
                    dari 100
                </span>
            </div>
        </div>

        <!-- Grade Verbal Status -->
        <div>
            <h3 class="text-sm font-black text-slate-800">
                @if($hasil->nilai_akhir >= 85)
                    Sangat Baik & Membanggakan
                @elseif($hasil->nilai_akhir >= 75)
                    Baik & Memenuhi Target KKM
                @else
                    Perlu Latihan & Bimbingan Remedial
                @endif
            </h3>
        </div>
    </div>

    <!-- 3 Stat KPI Cards (Sesuai Gambar 4) -->
    <div class="grid grid-cols-3 gap-3">
        <!-- 1. Benar -->
        <div class="bg-[#13527D] text-white p-4 rounded-2xl shadow-sm text-center space-y-1">
            <div class="w-7 h-7 rounded-xl bg-white text-[#13527D] flex items-center justify-center mx-auto text-xs font-black shadow-xs">
                <i class="fas fa-check"></i>
            </div>
            <div class="text-xl font-black">{{ $hasil->jumlah_benar }}</div>
            <div class="text-[11px] font-bold text-white/80">Jawaban Benar</div>
        </div>

        <!-- 2. Salah -->
        <div class="bg-amber-500 text-white p-4 rounded-2xl shadow-sm text-center space-y-1">
            <div class="w-7 h-7 rounded-xl bg-white text-amber-500 flex items-center justify-center mx-auto text-xs font-black shadow-xs">
                <i class="fas fa-times"></i>
            </div>
            <div class="text-xl font-black">{{ $hasil->jumlah_salah }}</div>
            <div class="text-[11px] font-bold text-white/80">Jawaban Salah</div>
        </div>

        <!-- 3. Durasi Menit -->
        <div class="bg-[#0E3D5D] text-white p-4 rounded-2xl shadow-sm text-center space-y-1">
            <div class="w-7 h-7 rounded-xl bg-white text-[#0E3D5D] flex items-center justify-center mx-auto text-xs font-black shadow-xs">
                <i class="fas fa-clock"></i>
            </div>
            <div class="text-xl font-black">38'</div>
            <div class="text-[11px] font-bold text-white/80">Menit</div>
        </div>
    </div>

    <!-- Subject & Teacher Card (Sesuai Gambar 4) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-calculator"></i>
            </div>
            <div>
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">MATA PELAJARAN</span>
                <h4 class="text-sm font-black text-slate-900 leading-tight">
                    {{ $quiz->judul_quiz }}
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $quiz->subBab->bab->mataPelajaran->guru->nama_guru ?? 'Ibu Sarah Wijaya, S.Pd.' }}
                </p>
            </div>
        </div>

        <!-- Catatan Guru Quote Box -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 flex items-start gap-3">
            <div class="w-9 h-9 rounded-full overflow-hidden bg-[#13527D] text-white flex items-center justify-center shrink-0">
                <i class="fas fa-chalkboard-user text-sm"></i>
            </div>
            <div class="space-y-1">
                <span class="text-[10px] font-black text-[#13527D] flex items-center gap-1 uppercase tracking-wide">
                    <i class="fas fa-comment-dots text-xs"></i> Catatan Guru
                </span>
                <p class="text-xs text-slate-700 leading-relaxed italic font-medium">
                    &ldquo;Bagus sekali pemahaman materi pecahannya, pertahankan ya {{ explode(' ', session('user_name', 'Budi'))[0] }}!&rdquo;
                </p>
            </div>
        </div>
    </div>

    <!-- Action Buttons (Sesuai Gambar 4) -->
    <div class="space-y-2.5 pt-1">
        <button type="button" onclick="document.getElementById('section-pembahasan').classList.toggle('hidden')"
                class="w-full py-3.5 bg-white border-2 border-[#13527D] text-[#13527D] hover:bg-sky-50 rounded-2xl text-xs font-black transition flex items-center justify-center gap-2 shadow-xs">
            <i class="fas fa-book-open"></i>
            <span>Lihat Pembahasan Soal</span>
        </button>

        <a href="{{ route('siswa.ujian.index') }}"
           class="w-full py-3.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-2xl text-xs font-black transition flex items-center justify-center gap-2 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Daftar Ujian</span>
        </a>
    </div>

    <!-- Collapsible Pembahasan Soal -->
    <div id="section-pembahasan" class="hidden space-y-3 pt-2">
        <div class="flex items-center justify-between px-1">
            <h4 class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                <i class="fas fa-list-check text-[#13527D]"></i> Pembahasan Butir Soal
            </h4>
            <span class="text-[10px] text-slate-400 font-bold">{{ count($reviewDetails) }} Soal</span>
        </div>

        @foreach($reviewDetails as $idx => $item)
        @php
            $isCorrect = $item['is_correct'];
            $userAns = $item['user_answer'];
            $key = $item['kunci_jawaban'];
        @endphp
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-2">
                    <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center shrink-0 {{ $isCorrect === true ? 'bg-emerald-600 text-white' : ($isCorrect === false ? 'bg-rose-600 text-white' : 'bg-slate-700 text-white') }}">
                        {{ $idx + 1 }}
                    </span>
                    <p class="text-xs font-bold text-slate-800 leading-snug">
                        {{ $item['pertanyaan'] }}
                    </p>
                </div>
                <div>
                    @if($isCorrect === true)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Benar</span>
                    @elseif($isCorrect === false)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Salah</span>
                    @endif
                </div>
            </div>

            <!-- Options Preview -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs pl-8">
                @foreach(['A' => $item['opsi_a'], 'B' => $item['opsi_b'], 'C' => $item['opsi_c'], 'D' => $item['opsi_d']] as $optKey => $optVal)
                @php
                    $isCorrectKey = ($optKey === $key);
                    $isUserChoice = ($optKey === $userAns);
                    $style = 'bg-slate-50 border-slate-200 text-slate-600';
                    if ($isCorrectKey) {
                        $style = 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold';
                    } elseif ($isUserChoice && !$isCorrect) {
                        $style = 'bg-rose-50 border-rose-300 text-rose-900 line-through';
                    }
                @endphp
                <div class="p-2 rounded-xl border {{ $style }} flex items-center justify-between text-[11px]">
                    <span><strong>{{ $optKey }}.</strong> {{ $optVal }}</span>
                    @if($isCorrectKey)
                    <i class="fas fa-check text-emerald-600 text-[10px]"></i>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Mobile Bottom Navigation Bar (Sesuai Gambar 4) -->
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
@endsection
