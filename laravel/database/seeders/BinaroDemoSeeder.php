<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Bab;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Quiz;
use App\Models\Siswa;
use App\Models\SoalQuiz;
use App\Models\SubBab;
use Illuminate\Database\Seeder;

class BinaroDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Demo (bisa login dengan username 'admin' atau NIP '19850101001')
        Admin::updateOrCreate(
            ['nip' => '19850101001'],
            [
                'nama_admin' => 'Administrator Utama',
                'password' => 'admin123',
            ]
        );
        Admin::updateOrCreate(
            ['nama_admin' => 'admin'],
            [
                'nip' => 'admin',
                'password' => 'admin123',
            ]
        );

        // 2. Guru Demo (18 Digit NIP)
        $guru = Guru::updateOrCreate(
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

        // 3. Kelas & Mapel
        $kelas = Kelas::firstOrCreate(['pararel' => 'Kelas 4B']);

        $mapelMat = MataPelajaran::updateOrCreate(
            ['nama_mapel' => 'Matematika'],
            ['deskripsi' => 'Pembelajaran konsep bilangan, operasi hitung, pecahan, dan geometri dasar.']
        );

        $mapelIpa = MataPelajaran::updateOrCreate(
            ['nama_mapel' => 'Ilmu Pengetahuan Alam (IPA)'],
            ['deskripsi' => 'Eksplorasi sains alam, ekosistem, gaya dan gerak, serta tata surya.']
        );

        $mapelBindo = MataPelajaran::updateOrCreate(
            ['nama_mapel' => 'Bahasa Indonesia'],
            ['deskripsi' => 'Membaca pemahaman, tata bahasa, penulisan cerita, dan apresiasi sastra anak.']
        );

        // 4. Sample Hierarchy untuk Matematika (Bab -> Sub-Bab -> Materi)
        $bab1 = Bab::firstOrCreate([
            'id_mapel' => $mapelMat->id_mapel,
            'nama_bab' => 'Bab 1: Operasi Hitung Bilangan Cacah',
        ]);

        $subBab1 = SubBab::firstOrCreate([
            'id_bab' => $bab1->id_bab,
            'nama_sub_bab' => 'Sub-Bab 1.1: Nilai Tempat dan Penjumlahan Ribuan',
        ]);

        Materi::firstOrCreate([
            'id_sub_bab' => $subBab1->id_sub_bab,
            'judul_materi' => 'Video Penjelasan Nilai Tempat Puluhan & Ribuan',
        ], [
            'id_bab' => $bab1->id_bab,
            'tipe_materi' => 'video',
            'url_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'isi_materi' => 'Simak video penjelasan berikut mengenai cara menentukan nilai tempat bilangan ribuan.',
        ]);

        // Kuis Demo
        $quiz = Quiz::firstOrCreate([
            'id_sub_bab' => $subBab1->id_sub_bab,
            'judul_quiz' => 'Kuis Evaluasi: Operasi Bilangan',
        ], [
            'id_mapel' => $mapelMat->id_mapel,
            'nama_quiz' => 'Kuis Evaluasi: Operasi Bilangan',
        ]);

        Materi::firstOrCreate([
            'id_sub_bab' => $subBab1->id_sub_bab,
            'judul_materi' => 'Kuis Evaluasi Bilangan Cacah',
        ], [
            'id_bab' => $bab1->id_bab,
            'tipe_materi' => 'kuis',
            'id_quiz' => $quiz->id_quiz,
            'isi_materi' => 'Kerjakan kuis 5 soal pilihan ganda berikut untuk menguji pemahaman Anda.',
        ]);

        // Bank Soal Kuis
        if (SoalQuiz::where('id_quiz', $quiz->id_quiz)->count() === 0) {
            $soals = [
                ['Berapakah hasil dari 2.450 + 1.320?', '3.770', '3.750', '3.870', '3.670', 'A'],
                ['Angka 7 pada bilangan 5.724 menempati nilai tempat?', 'Satuan', 'Puluhan', 'Ratusan', 'Ribuan', 'C'],
                ['Berapakah 500 dikalikan 4?', '1.500', '2.000', '2.500', '3.000', 'B'],
                ['Hasil dari 1.000 - 375 adalah?', '625', '635', '725', '525', 'A'],
                ['Bilangan genap antara 11 dan 15 adalah?', '12 dan 13', '12 dan 14', '13 dan 14', '14 dan 15', 'B'],
                ['Berapakah 8 x 7?', '54', '56', '58', '60', 'B'],
            ];
            foreach ($soals as $s) {
                SoalQuiz::create([
                    'id_quiz' => $quiz->id_quiz,
                    'pertanyaan' => $s[0],
                    'opsi_a' => $s[1],
                    'opsi_b' => $s[2],
                    'opsi_c' => $s[3],
                    'opsi_d' => $s[4],
                    'kunci_jawaban' => $s[5],
                ]);
            }
        }

        // 5. Siswa Demo (10 Digit NISN)
        Siswa::updateOrCreate(
            ['nisn' => '0012345678'],
            [
                'nm_siswa' => 'Budi Santoso',
                'email' => 'budi.santoso@siswa.kalitapen01.sch.id',
                'no_hp' => '089876543210',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Merdeka No. 45',
                'username' => 'budisiswa',
                'password' => 'siswa123',
                'id_rooms' => $kelas->id_rooms,
                'id_mapel' => $mapelMat->id_mapel,
            ]
        );
    }
}
