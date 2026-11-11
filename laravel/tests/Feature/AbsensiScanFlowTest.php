<?php

namespace Tests\Feature;

use App\Models\Absen;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiScanFlowTest extends TestCase
{
    use RefreshDatabase;

    private Guru $guru;

    private Siswa $siswa;

    private string $kode;

    protected function setUp(): void
    {
        parent::setUp();

        $mapel = MataPelajaran::create(['nama_mapel' => 'Matematika Uji']);
        $kelas = Kelas::create(['pararel' => 'Kelas Uji 1A']);
        $this->guru = Guru::create([
            'nip' => 'GURU-UJI-001',
            'nama_guru' => 'Guru Uji Scan',
            'password' => bcrypt('password'),
        ]);
        $this->siswa = Siswa::create([
            'nisn' => '0099990001',
            'nm_siswa' => 'Siswa Scan Uji',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);
        // SiswaObserver otomatis membuat barcode saat siswa dibuat.
        $this->kode = $this->siswa->barcode->kode_barcode;
    }

    private function sebagaiGuru()
    {
        return $this->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru']);
    }

    public function test_scan_web_jam_tepat_waktu_mencatat_keterangan_tepat_waktu(): void
    {
        $this->travelTo(now()->setTime(7, 30));

        $response = $this->sebagaiGuru()
            ->postJson('/guru/absen/scan', ['kode' => $this->kode, 'metode' => 'scan']);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('statusAbsen', 'Hadir')
            ->assertJsonPath('message', 'Hadir: '.$this->siswa->nm_siswa);

        $this->assertDatabaseHas('absen', [
            'id_siswa' => $this->siswa->id_siswa,
            'status' => 'Hadir',
            'keterangan' => 'Tepat Waktu',
            'metode' => 'scan_qr',
        ]);
    }

    public function test_scan_web_setelah_batas_tutup_tercatat_terlambat(): void
    {
        $this->travelTo(now()->setTime(13, 0));

        $response = $this->sebagaiGuru()
            ->postJson('/guru/absen/scan', ['kode' => $this->kode, 'metode' => 'scan']);

        $response->assertOk()->assertJsonPath('statusAbsen', 'Terlambat');

        $this->assertDatabaseHas('absen', [
            'id_siswa' => $this->siswa->id_siswa,
            'keterangan' => 'Terlambat',
        ]);
    }

    public function test_scan_duplikat_dalam_hari_yang_sama_ditolak(): void
    {
        $this->sebagaiGuru()->postJson('/guru/absen/scan', ['kode' => $this->kode]);

        $response = $this->sebagaiGuru()->postJson('/guru/absen/scan', ['kode' => $this->kode]);

        $response->assertOk()->assertJsonPath('status', 'duplikat');
        $this->assertDatabaseCount('absen', 1);
    }

    public function test_scan_meningkatkan_alpa_otomatis_menjadi_hadir(): void
    {
        $this->travelTo(now()->setTime(7, 30));

        Absen::create([
            'id_guru' => $this->guru->id_guru,
            'id_siswa' => $this->siswa->id_siswa,
            'status' => 'Alpa',
            'metode' => 'manual_guru',
            'keterangan' => 'Alpha otomatis - tanpa keterangan',
            'waktu_absen' => now(),
            'tanggal' => today()->toDateString(),
        ]);

        $response = $this->sebagaiGuru()->postJson('/guru/absen/scan', ['kode' => $this->kode]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('statusAbsen', 'Hadir');
        $this->assertStringContainsString('Alpa otomatis diperbarui', $response->json('message'));

        $this->assertDatabaseHas('absen', [
            'id_siswa' => $this->siswa->id_siswa,
            'status' => 'Hadir',
        ]);
    }

    public function test_api_guru_scan_qr_mencatat_keterangan_waktu(): void
    {
        $this->travelTo(now()->setTime(13, 0));

        $response = $this->actingAs($this->guru, 'sanctum')
            ->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->postJson('/api/guru/absensi/scan-qr', ['kode_barcode' => $this->kode]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Hadir')
            ->assertJsonPath('data.keterangan', 'Terlambat');

        $this->assertDatabaseHas('absen', [
            'id_siswa' => $this->siswa->id_siswa,
            'metode' => 'scan_qr',
            'keterangan' => 'Terlambat',
        ]);
    }

    public function test_api_guru_scan_qr_menolak_kode_tak_dikenal(): void
    {
        $response = $this->actingAs($this->guru, 'sanctum')
            ->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
            ->postJson('/api/guru/absensi/scan-qr', ['kode_barcode' => 'QR-TIDAKADA']);

        $response->assertNotFound()->assertJsonPath('success', false);
    }

    public function test_api_siswa_self_scan_hanya_dengan_barcode_sendiri(): void
    {
        $this->travelTo(now()->setTime(7, 30));

        $response = $this->actingAs($this->siswa, 'sanctum')
            ->withSession(['user_id' => $this->siswa->id_siswa, 'user_type' => 'siswa'])
            ->postJson('/api/siswa/absensi-scan', ['id_barcode' => $this->siswa->barcode->id_barcode]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.keterangan', 'Tepat Waktu');

        $this->assertDatabaseHas('absen', [
            'id_siswa' => $this->siswa->id_siswa,
            'status' => 'Hadir',
            'metode' => 'scan_qr',
        ]);
    }

    public function test_alpa_otomatis_mengisi_siswa_yang_belum_absen_pada_tanggal_lampau(): void
    {
        $kemarin = now()->subDay()->toDateString();
        Absen::create([
            'id_guru' => $this->guru->id_guru,
            'id_siswa' => $this->siswa->id_siswa,
            'status' => 'Hadir',
            'metode' => 'scan_qr',
            'keterangan' => 'Tepat Waktu',
            'waktu_absen' => now()->subDay(),
            'tanggal' => $kemarin,
        ]);
        $siswaLain = Siswa::create([
            'nisn' => '0099990002',
            'nm_siswa' => 'Siswa Bolos Kemarin',
            'password' => bcrypt('password'),
            'id_mapel' => $this->siswa->id_mapel,
            'id_rooms' => $this->siswa->id_rooms,
        ]);

        $service = app(AbsensiService::class);
        $dibuat = $service->ensureAlphaOtomatis($kemarin);

        $this->assertSame(1, $dibuat);
        $alpaRow = Absen::where('id_siswa', $siswaLain->id_siswa)->whereDate('tanggal', $kemarin)->first();
        $this->assertNotNull($alpaRow);
        $this->assertSame('Alpa', $alpaRow->status);

        // Idempoten: panggilan ulang tidak menambah record.
        $this->assertSame(0, $service->ensureAlphaOtomatis($kemarin));
    }

    public function test_alpa_otomatis_hari_ini_dilewati_sebelum_batas_tutup(): void
    {
        $this->travelTo(now()->setTime(7, 0));

        $dibuat = app(AbsensiService::class)->ensureAlphaOtomatis(today()->toDateString());

        $this->assertSame(0, $dibuat);
        $this->assertDatabaseCount('absen', 0);
    }
}
