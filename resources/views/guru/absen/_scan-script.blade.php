const ENDPOINT = @json(route('guru.absen.scan'));
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const elHasil = document.getElementById('hasil');
const elLog = document.getElementById('log');
const elLogEmpty = document.getElementById('logEmpty');
let sibuk = false;

function tampilkan(tipe, pesan) {
    const base = 'px-3 py-2.5 rounded-xl text-xs font-bold text-center min-h-[44px] flex items-center justify-center ';
    if (tipe === 'ok') elHasil.className = base + 'bg-emerald-50 text-emerald-600';
    else if (tipe === 'err') elHasil.className = base + 'bg-rose-50 text-rose-600';
    else elHasil.className = base + 'bg-slate-50 text-slate-400';
    elHasil.textContent = pesan;
}

const STATUS_STYLE = {
    Hadir: { box: 'bg-emerald-50 text-emerald-600', jam: 'text-emerald-600', label: 'hadir' },
    Terlambat: { box: 'bg-amber-50 text-amber-600', jam: 'text-amber-600', label: 'terlambat' },
    Izin:  { box: 'bg-sky-50 text-sky-600', jam: 'text-sky-600', label: 'izin' },
    Sakit: { box: 'bg-amber-50 text-amber-600', jam: 'text-amber-600', label: 'sakit' },
};

function tambahLog(data) {
    const st = STATUS_STYLE[data.statusAbsen] || STATUS_STYLE.Hadir;
    const li = document.createElement('li');
    li.className = 'flex justify-between items-center gap-3 py-2.5';
    const kiri = document.createElement('div');
    kiri.className = 'flex items-center gap-3';
    const ikon = document.createElement('span');
    ikon.className = 'w-9 h-9 shrink-0 rounded-xl flex items-center justify-center text-sm ' + st.box;
    ikon.innerHTML = '<i class="fas fa-user-check"></i>';
    const teks = document.createElement('div');
    const nama = document.createElement('div');
    nama.className = 'font-bold text-xs text-slate-900';
    nama.textContent = data.siswa.nama;
    const info = document.createElement('div');
    info.className = 'text-[11px] text-slate-400';
    info.textContent = 'Kelas ' + data.siswa.kelas + ' • ' + st.label;
    teks.appendChild(nama); teks.appendChild(info);
    kiri.appendChild(ikon); kiri.appendChild(teks);
    const jam = document.createElement('div');
    jam.className = 'text-xs font-bold tabular-nums ' + st.jam;
    jam.textContent = data.jam;
    li.appendChild(kiri); li.appendChild(jam);
    elLog.prepend(li);
    elLogEmpty.hidden = true;
    naikkanRekap();
}

function naikkanRekap() {
    const elT = document.getElementById('statTercatat');
    const elB = document.getElementById('statBelum');
    const bar = document.getElementById('rekapBar');
    const ket = document.getElementById('rekapPersen');
    const cnt = document.getElementById('logCount');
    if (!elT || !ket || !ket.dataset.total) return;
    const total = parseInt(ket.dataset.total, 10) || 0;
    const tercatat = parseInt(elT.textContent, 10) + 1 || 1;
    elT.textContent = tercatat;
    if (elB) elB.textContent = Math.max(total - tercatat, 0);
    if (bar) bar.style.width = (total ? Math.round(tercatat / total * 100) : 0) + '%';
    ket.textContent = (total ? Math.round(tercatat / total * 100) : 0) + '% siswa sudah absen hari ini';
    if (cnt) cnt.textContent = tercatat + ' siswa';
}

