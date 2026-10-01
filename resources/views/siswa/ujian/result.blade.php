@extends('layouts.siswa')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-16">
    @php
        $mapelName = $quiz->mataPelajaran->nama_mapel ?? ($quiz->subBab->bab->mataPelajaran->nama_mapel ?? 'Mata Pelajaran');
        $guruName = $quiz->guru->nama_guru ?? ($quiz->mataPelajaran->guru->nama_guru ?? ($quiz->subBab->bab->mataPelajaran->guru->nama_guru ?? 'Guru Pengampu'));
        $durasi = $quiz->durasi_menit ?? 60;
        $isLulus = $hasil->nilai_akhir >= 75;
        $isPublished = (bool)($hasil->status_kirim ?? false);
    @endphp

    <!-- Top Header Bar with Back Button -->
    <div class="bg-[#13527D] rounded-3xl p-5 sm:p-6 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('siswa.ujian.index') }}" class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition shrink-0" title="Kembali">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-sky-200">
                    Hasil Ujian CBT
                </span>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white mt-0.5">
                    {{ $quiz->judul_quiz }}
                </h1>
                <p class="text-xs text-sky-200 mt-0.5">
                    {{ $mapelName }} &bull; Guru: {{ $guruName }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.ujian.index') }}"
               class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Daftar Ujian</span>
            </a>
        </div>
    </div>

    @if(!$isPublished)
    <!-- ================= STATE: MENUNGGU RILIS NILAI DARI GURU ================= -->
    <div class="bg-white rounded-3xl p-8 sm:p-12 border border-slate-200/90 shadow-sm text-center max-w-2xl mx-auto space-y-6">
        <div class="w-20 h-20 rounded-3xl bg-amber-50 text-amber-500 border border-amber-200 flex items-center justify-center text-3xl mx-auto shadow-inner">
            <i class="fas fa-hourglass-half animate-pulse"></i>
        </div>

        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                <i class="fas fa-clock"></i> MENUNGGU PUBLIKASI NILAI OLEH GURU
            </span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900">
                Ujian Berhasil Dikumpulkan!
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                Terima kasih, <strong>{{ session('user_name', 'Siswa') }}</strong>. Lembar jawaban ujian Anda telah tersimpan dengan aman di server sekolah.
            </p>
        </div>

        <!-- Meta Summary Box -->
        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 text-left text-xs space-y-2.5 max-w-md mx-auto">
            <div class="flex justify-between">
                <span class="text-slate-500">Mata Pelajaran:</span>
                <span class="font-bold text-slate-800">{{ $mapelName }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Guru Pengampu:</span>
                <span class="font-bold text-slate-800">{{ $guruName }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Waktu Pengumpulan:</span>
                <span class="font-bold text-slate-800">{{ $hasil->created_at ? $hasil->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }} WIB</span>
            </div>
            <div class="flex justify-between border-t border-slate-200/60 pt-2">
                <span class="text-slate-500">Status Nilai:</span>
                <span class="font-bold text-amber-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Menunggu Guru Mengirimkan Nilai
                </span>
            </div>
        </div>

        <!-- Info Note -->
        <div class="p-4 rounded-2xl bg-sky-50 border border-sky-100 text-xs text-sky-800 leading-relaxed max-w-md mx-auto flex items-start gap-2.5 text-left">
            <i class="fas fa-circle-info text-sky-600 mt-0.5 shrink-0"></i>
            <span>Nilai akhir dan pembahasan soal akan otomatis terbuka setelah guru pengampu menekan tombol <strong>Kirim Nilai ke Semua Siswa</strong> di panel rekap ujian guru.</span>
        </div>

        <div class="pt-2">
            <a href="{{ route('siswa.ujian.index') }}"
               class="px-8 py-3.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-black transition inline-flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Daftar Ujian</span>
            </a>
        </div>
    </div>

    @else
    <!-- ================= STATE: NILAI TELAH DIRILIS OLEH GURU ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Left: Circular Gauge Card (5/12) -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm flex flex-col justify-between items-center text-center space-y-4">
            <div>
                @if($isLulus)
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fas fa-check-circle text-emerald-600"></i> LULUS KKM (Target: 75)
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                    <i class="fas fa-circle-exclamation text-rose-600"></i> PERLU REMEDIAL (Target: 75)
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
                        $gaugeColor = $isLulus ? '#13527D' : '#E11D48';
                    @endphp
                    <circle cx="60" cy="60" r="48" fill="transparent" stroke="{{ $gaugeColor }}" stroke-width="10"
                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $strokeOffset }}" stroke-linecap="round" />
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-5xl font-black text-[#13527D] tracking-tight leading-none">
                        {{ round($hasil->nilai_akhir) }}
                    </span>
                    <span class="text-xs font-bold text-slate-400 mt-1">
                        dari 100
                    </span>
                </div>
            </div>

            <!-- Verbal Praise & Timestamp -->
            <div class="space-y-1">
                <h3 class="text-base font-black text-slate-900">
                    @if($hasil->nilai_akhir >= 85)
                        🌟 Sangat Baik & Membanggakan!
                    @elseif($hasil->nilai_akhir >= 75)
                        👍 Baik & Memenuhi Target KKM!
                    @else
                        💪 Tetap Semangat, Ayo Bimbingan Remedial!
                    @endif
                </h3>
                <p class="text-xs text-slate-500">
                    Dirilis pada {{ $hasil->waktu_kirim ? \Carbon\Carbon::parse($hasil->waktu_kirim)->format('d M Y, H:i') : ($hasil->updated_at ? $hasil->updated_at->format('d M Y, H:i') : now()->format('d M Y, H:i')) }} WIB
                </p>
            </div>
        </div>

        <!-- Right: 3 KPI Cards + Teacher Note + Actions (7/12) -->
        <div class="lg:col-span-7 flex flex-col justify-between space-y-5">
            <!-- 3 Stat KPI Cards -->
            <div class="grid grid-cols-3 gap-4">
                <!-- Benar -->
                <div class="bg-[#13527D] text-white p-5 rounded-3xl shadow-sm text-center space-y-1.5">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#13527D] flex items-center justify-center mx-auto text-xs font-black shadow-xs">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="text-2xl font-black">{{ $hasil->jumlah_benar }}</div>
                    <div class="text-xs font-bold text-sky-100">Jawaban Benar</div>
                </div>

                <!-- Salah -->
                <div class="bg-amber-500 text-white p-5 rounded-3xl shadow-sm text-center space-y-1.5">
                    <div class="w-8 h-8 rounded-xl bg-white text-amber-500 flex items-center justify-center mx-auto text-xs font-black shadow-xs">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="text-2xl font-black">{{ $hasil->jumlah_salah }}</div>
                    <div class="text-xs font-bold text-amber-100">Jawaban Salah</div>
                </div>

                <!-- Total Soal -->
                <div class="bg-[#0E3D5D] text-white p-5 rounded-3xl shadow-sm text-center space-y-1.5">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#0E3D5D] flex items-center justify-center mx-auto text-xs font-black shadow-xs">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <div class="text-2xl font-black">{{ count($reviewDetails) }}</div>
                    <div class="text-xs font-bold text-sky-100">Total Soal</div>
                </div>
            </div>

            <!-- Info Box Pengampu & Catatan Evaluasi -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#13527D] flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">GURU PENGAMPU MATA PELAJARAN</span>
                        <h4 class="text-sm font-black text-slate-900 leading-tight">
                            {{ $guruName }}
                        </h4>
                        <p class="text-xs text-slate-500">{{ $mapelName }}</p>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-[#13527D] text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="text-xs text-slate-700 leading-relaxed">
                        @if($hasil->catatan_guru)
                            <strong class="text-[#13527D]">Catatan Guru:</strong> &ldquo;{{ $hasil->catatan_guru }}&rdquo;
                        @elseif($isLulus)
                            Hasil ujian telah memenuhi kriteria ketuntasan minimal (KKM). Terus pertahankan prestasi belajar Anda!
                        @else
                            Hasil ujian belum mencapai target KKM. Silakan hubungi guru pengampu mata pelajaran untuk jadwal remedial atau pengayaan materi.
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <button type="button" onclick="document.getElementById('section-pembahasan').classList.toggle('hidden'); this.scrollIntoView({behavior: 'smooth'})"
                        class="w-full sm:flex-1 py-3.5 bg-white border-2 border-[#13527D] text-[#13527D] hover:bg-sky-50 rounded-2xl text-xs font-black transition flex items-center justify-center gap-2 shadow-xs">
                    <i class="fas fa-book-open"></i>
                    <span>Buka / Tutup Pembahasan Soal</span>
                </button>

                <a href="{{ route('siswa.ujian.index') }}"
                   class="w-full sm:flex-1 py-3.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-2xl text-xs font-black transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Daftar Ujian</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ================= PEMBAHASAN SOAL LENGKAP ================= -->
    <div id="section-pembahasan" class="space-y-4 pt-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
            <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-list-check text-[#13527D]"></i> Pembahasan Butir Soal Ujian
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
    @endif
</div>
@endsection
