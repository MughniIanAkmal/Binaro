<section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h2 class="font-bold text-sm text-slate-900 mb-1 flex items-center gap-2">
        <i class="fas fa-keyboard text-amber-500"></i> Masukkan Kode QR
    </h2>
    <p class="text-[11px] text-slate-400 mb-4">Jika QR tidak dapat di-scan, ketik kode unik yang tercetak di kartu siswa.</p>
    <form id="formManual" autocomplete="off" class="flex flex-col sm:flex-row gap-2">
        <input type="text" id="kodeManual" placeholder="Contoh: QR-XXXXXXXX" aria-label="Kode unik siswa"
            class="flex-1 min-w-0 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs uppercase font-mono focus:outline-none focus:border-[#13527D] focus:bg-white transition">
        <button class="px-6 py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl transition" type="submit">
            <i class="fas fa-check mr-1.5"></i>Absen
        </button>
    </form>
</section>

<section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h2 class="font-bold text-sm text-slate-900 mb-1 flex items-center gap-2">
        <i class="fas fa-notes-medical text-sky-500"></i> Input Izin / Sakit
    </h2>
    <p class="text-[11px] text-slate-400 mb-4">Isi nama siswa sesuai data terdaftar. Hasilnya langsung tercatat dan muncul di rekap absensi.</p>
    <form id="formIzin" autocomplete="off" class="space-y-3 text-xs" novalidate>
        <div>
            <label class="block font-bold text-slate-700 mb-1.5">Nama Siswa</label>
            <input type="text" id="izinNama" list="daftarNamaSiswa" placeholder="Ketik nama siswa…"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition">
            <datalist id="daftarNamaSiswa">
                @foreach ($daftarNama as $n)
                <option value="{{ $n['nama'] }}">{{ $n['kelas'] }} ({{ $n['nisn'] }})</option>
                @endforeach
            </datalist>
            <p class="text-[10px] text-slate-400 mt-1">Pilih dari daftar. Jika ada nama kembar, tulis “Nama (NISN)”.</p>
        </div>
        <div>
            <span class="block font-bold text-slate-700 mb-1.5">Jenis</span>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="izinJenis" value="Sakit" class="peer sr-only" checked>
                    <span class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 font-bold text-slate-500 peer-checked:border-amber-400 peer-checked:bg-amber-50 peer-checked:text-amber-700 transition">
                        <i class="fas fa-plus-circle"></i> Sakit
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="izinJenis" value="Izin" class="peer sr-only">
                    <span class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 font-bold text-slate-500 peer-checked:border-sky-400 peer-checked:bg-sky-50 peer-checked:text-sky-700 transition">
                        <i class="fas fa-info-circle"></i> Lainnya
                    </span>
                </label>
            </div>
        </div>
        <div id="wrapIzinKeterangan" class="hidden">
            <label class="block font-bold text-slate-700 mb-1.5">Keterangan <span class="text-rose-500">*</span></label>
            <textarea id="izinKeterangan" rows="2" maxlength="255" placeholder="Contoh: Izin mengikuti lomba kecamatan…"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#13527D] focus:bg-white transition"></textarea>
        </div>
        <p id="izinHasil" class="hidden px-3 py-2.5 rounded-xl text-xs font-bold text-center"></p>
        <button type="submit" class="w-full py-2.5 bg-[#13527D] hover:bg-[#0E3D5D] text-white text-xs font-bold rounded-xl transition">
            <i class="fas fa-save mr-1.5"></i>Simpan Izin
        </button>
    </form>
</section>