async function kirim(kode, metode) {
    if (metode === 'scan' && sibuk) return;
    sibuk = true;
    try {
        const res = await fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ kode: kode, metode: metode }),
        });
        const data = await res.json();
        const sukses = res.ok && (data.status === 'ok' || data.status === 'duplikat');
        tampilkan(sukses ? 'ok' : 'err', data.message || 'Terjadi kesalahan.');
        if (data.status === 'ok') {
            tambahLog(data);
            if (scanner && typeof scanner.pause === 'function') { try { scanner.pause(); } catch (e) {} }
            setTimeout(function() { if (scanner && typeof scanner.resume === 'function') { try { scanner.resume(); } catch (e) {} } }, 3000);
        }
    } catch (e) {
        tampilkan('err', 'Gagal terhubung ke server.');
    } finally {
        setTimeout(function () { sibuk = false; }, 2000);
    }
}

let scanner = null;
let kameraBelakang = true;
const KONFIG_SCAN = { fps: 10, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0 };
if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
    // Batasi ke QR Code saja agar deteksi lebih cepat dan tidak salah baca barcode lain.
    KONFIG_SCAN.formatsToSupport = [Html5QrcodeSupportedFormats.QR_CODE];
}

function onScanBerhasil(kode) {
    const bersih = (kode || '').toString().trim();
    if (!bersih) return;
    tampilkan('ok', 'QR terbaca. Menyimpan absen...');
    kirim(bersih, 'scan');
}

function terapkanCermin() {
    const video = document.querySelector('#reader video');
    if (video) video.style.transform = kameraBelakang ? 'none' : 'scaleX(-1)';
}

new MutationObserver(terapkanCermin).observe(document.getElementById('reader'), { childList: true, subtree: true });

async function hentikanScanner() {
    if (scanner) { try { await scanner.stop(); } catch (e) {} }
}

async function mulaiScanner() {
    if (typeof Html5Qrcode === 'undefined') {
        tampilkan('err', 'Library scanner belum termuat. Periksa koneksi internet lalu muat ulang halaman.');
        return;
    }
    if (typeof window.isSecureContext !== 'undefined' && !window.isSecureContext) {
        tampilkan('err', 'Kamera diblokir browser karena akses via HTTP. Buka via HTTPS atau localhost, atau gunakan kode manual di bawah.');
        return;
    }
    try {
        if (!scanner) scanner = new Html5Qrcode('reader');
        await hentikanScanner();
        await scanner.start({ facingMode: kameraBelakang ? 'environment' : 'user' }, KONFIG_SCAN, onScanBerhasil, function () {});
        terapkanCermin();
        tampilkan('', 'Kamera ' + (kameraBelakang ? 'belakang' : 'depan') + ' aktif. Arahkan QR ke kotak kamera.');
        return;
    } catch (error) {
        // Fallback ke daftar perangkat untuk browser lama.
    }
    try {
        if (!scanner) scanner = new Html5Qrcode('reader');
        const daftar = await Html5Qrcode.getCameras();
        if (!daftar.length) throw new Error('Tidak ada kamera.');
        let target = daftar[0];
        const cocok = daftar.find(function (item) {
            const label = item.label || '';
            return kameraBelakang ? /back|rear|environment|belakang/i.test(label) : /front|user|selfie|depan/i.test(label);
        });
        if (cocok) target = cocok;
        else if (kameraBelakang && daftar.length > 1) target = daftar[daftar.length - 1];
        await hentikanScanner();
        await scanner.start(target.id, KONFIG_SCAN, onScanBerhasil, function () {});
        terapkanCermin();
        tampilkan('', 'Kamera aktif. Arahkan QR ke kotak kamera.');
    } catch (e) {
        tampilkan('err', 'Kamera tidak bisa dibuka. Izinkan akses kamera pada browser, atau gunakan kode manual.');
    }
}
mulaiScanner();

document.getElementById('btnGantiKamera').addEventListener('click', async function () {
    kameraBelakang = !kameraBelakang;
    await mulaiScanner();
});

document.getElementById('formManual').addEventListener('submit', function (e) {
    e.preventDefault();
    const input = document.getElementById('kodeManual');
    const kode = input.value.trim();
    if (!kode) { tampilkan('err', 'Ketik kode unik dulu.'); return; }
    kirim(kode, 'manual');
    input.value = '';
});