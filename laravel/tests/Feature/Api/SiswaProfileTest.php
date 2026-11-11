<?php
namespace Tests\Feature\Api;

use App\Models\Absen;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SiswaProfileTest extends TestCase
{
    use RefreshDatabase;

    private function buatSiswa(string $nisn): Siswa
    {
        return Siswa::create([
            'nisn' => $nisn,
            'nm_siswa' => 'Siswa '.$nisn,
            'password' => Hash::make('rahasia123'),
        ]);
    }

    public function test_profil_mengikuti_token_bukan_session(): void
    {
        $siswa = $this->buatSiswa('111222333');
        Sanctum::actingAs($siswa);

        $this->getJson('/api/siswa/profile')
            ->assertOk()
            ->assertJsonPath('data.nisn', '111222333')
            ->assertJsonPath('success', true);

        $this->assertArrayNotHasKey('password', $this->getJson('/api/siswa/profile')->json('data'));
    }

    public function test_parameter_id_tidak_bisa_mengambil_siswa_lain(): void
    {
        $siswaA = $this->buatSiswa('111222333');
        $siswaB = $this->buatSiswa('999888777');
        Sanctum::actingAs($siswaA);

        $this->getJson('/api/siswa/profile?id='.$siswaB->id_siswa)
            ->assertOk()
            ->assertJsonPath('data.nisn', '111222333');
    }

    public function test_absensi_hari_ini_mengikuti_token(): void
    {
        $siswa = $this->buatSiswa('111222333');
        $guru = \App\Models\Guru::create(['nip' => '19900101', 'nama_guru' => 'Guru Uji']);
        Absen::create([
            'id_guru' => $guru->id_guru, 'id_siswa' => $siswa->id_siswa,
            'status' => 'Hadir', 'metode' => 'scan_qr',
            'waktu_absen' => now(), 'tanggal' => now()->toDateString(),
        ]);
        Sanctum::actingAs($siswa);

        $response = $this->getJson('/api/siswa/absensi-hari-ini');
        $response->assertOk();
        
        $data = $response->json('data');
        if ($data) {
            $this->assertEquals($siswa->id_siswa, $data['id_siswa']);
        } else {
            $this->fail('Absensi hari ini tidak ditemukan padahal sudah dibuat.');
        }
    }

    public function test_update_profil_hanya_milik_sendiri(): void
    {
        $siswaA = $this->buatSiswa('111222333');
        $siswaB = $this->buatSiswa('999888777');
        Sanctum::actingAs($siswaA);

        $this->postJson('/api/siswa/profile', ['no_hp' => '0811111111'])
            ->assertOk();

        $this->assertSame('0811111111', $siswaA->fresh()->no_hp);
        $this->assertNull($siswaB->fresh()->no_hp);
    }

    public function test_login_kemudian_token_dipakai_tanpa_session(): void
    {
        $this->buatSiswa('111222333');
        $login = $this->postJson('/api/login', ['nisn' => '111222333', 'password' => 'rahasia123']);

        $this->withToken($login->json('data.token'))
            ->getJson('/api/siswa/profile')
            ->assertOk()
            ->assertJsonPath('data.nisn', '111222333');
    }
}
