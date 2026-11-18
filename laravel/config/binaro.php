<?php

// Identitas & pengaturan global sekolah — satu sumber untuk semua layout, header, dan dokumen cetak.
// Nilai bisa dioverride lewat file .env (prefix BINARO_*).

return [
    'nama_sekolah' => env('BINARO_NAMA_SEKOLAH', 'SDN Kalitapen 01'),
    'tahun_ajaran' => env('BINARO_TAHUN_AJARAN', '2025/2026'),
    'semester' => env('BINARO_SEMESTER', 'Genap'),
    'kepala_sekolah' => env('BINARO_KEPALA_SEKOLAH', 'SUGIARTO, S.Pd., M.Pd.'),
    'kepala_sekolah_nip' => env('BINARO_KEPALA_SEKOLAH_NIP', '19700101 199503 1 001'),
    'kkm' => env('BINARO_KKM', 75),
    // Data kop surat dokumen cetak (masih placeholder — isi lewat .env saat data resmi tersedia)
    'kota' => env('BINARO_KOTA', 'Kalitapen'),
    'kabupaten_kop' => env('BINARO_KABUPATEN_KOP', 'PEMERINTAH KABUPATEN KARANGANYAR'),
    'dinas_kop' => env('BINARO_DINAS_KOP', 'DINAS PENDIDIKAN DAN KEBUDAYAAN'),
    'alamat_kop' => env('BINARO_ALAMAT_KOP', 'Jl. Pelajar Pintar No. 01, Telp: (0271) 789101, Email: info@sdnkaranganyar01.sch.id'),
    'npsn' => env('BINARO_NPSN', '20312345'),
];
