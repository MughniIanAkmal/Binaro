@extends('layouts.siswa')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-16">
    @php
        $mapelName = $quiz->subBab->bab->mataPelajaran->nama_mapel ?? ($quiz->mataPelajaran->nama_mapel ?? 'Mata Pelajaran');
        $babName = $quiz->subBab->bab->nama_bab ?? null;
        $subBabName = $quiz->subBab->nama_sub_bab ?? null;
        $guruName = $quiz->guru->nama_guru ?? ($quiz->mataPelajaran->guru->nama_guru ?? 'Guru Pengampu');
        $durasi = $quiz->durasi_menit ?? 20;
        $isLulus = $hasil->nilai_akhir >= 75;

        $backUrl = route('siswa.dashboard');
        if ($quiz->materi) {
            $backUrl = route('siswa.materi.view', $quiz->materi->id_materi);
        } elseif ($quiz->subBab) {
            $backUrl = route('siswa.sub_bab.materi', $quiz->subBab->id_sub_bab);
        } elseif ($quiz->id_mapel) {
            $backUrl = route('siswa.materi.index', $quiz->id_mapel);
        }
    @endphp

    <!-- Top Header Bar with Back Button to Materi -->
    <div class="bg-[#13527D] rounded-3xl p-5 sm:p-6 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ $backUrl }}" class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition shrink-0" title="Kembali ke Materi Belajar">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400 text-slate-900 shadow-xs flex items-center gap-1.5 w-max">
                    <i class="fas fa-brain text-[10px]"></i> Hasil Kuis Pemahaman Materi
                </span>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white mt-1">
                    {{ $quiz->judul_quiz }}
                </h1>
                <p class="text-xs text-sky-200 mt-0.5">
                    {{ $mapelName }}@if($babName) &bull; {{ $babName }}@endif @if($subBabName) &bull; {{ $subBabName }}@endif &bull; Guru: {{ $guruName }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}"
               class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fas fa-book-open text-xs"></i>
                <span>Kembali ke Materi</span>
            </a>
        </div>
    </div>

    <!-- ================= SCORE & SUMMARY CARD ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Left: Circular Gauge Card (5/12) -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm flex flex-col justify-between items-center text-center space-y-4">
            <div>
                @if($isLulus)
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fas fa-check-circle text-emerald-600"></i> PEMAHAMAN BAIK (Skor &ge; 75)
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200">
                    <i class="fas fa-triangle-exclamation text-amber-600"></i> PERLU BELAJAR LAGI (Skor &lt; 75)
                </span>
                @endif
            </div>

            <!-- Circular Gauge -->
            <div class="relative w-48 h-48 mx-auto flex items-center justify-center">
                <svg viewBox="0 0 120 120" class="w-full h-full transform -rotate-90">
                    <circle cx="60" cy="60" r="48" fill="transparent" stroke="#E2E8F0" stroke-width="10" />
                    @php
                        $circumference = 2 * pi() * 48;
                        $scorePercent = max(min($hasil->nilai_akhir, 100), 0);
                        $strokeOffset = $circumference - ($scorePercent / 100 * $circumference);
                        $gaugeColor = $isLulus ? '#059669' : '#D97706';
                    @endphp
                    <circle cx="60" cy="60" r="48" fill="transparent" stroke="{{ $gaugeColor }}" stroke-width="10"
                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $strokeOffset }}" stroke-linecap="round" />
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-5xl font-black text-slate-900 tracking-tight leading-none">
                        {{ round($hasil->nilai_akhir) }}
                    </span>
                    <span class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Skor Kuis</span>
                </div>
            </div>

            <div>
                <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                    @if($isLulus)
                    Selamat! Anda telah memahami materi ini dengan sangat baik. Lanjutkan mempelajari materi berikutnya!
                    @else
                    Jangan berkecil hati! Anda dapat membaca ulang materi dan mendiskusikannya dengan guru.
                    @endif
                </p>
            </div>
        </div>

        <!-- Right: Breakdown & Meta Info (7/12) -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm flex flex-col justify-between space-y-6">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-[#13527D]"></i> Ringkasan Kuis Materi
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Hasil pengerjaan latihan evaluasi sub-bab pembelajaran</p>
            </div>

            <!-- Stats Grid (3 Kolom) -->
            <div class="grid grid-cols-3 gap-3">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                    <span class="text-2xl font-black text-emerald-600 block">{{ $hasil->jumlah_benar }}</span>
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wide">Jawaban Benar</span>
                </div>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-center">
                    <span class="text-2xl font-black text-rose-600 block">{{ $hasil->jumlah_salah }}</span>
                    <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wide">Jawaban Salah</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                    <span class="text-2xl font-black text-slate-700 block">{{ count($reviewDetails) }}</span>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Total Soal</span>
                </div>
            </div>

            <!-- Detail List Meta -->
            <div class="space-y-2.5 text-xs border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Mata Pelajaran:</span>
                    <span class="font-bold text-slate-900">{{ $mapelName }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Nama Peserta:</span>
                    <span class="font-bold text-slate-900">{{ session('user_name', 'Siswa') }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Waktu Selesai:</span>
                    <span class="font-bold text-slate-900">{{ $hasil->created_at ? $hasil->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            <!-- Actions Footer -->
            <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
                <a href="#section-pembahasan"
                   class="w-full sm:flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                    <i class="fas fa-list-check text-xs"></i>
                    <span>Lihat Pembahasan Soal</span>
                </a>
                <a href="{{ $backUrl }}"
                   class="w-full sm:flex-1 py-3 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Kembali ke Materi Belajar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ================= PEMBAHASAN SOAL LENGKAP ================= -->
    <div id="section-pembahasan" class="space-y-4 pt-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
            <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-list-check text-[#13527D]"></i> Pembahasan Butir Soal Kuis Materi
            </h4>
            <span class="text-xs text-slate-500 font-bold">{{ count($reviewDetails) }} Soal Lengkap</span>
        </div>

        @foreach($reviewDetails as $idx => $item)
        @php
            $isCorrect = $item['is_correct'];
            $userAns = $item['user_answer'];
            $key = $item['kunci_jawaban'];
        @endphp
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl text-xs font-black flex items-center justify-center shrink-0 {{ $isCorrect === true ? 'bg-emerald-600 text-white' : ($isCorrect === false ? 'bg-rose-600 text-white' : 'bg-slate-700 text-white') }}">
                        {{ $idx + 1 }}
                    </span>
                    <div class="space-y-2">
                        <div class="text-sm font-bold text-slate-900 leading-relaxed">
                            {!! nl2br(e($item['pertanyaan'])) !!}
                        </div>

                        <!-- Gambar Soal Jika Ada -->
                        @if(!empty($item['gambar']))
                        <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 max-w-md">
                            <img src="{{ asset('storage/' . $item['gambar']) }}" alt="Gambar Soal {{ $idx + 1 }}" class="rounded-lg max-h-60 object-contain">
                        </div>
                        @endif
                    </div>
                </div>

                <div class="shrink-0">
                    @if($isCorrect === true)
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <i class="fas fa-check text-[10px]"></i> Benar
                    </span>
                    @elseif($isCorrect === false)
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300">
                        <i class="fas fa-times text-[10px]"></i> Salah
                    </span>
                    @else
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-700">
                        Tidak Dijawab
                    </span>
                    @endif
                </div>
            </div>

            <!-- Options Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs sm:pl-11">
                @foreach(['A' => $item['opsi_a'], 'B' => $item['opsi_b'], 'C' => $item['opsi_c'], 'D' => $item['opsi_d']] as $optKey => $optVal)
                @php
                    $isCorrectKey = ($optKey === $key);
                    $isUserChoice = ($optKey === $userAns);
                    $style = 'bg-slate-50 border-slate-200 text-slate-700';
                    if ($isCorrectKey) {
                        $style = 'bg-emerald-50 border-2 border-emerald-400 text-emerald-900 font-bold';
                    } elseif ($isUserChoice && !$isCorrect) {
                        $style = 'bg-rose-50 border-2 border-rose-400 text-rose-900 line-through';
                    }
                @endphp
                <div class="p-3 rounded-xl border {{ $style }} flex items-center justify-between text-xs">
                    <span><strong>{{ $optKey }}.</strong> {{ $optVal }}</span>
                    @if($isCorrectKey)
                    <span class="text-[10px] font-black uppercase text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">Kunci</span>
                    @elseif($isUserChoice)
                    <span class="text-[10px] font-black uppercase text-rose-700 bg-rose-100 px-2 py-0.5 rounded-md">Jawabanmu</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Bottom Return CTA -->
    <div class="text-center pt-4">
        <a href="{{ $backUrl }}" class="px-6 py-3 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-2 shadow-sm">
            <i class="fas fa-book-open"></i>
            <span>Kembali ke Materi Belajar</span>
        </a>
    </div>
</div>
@endsection
