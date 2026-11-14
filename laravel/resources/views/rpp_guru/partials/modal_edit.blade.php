<!-- ================= MODAL EDIT RPP ================= -->
<div id="modal-edit-rpp" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl my-8 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-primary text-white p-5 flex items-center justify-between shrink-0">
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
            <input type="hidden" name="id_jadwal" id="edit-id-jadwal" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Mapel -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="id_mapel" id="edit-id-mapel" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
                        @foreach($mapelList as $mapel)
                            <option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelas & Rombel</label>
                    <select name="id_rooms" id="edit-id-rooms" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
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
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
                <p class="text-[10px] text-slate-400 mt-1">Maks. 100 karakter. Hanya huruf, angka, dan spasi (tanpa simbol).</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
                <!-- Fase -->
                <div>
                    <label class="text-xs font-bold text-slate-700 mb-1.5 h-5 flex items-center justify-between">
                        <span>Fase Pembelajaran</span>
                    </label>
                    <input type="text" name="fase" id="edit-fase" placeholder="Fase B" maxlength="20"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
                    <p class="text-[10px] text-slate-400 mt-1">Standar kelas 4: Fase B.</p>
                </div>

                <!-- Modul Ke -->
                <div>
                    <label class="text-xs font-bold text-slate-700 mb-1.5 h-5 flex items-center justify-between">
                        <span>Nomor Modul / Bab</span>
                        <span id="counter-edit-modul" class="text-[10px] text-slate-400 font-normal">0/20</span>
                    </label>
                    <input type="text" name="modul_ke" id="edit-modul-ke" maxlength="20" placeholder="Contoh: Modul 28"
                           oninput="sanitizeAlphanumericOnly(this, 'counter-edit-modul', 20)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
                    <p class="text-[10px] text-slate-400 mt-1">Maks. 20 karakter, tanpa simbol.</p>
                </div>

                <!-- Alokasi Waktu (Otomatis dari Roster Jadwal) -->
                <div>
                    <label class="text-xs font-bold text-slate-700 mb-1.5 h-5 flex items-center justify-between gap-1">
                        <span class="whitespace-nowrap">Alokasi Waktu (JP)</span>
                        <span id="edit-badge-jp" class="text-[10px] text-emerald-800 font-bold bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-300 whitespace-nowrap">
                            1JP = 45m
                        </span>
                    </label>
                    <input type="text" name="alokasi_waktu" id="edit-alokasi-waktu" readonly placeholder="Otomatis dari jadwal"
                           class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none cursor-not-allowed">
                    <p id="edit-note-jp" class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fas fa-calculator text-emerald-600 shrink-0"></i>
                        <span class="truncate">Otomatis dari durasi jadwal.</span>
                    </p>
                </div>
            </div>

            <!-- Target Roster Jadwal Builder & Picker -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fas fa-clock text-primary"></i>
                        <span>Target Roster Jadwal Pelajaran <span class="text-rose-500">*</span></span>
                    </label>
                    <span class="text-[10px] font-semibold text-slate-600 bg-white px-2 py-0.5 rounded-full border border-slate-200">
                        <i class="fas fa-school mr-1 text-primary"></i> Tutup Sekolah: <strong>12:00 WIB</strong>
                    </span>
                </div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Hari -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Hari</label>
                        <select id="edit-hari" onchange="syncEditJadwalFromPickers()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-primary">
                            <option value="">-- Pilih Hari --</option>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                <option value="{{ $hari }}">{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jam Mulai -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Mulai <span class="font-normal text-slate-400">(24 jam)</span></label>
                        <div class="relative">
                            <input type="text" id="edit-jam-mulai" inputmode="numeric" maxlength="5" autocomplete="off" placeholder="07.00"
                                   oninput="maskJamInput(this); syncEditJadwalFromPickers()"
                                   class="w-full pl-3 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold font-mono text-slate-800 focus:outline-none focus:border-primary">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-primary pointer-events-none">WIB</span>
                        </div>
                    </div>

                    <!-- Jam Selesai -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Selesai <span class="font-normal text-slate-400">(24 jam)</span></label>
                        <div class="relative">
                            <input type="text" id="edit-jam-selesai" inputmode="numeric" maxlength="5" autocomplete="off" placeholder="08.30"
                                   oninput="maskJamInput(this); syncEditJadwalFromPickers()"
                                   class="w-full pl-3 pr-12 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold font-mono text-slate-800 focus:outline-none focus:border-primary">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-primary pointer-events-none">WIB</span>
                        </div>
                    </div>
                </div>

                <!-- Input Terformat untuk Database -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1 flex items-center justify-between">
                        <span>Format Teks Jadwal (Tersimpan di Sistem):</span>
                        <span id="counter-edit-jadwal" class="text-[10px] text-slate-400 font-normal">0/50</span>
                    </label>
                    <input type="text" name="target_jadwal" id="edit-target-jadwal" maxlength="50"
                           placeholder="Contoh: Senin, 07.15 - 08.45 WIB"
                           oninput="onEditTargetJadwalManual(this)"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:outline-none focus:border-primary">
                    <p class="text-[10px] text-slate-400 mt-0.5">
                        *Otomatis terisi saat memilih Hari, Jam Mulai & Selesai di atas (atau dapat diedit langsung).
                    </p>
                </div>

                <!-- Warning Box Kesalahan Jadwal (Jam Terbalik, Terlalu Singkat, dsb) -->
                <div id="edit-jadwal-error-box" class="hidden p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold flex items-start gap-2">
                    <i class="fas fa-triangle-exclamation text-rose-500 text-sm mt-0.5 shrink-0"></i>
                    <span id="edit-jadwal-error-text">Jam pelajaran terbalik! Jam mulai tidak boleh lebih lambat atau sama dengan jam selesai.</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Ruang -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Ruang / Lokasi Belajar</span>
                        <span id="counter-edit-ruang" class="text-[10px] text-slate-400 font-normal">0/50</span>
                    </label>
                    <input type="text" name="ruang" id="edit-ruang" maxlength="50" placeholder="Ruang Kelas 4B"
                           oninput="updateLengthCounter(this, 'counter-edit-ruang', 50)"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
                    <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 50 karakter.</p>
                </div>

                <!-- Target Tanggal (Opsional) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Tanggal Penerapan</label>
                    <input type="text" name="target_tanggal" id="edit-target-tanggal" placeholder="Contoh: Minggu ke-2 Semester 1" maxlength="50"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
                    <p class="text-[10px] text-slate-400 mt-0.5">Opsional (perkiraan minggu pembelajaran).</p>
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
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-rose-500 font-semibold">Simbol Dilarang</span>
                        <span id="counter-edit-deskripsi" class="text-[10px] text-slate-400 font-normal">0/500</span>
                    </div>
                </label>
                <textarea name="deskripsi" id="edit-deskripsi" rows="3" maxlength="500"
                          oninput="sanitizeDeskripsiTp(this, 'counter-edit-deskripsi', 500)"
                          class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary"></textarea>
                <p class="text-[10px] text-slate-400 mt-0.5">Maksimal 500 karakter. Hanya huruf, angka, dan tanda baca kalimat (.,-;:()"/!?).</p>
            </div>

            <!-- Komponen Checklist Interaktif -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <label class="block text-xs font-bold text-slate-800">Komponen Modul Ajar:</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-primary">
                        <input type="checkbox" name="tujuan" id="edit-check-tujuan" value="1" class="rounded text-primary">
                        <span>Tujuan Belajar</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-primary">
                        <input type="checkbox" name="video" id="edit-check-video" value="1" class="rounded text-primary">
                        <span>Video Animasi</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-primary">
                        <input type="checkbox" name="soal_proyektor" id="edit-check-soal" value="1" class="rounded text-primary">
                        <span>Soal Proyektor</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer bg-white p-2 rounded-lg border border-slate-200 hover:border-primary">
                        <input type="checkbox" name="lkpd" id="edit-check-lkpd" value="1" class="rounded text-primary">
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
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-primary">
                <p class="text-[10px] text-slate-400 mt-1">Pisahkan antar label dengan koma. Hanya huruf dan angka, dilarang simbol.</p>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Ganti Berkas Modul <span class="text-slate-500 font-normal">(Opsional - Hanya PDF / Word)</span>
                </label>
                <input type="file" name="file_rpp" id="edit-file-rpp" accept=".pdf,.docx,.doc,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword"
                       onchange="validateRppFileInput(this)"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-primary hover:file:bg-sky-100 cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-1">Format berkas yang didukung hanya: <strong>.pdf</strong>, <strong>.docx</strong>, atau <strong>.doc</strong> (Maks. 10MB).</p>
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeModalEditRpp()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-edit-draft" name="action" value="draft" class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    Simpan Draf
                </button>
                <button type="submit" id="btn-edit-publish" name="action" value="publish" class="px-5 py-2 text-xs font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-sm transition flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-paper-plane text-[10px]"></i> Ajukan ke Admin
                </button>
            </div>
        </form>
    </div>
</div>

