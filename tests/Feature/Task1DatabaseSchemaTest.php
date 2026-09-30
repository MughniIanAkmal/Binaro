<?php

namespace Tests\Feature;

use App\Models\Absen;
use App\Models\Barcode;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Task1DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_system_tables_exist(): void
    {
        $tables = ['admin', 'guru', 'mata_pelajaran', 'kelas', 'siswa', 'barcode', 'rpp', 'absen'];
        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} missing from schema.");
        }
    }

    public function test_table_columns_and_defaults(): void
    {
        $this->assertTrue(Schema::hasColumns('guru', ['id_guru', 'nip', 'nama_guru', 'no_hp', 'password']));
        $this->assertTrue(Schema::hasColumns('siswa', ['id_siswa', 'id_mapel', 'id_rooms', 'nisn', 'nm_siswa', 'no_hp', 'password', 'foto_profil']));
        $this->assertTrue(Schema::hasColumns('barcode', ['id_barcode', 'id_siswa', 'kode_barcode', 'qr_image_path', 'is_active']));
        $this->assertTrue(Schema::hasColumns('absen', ['id_absen', 'id_guru', 'id_siswa', 'id_barcode', 'status', 'keterangan', 'berkas_surat', 'metode', 'waktu_absen', 'tanggal']));
        $this->assertTrue(Schema::hasColumns('rpp', ['id_rpp', 'id_guru', 'id_rooms', 'id_mapel', 'judul_rpp', 'deskripsi', 'komponen_checklist', 'status', 'file_rpp']));
    }

    public function test_siswa_observer_auto_creates_barcode(): void
    {
        $siswa = Siswa::create([
            'nisn' => '1234567890',
            'nm_siswa' => 'Budi Santoso',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('barcode', [
            'id_siswa' => $siswa->id_siswa,
            'is_active' => true,
        ]);

        $barcode = Barcode::where('id_siswa', $siswa->id_siswa)->first();
        $this->assertNotNull($barcode);
        $this->assertStringStartsWith('BIN-1234567890-', $barcode->kode_barcode);
    }
}