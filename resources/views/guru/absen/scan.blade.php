<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Scan absen siswa</title>
@include('qr-absen._styles')
<style>
.scan-layout{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px;align-items:start}
@media (max-width:900px){.scan-layout{grid-template-columns:1fr}}
.box{padding:18px}.box h2{margin:0 0 12px;font-size:17px}
.progress{padding:16px 18px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:14px}
.progress .txt{flex:1;min-width:220px}.bar{height:7px;background:var(--line);border-radius:99px;overflow:hidden;margin-top:9px}.bar i{display:block;height:100%;background:var(--brand);border-radius:99px}
.tools{display:flex;gap:8px;padding:12px 14px;border-bottom:1px solid var(--line)}
.tools input{width:100%;background:var(--bg);border:1px solid var(--line);border-radius:9px;padding:8px 11px;min-height:38px}
.tablewrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:500px}th{text-align:left;font-size:12px;color:var(--muted);font-weight:600;padding:9px 14px;border-bottom:1px solid var(--line)}td{padding:10px 14px;border-bottom:1px solid var(--line);vertical-align:middle}tr:last-child td{border-bottom:0}.sub{font-size:12px;color:var(--muted)}.nm{font-weight:700}.thumb{width:42px;height:42px;padding:2px;background:#fff;border:1px solid var(--line);border-radius:6px}.thumb img{width:100%;height:100%;display:block}.act{text-align:right;white-space:nowrap}.pick{border:1px solid var(--line);background:#fff;border-radius:9px;padding:6px 10px;font-size:12px;cursor:pointer}.pick:hover{border-color:var(--brand);color:var(--brand)}
#reader{width:100%;max-width:240px;height:140px;margin:0 auto 12px;border-radius:9px;overflow:hidden;background:#0E1424;min-height:140px;position:relative}#reader:empty:before{content:'Arahkan QR ke kamera';position:absolute;inset:0;display:grid;place-items:end center;padding-bottom:8px;color:#fff;font-size:11px;font-weight:700;background:linear-gradient(transparent 70%,rgba(14,20,36,.8))}.hasil{padding:10px 12px;border-radius:10px;font-weight:700;background:var(--bg);color:var(--muted);text-align:center;min-height:44px}.hasil.ok{background:var(--ok-soft);color:var(--ok)}.hasil.err{background:var(--err-soft);color:var(--err)}
.sim-note{font-size:12px;color:var(--muted);margin:0 0 10px}.quick-list{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}.quick{border:1px solid var(--line);background:#fff;border-radius:9px;padding:6px 9px;font-size:12px;cursor:pointer}.quick:hover{border-color:var(--brand);color:var(--brand)}
.manual{margin-top:12px;border:1px solid var(--line);border-radius:10px;padding:9px 11px}.manual summary{cursor:pointer;font-weight:700;font-size:13px}.manual form{display:flex;gap:7px;margin-top:9px}.manual input{flex:1;min-width:0;background:var(--bg);border:1px solid var(--line);border-radius:9px;padding:7px 10px;min-height:36px;text-transform:uppercase;font-size:12px}
.log{list-style:none;margin:0;padding:0}.log li{display:flex;justify-content:space-between;gap:10px;padding:9px 0;border-top:1px solid var(--line)}.log .t{font-variant-numeric:tabular-nums;color:var(--ok);font-weight:700;font-size:12px}.empty{font-size:12px;color:var(--muted)}
</style>
</head>
<body>
<div class="wrap">
    <div class="crumb">Guru / Absen</div>
    <h1>Scan absen siswa</h1>
    <p class="lead">Simulasi scan juga tersedia untuk mencoba alur absen dari kartu siswa.</p>

    <div class="scan-layout">
        <main>
            <section class="panel progress">
                <div class="txt"><strong>{{ $siswas->filter(fn ($s) => $s->barcode)->count() }} dari {{ $siswas->count() }} siswa sudah punya QR code</strong><div class="bar"><i style="width:{{ $siswas->count() ? round($siswas->filter(fn ($s) => $s->barcode)->count() / $siswas->count() * 100) : 0 }}%"></i></div></div>
                <a class="btn" href="{{ route('admin.qr.index') }}">Kelola QR siswa</a>
            </section>

            <section class="panel">
                <form class="tools" id="filterSiswa"><input type="search" id="cariSiswa" placeholder="Cari nama atau NISN" aria-label="Cari siswa"></form>
                <div class="tablewrap"><table><thead><tr><th>Siswa</th><th>Kelas</th><th>QR code</th><th></th></tr></thead><tbody id="daftarSiswa">
                @forelse ($siswas as $s)
                    <tr data-search="{{ strtolower($s->nama_siswa.' '.$s->nisn) }}">
                        <td><div class="nm">{{ $s->nama_siswa }}</div><div class="sub">NISN {{ $s->nisn }}</div></td>
                        <td>{{ $s->nama_kelas }}</td>
                        <td>@if ($s->barcode) @php($qr = new \chillerlan\QRCode\QRCode())<div class="thumb"><img src="{{ $qr->render($s->barcode->kode) }}" alt="QR {{ $s->nama_siswa }}"></div>@else<span class="sub">Belum ada</span>@endif</td>
                        <td class="act">@if ($s->barcode)<button type="button" class="pick" data-kode="{{ $s->barcode->kode }}">Simulasikan scan</button>@else<span class="sub">Buat QR dulu</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">Belum ada data siswa.</td></tr>
                @endforelse
                </tbody></table></div>
            </section>
        </main>

        <aside>
            <section class="panel box">
                <h2>Absen hari ini</h2>
                <p class="sim-note">Simulasi saja: arahkan QR di kartu siswa ke kamera, atau pilih siswa di bawah.</p>
                <div id="reader"></div>
                <div class="quick-list">
                    @foreach ($siswas->filter(fn ($s) => $s->barcode)->take(10) as $s)
                        <button type="button" class="quick" data-kode="{{ $s->barcode->kode }}">{{ $s->nama_siswa }} ({{ $s->nama_kelas }})</button>
                    @endforeach
                </div>
                <div id="hasil" class="hasil" role="status" aria-live="polite">Arahkan QR ke kamera.</div>
                <details class="manual"><summary>QR rusak atau tidak terbaca?</summary><p class="sub" style="margin:8px 0 0">Ketik kode unik yang tercetak di kartu siswa.</p><form id="formManual" autocomplete="off"><input type="text" id="kodeManual" placeholder="Contoh: SD-K7M2XQ9PTA" aria-label="Kode unik siswa" autocapitalize="characters"><button class="btn primary" type="submit">Absen</button></form></details>
            </section>
            <section class="panel box" style="margin-top:14px"><h2>Sudah absen</h2><ul class="log" id="log">@foreach ($hariIni as $a)<li><div><div class="nm">{{ $a->siswa->nama_siswa }}</div><div class="sub">Kelas {{ $a->siswa->nama_kelas }}, hadir</div></div><div class="t">{{ $a->waktu_absen?->format('H:i') }}</div></li>@endforeach</ul><p class="sub" id="logEmpty" @if ($hariIni->isNotEmpty()) hidden @endif>Belum ada yang absen. Pilih kartu siswa di atas untuk mensimulasikan scan.</p></section>
        </aside>
    </div>
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
    elHasil.className = 'hasil ' + tipe;
    elHasil.textContent = pesan;
}

function tambahLog(data) {
    const li = document.createElement('li');
    const kiri = document.createElement('div');
    const nama = document.createElement('div');
    const info = document.createElement('div');
    const waktu = document.createElement('div');
    nama.className = 'nm';   nama.textContent = data.siswa.nama;
    info.className = 'sub';  info.textContent = 'Kelas ' + data.siswa.kelas + ', hadir';
    waktu.className = 't';   waktu.textContent = data.jam;
    kiri.appendChild(nama); kiri.appendChild(info);
    li.appendChild(kiri); li.appendChild(waktu);
    elLog.prepend(li);
    elLogEmpty.hidden = true;
}

async function kirim(kode, metode) {
    // scan kamera membaca QR berkali-kali per detik, jadi dibatasi.
    if (metode === 'scan' && sibuk) return;
    sibuk = true;
    try {
        const res = await fetch(ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ kode: kode, metode: metode }),
        });
        const data = await res.json();
        const sukses = res.ok && (data.status === 'ok' || data.status === 'duplikat');
        tampilkan(sukses ? 'ok' : 'err', data.message || 'Terjadi kesalahan.');
        if (data.status === 'ok') {
            tambahLog(data);
            if (scanner) scanner.pause(); // Jeda scanner setelah sukses
            setTimeout(function() { if (scanner) scanner.resume(); }, 3000); // Resume setelah 3 detik
        }
    } catch (e) {
        tampilkan('err', 'Gagal terhubung ke server.');
    } finally {
        setTimeout(function () { sibuk = false; }, 2000);
    }
}

