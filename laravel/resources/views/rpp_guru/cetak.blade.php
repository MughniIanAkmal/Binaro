<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPP - {{ $rpp->judul_rpp }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; background-color: #f1f5f9; padding: 20px 0; }
        .no-print-bar { max-width: 210mm; margin: 0 auto 15px auto; background: #fff; border: 1px solid #cbd5e1; padding: 12px 20px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-print { background-color: #13527D; color: #fff; border: none; padding: 8px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background-color 0.2s; }
        .btn-print:hover { background-color: #0E3D5D; }
        .btn-close { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; text-decoration: none; }
        .page-container { width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; padding: 20mm 20mm; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        /* Kop Surat */
        .kop-header { text-align: center; border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 20px; }
        .kop-header h4 { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop-header h3 { font-size: 15pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-header p { font-size: 10pt; font-style: italic; margin-top: 2px; }
        .doc-title { text-align: center; margin: 20px 0 25px 0; }
        .doc-title h2 { font-size: 14pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .doc-title span { font-size: 11pt; font-weight: bold; display: block; margin-top: 3px; }
        .section-header { font-size: 12pt; font-weight: bold; margin: 16px 0 8px 0; text-transform: uppercase; background-color: #f8fafc; padding: 4px 8px; border-left: 4px solid #13527D; }
        table.info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.info-table td { padding: 4px 6px; vertical-align: top; font-size: 11pt; }
        table.info-table td.label { width: 28%; font-weight: bold; }
        table.info-table td.colon { width: 2%; }
        table.info-table td.value { width: 70%; }
        .content-box { font-size: 11pt; text-align: justify; margin-bottom: 15px; padding-left: 8px; }
        /* Lembar Pengesahan Tanda Tangan */
        .signature-table { width: 100%; margin-top: 40px; page-break-inside: avoid; }
        .signature-table td { width: 50%; text-align: center; vertical-align: top; font-size: 11pt; }
        .signature-space { height: 70px; }
        .sign-name { font-weight: bold; text-decoration: underline; }
        @media print { body { background: #fff; padding: 0; } .no-print-bar { display: none !important; } .page-container { width: 100%; margin: 0; padding: 0; box-shadow: none; } .section-header { background-color: transparent; border-left: 3px solid #000; } @page { size: A4 portrait; margin: 20mm 15mm; } }
    </style>
</head>
<body>
    <!-- Toolbar Layar -->
    <div class="no-print-bar">
        <div>
            <strong style="color: #0f172a; font-size: 14px;">Pratinjau Dokumen RPP (Siap Simpan PDF)</strong>
            <p style="color: #64748b; font-size: 12px; margin-top: 2px;">Klik tombol di samping untuk mencetak atau menyimpan dokumen ini sebagai file <strong>PDF</strong>.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print"><i class="fas fa-file-pdf"></i> Simpan / Cetak PDF</button>
            <button onclick="window.close()" class="btn-close">Tutup</button>
        </div>
    </div>

    <!-- Halaman Utama Dokumen A4 -->
    <div class="page-container">
        <!-- Kop Surat -->
        <div class="kop-header">
            <h4>{{ config('binaro.kabupaten_kop') }} &bull; {{ config('binaro.dinas_kop') }}</h4>
            <h3>{{ config('binaro.nama_sekolah') }}</h3>
            <p>{{ config('binaro.alamat_kop') }}</p>
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h2>KEGIATAN INTRAKURIKULER</h2>
            <span>(Rencana Pelaksanaan Pembelajaran / Modul Ajar)</span>
            <span>Nomor: {{ $rpp->nomor_rpp ?? '#' . $rpp->id_rpp }} | Tahun Ajaran {{ $rpp->tahun_ajaran ?? config('binaro.tahun_ajaran') }}</span>
        </div>

        <!-- Bagian A: Identitas Modul -->
        <div class="section-header">A. INFORMASI UMUM</div>
        <table class="info-table">
            <tr><td class="label">Satuan Pendidikan</td><td class="colon">:</td><td class="value">{{ config('binaro.nama_sekolah') }}</td></tr>
            <tr><td class="label">Mata Pelajaran</td><td class="colon">:</td><td class="value">{{ $rpp->mataPelajaran->nama_mapel ?? '-' }}</td></tr>
            <tr><td class="label">Fase / Kelas</td><td class="colon">:</td><td class="value">{{ $rpp->fase ?? 'Fase B' }} / {{ $rpp->kelas->pararel ?? 'Kelas 4' }}</td></tr>
            <tr><td class="label">Semester</td><td class="colon">:</td><td class="value">{{ $rpp->semester ? ucfirst($rpp->semester) : 'Ganjil' }}</td></tr>
            <tr><td class="label">Topik / Tema</td><td class="colon">:</td><td class="value"><strong>{{ $rpp->judul_rpp }}</strong></td></tr>
            <tr><td class="label">Modul / Bab Ke</td><td class="colon">:</td><td class="value">{{ $rpp->modul_ke ?? ($rpp->bab ? $rpp->bab->nama_bab : ('Modul #' . $rpp->id_rpp)) }}</td></tr>
            <tr><td class="label">Jumlah Pertemuan</td><td class="colon">:</td><td class="value">{{ $rpp->total_pertemuan ?? 1 }} Pertemuan</td></tr>
            <tr><td class="label">Alokasi Waktu</td><td class="colon">:</td><td class="value">{{ $rpp->alokasi_waktu ?? ((int) (($rpp->alokasi_waktu_menit ?: 70) / 35).' JP') }} ({{ $rpp->alokasi_waktu_menit ?? 70 }} menit / pertemuan, 1 JP = 35 Menit)</td></tr>
            <tr><td class="label">Target Roster Jadwal</td><td class="colon">:</td><td class="value">{{ $rpp->target_jadwal ?? '-' }} &bull; {{ $rpp->ruang ?? 'Ruang Kelas' }}</td></tr>
            <tr><td class="label">Guru Pengajar</td><td class="colon">:</td><td class="value">{{ $rpp->guru->nama_guru ?? '-' }} (NIP. {{ $rpp->guru->nip ?? '-' }})</td></tr>
        </table>

        @php
            $sessions = $rpp->sessions->isNotEmpty()
                ? $rpp->sessions->sortBy('nomor_pertemuan')
                : collect([(object) ['nomor_pertemuan' => 1, 'konten' => []]]);
            $multiPertemuan = $sessions->count() > 1;
        @endphp

        @foreach($sessions as $session)
            @php $konten = (array) ($session->konten ?? []); @endphp
            @if($multiPertemuan)
                <div style="margin: 22px 0 10px 0; padding: 6px 10px; border: 1px solid #13527D; background: #eff6ff; font-weight: bold; font-size: 11.5pt; text-transform: uppercase;">
                    PERTEMUAN KE-{{ $session->nomor_pertemuan }} DARI {{ $rpp->total_pertemuan ?? $sessions->count() }}
                </div>
            @endif

            @if(!empty($konten['kesiapan_peserta_didik']))
                <div class="section-header">IDENTIFIKASI KESIAPAN PESERTA DIDIK</div>
                <div class="content-box" style="white-space: pre-line;">{{ $konten['kesiapan_peserta_didik'] }}</div>
            @endif

            <!-- Bagian B: Capaian Pembelajaran -->
            <div class="section-header">B. CAPAIAN PEMBELAJARAN (CP)</div>
            <div class="content-box">
                @if(!empty($konten['cp_elemen']))
                    <p><strong>Elemen:</strong> {{ $konten['cp_elemen'] }}</p>
                @endif
                @if(!empty($konten['cp_text']))
                    <p style="white-space: pre-line;">{{ $konten['cp_text'] }}</p>
                @elseif(!empty($rpp->deskripsi))
                    <p style="white-space: pre-line;">{{ $rpp->deskripsi }}</p>
                @else
                    <p><em>Capaian pembelajaran belum diisi.</em></p>
                @endif
                @if(!empty($konten['pemahaman_bermakna']))
                    <p style="margin-top: 8px;"><strong>Pemahaman Bermakna:</strong></p>
                    <p style="white-space: pre-line;">{{ $konten['pemahaman_bermakna'] }}</p>
                @endif
            </div>

            <!-- Bagian C: Tujuan Pembelajaran -->
            <div class="section-header">C. TUJUAN PEMBELAJARAN (TP)</div>
            @if(!empty($konten['tp_list']) && count($konten['tp_list']) > 0)
            <div class="content-box">
                @foreach($konten['tp_list'] as $index => $tp)
                    @if(trim((string) ($tp['teks'] ?? '')) !== '')
                        <p style="margin-bottom: 4px;"><strong>TP {{ $index + 1 }}:</strong> {{ $tp['teks'] }}
                        @if(!empty($tp['dimensi']) && count($tp['dimensi']) > 0)
                            <br><span style="font-size: 10pt;"><em>Profil Pelajar Pancasila: {{ implode(', ', $tp['dimensi']) }}</em></span>
                        @endif
                        </p>
                    @endif
                @endforeach
            </div>
            @else
            <div class="content-box">
                <p><em>Tujuan pembelajaran belum diisi.</em></p>
            </div>
            @endif

            <!-- Bagian D: Pertanyaan Pemantik -->
            @if(!empty($konten['pertanyaan_pemantik']) && count(array_filter($konten['pertanyaan_pemantik'])) > 0)
            <div class="section-header">D. PERTANYAAN PEMANTIK</div>
            <div class="content-box">
                @foreach(array_values(array_filter($konten['pertanyaan_pemantik'])) as $index => $q)
                    <p>{{ $index + 1 }}. {{ $q }}</p>
                @endforeach
            </div>
            @endif

            <!-- Bagian E: Langkah Pembelajaran -->
            @if(!empty($konten['langkah_pendahuluan']) || !empty($konten['model_pembelajaran']) || !empty($konten['langkah_inti']) || !empty($konten['langkah_penutup']))
            <div class="section-header">E. LANGKAH PEMBELAJARAN (PEMBELAJARAN MENDALAM)</div>
            <div class="content-box">
                @if(!empty($konten['model_pembelajaran']))
                    <p><strong>Model Pembelajaran:</strong> {{ $konten['model_pembelajaran'] }}</p>
                @endif
                @if(!empty($konten['metode_pembelajaran']))
                    <p><strong>Metode:</strong> {{ is_array($konten['metode_pembelajaran']) ? implode(', ', $konten['metode_pembelajaran']) : $konten['metode_pembelajaran'] }}</p>
                @endif
                @if(!empty($konten['langkah_pendahuluan']))
                    <p style="margin-top: 8px;"><strong>1. Pendahuluan ({{ $konten['durasi']['pendahuluan'] ?? 10 }} menit):</strong></p>
                    <p style="white-space: pre-line;">{{ $konten['langkah_pendahuluan'] }}</p>
                @endif
                @if(!empty($konten['langkah_inti']) && count($konten['langkah_inti']) > 0)
                    <p style="margin-top: 8px;"><strong>2. Kegiatan Inti ({{ $konten['durasi']['inti'] ?? 50 }} menit):</strong></p>
                    @foreach($konten['langkah_inti'] as $idx => $tahap)
                        <p>{{ $idx + 1 }}. <strong>{{ $tahap['nama'] ?? 'Tahap '.($idx+1) }}</strong>@if(!empty($tahap['durasi'])) ({{ $tahap['durasi'] }} menit)@endif: {{ $tahap['uraian'] ?? '' }}</p>
                    @endforeach
                @endif
                @if(!empty($konten['langkah_penutup']))
                    <p style="margin-top: 8px;"><strong>3. Penutup ({{ $konten['durasi']['penutup'] ?? 10 }} menit):</strong></p>
                    <p style="white-space: pre-line;">{{ $konten['langkah_penutup'] }}</p>
                @endif
                @if(!empty($konten['refleksi_pembelajaran']))
                    <p style="margin-top: 8px;"><strong>4. Refleksi Peserta Didik &amp; Guru:</strong></p>
                    <p style="white-space: pre-line;">{{ $konten['refleksi_pembelajaran'] }}</p>
                @endif
            </div>
            @endif

            <!-- Bagian F: Media & Sumber Belajar -->
            @if(!empty($konten['media_fisik']) || !empty($konten['media_digital']) || (!empty($konten['file_uploads']) && count($konten['file_uploads']) > 0))
            <div class="section-header">F. MEDIA &amp; SUMBER BELAJAR</div>
            <div class="content-box">
                @if(!empty($konten['media_fisik']) && count($konten['media_fisik']) > 0)
                    <p><strong>Media Fisik:</strong></p>
                    <ul style="padding-left: 18px;">
                        @foreach($konten['media_fisik'] as $m)
                            <li>{{ $m }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($konten['media_digital']) && count($konten['media_digital']) > 0)
                    <p style="margin-top: 6px;"><strong>Media Digital:</strong></p>
                    <ul style="padding-left: 18px;">
                        @foreach($konten['media_digital'] as $m)
                            <li>{{ $m }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($konten['file_uploads']) && count($konten['file_uploads']) > 0)
                    <p style="margin-top: 6px;"><strong>Berkas Pendukung:</strong></p>
                    <ul style="padding-left: 18px;">
                        @foreach($konten['file_uploads'] as $file)
                            <li>{{ $file['name'] ?? $file['path'] ?? '-' }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @endif

            <!-- Bagian G: Rencana Asesmen -->
            @if(!empty($konten['asesmen_diagnostik']) || !empty($konten['asesmen_formatif']) || !empty($konten['asesmen_sumatif']))
            <div class="section-header">G. RENCANA ASESMEN</div>
            <div class="content-box">
                @foreach(['diagnostik' => 'Diagnostik / Asesmen Awal', 'formatif' => 'Formatif (Proses)', 'sumatif' => 'Sumatif (Akhir)'] as $key => $label)
                    @if(!empty($konten["asesmen_{$key}"]) && (!empty($konten["asesmen_{$key}"]["metode"]) || !empty($konten["asesmen_{$key}"]["instrumen"])))
                        <p style="margin-top: 6px;"><strong>{{ $label }}:</strong></p>
                        @if(!empty($konten["asesmen_{$key}"]["metode"]))
                            <p>&nbsp;&nbsp;Metode: {{ $konten["asesmen_{$key}"]["metode"] }}</p>
                        @endif
                        @if(!empty($konten["asesmen_{$key}"]["instrumen"]))
                            <p style="white-space: pre-line;">&nbsp;&nbsp;Instrumen/Rubrik: {{ $konten["asesmen_{$key}"]["instrumen"] }}</p>
                        @endif
                    @endif
                @endforeach
            </div>
            @endif

            <!-- Bagian H: Diferensiasi -->
            @if(!empty($konten['diferensiasi_jenis']) && count($konten['diferensiasi_jenis']) > 0)
            <div class="section-header">H. DIFERENSIASI PEMBELAJARAN</div>
            <div class="content-box">
                @foreach($konten['diferensiasi_jenis'] as $jenis)
                    <p><strong>{{ $jenis === 'konten' ? 'Diferensiasi Konten' : ($jenis === 'proses' ? 'Diferensiasi Proses' : 'Diferensiasi Produk') }}:</strong></p>
                    <p style="white-space: pre-line; margin-bottom: 4px;">{{ $konten["diferensiasi_{$jenis}"] ?? '-' }}</p>
                @endforeach
            </div>
            @endif
        @endforeach

        <!-- Bagian I: Keterangan -->
        <div class="section-header">I. KETERANGAN</div>
        <div class="content-box">
            <p><em>Modul ajar ini disusun sesuai Kurikulum Merdeka dan telah ditinjau oleh Admin.</em></p>
            @if(!empty($rpp->catatan_revisi))
                <p style="margin-top: 8px; color: #dc2626;"><strong>Catatan Revisi:</strong> {{ $rpp->catatan_revisi }}</p>
            @endif
        </div>

        <!-- Lembar Pengesahan -->
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala {{ config('binaro.nama_sekolah') }}
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

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>
