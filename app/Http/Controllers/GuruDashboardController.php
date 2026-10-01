<?php

namespace App\Http\Controllers;

class GuruDashboardController extends Controller
{
    public function index()
    {
        // Cek apakah guru sudah login
        if (session('login') !== true || session('role') !== 'guru') {
            return redirect()->route('login');
        }

        // Ambil data guru dari session
        $guru = [
            'nama' => session('nama'),
            'inisial' => strtoupper(substr(session('nama'), 0, 1)),
            'nip' => session('nip'),
            'kelas' => 'Kelas 4B',
            'sekolah' => 'SDN Kalitapen 01',
            'semester' => 'TA 2025/2026 Genap',
        ];

        // Statistik dashboard
        $statistik = [
            [
                'icon' => '📖',
                'jumlah' => 4,
                'judul' => 'Mata Pelajaran',
                'link' => 'Lihat Kurikulum',
                'warna' => 'blue',
            ],
            [
                'icon' => '▣',
                'jumlah' => 38,
                'judul' => 'Total Modul',
                'link' => 'Buka Modul Pembelajaran',
                'warna' => 'green',
            ],
            [
                'icon' => '☷',
                'jumlah' => 3,
                'judul' => 'Pekerjaan Rumah',
                'link' => 'Periksa Tugas Siswa',
                'warna' => 'yellow',
            ],
        ];

        // Materi pembelajaran
        $materi = [
            [
                'mapel' => 'Matematika',
                'status' => 'Aktif',
                'judul' => 'Membandingkan Pecahan',
                'icon' => '▶',
                'warna' => 'blue',
                'aksi' => 'Mulai Ajar',
            ],
            [
                'mapel' => 'IPA',
                'status' => 'Aktif',
                'judul' => 'Siklus Air dan Kehidupan di Bumi',
                'icon' => '◉',
                'warna' => 'green',
                'aksi' => 'Mulai Ajar',
            ],
            [
                'mapel' => 'IPA',
                'status' => 'Aktif',
                'judul' => 'Siklus Air dan Kehidupan di Bumi',
                'icon' => '◉',
                'warna' => 'green',
                'aksi' => 'Mulai Ajar',
            ],
        ];

        // Jadwal
        $jadwal = [
            [
                'jam' => '07:30 - 09:00',
                'mapel' => 'Matematika (4B)',
                'status' => 'Selesai',
                'warna' => 'green',
            ],
            [
                'jam' => '09:15 - 10:45',
                'mapel' => 'IPA Eksperimen (4A)',
                'status' => 'Berlangsung',
                'warna' => 'orange',
                'ruang' => 'Masuk Kelas (Proyektor)',
            ],
            [
                'jam' => '11:00 - 11:45',
                'mapel' => 'Konsultasi Wali Murid',
                'status' => 'Ruang Guru 1',
                'warna' => 'gray',
            ],
        ];

        // Pekerjaan rumah
        $pr = [
            [
                'mapel' => 'Matematika 4B',
                'judul' => 'Latihan Soal Cerita Pecahan',
                'terkumpul' => '24 / 28 Siswa (85%)',
                'deadline' => 'Besok 17:00',
                'status' => '4 Belum Mengumpulkan',
                'progress' => 85,
            ],
            [
                'mapel' => 'IPA 4B',
                'judul' => 'Mencatat Pengamatan Tanaman',
                'terkumpul' => '12 / 28 Siswa (42%)',
                'deadline' => 'Senin, 15 Sep',
                'status' => '16 Dalam Proses',
                'progress' => 42,
            ],
        ];

        return view('guru.dashboard', compact(
            'guru',
            'statistik',
            'materi',
            'jadwal',
            'pr'
        ));
    }
}