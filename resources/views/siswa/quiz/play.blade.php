@extends('layouts.siswa')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-20">
    @php
        $mapelName = $quiz->subBab->bab->mataPelajaran->nama_mapel ?? ($quiz->mataPelajaran->nama_mapel ?? 'Mata Pelajaran');
        $babName = $quiz->subBab->bab->nama_bab ?? null;
        $subBabName = $quiz->subBab->nama_sub_bab ?? null;
        $guruName = $quiz->guru->nama_guru ?? ($quiz->mataPelajaran->guru->nama_guru ?? 'Guru Pengampu');
        $durasiMenit = $quiz->durasi_menit ?? 30;

        $backUrl = route('siswa.dashboard');
        if ($quiz->materi) {
            $backUrl = route('siswa.materi.view', $quiz->materi->id_materi);
        } elseif ($quiz->subBab) {
            $backUrl = route('siswa.sub_bab.materi', $quiz->subBab->id_sub_bab);
        } elseif ($quiz->id_mapel) {
            $backUrl = route('siswa.materi.index', $quiz->id_mapel);
        }
    @endphp

    <!-- Top Desktop Kuis Header Bar -->
    <div class="bg-[#13527D] rounded-3xl p-5 sm:p-6 text-white shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ $backUrl }}" onclick="return confirm('Apakah Anda yakin ingin keluar dari lembar kuis materi? Jawaban yang belum dikirim akan hilang.');"
               class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition shrink-0" title="Kembali ke Materi Belajar">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400 text-slate-900 shadow-xs flex items-center gap-1">
                        <i class="fas fa-brain text-[10px]"></i> Kuis Materi Pembelajaran
                    </span>
                    <span class="text-xs text-sky-200 font-medium">
                        {{ $mapelName }}@if($babName) &bull; {{ $babName }}@endif @if($subBabName) &bull; {{ $subBabName }}@endif
                    </span>
                </div>
                <h1 class="text-lg sm:text-xl font-black tracking-tight text-white mt-0.5">
                    {{ $quiz->judul_quiz }}
                </h1>
            </div>
        </div>

        <!-- Timer & Quick Student Profile -->
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
            <!-- Countdown Timer Badge -->
            <div class="flex items-center gap-2.5 bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/20 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-amber-400 text-slate-900 flex items-center justify-center text-sm shadow-xs">
                    <i class="fas fa-stopwatch"></i>
                </div>
                <div>
                    <span class="text-[9px] uppercase font-extrabold text-sky-200 block tracking-wider">Waktu Kuis</span>
                    <span id="timer-display" class="font-mono font-black text-base text-white">00:00:00</span>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="hidden sm:flex items-center gap-3 bg-white/10 px-3.5 py-2 rounded-2xl border border-white/10">
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-emerald-300 font-bold">
                    <i class="fas fa-user-graduate text-xs"></i>
                </div>
                <div class="text-left text-xs">
                    <div class="font-bold text-white truncate max-w-[120px]">{{ session('user_name', 'Siswa') }}</div>
                    <div class="text-[10px] text-white/70">Peserta Kuis</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Desktop Layout: Left Panel Soal (8/12) + Right Sidebar Navigasi Soal (4/12) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- ================= KOLOM KIRI: LEMBAR SOAL ================= -->
        <div class="lg:col-span-8 xl:col-span-9 space-y-5">
            <form id="form-quiz" action="{{ route('siswa.quiz.submit', $quiz->id_quiz) }}" method="POST">
                @csrf

                @foreach($soals as $index => $soal)
                <div id="soal-card-{{ $index }}" class="soal-slide space-y-5 {{ $index === 0 ? '' : 'hidden' }}">
                    <!-- Card Soal Konten -->
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-sm space-y-5">
                        <!-- Header Soal (Nomor, Bobot, Status) -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-2xl bg-[#13527D] text-white font-black text-sm flex items-center justify-center shadow-xs">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900">
                                        Soal Nomor {{ $index + 1 }} <span class="text-xs font-medium text-slate-400">dari {{ count($soals) }}</span>
                                    </h3>
                                    <span class="text-[11px] text-slate-500 font-medium">
                                        Bobot: <strong class="text-slate-700">{{ $soal->bobot_nilai ?? 10 }} Poin</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span id="status-badge-{{ $index }}" class="px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    Belum Dijawab
                                </span>
                            </div>
                        </div>

                        <!-- Teks Pertanyaan Asli yang Diinput Guru (Preserving formatting & symbols) -->
                        <div class="text-sm sm:text-base font-semibold text-slate-800 leading-relaxed space-y-3">
                            {!! nl2br(e($soal->pertanyaan)) !!}
                        </div>

                        <!-- Gambar Pendukung Soal dari Guru (Jika Ada) -->
                        @if(!empty($soal->gambar))
                        <div class="my-4 p-3.5 bg-slate-50/80 border border-slate-200/80 rounded-2xl max-w-2xl">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i class="fas fa-image text-slate-500"></i> Lampiran Gambar Pendukung Soal:
                            </div>
                            <div class="overflow-hidden rounded-xl bg-white border border-slate-200 p-2 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal {{ $index + 1 }}"
                                     class="max-h-96 w-auto max-w-full object-contain rounded-lg transition hover:scale-[1.01]">
                            </div>
                        </div>
                        @endif

                        <!-- Section Header Pilihan Jawaban -->
                        <div class="pt-2">
                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">
                                Pilihan Jawaban:
                            </span>
                        </div>

                        <!-- Pilihan Opsi A, B, C, D Asli Guru -->
                        <div class="space-y-3">
                            @foreach(['A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c, 'D' => $soal->opsi_d] as $optKey => $optVal)
                            <label id="label-opt-{{ $soal->id_soal }}-{{ $optKey }}"
                                   onclick="selectOption({{ $index }}, '{{ $soal->id_soal }}', '{{ $optKey }}')"
                                   class="opt-label-{{ $soal->id_soal }} bg-white hover:bg-slate-50/80 rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex items-center justify-between cursor-pointer transition select-none group">
                                <div class="flex items-center gap-3.5 flex-1 pr-4">
                                    <div id="badge-opt-{{ $soal->id_soal }}-{{ $optKey }}"
                                         class="opt-badge-{{ $soal->id_soal }} w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center transition shrink-0 group-hover:bg-[#13527D]/10 group-hover:text-[#13527D]">
                                        {{ $optKey }}
                                    </div>
                                    <span id="text-opt-{{ $soal->id_soal }}-{{ $optKey }}" class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">
                                        {{ $optVal }}
                                    </span>
                                </div>

                                <input type="radio" name="jawaban[{{ $soal->id_soal }}]" value="{{ $optKey }}" class="hidden">
                                <div id="check-icon-{{ $soal->id_soal }}-{{ $optKey }}"
                                     class="opt-check-{{ $soal->id_soal }} w-6 h-6 rounded-full border-2 border-slate-300 flex items-center justify-center text-white transition text-xs shrink-0">
                                    <i class="fas fa-check text-[10px] hidden"></i>
                                </div>
                            </label>
                            @endforeach
                        </div>

                        <!-- Auto-save notification -->
                        <div class="bg-sky-50/60 border border-sky-100 rounded-2xl p-3.5 flex items-center gap-3 text-xs text-[#13527D] font-medium">
                            <div class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-xs shrink-0">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </div>
                            <span>Jawaban yang Anda pilih akan otomatis tersimpan dalam lembar ujian ini.</span>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Tombol Navigasi Bawah (Sebelumnya, Ragu-ragu, Selanjutnya) -->
                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/90 shadow-sm flex items-center justify-between gap-3 mt-4">
                    <button type="button" id="btn-prev" onclick="navigateSoal(-1)"
                            class="px-5 sm:px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                        <i class="fas fa-arrow-left text-[11px]"></i>
                        <span>Sebelumnya</span>
                    </button>

                    <button type="button" id="btn-ragu" onclick="toggleRaguRagu()"
                            class="px-4 sm:px-6 py-3 bg-amber-50 border border-amber-300 text-amber-800 hover:bg-amber-100 rounded-xl text-xs font-bold transition flex items-center gap-2">
                        <i class="fas fa-bookmark text-[11px]"></i>
                        <span id="text-ragu">Ragu-ragu</span>
                    </button>

                    <button type="button" id="btn-next" onclick="navigateSoal(1)"
                            class="px-5 sm:px-7 py-3 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                        <span id="text-next">Selanjutnya</span>
                        <i class="fas fa-arrow-right text-[11px]"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= KOLOM KANAN: SIDEBAR NAVIGASI NOMOR SOAL ================= -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-5 lg:sticky lg:top-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-5">
                <!-- Panel Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fas fa-table-cells-large text-[#13527D]"></i>
                        <span>Daftar Nomor Soal</span>
                    </h3>
                    <span class="text-xs font-bold text-slate-400">Total: {{ count($soals) }}</span>
                </div>

                <!-- Legend Status -->
                <div class="grid grid-cols-3 gap-2 text-[10px] font-bold text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-center">
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-3.5 h-3.5 rounded-md bg-[#13527D] inline-block shadow-2xs"></span>
                        <span>Terjawab</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-3.5 h-3.5 rounded-md bg-amber-400 inline-block shadow-2xs"></span>
                        <span>Ragu-ragu</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-3.5 h-3.5 rounded-md bg-slate-200 inline-block shadow-2xs"></span>
                        <span>Belum</span>
                    </div>
                </div>

                <!-- Matrix Tombol Nomor Soal (5 Kolom Desktop) -->
                <div class="grid grid-cols-5 gap-2.5 max-h-[320px] overflow-y-auto pr-1">
                    @for($i = 0; $i < count($soals); $i++)
                    <button type="button" id="grid-btn-{{ $i }}" onclick="goToSoal({{ $i }})"
                            class="py-3 rounded-xl text-xs font-black transition border {{ $i === 0 ? 'bg-[#13527D] text-white border-[#13527D] ring-2 ring-[#13527D]/30' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200' }}">
                        {{ $i + 1 }}
                    </button>
                    @endfor
                </div>

                <!-- Quick Progress Summary -->
                <div class="space-y-2 pt-2 border-t border-slate-100 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Sudah Dijawab:</span>
                        <strong id="counter-terjawab" class="text-emerald-700 font-black">0 / {{ count($soals) }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Ragu-ragu:</span>
                        <strong id="counter-ragu" class="text-amber-700 font-black">0 Soal</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Belum Dijawab:</span>
                        <strong id="counter-belum" class="text-rose-700 font-black">{{ count($soals) }} Soal</strong>
                    </div>
                </div>

                <!-- Action Button: Selesaikan Kuis Materi -->
                <div class="pt-2">
                    <button type="button" onclick="confirmSubmitExam()"
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl text-xs font-black transition flex items-center justify-center gap-2 shadow-md">
                        <i class="fas fa-check-double text-xs"></i>
                        <span>Selesaikan Kuis Materi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL KONFIRMASI PENGUMPULAN KUIS MATERI ================= -->
<div id="modal-confirm-submit" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md p-6 sm:p-7 space-y-5 shadow-2xl border border-slate-200 transform transition-all text-center">
        <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
            <i class="fas fa-clipboard-check"></i>
        </div>

        <div>
            <h3 class="text-lg font-black text-slate-900">Selesaikan Kuis Materi?</h3>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Pastikan seluruh pertanyaan pemahaman materi telah dijawab. Nilai dan pembahasan butir kuis akan langsung ditampilkan.
            </p>
        </div>

        <!-- Summary Status Box -->
        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs space-y-1.5 text-left">
            <div class="flex justify-between">
                <span class="text-slate-500">Soal Terjawab:</span>
                <span id="modal-sum-terjawab" class="font-bold text-emerald-600">0 Soal</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Soal Ragu-ragu:</span>
                <span id="modal-sum-ragu" class="font-bold text-amber-600">0 Soal</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Soal Belum Terjawab:</span>
                <span id="modal-sum-belum" class="font-bold text-rose-600">0 Soal</span>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="button" onclick="closeConfirmModal()"
                    class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Periksa Lagi
            </button>
            <button type="button" onclick="executeFinalSubmit()"
                    class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition">
                Ya, Selesaikan
            </button>
        </div>
    </div>
</div>

<script>
    const totalSoal = {{ count($soals) }};
    let currentSoalIndex = 0;
    const answeredMap = {};
    const flaggedMap = {};

    function updateView() {
        // Toggle question visibility
        document.querySelectorAll('.soal-slide').forEach((el, idx) => {
            el.classList.toggle('hidden', idx !== currentSoalIndex);
        });

        // Prev button state
        const btnPrev = document.getElementById('btn-prev');
        btnPrev.disabled = (currentSoalIndex === 0);
        btnPrev.classList.toggle('opacity-50', currentSoalIndex === 0);
        btnPrev.classList.toggle('cursor-not-allowed', currentSoalIndex === 0);

        // Next button text
        const textNext = document.getElementById('text-next');
        const btnNext = document.getElementById('btn-next');
        if (currentSoalIndex === totalSoal - 1) {
            textNext.textContent = 'Selesai & Kumpulkan';
            btnNext.className = 'px-5 sm:px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm';
        } else {
            textNext.textContent = 'Selanjutnya';
            btnNext.className = 'px-5 sm:px-7 py-3 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm';
        }

        // Ragu-ragu active state
        const isRagu = flaggedMap[currentSoalIndex] || false;
        const btnRagu = document.getElementById('btn-ragu');
        if (isRagu) {
            btnRagu.className = 'px-4 sm:px-6 py-3 bg-amber-500 text-white rounded-xl text-xs font-black transition flex items-center gap-2 shadow-xs';
        } else {
            btnRagu.className = 'px-4 sm:px-6 py-3 bg-amber-50 border border-amber-300 text-amber-800 hover:bg-amber-100 rounded-xl text-xs font-bold transition flex items-center gap-2';
        }

        updateGridButtons();
        updateCounters();
    }

    function selectOption(soalIdx, idSoal, optKey) {
        answeredMap[soalIdx] = optKey;

        // Reset styling for all choices in this question
        document.querySelectorAll('.opt-label-' + idSoal).forEach(el => {
            el.className = 'opt-label-' + idSoal + ' bg-white hover:bg-slate-50/80 rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs flex items-center justify-between cursor-pointer transition select-none group';
        });
        document.querySelectorAll('.opt-badge-' + idSoal).forEach(el => {
            el.className = 'opt-badge-' + idSoal + ' w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-black text-xs flex items-center justify-center transition shrink-0 group-hover:bg-[#13527D]/10 group-hover:text-[#13527D]';
        });
        document.querySelectorAll('.opt-check-' + idSoal).forEach(el => {
            el.className = 'opt-check-' + idSoal + ' w-6 h-6 rounded-full border-2 border-slate-300 flex items-center justify-center text-white transition text-xs shrink-0';
            const icon = el.querySelector('i');
            if (icon) icon.classList.add('hidden');
        });

        // Set active styling on selected option
        const label = document.getElementById('label-opt-' + idSoal + '-' + optKey);
        const badge = document.getElementById('badge-opt-' + idSoal + '-' + optKey);
        const check = document.getElementById('check-icon-' + idSoal + '-' + optKey);

        if (label) {
            label.className = 'opt-label-' + idSoal + ' bg-sky-50/60 rounded-2xl p-4 sm:p-5 border-2 border-[#13527D] shadow-sm flex items-center justify-between cursor-pointer transition select-none';
        }
        if (badge) {
            badge.className = 'opt-badge-' + idSoal + ' w-9 h-9 rounded-xl bg-[#13527D] text-white font-black text-xs flex items-center justify-center transition shrink-0 shadow-2xs';
        }
        if (check) {
            check.className = 'opt-check-' + idSoal + ' w-6 h-6 rounded-full bg-[#13527D] border-2 border-[#13527D] flex items-center justify-center text-white transition text-xs shrink-0';
            const icon = check.querySelector('i');
            if (icon) icon.classList.remove('hidden');
        }

        // Set status badge in question card
        const statusBadge = document.getElementById('status-badge-' + soalIdx);
        if (statusBadge) {
            if (flaggedMap[soalIdx]) {
                statusBadge.textContent = 'Ragu-ragu (' + optKey + ')';
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300';
            } else {
                statusBadge.textContent = 'Terjawab (' + optKey + ')';
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-black bg-sky-100 text-[#13527D] border border-sky-300';
            }
        }

        // Update radio input value
        const radio = label ? label.querySelector('input[type="radio"]') : null;
        if (radio) {
            radio.checked = true;
        }

        updateGridButtons();
        updateCounters();
    }

    function toggleRaguRagu() {
        flaggedMap[currentSoalIndex] = !flaggedMap[currentSoalIndex];
        const statusBadge = document.getElementById('status-badge-' + currentSoalIndex);
        const ans = answeredMap[currentSoalIndex];

        if (statusBadge) {
            if (flaggedMap[currentSoalIndex]) {
                statusBadge.textContent = ans ? 'Ragu-ragu (' + ans + ')' : 'Ragu-ragu';
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300';
            } else {
                if (ans) {
                    statusBadge.textContent = 'Terjawab (' + ans + ')';
                    statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-black bg-sky-100 text-[#13527D] border border-sky-300';
                } else {
                    statusBadge.textContent = 'Belum Dijawab';
                    statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200';
                }
            }
        }

        updateView();
    }

    function navigateSoal(step) {
        const nextIdx = currentSoalIndex + step;
        if (nextIdx >= 0 && nextIdx < totalSoal) {
            currentSoalIndex = nextIdx;
            updateView();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (nextIdx >= totalSoal) {
            confirmSubmitExam();
        }
    }

    function goToSoal(index) {
        if (index >= 0 && index < totalSoal) {
            currentSoalIndex = index;
            updateView();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function updateGridButtons() {
        for (let i = 0; i < totalSoal; i++) {
            const btn = document.getElementById('grid-btn-' + i);
            if (!btn) continue;

            const isCurrent = (i === currentSoalIndex);
            const isAnswered = !!answeredMap[i];
            const isFlagged = !!flaggedMap[i];

            let classes = 'py-3 rounded-xl text-xs font-black transition border ';

            if (isCurrent) {
                classes += 'ring-2 ring-sky-400 ring-offset-1 ';
            }

            if (isFlagged) {
                classes += 'bg-amber-400 text-slate-900 border-amber-500 shadow-2xs';
            } else if (isAnswered) {
                classes += 'bg-[#13527D] text-white border-[#13527D] shadow-2xs';
            } else {
                classes += 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200';
            }

            btn.className = classes;
        }
    }

    function updateCounters() {
        const totalAnswered = Object.keys(answeredMap).length;
        let totalRagu = 0;
        for (let k in flaggedMap) {
            if (flaggedMap[k]) totalRagu++;
        }
        const totalBelum = totalSoal - totalAnswered;

        document.getElementById('counter-terjawab').textContent = totalAnswered + ' / ' + totalSoal;
        document.getElementById('counter-ragu').textContent = totalRagu + ' Soal';
        document.getElementById('counter-belum').textContent = totalBelum + ' Soal';
    }

    function confirmSubmitExam() {
        const totalAnswered = Object.keys(answeredMap).length;
        let totalRagu = 0;
        for (let k in flaggedMap) {
            if (flaggedMap[k]) totalRagu++;
        }
        const totalBelum = totalSoal - totalAnswered;

        document.getElementById('modal-sum-terjawab').textContent = totalAnswered + ' Soal';
        document.getElementById('modal-sum-ragu').textContent = totalRagu + ' Soal';
        document.getElementById('modal-sum-belum').textContent = totalBelum + ' Soal';

        document.getElementById('modal-confirm-submit').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('modal-confirm-submit').classList.add('hidden');
    }

    function executeFinalSubmit() {
        document.getElementById('form-quiz').submit();
    }

    // ================= REALTIME TIMER COUNTDOWN =================
    const durasiMenit = {{ $durasiMenit }};
    const storageKey = 'binaro_exam_timer_{{ $quiz->id_quiz }}_{{ session("user_id") }}';

    let endTime = localStorage.getItem(storageKey);
    if (!endTime) {
        endTime = new Date().getTime() + (durasiMenit * 60 * 1000);
        localStorage.setItem(storageKey, endTime);
    } else {
        endTime = parseInt(endTime, 10);
    }

    function startTimer() {
        const timerDisplay = document.getElementById('timer-display');

        const interval = setInterval(function () {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance <= 0) {
                clearInterval(interval);
                localStorage.removeItem(storageKey);
                timerDisplay.textContent = '00:00:00';
                alert('Waktu pengerjaan kuis materi telah berakhir! Lembar kuis Anda akan dikumpulkan otomatis.');
                executeFinalSubmit();
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const hh = String(hours).padStart(2, '0');
            const mm = String(minutes).padStart(2, '0');
            const ss = String(seconds).padStart(2, '0');

            timerDisplay.textContent = `${hh}:${mm}:${ss}`;

            // Warn if less than 5 minutes
            if (distance < 5 * 60 * 1000) {
                timerDisplay.parentElement.classList.add('animate-pulse');
                timerDisplay.classList.add('text-amber-300');
            }
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateView();
        startTimer();
    });
</script>
@endsection
