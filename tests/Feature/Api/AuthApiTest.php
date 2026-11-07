<?php
namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function buatSiswa(string $nisn = '111222333', string $password = 'rahasia123')
    {
        return Siswa::create([
            'nisn' => $nisn,
            'nm_siswa' => 'Siswa '.$nisn,
            'password' => Hash::make($password),
        ]);
    }

    private function buatGuru(string $nip = '19900101', string $password = 'guru123')
    {
        return Guru::create([
            'nip' => $nip,
            'nama_guru' => 'Guru '.$nip,
            'password' => Hash::make($password),
        ]);
    }

    public function test_login_menghasilkan_token_sanctum()
    {
        $this->buatSiswa();
        $response = $this->postJson('/api/login', [
            'nisn' => '111222333',
            'password' => 'rahasia123',
            'nama_lengkap' => '',
        ]);
        $response->assertOk()->assertJsonPath('success', true);
        $this->assertMatchesRegularExpression('/^\d+\|.+$/', $response->json('data.token'));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_password_legacy_plain_diupgrade_setelah_login()
    {
        // simpan plain password
        $siswa = Siswa::create([
            'nisn' => '222333444',
            'nm_siswa' => 'Legacy',
            'password' => 'plainpass', // plain
        ]);
        $this->postJson('/api/login', [
            'nisn' => '222333444',
            'password' => 'plainpass',
            'nama_lengkap' => '',
        ])->assertOk();
        $this->assertStringStartsWith('$2y$', Siswa::where('nisn','222333444')->first()->password);
    }

    public function test_logout_hanya_mencabut_token_aktif()
    {
        $siswa = $this->buatSiswa();
        $login = $this->postJson('/api/login', [
            'nisn' => '111222333',
            'password' => 'rahasia123',
            'nama_lengkap' => '',
        ]);
        $token = $login->json('data.token');
        // create another token manually
        $other = $siswa->createToken('other')->plainTextToken;
        // logout with first token
        $this->withHeaders(['Authorization' => "Bearer $token"])->postJson('/api/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => explode('|', $other)[0]]);
    }
}
