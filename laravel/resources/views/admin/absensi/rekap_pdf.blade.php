<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi - {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 landscape;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #3b82f6;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 9pt;
            color: #64748b;
        }
        .summary-cards {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .summary-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
        }
        .summary-card.total { border-left: 3px solid #3b82f6; }
        .summary-card.hadir { border-left: 3px solid #10b981; }
        .summary-card.izin { border-left: 3px solid #0ea5e9; }
        .summary-card.sakit { border-left: 3px solid #f59e0b; }
        .summary-card.alpa { border-left: 3px solid #ef4444; }
        .summary-card .label {
            font-size: 8pt;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-card .value {
            font-size: 18pt;
            font-weight: 700;
            color: #1e293b;
        }
        .summary-card.hadir .value { color: #059669; }
        .summary-card.izin .value { color: #0284c7; }
        .summary-card.sakit .value { color: #d97706; }
        .summary-card.alpa .value { color: #dc2626; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        thead tr {
            background: #f1f5f9;
        }
        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 8pt;
            text-transform: uppercase;
            white-space: nowrap;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #f1f5f9;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
        }
        .status-hadir { background: #d1fae5; color: #065f46; }
        .status-izin { background: #e0f2fe; color: #0369a1; }
        .status-sakit { background: #fef3c7; color: #92400e; }
        .status-alpa { background: #ffe4e6; color: #9f1239; }
        .status-terlambat { background: #fef9c3; color: #854d0e; }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9pt;
            color: #64748b;
        }
        .no-print {
            display: none;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 24px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-size: 10pt; font-weight: 600; cursor: pointer;">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="header">
        <h1>REKAPITULASI ABSENSI SISWA</h1>
        <p>SD Negeri Karanganyar 01</p>
        <p>Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</p>
        @if($selectedKelas)
            <p>Kelas: {{ \App\Models\Kelas::find($selectedKelas)?->pararel ?? '-' }}</p>
        @endif
        @if($selectedStatus)
            <p>Status: {{ $selectedStatus }}</p>
        @endif
    </div>

    <div class="summary-cards">
        <div class="summary-card total">
            <div class="label">Total Siswa</div>
            <div class="value">{{ $totalSiswa }}</div>
        </div>
        <div class="summary-card hadir">
            <div class="label">Hadir</div>
            <div class="value">{{ $hadir }}</div>
        </div>
        <div class="summary-card izin">
            <div class="label">Izin</div>
            <div class="value">{{ $izin }}</div>
        </div>
        <div class="summary-card sakit">
            <div class="label">Sakit</div>
            <div class="value">{{ $sakit }}</div>
        </div>
        <div class="summary-card alpa">
            <div class="label">Alpa</div>
            <div class="value">{{ $alpa }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Jam Masuk</th>
                <th>Guru Pencatat</th>
                <th>Metode</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $idx => $row)
                @php
                    $isTerlambat = $row->status === 'Hadir' && isset($row->keterangan) && str_contains((string) $row->keterangan, 'Terlambat');
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="font-family: monospace;">{{ $row->nisn }}</td>
                    <td><strong>{{ $row->nm_siswa }}</strong></td>
                    <td>{{ $row->nama_kelas ?? '-' }}</td>
                    <td>
                        @if($isTerlambat)
                            <span class="status-badge status-terlambat">Terlambat</span>
                        @elseif($row->status == 'Hadir')
                            <span class="status-badge status-hadir">Hadir</span>
                        @elseif($row->status == 'Sakit')
                            <span class="status-badge status-sakit">Sakit</span>
                        @elseif($row->status == 'Izin')
                            <span class="status-badge status-izin">Izin</span>
                        @else
                            <span class="status-badge status-alpa">Alpa</span>
                        @endif
                    </td>
                    <td>{{ $row->waktu_absen ? \Carbon\Carbon::parse($row->waktu_absen)->format('H:i:s') : '-' }}</td>
                    <td>{{ $row->nama_guru_pencatat ?? '-' }}</td>
                    <td>{{ $row->metode === 'scan_qr' ? 'Scan QR' : 'Manual' }}</td>
                    <td>{{ $row->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
        <p>Mengetahui,</p>
        <br><br><br>
        <p>({{ $guru?->nama_guru ?? '________________' }})</p>
        <p>NIP. .........................</p>
    </div>
</body>
</html>
