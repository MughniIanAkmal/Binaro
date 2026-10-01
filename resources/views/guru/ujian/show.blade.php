@extends('layouts.guru')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.ujian.index') }}" class="hover:text-[#13527D] transition">Ujian Online</a>
                <span>/</span>
                <span class="text-[#13527D] font-semibold">Detail & Bank Soal</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900">{{ $quiz->judul_quiz }}</h1>
                @if($quiz->tingkat_level === 'mudah')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        🟢 Level Mudah
                    </span>
                @elseif($quiz->tingkat_level === 'sedang')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        🟡 Level Sedang
                    </span>
                @elseif($quiz->tingkat_level === 'susah')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        🔴 Level Susah (HOTS)
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $quiz->mataPelajaran->nama_mapel ?? 'Semua Mapel' }} &bull; Durasi: {{ $quiz->durasi_menit ?? 60 }} Menit &bull; Dibuat oleh: {{ $quiz->guru->nama_guru ?? 'Guru' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('guru.ujian.edit', $quiz->id_quiz) }}" class="px-3.5 py-2 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition shadow-sm flex items-center gap-1.5">
                <i class="fas fa-pen-to-square"></i> Edit Ujian
            </a>
            <button onclick="openModal('modal-tambah-soal')" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-xl transition shadow-sm flex items-center gap-1.5">
                <i class="fas fa-plus"></i> Tambah Soal
            </button>
        </div>
    </div>

    <!-- Info Banner Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Jumlah Soal</div>
            <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $quiz->soal->count() }} <span class="text-xs font-normal text-slate-500">Soal</span></div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Total Bobot Nilai</div>
            <div class="text-lg font-bold text-[#13527D] mt-0.5">{{ $totalBobot }} <span class="text-xs font-normal text-slate-500">Poin</span></div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Durasi Pengerjaan</div>
            <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $quiz->durasi_menit ?? 60 }} <span class="text-xs font-normal text-slate-500">Menit</span></div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Sasaran Siswa</div>
            <div class="text-xs font-bold text-slate-800 mt-1.5 truncate">
                @if(($quiz->target_tipe ?? 'semua') === 'semua')
                    <span class="text-blue-600"><i class="fas fa-users mr-1"></i> Semua Siswa</span>
                @else
                    <span class="text-purple-600"><i class="fas fa-user-tag mr-1"></i> {{ $quiz->targetSiswa->count() }} Siswa Pilihan</span>
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

                    <div class="flex items-center gap-2 shrink-0">
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
                        <p class="text-xs text-slate-600">Daftar {{ $quiz->targetSiswa->count() }} siswa yang ditugaskan mengerjakan ujian ini:</p>
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
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-square-poll-vertical text-emerald-600"></i>
                        Riwayat Pengerjaan Siswa ({{ $quiz->hasilSiswa->count() }})
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4 text-center">Skor Nilai</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Waktu Submit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($quiz->hasilSiswa as $hasil)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $hasil->siswa->nm_siswa ?? 'Siswa' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $hasil->siswa->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-sm text-[#13527D]">
                                    {{ $hasil->nilai_akhir ?? $hasil->skor_nilai ?? $hasil->nilai ?? 0 }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $skor = $hasil->nilai_akhir ?? $hasil->skor_nilai ?? $hasil->nilai ?? 0;
                                    @endphp
                                    @if($skor >= 75)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Lulus
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Remedial
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center text-slate-500 text-[11px]">
                                    {{ $hasil->created_at ? $hasil->created_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 italic">
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

        <form action="{{ route('guru.ujian.soal.store', $quiz->id_quiz) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Bobot Nilai & Info -->
            <div class="flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-200">
                <span class="text-xs font-bold text-slate-700">Tentukan Bobot Nilai Soal Ini:</span>
                <div class="flex items-center gap-1.5">
                    <input type="number" name="bobot_nilai" value="10" min="1" max="100" required class="w-20 px-2 py-1 text-xs font-bold text-center border border-slate-300 rounded-lg bg-white focus:outline-none focus:border-[#13527D]">
                    <span class="text-xs text-slate-500 font-semibold">Poin</span>
                </div>
            </div>

            <!-- Teks Pertanyaan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan Soal <span class="text-rose-500">*</span></label>
                <textarea name="pertanyaan" rows="3" required placeholder="Tuliskan pertanyaan ujian secara lengkap..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition"></textarea>
            </div>

            <!-- Upload Gambar Pendukung (Opsional) -->
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    <i class="far fa-image text-slate-400 mr-1"></i> Gambar Pendukung Soal <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input type="file" name="gambar" accept="image/*" class="text-xs text-slate-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" onchange="previewImage(this, 'preview-modal-add')">
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
                        <input type="text" name="opsi_{{ strtolower($huruf) }}" required placeholder="Pilihan {{ $huruf }}" class="w-full text-xs border-0 bg-transparent focus:ring-0 p-0 text-slate-800">
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

            <!-- Bobot Nilai -->
            <div class="flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-200">
                <span class="text-xs font-bold text-slate-700">Bobot Nilai Soal Ini:</span>
                <div class="flex items-center gap-1.5">
                    <input type="number" id="edit-bobot-nilai" name="bobot_nilai" min="1" max="100" required class="w-20 px-2 py-1 text-xs font-bold text-center border border-slate-300 rounded-lg bg-white focus:outline-none focus:border-[#13527D]">
                    <span class="text-xs text-slate-500 font-semibold">Poin</span>
                </div>
            </div>

            <!-- Teks Pertanyaan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan Soal <span class="text-rose-500">*</span></label>
                <textarea id="edit-pertanyaan" name="pertanyaan" rows="3" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition"></textarea>
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
                    <input type="file" name="gambar" accept="image/*" class="text-xs text-slate-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-white file:text-slate-700 hover:file:bg-slate-100 cursor-pointer" onchange="previewImage(this, 'preview-modal-edit')">
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
                        <input type="text" id="edit-opsi-{{ strtolower($huruf) }}" name="opsi_{{ strtolower($huruf) }}" required class="w-full text-xs border-0 bg-transparent focus:ring-0 p-0 text-slate-800">
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
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function editSoal(soal) {
    const form = document.getElementById('form-edit-soal');
    form.action = `{{ url('guru/ujian/' . $quiz->id_quiz . '/soal') }}/${soal.id_soal}`;

    document.getElementById('edit-bobot-nilai').value = soal.bobot_nilai || 10;
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
</script>
@endsection
