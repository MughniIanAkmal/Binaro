<!-- ================= MODAL TAMBAH RPP ================= -->
<div id="modal-tambah-rpp" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#13527D] text-white p-5 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="fas fa-file-circle-plus text-amber-300"></i> Buat Modul Ajar (RPP) Baru
                </h3>
                <p class="text-xs text-sky-100 mt-0.5">
                    Kurikulum Merdeka &bull; {{ $guru->nama_guru ?? 'Guru Pengajar' }}
                </p>
            </div>
            <button type="button" onclick="closeModalTambahRpp()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form action="{{ route('guru.rpp.store') }}" method="POST" enctype="multipart/form-data" class="p-6 overflow-y-auto flex-1 space-y-4">
            @csrf
            <input type="hidden" name="id_guru" value="{{ $guru->id_guru ?? 1 }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Mapel -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="id_mapel" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapelList as $mapel)
                            <option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelas & Rombel</label>
                    <select name="id_rooms" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id_rooms }}">{{ $kelas->pararel ?? 'Kelas ' . $kelas->id_rooms }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Judul RPP -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                    <span>Topik / Judul Modul Ajar <span class="text-rose-500">*</span></span>
                    <span id="counter-tambah-judul" class="text-[10px] text-slate-400 font-normal">0/100</span>
                </label>
                <input type="text" name="judul_rpp" id="tambah-judul-rpp" required maxlength="100" placeholder="Contoh: Konsep Pecahan Senilai dan Membandingkan Pecahan"
                       oninput="sanitizeAlphanumericOnly(this, 'counter-tambah-judul', 100)"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                <p class="text-[10px] text-slate-400 mt-1">Maks. 100 karakter. Hanya huruf, angka, dan spasi (tanpa simbol).</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Fase -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fase Pembelajaran</label>
                    <input type="text" name="fase" value="Fase B" placeholder="Fase B" maxlength="20"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                </div>

                <!-- Modul Ke -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Nomor Modul / Bab</span>
                        <span id="counter-tambah-modul" class="text-[10px] text-slate-400 font-normal">0/20</span>
                    </label>
                    <input type="text" name="modul_ke" id="tambah-modul-ke" maxlength="20" placeholder="Contoh: Modul 28"
                           oninput="sanitizeAlphanumericOnly(this, 'counter-tambah-modul', 20)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-400 mt-0.5">Maks. 20 karakter, tanpa simbol.</p>
                </div>

                <!-- Alokasi Waktu -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Alokasi Waktu (JP)</span>
                        <span class="text-[10px] text-amber-700 font-bold bg-amber-100 px-1.5 py-0.5 rounded border border-amber-200">1JP = 45 Menit</span>
                    </label>
                    <input type="text" name="alokasi_waktu" id="tambah-alokasi-waktu" maxlength="4" placeholder="2 JP"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-500 mt-0.5"><span class="font-semibold text-amber-700">Note: 1JP = 45 Menit</span> (Maks. 4 huruf, contoh: 2 JP)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Jadwal -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Target Roster Jadwal</span>
                        <span id="counter-tambah-jadwal" class="text-[10px] text-slate-400 font-normal">0/50</span>
                    </label>
                    <input type="text" name="target_jadwal" id="tambah-target-jadwal" maxlength="50" placeholder="Senin, 08.00 - 09.30 WIB"
                           oninput="updateLengthCounter(this, 'counter-tambah-jadwal', 50)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 50 karakter.</p>
                </div>

                <!-- Ruang -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Ruang / Lokasi Belajar</span>
                        <span id="counter-tambah-ruang" class="text-[10px] text-slate-400 font-normal">0/50</span>
                    </label>
                    <input type="text" name="ruang" id="tambah-ruang" maxlength="50" placeholder="Ruang Kelas 4B"
                           oninput="updateLengthCounter(this, 'counter-tambah-ruang', 50)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 50 karakter.</p>
                </div>
            </div>

            <!-- Capaian Pembelajaran (TP) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                    <span>Capaian Pembelajaran (TP) & Deskripsi Ringkas</span>
                    <span id="counter-tambah-deskripsi" class="text-[10px] text-slate-400 font-normal">0/500</span>
                </label>
                <textarea name="deskripsi" id="tambah-deskripsi" rows="3" maxlength="500" placeholder="Tuliskan tujuan pembelajaran yang dicapai peserta didik dalam aktivitas materi ini (maksimal 500 karakter)..."
                          oninput="updateLengthCounter(this, 'counter-tambah-deskripsi', 500)"
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]"></textarea>
                <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 500 karakter.</p>
            </div>

            <!-- Komponen Checklist Interaktif -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <label class="block text-xs font-bold text-slate-800">Komponen & Fasilitas Modul Ajar:</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="tujuan" value="1" checked class="rounded text-[#13527D]">
                        <span>Tujuan Belajar</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="video" value="1" checked class="rounded text-[#13527D]">
                        <span>Video Animasi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="soal_proyektor" value="1" checked class="rounded text-[#13527D]">
                        <span>Soal Proyektor</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="lkpd" value="1" checked class="rounded text-[#13527D]">
                        <span>LKPD Siap Cetak</span>
                    </label>
                </div>
            </div>

            <!-- Tags Kustom -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                    <span>Label / Tag Tambahan (Tanpa Simbol)</span>
                    <span class="text-[10px] text-rose-500 font-semibold">Simbol Dilarang</span>
                </label>
                <input type="text" name="custom_tags" id="tambah-custom-tags" maxlength="150"
                       placeholder="Tujuan Pembelajaran, Video Animasi Interaktif, LKPD Cetak"
                       oninput="sanitizeTagsInput(this)"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                <p class="text-[10px] text-slate-400 mt-1">Pisahkan antar label dengan koma. Hanya huruf dan angka, dilarang simbol.</p>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Unggah Berkas Modul RPP (PDF / DOCX)</label>
                <input type="file" name="file_rpp" accept=".pdf,.docx,.doc"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-[#13527D] hover:file:bg-sky-100 cursor-pointer">
            </div>

            <!-- Info Status Supervisi -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2.5">
                <i class="fas fa-circle-info text-amber-600 mt-0.5 text-xs"></i>
                <div class="text-[11px] text-amber-900 leading-relaxed">
                    <span class="font-bold">Alur Persetujuan Admin:</span> Setelah diajukan, RPP berstatus <strong>Menunggu Persetujuan Admin</strong> dan belum bisa langsung di-apply mengajar hingga di-accept/disetujui oleh Admin.
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeModalTambahRpp()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" name="action" value="draft" class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    Simpan Draf
                </button>
                <button type="submit" name="action" value="publish" class="px-5 py-2 text-xs font-bold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <i class="fas fa-paper-plane text-[10px]"></i> Ajukan ke Admin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT RPP ================= -->
<div id="modal-edit-rpp" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#13527D] text-white p-5 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-amber-300"></i> Edit Modul Ajar (RPP)
                </h3>
                <p class="text-xs text-sky-100 mt-0.5" id="edit-modal-subtitle">
                    Perbarui rincian topik atau berkas pendukung
                </p>
            </div>
            <button type="button" onclick="closeModalEditRpp()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="form-edit-rpp" method="POST" enctype="multipart/form-data" class="p-6 overflow-y-auto flex-1 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Mapel -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="id_mapel" id="edit-id-mapel" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                        @foreach($mapelList as $mapel)
                            <option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelas & Rombel</label>
                    <select name="id_rooms" id="edit-id-rooms" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id_rooms }}">{{ $kelas->pararel ?? 'Kelas ' . $kelas->id_rooms }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Judul RPP -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                    <span>Topik / Judul Modul Ajar <span class="text-rose-500">*</span></span>
                    <span id="counter-edit-judul" class="text-[10px] text-slate-400 font-normal">0/100</span>
                </label>
                <input type="text" name="judul_rpp" id="edit-judul-rpp" required maxlength="100"
                       oninput="sanitizeAlphanumericOnly(this, 'counter-edit-judul', 100)"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                <p class="text-[10px] text-slate-400 mt-1">Maks. 100 karakter. Hanya huruf, angka, dan spasi (tanpa simbol).</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Fase -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fase Pembelajaran</label>
                    <input type="text" name="fase" id="edit-fase" placeholder="Fase B" maxlength="20"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                </div>

                <!-- Modul Ke -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Nomor Modul / Bab</span>
                        <span id="counter-edit-modul" class="text-[10px] text-slate-400 font-normal">0/20</span>
                    </label>
                    <input type="text" name="modul_ke" id="edit-modul-ke" maxlength="20" placeholder="Contoh: Modul 28"
                           oninput="sanitizeAlphanumericOnly(this, 'counter-edit-modul', 20)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-400 mt-0.5">Maks. 20 karakter, tanpa simbol.</p>
                </div>

                <!-- Alokasi Waktu -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Alokasi Waktu (JP)</span>
                        <span class="text-[10px] text-amber-700 font-bold bg-amber-100 px-1.5 py-0.5 rounded border border-amber-200">1JP = 45 Menit</span>
                    </label>
                    <input type="text" name="alokasi_waktu" id="edit-alokasi-waktu" maxlength="4" placeholder="2 JP"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-500 mt-0.5"><span class="font-semibold text-amber-700">Note: 1JP = 45 Menit</span> (Maks. 4 huruf, contoh: 2 JP)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Jadwal -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Target Roster Jadwal</span>
                        <span id="counter-edit-jadwal" class="text-[10px] text-slate-400 font-normal">0/50</span>
                    </label>
                    <input type="text" name="target_jadwal" id="edit-target-jadwal" maxlength="50" placeholder="Senin, 08.00 - 09.30 WIB"
                           oninput="updateLengthCounter(this, 'counter-edit-jadwal', 50)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 50 karakter.</p>
                </div>

                <!-- Ruang -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Ruang / Lokasi Belajar</span>
                        <span id="counter-edit-ruang" class="text-[10px] text-slate-400 font-normal">0/50</span>
                    </label>
                    <input type="text" name="ruang" id="edit-ruang" maxlength="50" placeholder="Ruang Kelas 4B"
                           oninput="updateLengthCounter(this, 'counter-edit-ruang', 50)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                    <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 50 karakter.</p>
                </div>
            </div>

            <!-- Info Status Supervisi -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2.5">
                <i class="fas fa-circle-info text-amber-600 mt-0.5 text-xs"></i>
                <div class="text-[11px] text-amber-900 leading-relaxed">
                    <span class="font-bold">Alur Persetujuan Admin:</span> Setelah diajukan/diperbarui, RPP akan berstatus <strong>Menunggu Persetujuan Admin</strong> dan belum bisa langsung di-apply mengajar hingga di-accept/disetujui oleh Administrator.
                </div>
            </div>

            <!-- Capaian Pembelajaran (TP) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                    <span>Capaian Pembelajaran (TP)</span>
                    <span id="counter-edit-deskripsi" class="text-[10px] text-slate-400 font-normal">0/500</span>
                </label>
                <textarea name="deskripsi" id="edit-deskripsi" rows="3" maxlength="500"
                          oninput="updateLengthCounter(this, 'counter-edit-deskripsi', 500)"
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]"></textarea>
                <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 500 karakter.</p>
            </div>

            <!-- Komponen Checklist Interaktif -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <label class="block text-xs font-bold text-slate-800">Komponen Modul Ajar:</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="tujuan" id="edit-check-tujuan" value="1" class="rounded text-[#13527D]">
                        <span>Tujuan Belajar</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="video" id="edit-check-video" value="1" class="rounded text-[#13527D]">
                        <span>Video Animasi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="soal_proyektor" id="edit-check-soal" value="1" class="rounded text-[#13527D]">
                        <span>Soal Proyektor</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-[#13527D]">
                        <input type="checkbox" name="lkpd" id="edit-check-lkpd" value="1" class="rounded text-[#13527D]">
                        <span>LKPD Siap Cetak</span>
                    </label>
                </div>
            </div>

            <!-- Tags Kustom Edit -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                    <span>Label / Tag Tambahan (Tanpa Simbol)</span>
                    <span class="text-[10px] text-rose-500 font-semibold">Simbol Dilarang</span>
                </label>
                <input type="text" name="custom_tags" id="edit-custom-tags" maxlength="150"
                       placeholder="Tujuan Belajar, Video Animasi, Soal Proyektor, LKPD Cetak"
                       oninput="sanitizeTagsInput(this)"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-[#13527D]">
                <p class="text-[10px] text-slate-400 mt-1">Pisahkan antar label dengan koma. Hanya huruf dan angka, dilarang simbol.</p>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Berkas Modul (Opsional)</label>
                <input type="file" name="file_rpp" accept=".pdf,.docx,.doc"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-[#13527D] hover:file:bg-sky-100 cursor-pointer">
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeModalEditRpp()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" name="action" value="draft" class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    Simpan Draf
                </button>
                <button type="submit" name="action" value="publish" class="px-5 py-2 text-xs font-bold text-white bg-[#13527D] hover:bg-[#0E3D5D] rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <i class="fas fa-paper-plane text-[10px]"></i> Ajukan ke Admin
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL DETAIL RPP ================= -->
<div id="modal-detail-rpp" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-[#13527D] text-white p-5 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold flex items-center gap-2" id="detail-title">
                    <i class="fas fa-file-lines text-amber-300"></i> Detail Modul Ajar
                </h3>
                <p class="text-xs text-sky-100 mt-0.5" id="detail-subtitle">
                    SDN Kalitapen 01 &bull; Portal Guru
                </p>
            </div>
            <button type="button" onclick="closeModalDetailRpp()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Detail Content -->
        <div class="p-6 overflow-y-auto flex-1 space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-4 pb-3 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Mata Pelajaran & Kelas</span>
                    <span class="font-bold text-slate-800 text-sm" id="detail-mapel">-</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Fase & Modul</span>
                    <span class="font-bold text-slate-800 text-sm" id="detail-fase-modul">-</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alokasi Waktu</span>
                    <span class="font-semibold text-slate-700" id="detail-alokasi">-</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jadwal & Ruang</span>
                    <span class="font-semibold text-slate-700" id="detail-jadwal">-</span>
                </div>
            </div>

            <!-- Capaian Pembelajaran -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-1.5">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">CAPAIAN PEMBELAJARAN (TP)</span>
                <p class="text-slate-800 text-xs leading-relaxed" id="detail-tp">
                    -
                </p>
            </div>

            <!-- Status Supervisi -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">STATUS SUPERVISI ADMIN</span>
                        <div id="detail-status-badge">
                            <!-- Filled dynamically -->
                        </div>
                    </div>
                    <div id="detail-download-btn-container">
                        <!-- Dynamic Download Button -->
                    </div>
                </div>
                <div id="detail-status-note">
                    <!-- Status explanatory note / revision note -->
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="button" onclick="closeModalDetailRpp()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT MODALS & CONTROLS ================= -->
<script>
    function sanitizeAlphanumericOnly(el, counterId, maxLen) {
        if (!el) return;
        const cleaned = el.value.replace(/[^a-zA-Z0-9\s]/g, '');
        if (el.value !== cleaned) {
            el.value = cleaned;
        }
        if (counterId && maxLen) {
            const counter = document.getElementById(counterId);
            if (counter) {
                counter.textContent = `${el.value.length}/${maxLen}`;
                if (el.value.length >= maxLen) {
                    counter.className = 'text-[10px] font-bold text-rose-600';
                } else {
                    counter.className = 'text-[10px] text-slate-400 font-normal';
                }
            }
        }
    }

    function updateLengthCounter(el, counterId, maxLen) {
        if (!el) return;
        if (counterId && maxLen) {
            const counter = document.getElementById(counterId);
            if (counter) {
                counter.textContent = `${el.value.length}/${maxLen}`;
                if (el.value.length >= maxLen) {
                    counter.className = 'text-[10px] font-bold text-rose-600';
                } else {
                    counter.className = 'text-[10px] text-slate-400 font-normal';
                }
            }
        }
    }

    function sanitizeTagsInput(el) {
        if (!el) return;
        // Hanya huruf, angka, spasi, dan koma sebagai pemisah
        const cleaned = el.value.replace(/[^a-zA-Z0-9\s,]/g, '');
        if (el.value !== cleaned) {
            el.value = cleaned;
        }
    }

    function openModalTambahRpp() {
        document.getElementById('modal-tambah-rpp').classList.remove('hidden');
        sanitizeAlphanumericOnly(document.getElementById('tambah-judul-rpp'), 'counter-tambah-judul', 100);
        sanitizeAlphanumericOnly(document.getElementById('tambah-modul-ke'), 'counter-tambah-modul', 20);
        updateLengthCounter(document.getElementById('tambah-target-jadwal'), 'counter-tambah-jadwal', 50);
        updateLengthCounter(document.getElementById('tambah-ruang'), 'counter-tambah-ruang', 50);
        updateLengthCounter(document.getElementById('tambah-deskripsi'), 'counter-tambah-deskripsi', 500);
    }
    function closeModalTambahRpp() {
        document.getElementById('modal-tambah-rpp').classList.add('hidden');
    }

    function openModalEditRpp(rpp) {
        document.getElementById('form-edit-rpp').action = `/guru/rpp/${rpp.id_rpp}`;
        document.getElementById('edit-modal-subtitle').innerText = `ID Modul: #${rpp.id_rpp} • ${rpp.fase || 'Fase B'}`;
        document.getElementById('edit-id-mapel').value = rpp.id_mapel || '';
        document.getElementById('edit-id-rooms').value = rpp.id_rooms || '';
        document.getElementById('edit-judul-rpp').value = rpp.judul_rpp || '';
        document.getElementById('edit-fase').value = rpp.fase || 'Fase B';
        document.getElementById('edit-modul-ke').value = rpp.modul_ke || '';
        document.getElementById('edit-alokasi-waktu').value = rpp.alokasi_waktu || '';
        document.getElementById('edit-target-jadwal').value = rpp.target_jadwal || '';
        document.getElementById('edit-ruang').value = rpp.ruang || '';
        document.getElementById('edit-deskripsi').value = rpp.deskripsi || '';

        const check = rpp.komponen_checklist || {};
        document.getElementById('edit-check-tujuan').checked = !!check.tujuan;
        document.getElementById('edit-check-video').checked = !!check.video;
        document.getElementById('edit-check-soal').checked = !!check.soal_proyektor;
        document.getElementById('edit-check-lkpd').checked = !!check.lkpd;

        const tags = (check && check.tags) ? check.tags : [];
        document.getElementById('edit-custom-tags').value = Array.isArray(tags) ? tags.join(', ') : (tags || '');

        sanitizeAlphanumericOnly(document.getElementById('edit-judul-rpp'), 'counter-edit-judul', 100);
        sanitizeAlphanumericOnly(document.getElementById('edit-modul-ke'), 'counter-edit-modul', 20);
        updateLengthCounter(document.getElementById('edit-target-jadwal'), 'counter-edit-jadwal', 50);
        updateLengthCounter(document.getElementById('edit-ruang'), 'counter-edit-ruang', 50);
        updateLengthCounter(document.getElementById('edit-deskripsi'), 'counter-edit-deskripsi', 500);

        document.getElementById('modal-edit-rpp').classList.remove('hidden');
    }
    function closeModalEditRpp() {
        document.getElementById('modal-edit-rpp').classList.add('hidden');
    }

    function openModalDetailRpp(idRpp) {
        fetch(`/guru/rpp/${idRpp}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('detail-title').innerHTML = `<i class="fas fa-file-lines text-amber-300"></i> ${data.judul_rpp}`;
            document.getElementById('detail-subtitle').innerText = `${data.guru ? data.guru.nama_guru : 'Guru Binaro'} • NIP. ${data.guru ? (data.guru.nip || '-') : '-'}`;
            document.getElementById('detail-mapel').innerText = `${data.mata_pelajaran ? data.mata_pelajaran.nama_mapel : '-'} (${data.kelas ? data.kelas.pararel : 'Kelas 4B'})`;
            document.getElementById('detail-fase-modul').innerText = `${data.fase || 'Fase B'} • ${data.modul_ke || ('Modul #' + data.id_rpp)}`;
            document.getElementById('detail-alokasi').innerText = data.alokasi_waktu || '2 JP (2 x 35 Menit)';
            document.getElementById('detail-jadwal').innerText = `${data.target_jadwal || '-'} (${data.ruang || 'Ruang Kelas'})`;
            document.getElementById('detail-tp').innerText = data.deskripsi || 'Capaian Pembelajaran belum diisi.';

            const statusBadge = document.getElementById('detail-status-badge');
            const statusNote = document.getElementById('detail-status-note');
            
            if (data.status === 'terverifikasi') {
                statusBadge.innerHTML = `<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5"><i class="fas fa-circle-check text-emerald-600"></i> Disetujui & Siap Ajar</span>`;
                statusNote.innerHTML = `<p class="text-[11px] text-emerald-700 bg-emerald-50/80 p-2.5 rounded-lg border border-emerald-100 mt-2"><i class="fas fa-info-circle mr-1"></i> Modul ajar telah disetujui (di-accept) oleh Admin dan siap diaplikasikan untuk kegiatan belajar mengajar.</p>`;
            } else if (data.status === 'menunggu_review') {
                statusBadge.innerHTML = `<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1.5"><i class="fas fa-clock text-amber-600 animate-pulse"></i> Menunggu Persetujuan Admin</span>`;
                statusNote.innerHTML = `<p class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded-lg border border-amber-200 mt-2"><i class="fas fa-hourglass-half mr-1"></i> Modul ini telah terkirim, namun belum bisa langsung di-apply karena masih menunggu proses review & persetujuan dari Administrator sekolah.</p>`;
            } else if (data.status === 'perlu_revisi') {
                statusBadge.innerHTML = `<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1.5"><i class="fas fa-circle-exclamation text-rose-600"></i> Perlu Revisi</span>`;
                let revHtml = `<div class="text-[11px] text-rose-900 bg-rose-50 p-2.5 rounded-lg border border-rose-200 mt-2 space-y-1">
                    <p class="font-bold"><i class="fas fa-circle-exclamation mr-1"></i> Catatan Revisi dari Admin:</p>
                    <p class="italic text-rose-800 font-normal">${data.catatan_revisi || 'Silakan tinjau kembali berkas dan kelengkapan modul ajar sesuai petunjuk supervisi.'}</p>
                </div>`;
                statusNote.innerHTML = revHtml;
            } else {
                statusBadge.innerHTML = `<span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-200 text-slate-700 border border-slate-300 inline-flex items-center gap-1.5"><i class="fas fa-file-dashed-line text-slate-500"></i> Draf (Belum Diajukan)</span>`;
                statusNote.innerHTML = `<p class="text-[11px] text-slate-600 bg-slate-100 p-2.5 rounded-lg border border-slate-200 mt-2"><i class="fas fa-pencil mr-1"></i> Modul ajar masih berstatus draf pribadi dan belum dikirimkan ke Admin.</p>`;
            }

            const dlBtn = document.getElementById('detail-download-btn-container');
            dlBtn.innerHTML = `
                <a href="/guru/rpp/${data.id_rpp}/download" class="px-4 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fas fa-download"></i> Unduh Berkas RPP
                </a>
            `;

            document.getElementById('modal-detail-rpp').classList.remove('hidden');
        })
        .catch(err => {
            console.error('Error fetching RPP detail:', err);
            alert('Gagal memuat rincian RPP.');
        });
    }
    function closeModalDetailRpp() {
        document.getElementById('modal-detail-rpp').classList.add('hidden');
    }

    // Close modals on clicking backdrop
    window.addEventListener('click', function(e) {
        const modalTambah = document.getElementById('modal-tambah-rpp');
        const modalEdit = document.getElementById('modal-edit-rpp');
        const modalDetail = document.getElementById('modal-detail-rpp');

        if (e.target === modalTambah) closeModalTambahRpp();
        if (e.target === modalEdit) closeModalEditRpp();
        if (e.target === modalDetail) closeModalDetailRpp();
    });
</script>
