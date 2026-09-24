@extends('layouts.app')

@section('content')
@include('qr-absen._styles')
<style>
    body{font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:#f8fafc;color:#1e293b;font-size:16px;line-height:1.5}
    body > aside{font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:#fff;line-height:1.25}
    body > aside h1{font-size:.875rem;line-height:1.25;color:#fff;margin:0}
    body > aside p{margin:0}
    body > aside a{color:inherit}
    body > main{font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:#1e293b;line-height:1.5}
    .wrap{max-width:none;margin:0;padding:32px}
    .crumb{font-size:11px;font-weight:600;color:#64748b;margin-bottom:4px}
    h1{font-size:24px;line-height:1.25;letter-spacing:-.025em;color:#0f172a}
    .lead{font-size:12px;margin:4px 0 24px;color:#64748b}
    .panel{border-color:#e2e8f0;border-radius:12px;box-shadow:0 1px 2px rgba(15,23,42,.03)}
    .progress{padding:20px;gap:16px;margin-bottom:16px}
    .progress .txt{font-size:12px}
    .sub{font-size:11px;color:#64748b}
    .btn{border-color:#cbd5e1;border-radius:8px;padding:8px 12px;min-height:38px;font-size:12px;font-weight:700}
    .btn.primary{background:#13527d;border-color:#13527d}
    .btn.warn{background:#fef3c7;color:#92400e}
    .btn.small{padding:6px 10px;min-height:32px;font-size:11px}
    .tools{padding:12px 16px;gap:8px}
    .tools input,.tools select{border-color:#cbd5e1;border-radius:8px;padding:8px 10px;min-height:38px;font-size:12px}
    th{font-size:11px;padding:10px 16px;color:#64748b}
    td{font-size:12px;padding:12px 16px}
    .nm{font-size:12px;font-weight:700;color:#0f172a}
    .pill{font-size:11px;padding:3px 8px}
    .pagination li a,.pagination li span{font-size:11px;padding:6px 10px;border-radius:6px}
    @media (max-width: 768px){.wrap{padding:20px 16px}.progress{align-items:stretch}}
    .card-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(23,31,44,0.42);z-index:50;padding:24px}
    .card-modal.show{display:flex}
    .card-box{width:min(560px,92vw);background:#f2f2f2;border-radius:18px;box-shadow:0 20px 50px rgba(15,23,42,.25);overflow:hidden;border:1px solid rgba(108,117,132,.2)}
    .card-top{background:linear-gradient(180deg,#f5ba2d 0%,#ebaf1e 100%);padding:18px 22px 12px;color:#0f172a;font-weight:800;font-size:18px;display:flex;align-items:center;justify-content:space-between}
    .card-top .close{font-size:30px;line-height:1;cursor:pointer;color:#0f172a;background:transparent;border:none;padding:0}
    .card-body{padding:18px 24px 20px;background:#fff}
    .card-school{font-size:16px;font-weight:800;color:#1b2439;letter-spacing:.02em}
    .card-name{font-size:30px;font-weight:800;line-height:1.2;margin:16px 0 4px;color:#0f172a;text-align:center}
    .card-meta{font-size:15px;color:#586582;text-align:center;margin-bottom:14px}
    .card-qr-wrap{display:flex;justify-content:center;padding:18px 0 10px;border-top:2px dashed #d6dbe5;border-bottom:2px dashed #d6dbe5}
    .card-qr{display:flex;align-items:center;justify-content:center;min-height:220px;background:#fff}
    .card-qr img{width:208px;height:208px;display:block}
    .card-note{font-size:12px;color:#555f6f;text-align:center;margin-top:12px;margin-bottom:8px}
    .card-code{font-size:22px;font-weight:800;letter-spacing:.08em;text-align:center;color:#101827;margin-bottom:8px}
    .card-sub{font-size:14px;color:#4f5d79;text-align:center;line-height:1.5;margin:12px 0 18px}
    .card-actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}
    .card-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;border-radius:10px;padding:0 18px;border:1px solid #d5d9e2;background:#fff;color:#1f2937;font-size:15px;font-weight:700;text-decoration:none;cursor:pointer}
    .card-btn.primary{background:#2456C8;color:#fff;border-color:#2456C8}
    .card-btn.secondary{background:#f1f1f1}
    .sr-only{position:absolute;left:-9999px}
    .tools input[type="search"],.tools button[type="submit"]{display:none}
</style>
<div class="wrap">
    <div class="crumb">Admin / QR code siswa</div>
    <h1>QR code siswa</h1>
    <p class="lead">Buat kartu QR code untuk absen. Guru memindai kartu ini saat mengabsen siswa.</p>

    @if (session('success'))
        <div class="flash ok" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="flash err" role="alert">{{ session('error') }}</div>
    @endif

    <section class="panel progress">
        <div class="txt">
            <strong>{{ $sudah }} dari {{ $total }} siswa sudah punya QR code</strong>
            <div class="bar"><i style="width: {{ $total ? round($sudah / $total * 100) : 0 }}%"></i></div>
        </div>
        <a class="btn" href="{{ route('admin.qr.cetakKelas', request()->only('kelas')) }}" target="_blank">
            Cetak semua kartu{{ request('kelas') ? ' kelas '.request('kelas') : '' }}
        </a>
        @if ($sudah < $total)
            <form method="POST" action="{{ route('admin.qr.storeAll') }}">
                @csrf
                <button class="btn primary" type="submit">Buat semua yang belum punya</button>
            </form>
        @endif
    </section>

    <section class="panel">
        <form class="tools" method="GET" action="{{ route('admin.qr.index') }}">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau NISN" aria-label="Cari nama atau NISN">
            <select name="kelas" aria-label="Filter kelas" onchange="this.form.submit()">
                <option value="">Semua kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k }}" @selected(request('kelas') === $k)>Kelas {{ $k }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Cari</button>
        </form>

        <div class="tablewrap">
            <table>
                <thead>
                    <tr><th>Siswa</th><th>Kelas</th><th>QR code</th><th></th></tr>
                </thead>
                <tbody>
                @forelse ($siswas as $s)
                    <tr>
                        <td>
                            <div class="nm">{{ $s->nama_siswa }}</div>
                            <div class="sub">NISN : {{ $s->nisn }}</div>
                        </td>
                        <td>{{ $s->nama_kelas }}</td>
                        <td>
                            @if ($s->barcode)
                                @php
                                    $qr = new \chillerlan\QRCode\QRCode();
                                    $svg = $qr->render($s->barcode->kode);
                                @endphp
                                <div class="thumb">
                                    <img src="{{ $svg }}" alt="QR {{ $s->nama_siswa }}">
                                </div>
                                <div class="qr-inline" style="display:none;">
                                    <img src="{{ $svg }}" alt="QR {{ $s->nama_siswa }}">
                                </div>
                            @else
                                <span class="pill">Belum dibuat</span>
                            @endif
                        </td>
                        <td class="act">
                            @if ($s->barcode)
                                @php
                                    $qrSvg = (new \chillerlan\QRCode\QRCode())->render($s->barcode->kode);
                                @endphp
                                <button class="btn small card-open"
                                        type="button"
                                        data-nama="{{ $s->nama_siswa }}"
                                        data-kelas="{{ $s->nama_kelas }}"
                                        data-nisn="{{ $s->nisn }}"
                                        data-kode="{{ $s->barcode->kode }}"
                                        data-print="{{ route('admin.qr.cetak', $s) }}">
                                    Lihat kartu
                                </button>
                                <form method="POST" action="{{ route('admin.qr.regenerate', $s) }}"
                                      onsubmit="return confirm('Buat ulang kode? Kartu lama tidak akan bisa dipakai lagi.')">
                                    @csrf
                                    <button class="btn small warn" type="submit">Buat ulang</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.qr.store', $s) }}">
                                    @csrf
                                    <button class="btn small primary" type="submit">Buat QR code</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">Tidak ada siswa yang cocok. Ubah kata kunci atau pilih kelas lain.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($siswas->hasPages())
            {{ $siswas->links('pagination::default') }}
        @endif
    </section>
</div>

<div class="card-modal" id="cardModal" aria-hidden="true">
    <div class="card-box" role="dialog" aria-modal="true" aria-labelledby="cardTitle">
        <div class="card-top">
            <div id="cardTitle">{{ config('app.name') }}</div>
            <button type="button" class="close" aria-label="Tutup" data-close="cardModal">&times;</button>
        </div>
        <div class="card-body">
            <div class="card-school">{{ config('app.name') }}</div>
            <div class="card-name" id="cardName">Nama Siswa</div>
            <div class="card-meta" id="cardMeta">Kelas ... | NISN ...</div>

            <div class="card-qr-wrap">
                <div class="card-qr" id="cardQr"></div>
            </div>

            <div class="card-note">Kode unik (dipakai jika QR rusak)</div>
            <div class="card-code" id="cardCode">SD-XXXXXXXXXX</div>

            <div class="card-sub">Setiap siswa punya kode unik. Guru cukup scan QR. Kode di bawahnya hanya dipakai manual jika QR rusak.</div>

            <div class="card-actions">
                <a class="card-btn primary" id="cardPrintLink" href="#" target="_blank">Cetak kartu</a>
                <button type="button" class="card-btn secondary" data-close="cardModal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('cardModal');
    const cardName = document.getElementById('cardName');
    const cardMeta = document.getElementById('cardMeta');
    const cardQr = document.getElementById('cardQr');
    const cardCode = document.getElementById('cardCode');
    const cardPrintLink = document.getElementById('cardPrintLink');

    document.querySelectorAll('.card-open').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const nama = btn.dataset.nama;
            const kelas = btn.dataset.kelas;
            const nisn = btn.dataset.nisn;
            const kode = btn.dataset.kode;
            const qrMarkup = btn.closest('tr').querySelector('.qr-inline')?.innerHTML || '';

            cardName.textContent = nama;
            cardMeta.textContent = 'Kelas ' + kelas + ' | NISN ' + nisn;
            cardQr.innerHTML = qrMarkup;
            cardCode.textContent = kode;
            cardPrintLink.href = btn.dataset.print || '#';

            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    document.querySelectorAll('[data-close="cardModal"]').forEach(function (el) {
        el.addEventListener('click', function () {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('show')) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
    });
</script>
@endsection
