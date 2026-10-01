@extends('layouts.siswa')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-16">
    <!-- Clean Page Header (Tanpa Banner Biru Besar) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-[#13527D] text-white flex items-center justify-center text-sm shadow-xs">
                    <i class="fas fa-clipboard-question"></i>
                </span>
                <span>Ujian Online Siswa</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Daftar evaluasi dan ujian kompetensi yang dapat dikerjakan.</p>
        </div>
    </div>

    <!-- Filter & Navigation Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Tabs -->
        <div class="flex gap-2 w-full sm:w-auto">
            <button type="button" onclick="switchUjianTab('tersedia')" id="tab-btn-tersedia"
                    class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#13527D] text-white shadow-xs text-xs font-black flex items-center justify-center gap-2.5 transition">
                <i class="fas fa-clipboard-list text-sm"></i>
                <span>Ujian Tersedia</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 font-black">
                    {{ $ujianTersedia->count() }}
                </span>
            </button>
            <button type="button" onclick="switchUjianTab('selesai')" id="tab-btn-selesai"
                    class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200/70 text-xs font-bold flex items-center justify-center gap-2.5 transition">
                <i class="fas fa-circle-check text-sm"></i>
                <span>Riwayat Selesai</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-700 font-black">
                    {{ $ujianSelesai->count() }}
                </span>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full sm:w-72">
            <input type="text" id="search-ujian-input" onkeyup="filterUjianCards()" placeholder="Cari nama ujian atau mapel..."
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-[#13527D] focus:border-transparent outline-none transition">
            <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
        </div>
    </div>

    <!-- ================= TAB 1: UJIAN TERSEDIA ================= -->
    <div id="content-tersedia">
        @if($ujianTersedia->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="grid-tersedia">
            @foreach($ujianTersedia as $quiz)
            @php
                $mapelName = $quiz->mataPelajaran->nama_mapel ?? ($quiz->subBab->bab->mataPelajaran->nama_mapel ?? 'Mata Pelajaran Umum');
                $guruName = $quiz->guru->nama_guru ?? ($quiz->mataPelajaran->guru->nama_guru ?? ($quiz->subBab->bab->mataPelajaran->guru->nama_guru ?? 'Guru Pengampu'));
                $totalSoal = $quiz->soal->count();
                $durasi = $quiz->durasi_menit ?? 60;
                $level = strtolower($quiz->tingkat_level ?? 'sedang');
            @endphp
            <div class="ujian-card bg-white rounded-2xl p-6 border border-slate-200/90 shadow-sm hover:shadow-md hover:border-[#13527D]/40 transition flex flex-col justify-between group"
                 data-title="{{ strtolower($quiz->judul_quiz) }}" data-mapel="{{ strtolower($mapelName) }}">
                <div class="space-y-4">
                    <!-- Badges -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-[#13527D] border border-sky-100 flex items-center gap-1.5 truncate max-w-[180px]">
                            <i class="fas fa-book-open text-[10px]"></i> {{ $mapelName }}
                        </span>

                        @if($level === 'mudah')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Level Mudah
                            </span>
                        @elseif($level === 'susah')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Level Sulit
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Level Sedang
                            </span>
                        @endif
                    </div>

                    <!-- Judul & Guru -->
                    <div>
                        <h3 class="text-base font-black text-slate-900 group-hover:text-[#13527D] transition leading-snug line-clamp-2">
                            {{ $quiz->judul_quiz }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1.5">
                            <i class="fas fa-chalkboard-user text-slate-400"></i>
                            <span class="truncate">Guru: {{ $guruName }}</span>
                        </p>
                        @if($quiz->deskripsi)
                        <p class="text-xs text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                            {{ $quiz->deskripsi }}
                        </p>
                        @endif
                    </div>

                    <!-- Meta Info Box (Waktu, Soal, Status) -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-50/80 p-3 rounded-xl border border-slate-100 text-xs font-bold text-slate-600">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-sky-100/70 text-sky-700 flex items-center justify-center text-xs">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Durasi</span>
                                <span>{{ $durasi }} Menit</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100/70 text-indigo-700 flex items-center justify-center text-xs">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Jumlah Soal</span>
                                <span>{{ $totalSoal }} Butir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button: Open Petunjuk Modal -->
                <div class="pt-5 border-t border-slate-100 mt-4">
                    <button type="button"
                            onclick="openPetunjukModal({
                                id: {{ $quiz->id_quiz }},
                                judul: '{{ addslashes($quiz->judul_quiz) }}',
                                mapel: '{{ addslashes($mapelName) }}',
                                guru: '{{ addslashes($guruName) }}',
                                durasi: {{ $durasi }},
                                totalSoal: {{ $totalSoal }},
                                level: '{{ ucfirst($level) }}',
                                playUrl: '{{ route('siswa.ujian.play', $quiz->id_quiz) }}'
                            })"
                            class="w-full py-3 bg-[#13527D] hover:bg-[#0E3D5D] active:scale-[0.99] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-play text-[10px]"></i>
                        <span>Mulai Ujian</span>
                        <i class="fas fa-arrow-right text-[11px] ml-1"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State Tersedia -->
        <div class="bg-white rounded-3xl p-12 border border-slate-200 text-center max-w-xl mx-auto space-y-4 shadow-xs">
            <div class="w-20 h-20 rounded-3xl bg-sky-50 text-[#13527D] flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <h3 class="text-base font-black text-slate-900">Belum Ada Ujian Online Tersedia</h3>
            <p class="text-xs text-slate-500 leading-relaxed max-w-md mx-auto">
                Saat ini belum ada ujian baru yang dijadwalkan oleh guru untuk Anda. Periksa kembali jadwal pembelajaran atau lihat materi yang telah dipelajari.
            </p>
            <div class="pt-2">
                <a href="{{ route('siswa.mapel.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    <i class="fas fa-book-open"></i> Pelajari Materi
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- ================= TAB 2: UJIAN SELESAI ================= -->
    <div id="content-selesai" class="hidden">
        @if($ujianSelesai->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="grid-selesai">
            @foreach($ujianSelesai as $quiz)
            @php
                $mapelName = $quiz->mataPelajaran->nama_mapel ?? ($quiz->subBab->bab->mataPelajaran->nama_mapel ?? 'Mata Pelajaran');
                $guruName = $quiz->guru->nama_guru ?? ($quiz->mataPelajaran->guru->nama_guru ?? 'Guru Pengampu');
                $hasil = $quiz->hasilSiswa->first();
                $nilai = $hasil ? $hasil->nilai_akhir : 0;
                $isLulus = $nilai >= 75;
                $isPublished = (bool)($hasil?->status_kirim ?? false);
            @endphp
            <div class="ujian-card bg-white rounded-2xl p-6 border border-slate-200/90 shadow-sm flex flex-col justify-between"
                 data-title="{{ strtolower($quiz->judul_quiz) }}" data-mapel="{{ strtolower($mapelName) }}">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-[#13527D] border border-sky-100">
                            {{ $mapelName }}
                        </span>
                        @if($isPublished)
                            @if($isLulus)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Lulus KKM
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200">
                                Remedial
                            </span>
                            @endif
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1">
                                <i class="fas fa-clock text-[9px]"></i> Menunggu Rilis Nilai
                            </span>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-base font-black text-slate-900 leading-snug">{{ $quiz->judul_quiz }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Guru: {{ $guruName }}</p>
                    </div>

                    <!-- Score Card Mini -->
                    @if($isPublished)
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600">Nilai Akhir Ujian:</span>
                        <span class="text-lg font-black {{ $isLulus ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ round($nilai) }} <span class="text-xs text-slate-400 font-medium">/ 100</span>
                        </span>
                    </div>
                    @else
                    <div class="bg-amber-50/70 rounded-xl p-3 border border-amber-200/80 flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs shrink-0">
                            <i class="fas fa-lock"></i>
                        </div>
                        <span class="text-xs text-amber-900 font-medium">Nilai belum dirilis oleh guru pengampu.</span>
                    </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-slate-100 mt-4">
                    <a href="{{ route('siswa.ujian.result', $quiz->id_quiz) }}"
                       class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-[#13527D] rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                        <i class="fas fa-eye text-xs"></i>
                        <span>{{ $isPublished ? 'Lihat Hasil & Evaluasi' : 'Lihat Status Ujian' }}</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-3xl p-12 border border-slate-200 text-center max-w-xl mx-auto space-y-3 shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto">
                <i class="fas fa-hourglass-start"></i>
            </div>
            <h3 class="text-base font-black text-slate-800">Belum Ada Riwayat Ujian</h3>
            <p class="text-xs text-slate-500">Anda belum pernah menyelesaikan ujian online di platform ini.</p>
        </div>
        @endif
    </div>

    <!-- Section Nilai Ujian Terakhir (Desktop Card Banner) -->
    @if($latestHasil && $latestHasil->quiz)
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-500 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                <i class="fas fa-award"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-100">
                        {{ $latestHasil->quiz->mataPelajaran->nama_mapel ?? ($latestHasil->quiz->subBab->bab->mataPelajaran->nama_mapel ?? 'Mata Pelajaran') }}
                    </span>
                    <span class="text-xs text-slate-400 font-medium">Ujian Terakhir Dikerjakan</span>
                </div>
                <h4 class="text-sm sm:text-base font-black text-slate-900 mt-1">
                    {{ $latestHasil->quiz->judul_quiz }}
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    Diselesaikan pada {{ $latestHasil->created_at->format('d M Y, H:i') }} WIB &bull; Benar: {{ $latestHasil->jumlah_benar }}, Salah: {{ $latestHasil->jumlah_salah }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 shrink-0 w-full md:w-auto justify-between md:justify-end">
            <div class="text-right">
                <span class="text-[10px] uppercase font-extrabold text-slate-400 block tracking-wider">Skor Kamu</span>
                <span class="text-2xl font-black text-[#13527D]">{{ round($latestHasil->nilai_akhir) }}</span>
                <span class="text-xs font-bold text-slate-400">/ 100</span>
            </div>
            <a href="{{ route('siswa.ujian.result', $latestHasil->id_quiz) }}"
               class="px-5 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs">
                <span>Detail Hasil</span>
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        </div>
    </div>
    @endif
</div>

<!-- ================= MODAL POP-UP: PETUNJUK UJIAN ================= -->
<div id="modal-petunjuk-ujian" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-2xl p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 transform transition-all relative">
        <!-- Close Button -->
        <button type="button" onclick="closePetunjukModal()" class="absolute top-5 right-5 w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
            <i class="fas fa-times text-sm"></i>
        </button>

        <!-- Header Modal -->
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#13527D] text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                <i class="fas fa-file-signature"></i>
            </div>
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-sky-50 text-[#13527D] border border-sky-100">
                    Konfirmasi Pelaksanaan Ujian
                </span>
                <h3 id="modal-ujian-judul" class="text-lg font-black text-slate-900 mt-1 leading-tight">
                    Judul Ujian Online
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                    <span id="modal-ujian-mapel" class="font-bold text-slate-700">Matematika</span> &bull;
                    <span id="modal-ujian-guru">Guru Pengampu</span>
                </p>
            </div>
        </div>

        <!-- 4 Grid Ringkasan Petunjuk (Waktu, Durasi, Kesusahan Soal, Jumlah Soal) Sesuai Permintaan User -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- 1. Waktu / Status -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center space-y-1">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mx-auto text-xs">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Waktu</span>
                <span class="text-xs font-black text-slate-800">Hari Ini</span>
            </div>

            <!-- 2. Durasi -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center space-y-1">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto text-xs">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Durasi</span>
                <span id="modal-ujian-durasi" class="text-xs font-black text-slate-800">60 Menit</span>
            </div>

            <!-- 3. Kesusahan Soal -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center space-y-1">
                <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center mx-auto text-xs">
                    <i class="fas fa-gauge-high"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Tingkat Soal</span>
                <span id="modal-ujian-level" class="text-xs font-black text-purple-700">Sedang</span>
            </div>

            <!-- 4. Jumlah Soal -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-center space-y-1">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto text-xs">
                    <i class="fas fa-list-ol"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400 block uppercase">Jumlah Soal</span>
                <span id="modal-ujian-jumlah" class="text-xs font-black text-slate-800">20 Soal</span>
            </div>
        </div>

        <!-- Tata Tertib & Petunjuk Pengerjaan -->
        <div class="bg-sky-50/70 border border-sky-100 rounded-2xl p-4 space-y-2">
            <h4 class="text-xs font-black text-[#13527D] flex items-center gap-1.5">
                <i class="fas fa-circle-info"></i> Petunjuk & Peraturan Ujian:
            </h4>
            <ul class="text-xs text-slate-700 space-y-1.5 list-disc list-inside">
                <li>Pastikan koneksi internet Anda stabil sebelum menekan tombol <strong>Mulai Kerjakan</strong>.</li>
                <li>Waktu pengerjaan akan otomatis berjalan mundur saat lembar ujian dibuka.</li>
                <li>Jawaban yang dipilih akan otomatis tersimpan oleh sistem secara real-time.</li>
                <li>Dilarang membuka tab browser lain atau bekerja sama dengan orang lain selama ujian berlangsung.</li>
                <li>Jika waktu habis, jawaban yang telah dipilih akan dikumpulkan otomatis.</li>
            </ul>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" onclick="closePetunjukModal()"
                    class="px-5 py-3 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition">
                Batal
            </button>
            <a id="modal-ujian-btn-mulai" href="#"
               class="px-6 py-3 rounded-xl bg-[#13527D] hover:bg-[#0E3D5D] active:scale-[0.99] text-white text-xs font-black shadow-md flex items-center gap-2 transition">
                <span>Mulai Kerjakan Sekarang</span>
                <i class="fas fa-arrow-right text-[11px]"></i>
            </a>
        </div>
    </div>
</div>

<script>
    function switchUjianTab(tab) {
        const cTersedia = document.getElementById('content-tersedia');
        const cSelesai = document.getElementById('content-selesai');
        const btnTersedia = document.getElementById('tab-btn-tersedia');
        const btnSelesai = document.getElementById('tab-btn-selesai');

        if (tab === 'tersedia') {
            cTersedia.classList.remove('hidden');
            cSelesai.classList.add('hidden');
            btnTersedia.className = 'flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#13527D] text-white shadow-xs text-xs font-black flex items-center justify-center gap-2.5 transition';
            btnSelesai.className = 'flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200/70 text-xs font-bold flex items-center justify-center gap-2.5 transition';
        } else {
            cTersedia.classList.add('hidden');
            cSelesai.classList.remove('hidden');
            btnSelesai.className = 'flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-[#13527D] text-white shadow-xs text-xs font-black flex items-center justify-center gap-2.5 transition';
            btnTersedia.className = 'flex-1 sm:flex-initial px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200/70 text-xs font-bold flex items-center justify-center gap-2.5 transition';
        }
    }

    function openPetunjukModal(data) {
        document.getElementById('modal-ujian-judul').textContent = data.judul;
        document.getElementById('modal-ujian-mapel').textContent = data.mapel;
        document.getElementById('modal-ujian-guru').textContent = 'Guru: ' + data.guru;
        document.getElementById('modal-ujian-durasi').textContent = data.durasi + ' Menit';
        document.getElementById('modal-ujian-jumlah').textContent = data.totalSoal + ' Soal';
        document.getElementById('modal-ujian-level').textContent = data.level;
        document.getElementById('modal-ujian-btn-mulai').href = data.playUrl;

        document.getElementById('modal-petunjuk-ujian').classList.remove('hidden');
    }

    function closePetunjukModal() {
        document.getElementById('modal-petunjuk-ujian').classList.add('hidden');
    }

    // Close on background click
    document.getElementById('modal-petunjuk-ujian')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closePetunjukModal();
        }
    });

    function filterUjianCards() {
        const query = document.getElementById('search-ujian-input').value.toLowerCase();
        document.querySelectorAll('.ujian-card').forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const mapel = card.getAttribute('data-mapel') || '';
            if (title.includes(query) || mapel.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endsection
