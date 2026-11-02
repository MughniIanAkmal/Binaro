<!-- ================= JAVASCRIPT MODALS & KONTROL RPP ================= -->
<script>
    const MENIT_PER_JP = 45; // Standar 1 JP = 45 Menit
    const JAM_TUTUP_MAX_MINUTES = 12 * 60; // 12:00 WIB (Jam Tutup Sekolah)

    function formatTimeHM(h, m) {
        return `${String(h).padStart(2, '0')}.${String(m).padStart(2, '0')}`;
    }

    /**
     * Hitung durasi, validasi aturan roster jadwal, dan konversi ke JP otomatis
     */
    function evaluateJadwalTimes(hMulai, mMulai, hSelesai, mSelesai) {
        if (isNaN(hMulai) || isNaN(mMulai) || isNaN(hSelesai) || isNaN(mSelesai)) {
            return { valid: true, error: null, warning: null, durasi: 0, jp: null };
        }

        if (hMulai > 23 || mMulai > 59 || hSelesai > 23 || mSelesai > 59) {
            return {
                valid: false,
                error: 'Format nilai jam (00-23) atau menit (00-59) tidak valid.',
                warning: null,
                durasi: 0,
                jp: null
            };
        }

        const totalMulai = (hMulai * 60) + mMulai;
        const totalSelesai = (hSelesai * 60) + mSelesai;
        const durasi = totalSelesai - totalMulai;

        const strMulai = formatTimeHM(hMulai, mMulai);
        const strSelesai = formatTimeHM(hSelesai, mSelesai);

        // 1. Cek Jam Terbalik / Mundur
        if (durasi <= 0) {
            return {
                valid: false,
                error: `Jam pelajaran terbalik! Jam mulai (${strMulai}) tidak boleh lebih lambat atau sama dengan jam selesai (${strSelesai}).`,
                warning: null,
                durasi: durasi,
                jp: null
            };
        }

        // 2. Cek Durasi Terlalu Singkat (< 1 JP)
        if (durasi < MENIT_PER_JP) {
            return {
                valid: false,
                error: `Durasi pembelajaran (${durasi} menit) terlalu singkat! Minimal 1 JP (${MENIT_PER_JP} menit).`,
                warning: null,
                durasi: durasi,
                jp: null
            };
        }

        // 3. Cek Durasi Terlalu Panjang (Maksimal 5 JP / 225 menit)
        if (durasi > 225) {
            return {
                valid: false,
                error: `Durasi pembelajaran (${durasi} menit) melebihi batas wajar 1 modul ajar (maksimal 5 JP / 225 menit).`,
                warning: null,
                durasi: durasi,
                jp: null
            };
        }

        // 4. Cek Melebihi Jam Tutup Sekolah
        let warning = null;
        if (totalSelesai > JAM_TUTUP_MAX_MINUTES) {
            warning = `Perhatian: Jam selesai (${strSelesai}) melewati jam tutup operasional sekolah (12.00 WIB).`;
        }

        // 5. Hitung JP Otomatis (1 JP = 45 menit)
        const jp = Math.max(1, Math.round(durasi / MENIT_PER_JP));

        return {
            valid: true,
            error: null,
            warning: warning,
            durasi: durasi,
            jp: jp,
            jpText: `${jp} JP`
        };
    }

    /**
     * Ekstraksi Hari, Jam Mulai, dan Jam Selesai dari teks target jadwal
     */
    function parseJadwalText(text) {
        if (!text) return null;

        let hari = '';
        const matchHari = text.match(/(Senin|Selasa|Rabu|Kamis|Jumat|Sabtu)/i);
        if (matchHari) {
            hari = matchHari[1].charAt(0).toUpperCase() + matchHari[1].slice(1).toLowerCase();
        }

        const matchJam = text.match(/(\d{1,2})[:.](\d{2})\s*[-–]\s*(\d{1,2})[:.](\d{2})/);
        if (matchJam) {
            return {
                hari: hari,
                hMulai: parseInt(matchJam[1], 10),
                mMulai: parseInt(matchJam[2], 10),
                hSelesai: parseInt(matchJam[3], 10),
                mSelesai: parseInt(matchJam[4], 10),
                rawMulai: `${String(matchJam[1]).padStart(2, '0')}:${matchJam[2]}`,
                rawSelesai: `${String(matchJam[3]).padStart(2, '0')}:${matchJam[4]}`
            };
        }

        return { hari: hari, hMulai: NaN, mMulai: NaN, hSelesai: NaN, mSelesai: NaN };
    }

    /* ========================================================
       MODAL TAMBAH: SINKRONISASI JADWAL & PERHITUNGAN JP
       ======================================================== */
    function syncTambahJadwalFromPickers() {
        const hari = document.getElementById('tambah-hari').value;
        const jamMulai = document.getElementById('tambah-jam-mulai').value;
        const jamSelesai = document.getElementById('tambah-jam-selesai').value;
        const targetInput = document.getElementById('tambah-target-jadwal');
        const alokasiInput = document.getElementById('tambah-alokasi-waktu');
        const badgeJp = document.getElementById('tambah-badge-jp');
        const errBox = document.getElementById('tambah-jadwal-error-box');
        const errText = document.getElementById('tambah-jadwal-error-text');
        const btnPublish = document.getElementById('btn-tambah-publish');
        const btnDraft = document.getElementById('btn-tambah-draft');

        if (jamMulai && jamSelesai) {
            const [hM, mM] = jamMulai.split(':').map(n => parseInt(n, 10));
            const [hS, mS] = jamSelesai.split(':').map(n => parseInt(n, 10));

            // Format tampilan string: Hari, HH.MM - HH.MM WIB
            const strM = formatTimeHM(hM, mM);
            const strS = formatTimeHM(hS, mS);
            targetInput.value = `${hari ? hari + ', ' : ''}${strM} - ${strS} WIB`;
            updateLengthCounter(targetInput, 'counter-tambah-jadwal', 50);

            const result = evaluateJadwalTimes(hM, mM, hS, mS);

            if (!result.valid) {
                errText.textContent = result.error;
                errBox.classList.remove('hidden');
                errBox.className = 'p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold flex items-start gap-2';
                alokasiInput.value = 'Tidak Valid';
                badgeJp.innerHTML = `<i class="fas fa-triangle-exclamation text-rose-600"></i> Jam Terbalik / Salah`;
                badgeJp.className = 'text-[10px] text-rose-800 font-bold bg-rose-100 px-1.5 py-0.5 rounded border border-rose-300';
                if (btnPublish) btnPublish.disabled = true;
                if (btnDraft) btnDraft.disabled = true;
                return false;
            }

            // Valid
            if (result.warning) {
                errText.textContent = result.warning;
                errBox.classList.remove('hidden');
                errBox.className = 'p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold flex items-start gap-2';
            } else {
                errBox.classList.add('hidden');
            }

            alokasiInput.value = result.jpText;
            badgeJp.innerHTML = `<i class="fas fa-check-circle text-emerald-600"></i> ${result.jpText} (${result.durasi} Menit &bull; 1 JP = 45 Menit)`;
            badgeJp.className = 'text-[10px] text-emerald-800 font-bold bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-300';
            if (btnPublish) btnPublish.disabled = false;
            if (btnDraft) btnDraft.disabled = false;
            return true;
        } else {
            errBox.classList.add('hidden');
            if (btnPublish) btnPublish.disabled = false;
            if (btnDraft) btnDraft.disabled = false;
        }
    }

    function onTambahTargetJadwalManual(el) {
        updateLengthCounter(el, 'counter-tambah-jadwal', 50);
        const parsed = parseJadwalText(el.value);
        if (parsed && !isNaN(parsed.hMulai) && !isNaN(parsed.hSelesai)) {
            if (parsed.hari) {
                document.getElementById('tambah-hari').value = parsed.hari;
            }
            document.getElementById('tambah-jam-mulai').value = parsed.rawMulai;
            document.getElementById('tambah-jam-selesai').value = parsed.rawSelesai;
            syncTambahJadwalFromPickers();
        }
    }

    function applyRosterToTambah(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) return;

        document.getElementById('tambah-id-jadwal').value = opt.value;

        const mapel = opt.getAttribute('data-mapel');
        const kelas = opt.getAttribute('data-kelas');
        const ruang = opt.getAttribute('data-ruang');
        const hari = opt.getAttribute('data-hari');
        const jam = opt.getAttribute('data-jam');

        if (mapel) document.getElementById('tambah-id-mapel').value = mapel;
        if (kelas) document.getElementById('tambah-id-rooms').value = kelas;
        if (ruang) {
            document.getElementById('tambah-ruang').value = ruang;
            updateLengthCounter(document.getElementById('tambah-ruang'), 'counter-tambah-ruang', 50);
        }

        if (hari) document.getElementById('tambah-hari').value = hari;

        if (jam) {
            const parsed = parseJadwalText(jam);
            if (parsed && !isNaN(parsed.hMulai)) {
                document.getElementById('tambah-jam-mulai').value = parsed.rawMulai;
                document.getElementById('tambah-jam-selesai').value = parsed.rawSelesai;
            }
        }
        syncTambahJadwalFromPickers();
    }

    /* ========================================================
       MODAL EDIT: SINKRONISASI JADWAL & PERHITUNGAN JP
       ======================================================== */
    function syncEditJadwalFromPickers() {
        const hari = document.getElementById('edit-hari').value;
        const jamMulai = document.getElementById('edit-jam-mulai').value;
        const jamSelesai = document.getElementById('edit-jam-selesai').value;
        const targetInput = document.getElementById('edit-target-jadwal');
        const alokasiInput = document.getElementById('edit-alokasi-waktu');
        const badgeJp = document.getElementById('edit-badge-jp');
        const errBox = document.getElementById('edit-jadwal-error-box');
        const errText = document.getElementById('edit-jadwal-error-text');
        const btnPublish = document.getElementById('btn-edit-publish');
        const btnDraft = document.getElementById('btn-edit-draft');

        if (jamMulai && jamSelesai) {
            const [hM, mM] = jamMulai.split(':').map(n => parseInt(n, 10));
            const [hS, mS] = jamSelesai.split(':').map(n => parseInt(n, 10));

            const strM = formatTimeHM(hM, mM);
            const strS = formatTimeHM(hS, mS);
            targetInput.value = `${hari ? hari + ', ' : ''}${strM} - ${strS} WIB`;
            updateLengthCounter(targetInput, 'counter-edit-jadwal', 50);

            const result = evaluateJadwalTimes(hM, mM, hS, mS);

            if (!result.valid) {
                errText.textContent = result.error;
                errBox.classList.remove('hidden');
                errBox.className = 'p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold flex items-start gap-2';
                alokasiInput.value = 'Tidak Valid';
                badgeJp.innerHTML = `<i class="fas fa-triangle-exclamation text-rose-600"></i> Jam Terbalik / Salah`;
                badgeJp.className = 'text-[10px] text-rose-800 font-bold bg-rose-100 px-1.5 py-0.5 rounded border border-rose-300';
                if (btnPublish) btnPublish.disabled = true;
                if (btnDraft) btnDraft.disabled = true;
                return false;
            }

            if (result.warning) {
                errText.textContent = result.warning;
                errBox.classList.remove('hidden');
                errBox.className = 'p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold flex items-start gap-2';
            } else {
                errBox.classList.add('hidden');
            }

            alokasiInput.value = result.jpText;
            badgeJp.innerHTML = `<i class="fas fa-check-circle text-emerald-600"></i> ${result.jpText} (${result.durasi} Menit &bull; 1 JP = 45 Menit)`;
            badgeJp.className = 'text-[10px] text-emerald-800 font-bold bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-300';
            if (btnPublish) btnPublish.disabled = false;
            if (btnDraft) btnDraft.disabled = false;
            return true;
        } else {
            errBox.classList.add('hidden');
            if (btnPublish) btnPublish.disabled = false;
            if (btnDraft) btnDraft.disabled = false;
        }
    }

    function onEditTargetJadwalManual(el) {
        updateLengthCounter(el, 'counter-edit-jadwal', 50);
        const parsed = parseJadwalText(el.value);
        if (parsed && !isNaN(parsed.hMulai) && !isNaN(parsed.hSelesai)) {
            if (parsed.hari) {
                document.getElementById('edit-hari').value = parsed.hari;
            }
            document.getElementById('edit-jam-mulai').value = parsed.rawMulai;
            document.getElementById('edit-jam-selesai').value = parsed.rawSelesai;
            syncEditJadwalFromPickers();
        }
    }

    function applyRosterToEdit(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) return;

        document.getElementById('edit-id-jadwal').value = opt.value;

        const mapel = opt.getAttribute('data-mapel');
        const kelas = opt.getAttribute('data-kelas');
        const ruang = opt.getAttribute('data-ruang');
        const hari = opt.getAttribute('data-hari');
        const jam = opt.getAttribute('data-jam');

        if (mapel) document.getElementById('edit-id-mapel').value = mapel;
        if (kelas) document.getElementById('edit-id-rooms').value = kelas;
        if (ruang) {
            document.getElementById('edit-ruang').value = ruang;
            updateLengthCounter(document.getElementById('edit-ruang'), 'counter-edit-ruang', 50);
        }

        if (hari) document.getElementById('edit-hari').value = hari;

        if (jam) {
            const parsed = parseJadwalText(jam);
            if (parsed && !isNaN(parsed.hMulai)) {
                document.getElementById('edit-jam-mulai').value = parsed.rawMulai;
                document.getElementById('edit-jam-selesai').value = parsed.rawSelesai;
            }
        }
        syncEditJadwalFromPickers();
    }

    /* ========================================================
       FUNGSI SANITASI & VALIDASI UMUM
       ======================================================== */
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
        // Hanya huruf, angka, spasi, dan koma
        const cleaned = el.value.replace(/[^a-zA-Z0-9\s,]/g, '');
        if (el.value !== cleaned) {
            el.value = cleaned;
        }
    }

    function validateRppFileInput(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const fileName = file.name.toLowerCase();
        const validExtensions = ['.pdf', '.docx', '.doc'];
        const isValid = validExtensions.some(ext => fileName.endsWith(ext));
        if (!isValid) {
            alert('Tipe berkas tidak valid! Berkas Modul RPP hanya boleh berformat PDF (.pdf) atau Word (.docx, .doc).');
            input.value = '';
            return false;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran berkas terlalu besar! Maksimal ukuran file adalah 10MB.');
            input.value = '';
            return false;
        }
    }

    /* ========================================================
       MODAL OPEN / CLOSE HANDLERS
       ======================================================== */
    function openModalTambahRpp() {
        document.getElementById('modal-tambah-rpp').classList.remove('hidden');
        sanitizeAlphanumericOnly(document.getElementById('tambah-judul-rpp'), 'counter-tambah-judul', 100);
        sanitizeAlphanumericOnly(document.getElementById('tambah-modul-ke'), 'counter-tambah-modul', 20);
        updateLengthCounter(document.getElementById('tambah-target-jadwal'), 'counter-tambah-jadwal', 50);
        updateLengthCounter(document.getElementById('tambah-ruang'), 'counter-tambah-ruang', 50);
        updateLengthCounter(document.getElementById('tambah-deskripsi'), 'counter-tambah-deskripsi', 500);

        // Jika picker belum diisi, coba inisialisasi dari teks default jika ada
        const targetJadwalVal = document.getElementById('tambah-target-jadwal').value;
        if (targetJadwalVal) {
            onTambahTargetJadwalManual(document.getElementById('tambah-target-jadwal'));
        } else {
            if (!document.getElementById('tambah-hari').value) {
                document.getElementById('tambah-hari').value = 'Senin';
            }
            if (!document.getElementById('tambah-jam-mulai').value) {
                document.getElementById('tambah-jam-mulai').value = '07:00';
            }
            if (!document.getElementById('tambah-jam-selesai').value) {
                document.getElementById('tambah-jam-selesai').value = '08:30';
            }
            syncTambahJadwalFromPickers();
        }
    }

    function closeModalTambahRpp() {
        document.getElementById('modal-tambah-rpp').classList.add('hidden');
    }

    function openModalEditRpp(rpp) {
        document.getElementById('form-edit-rpp').action = `/guru/rpp/${rpp.id_rpp}`;
        document.getElementById('edit-modal-subtitle').innerText = `ID Modul: #${rpp.id_rpp} • ${rpp.fase || 'Fase B'}`;
        document.getElementById('edit-id-mapel').value = rpp.id_mapel || '';
        document.getElementById('edit-id-rooms').value = rpp.id_rooms || '';
        document.getElementById('edit-id-jadwal').value = rpp.id_jadwal || '';
        document.getElementById('edit-judul-rpp').value = rpp.judul_rpp || '';
        document.getElementById('edit-fase').value = rpp.fase || 'Fase B';
        document.getElementById('edit-modul-ke').value = rpp.modul_ke || '';
        document.getElementById('edit-target-jadwal').value = rpp.target_jadwal || '';
        document.getElementById('edit-ruang').value = rpp.ruang || '';
        document.getElementById('edit-target-tanggal').value = rpp.target_tanggal || '';
        document.getElementById('edit-deskripsi').value = rpp.deskripsi || '';

        // Parsing target_jadwal yang ada ke dalam picker hari dan jam
        if (rpp.target_jadwal) {
            const parsed = parseJadwalText(rpp.target_jadwal);
            if (parsed) {
                if (parsed.hari) document.getElementById('edit-hari').value = parsed.hari;
                if (!isNaN(parsed.hMulai)) {
                    document.getElementById('edit-jam-mulai').value = parsed.rawMulai;
                    document.getElementById('edit-jam-selesai').value = parsed.rawSelesai;
                    syncEditJadwalFromPickers();
                } else {
                    document.getElementById('edit-alokasi-waktu').value = rpp.alokasi_waktu || '2 JP';
                }
            }
        } else {
            document.getElementById('edit-alokasi-waktu').value = rpp.alokasi_waktu || '';
        }

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

            // Tampilkan JP dengan detail durasi
            const alokasi = data.alokasi_waktu || '2 JP';
            document.getElementById('detail-alokasi').innerHTML = `<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">${alokasi}</span> <span class="text-slate-400 font-normal">(1 JP = 45 Menit)</span>`;
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

    // Mencegah submit jika jam terbalik atau error
    document.addEventListener('DOMContentLoaded', function () {
        const formTambah = document.getElementById('form-tambah-rpp');
        if (formTambah) {
            formTambah.addEventListener('submit', function (e) {
                const jmMulai = document.getElementById('tambah-jam-mulai').value;
                const jmSelesai = document.getElementById('tambah-jam-selesai').value;
                if (jmMulai && jmSelesai) {
                    const valid = syncTambahJadwalFromPickers();
                    if (!valid) {
                        e.preventDefault();
                        alert('Perbaiki kesalahan Target Roster Jadwal sebelum menyimpan!');
                        document.getElementById('tambah-jam-mulai').focus();
                    }
                }
            });
        }

        const formEdit = document.getElementById('form-edit-rpp');
        if (formEdit) {
            formEdit.addEventListener('submit', function (e) {
                const jmMulai = document.getElementById('edit-jam-mulai').value;
                const jmSelesai = document.getElementById('edit-jam-selesai').value;
                if (jmMulai && jmSelesai) {
                    const valid = syncEditJadwalFromPickers();
                    if (!valid) {
                        e.preventDefault();
                        alert('Perbaiki kesalahan Target Roster Jadwal sebelum menyimpan!');
                        document.getElementById('edit-jam-mulai').focus();
                    }
                }
            });
        }
    });

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

