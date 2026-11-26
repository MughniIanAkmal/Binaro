<!-- ================= MODAL BUAT MODUL AJAR (RPP) ================= -->
<div id="modal-tambah-rpp"
     class="fixed inset-0 z-50 hidden"
     aria-modal="true" role="dialog" aria-labelledby="modal-tambah-title">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeModalTambahRpp()"></div>

    {{-- Dialog --}}
    <div class="relative flex items-center justify-center min-h-full p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl flex flex-col max-h-[92vh]"
             style="animation: slideUp .2s ease">

            {{-- ── Header ── --}}
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div style="width:2rem;height:2rem;border-radius:.5rem;background:#eff6ff;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-file-circle-plus" style="color:var(--color-primary);font-size:.8rem"></i>
                    </div>
                    <div>
                        <h3 id="modal-tambah-title" style="font-size:.8rem;font-weight:800;color:var(--color-ink)">
                            Buat Modul Ajar Baru
                        </h3>
                        <p style="font-size:.65rem;color:var(--color-ink-muted)">
                            Kurikulum Merdeka · {{ $guru->nama_guru ?? 'Guru Pengajar' }}
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeModalTambahRpp()"
                        style="width:1.75rem;height:1.75rem;border-radius:.4rem;border:1px solid var(--color-line);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--color-ink-muted);cursor:pointer;font-size:.75rem"
                        aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- ── Form ── --}}
            <form id="form-tambah-rpp"
                  action="{{ route('guru.rpp.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="overflow-y-auto flex-1"
                  style="padding:1.25rem;display:flex;flex-direction:column;gap:1rem">
                @csrf
                <input type="hidden" name="id_guru" value="{{ $guru->id_guru ?? 1 }}">
                <input type="hidden" name="id_jadwal" id="tambah-id-jadwal" value="">
                <input type="hidden" name="from_modal" value="1">

                {{-- Mapel + Kelas --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                    <div>
                        <label class="rpp-modal-label">
                            Mata Pelajaran <span class="text-rose-500">*</span>
                        </label>
                        <select name="id_mapel" id="tambah-id-mapel" required class="rpp-modal-input">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($mapelList as $mapel)
                                <option value="{{ $mapel->id_mapel }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="rpp-modal-label">Kelas</label>
                        <select name="id_rooms" id="tambah-id-rooms" class="rpp-modal-input">
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id_rooms }}" data-fase="{{ $kelas->fase }}">
                                    {{ $kelas->pararel ?? 'Kelas '.$kelas->id_rooms }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Bab --}}
                <div>
                    <label class="rpp-modal-label">
                        Bab yang Diajarkan <span class="text-rose-500">*</span>
                    </label>
                    <select name="id_bab" id="tambah-id-bab" required class="rpp-modal-input">
                        <option value="">-- Pilih Bab --</option>
                        @foreach($babList as $bab)
                            <option value="{{ $bab->id_bab }}"
                                    data-mapel="{{ $bab->id_mapel }}"
                                    {{ $rppPerBab->has($bab->id_bab) ? 'disabled' : '' }}>
                                {{ $bab->mataPelajaran->nama_mapel ?? '' }} — {{ $bab->nama_bab }}
                                {{ $rppPerBab->has($bab->id_bab) ? ' (sudah ada RPP)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="rpp-modal-hint">Bab yang sudah ada RPP-nya tidak dapat dipilih ulang.</p>
                </div>

                {{-- Judul --}}
                <div>
                    <label class="rpp-modal-label" style="display:flex;align-items:center;justify-content:space-between">
                        <span>Topik / Judul Modul <span class="text-rose-500">*</span></span>
                        <span id="counter-tambah-judul" class="rpp-modal-hint" style="margin:0">0/100</span>
                    </label>
                    <input type="text" name="judul_rpp" id="tambah-judul-rpp"
                           required maxlength="100"
                           placeholder="Contoh: Konsep Pecahan Senilai"
                           oninput="sanitizeAlphanumericOnly(this,'counter-tambah-judul',100)"
                           class="rpp-modal-input">
                </div>

                {{-- Fase + Modul + Alokasi --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem">
                    <div>
                        <label class="rpp-modal-label">Fase</label>
                        <input type="hidden" name="fase" id="tambah-fase" value="">
                        <div id="tambah-fase-badge" class="rpp-modal-input"
                             style="background:#f0fdf4;border-color:#bbf7d0;color:#16a34a;font-weight:700;cursor:default">
                            Otomatis
                        </div>
                    </div>
                    <div>
                        <label class="rpp-modal-label" style="display:flex;align-items:center;justify-content:space-between">
                            <span>Nomor Modul</span>
                            <span id="counter-tambah-modul" class="rpp-modal-hint" style="margin:0">0/20</span>
                        </label>
                        <input type="text" name="modul_ke" id="tambah-modul-ke"
                               maxlength="20" placeholder="Modul 1"
                               oninput="sanitizeAlphanumericOnly(this,'counter-tambah-modul',20)"
                               class="rpp-modal-input">
                    </div>
                    <div>
                        <label class="rpp-modal-label" style="display:flex;align-items:center;justify-content:space-between">
                            <span>Alokasi (JP)</span>
                            <span id="tambah-badge-jp" style="font-size:.6rem;font-weight:700;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:.3rem;padding:.1rem .4rem">1JP=35m</span>
                        </label>
                        <input type="text" name="alokasi_waktu" id="tambah-alokasi-waktu"
                               readonly placeholder="Otomatis"
                               class="rpp-modal-input" style="cursor:not-allowed;background:var(--color-canvas)">
                    </div>
                </div>

                {{-- Target Jadwal --}}
                <div style="background:var(--color-canvas);border:1px solid var(--color-line);border-radius:.625rem;padding:.875rem">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.625rem">
                        <i class="fas fa-clock" style="color:var(--color-primary);font-size:.7rem"></i>
                        <span style="font-size:.72rem;font-weight:700;color:var(--color-ink)">
                            Target Roster Jadwal <span class="text-rose-500">*</span>
                        </span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.6rem;margin-bottom:.6rem">
                        <div>
                            <label class="rpp-modal-hint" style="display:block;margin-bottom:.25rem">Hari</label>
                            <select id="tambah-hari" onchange="syncTambahJadwalFromPickers()" class="rpp-modal-input">
                                <option value="">-- Pilih --</option>
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                                    <option value="{{ $hari }}">{{ $hari }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="rpp-modal-hint" style="display:block;margin-bottom:.25rem">Jam Mulai</label>
                            <div style="position:relative">
                                <input type="text" id="tambah-jam-mulai" inputmode="numeric" maxlength="5"
                                       placeholder="07.00"
                                       oninput="maskJamInput(this);syncTambahJadwalFromPickers()"
                                       class="rpp-modal-input" style="padding-right:2.5rem">
                                <span style="position:absolute;right:.625rem;top:50%;transform:translateY(-50%);font-size:.6rem;font-weight:700;color:var(--color-primary)">WIB</span>
                            </div>
                        </div>
                        <div>
                            <label class="rpp-modal-hint" style="display:block;margin-bottom:.25rem">Jam Selesai</label>
                            <div style="position:relative">
                                <input type="text" id="tambah-jam-selesai" inputmode="numeric" maxlength="5"
                                       placeholder="08.30"
                                       oninput="maskJamInput(this);syncTambahJadwalFromPickers()"
                                       class="rpp-modal-input" style="padding-right:2.5rem">
                                <span style="position:absolute;right:.625rem;top:50%;transform:translateY(-50%);font-size:.6rem;font-weight:700;color:var(--color-primary)">WIB</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="rpp-modal-hint" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.25rem">
                            <span>Format tersimpan</span>
                            <span id="counter-tambah-jadwal">0/50</span>
                        </label>
                        <input type="text" name="target_jadwal" id="tambah-target-jadwal"
                               maxlength="50" placeholder="Senin, 07.00–08.30 WIB"
                               oninput="onTambahTargetJadwalManual(this)"
                               class="rpp-modal-input" style="font-family:monospace;font-weight:600">
                    </div>
                    <div id="tambah-jadwal-error-box"
                         class="hidden" style="margin-top:.5rem;padding:.5rem .75rem;background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;display:flex;align-items:flex-start;gap:.5rem;font-size:.68rem;color:#dc2626;font-weight:600">
                        <i class="fas fa-triangle-exclamation" style="margin-top:.1rem;flex-shrink:0"></i>
                        <span id="tambah-jadwal-error-text"></span>
                    </div>
                </div>

                {{-- Ruang + Target Tanggal --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                    <div>
                        <label class="rpp-modal-label">Ruang / Lokasi</label>
                        <input type="text" name="ruang" id="tambah-ruang"
                               maxlength="50" placeholder="Ruang Kelas 4B"
                               oninput="updateLengthCounter(this,'counter-tambah-ruang',50)"
                               class="rpp-modal-input">
                    </div>
                    <div>
                        <label class="rpp-modal-label">Target Tanggal <span class="rpp-modal-hint">(Opsional)</span></label>
                        <input type="text" name="target_tanggal" id="tambah-target-tanggal"
                               maxlength="50" placeholder="Minggu ke-2 Sem. 1"
                               class="rpp-modal-input">
                    </div>
                </div>

                {{-- Deskripsi CP/TP --}}
                <div>
                    <label class="rpp-modal-label" style="display:flex;align-items:center;justify-content:space-between">
                        <span>Capaian Pembelajaran (Deskripsi Ringkas)</span>
                        <span id="counter-tambah-deskripsi" class="rpp-modal-hint" style="margin:0">0/500</span>
                    </label>
                    <textarea name="deskripsi" id="tambah-deskripsi" rows="2"
                              maxlength="500"
                              placeholder="Tuliskan tujuan pembelajaran yang ingin dicapai…"
                              oninput="sanitizeDeskripsiTp(this,'counter-tambah-deskripsi',500)"
                              class="rpp-modal-input" style="height:auto;resize:vertical"></textarea>
                </div>

                {{-- Komponen Wajib --}}
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.625rem;padding:.875rem">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.5rem">
                        <span style="font-size:.72rem;font-weight:700;color:var(--color-ink)">Komponen Wajib</span>
                        <span style="font-size:.6rem;font-weight:700;background:#fecaca;color:#dc2626;border-radius:.3rem;padding:.1rem .4rem;border:1px solid #fca5a5">WAJIB DIAJUKAN</span>
                    </div>
                    <p style="font-size:.65rem;color:var(--color-ink-soft);margin-bottom:.625rem;line-height:1.5">
                        Harus lengkap sebelum RPP bisa diajukan. Draf tetap bisa disimpan walau belum lengkap.
                    </p>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem">
                        @foreach([['tujuan','Tujuan Pembelajaran','Kompetensi + konten'],['langkah','Langkah Pembelajaran','Memahami–merefleksi'],['asesmen','Asesmen','Minimal 2 teknik']] as [$nama,$label,$sub])
                        <label style="display:flex;align-items:flex-start;gap:.5rem;background:#fff;padding:.5rem .625rem;border-radius:.4rem;border:1px solid #fca5a5;cursor:pointer;font-size:.68rem">
                            <input type="checkbox" name="{{ $nama }}" value="1" class="rounded mt-0.5" style="accent-color:var(--color-primary)">
                            <span>
                                <b style="display:block;font-size:.68rem">{{ $label }}</b>
                                <span style="color:var(--color-ink-muted);font-size:.62rem">{{ $sub }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <div style="margin-top:.625rem;padding-top:.625rem;border-top:1px dashed #fca5a5">
                        <span style="display:block;font-size:.68rem;font-weight:700;color:var(--color-ink);margin-bottom:.375rem">
                            Teknik Asesmen yang Digunakan <span style="font-weight:500;color:var(--color-ink-soft)">(pilih minimal 2 saat mengajukan)</span>
                        </span>
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.375rem">
                            @foreach(['Observasi', 'Tes tulis', 'Unjuk kerja', 'Produk', 'Proyek', 'Portofolio', 'Penilaian diri', 'Penilaian sejawat'] as $teknik)
                            <label style="display:flex;align-items:center;gap:.35rem;background:#fff;padding:.35rem .45rem;border-radius:.35rem;border:1px solid #fca5a5;cursor:pointer;font-size:.64rem">
                                <input type="checkbox" name="asesmen_teknik[]" value="{{ $teknik }}" style="accent-color:var(--color-primary)">
                                <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $teknik }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Komponen Disarankan --}}
                <div style="background:var(--color-canvas);border:1px solid var(--color-line);border-radius:.625rem;padding:.875rem">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.5rem">
                        <span style="font-size:.72rem;font-weight:700;color:var(--color-ink)">Komponen Pendukung</span>
                        <span style="font-size:.6rem;font-weight:700;background:#fff;color:var(--color-ink-soft);border-radius:.3rem;padding:.1rem .4rem;border:1px solid var(--color-line)">DISARANKAN</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.4rem">
                        @foreach(['Media','LKPD','Refleksi','Pengayaan','Remedial','Glosarium','Video'] as $k)
                        <label style="display:flex;align-items:center;gap:.375rem;background:#fff;padding:.4rem .5rem;border-radius:.4rem;border:1px solid var(--color-line);cursor:pointer;font-size:.67rem">
                            <input type="checkbox" name="{{ strtolower($k) }}" value="1" class="rounded" style="accent-color:var(--color-primary)">
                            {{ $k }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Ringkasan Kelengkapan --}}
                <div style="background:#fff;border:1px solid var(--color-line);border-radius:.625rem;padding:.875rem">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem">
                        <span style="font-size:.72rem;font-weight:700;color:var(--color-ink)">Ringkasan Kelengkapan</span>
                        <span id="tambah-skor-kelengkapan" style="font-size:.8rem;font-weight:800;color:var(--color-ink)">
                            0<span style="font-size:.65rem;color:var(--color-ink-muted);font-weight:600">/100</span>
                        </span>
                    </div>
                    <div style="height:.35rem;background:var(--color-line);border-radius:9999px;overflow:hidden;margin-bottom:.5rem">
                        <div id="tambah-skor-bar" style="height:100%;background:linear-gradient(90deg,var(--color-primary),#16a34a);border-radius:9999px;transition:width .3s;width:0%"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.67rem">
                        <div style="display:flex;align-items:center;justify-content:space-between;background:var(--color-canvas);border:1px solid var(--color-line);border-radius:.4rem;padding:.35rem .5rem">
                            <span style="font-weight:600;color:var(--color-ink-soft)">Wajib</span>
                            <span id="tambah-count-wajib" style="font-weight:800;color:var(--color-ink)">0/3</span>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;background:var(--color-canvas);border:1px solid var(--color-line);border-radius:.4rem;padding:.35rem .5rem">
                            <span style="font-weight:600;color:var(--color-ink-soft)">Disarankan</span>
                            <span id="tambah-count-disarankan" style="font-weight:800;color:var(--color-ink)">0/7</span>
                        </div>
                    </div>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="rpp-modal-label">Unggah Berkas RPP <span class="rpp-modal-hint">(PDF / Word, maks. 10 MB)</span></label>
                    <input type="file" name="file_rpp" id="tambah-file-rpp"
                           accept=".pdf,.docx,.doc,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword"
                           onchange="validateRppFileInput(this)"
                           style="font-size:.7rem;width:100%">
                </div>

                {{-- Alur info --}}
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.5rem;padding:.625rem .75rem;display:flex;align-items:flex-start;gap:.5rem;font-size:.68rem;color:#92400e;line-height:1.5">
                    <i class="fas fa-circle-info" style="color:var(--color-accent-deep);margin-top:.1rem;flex-shrink:0"></i>
                    <span><b>Alur:</b> Setelah diajukan, RPP berstatus <b>Menunggu Admin</b> dan harus disetujui sebelum bisa digunakan mengajar.</span>
                </div>

                {{-- Footer Actions --}}
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:.625rem;padding-top:.75rem;border-top:1px solid var(--color-line)">
                    <button type="button" onclick="closeModalTambahRpp()"
                            style="padding:.45rem .875rem;font-size:.7rem;font-weight:700;color:var(--color-ink-soft);background:#fff;border:1px solid var(--color-line);border-radius:.5rem;cursor:pointer;font-family:inherit;transition:background .15s"
                            onmouseover="this.style.background='var(--color-canvas)'" onmouseout="this.style.background='#fff'">
                        Batal
                    </button>
                    <button type="submit" id="btn-tambah-draft" name="action" value="draft"
                            style="padding:.45rem .875rem;font-size:.7rem;font-weight:700;color:var(--color-ink);background:var(--color-canvas);border:1px solid var(--color-line);border-radius:.5rem;cursor:pointer;font-family:inherit;transition:background .15s"
                            onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--color-canvas)'">
                        <i class="fas fa-floppy-disk"></i> Simpan Draf
                    </button>
                    <button type="submit" id="btn-tambah-publish" name="action" value="publish"
                            style="padding:.45rem 1rem;font-size:.7rem;font-weight:700;color:#fff;background:var(--color-primary);border:1px solid var(--color-primary);border-radius:.5rem;cursor:pointer;font-family:inherit;transition:background .15s;display:flex;align-items:center;gap:.375rem"
                            onmouseover="this.style.background='var(--color-primary-hover)'" onmouseout="this.style.background='var(--color-primary)'">
                        <i class="fas fa-paper-plane" style="font-size:.65rem"></i> Ajukan ke Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scoped modal styles --}}
<style>
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(1rem); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .rpp-modal-label {
        display: block;
        font-size: .7rem;
        font-weight: 700;
        color: var(--color-ink);
        margin-bottom: .3rem;
    }
    .rpp-modal-input {
        width: 100%;
        padding: .45rem .75rem;
        border: 1px solid var(--color-line);
        border-radius: .5rem;
        background: #fff;
        color: var(--color-ink);
        font-size: .72rem;
        font-family: inherit;
        height: 2.25rem;
        transition: border-color .15s, box-shadow .15s;
        box-sizing: border-box;
    }
    textarea.rpp-modal-input { height: auto; }
    .rpp-modal-input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(19,82,125,.1);
        background: #fff;
    }
    .rpp-modal-hint {
        font-size: .62rem;
        color: var(--color-ink-muted);
        margin-top: .2rem;
        display: block;
    }
</style>
