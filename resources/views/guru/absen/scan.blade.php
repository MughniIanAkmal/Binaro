@extends('layouts.guru')

@section('content')
<style>
    /* Orientasi preview diatur dinamis via JS (fungsi terapkanCermin):
       kamera depan dicerminkan seperti aplikasi selfie, kamera belakang tampil apa adanya */
    #reader video { max-width: 100%; }
    /* Overlay efek scan di atas preview kamera */
    .scan-overlay { position: absolute; inset: 0; pointer-events: none; z-index: 20; }
    .scan-overlay .corner { position: absolute; width: 26px; height: 26px; border: 3px solid #22c55e; }
    .scan-overlay .corner.tl { top: 12px; left: 12px; border-right: none; border-bottom: none; border-top-left-radius: 8px; }
    .scan-overlay .corner.tr { top: 12px; right: 12px; border-left: none; border-bottom: none; border-top-right-radius: 8px; }
    .scan-overlay .corner.bl { bottom: 12px; left: 12px; border-right: none; border-top: none; border-bottom-left-radius: 8px; }
    .scan-overlay .corner.br { bottom: 12px; right: 12px; border-left: none; border-top: none; border-bottom-right-radius: 8px; }
    .scan-laser {
        position: absolute; left: 16px; right: 16px; height: 2px; border-radius: 2px;
        background: linear-gradient(90deg, transparent, #22c55e, transparent);
        box-shadow: 0 0 10px rgba(34, 197, 94, .9);
        animation: scanLaser 2.2s ease-in-out infinite;
    }
    @keyframes scanLaser { 0%, 100% { top: 16px; } 50% { top: calc(100% - 18px); } }
</style>

<div class="space-y-6 max-w-3xl mx-auto">
    <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Guru / Absensi</p>
        <h1 class="text-xl font-bold text-slate-900">Scan Absen Siswa</h1>
        <p class="text-xs text-slate-500 mt-1">Arahkan QR kartu siswa ke kamera. Jika QR tidak terbaca, ketik kodenya secara manual.</p>
    </div>

    @php
        $tercatatCount = $hariIni->count();
        $belumCount = max($totalSiswa - $tercatatCount, 0);
        $persen = $totalSiswa ? round($tercatatCount / $totalSiswa * 100) : 0;
    @endphp

    <!-- 1. Rekap absen (di atas kamera) -->
    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fas fa-chart-pie text-[#13527D]"></i> Rekap Absen Hari Ini
            </h2>
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-slate-400 font-semibold">{{ now()->translatedFormat('d M Y') }}</span>
                <a href="{{ route('guru.absensi.rekap') }}" class="text-[11px] font-bold text-[#13527D] hover:underline">Lihat rekap <i class="fas fa-arrow-right text-[10px]"></i></a>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-slate-50 border border-slate-100 rounded-xl py-3">
                <p class="text-xl font-black text-slate-900">{{ $totalSiswa }}</p>
                <p class="text-[10px] text-slate-500 font-semibold mt-0.5">Total Siswa</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl py-3">
                <p id="statTercatat" class="text-xl font-black text-emerald-600">{{ $tercatatCount }}</p>
                <p class="text-[10px] text-emerald-600/70 font-semibold mt-0.5">Sudah Absen</p>
                <p class="text-[10px] text-emerald-600/60 mt-0.5">Hadir {{ $hadirCount }} &bull; Izin/Sakit {{ $izinSakitCount }}</p>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-xl py-3">
                <p id="statBelum" class="text-xl font-black text-amber-600">{{ $belumCount }}</p>
                <p class="text-[10px] text-amber-600/70 font-semibold mt-0.5">Belum Absen</p>
            </div>
        </div>
        <div class="h-2 bg-slate-100 rounded-full overflow-hidden mt-4">
            <div id="rekapBar" class="h-full bg-[#13527D] rounded-full transition-all" style="width: {{ $persen }}%"></div>
        </div>
        <p id="rekapPersen" data-total="{{ $totalSiswa }}" class="text-[11px] text-slate-400 mt-1.5">{{ $persen }}% siswa sudah absen hari ini</p>
    </section>

    <!-- 2. Kamera scan -->
    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-bold text-sm text-slate-900 mb-1 flex items-center gap-2">
            <i class="fas fa-camera text-[#13527D]"></i> Kamera Scan
        </h2>
        <p class="text-[11px] text-slate-400 mb-4">Arahkan QR di kartu siswa ke dalam bingkai hijau.</p>
        <div class="w-full max-w-[320px] mx-auto mb-2 relative">
            <div id="reader" class="w-full h-[200px] rounded-xl overflow-hidden bg-slate-900 relative"></div>
            <div class="scan-overlay" aria-hidden="true">
                <span class="corner tl"></span>
                <span class="corner tr"></span>
                <span class="corner bl"></span>
                <span class="corner br"></span>
                <div class="scan-laser"></div>
            </div>
        </div>
        <button type="button" id="btnGantiKamera"
            class="w-full max-w-[320px] mx-auto mb-4 flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-slate-500 border border-slate-200 rounded-lg hover:border-[#13527D] hover:text-[#13527D] transition">
            <i class="fas fa-camera-rotate"></i> Ganti kamera
        </button>
        <div id="hasil" role="status" aria-live="polite"
            class="px-3 py-2.5 rounded-xl text-xs font-bold text-center bg-slate-50 text-slate-400 min-h-[44px] flex items-center justify-center">Arahkan QR ke kamera.</div>
    </section>

    <!-- 3. Masukkan kode manual (di bawah kamera) -->
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

    <!-- 4. Input izin / sakit oleh guru -->
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

    <!-- 5. Data yang sudah absen (di bawah fitur kode) -->
    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h2 class="font-bold text-sm text-slate-900 mb-3 flex items-center gap-2">
            <i class="fas fa-clipboard-check text-emerald-500"></i> Sudah Absen
            <span id="logCount" class="ml-auto text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 px-2.5 py-1 rounded-full">{{ $tercatatCount }} siswa</span>
        </h2>
        <ul id="log" class="divide-y divide-slate-50">
            @forelse ($hariIni as $a)
            @php
                $st = $a->status ?? 'Hadir';
                $box = $st === 'Izin' ? 'bg-sky-50 text-sky-600' : ($st === 'Sakit' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600');
                $jamCls = $st === 'Izin' ? 'text-sky-600' : ($st === 'Sakit' ? 'text-amber-600' : 'text-emerald-600');
            @endphp
            <li class="flex justify-between items-center gap-3 py-2.5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 shrink-0 {{ $box }} rounded-xl flex items-center justify-center text-sm">
                        <i class="fas fa-user-check"></i>
                    </span>
                    <div>
                        <div class="font-bold text-xs text-slate-900">{{ $a->siswa->nama_siswa }}</div>
                        <div class="text-[11px] text-slate-400">Kelas {{ $a->siswa->nama_kelas }} &bull; {{ strtolower($st) }}</div>
                    </div>
                </div>
                <div class="text-xs font-bold {{ $jamCls }} tabular-nums">{{ $a->waktu_absen?->format('H:i') }}</div>
            </li>
            @empty
            <li class="py-6 text-center text-xs text-slate-400">Belum ada yang absen hari ini.</li>
            @endforelse
        </ul>
        <p id="logEmpty" @if ($hariIni->isNotEmpty()) hidden @endif class="text-[11px] text-slate-400 mt-1">Hasil scan akan muncul di sini secara otomatis.</p>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
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
            if (scanner) scanner.pause();
            setTimeout(function() { if (scanner) scanner.resume(); }, 3000);
        }
    } catch (e) {
        tampilkan('err', 'Gagal terhubung ke server.');
    } finally {
        setTimeout(function () { sibuk = false; }, 2000);
    }
}

let scanner = null;
let kameraBelakang = true; // default kamera belakang agar tidak terbalik saat scan kartu siswa
const KONFIG_SCAN = { fps: 10, qrbox: { width: 200, height: 200 }, aspectRatio: 1.6 };

function onScanBerhasil(kode) {
    tampilkan('ok', 'QR terbaca. Menyimpan absen...');
    kirim(kode, 'scan');
}

// Kamera depan dicerminkan (seperti aplikasi selfie: tangan kiri tampil di kiri),
// kamera belakang tampil apa adanya. Hanya mengubah tampilan, tidak mengganggu hasil scan.
function terapkanCermin() {
    const video = document.querySelector('#reader video');
    if (video) video.style.transform = kameraBelakang ? 'none' : 'scaleX(-1)';
}

// Pantau elemen video yang dibuat ulang oleh library setiap ganti/mulai kamera
new MutationObserver(terapkanCermin).observe(document.getElementById('reader'), { childList: true, subtree: true });

async function hentikanScanner() {
    if (scanner) { try { await scanner.stop(); } catch (e) {} }
}

async function mulaiScanner() {
    if (typeof Html5Qrcode === 'undefined') {
        tampilkan('err', 'Library scanner belum termuat. Periksa koneksi internet lalu muat ulang halaman.');
        return;
    }
    // 1. Coba langsung kamera belakang/depan via facingMode (label kamera masih kosong sebelum izin diberikan)
    try {
        if (!scanner) scanner = new Html5Qrcode('reader');
        await hentikanScanner();
        await scanner.start({ facingMode: kameraBelakang ? 'environment' : 'user' }, KONFIG_SCAN, onScanBerhasil, function () {});
        terapkanCermin();
        tampilkan('', 'Kamera ' + (kameraBelakang ? 'belakang' : 'depan') + ' aktif. Arahkan QR ke kotak kamera.');
        return;
    } catch (error) {
        // lanjut ke fallback daftar perangkat di bawah (untuk browser lama)
    }
    // 2. Fallback: pilih dari daftar perangkat
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

// ---- form izin / sakit ----
const ENDPOINT_IZIN = @json(route('guru.absen.izin'));
const DAFTAR_SISWA = @json($daftarNama);
const elIzinHasil = document.getElementById('izinHasil');
const wrapIzinKet = document.getElementById('wrapIzinKeterangan');

function izinJenisTerpilih() {
    const r = document.querySelector('input[name="izinJenis"]:checked');
    return r ? r.value : 'Sakit';
}

document.querySelectorAll('input[name="izinJenis"]').forEach(function (r) {
    r.addEventListener('change', function () {
        wrapIzinKet.classList.toggle('hidden', izinJenisTerpilih() !== 'Izin');
    });
});

function izinPesan(tipe, pesan) {
    const base = 'px-3 py-2.5 rounded-xl text-xs font-bold text-center ';
    elIzinHasil.classList.remove('hidden');
    elIzinHasil.className = base + (tipe === 'ok' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600');
    elIzinHasil.textContent = pesan;
}

function cariSiswaDiDaftar(namaInput) {
    const m = namaInput.match(/\((\d+)\)\s*$/);
    if (m) return DAFTAR_SISWA.find(function (s) { return s.nisn === m[1]; }) || null;
    const rendah = namaInput.toLowerCase();
    const cocok = DAFTAR_SISWA.filter(function (s) { return (s.nama || '').toLowerCase() === rendah; });
    if (cocok.length === 1) return cocok[0];
    if (cocok.length > 1) return { ambigu: true, jumlah: cocok.length, contoh: cocok[0] };
    return null;
}

document.getElementById('formIzin').addEventListener('submit', async function (e) {
    e.preventDefault();
    const namaInput = document.getElementById('izinNama').value.trim();
    const jenis = izinJenisTerpilih();
    const keterangan = document.getElementById('izinKeterangan').value.trim();

    if (!namaInput) { izinPesan('err', 'Isi nama siswa terlebih dahulu.'); return; }

    const cek = cariSiswaDiDaftar(namaInput);
    if (!cek) { izinPesan('err', 'Nama tidak terdaftar. Isi ulang nama dengan benar sesuai data siswa.'); return; }
    if (cek.ambigu) { izinPesan('err', 'Ada ' + cek.jumlah + ' siswa bernama tersebut. Tulis "Nama (NISN)", contoh: ' + cek.contoh.nama + ' (' + cek.contoh.nisn + ').'); return; }
    if (jenis === 'Izin' && !keterangan) { izinPesan('err', 'Anda memilih keterangan lain: wajib mengisi kolom keterangan.'); return; }

    try {
        const res = await fetch(ENDPOINT_IZIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ nama: namaInput, jenis: jenis, keterangan: keterangan }),
        });
        const data = await res.json();
        if (res.ok && data.status === 'ok') {
            izinPesan('ok', data.message);
            tambahLog(data);
            document.getElementById('formIzin').reset();
            wrapIzinKet.classList.add('hidden');
        } else {
            izinPesan('err', data.message || 'Gagal menyimpan izin.');
        }
    } catch (err) {
        izinPesan('err', 'Gagal terhubung ke server.');
    }
});
</script>
@endsection
