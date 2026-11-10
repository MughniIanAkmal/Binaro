<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cetak kartu QR siswa</title>
@include('qr-absen._styles')
<style>
.toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:18px}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
.idcard{background:#fff;color:#16223A;border:1px solid #CBD3E3;border-radius:14px;overflow:hidden;break-inside:avoid}
.idcard .stripe{background:var(--pencil);padding:10px 16px;font-weight:800;font-size:14px;color:#16223A}
.idcard .body{padding:16px}
.idcard .who{font-size:19px;font-weight:800;line-height:1.25;margin:0}
.idcard .meta{font-size:13px;color:#4A5875;margin:4px 0 14px}
.idcard .codebox{display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px;border:1px dashed #B7C1D6;border-radius:10px}
.idcard .codebox svg{width:130px;height:130px}
.kode-label{font-size:12px;color:#4A5875;margin-top:4px}
.kode{font-size:15px;letter-spacing:.08em;font-weight:800}
@media print{
    body{background:#fff}
    .toolbar{display:none}
    .wrap{padding:0;max-width:none}
    .cards{grid-template-columns:repeat(2,1fr)}
    .idcard{border-color:#000}
}
</style>
</head>
<body>
<div class="wrap">
    <div class="toolbar">
        <button class="btn primary" onclick="window.print()">Cetak</button>
        <a class="btn" href="{{ route('admin.qr.index') }}">Kembali</a>
        <span class="sub">{{ $siswas->count() }} kartu</span>
    </div>

    @if ($siswas->isEmpty())
        <p class="empty">Belum ada QR code yang bisa dicetak.</p>
    @endif

    <div class="cards">
        @foreach ($siswas as $s)
            <div class="idcard">
                <div class="stripe">{{ config('app.name') }}</div>
                <div class="body">
                    <p class="who">{{ $s->nama_siswa }}</p>
                    <p class="meta">Kelas {{ $s->nama_kelas }} | NISN {{ $s->nisn }}</p>
                    <div class="codebox">
                        @php
                            $qr = new \chillerlan\QRCode\QRCode();
                            $svg = $qr->render($s->barcode->kode);
                        @endphp
                        {!! $svg !!}
                        <div class="kode-label">Kode unik (dipakai jika QR rusak)</div>
                        <div class="kode">{{ $s->barcode->kode }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
