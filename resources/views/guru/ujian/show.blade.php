@extends('layouts.guru')

@section('page_title', $quiz->judul_quiz)

@section('breadcrumb_trail')
    <a href="{{ route('guru.ujian.index') }}" class="hover:text-slate-600 transition">Tugas & Ujian</a>
    <i class="fas fa-chevron-right text-[8px] opacity-60"></i>
    <span class="text-slate-500">Detail & Bank Soal</span>
@endsection

@section('page_subtitle')
    {{ $quiz->mataPelajaran->nama_mapel ?? 'Semua Mapel' }} · Durasi: {{ $quiz->durasi_menit ?? 60 }} Menit · Dibuat oleh: {{ $quiz->guru->nama_guru ?? 'Guru' }}
@endsection

@section('header_actions')
    @if($quiz->tingkat_level === 'mudah')
        <span class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">Level Mudah</span>
    @elseif($quiz->tingkat_level === 'sedang')
        <span class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">Level Sedang</span>
    @elseif($quiz->tingkat_level === 'susah')
        <span class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap">Level Susah (HOTS)</span>
    @endif
    <a href="{{ route('guru.ujian.edit', $quiz->id_quiz) }}"
       class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition">
        <i class="fas fa-pen-to-square text-[11px]"></i> Edit Ujian
    </a>
    <button onclick="openModal('modal-tambah-soal')"
            class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-[12px] font-bold text-white shadow-sm hover:opacity-90 transition"
            style="background: var(--binaro-navy);">
        <i class="fas fa-plus text-[11px]"></i> Tambah Soal
    </button>
@endsection

