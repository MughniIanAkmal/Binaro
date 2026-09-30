@extends('layouts.siswa')

@section('content')
<div class="max-w-xl mx-auto space-y-4 pb-20">
    <!-- Top Bar with Back & Title (Sesuai Gambar 3) -->
    <div class="bg-[#13527D] -mx-4 -mt-4 sm:mx-0 sm:mt-0 sm:rounded-2xl px-4 py-3.5 text-white shadow-md flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('siswa.ujian.index') }}" onclick="return confirm('Apakah Anda yakin ingin keluar dari lembar ujian? Waktu akan tetap berjalan.');" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-sm font-black tracking-tight truncate max-w-[200px] sm:max-w-xs">
                {{ $quiz->judul_quiz }}
            </h1>
        </div>
        <div class="w-9 h-9 rounded-full border-2 border-white/40 overflow-hidden bg-white/20 flex items-center justify-center">
            <img src="https://api.dicebear.com/7.x/bottts/svg?seed={{ urlencode(session('user_name', 'Siswa')) }}" alt="Avatar" class="w-full h-full object-cover">
        </div>
    </div>

    <!-- Status Bar: Nomor Soal, Timer Countdown, Tombol Nomor Soal (Sesuai Gambar 3) -->
    <div class="flex items-center justify-between px-1">
        <div class="text-xs font-bold text-slate-700">
            Soal <span id="label-current-num" class="text-[#13527D] text-sm font-black">1</span> dari <span class="font-black">{{ count($soals) }}</span>
        </div>

        <!-- Realtime Timer Countdown Badge -->
        <div class="px-3.5 py-1 rounded-full bg-amber-500 text-white font-mono font-black text-xs shadow-xs flex items-center gap-1.5" id="timer-badge">
            <i class="fas fa-clock text-[10px]"></i>
            <span id="timer-display">59:59</span>
        </div>

        <!-- Button Nomor Soal Modal -->
        <button type="button" onclick="openNomorSoalModal()" class="px-2.5 py-1 bg-sky-50 border border-sky-200 text-[#13527D] rounded-xl text-xs font-bold hover:bg-sky-100 flex items-center gap-1.5 transition shadow-2xs">
            <i class="fas fa-table-cells-large text-[11px]"></i>
            <span>Nomor Soal</span>
        </button>
    </div>

    <!-- Progress Bar -->
    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
        <div id="exam-progress-bar" class="bg-[#13527D] h-full transition-all duration-300" style="width: {{ 100 / max(count($soals), 1) }}%;"></div>
    </div>

    <!-- CBT Main Form -->
    <form id="form-quiz" action="{{ route('siswa.quiz.submit', $quiz->id_quiz) }}" method="POST">
        @csrf

        @foreach($soals as $index => $soal)
        <div id="soal-card-{{ $index }}" class="soal-slide space-y-4 {{ $index === 0 ? '' : 'hidden' }}">
            <!-- Question Card (Sesuai Gambar 3) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-[#13527D] border border-sky-100">
                        {{ $quiz->subBab->nama_sub_bab ?? 'Pecahan Sederhana' }}
                    </span>
                    <span class="text-xs font-bold text-slate-400">Poin: 5</span>
                </div>

                <p class="text-sm font-bold text-slate-900 leading-relaxed">
                    {{ $soal->pertanyaan }}
                </p>

                <!-- Optional Visual / Illustration Placeholder (Diagram Pecahan / Soal) -->
                @if(stripos($soal->pertanyaan, 'semangka') !== false || stripos($soal->pertanyaan, 'pecahan') !== false || $index === 0)
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-center space-y-3">
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider block">DIAGRAM PECAHAN 8 POTONG</span>
                    <div class="relative w-36 h-36 mx-auto">
                        <!-- SVG Diagram Pecahan 8 Potong -->
                        <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                            <!-- Background 8 Slices -->
                            <circle cx="50" cy="50" r="40" fill="transparent" stroke="#E2E8F0" stroke-width="25" stroke-dasharray="251.2" stroke-dashoffset="0" />
                            <!-- 3 Slices Eaten (3/8 = 37.5%) -->
                            <circle cx="50" cy="50" r="40" fill="transparent" stroke="#13527D" stroke-width="25" stroke-dasharray="251.2" stroke-dashoffset="157" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-center gap-4 text-[10px] font-semibold text-slate-500">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-[#13527D]"></span> Dimakan Budi (3)</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Sisa Semangka (5)</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Subtitle Pilih satu jawaban -->
            <div class="px-1 text-xs font-bold text-slate-700">
                Pilih satu jawaban yang benar:
            </div>

            <!-- Options Cards (A, B, C, D) (Sesuai Gambar 3) -->
            <div class="space-y-2.5">
                @foreach(['A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c, 'D' => $soal->opsi_d] as $optKey => $optVal)
                <label id="label-opt-{{ $soal->id_soal }}-{{ $optKey }}"
                       onclick="selectOption({{ $index }}, '{{ $soal->id_soal }}', '{{ $optKey }}')"
                       class="opt-label-{{ $soal->id_soal }} bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between cursor-pointer hover:border-[#13527D]/40 transition group select-none">
                    <div class="flex items-center gap-3">
                        <div id="badge-opt-{{ $soal->id_soal }}-{{ $optKey }}"
                             class="opt-badge-{{ $soal->id_soal }} w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-extrabold text-xs flex items-center justify-center transition">
                            {{ $optKey }}
                        </div>
                        <span id="text-opt-{{ $soal->id_soal }}-{{ $optKey }}" class="text-xs font-bold text-slate-800">
                            {{ $optVal }}
                        </span>
                    </div>

                    <input type="radio" name="jawaban[{{ $soal->id_soal }}]" value="{{ $optKey }}" class="hidden">
                    <div id="check-icon-{{ $soal->id_soal }}-{{ $optKey }}"
                         class="opt-check-{{ $soal->id_soal }} w-6 h-6 rounded-full border-2 border-slate-300 flex items-center justify-center text-white transition text-xs">
                        <i class="fas fa-check text-[10px] hidden"></i>
                    </div>
                </label>
                @endforeach
            </div>

            <!-- Hint Box (Sesuai Gambar 3) -->
            <div class="bg-amber-50/60 border border-amber-200/70 rounded-2xl p-3.5 flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-amber-400 text-white flex items-center justify-center text-xs shrink-0 shadow-2xs">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <p class="text-[11px] text-amber-900 font-medium leading-snug">
                    Hebat! Jawabanmu otomatis disimpan saat kamu memilih salah satu opsi.
                </p>
            </div>
        </div>
        @endforeach

        <!-- Navigation Controls: Sebelumnya, Ragu-ragu, Selanjutnya (Sesuai Gambar 3) -->
        <div class="pt-2 flex items-center justify-between gap-2">
            <button type="button" id="btn-prev" onclick="navigateSoal(-1)"
                    class="flex-1 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                <i class="fas fa-arrow-left text-[11px]"></i>
                <span>Sebelumnya</span>
            </button>

            <button type="button" id="btn-ragu" onclick="toggleRaguRagu()"
                    class="py-3 px-4 bg-amber-50 border border-amber-300 text-amber-800 hover:bg-amber-100 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                <i class="fas fa-bookmark text-[11px]"></i>
                <span id="text-ragu">Ragu-ragu</span>
            </button>

            <button type="button" id="btn-next" onclick="navigateSoal(1)"
                    class="flex-1 py-3 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                <span id="text-next">Selanjutnya</span>
                <i class="fas fa-arrow-right text-[11px]"></i>
            </button>
        </div>
    </form>
</div>

<!-- Modal Grid Nomor Soal (Pop-up Nomor Soal) -->
<div id="modal-nomor-soal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-5 space-y-4 shadow-2xl border border-slate-200 transform transition-all">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-table-cells-large text-[#13527D]"></i> Daftar Nomor Soal
            </h3>
            <button type="button" onclick="closeNomorSoalModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Legend -->
        <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-md bg-[#13527D]"></span> Terjawab</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-md bg-amber-400"></span> Ragu-ragu</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-md bg-slate-200"></span> Belum</span>
        </div>

        <!-- Grid Buttons -->
        <div class="grid grid-cols-5 gap-2 max-h-60 overflow-y-auto p-1">
            @for($i = 0; $i < count($soals); $i++)
            <button type="button" id="grid-btn-{{ $i }}" onclick="goToSoal({{ $i }})"
                    class="py-2.5 rounded-xl text-xs font-black transition border {{ $i === 0 ? 'bg-[#13527D] text-white border-[#13527D]' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                {{ $i + 1 }}
            </button>
            @endfor
        </div>

        <button type="button" onclick="closeNomorSoalModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
            Tutup
        </button>
    </div>
</div>

<script>
    const totalSoal = {{ count($soals) }};
    let currentSoalIndex = 0;
    const answeredMap = {};
    const flaggedMap = {};

    function updateView() {
        document.querySelectorAll('.soal-slide').forEach((el, idx) => {
            el.classList.toggle('hidden', idx !== currentSoalIndex);
        });

        document.getElementById('label-current-num').textContent = currentSoalIndex + 1;
        document.getElementById('exam-progress-bar').style.width = ((currentSoalIndex + 1) / totalSoal * 100) + '%';

        // Prev button disable state
        const btnPrev = document.getElementById('btn-prev');
        btnPrev.disabled = (currentSoalIndex === 0);
        btnPrev.classList.toggle('opacity-50', currentSoalIndex === 0);

        // Next or Submit text
        const textNext = document.getElementById('text-next');
        const btnNext = document.getElementById('btn-next');
        if (currentSoalIndex === totalSoal - 1) {
            textNext.textContent = 'Kumpulkan Ujian';
            btnNext.classList.remove('bg-[#13527D]');
            btnNext.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        } else {
            textNext.textContent = 'Selanjutnya';
            btnNext.classList.add('bg-[#13527D]');
            btnNext.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
        }

        // Ragu-ragu active state
        const isRagu = flaggedMap[currentSoalIndex] || false;
        const btnRagu = document.getElementById('btn-ragu');
        if (isRagu) {
            btnRagu.className = 'py-3 px-4 bg-amber-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs';
        } else {
            btnRagu.className = 'py-3 px-4 bg-amber-50 border border-amber-300 text-amber-800 hover:bg-amber-100 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs';
        }
    }

    function selectOption(soalIdx, idSoal, optKey) {
        answeredMap[soalIdx] = optKey;

        // Reset all labels in this question
        document.querySelectorAll('.opt-label-' + idSoal).forEach(el => {
            el.className = 'opt-label-' + idSoal + ' bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between cursor-pointer hover:border-[#13527D]/40 transition group select-none';
        });
        document.querySelectorAll('.opt-badge-' + idSoal).forEach(el => {
            el.className = 'opt-badge-' + idSoal + ' w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-extrabold text-xs flex items-center justify-center transition';
        });
        document.querySelectorAll('.opt-check-' + idSoal).forEach(el => {
            el.className = 'opt-check-' + idSoal + ' w-6 h-6 rounded-full border-2 border-slate-300 flex items-center justify-center text-white transition text-xs';
            el.querySelector('i').classList.add('hidden');
        });

        // Set active for clicked option
        const label = document.getElementById('label-opt-' + idSoal + '-' + optKey);
        const badge = document.getElementById('badge-opt-' + idSoal + '-' + optKey);
        const check = document.getElementById('check-icon-' + idSoal + '-' + optKey);

        label.className = 'opt-label-' + idSoal + ' bg-sky-50/40 rounded-2xl p-4 border-2 border-[#13527D] shadow-sm flex items-center justify-between cursor-pointer transition select-none';
        badge.className = 'opt-badge-' + idSoal + ' w-8 h-8 rounded-xl bg-[#13527D] text-white font-extrabold text-xs flex items-center justify-center transition shadow-2xs';
        check.className = 'opt-check-' + idSoal + ' w-6 h-6 rounded-full bg-[#13527D] border-2 border-[#13527D] flex items-center justify-center text-white transition text-xs';
        check.querySelector('i').classList.remove('hidden');

        // Check radio input
        label.querySelector('input[type="radio"]').checked = true;

        updateGridButtons();
    }

    function toggleRaguRagu() {
        flaggedMap[currentSoalIndex] = !flaggedMap[currentSoalIndex];
        updateView();
        updateGridButtons();
    }

    function navigateSoal(dir) {
        if (dir === 1 && currentSoalIndex === totalSoal - 1) {
            // Confirm submit
            if (confirm('Kumpulkan dan akhiri ujian sekarang? Pastikan Anda sudah memeriksa seluruh jawaban.')) {
                document.getElementById('form-quiz').submit();
            }
            return;
        }

        const target = currentSoalIndex + dir;
        if (target >= 0 && target < totalSoal) {
            currentSoalIndex = target;
            updateView();
        }
    }

    function goToSoal(idx) {
        currentSoalIndex = idx;
        updateView();
        closeNomorSoalModal();
    }

    function updateGridButtons() {
        for (let i = 0; i < totalSoal; i++) {
            const btn = document.getElementById('grid-btn-' + i);
            if (!btn) continue;

            if (flaggedMap[i]) {
                btn.className = 'py-2.5 rounded-xl text-xs font-black transition border bg-amber-400 text-white border-amber-500 shadow-2xs';
            } else if (answeredMap[i]) {
                btn.className = 'py-2.5 rounded-xl text-xs font-black transition border bg-[#13527D] text-white border-[#13527D] shadow-2xs';
            } else {
                btn.className = 'py-2.5 rounded-xl text-xs font-black transition border bg-slate-100 text-slate-700 border-slate-200';
            }
        }
    }

    function openNomorSoalModal() {
        document.getElementById('modal-nomor-soal').classList.remove('hidden');
    }

    function closeNomorSoalModal() {
        document.getElementById('modal-nomor-soal').classList.add('hidden');
    }

    // Realtime Countdown Timer (60 menit = 3600 detik)
    let remainingSeconds = 3600;
    const timerInterval = setInterval(() => {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
            clearInterval(timerInterval);
            alert('Waktu ujian telah berakhir! Lembar jawaban akan dikumpulkan otomatis.');
            document.getElementById('form-quiz').submit();
            return;
        }

        const m = Math.floor(remainingSeconds / 60);
        const s = remainingSeconds % 60;
        document.getElementById('timer-display').textContent = 
            (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }, 1000);

    updateView();
</script>
@endsection
