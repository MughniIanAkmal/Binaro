<!-- ================= JAVASCRIPT MODALS & KONTROL RPP ================= -->
<script>
    const MENIT_PER_JP = 45; // Standar 1 JP = 45 Menit
    const JAM_TUTUP_MAX_MINUTES = 12 * 60; // 12:00 WIB (Jam Tutup Sekolah)

    function formatTimeHM(h, m) {
        return `${String(h).padStart(2, '0')}.${String(m).padStart(2, '0')}`;
    }

    /**
     * Mask input jam 24 jam: hanya angka, titik otomatis (0700 -> 07.00)
     */
    function maskJamInput(el) {
        if (!el) return;
        let digits = el.value.replace(/\D/g, '').slice(0, 4);
        if (digits.length > 2) {
            digits = digits.slice(0, 2) + '.' + digits.slice(2);
        }
        el.value = digits;
    }

    /**
     * Cek apakah teks jam lengkap (HH.MM)
     */
    function isJamLengkap(val) {
        return /^\d{2}[.:]\d{2}$/.test(val || '');
    }

    /**
     * Helper untuk mengisi input jam WIB dengan format HH.MM
     */
    function setSelectTimeValue(inputEl, rawTime) {
        if (!inputEl || !rawTime) return;
        const parts = rawTime.replace('.', ':').split(':');
        if (parts.length < 2) return;
        inputEl.value = `${String(parts[0]).padStart(2, '0')}.${String(parts[1]).padStart(2, '0')}`;
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

        // 4. Durasi wajib kelipatan 1 JP (45 menit) -> tidak boleh 07.00 - 10.09
        if (durasi % MENIT_PER_JP !== 0) {
            const jpBawah = Math.floor(durasi / MENIT_PER_JP);
            const saran = [];
            if (jpBawah >= 1) {
                const t = totalMulai + (jpBawah * MENIT_PER_JP);
                saran.push(`${formatTimeHM(Math.floor(t / 60) % 24, t % 60)} (${jpBawah} JP)`);
            }
            const tAtas = totalMulai + ((jpBawah + 1) * MENIT_PER_JP);
            saran.push(`${formatTimeHM(Math.floor(tAtas / 60) % 24, tAtas % 60)} (${jpBawah + 1} JP)`);
            return {
                valid: false,
                error: `Durasi ${durasi} menit tidak valid! Harus kelipatan ${MENIT_PER_JP} menit (1 JP). Dari jam mulai ${strMulai}, jam selesai yang sesuai: ${saran.join(' atau ')}.`,
                warning: null,
                durasi: durasi,
                jp: null
            };
        }

        // 5. Cek Melebihi Jam Tutup Sekolah
        let warning = null;
        if (totalSelesai > JAM_TUTUP_MAX_MINUTES) {
            warning = `Perhatian: Jam selesai (${strSelesai}) melewati jam tutup operasional sekolah (12.00 WIB).`;
        }

        // 6. Hitung JP Otomatis (1 JP = 45 menit, pasti bulat)
        const jp = durasi / MENIT_PER_JP;

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
        const noteJp = document.getElementById('tambah-note-jp');
        const errBox = document.getElementById('tambah-jadwal-error-box');
        const errText = document.getElementById('tambah-jadwal-error-text');
        const btnPublish = document.getElementById('btn-tambah-publish');
        const btnDraft = document.getElementById('btn-tambah-draft');

        if (isJamLengkap(jamMulai) && isJamLengkap(jamSelesai)) {
            const [hM, mM] = jamMulai.split(/[:.]/).map(n => parseInt(n, 10));
            const [hS, mS] = jamSelesai.split(/[:.]/).map(n => parseInt(n, 10));

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
                badgeJp.innerHTML = `<i class="fas fa-triangle-exclamation text-rose-600"></i> Salah`;
                badgeJp.className = 'text-[10px] text-rose-800 font-bold bg-rose-100 px-1.5 py-0.5 rounded border border-rose-300 whitespace-nowrap';
                if (noteJp) noteJp.innerHTML = `<i class="fas fa-circle-xmark text-rose-500 shrink-0"></i> <span class="text-rose-600 truncate">Perbaiki jam jadwal.</span>`;
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
            badgeJp.innerHTML = `<i class="fas fa-check-circle text-emerald-600"></i> 1JP = 45m`;
            badgeJp.className = 'text-[10px] text-emerald-800 font-bold bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-300 whitespace-nowrap';
            if (noteJp) noteJp.innerHTML = `<i class="fas fa-calculator text-emerald-600 shrink-0"></i> <span class="truncate text-emerald-700 font-medium">Auto: ${result.jpText} (${result.durasi} Menit)</span>`;
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
            setSelectTimeValue(document.getElementById('tambah-jam-mulai'), parsed.rawMulai);
            setSelectTimeValue(document.getElementById('tambah-jam-selesai'), parsed.rawSelesai);
            syncTambahJadwalFromPickers();
        }
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
        const noteJp = document.getElementById('edit-note-jp');
        const errBox = document.getElementById('edit-jadwal-error-box');
        const errText = document.getElementById('edit-jadwal-error-text');
        const btnPublish = document.getElementById('btn-edit-publish');
        const btnDraft = document.getElementById('btn-edit-draft');

        if (isJamLengkap(jamMulai) && isJamLengkap(jamSelesai)) {
            const [hM, mM] = jamMulai.split(/[:.]/).map(n => parseInt(n, 10));
            const [hS, mS] = jamSelesai.split(/[:.]/).map(n => parseInt(n, 10));

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
                badgeJp.innerHTML = `<i class="fas fa-triangle-exclamation text-rose-600"></i> Salah`;
                badgeJp.className = 'text-[10px] text-rose-800 font-bold bg-rose-100 px-1.5 py-0.5 rounded border border-rose-300 whitespace-nowrap';
                if (noteJp) noteJp.innerHTML = `<i class="fas fa-circle-xmark text-rose-500 shrink-0"></i> <span class="text-rose-600 truncate">Perbaiki jam jadwal.</span>`;
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
            badgeJp.innerHTML = `<i class="fas fa-check-circle text-emerald-600"></i> 1JP = 45m`;
            badgeJp.className = 'text-[10px] text-emerald-800 font-bold bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-300 whitespace-nowrap';
            if (noteJp) noteJp.innerHTML = `<i class="fas fa-calculator text-emerald-600 shrink-0"></i> <span class="truncate text-emerald-700 font-medium">Auto: ${result.jpText} (${result.durasi} Menit)</span>`;
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
            setSelectTimeValue(document.getElementById('edit-jam-mulai'), parsed.rawMulai);
            setSelectTimeValue(document.getElementById('edit-jam-selesai'), parsed.rawSelesai);
            syncEditJadwalFromPickers();
        }
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

    function sanitizeDeskripsiTp(el, counterId, maxLen) {
        if (!el) return;
        // Hanya huruf, angka, spasi, baris baru, dan tanda baca kalimat standar: . , - : ; ( ) " ' / ? !
        const cleaned = el.value.replace(/[^a-zA-Z0-9\s.,\-:;()"'/?!\n\r]/g, '');
        if (el.value !== cleaned) {
            el.value = cleaned;
        }
        updateLengthCounter(el, counterId, maxLen);
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
                document.getElementById('tambah-jam-mulai').value = '07.00';
            }
            if (!document.getElementById('tambah-jam-selesai').value) {
                document.getElementById('tambah-jam-selesai').value = '08.30';
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
                    setSelectTimeValue(document.getElementById('edit-jam-mulai'), parsed.rawMulai);
                    setSelectTimeValue(document.getElementById('edit-jam-selesai'), parsed.rawSelesai);
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
        sanitizeDeskripsiTp(document.getElementById('edit-deskripsi'), 'counter-edit-deskripsi', 500);

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
                <a href="/guru/rpp/${data.id_rpp}/download" target="_blank" class="px-4 py-2 bg-[#13527D] hover:bg-[#0E3D5D] text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fas fa-print"></i> Cetak / Simpan PDF
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

    // Mencegah submit jika jam terbalik, tidak lengkap, atau error
    document.addEventListener('DOMContentLoaded', function () {
        function bindValidasiJadwal(formId, prefix, syncFn) {
            const form = document.getElementById(formId);
            if (!form) return;
            form.addEventListener('submit', function (e) {
                const mulai = document.getElementById(`${prefix}-jam-mulai`);
                const selesai = document.getElementById(`${prefix}-jam-selesai`);
                if (!mulai.value && !selesai.value) return;

                if (!isJamLengkap(mulai.value) || !isJamLengkap(selesai.value)) {
                    e.preventDefault();
                    alert('Format jam belum lengkap! Isi dengan format 24 jam, contoh: 07.00 dan 08.30.');
                    (isJamLengkap(mulai.value) ? selesai : mulai).focus();
                    return;
                }
                if (!syncFn()) {
                    e.preventDefault();
                    alert('Perbaiki kesalahan Target Roster Jadwal sebelum menyimpan!');
                    mulai.focus();
                }
            });
        }

        bindValidasiJadwal('form-tambah-rpp', 'tambah', syncTambahJadwalFromPickers);
        bindValidasiJadwal('form-edit-rpp', 'edit', syncEditJadwalFromPickers);
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