@section('content')
<div class="space-y-5 pb-6">
    <!-- Info Banner Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Jumlah Soal</div>
            <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $quiz->soal->count() }} <span class="text-xs font-normal text-slate-500">Soal</span></div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Total Bobot Nilai</div>
                @if($totalBobot >= 100)
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Limit 100 Poin</span>
                @else
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-800">Sisa {{ 100 - $totalBobot }} Poin</span>
                @endif
            </div>
            <div class="text-lg font-bold {{ $totalBobot >= 100 ? 'text-emerald-700' : 'text-[#13527D]' }} mt-0.5">
                {{ $totalBobot }} <span class="text-xs font-semibold text-slate-400">/ 100 Poin</span>
            </div>
            <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                <div class="h-full {{ $totalBobot >= 100 ? 'bg-emerald-600' : 'bg-[#13527D]' }}" style="width: {{ min(100, $totalBobot) }}%"></div>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Durasi & Waktu Per Soal</div>
            <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $quiz->durasi_menit ?? 60 }} <span class="text-xs font-normal text-slate-500">Menit</span></div>
            @php
                $soalCount = $quiz->soal->count();
                $durasiMenit = $quiz->durasi_menit ?? 60;
                $detikPerSoal = $soalCount > 0 ? round(($durasiMenit * 60) / $soalCount) : 0;
                $menitPerSoal = floor($detikPerSoal / 60);
                $sisaDetik = $detikPerSoal % 60;
            @endphp
            <div class="text-[10px] font-semibold text-[#13527D] mt-0.5 flex items-center gap-1">
                <i class="fas fa-stopwatch text-[9px]"></i>
                @if($soalCount > 0)
                    <span>Rata-rata: {{ $menitPerSoal > 0 ? $menitPerSoal . 'm ' : '' }}{{ $sisaDetik > 0 ? $sisaDetik . 's' : '' }} / soal</span>
                @else
                    <span>(Belum ada butir soal)</span>
                @endif
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Sasaran Siswa</div>
            <div class="text-xs font-bold text-slate-800 mt-1.5 truncate">
                @if(($quiz->target_tipe ?? 'semua') === 'semua')
                    <span class="text-blue-600"><i class="fas fa-users mr-1"></i> Semua Siswa</span>
                @else
                    @php
                        $kelasQuiz = $quiz->targetSiswa->map(fn($s) => $s->kelas->pararel ?? $s->kelas->nama_kelas ?? null)->filter()->unique()->values();
                    @endphp
                    @if($kelasQuiz->count() === 1)
                        <span class="text-purple-600" title="{{ $quiz->targetSiswa->pluck('nm_siswa')->join(', ') }}"><i class="fas fa-users mr-1"></i> Siswa kelas {{ $kelasQuiz->first() }}</span>
                    @else
                        <span class="text-purple-600"><i class="fas fa-user-tag mr-1"></i> {{ $quiz->targetSiswa->count() }} Siswa Pilihan</span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if($quiz->deskripsi)
    <div class="bg-blue-50/60 border border-blue-200/70 p-4 rounded-2xl text-xs text-blue-900 flex items-start gap-3">
        <i class="fas fa-circle-info text-blue-500 mt-0.5 text-sm"></i>
        <div>
            <strong class="font-semibold block mb-0.5">Petunjuk Pengerjaan:</strong>
            <p class="text-blue-800/90 leading-relaxed">{{ $quiz->deskripsi }}</p>
        </div>
    </div>
    @endif

    <!-- TABS: Bank Soal & Target Peserta / Hasil -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200">
            <div class="flex items-center gap-4">
                <button type="button" onclick="switchTab('tab-soal')" id="btn-tab-soal" class="py-2.5 px-3 text-xs font-bold border-b-2 border-[#13527D] text-[#13527D] transition flex items-center gap-1.5">
                    <i class="fas fa-list-check"></i>
                    <span>Daftar Soal Ujian ({{ $quiz->soal->count() }})</span>
                </button>
                <button type="button" onclick="switchTab('tab-peserta')" id="btn-tab-peserta" class="py-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5">
                    <i class="fas fa-users"></i>
                    <span>Peserta & Hasil Ujian ({{ $quiz->hasilSiswa->count() }})</span>
                </button>
            </div>

            <button onclick="openModal('modal-tambah-soal')" class="text-xs font-semibold text-[#13527D] hover:underline flex items-center gap-1">
                <i class="fas fa-plus-circle"></i> Tambah Soal Baru
            </button>
        </div>

        <!-- TAB CONTENT: SOAL -->
        <div id="tab-soal" class="space-y-4">
            @forelse($quiz->soal as $index => $soal)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3.5 hover:border-slate-300 transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-[#13527D]/10 text-[#13527D] text-xs font-bold flex items-center justify-center shrink-0">
                            {{ $index + 1 }}
                        </span>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-800 leading-relaxed">
                                {{ $soal->pertanyaan }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
                        @php
                            $soalLevel = strtolower($soal->tingkat_kesulitan ?? 'sedang');
                        @endphp
                        @if($soalLevel === 'mudah')
                            <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                🟢 Mudah
                            </span>
                        @elseif($soalLevel === 'sulit' || $soalLevel === 'susah')
                            <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold">
                                🔴 Sulit
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                🟡 Sedang
                            </span>
                        @endif

                        <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#13527D] border border-blue-200 text-[11px] font-bold">
                            Bobot: {{ $soal->bobot_nilai ?? 10 }} Poin
                        </span>

                        <!-- Tombol Edit Soal -->
                        <button type="button" onclick="editSoal({{ json_encode($soal) }})" class="p-1.5 px-2 text-xs text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition" title="Edit Soal">
                            <i class="fas fa-pen-to-square"></i>
                        </button>

                        <!-- Tombol Hapus Soal -->
                        <form action="{{ route('guru.ujian.soal.destroy', [$quiz->id_quiz, $soal->id_soal]) }}" method="POST" onsubmit="return confirm('Hapus soal nomor {{ $index + 1 }}?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 px-2 text-xs text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition" title="Hapus Soal">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Gambar Pendukung Jika Ada -->
                @if($soal->gambar)
                <div class="pl-10">
                    <div class="inline-block p-1.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <p class="text-[10px] font-semibold text-slate-500 mb-1 flex items-center gap-1">
                            <i class="far fa-image"></i> Gambar Pendukung Soal:
                        </p>
                        <a href="{{ asset('storage/' . $soal->gambar) }}" target="_blank" class="block group relative">
                            <img src="{{ asset('storage/' . $soal->gambar) }}" alt="Gambar Soal" class="max-h-48 rounded-lg object-contain group-hover:opacity-90 transition">
                            <span class="absolute bottom-1 right-1 bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">
                                <i class="fas fa-magnifying-glass-plus"></i> Lihat Penuh
                            </span>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Pilihan Jawaban A, B, C, D -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-10">
                    @foreach(['A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c, 'D' => $soal->opsi_d] as $huruf => $teks)
                    <div class="p-2.5 rounded-xl border text-xs flex items-center justify-between {{ $soal->kunci_jawaban === $huruf ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold shadow-xs' : 'bg-slate-50/70 border-slate-200 text-slate-700' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] {{ $soal->kunci_jawaban === $huruf ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700 font-bold' }}">
                                {{ $huruf }}
                            </span>
                            <span>{{ $teks }}</span>
                        </div>
                        @if($soal->kunci_jawaban === $huruf)
                            <span class="text-[10px] bg-emerald-200/80 text-emerald-800 px-2 py-0.5 rounded-full font-bold flex items-center gap-1">
                                <i class="fas fa-check"></i> Kunci Benar
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Soal di Ujian Ini</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">Tambahkan butir soal pilihan ganda, tentukan bobot nilai masing-masing, dan lampirkan gambar pendukung jika diperlukan.</p>
                <button onclick="openModal('modal-tambah-soal')" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 bg-[#13527D] text-white text-xs font-semibold rounded-xl hover:bg-[#0E3D5D] transition shadow-sm">
                    <i class="fas fa-plus"></i> Tambah Soal Pertama
                </button>
            </div>
            @endforelse
        </div>

        <!-- TAB CONTENT: PESERTA & HASIL -->
        <div id="tab-peserta" class="hidden space-y-4">
            <!-- Sasaran Siswa -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-bullseye text-[#13527D]"></i>
                    Sasaran Peserta Ujian:
                </h3>
                @if(($quiz->target_tipe ?? 'semua') === 'semua')
                    <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-200 text-xs text-blue-900 flex items-center gap-2">
                        <i class="fas fa-users text-blue-600"></i>
                        <span>Ujian ini ditujukan untuk <strong>Seluruh Siswa Aktif</strong> sekolah.</span>
                    </div>
                @else
                    <div class="space-y-2">
                        @php
                            $kelasPeserta = $quiz->targetSiswa->map(fn($s) => $s->kelas->pararel ?? $s->kelas->nama_kelas ?? null)->filter()->unique()->values();
                        @endphp
                        @if($kelasPeserta->count() === 1)
                            <p class="text-xs text-slate-600">Daftar <strong>siswa kelas {{ $kelasPeserta->first() }}</strong> ({{ $quiz->targetSiswa->count() }} siswa) yang ditugaskan mengerjakan ujian ini:</p>
                        @else
                            <p class="text-xs text-slate-600">Daftar {{ $quiz->targetSiswa->count() }} siswa yang ditugaskan mengerjakan ujian ini:</p>
                        @endif
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($quiz->targetSiswa as $ts)
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-[10px]">
                                    {{ substr($ts->nm_siswa, 0, 1) }}
                                </div>
                                <div class="truncate">
                                    <div class="font-bold text-slate-800 truncate">{{ $ts->nm_siswa }}</div>
                                    <div class="text-[10px] text-slate-400">Kelas {{ $ts->kelas->nama_kelas ?? '-' }} &bull; NISN: {{ $ts->nisn ?? '-' }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Hasil Siswa Yang Mengerjakan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                @php
                    $menungguCount = $quiz->hasilSiswa->where('status_kirim', false)->count();
                    $terkirimCount = $quiz->hasilSiswa->where('status_kirim', true)->count();
                @endphp
                <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-slate-50/70 to-white">
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-square-poll-vertical text-emerald-600"></i>
                            Riwayat Pengerjaan Siswa ({{ $quiz->hasilSiswa->count() }})
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                <i class="fas fa-check-double text-[10px]"></i> {{ $terkirimCount }} Nilai Ditampilkan ke Siswa
                            </span>
                            @if($menungguCount > 0)
                                <span class="text-slate-300">&bull;</span>
                                <span class="inline-flex items-center gap-1 font-bold text-amber-600 animate-pulse">
                                    <i class="fas fa-hourglass-half text-[10px]"></i> {{ $menungguCount }} Siswa Menunggu Nilai Dirilis
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if($quiz->hasilSiswa->isNotEmpty())
                        <form action="{{ route('guru.ujian.rilis_nilai_semua', $quiz->id_quiz) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin merilis & menampilkan nilai ujian ini ke SELURUH siswa yang telah mengerjakan? Siswa akan langsung melihat nilai di aplikasi mobile & portal.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm hover:shadow">
                                <i class="fas fa-bullhorn"></i>
                                <span>Tampilkan Nilai ke Seluruh Siswa</span>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('rekap_ujian.index', ['quiz_id' => $quiz->id_quiz]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                            <i class="fas fa-chart-line text-[#13527D]"></i>
                            <span>Rekap Lengkap</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4 text-center">Skor Nilai</th>
                                <th class="py-3 px-4 text-center">Kelulusan</th>
                                <th class="py-3 px-4 text-center">Status di Siswa</th>
                                <th class="py-3 px-4 text-center">Waktu Submit</th>
                                <th class="py-3 px-4 text-center">Aksi Rilis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($quiz->hasilSiswa as $hasil)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $hasil->siswa->nm_siswa ?? 'Siswa' }}
                                    <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $hasil->siswa->nisn ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $hasil->siswa->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-sm text-[#13527D]">
                                    {{ (int)($hasil->nilai_akhir ?? $hasil->skor_nilai ?? $hasil->nilai ?? 0) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $skor = (float)($hasil->nilai_akhir ?? $hasil->skor_nilai ?? $hasil->nilai ?? 0);
                                    @endphp
                                    @if($skor >= 75)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Lulus KKM
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Remedial
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($hasil->status_kirim)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-check-circle text-[9px]"></i> Nilai Ditampilkan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                            <i class="fas fa-clock text-[9px]"></i> Menunggu Nilai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center text-slate-500 text-[11px]">
                                    {{ $hasil->created_at ? $hasil->created_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <form action="{{ route('guru.ujian.toggle_rilis_nilai', $hasil->id_hasil) }}" method="POST" class="inline">
                                        @csrf
                                        @if($hasil->status_kirim)
                                            <button type="submit" class="p-1 px-2.5 text-[10px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition" title="Sembunyikan nilai dari siswa ini">
                                                <i class="fas fa-eye-slash mr-1"></i> Sembunyikan
                                            </button>
                                        @else
                                            <button type="submit" class="p-1 px-2.5 text-[10px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-xs" title="Tampilkan nilai ke siswa ini">
                                                <i class="fas fa-paper-plane mr-1"></i> Tampilkan Nilai
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 italic">
                                    Belum ada siswa yang menyelesaikan ujian online ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH SOAL -->
<div id="modal-tambah-soal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 border border-slate-200 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#13527D]/10 text-[#13527D] flex items-center justify-center font-bold text-xs"><i class="fas fa-plus"></i></span>
                <h3 class="text-sm font-bold text-slate-900">Tambah Soal Ujian Baru</h3>
            </div>
            <button onclick="closeModal('modal-tambah-soal')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>

        <form action="{{ route('guru.ujian.soal.store', $quiz->id_quiz) }}" method="POST" enctype="multipart/form-data" id="form-tambah-soal" class="space-y-4">
            @csrf

            <!-- Info Batas Bobot Soal -->
            <div class="p-3 bg-gradient-to-r from-slate-50 to-sky-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-[#13527D] text-white flex items-center justify-center text-[11px] font-bold">
                        <i class="fas fa-scale-balanced"></i>
                    </span>
                    <span class="text-slate-700">Total Bobot Saat Ini: <strong>{{ $totalBobot }} / 100 Poin</strong></span>
                </div>
                <div>
                    @if($totalBobot >= 100)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Limit 100 Poin Tercapai
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                            Sisa: {{ 100 - $totalBobot }} Poin
                        </span>
                    @endif
                </div>
            </div>

            @if($totalBobot >= 100)
            <div class="p-3 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                <i class="fas fa-triangle-exclamation text-amber-600 mt-0.5 text-sm shrink-0"></i>
                <p>Total bobot ujian sudah mencapai <strong>100 Poin</strong>. Kurangi bobot soal yang sudah ada terlebih dahulu sebelum menambah butir soal baru.</p>
            </div>
            @endif

            <div id="add-modal-bobot-alert" class="hidden p-3 bg-rose-50 border border-rose-300 rounded-xl text-xs text-rose-900 flex items-start gap-2">
                <i class="fas fa-circle-exclamation text-rose-600 mt-0.5 text-sm shrink-0"></i>
                <p id="add-modal-alert-text"></p>
            </div>

            <!-- Bobot Nilai & Tingkat Kesulitan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Bobot Nilai:</span>
                    <div class="flex items-center gap-1.5">
                        <input type="number" id="add-bobot-nilai" name="bobot_nilai" value="{{ min(10, max(1, 100 - $totalBobot)) }}" min="1" max="100" required oninput="handleModalAddBobot(this)" class="w-16 px-2 py-1 text-xs font-bold text-center border border-slate-300 rounded-lg bg-white focus:outline-none focus:border-[#13527D]">
                        <span class="text-xs text-slate-500 font-semibold">Poin</span>
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-2">
                    <span class="text-xs font-bold text-slate-700">Tingkat Kesulitan:</span>
                    <select name="tingkat_kesulitan" class="px-2.5 py-1 text-xs font-bold bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D]">
                        <option value="mudah">🟢 Mudah</option>
                        <option value="sedang" selected>🟡 Sedang</option>
                        <option value="sulit">🔴 Sulit</option>
                    </select>
                </div>
            </div>

            <!-- Teks Pertanyaan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan Soal <span class="text-rose-500">*</span></label>
                <textarea name="pertanyaan" rows="3" required maxlength="2000" placeholder="Tuliskan pertanyaan ujian secara lengkap (maksimal 2000 karakter)..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition"></textarea>
            </div>

            <!-- Upload Gambar Pendukung (Opsional) -->
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    <i class="far fa-image text-slate-400 mr-1"></i> Gambar Pendukung Soal <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input type="file" name="gambar" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" class="text-xs text-slate-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" onchange="previewImage(this, 'preview-modal-add')">
                <div id="preview-modal-add" class="mt-2 hidden">
                    <img src="" alt="Preview Gambar" class="max-h-36 rounded-lg border border-slate-200 object-contain">
                </div>
            </div>

            <!-- Opsi Jawaban A, B, C, D dan Kunci Jawaban -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Pilihan Jawaban & Tandai Kunci Jawaban yang Benar <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach(['A', 'B', 'C', 'D'] as $huruf)
                    <div class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200 focus-within:border-[#13527D] focus-within:bg-white">
                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                            <input type="radio" name="kunci_jawaban" value="{{ $huruf }}" {{ $huruf === 'A' ? 'checked' : '' }} class="text-emerald-600 focus:ring-0">
                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">{{ $huruf }}</span>
                        </label>
                        <input type="text" name="opsi_{{ strtolower($huruf) }}" required maxlength="255" placeholder="Pilihan {{ $huruf }}" class="w-full text-xs border-0 bg-transparent focus:ring-0 p-0 text-slate-800">
                    </div>
                    @endforeach
                </div>
                <p class="text-[10px] text-slate-400 italic">Pilih radio button di samping huruf yang merupakan jawaban yang benar.</p>
            </div>

            <!-- Footer Modal -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-tambah-soal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-xl transition shadow-sm">
                    Simpan Soal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT SOAL -->
<div id="modal-edit-soal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 border border-slate-200 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs"><i class="fas fa-pen-to-square"></i></span>
                <h3 class="text-sm font-bold text-slate-900">Sunting / Edit Soal Ujian</h3>
            </div>
            <button onclick="closeModal('modal-edit-soal')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
        </div>

        <form id="form-edit-soal" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Info Batas Bobot Soal Edit -->
            <div id="edit-modal-bobot-info" class="p-3 bg-gradient-to-r from-slate-50 to-sky-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-amber-500 text-white flex items-center justify-center text-[11px] font-bold">
                        <i class="fas fa-scale-balanced"></i>
                    </span>
                    <span class="text-slate-700">Total Akumulasi: <strong id="edit-modal-total-calc">{{ $totalBobot }}</strong> <strong>/ 100 Poin</strong></span>
                </div>
                <span id="edit-modal-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                    Maks. 100 Poin
                </span>
            </div>
            <div id="edit-modal-bobot-alert" class="hidden p-3 bg-rose-50 border border-rose-300 rounded-xl text-xs text-rose-900 flex items-start gap-2">
                <i class="fas fa-circle-exclamation text-rose-600 mt-0.5 text-base shrink-0"></i>
                <p id="edit-modal-alert-text"></p>
            </div>

            <!-- Bobot Nilai & Tingkat Kesulitan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Bobot Nilai:</span>
                    <div class="flex items-center gap-1.5">
                        <input type="number" id="edit-bobot-nilai" name="bobot_nilai" min="1" max="100" required oninput="handleModalEditBobot(this)" class="w-16 px-2 py-1 text-xs font-bold text-center border border-slate-300 rounded-lg bg-white focus:outline-none focus:border-[#13527D]">
                        <span class="text-xs text-slate-500 font-semibold">Poin</span>
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-2">
                    <span class="text-xs font-bold text-slate-700">Tingkat Kesulitan:</span>
                    <select id="edit-tingkat-kesulitan" name="tingkat_kesulitan" class="px-2.5 py-1 text-xs font-bold bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#13527D]">
                        <option value="mudah">🟢 Mudah</option>
                        <option value="sedang">🟡 Sedang</option>
                        <option value="sulit">🔴 Sulit</option>
                    </select>
                </div>
            </div>

            <!-- Teks Pertanyaan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan Soal <span class="text-rose-500">*</span></label>
                <textarea id="edit-pertanyaan" name="pertanyaan" rows="3" required maxlength="2000" placeholder="Tuliskan pertanyaan ujian secara lengkap (maksimal 2000 karakter)..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition"></textarea>
            </div>

            <!-- Gambar Pendukung -->
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    <i class="far fa-image text-slate-400 mr-1"></i> Gambar Pendukung Soal <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>

                <!-- Gambar Lama Jika Ada -->
                <div id="container-gambar-lama" class="hidden pb-2 border-b border-slate-200">
                    <p class="text-[10px] text-slate-500 mb-1">Gambar saat ini:</p>
                    <div class="flex items-center gap-3">
                        <img id="img-gambar-lama" src="" alt="Gambar Saat Ini" class="max-h-24 rounded-lg border border-slate-200 object-contain">
                        <label class="flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer font-semibold">
                            <input type="checkbox" name="hapus_gambar" value="1" class="text-rose-600 rounded">
                            <span>Hapus gambar ini</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-[11px] text-slate-500 block mb-1">Ganti / Unggah Gambar Baru:</label>
                    <input type="file" name="gambar" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" class="text-xs text-slate-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" onchange="previewImage(this, 'preview-modal-edit')">
                </div>
                <div id="preview-modal-edit" class="mt-2 hidden">
                    <img src="" alt="Preview Gambar Baru" class="max-h-36 rounded-lg border border-slate-200 object-contain">
                </div>
            </div>

            <!-- Opsi Jawaban A, B, C, D dan Kunci Jawaban -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Pilihan Jawaban & Tandai Kunci Jawaban yang Benar <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach(['A', 'B', 'C', 'D'] as $huruf)
                    <div class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200 focus-within:border-[#13527D] focus-within:bg-white">
                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
                            <input type="radio" id="edit-kunci-{{ $huruf }}" name="kunci_jawaban" value="{{ $huruf }}" class="text-emerald-600 focus:ring-0">
                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">{{ $huruf }}</span>
                        </label>
                        <input type="text" id="edit-opsi-{{ strtolower($huruf) }}" name="opsi_{{ strtolower($huruf) }}" required maxlength="255" class="w-full text-xs border-0 bg-transparent focus:ring-0 p-0 text-slate-800">
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-edit-soal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-xl transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const totalBobotUjian = {{ (int) $totalBobot }};
let editingSoalBobotLama = 0;

function switchTab(tabId) {
    document.getElementById('tab-soal').classList.add('hidden');
    document.getElementById('tab-peserta').classList.add('hidden');
    document.getElementById('btn-tab-soal').className = 'py-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5';
    document.getElementById('btn-tab-peserta').className = 'py-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5';

    document.getElementById(tabId).classList.remove('hidden');
    if (tabId === 'tab-soal') {
        document.getElementById('btn-tab-soal').className = 'py-2.5 px-3 text-xs font-bold border-b-2 border-[#13527D] text-[#13527D] transition flex items-center gap-1.5';
    } else {
        document.getElementById('btn-tab-peserta').className = 'py-2.5 px-3 text-xs font-bold border-b-2 border-[#13527D] text-[#13527D] transition flex items-center gap-1.5';
    }
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    if (id === 'modal-tambah-soal') {
        const input = document.getElementById('add-bobot-nilai');
        if (input) handleModalAddBobot(input);
    }
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function handleModalAddBobot(input) {
    try {
        let val = parseFloat(input.value) || 0;
        if (val < 0) { input.value = 0; val = 0; }
        if (val > 100) { input.value = 100; val = 100; }
        const newTotal = totalBobotUjian + val;
        const alertBox = document.getElementById('add-modal-bobot-alert');
        const alertText = document.getElementById('add-modal-alert-text');
        if (newTotal > 100) {
            const kelebihan = newTotal - 100;
            throw new Error(`Total bobot ujian akan menjadi ${newTotal} poin (melebihi limit maksimal 100 poin sebesar ${kelebihan} poin).`);
        } else {
            if (alertBox) alertBox.classList.add('hidden');
        }
    } catch (err) {
        const alertBox = document.getElementById('add-modal-bobot-alert');
        const alertText = document.getElementById('add-modal-alert-text');
        if (alertBox && alertText) {
            alertText.textContent = err.message;
            alertBox.classList.remove('hidden');
        }
    }
}

function handleModalEditBobot(input) {
    try {
        let val = parseFloat(input.value) || 0;
        if (val < 0) { input.value = 0; val = 0; }
        if (val > 100) { input.value = 100; val = 100; }
        const otherTotal = Math.max(0, totalBobotUjian - editingSoalBobotLama);
        const newTotal = otherTotal + val;

        const totalCalc = document.getElementById('edit-modal-total-calc');
        const statusBadge = document.getElementById('edit-modal-status-badge');
        const alertBox = document.getElementById('edit-modal-bobot-alert');
        const alertText = document.getElementById('edit-modal-alert-text');

        if (totalCalc) totalCalc.textContent = newTotal;

        if (newTotal > 100) {
            const kelebihan = newTotal - 100;
            if (statusBadge) {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200';
                statusBadge.textContent = 'Melebihi Limit';
            }
            throw new Error(`Total bobot soal akan menjadi ${newTotal} poin (melebihi batas maksimal 100 poin sebesar ${kelebihan} poin).`);
        } else if (newTotal === 100) {
            if (statusBadge) {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
                statusBadge.textContent = 'Limit 100 Poin Tercapai';
            }
            if (alertBox) alertBox.classList.add('hidden');
        } else {
            if (statusBadge) {
                statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700';
                statusBadge.textContent = `Sisa ${100 - newTotal} Poin`;
            }
            if (alertBox) alertBox.classList.add('hidden');
        }
    } catch (err) {
        const alertBox = document.getElementById('edit-modal-bobot-alert');
        const alertText = document.getElementById('edit-modal-alert-text');
        if (alertBox && alertText) {
            alertText.textContent = err.message;
            alertBox.classList.remove('hidden');
        }
    }
}

function editSoal(soal) {
    const form = document.getElementById('form-edit-soal');
    form.action = `{{ url('guru/ujian/' . $quiz->id_quiz . '/soal') }}/${soal.id_soal}`;

    editingSoalBobotLama = parseInt(soal.bobot_nilai) || 10;
    const bobotInput = document.getElementById('edit-bobot-nilai');
    bobotInput.value = editingSoalBobotLama;

    const kes = (soal.tingkat_kesulitan || 'sedang').toLowerCase();
    document.getElementById('edit-tingkat-kesulitan').value = (kes === 'susah' ? 'sulit' : kes);
    document.getElementById('edit-pertanyaan').value = soal.pertanyaan || '';
    document.getElementById('edit-opsi-a').value = soal.opsi_a || '';
    document.getElementById('edit-opsi-b').value = soal.opsi_b || '';
    document.getElementById('edit-opsi-c').value = soal.opsi_c || '';
    document.getElementById('edit-opsi-d').value = soal.opsi_d || '';

    const kunci = (soal.kunci_jawaban || 'A').toUpperCase();
    const radioKunci = document.getElementById(`edit-kunci-${kunci}`);
    if (radioKunci) radioKunci.checked = true;

    // Handle gambar lama
    const containerGambarLama = document.getElementById('container-gambar-lama');
    const imgGambarLama = document.getElementById('img-gambar-lama');
    if (soal.gambar) {
        containerGambarLama.classList.remove('hidden');
        imgGambarLama.src = `{{ asset('storage') }}/${soal.gambar}`;
    } else {
        containerGambarLama.classList.add('hidden');
        imgGambarLama.src = '';
    }

    handleModalEditBobot(bobotInput);
    openModal('modal-edit-soal');
}

function previewImage(input, previewId) {
    const previewContainer = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = previewContainer.querySelector('img');
            img.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const formTambah = document.getElementById('form-tambah-soal');
    if (formTambah) {
        formTambah.addEventListener('submit', function(e) {
            try {
                const input = document.getElementById('add-bobot-nilai');
                const val = parseFloat(input ? input.value : 0) || 0;
                if (totalBobotUjian + val > 100) {
                    throw new Error(`Total bobot ujian (${totalBobotUjian + val} poin) melebihi batas maksimal 100 poin! Harap sesuaikan bobot soal.`);
                }
            } catch (err) {
                e.preventDefault();
                alert(err.message);
                return false;
            }
        });
    }

    const formEdit = document.getElementById('form-edit-soal');
    if (formEdit) {
        formEdit.addEventListener('submit', function(e) {
            try {
                const input = document.getElementById('edit-bobot-nilai');
                const val = parseFloat(input ? input.value : 0) || 0;
                const otherTotal = Math.max(0, totalBobotUjian - editingSoalBobotLama);
                if (otherTotal + val > 100) {
                    throw new Error(`Total bobot ujian (${otherTotal + val} poin) melebihi batas maksimal 100 poin! Harap sesuaikan bobot soal.`);
                }
            } catch (err) {
                e.preventDefault();
                alert(err.message);
                return false;
            }
        });
    }
});
</script>
@endsection
