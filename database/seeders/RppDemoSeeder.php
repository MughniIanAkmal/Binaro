<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\JadwalMataPelajaran;
use App\Models\Rpp;

class RppDemoSeeder extends Seeder
{
    public function run(): void
    {
        $guru = Guru::firstOrCreate(
            ['nip' => '198501012010012001'],
            [
                'nama_guru' => 'Ibu Sarah Wijaya, S.Pd.',
                'email' => 'sarah.wijaya@kalitapen01.sch.id',
                'no_hp' => '081234567890',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Kalitapen No. 12',
                'username' => 'sarahguru',
                'password' => 'guru123',
            ]
        );

        $kelas = Kelas::firstOrCreate(['pararel' => 'Kelas 4B']);

        $mapelMat = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Matematika'],
            ['deskripsi' => 'Pembelajaran konsep bilangan, operasi hitung, pecahan, dan geometri dasar.']
        );

        $mapelIpa = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Ilmu Pengetahuan Alam (IPA)'],
            ['deskripsi' => 'Eksplorasi sains alam, ekosistem, gaya dan gerak, serta tata surya.']
        );

        $mapelBindo = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Bahasa Indonesia'],
            ['deskripsi' => 'Membaca pemahaman, tata bahasa, penulisan cerita, dan apresiasi sastra anak.']
        );

        $mapelPancasila = MataPelajaran::firstOrCreate(
            ['nama_mapel' => 'Pendidikan Pancasila'],
            ['deskripsi' => 'Pembiasaan norma, aturan di sekolah, dan pengamalan nilai-nilai luhur Pancasila.']
        );

        // Jadwal Roster
        $j1 = JadwalMataPelajaran::firstOrCreate([
            'id_guru' => $guru->id_guru,
            'id_mapel' => $mapelMat->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'hari' => 'Senin',
        ], [
            'jam' => '08.00 - 09.30 WIB',
        ]);

        $j2 = JadwalMataPelajaran::firstOrCreate([
            'id_guru' => $guru->id_guru,
            'id_mapel' => $mapelIpa->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'hari' => 'Selasa',
        ], [
            'jam' => '09.45 - 11.30 WIB',
        ]);

        $j3 = JadwalMataPelajaran::firstOrCreate([
            'id_guru' => $guru->id_guru,
            'id_mapel' => $mapelBindo->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'hari' => 'Kamis',
        ], [
            'jam' => '07.15 - 08.45 WIB',
        ]);

        $j4 = JadwalMataPelajaran::firstOrCreate([
            'id_guru' => $guru->id_guru,
            'id_mapel' => $mapelPancasila->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'hari' => 'Senin',
        ], [
            'jam' => '09.45 - 11.00 WIB',
        ]);

        // RPP 1: Matematika Pecahan Senilai
        Rpp::updateOrCreate([
            'id_guru' => $guru->id_guru,
            'judul_rpp' => 'Konsep Pecahan Senilai & Membandingkan Pecahan',
        ], [
            'id_mapel' => $mapelMat->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'id_jadwal' => $j1->id_jadwal,
            'fase' => 'Fase B',
            'modul_ke' => 'Modul #28',
            'alokasi_waktu' => '2 JP (2 x 35 Menit)',
            'target_jadwal' => 'Senin, 08.00 - 09.30 WIB - Jam ke-1 & 2',
            'ruang' => 'Ruang Kelas 4B (Gedung Melati)',
            'target_tanggal' => 'Jadwal Berikut & Sesi 1',
            'deskripsi' => 'Peserta didik mampu membandingkan dua pecahan berpenyebut sama & berbeda menggunakan alat peraga konkret semangka digital serta menyusun urutan dari terkecil.',
            'komponen_checklist' => [
                'tujuan' => true,
                'video' => true,
                'kktp' => true,
                'lkpd' => true,
                'soal_proyektor' => true,
                'tags' => ['Tujuan Pembelajaran', 'Video Animasi Interaktif', '10 Soal Proyektor', 'LKPD Digital Siap Cetak'],
            ],
            'status' => 'terverifikasi',
        ]);

        // RPP 2: IPAS Daur Hidup Kupu-kupu
        Rpp::updateOrCreate([
            'id_guru' => $guru->id_guru,
            'judul_rpp' => 'Daur Hidup Kupu-kupu & Metamorfosis Sempurna',
        ], [
            'id_mapel' => $mapelIpa->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'id_jadwal' => $j2->id_jadwal,
            'fase' => 'Fase B',
            'modul_ke' => 'Modul #06',
            'alokasi_waktu' => '3 JP (105 Menit)',
            'target_jadwal' => 'Selasa, 09.45 - 11.30 WIB - Jam ke-4, 5 & 6',
            'ruang' => 'Ruang Lab IPA & Taman Sekolah',
            'target_tanggal' => 'Besok, 21 Mei 2024',
            'deskripsi' => 'Mengidentifikasi tahapan metamorfosis sempurna serta menganalisis peran ulat dan kupu-kupu dalam menjaga keseimbangan ekosistem taman sekolah.',
            'komponen_checklist' => [
                'tujuan' => true,
                'video' => true,
                'kktp' => true,
                'lkpd' => true,
                'soal_proyektor' => true,
                'tags' => ['Video Pembelajaran 3D', 'Lembar Observasi Taman', 'Kuis Game Interaktif (5 Soal)'],
            ],
            'status' => 'terverifikasi',
        ]);

        // RPP 3: Bahasa Indonesia Teks Narasi Cerita Rakyat
        Rpp::updateOrCreate([
            'id_guru' => $guru->id_guru,
            'judul_rpp' => 'Membaca Intensif Teks Narasi Cerita Rakyat Nusantara',
        ], [
            'id_mapel' => $mapelBindo->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'id_jadwal' => $j3->id_jadwal,
            'fase' => 'Fase B',
            'modul_ke' => 'Modul #11',
            'alokasi_waktu' => '2 JP (70 Menit)',
            'target_jadwal' => 'Kamis, 07.15 - 08.45 WIB - Jam Ke-1 & 2',
            'ruang' => 'Ruang Kelas 4B (Pojok Baca)',
            'target_tanggal' => 'Kamis, 23 Mei 2024',
            'deskripsi' => 'Menemukan gagasan pokok, watak tokoh utama, serta pesan moral dalam teks narasi fiksi daerah dengan teknik membaca nyaring bergantian.',
            'komponen_checklist' => [
                'tujuan' => true,
                'video' => false,
                'kktp' => true,
                'lkpd' => true,
                'audio' => true,
                'tags' => ['Teks Bacaan Digital', 'Lembar Observasi Karakter', 'Audio Storytelling (Proses)'],
            ],
            'status' => 'terverifikasi',
        ]);

        // RPP 4: Pendidikan Pancasila Norma dan Aturan
        Rpp::updateOrCreate([
            'id_guru' => $guru->id_guru,
            'judul_rpp' => 'Norma dan Aturan dalam Kehidupan Sehari-hari di Sekolah',
        ], [
            'id_mapel' => $mapelPancasila->id_mapel,
            'id_rooms' => $kelas->id_rooms,
            'id_jadwal' => $j4->id_jadwal,
            'fase' => 'Fase B',
            'modul_ke' => 'Bab 4',
            'alokasi_waktu' => 'Alokasi 2 JP (70 Menit)',
            'target_jadwal' => 'Rencana Roster: Senin Pekan Depan - Jam Ke-3 & 4 (09.45 - 11.00 WIB)',
            'ruang' => 'Ruang Kelas 4B',
            'target_tanggal' => 'Target: 27 Mei 2024',
            'deskripsi' => 'Mengenal macam norma di lingkungan sekolah serta membiasakan sikap disiplin waktu datang ke sekolah dan mematuhi piket kelas.',
            'komponen_checklist' => [
                'tujuan' => true,
                'video' => false,
                'kktp' => false,
                'lkpd' => false,
                'tags' => ['Tujuan Pembelajaran Dasar'],
            ],
            'status' => 'draft',
        ]);
    }
}
