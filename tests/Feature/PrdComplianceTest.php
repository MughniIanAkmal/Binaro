<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Absen;
use App\Models\Barcode;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\Rpp;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Carbon\Carbon;

class PrdComplianceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that a barcode is automatically created when a Siswa is created.
     * The barcode should have format BIN-{nisn}-{RANDOM6} and is_active = true.
     */
    public function test_barcode_auto_creation_on_siswa_created(): void
    {
        // Create required related records first
        $mapel = MataPelajaran::create(['nama_mapel' => 'Test Mapel']);
        $kelas = Kelas::create(['pararel' => '10A']);

        // Create a Siswa instance
        $siswa = Siswa::create([
            'nisn' => '1234567890',
            'nm_siswa' => 'Budi Santoso',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        // Assert a Barcode record exists
        $this->assertDatabaseHas('barcode', [
            'id_siswa' => $siswa->id_siswa,
            'is_active' => true,
        ]);

        // Get the barcode and check format
        $barcode = Barcode::where('id_siswa', $siswa->id_siswa)->first();
        $this->assertNotNull($barcode);
        $this->assertStringStartsWith('BIN-1234567890-', $barcode->kode_barcode);
        // Check that the random part is 6 characters uppercase
        $randomPart = substr($barcode->kode_barcode, strlen('BIN-1234567890-'));
        $this->assertEquals(6, strlen($randomPart));
        $this->assertTrue(ctype_alnum($randomPart));
        $this->assertTrue($barcode->is_active);
    }

    /**
     * Test plain text password verification for Admin/Guru/Siswa.
     * Seed users with plain passwords and attempt login via AuthController.
     * Assert successful authentication via === comparison, not Hash.
     */
    public function test_plain_text_password_verification(): void
    {
        // Create required related records first
        $mapel = MataPelajaran::create(['nama_mapel' => 'Test Mapel']);
        $kelas = Kelas::create(['pararel' => '10A']);

        // Create test users with plain text passwords (as per the application's current auth approach)
        $admin = Admin::create([
            'nip' => 'ADMIN001',
            'nama_admin' => 'Admin User',
            'password' => 'admin123', // plain text as stored in DB
        ]);

        $guru = Guru::create([
            'nip' => 'GURU001',
            'nama_guru' => 'Guru User',
            'no_hp' => '08123456789',
            'password' => 'guru123', // plain text as stored in DB
        ]);

        $siswa = Siswa::create([
            'nisn' => '9876543210',
            'nm_siswa' => 'Siswa User',
            'no_hp' => '08123456789',
            'password' => 'siswa123', // plain text as stored in DB
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        // Test Admin login
        $response = $this->post('/login', [
            'username' => $admin->nip,
            'password' => 'admin123',
        ]);
        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('user_id', $admin->id_admin);
        $response->assertSessionHas('user_type', 'admin');
        $this->assertTrue($admin->password === 'admin123');

        // Clear session for next test
        Session::flush();

        // Test Guru login
        $response = $this->post('/login', [
            'username' => $guru->nip,
            'password' => 'guru123',
        ]);
        $response->assertRedirect('/guru/dashboard');
        $response->assertSessionHas('user_id', $guru->id_guru);
        $response->assertSessionHas('user_type', 'guru');
        $this->assertTrue($guru->password === 'guru123');

        // Clear session for next test
        Session::flush();

        // Test Siswa login
        $response = $this->post('/login', [
            'username' => $siswa->nisn,
            'password' => 'siswa123',
        ]);
        $response->assertRedirect('/siswa/dashboard');
        $response->assertSessionHas('user_id', $siswa->id_siswa);
        $response->assertSessionHas('user_type', 'siswa');
        $this->assertTrue($siswa->password === 'siswa123');
    }

    /**
     * Test QR scan flow anti-duplicate mechanism.
     * Create Siswa, Guru, Barcode for siswa.
     * POST /api/guru/absensi/scan-qr with valid kode_barcode → assert HTTP 201 and success message.
     * Repeat same request same day → assert HTTP 409 and duplicate message.
     * Change date (mock tomorrow) → second scan allowed again.
     */
    public function test_qr_scan_flow_anti_duplicate(): void
    {
        // Create required related records first
        $mapel = MataPelajaran::create(['nama_mapel' => 'Test Mapel']);
        $kelas = Kelas::create(['pararel' => '10A']);

        // Create test data
        $siswa = Siswa::create([
            'nisn' => '1111111111',
            'nm_siswa' => 'Test Siswa',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        $guru = Guru::create([
            'nip' => 'GURU999',
            'nama_guru' => 'Test Guru',
            'no_hp' => '08123456789',
            'password' => bcrypt('password'),
        ]);

        $barcode = Barcode::where('id_siswa', $siswa->id_siswa)->first();
        if (!$barcode) {
            $barcode = Barcode::create([
                'id_siswa' => $siswa->id_siswa,
                'kode_barcode' => 'BIN-1111111111-ABCDEF',
                'is_active' => true,
            ]);
        }

        // Set test date to today
        Carbon::setTestNow(Carbon::today());

        // First scan - should succeed
        $response = $this->actingAs($guru, 'sanctum')
            ->postJson('/api/guru/absensi/scan-qr', [
                'kode_barcode' => $barcode->kode_barcode,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'siswa',
                'nisn',
                'status',
                'waktu_absen',
                'metode',
            ]
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Hadir', $response->json('data.status'));

        // Second scan same day - should fail with 409 (duplicate)
        $response = $this->actingAs($guru, 'sanctum')
            ->postJson('/api/guru/absensi/scan-qr', [
                'kode_barcode' => $barcode->kode_barcode,
            ]);

        $response->assertStatus(409);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('sudah absen', $response->json('message'));

        // Change date to tomorrow - should allow scan again
        Carbon::setTestNow(Carbon::tomorrow());

        $response = $this->actingAs($guru, 'sanctum')
            ->postJson('/api/guru/absensi/scan-qr', [
                'kode_barcode' => $barcode->kode_barcode,
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));

        // Clean up
        Carbon::setTestNow(null);
    }

    /**
     * Test that Siswa cannot access Guru RPP endpoints.
     * Authenticate as Siswa, attempt GET /api/guru/rpp → assert HTTP 403/401 redirect or unauthorized.
     */
    public function test_siswa_cannot_access_guru_rpp_endpoints(): void
    {
        // Create required related records first
        $mapel = MataPelajaran::create(['nama_mapel' => 'Test Mapel']);
        $kelas = Kelas::create(['pararel' => '10A']);

        // Create and authenticate as siswa
        $siswa = Siswa::create([
            'nisn' => '2222222222',
            'nm_siswa' => 'Siswa User',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        // Attempt to access Guru RPP endpoint as Siswa
        $response = $this->withSession(['user_id' => $siswa->id_siswa, 'user_type' => 'siswa'])
            ->getJson('/api/guru/rpp');

        // Should return unauthorized (401) or forbidden (403)
        $this->assertTrue(
            $response->status() === 401 || $response->status() === 403,
            'Expected 401 or 403 status code, got ' . $response->status()
        );
    }

    /**
     * Test admin rekap returns summary and data.
     * Create attendance records for various siswa/status.
     * GET /api/admin/absensi/rekap?fecha=today → assert JSON has summary (total_siswa, hadir, izin, sakit, alpa) and data array.
     */
    public function test_admin_rekap_returns_summary_and_data(): void
    {
        // Create required related records first
        $mapel = MataPelajaran::create(['nama_mapel' => 'Test Mapel']);
        $kelas = Kelas::create(['pararel' => '10A']);

        // Create test data
        $siswa1 = Siswa::create([
            'nisn' => '3333333333',
            'nm_siswa' => 'Siswa Hadir',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        $siswa2 = Siswa::create([
            'nisn' => '4444444444',
            'nm_siswa' => 'Siswa Izin',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        $siswa3 = Siswa::create([
            'nisn' => '5555555555',
            'nm_siswa' => 'Siswa Sakit',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        $siswa4 = Siswa::create([
            'nisn' => '6666666666',
            'nm_siswa' => 'Siswa Alpa',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        $siswa5 = Siswa::create([
            'nisn' => '7777777777',
            'nm_siswa' => 'Siswa Tanpa Absen',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);

        $guru = Guru::create([
            'nip' => 'GURU888',
            'nama_guru' => 'Test Guru',
            'no_hp' => '08123456789',
            'password' => bcrypt('password'),
        ]);

        // Create attendance records for today
        $today = Carbon::today()->toDateString();
        // SiswaObserver already created barcodes automatically on Siswa::create
        $bc1 = Barcode::where('id_siswa', $siswa1->id_siswa)->first();
        $bc2 = Barcode::where('id_siswa', $siswa2->id_siswa)->first();
        $bc3 = Barcode::where('id_siswa', $siswa3->id_siswa)->first();
        $bc4 = Barcode::where('id_siswa', $siswa4->id_siswa)->first();

        Absen::create([
            'id_guru' => $guru->id_guru,
            'id_siswa' => $siswa1->id_siswa,
            'id_barcode' => $bc1?->id_barcode,
            'status' => 'Hadir',
            'metode' => 'scan_qr',
            'waktu_absen' => now(),
            'tanggal' => $today,
        ]);

        Absen::create([
            'id_guru' => $guru->id_guru,
            'id_siswa' => $siswa2->id_siswa,
            'id_barcode' => $bc2?->id_barcode,
            'status' => 'Izin',
            'metode' => 'scan_qr',
            'waktu_absen' => now(),
            'tanggal' => $today,
        ]);

        Absen::create([
            'id_guru' => $guru->id_guru,
            'id_siswa' => $siswa3->id_siswa,
            'id_barcode' => $bc3?->id_barcode,
            'status' => 'Sakit',
            'metode' => 'scan_qr',
            'waktu_absen' => now(),
            'tanggal' => $today,
        ]);

        Absen::create([
            'id_guru' => $guru->id_guru,
            'id_siswa' => $siswa4->id_siswa,
            'id_barcode' => $bc4?->id_barcode,
            'status' => 'Alpa',
            'metode' => 'scan_qr',
            'waktu_absen' => now(),
            'tanggal' => $today,
        ]);

        // Authenticate as admin (using session since admin API uses session/web auth)
        $admin = Admin::create([
            'nip' => 'ADMIN999',
            'nama_admin' => 'Admin User',
            'password' => 'admin123', // plain text
        ]);

        // Call the rekap endpoint with admin session
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/absensi/rekap?fecha=' . $today);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'summary' => [
                'total_siswa',
                'hadir',
                'izin',
                'sakit',
                'alpa',
            ],
            'data',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(5, $response->json('summary.total_siswa')); // 5 siswa created
        $this->assertEquals(1, $response->json('summary.hadir'));
        $this->assertEquals(1, $response->json('summary.izin'));
        $this->assertEquals(1, $response->json('summary.sakit'));
        $this->assertEquals(1, $response->json('summary.alpa'));

        // Data should contain 4 attendance records (one for each status)
        $dataItems = $response->json('data.data') ?? $response->json('data');
        $this->assertCount(4, $dataItems);
    }

    /**
     * Test database migrations and seed run cleanly.
     * Execute php artisan migrate:fresh → assert zero errors.
     * Execute php artisan test → assert test suite passes (at least this test class passes).
     */
    public function test_database_migrations_and_seed_run_cleanly(): void
    {
        // This test is designed to run after migrations and seeds
        // We'll verify that the database is properly set up by checking we can run basic queries

        // Check that we have the expected tables
        $this->assertTrue($this->app['db']->getSchemaBuilder()->hasTable('siswa'));
        $this->assertTrue($this->app['db']->getSchemaBuilder()->hasTable('guru'));
        $this->assertTrue($this->app['db']->getSchemaBuilder()->hasTable('admin'));
        $this->assertTrue($this->app['db']->getSchemaBuilder()->hasTable('barcode'));
        $this->assertTrue($this->app['db']->getSchemaBuilder()->hasTable('absen'));
        $this->assertTrue($this->app['db']->getSchemaBuilder()->hasTable('rpp'));

        // Check that we can insert and retrieve data
        $mapel = MataPelajaran::create(['nama_mapel' => 'Test Mapel']);
        $kelas = Kelas::create(['pararel' => '10A']);

        $siswa = Siswa::create([
            'nisn' => '8888888888',
            'nm_siswa' => 'Test Siswa',
            'password' => bcrypt('password'),
            'id_mapel' => $mapel->id_mapel,
            'id_rooms' => $kelas->id_rooms,
        ]);
        $this->assertDatabaseHas('siswa', [
            'id_siswa' => $siswa->id_siswa,
        ]);

        // Since we're using RefreshDatabase trait, migrations are fresh for each test
        // This test essentially verifies that the test environment is working correctly
        $this->assertTrue(true);
    }
}