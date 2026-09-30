<!-- Modal Action Pembelajaran (Tambah / Edit) PRD 4.2 -->
<div id="modal-learning-action" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Modal Header with Mode Tabs -->
        <div class="bg-[#13527D] text-white p-5 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold flex items-center gap-2" id="modal-action-title">
                    <i class="fas fa-layer-group text-sky-300"></i> Kelola Materi & Silabus
                </h3>
                <p class="text-xs text-sky-100 mt-0.5" id="modal-action-subtitle">
                    SDN Kalitapen 01 &bull; Modul Guru
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Mode Switcher -->
                <div class="bg-[#0E3D5D] p-1 rounded-xl flex items-center text-xs">
                    <button type="button" id="tab-btn-tambah" onclick="switchActionTab('tambah')" class="px-3 py-1.5 rounded-lg font-bold bg-white text-[#13527D] shadow-sm transition">
                        <i class="fas fa-plus mr-1"></i> Tambah
                    </button>
                    <button type="button" id="tab-btn-edit" onclick="switchActionTab('edit')" class="px-3 py-1.5 rounded-lg font-semibold text-white/80 hover:text-white transition">
                        <i class="fas fa-pen-to-square mr-1"></i> Edit
                    </button>
                </div>
                <button type="button" onclick="closeModalAction()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">

            <!-- ================= TAB 1: TAMBAH ================= -->
            <div id="tab-content-tambah" class="space-y-5">
                <!-- Target Selector: Bab, Sub-Bab, Materi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Target yang Ingin Ditambahkan:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="setTambahTarget('bab')" id="target-btn-bab" class="py-2.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:border-[#13527D] transition text-center flex flex-col items-center gap-1">
                            <i class="fas fa-folder text-base text-[#13527D]"></i>
                            <span>1. Bab Baru</span>
                        </button>
                        <button type="button" onclick="setTambahTarget('sub_bab')" id="target-btn-sub_bab" class="py-2.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:border-[#13527D] transition text-center flex flex-col items-center gap-1">
                            <i class="fas fa-folder-tree text-base text-sky-600"></i>
                            <span>2. Sub-Bab</span>
                        </button>
                        <button type="button" onclick="setTambahTarget('materi')" id="target-btn-materi" class="py-2.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:border-[#13527D] transition text-center flex flex-col items-center gap-1">
                            <i class="fas fa-file-circle-plus text-base text-emerald-600"></i>
                            <span>3. Konten Materi</span>
                        </button>
                    </div>
                </div>

                <!-- Form Tambah Bab -->
                <form id="form-tambah-bab" action="{{ route('guru.materi.store.bab') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="id_mapel" id="tambah-bab-mapel" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach(\App\Models\MataPelajaran::all() as $m)
                                <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bab <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_bab" required placeholder="Contoh: Bab 1: Operasi Hitung Bilangan Cacah" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                        <p class="text-[10px] text-slate-400 mt-1">Nama Bab tidak boleh kembar dalam satu mata pelajaran.</p>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl shadow-sm transition">
                            Simpan Bab
                        </button>
                    </div>
                </form>

                <!-- Form Tambah Sub-Bab -->
                <form id="form-tambah-sub_bab" action="{{ route('guru.materi.store.sub-bab') }}" method="POST" class="space-y-4 hidden">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select id="tambah-subbab-mapel" onchange="loadBabsForSelect(this.value, 'tambah-subbab-bab')" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach(\App\Models\MataPelajaran::all() as $m)
                                <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Bab Induk <span class="text-rose-500">*</span></label>
                        <select name="id_bab" id="tambah-subbab-bab" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                            <option value="">-- Pilih Mapel Terlebih Dahulu --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sub-Bab <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_sub_bab" required placeholder="Contoh: Sub-Bab A: Penjumlahan Ribuan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl shadow-sm transition">
                            Simpan Sub-Bab
                        </button>
                    </div>
                </form>

                <!-- Form Tambah Materi (3 Tipe) -->
                <form id="form-tambah-materi" action="{{ route('guru.materi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 hidden">
                    @csrf
                    <!-- Cascading Dropdown: Mapel -> Bab -> Sub-Bab -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">1. Mapel <span class="text-rose-500">*</span></label>
                            <select id="tambah-materi-mapel" onchange="loadBabsForSelect(this.value, 'tambah-materi-bab', 'tambah-materi-subbab')" required class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                                <option value="">-- Mapel --</option>
                                @foreach(\App\Models\MataPelajaran::all() as $m)
                                    <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">2. Bab <span class="text-rose-500">*</span></label>
                            <select id="tambah-materi-bab" onchange="loadSubBabsForSelect(this.value, 'tambah-materi-subbab')" required class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                                <option value="">-- Pilih Mapel Dulu --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">3. Sub-Bab <span class="text-rose-500">*</span></label>
                            <select name="id_sub_bab" id="tambah-materi-subbab" required class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                                <option value="">-- Pilih Bab Dulu --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Judul Materi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Materi <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul_materi" required placeholder="Contoh: Video Penjelasan Nilai Tempat" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    </div>

                    <!-- Tipe Materi Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Konten Materi <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="tipe_materi" value="video" checked onchange="toggleTipeInput('tambah', 'video')" class="text-[#13527D] focus:ring-[#13527D]">
                                <span class="text-xs font-bold text-slate-700"><i class="fas fa-video text-sky-500 mr-1"></i> Video</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="tipe_materi" value="dokumen" onchange="toggleTipeInput('tambah', 'dokumen')" class="text-[#13527D] focus:ring-[#13527D]">
                                <span class="text-xs font-bold text-slate-700"><i class="fas fa-file-pdf text-rose-500 mr-1"></i> PDF</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="tipe_materi" value="kuis" onchange="toggleTipeInput('tambah', 'kuis')" class="text-[#13527D] focus:ring-[#13527D]">
                                <span class="text-xs font-bold text-slate-700"><i class="fas fa-circle-question text-emerald-500 mr-1"></i> Kuis</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tipe Form Input: Video -->
                    <div id="tambah-input-video" class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">Link URL Video (YouTube / MP4) <span class="text-rose-500">*</span></label>
                        <input type="url" name="url_video" id="tambah-url-video" placeholder="https://www.youtube.com/watch?v=... atau https://example.com/video.mp4" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                        <p class="text-[10px] text-slate-400">Wajib URL YouTube valid atau link berkas video .mp4.</p>
                    </div>

                    <!-- Tipe Form Input: Dokumen PDF -->
                    <div id="tambah-input-dokumen" class="space-y-1 hidden">
                        <label class="block text-xs font-bold text-slate-700">Berkas Dokumen PDF <span class="text-rose-500">*</span></label>
                        <input type="file" name="file_pdf" id="tambah-file-pdf" accept="application/pdf" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                        <p class="text-[10px] text-slate-400">Format wajib .pdf, batas ukuran maksimal 10 MB.</p>
                    </div>

                    <!-- Tipe Form Input: Kuis -->
                    <div id="tambah-input-kuis" class="space-y-3 hidden bg-emerald-50/50 p-4 rounded-xl border border-emerald-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-800">
                                <i class="fas fa-circle-info mr-1"></i> Bank Soal Kuis Otomatis
                            </span>
                            <a href="{{ route('guru.quiz.template.download') }}" class="text-[11px] font-bold text-[#13527D] hover:underline flex items-center gap-1">
                                <i class="fas fa-download"></i> Unduh Template Excel
                            </a>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Soal Excel / CSV (Opsional sekarang, bisa diisi nanti):</label>
                            <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                            <p class="text-[10px] text-slate-500 mt-1">Maksimal 50 soal per file. Format kolom: pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban.</p>
                        </div>
                    </div>

                    <!-- Deskripsi / Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Catatan Materi (Opsional)</label>
                        <textarea name="isi_materi" rows="2" placeholder="Petunjuk pengerjaan atau ringkasan..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl shadow-sm transition">
                            Simpan Konten Materi
                        </button>
                    </div>
                </form>
            </div>

            <!-- ================= TAB 2: EDIT ================= -->
            <div id="tab-content-edit" class="space-y-5 hidden">
                <!-- Cascading Search Materi Eksisting PRD 4.2.B -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <span class="text-xs font-bold text-slate-700 block">1. Cari Materi yang Ingin Diedit:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1">Pilih Mapel</label>
                            <select id="edit-filter-mapel" onchange="loadBabsForSelect(this.value, 'edit-filter-bab', 'edit-filter-subbab', 'edit-filter-materi')" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                                <option value="">-- Pilih Mapel --</option>
                                @foreach(\App\Models\MataPelajaran::all() as $m)
                                    <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1">Pilih Bab</label>
                            <select id="edit-filter-bab" onchange="loadSubBabsForSelect(this.value, 'edit-filter-subbab', 'edit-filter-materi')" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                                <option value="">-- Pilih Mapel Dahulu --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1">Pilih Sub-Bab</label>
                            <select id="edit-filter-subbab" onchange="loadMaterisForSelect(this.value, 'edit-filter-materi')" class="w-full px-2.5 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                                <option value="">-- Pilih Bab Dahulu --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1">Pilih Materi Target</label>
                            <select id="edit-filter-materi" onchange="fetchAndPopulateEditMateri(this.value)" class="w-full px-2.5 py-2 bg-white border border-[#13527D] rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-[#13527D]">
                                <option value="">-- Pilih Sub-Bab Dahulu --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Form Edit Dinamis Menyesuaikan Tipe Terpilih -->
                <form id="form-edit-materi" action="" method="POST" enctype="multipart/form-data" class="space-y-4 hidden">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="tipe_materi" id="edit-materi-tipe-hidden">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Materi <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul_materi" id="edit-judul-materi" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    </div>

                    <!-- Edit Video UI -->
                    <div id="edit-container-video" class="space-y-2 hidden">
                        <label class="block text-xs font-bold text-slate-700">Link Video Saat Ini</label>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 break-all flex items-center justify-between">
                            <span id="edit-current-video-url" class="truncate mr-2 font-mono text-[11px]">-</span>
                        </div>
                        <label class="block text-xs font-bold text-slate-700 mt-2">Ganti URL Video Baru (Opsional)</label>
                        <input type="url" name="url_video" id="edit-new-video-url" placeholder="Masukkan URL YouTube / MP4 baru..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    </div>

                    <!-- Edit Dokumen UI -->
                    <div id="edit-container-dokumen" class="space-y-2 hidden">
                        <label class="block text-xs font-bold text-slate-700">Berkas Dokumen PDF Saat Ini</label>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs flex items-center justify-between">
                            <span id="edit-current-pdf-name" class="text-slate-700 font-medium">PDF Terunggah</span>
                            <a id="edit-current-pdf-link" href="#" target="_blank" class="text-xs font-bold text-[#13527D] hover:underline flex items-center gap-1">
                                <i class="fas fa-eye"></i> Lihat PDF
                            </a>
                        </div>
                        <label class="block text-xs font-bold text-slate-700 mt-2">Unggah PDF Pengganti (Opsional, Max 10MB)</label>
                        <input type="file" name="file_pdf" accept="application/pdf" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    </div>

                    <!-- Edit Kuis UI -->
                    <div id="edit-container-kuis" class="space-y-3 hidden">
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                            <h5 class="text-xs font-bold text-emerald-800 mb-1">Informasi Kuis</h5>
                            <p class="text-xs text-slate-600" id="edit-current-quiz-info">Memuat data kuis...</p>
                            <div class="mt-3 flex gap-2">
                                <a id="edit-btn-bank-soal" href="#" class="px-3 py-1.5 bg-[#13527D] text-white rounded-lg text-xs font-bold">
                                    <i class="fas fa-tasks mr-1"></i> Buka Bank Soal
                                </a>
                                <a href="{{ route('guru.quiz.template.download') }}" class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-50">
                                    <i class="fas fa-download mr-1"></i> Download Template Excel
                                </a>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Timpa / Tambah Bank Soal dengan File Excel Baru:</label>
                            <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                            <p class="text-[10px] text-slate-400 mt-1">Mengunggah file excel baru akan menambahkan bank soal secara otomatis.</p>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Deskripsi</label>
                        <textarea name="isi_materi" id="edit-isi-materi" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]"></textarea>
                    </div>

                    <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                        <span id="edit-loading-hint" class="text-xs text-slate-400 italic hidden">Menyimpan...</span>
                        <div class="flex justify-end gap-2 w-full">
                            <button type="button" onclick="closeModalAction()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl shadow-sm transition">
                                Simpan Perubahan Materi
                            </button>
                        </div>
                    </div>
                </form>

                <div id="edit-empty-placeholder" class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                    <i class="fas fa-magnifying-glass text-2xl mb-2 text-slate-300"></i>
                    <p>Silakan pilih Mapel, Bab, Sub-Bab, dan Materi di atas untuk mulai mengedit.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Global Modal State & Handlers
    let currentActionTab = 'tambah';
    let currentTambahTarget = 'bab';

    function openModalAction(mode = 'tambah', target = 'materi', prefill = {}) {
        const modal = document.getElementById('modal-learning-action');
        if (!modal) return;
        modal.classList.remove('hidden');

        switchActionTab(mode);
        if (mode === 'tambah') {
            setTambahTarget(target);
            if (prefill.idMapel) {
                if (target === 'bab') {
                    const sel = document.getElementById('tambah-bab-mapel');
                    if (sel) sel.value = prefill.idMapel;
                } else if (target === 'sub_bab') {
                    const sel = document.getElementById('tambah-subbab-mapel');
                    if (sel) {
                        sel.value = prefill.idMapel;
                        loadBabsForSelect(prefill.idMapel, 'tambah-subbab-bab').then(() => {
                            if (prefill.idBab) {
                                const bSel = document.getElementById('tambah-subbab-bab');
                                if (bSel) bSel.value = prefill.idBab;
                            }
                        });
                    }
                } else if (target === 'materi') {
                    const mSel = document.getElementById('tambah-materi-mapel');
                    if (mSel) {
                        mSel.value = prefill.idMapel;
                        loadBabsForSelect(prefill.idMapel, 'tambah-materi-bab').then(() => {
                            if (prefill.idBab) {
                                const bSel = document.getElementById('tambah-materi-bab');
                                if (bSel) {
                                    bSel.value = prefill.idBab;
                                    loadSubBabsForSelect(prefill.idBab, 'tambah-materi-subbab').then(() => {
                                        if (prefill.idSubBab) {
                                            const sSel = document.getElementById('tambah-materi-subbab');
                                            if (sSel) sSel.value = prefill.idSubBab;
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            }
        } else if (mode === 'edit') {
            if (prefill.idMateri) {
                editMateriDirect(prefill.idMateri);
            }
        }
    }

    function closeModalAction() {
        const modal = document.getElementById('modal-learning-action');
        if (modal) modal.classList.add('hidden');
    }

    // Tab Switcher (Tambah vs Edit)
    function switchActionTab(tab) {
        currentActionTab = tab;
        const tabTambah = document.getElementById('tab-content-tambah');
        const tabEdit = document.getElementById('tab-content-edit');
        const btnTambah = document.getElementById('tab-btn-tambah');
        const btnEdit = document.getElementById('tab-btn-edit');

        if (tab === 'tambah') {
            tabTambah.classList.remove('hidden');
            tabEdit.classList.add('hidden');
            btnTambah.className = 'px-3 py-1.5 rounded-lg font-bold bg-white text-[#13527D] shadow-sm transition';
            btnEdit.className = 'px-3 py-1.5 rounded-lg font-semibold text-white/80 hover:text-white transition';
            document.getElementById('modal-action-title').innerHTML = '<i class="fas fa-layer-group text-sky-300"></i> Tambah Bab / Materi Baru';
        } else {
            tabTambah.classList.add('hidden');
            tabEdit.classList.remove('hidden');
            btnEdit.className = 'px-3 py-1.5 rounded-lg font-bold bg-white text-[#13527D] shadow-sm transition';
            btnTambah.className = 'px-3 py-1.5 rounded-lg font-semibold text-white/80 hover:text-white transition';
            document.getElementById('modal-action-title').innerHTML = '<i class="fas fa-pen-to-square text-sky-300"></i> Edit Materi Eksisting';
        }
    }

    // Target Switcher in Tambah Tab (Bab vs Sub-Bab vs Materi)
    function setTambahTarget(target) {
        currentTambahTarget = target;
        const targets = ['bab', 'sub_bab', 'materi'];
        targets.forEach(t => {
            const form = document.getElementById('form-tambah-' + t);
            const btn = document.getElementById('target-btn-' + t);
            if (t === target) {
                form.classList.remove('hidden');
                btn.className = 'py-2.5 px-3 rounded-xl border-2 border-[#13527D] bg-sky-50/50 text-xs font-bold text-[#13527D] shadow-sm text-center flex flex-col items-center gap-1';
            } else {
                form.classList.add('hidden');
                btn.className = 'py-2.5 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:border-[#13527D] transition text-center flex flex-col items-center gap-1';
            }
        });
    }

    // Toggle Tipe Input in Tambah Form
    function toggleTipeInput(prefix, tipe) {
        document.getElementById(prefix + '-input-video').classList.toggle('hidden', tipe !== 'video');
        document.getElementById(prefix + '-input-dokumen').classList.toggle('hidden', tipe !== 'dokumen');
        document.getElementById(prefix + '-input-kuis').classList.toggle('hidden', tipe !== 'kuis');
    }

    // Cascading Dropdown Handlers
    async function loadBabsForSelect(idMapel, targetSelectId, resetSubBabId = null, resetMateriId = null) {
        const select = document.getElementById(targetSelectId);
        if (!select) return;
        select.innerHTML = '<option value="">-- Memuat Bab... --</option>';

        if (resetSubBabId) {
            const s = document.getElementById(resetSubBabId);
            if (s) s.innerHTML = '<option value="">-- Pilih Bab Dahulu --</option>';
        }
        if (resetMateriId) {
            const m = document.getElementById(resetMateriId);
            if (m) m.innerHTML = '<option value="">-- Pilih Sub-Bab Dahulu --</option>';
        }

        if (!idMapel) {
            select.innerHTML = '<option value="">-- Pilih Mapel Dahulu --</option>';
            return;
        }

        try {
            const res = await fetch(`/guru/api/cascading/babs/${idMapel}`);
            const data = await res.json();
            select.innerHTML = '<option value="">-- Pilih Bab --</option>';
            data.forEach(b => {
                select.innerHTML += `<option value="${b.id_bab}">${b.nama_bab}</option>`;
            });
        } catch (e) {
            select.innerHTML = '<option value="">Gagal memuat Bab</option>';
        }
    }

    async function loadSubBabsForSelect(idBab, targetSelectId, resetMateriId = null) {
        const select = document.getElementById(targetSelectId);
        if (!select) return;
        select.innerHTML = '<option value="">-- Memuat Sub-Bab... --</option>';

        if (resetMateriId) {
            const m = document.getElementById(resetMateriId);
            if (m) m.innerHTML = '<option value="">-- Pilih Sub-Bab Dahulu --</option>';
        }

        if (!idBab) {
            select.innerHTML = '<option value="">-- Pilih Bab Dahulu --</option>';
            return;
        }

        try {
            const res = await fetch(`/guru/api/cascading/sub-babs/${idBab}`);
            const data = await res.json();
            select.innerHTML = '<option value="">-- Pilih Sub-Bab --</option>';
            data.forEach(sb => {
                select.innerHTML += `<option value="${sb.id_sub_bab}">${sb.nama_sub_bab}</option>`;
            });
        } catch (e) {
            select.innerHTML = '<option value="">Gagal memuat Sub-Bab</option>';
        }
    }

    async function loadMaterisForSelect(idSubBab, targetSelectId) {
        const select = document.getElementById(targetSelectId);
        if (!select) return;
        select.innerHTML = '<option value="">-- Memuat Materi... --</option>';

        if (!idSubBab) {
            select.innerHTML = '<option value="">-- Pilih Sub-Bab Dahulu --</option>';
            return;
        }

        try {
            const res = await fetch(`/guru/api/cascading/materis/${idSubBab}`);
            const data = await res.json();
            select.innerHTML = '<option value="">-- Pilih Materi untuk Diedit --</option>';
            data.forEach(m => {
                select.innerHTML += `<option value="${m.id_materi}">[${m.tipe_materi.toUpperCase()}] ${m.judul_materi}</option>`;
            });
        } catch (e) {
            select.innerHTML = '<option value="">Gagal memuat materi</option>';
        }
    }

    // Direct Edit from view list
    async function editMateriDirect(idMateri) {
        openModalAction('edit');
        await fetchAndPopulateEditMateri(idMateri);
    }

    // Populate Edit Form with details
    async function fetchAndPopulateEditMateri(idMateri) {
        const form = document.getElementById('form-edit-materi');
        const emptyState = document.getElementById('edit-empty-placeholder');
        if (!idMateri) {
            form.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        try {
            const res = await fetch(`/guru/api/cascading/materi/${idMateri}`);
            const materi = await res.json();

            emptyState.classList.add('hidden');
            form.classList.remove('hidden');

            form.action = `/guru/materi/${materi.id_materi}`;
            document.getElementById('edit-judul-materi').value = materi.judul_materi || '';
            document.getElementById('edit-isi-materi').value = materi.isi_materi || '';
            document.getElementById('edit-materi-tipe-hidden').value = materi.tipe_materi;

            // Reset containers
            const cVideo = document.getElementById('edit-container-video');
            const cDokumen = document.getElementById('edit-container-dokumen');
            const cKuis = document.getElementById('edit-container-kuis');
            cVideo.classList.add('hidden');
            cDokumen.classList.add('hidden');
            cKuis.classList.add('hidden');

            if (materi.tipe_materi === 'video') {
                cVideo.classList.remove('hidden');
                document.getElementById('edit-current-video-url').textContent = materi.url_video || '(Belum ada link)';
                document.getElementById('edit-new-video-url').value = materi.url_video || '';
            } else if (materi.tipe_materi === 'dokumen') {
                cDokumen.classList.remove('hidden');
                document.getElementById('edit-current-pdf-name').textContent = materi.file_pdf ? materi.file_pdf.split('/').pop() : '(Belum ada file)';
                const pdfLink = document.getElementById('edit-current-pdf-link');
                if (materi.file_pdf) {
                    pdfLink.href = `/storage/${materi.file_pdf}`;
                    pdfLink.classList.remove('hidden');
                } else {
                    pdfLink.classList.add('hidden');
                }
            } else if (materi.tipe_materi === 'kuis') {
                cKuis.classList.remove('hidden');
                const infoText = materi.quiz ? `Judul Kuis: "${materi.quiz.judul_quiz}"` : 'Kuis belum diinisialisasi.';
                document.getElementById('edit-current-quiz-info').textContent = infoText;
                if (materi.quiz) {
                    document.getElementById('edit-btn-bank-soal').href = `/guru/quiz/${materi.quiz.id_quiz}/bank`;
                }
            }
        } catch (e) {
            alert('Gagal memuat detail materi: ' + e.message);
        }
    }

    // ESC key listener to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModalAction();
    });
</script>
