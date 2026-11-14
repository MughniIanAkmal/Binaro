<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Presensi Siswa {{ $namaKelas }} - {{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-container { box-shadow: none !important; border: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; padding: 0 !important; }
            @page { margin: 15mm 15mm 15mm 15mm; size: A4 portrait; }
        }
        body { font-family: 'Times New Roman', Times, serif; }
        .font-sans-ui { font-family: ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen py-8">

    <!-- Top Action Bar for screen -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <a href="{{ route('guru.absen.index') }}" class="font-sans-ui text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Presensi Siswa
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.absen.export.excel') }}" class="font-sans-ui text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition">
                <i class="fas fa-file-excel"></i> Unduh File Excel (.csv)
            </a>
            <button onclick="window.print()" class="font-sans-ui text-xs font-bold bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Official Paper Container -->
    <div class="print-container max-w-4xl mx-auto bg-white p-10 rounded-2xl shadow-md border border-slate-200">
        
        <!-- Kop Surat Resmi -->
        <div class="flex items-center justify-between pb-4 border-b-4 border-double border-slate-900 mb-6 text-center">
            <div class="w-20 text-center">
                <i class="fas fa-graduation-cap text-5xl text-primary"></i>
            </div>
            <div class="flex-1 px-4">
                <h3 class="text-sm font-bold uppercase tracking-wider">{{ config('binaro.kabupaten_kop') }}</h3>
                <h3 class="text-sm font-bold uppercase tracking-wider">{{ config('binaro.dinas_kop') }}</h3>
                <h1 class="text-xl font-black uppercase tracking-wide text-primary my-0.5">{{ strtoupper(config('binaro.nama_sekolah')) }}</h1>
                <p class="text-xs italic text-slate-600">{{ config('binaro.alamat_kop') }}</p>
                <p class="text-[11px] font-semibold text-slate-700">NPSN: {{ config('binaro.npsn') }} • AKREDITASI A • KURIKULUM MERDEKA</p>
            </div>
            <div class="w-20 text-center">
                <div class="w-16 h-16 border-2 border-dashed border-slate-300 rounded-lg flex items-center justify-center text-[10px] text-slate-400 font-sans-ui">
                    LOGO
                </div>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center my-6">
            <h2 class="text-base font-bold uppercase underline tracking-wider">LAPORAN REKAPITULASI PRESENSI & KEHADIRAN SISWA</h2>
            <p class="text-xs font-sans-ui font-semibold text-slate-700 mt-1">
                Kelas: <span class="font-bold text-slate-900">{{ $namaKelas }}</span> &bull; 
                Tanggal: <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</span> &bull; 
                Semester: <span class="font-bold text-slate-900">{{ config('binaro.semester') }} {{ config('binaro.tahun_ajaran') }}</span>
            </p>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="grid grid-cols-5 gap-2 mb-6 font-sans-ui text-center text-xs">
            <div class="border border-slate-300 rounded p-2 bg-slate-50">
                <div class="text-[10px] font-bold text-slate-500 uppercase">Total Siswa</div>
                <div class="text-base font-black text-slate-900">{{ $totalSiswa }}</div>
            </div>
            <div class="border border-emerald-300 rounded p-2 bg-emerald-50">
                <div class="text-[10px] font-bold text-emerald-700 uppercase">Hadir</div>
                <div class="text-base font-black text-emerald-800">{{ $hadir }} ({{ $persen }}%)</div>
            </div>
            <div class="border border-sky-300 rounded p-2 bg-sky-50">
                <div class="text-[10px] font-bold text-sky-700 uppercase">Izin</div>
                <div class="text-base font-black text-sky-800">{{ $izin }}</div>
            </div>
            <div class="border border-amber-300 rounded p-2 bg-amber-50">
                <div class="text-[10px] font-bold text-amber-700 uppercase">Sakit</div>
                <div class="text-base font-black text-amber-800">{{ $sakit }}</div>
            </div>
            <div class="border border-rose-300 rounded p-2 bg-rose-50">
                <div class="text-[10px] font-bold text-rose-700 uppercase">Belum / Alpa</div>
                <div class="text-base font-black text-rose-800">{{ $belum + $alpa }}</div>
            </div>
        </div>

        <!-- Tabel Presensi -->
        <table class="w-full text-xs border-collapse border border-slate-400 mb-8">
            <thead>
                <tr class="bg-slate-100 text-slate-800 text-center font-bold">
                    <th class="border border-slate-400 py-2 px-1 w-10">No</th>
                    <th class="border border-slate-400 py-2 px-2 w-28">NISN</th>
                    <th class="border border-slate-400 py-2 px-3 text-left">Nama Siswa</th>
                    <th class="border border-slate-400 py-2 px-2 w-20">Jam</th>
                    <th class="border border-slate-400 py-2 px-2 w-24">Status</th>
                    <th class="border border-slate-400 py-2 px-3 text-left">Keterangan</th>
                    <th class="border border-slate-400 py-2 px-2 w-24">Metode</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataSiswa as $s)
                <tr class="{{ $loop->even ? 'bg-slate-50/70' : 'bg-white' }}">
                    <td class="border border-slate-400 py-1.5 px-1 text-center font-bold">{{ $s['no'] }}</td>
                    <td class="border border-slate-400 py-1.5 px-2 text-center font-mono text-[11px]">{{ $s['nisn'] }}</td>
                    <td class="border border-slate-400 py-1.5 px-3 font-semibold">{{ $s['nama'] }}</td>
                    <td class="border border-slate-400 py-1.5 px-2 text-center font-sans-ui text-[11px]">{{ $s['jam'] }}</td>
                    <td class="border border-slate-400 py-1.5 px-2 text-center font-bold font-sans-ui text-[11px]">
                        @if($s['status'] === 'Hadir')
                            <span class="text-emerald-700">Hadir</span>
                        @elseif($s['status'] === 'Izin')
                            <span class="text-sky-700">Izin</span>
                        @elseif($s['status'] === 'Sakit')
                            <span class="text-amber-700">Sakit</span>
                        @elseif($s['status'] === 'Alpa')
                            <span class="text-rose-700">Alpa</span>
                        @else
                            <span class="text-slate-500 italic">Belum Presensi</span>
                        @endif
                    </td>
                    <td class="border border-slate-400 py-1.5 px-3 text-slate-700 text-[11px]">{{ $s['keterangan'] }}</td>
                    <td class="border border-slate-400 py-1.5 px-2 text-center text-slate-600 text-[10px] font-sans-ui">{{ $s['metode'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Lembar Tanda Tangan -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-10 pt-4">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala Sekolah {{ config('binaro.nama_sekolah') }}</p>
                <div class="h-20"></div>
                <p class="font-bold underline uppercase">{{ config('binaro.kepala_sekolah') }}</p>
                <p class="text-[11px] text-slate-600">NIP. {{ config('binaro.kepala_sekolah_nip') }}</p>
            </div>
            <div>
                <p>{{ config('binaro.kota') }}, {{ \Carbon\Carbon::parse($today)->translatedFormat('d F Y') }}</p>
                <p class="font-bold">Wali Kelas {{ $namaKelas }}</p>
                <div class="h-20"></div>
                <p class="font-bold underline uppercase">{{ $guru->nama_guru ?? 'Guru' }}</p>
                <p class="text-[11px] text-slate-600">{{ ($guru->nip ?? null) ? 'NIP. ' . $guru->nip : '' }}</p>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-200 text-[10px] text-slate-400 font-sans-ui flex justify-between items-center">
            <span>Sahabat Belajar • Dokumen Resmi Presensi Kelas Terpadu</span>
            <span>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
        </div>

    </div>

</body>
</html>