// ---- kamera ----
let scanner = null;
const konfigurasiScanner = {
    fps: 10,
    qrbox: { width: 180, height: 180 },
    aspectRatio: 1.333334,
};

async function mulaiScanner() {
    if (typeof Html5Qrcode === 'undefined') {
        tampilkan('err', 'Library scanner belum termuat. Periksa koneksi internet lalu muat ulang halaman.');
        return;
    }

    try {
        scanner = new Html5Qrcode('reader');
        const kamera = await Html5Qrcode.getCameras();
        if (!kamera.length) throw new Error('Tidak ada kamera yang ditemukan.');

        const kameraBelakang = kamera.find(function (item) {
            return /back|rear|environment|belakang/i.test(item.label);
        });
        const kameraId = (kameraBelakang || kamera[0]).id;

        await scanner.start(
            kameraId,
            konfigurasiScanner,
            function (kode) {
                tampilkan('ok', 'QR terbaca. Menyimpan absen...');
                kirim(kode, 'scan');
            },
            function () { /* frame tanpa QR: abaikan */ }
        );
        tampilkan('', 'Kamera aktif. Arahkan QR ke kotak kamera.');
    } catch (error) {
        tampilkan('err', 'Kamera tidak bisa dibuka. Izinkan akses kamera pada browser, atau gunakan kode manual.');
    }
}

mulaiScanner();

// ---- kode manual (cadangan) ----
document.getElementById('formManual').addEventListener('submit', function (e) {
    e.preventDefault();
    const input = document.getElementById('kodeManual');
    const kode = input.value.trim();
    if (!kode) { tampilkan('err', 'Ketik kode unik dulu.'); return; }
    kirim(kode, 'manual');
    input.value = '';
});

document.querySelectorAll('[data-kode]').forEach(function (button) {
    button.addEventListener('click', function () {
        kirim(button.dataset.kode, 'scan');
    });
});

document.getElementById('cariSiswa').addEventListener('input', function () {
    const kata = this.value.trim().toLowerCase();
    document.querySelectorAll('#daftarSiswa tr[data-search]').forEach(function (baris) {
        baris.hidden = kata !== '' && !baris.dataset.search.includes(kata);
    });
});
</script>
</body>
</html>
