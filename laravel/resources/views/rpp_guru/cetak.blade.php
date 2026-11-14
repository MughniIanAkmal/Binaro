<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPP - {{ $rpp->judul_rpp }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background-color: #f1f5f9;
            padding: 20px 0;
        }

        .no-print-bar {
            max-width: 210mm;
            margin: 0 auto 15px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .btn-print {
            background-color: #13527D;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .btn-print:hover {
            background-color: #0E3D5D;
        }

        .btn-close {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 20mm 20mm;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        /* Kop Surat */
        .kop-header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .kop-header h4 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-header h3 {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .kop-header p {
            font-size: 10pt;
            font-style: italic;
            margin-top: 2px;
        }

        .doc-title {
            text-align: center;
            margin: 20px 0 25px 0;
        }

        .doc-title h2 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .doc-title span {
            font-size: 11pt;
            font-weight: bold;
            display: block;
            margin-top: 3px;
        }

        .section-header {
            font-size: 12pt;
            font-weight: bold;
            margin: 16px 0 8px 0;
            text-transform: uppercase;
            background-color: #f8fafc;
            padding: 4px 8px;
            border-left: 4px solid #13527D;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.info-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11pt;
        }

        table.info-table td.label {
            width: 28%;
            font-weight: bold;
        }

        table.info-table td.colon {
            width: 2%;
        }

        table.info-table td.value {
            width: 70%;
        }

        .content-box {
            font-size: 11pt;
            text-align: justify;
            margin-bottom: 15px;
            padding-left: 8px;
        }

        .checklist-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 15px;
            padding-left: 8px;
            font-size: 11pt;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Lembar Pengesahan Tanda Tangan */
        .signature-table {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 11pt;
        }

        .signature-space {
            height: 70px;
        }

        .sign-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .page-container {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .section-header {
                background-color: transparent;
                border-left: 3px solid #000;
            }

            @page {
                size: A4 portrait;
                margin: 20mm 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Layar (Tidak Muncul Saat Dicetak ke PDF) -->
    <div class="no-print-bar">
        <div>
            <strong style="color: #0f172a; font-size: 14px;">Pratinjau Dokumen RPP (Siap Simpan PDF)</strong>
            <p style="color: #64748b; font-size: 12px; margin-top: 2px;">
                Klik tombol di samping untuk mencetak atau menyimpan dokumen ini sebagai file <strong>PDF</strong>.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-file-pdf"></i> Simpan / Cetak PDF
            </button>
            <button onclick="window.close()" class="btn-close">
                Tutup
            </button>
        </div>
    </div>

    <!-- Halaman Utama Dokumen A4 -->
    <div class="page-container">
        <!-- Kop Surat -->
        <div class="kop-header">
            <h4>PEMERINTAH KABUPATEN BONDOWOSO &bull; DINAS PENDIDIKAN</h4>
            <h3>SEKOLAH DASAR NEGERI KALITAPEN 01</h3>
            <p>{{ config('binaro.alamat_kop') }}</p>
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h2>MODUL AJAR / RPP KURIKULUM MERDEKA</h2>
            <span>TAHUN AJARAN {{ config('binaro.tahun_ajaran') }}</span>
        </div>

        <!-- Bagian A: Identitas Modul -->
        <div class="section-header">A. IDENTITAS MODUL</div>
        <table class="info-table">
            <tr>
                <td class="label">Satuan Pendidikan</td>
                <td class="colon">:</td>
                <td class="value">{{ config("binaro.nama_sekolah") }}</td>
            </tr>
            <tr>
                <td class="label">Mata Pelajaran</td>
                <td class="colon">:</td>
                <td class="value">{{ $rpp->mataPelajaran->nama_mapel ?? 'Semua Mapel' }}</td>
            </tr>
            <tr>
                <td class="label">Fase / Kelas / Semester</td>
                <td class="colon">:</td>
                <td class="value">{{ $rpp->fase ?? 'Fase B' }} / {{ $rpp->kelas->pararel ?? 'Kelas 4' }} / Semester 1 (Ganjil)</td>
            </tr>
            <tr>
                <td class="label">Topik / Judul Pembelajaran</td>
                <td class="colon">:</td>
                <td class="value"><strong>{{ $rpp->judul_rpp }}</strong></td>
            </tr>
            <tr>
                <td class="label">Modul / Bab Ke</td>
                <td class="colon">:</td>
                <td class="value">{{ $rpp->modul_ke ?? 'Modul #'.$rpp->id_rpp }}</td>
            </tr>
            <tr>
                <td class="label">Alokasi Waktu</td>
                <td class="colon">:</td>
                <td class="value">{{ $rpp->alokasi_waktu ?? '2 JP' }} (Standar 1 JP = 45 Menit)</td>
            </tr>
            <tr>
                <td class="label">Target Roster Jadwal</td>
                <td class="colon">:</td>
                <td class="value">{{ $rpp->target_jadwal ?? '-' }} &bull; {{ $rpp->ruang ?? 'Ruang Kelas' }}</td>
            </tr>
            <tr>
                <td class="label">Guru Pengajar</td>
                <td class="colon">:</td>
                <td class="value">{{ $rpp->guru->nama_guru ?? '-' }} (NIP. {{ $rpp->guru->nip ?? '-' }})</td>
            </tr>
        </table>

        <!-- Bagian B: Capaian & Tujuan Pembelajaran -->
        <div class="section-header">B. CAPAIAN & TUJUAN PEMBELAJARAN (TP)</div>
        <div class="content-box">
            <p>{{ $rpp->deskripsi ?? 'Peserta didik mampu memahami dan mendemonstrasikan capaian pembelajaran materi ini melalui aktivitas kontekstual serta pengamatan langsung di kelas.' }}</p>
        </div>

        <!-- Bagian C: Komponen & Fasilitas Modul Ajar -->
        <div class="section-header">C. KOMPONEN & FASILITAS MODUL AJAR</div>
        @php
            $checklist = $rpp->komponen_checklist ?? [];
            $tags = $checklist['tags'] ?? [];
        @endphp
        <div class="checklist-grid">
            <div class="checklist-item">
                <i class="fas fa-check-square"></i>
                <span>Tujuan Pembelajaran Khusus (TPK)</span>
            </div>
            <div class="checklist-item">
                <i class="fas fa-check-square"></i>
                <span>Materi & Media Pembelajaran Interaktif</span>
            </div>
            <div class="checklist-item">
                <i class="fas fa-check-square"></i>
                <span>Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)</span>
            </div>
            <div class="checklist-item">
                <i class="fas fa-check-square"></i>
                <span>Lembar Kerja Peserta Didik (LKPD)</span>
            </div>
        </div>

        @if(!empty($tags))
            <div class="content-box">
                <strong>Label / Tag Modul:</strong> {{ implode(', ', $tags) }}
            </div>
        @endif

        <!-- Bagian D: Lembar Pengesahan -->
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah {{ config('binaro.nama_sekolah') }}
                    <div class="signature-space"></div>
                    <span class="sign-name">{{ config("binaro.kepala_sekolah") }}</span><br>
                    NIP. {{ config('binaro.kepala_sekolah_nip') }}
                </td>
                <td>
                    {{ config('binaro.kota') }}, {{ now()->translatedFormat('d F Y') }}<br>
                    Guru Pengajar Kelas
                    <div class="signature-space"></div>
                    <span class="sign-name">{{ $rpp->guru->nama_guru ?? 'Guru' }}</span><br>
                    {{ ($rpp->guru->nip ?? null) ? 'NIP. ' . $rpp->guru->nip : '' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Script Otomatis Membuka Dialog Print Browser -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Beri jeda 500ms agar style dan icon termuat sempurna sebelum popup print muncul
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>

