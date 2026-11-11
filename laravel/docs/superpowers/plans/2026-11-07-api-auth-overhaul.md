# API Auth Overhaul (Laravel ↔ Flutter) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti autentikasi API Binaro dari session/palsu menjadi Sanctum token yang sah, aman, dan teruji, plus service layer Flutter yang benar-benar mengirim token.

**Architecture:** Satu slice vertikal per task: test merah dulu → implementasi kecil → hijau → commit. Sisi Laravel: Sanctum token sebagai satu-satunya sumber identitas API (`$request->user()`), middleware role per prefix, response seragam `{success, message, data}`. Sisi Flutter: `ApiClient` + `AuthService` terpusat dengan token tersimpan di `shared_preferences`.

**Tech Stack:** Laravel 12 + PHP 8.3, laravel/sanctum, PHPUnit (SQLite :memory:), Laravel Pint; Flutter 3.47 / Dart 3.13, `http`, `shared_preferences`, `flutter_test` + `MockClient` (package:http/testing).

**Spec:** `docs/superpowers/specs/2026-11-07-api-auth-overhaul-design.md`

## Global Constraints

- **JANGAN sentuh** `tests/Feature/RppValidationAndLimitsTest.php` — sedang dimodifikasi user, belum di-commit.
- `git add` selalu dengan path eksplisit; **jangan pernah** `git add -A` / `git add .`.
- Bentuk response login TIDAK boleh berubah: `{success, message, data:{token, role, user:{...}}}` — Flutter sudah membacanya.
- Semua test API baru di `tests/Feature/Api/` (folder baru).
- Komentar & docblock bahasa Indonesia, mengikuti gaya project (lihat docblock `AbsensiService`).
- Pint hanya untuk path yang disentuh task: `vendor/bin/pint <path...>` (bukan seluruh repo).
- Format Dart hanya folder `binaro_flutter/lib` dan `binaro_flutter/test`.
- Route & flow login **web** tidak boleh berubah (routes/web.php, LoginController, EnsureRole tetap).
- Kredensial demo dari seeder: siswa `0012345678` / `siswa123`; siswa `0012345601` (ADIT) / `siswa1234`; guru `198501012010012001` / `guru123`.
- Fixture test (kolom wajib dari migration): `Siswa::create(['nisn','nm_siswa','password'])`, `Guru::create(['nip','nama_guru','password'])` (password nullable), `Admin::create(['nip','nama_admin','password'])`, `Barcode::create(['id_siswa','kode_barcode'])` (kode pola `QR-XXXX1234`), `Absen` wajib `id_guru` NOT NULL & unik `(id_siswa, tanggal)`.

## Review Focus

Lima hal paling mungkin menyakitkan pengguna, dan test yang menguncinya:

1. **Guard `sanctum` di `config/auth.php` dihapus** — jika framework melempar `Auth guard [sanctum] is not defined`, fix-nya adalah menambah entri `'sanctum' => ['driver' => 'sanctum']`. Dikunci di Task 1 (test resolusi guard) + Task 3 (E2E Bearer nyata).
2. **Password legacy plain/MD5** harus tetap bisa login dan ter-upgrade ke bcrypt — dikeluarkan dari DB = semua siswa lama terkunci. Dikunci di Task 2 (`password_legacy_plain_terupgrade_bcrypt`).
3. **Regresi login web** akibat perubahan config/model — dikelunci di Task 6 (`php artisan test` penuh, khususnya `LoginRoleTest`).
4. **Semantik token multi-perangkat** — logout hanya mencabut token aktif, perangkat lain tetap login. Dikunci di Task 2 (`logout_hanya_mencabut_token_aktif`).
5. **Flutter benar-benar mengirim `Authorization: Bearer`** dan membaca pesan error server — dikelunci di Task 7 (test header via MockClient) + Task 8 (E2E curl sungguhan).

---

### Task 1: Fondasi Sanctum & model aman

**Files:**
- Create: `tests/Feature/Api/ModelSecurityTest.php`
- Modify: `composer.json` + `composer.lock` (via `composer require laravel/sanctum`)
- Create: migration `database/migrations/*_create_personal_access_tokens_table.php` (hasil publish)
- Modify: `config/auth.php:48-51` (hapus entri guard `sanctum` palsu)
- Modify: `app/Models/Siswa.php`, `app/Models/Guru.php`, `app/Models/Admin.php`

**Interfaces:**
- Produces: trait `Laravel\Sanctum\HasApiTokens` aktif di 3 model; `$hidden = ['password']` di 3 model; tabel `personal_access_tokens` ada; guard `auth()->guard('sanctum')` ter-resolve. Task 2 memakai `$model->createToken('binaro-app')->plainTextToken`.

- [ ] **Step 1: Tulis test gagal `ModelSecurityTest`**

```php
// tests/Feature/Api/ModelSecurityTest.php
namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_tidak_membocorkan_password(): void
    {
        $siswa = Siswa::create(['nisn' => '111222333', 'nm_siswa' => 'Siswa Uji', 'password' => 'rahasia123']);

        $this->assertArrayNotHasKey('password', $siswa->toArray());
    }

    public function test_guru_tidak_membocorkan_password(): void
    {
        $guru = Guru::create(['nip' => '19900101', 'nama_guru' => 'Guru Uji', 'password' => 'rahasia123']);

        $this->assertArrayNotHasKey('password', $guru->toArray());
    }

    public function test_admin_tidak_membocorkan_password(): void
    {
        $admin = Admin::create(['nip' => 'ADM001', 'nama_admin' => 'Admin Uji', 'password' => 'rahasia123']);

        $this->assertArrayNotHasKey('password', $admin->toArray());
    }

    public function test_tiga_model_punya_trait_token_api(): void
    {
        foreach ([new Siswa, new Guru, new Admin] as $model) {
            $this->assertInstanceOf(\Laravel\Sanctum\HasApiTokens::class, $model);
        }
    }

    public function test_guard_sanctum_bisa_diresolusi(): void
    {
        $this->assertNotNull(auth()->guard('sanctum'));
    }
}
```

- [ ] **Step 2: Jalankan, pastikan MERAH**

Run: `php artisan test --filter=ModelSecurityTest`
Expected: FAIL — `password` masih ada di `toArray()` (3 test pertama); class `HasApiTokens` belum ada (test ke-4).

- [ ] **Step 3: Install Sanctum + konfigurasi**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=sanctum-migrations
php artisan migrate
```

Verifikasi tabel: `php artisan migrate:status` → baris `create_personal_access_tokens_table` = `Ran`.
Jika perintah publish gagal/tag tidak dikenal, cari file migration di `vendor/laravel/sanctum` dan salin ke `database/migrations/` dengan nama yang sama.

- [ ] **Step 4: Hapus guard palsu & tambah trait + $hidden**

Di `config/auth.php`, hapus entri persis ini (guard dengan driver session yang menutupi Sanctum):

```php
'sanctum' => [
    'driver' => 'session',
    'provider' => 'users',
],
```

Di ketiga model (`Siswa`, `Guru`, `Admin`): tambah `use Laravel\Sanctum\HasApiTokens;`, `use HasApiTokens;` di dalam class, dan `protected $hidden = ['password'];`.

- [ ] **Step 5: Jalankan test, pastikan HIJAU**

Run: `php artisan test --filter=ModelSecurityTest`
Expected: PASS semua (5 test).
Jika `test_guard_sanctum_bisa_diresolusi` gagal dengan `Auth guard [sanctum] is not defined` → tambahkan ke `config/auth.php`:

```php
'sanctum' => [
    'driver' => 'sanctum',
],
```

lalu ulangi Step 5. (Cabang ini sudah diperkirakan di Review Focus #1.)

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock config/auth.php app/Models/Siswa.php app/Models/Guru.php app/Models/Admin.php database/migrations/ tests/Feature/Api/ModelSecurityTest.php
git commit -m "pasang sanctum dan sembunyikan password dari response model"
```

(Catatan: `database/migrations/` cukup aman — pastikan tidak ada file lain yang ikut; jika ragu, sebut file migration-nya eksplisit.)

---

### Task 2: Login token nyata, upgrade password legacy, logout mencabut

**Files:**
- Create: `tests/Feature/Api/AuthApiTest.php`
- Modify: `app/Http/Controllers/Api/AuthApiController.php` (metode `login`, `logout`)
- Modify: `routes/api.php:16-17`

**Interfaces:**
- Consumes: `HasApiTokens` dari Task 1 (`createToken(string $name): HasApiTokens` → `->plainTextToken: string` format `id|secret`).
- Produces: `POST /api/login` mengembalikan token tersimpan di `personal_access_tokens`; `POST /api/logout` (auth:sanctum) mencabut token aktif; helper privat `upgradeLegacyPassword($user, string $plainPassword): void` di `AuthApiController`. Task 3 memakai token ini untuk E2E.

- [ ] **Step 1: Tulis test gagal `AuthApiTest`**

```php
// tests/Feature/Api/AuthApiTest.php
namespace Tests\Feature\Api;

use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function siswaLogin(string $password = 'rahasia123'): Siswa
    {
        return Siswa::create([
            'nisn' => '111222333',
            'nm_siswa' => 'Siswa Login Uji',
            'password' => Hash::make($password),
        ]);
    }

    public function test_login_menghasilkan_token_nyata(): void
    {
        $this->siswaLogin();

        $response = $this->postJson('/api/login', [
            'nisn' => '111222333', 'password' => 'rahasia123', 'nama_lengkap' => '',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertMatchesRegularExpression('/^\d+\|.+$/', $response->json('data.token'));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertArrayNotHasKey('password', $response->json('data.user'));
    }

    public function test_password_salah_ditolak_401(): void
    {
        $this->siswaLogin();

        $this->postJson('/api/login', ['nisn' => '111222333', 'password' => 'salah'])
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'NISN atau Password salah.');
    }

    public function test_password_legacy_plain_terupgrade_bcrypt(): void
    {
        Siswa::create(['nisn' => '444555666', 'nm_siswa' => 'Siswa Legacy', 'password' => 'siswa1234']);

        $this->postJson('/api/login', ['nisn' => '444555666', 'password' => 'siswa1234'])
            ->assertOk();

        $this->assertStringStartsWith('$2y$', Siswa::where('nisn', '444555666')->first()->password);
    }

    public function test_logout_hanya_mencabut_token_aktif(): void
    {
        $siswa = $this->siswaLogin();
        $login = $this->postJson('/api/login', ['nisn' => '111222333', 'password' => 'rahasia123']);
        $tokenAktif = $login->json('data.token');
        $tokenLain = $siswa->createToken('perangkat-lain')->plainTextToken;

        $this->withToken($tokenAktif)->postJson('/api/logout')
            ->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => explode('|', $tokenLain)[0]]);
    }

    public function test_logout_tanpa_token_ditolak_401(): void
    {
        $this->postJson('/api/logout')->assertStatus(401);
    }
}
```

- [ ] **Step 2: Jalankan, pastikan MERAH**

Run: `php artisan test --filter=AuthApiTest`
Expected: FAIL — `test_login_menghasilkan_token_nyata` (count token = 0), `test_password_legacy_plain_terupgrade_bcrypt` (password tetap plain), `test_logout_hanya_mencabut_token_aktif` (logout tidak menghapus), `test_logout_tanpa_token_ditolak_401` (logout masih publik → 200). `test_password_salah_ditolak_401` sudah PASS (regresi).

- [ ] **Step 3: Implementasi login & logout di `AuthApiController`**

- Ganti `$token = bin2hex(random_bytes(32));` pada kedua cabang (siswa & guru) menjadi:
  `$token = $user->createToken('binaro-app')->plainTextToken;`
  (variabel `$siswa` / `$guru` masing-masing cabang).
- Tambah helper privat (dipakai kedua cabang, sebelum `return $this->success(...)`):

```php
/**
 * Meng-upgrade password legacy (plain text/MD5) menjadi bcrypt
 * setelah verifikasi berhasil, agar verifikasi aman untuk berikutnya.
 */
private function upgradeLegacyPassword($user, string $plainPassword): void
{
    $stored = (string) ($user->password ?? '');
    if (! str_starts_with($stored, '$2y$') && ! str_starts_with($stored, '$2a$') && ! str_starts_with($stored, '$argon2')) {
        $user->update(['password' => Hash::make($plainPassword)]);
    }
}
```

Panggil `upgradeLegacyPassword($siswa, $password);` / `upgradeLegacyPassword($guru, $password);` tepat setelah `RateLimiter::clear(...)`.
- `logout` menjadi:

```php
public function logout(Request $request)
{
    $token = $request->user()->currentAccessToken();
    if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
        $token->delete();
    }

    return $this->success(null, 'Berhasil logout');
}
```

- `routes/api.php`: pindahkan `Route::post('/logout', ...)` ke dalam group `Route::middleware('auth:sanctum')` (baris pertama di dalam group, sebelum prefix).

- [ ] **Step 4: Jalankan test, pastikan HIJAU**

Run: `php artisan test --filter=AuthApiTest`
Expected: PASS semua (5 test).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/AuthApiController.php routes/api.php tests/Feature/Api/AuthApiTest.php
git commit -m "buat token login Sanctum nyata dan cabut saat logout"
```

---

### Task 3: SiswaApiController pakai token + tutup IDOR + E2E Bearer

**Files:**
- Create: `tests/Feature/Api/SiswaProfileTest.php`
- Modify: `app/Http/Controllers/Api/SiswaApiController.php`

**Interfaces:**
- Consumes: token dari Task 2, guard Sanctum dari Task 1.
- Produces: semua endpoint `/api/siswa/*` membaca `$request->user()` (model `Siswa`); parameter `?id=` di `/api/siswa/profile` dihapus. Dipakai test Task 4 & konversi test lama Task 6.

- [ ] **Step 1: Tulis test gagal `SiswaProfileTest`**

```php
// tests/Feature/Api/SiswaProfileTest.php
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

        $this->getJson('/api/siswa/absensi-hari-ini')
            ->assertOk()
            ->assertJsonPath('data.id_siswa', $siswa->id_siswa);
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
```

- [ ] **Step 2: Jalankan, pastikan MERAH**

Run: `php artisan test --filter=SiswaProfileTest`
Expected: FAIL 401 `User tidak terautentikasi` (controller masih baca session) — termasuk test E2E terakhir.

- [ ] **Step 3: Konversi `SiswaApiController` ke token**

- Hapus `use Illuminate\Support\Facades\Session;`.
- `profile()`: hapus seluruh cabang `$siswaId = $request->query('id');` — identitas selalu `$request->user()`:

```php
$siswa = $request->user()->load(['barcode', 'kelas', 'absens' => function ($query) {
    $query->orderByDesc('tanggal')->orderByDesc('waktu_absen');
}]);
return $this->success($siswa);
```

- `updateProfile()`, `todayAttendance()`, `selfScan()`: ganti `$userId = Session::get('user_id'); if (! $userId) {...}` menjadi `$userId = $request->user()->id_siswa;`. Sisanya (validasi, cek `$barcode->id_siswa != $userId`, response) tidak berubah.

- [ ] **Step 4: Jalankan test, pastikan HIJAU**

Run: `php artisan test --filter=SiswaProfileTest`
Expected: PASS semua (5 test, termasuk E2E login→Bearer→profil).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/SiswaApiController.php tests/Feature/Api/SiswaProfileTest.php
git commit -m "ambil identitas siswa dari token Sanctum dan tutup akses IDOR profil"
```

---

### Task 4: Middleware role API + konversi controller guru & admin

**Files:**
- Create: `app/Http/Middleware/EnsureApiRole.php`
- Create: `tests/Feature/Api/RoleAccessTest.php`, `tests/Feature/Api/GuruApiTest.php`, `tests/Feature/Api/AdminApiTest.php`
- Modify: `bootstrap/app.php:16-18` (alias), `routes/api.php`, `app/Http/Controllers/Api/GuruApiController.php`, `app/Http/Controllers/Api/AdminApiController.php`

**Interfaces:**
- Consumes: guard Sanctum (Task 1), `$request->user()` (Task 3 sebagai pola).
- Produces: alias middleware `'api.role'` → `EnsureApiRole` dengan signature `handle(Request $request, Closure $next, string $role)`; `/api/guru/*` butuh role `guru`, `/api/admin/*` butuh `admin`, `/api/siswa/*` butuh `siswa`; `GuruApiController` & `AdminApiController` extends `ApiController` dan memakai `$this->success()/error()`; `scanQr` mencatat `$request->user()->id_guru`. Test Task 5 memakai bentuk response ini.

- [ ] **Step 1: Tulis tiga test gagal**

`RoleAccessTest` — 5 test status (semua 401 `Unauthenticated.` saat ini → diharapkan 403/200):

```php
public function test_siswa_ditolak_route_guru(): void
{
    Sanctum::actingAs(Siswa::create(['nisn' => '111222333', 'nm_siswa' => 'A', 'password' => 'x']));
    $this->getJson('/api/guru/rpp')->assertStatus(403)
        ->assertJsonPath('success', false)->assertJsonPath('message', 'Forbidden');
}
// test_siswa_ditolak_route_admin  → GET /api/admin/absensi/rekap → 403 (pola sama)
// test_guru_ditolak_route_admin   → GET /api/admin/qr/regenerate/1 → 403 (pola sama)
// test_guru_bisa_akses_route_guru → Sanctum::actingAs($guru); GET /api/guru/rpp → 200
// test_admin_bisa_akses_route_admin → Sanctum::actingAs($admin); GET /api/admin/absensi/rekap → 200
```

Fixture: `Guru::create(['nip'=>'19900101','nama_guru'=>'Guru Uji'])`, `Admin::create(['nip'=>'ADM001','nama_admin'=>'Admin Uji','password'=>'x'])`. (Komentari pola `//` di atas jadi test lengkap saat implementasi — nama method persis seperti disebut.)

`GuruApiTest` — 3 test (semua 401 saat ini):

```php
public function test_rpp_list_hanya_milik_guru_token(): void
{
    // $guruA token, $guruB dibuat lebih dulu; Rpp::create untuk masing-masing
    // GET /api/guru/rpp → assert hanya berisi judul milik $guruA
}
public function test_scan_qr_mencatat_id_guru_dari_token(): void
{
    // $guruB dibuat PERTAMA, token = $guruA; siswa + Barcode::create(['id_siswa'=>..,'kode_barcode'=>'QR-UJI1234'])
    // POST /api/guru/absensi/scan-qr {'kode_barcode':'QR-UJI1234'} → 201
    // assertDatabaseHas('absen', ['id_guru' => $guruA->id_guru])  // RED: tercatat ke guru pertama (B)
}
public function test_create_rpp_mengikuti_guru_token(): void
{
    // Sanctum::actingAs($guruA); POST /api/guru/rpp {'judul_rpp'=>'RPP Uji'} → 201
    // assertDatabaseHas('rpp', ['id_guru' => $guruA->id_guru, 'judul_rpp' => 'RPP Uji'])
}
```

`AdminApiTest` — 2 test:

```php
public function test_regenerate_qr_berhasil(): void
{
    // siswa + barcode 'QR-LAMA1234'; Sanctum::actingAs($admin)
    // GET /api/admin/qr/regenerate/{id_siswa} → 200, success true, kode_barcode berubah
}
public function test_regenerate_qr_dibatasi_satu_kali_sehari(): void
{
    // barcode updated_at = now() → GET yang sama → 429
}
```

- [ ] **Step 2: Jalankan, pastikan MERAH**

Run: `php artisan test --filter=RoleAccessTest --filter=GuruApiTest --filter=AdminApiTest`
Expected: FAIL — 401 di hampir semua (guard), dan `test_scan_qr_mencatat_id_guru_dari_token` gagal di assertion `id_guru` bila guard sudah lolos.

- [ ] **Step 3: Implementasi middleware, routes, konversi dua controller**

`app/Http/Middleware/EnsureApiRole.php`:

```php
public function handle(Request $request, Closure $next, string $role): Response
{
    $user = $request->user();
    $peran = match (true) {
        $user instanceof Siswa => 'siswa',
        $user instanceof Guru => 'guru',
        $user instanceof Admin => 'admin',
        default => null,
    };

    if ($peran !== $role) {
        return response()->json(['success' => false, 'message' => 'Forbidden', 'errors' => []], 403);
    }

    return $next($request);
}
```

(Class imports: `Siswa`, `Guru`, `Admin`, `Request`, `Closure`, `Response`.)

`bootstrap/app.php` — tambah alias `'api.role' => EnsureApiRole::class,` bersampingan `'role'`.

`routes/api.php` — bentuk akhir:

```php
Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);

    Route::prefix('siswa')->middleware('api.role:siswa')->group(function () { ...tetap... });
    Route::prefix('guru')->middleware('api.role:guru')->group(function () { ...tetap... });
    Route::prefix('admin')->middleware('api.role:admin')->group(function () { ...tetap... });
});
```

`GuruApiController`: extends `ApiController` (ganti `Controller`), hapus `authGuruId()` dan semua guard `if (! $guruId) return response()->json(...)`, ganti dengan `$guruId = $request->user()->id_guru;`. Ganti semua `response()->json(['success' => true, ...])` → `$this->success($data, 'pesan')` dan error → `$this->error('pesan', kode)`. `scanQr`: `catatScan($data['kode_barcode'], $request->user()->id_guru, 'scan_qr')`.

`AdminApiController`: extends `ApiController`; `regenerateQr` → `$this->error('Siswa tidak ditemukan', 404)`, `$this->error('Batas maksimum regenerate barcode 1x per hari.', 429)`, `$this->success($barcode, 'Barcode berhasil diperbarui')`. (`rekapAbsensi` bentuk response baru → Task 5.)

- [ ] **Step 4: Jalankan test, pastikan HIJAU**

Run: `php artisan test --filter=RoleAccessTest --filter=GuruApiTest --filter=AdminApiTest`
Expected: PASS (10 test).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Middleware/EnsureApiRole.php bootstrap/app.php routes/api.php app/Http/Controllers/Api/GuruApiController.php app/Http/Controllers/Api/AdminApiController.php tests/Feature/Api/RoleAccessTest.php tests/Feature/Api/GuruApiTest.php tests/Feature/Api/AdminApiTest.php
git commit -m "tambah middleware role API dan konversi controller guru/admin ke token"
```

---

### Task 5: Standarisasi response rekap + paksa JSON di /api

**Files:**
- Create: `tests/Feature/Api/ApiContractTest.php`, `app/Http/Middleware/EnsureJsonRequests.php`
- Modify: `app/Http/Controllers/Api/AdminApiController.php` (metode `rekapAbsensi`), `bootstrap/app.php`

**Interfaces:**
- Consumes: `ApiController::success/error` (Task 4), alias grup `api` (framework).
- Produces: bentuk rekap `{success, message, data:{summary, absensi, meta}}`; semua request `/api/*` diperlakukan JSON tanpa perlu header `Accept` dari klien.

- [ ] **Step 1: Tulis test gagal `ApiContractTest`**

```php
public function test_rekap_mengikuti_kontrak_response(): void
{
    Sanctum::actingAs(Admin::create(['nip' => 'ADM001', 'nama_admin' => 'A', 'password' => 'x']));

    $this->getJson('/api/admin/absensi/rekap')
        ->assertOk()
        ->assertJsonStructure([
            'success', 'message',
            'data' => [
                'summary' => ['total_siswa', 'hadir', 'izin', 'sakit', 'alpa'],
                'absensi',
                'meta' => ['current_page', 'last_page', 'total'],
            ],
        ])
        ->assertJsonPath('success', true);
}

public function test_error_validasi_api_selalu_json(): void
{
    // $this->post TANPA header Accept — sekarang redirect 302, harus jadi 422 JSON
    $this->post('/api/login', [])->assertStatus(422)
        ->assertJsonValidationErrors(['nisn', 'password']);
}
```

- [ ] **Step 2: Jalankan, pastikan MERAH**

Run: `php artisan test --filter=ApiContractTest`
Expected: FAIL — struktur rekap lama (`summary` di root, tanpa `message`); test kedua gagal di 302.

- [ ] **Step 3: Implementasi**

`rekapAbsensi` — ganti bagian akhir menjadi:

```php
return $this->success([
    'summary' => $summary,
    'absensi' => $data->items(),
    'meta' => [
        'current_page' => $data->currentPage(),
        'last_page' => $data->lastPage(),
        'total' => $data->total(),
    ],
], 'Rekap absensi berhasil');
```

`EnsureJsonRequests`:

```php
public function handle(Request $request, Closure $next)
{
    $request->headers->set('Accept', 'application/json');

    return $next($request);
}
```

`bootstrap/app.php`, di dalam `withMiddleware`:

```php
$middleware->appendToGroup('api', EnsureJsonRequests::class);
```

- [ ] **Step 4: Jalankan test, pastikan HIJAU**

Run: `php artisan test --filter=ApiContractTest`
Expected: PASS (2 test).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Middleware/EnsureJsonRequests.php bootstrap/app.php app/Http/Controllers/Api/AdminApiController.php tests/Feature/Api/ApiContractTest.php
git commit -m "standarkan bentuk response rekap dan paksa JSON untuk semua route API"
```

---

### Task 6: Konversi test lama ke token + suite penuh hijau + Pint

**Files:**
- Modify: `tests/Feature/AbsensiScanFlowTest.php:127-158`, `tests/Feature/PrdComplianceTest.php` (pemanggilan API di baris ~170, ~191, ~207, ~239, ~368 + assertion rekap)
- TIDAK disentuh: `tests/Feature/RppValidationAndLimitsTest.php`

**Interfaces:**
- Consumes: semua hasil Task 1–5.
- Produkes: seluruh suite hijau; kode PHP lolos Pint. Ini gerbang sebelum pekerjaan Flutter.

- [ ] **Step 1: Konversi pemanggilan API di test lama**

Pola pengganti (untuk semua request ber-prefix `/api/` saja — web tetap `withSession`):

```php
// SEBELUM
$response = $this->actingAs($this->guru, 'sanctum')
    ->withSession(['user_id' => $this->guru->id_guru, 'user_type' => 'guru'])
    ->postJson('/api/guru/absensi/scan-qr', [...]);

// SESUDAH
Sanctum::actingAs($this->guru);
$response = $this->postJson('/api/guru/absensi/scan-qr', [...]);
```

- `AbsensiScanFlowTest`: 3 pemanggilan (baris ~127, ~145, ~156).
- `PrdComplianceTest`:
  - baris ~170, ~191, ~207: `actingAs($guru, 'sanctum')` → `Sanctum::actingAs($guru)`.
  - baris ~239: uji siswa akses `/api/guru/rpp` → `Sanctum::actingAs($siswa)` + ekspektasi **403** (bukan 401/403 longgar — sekarang contract-nya jelas).
  - baris ~368: `Sanctum::actingAs($admin)` + perbarui assertion rekap ke bentuk baru: `data.summary.total_siswa` (bukan `summary.total_siswa`), `assertJsonStructure(['success','message','data'=>['summary'=>[...],'absensi','meta'=>[...]]])`, dan `assertCount(4, $response->json('data.absensi'))`.

- [ ] **Step 2: Jalankan suite penuh**

Run: `php artisan test`
Expected: **semua hijau**. Pengecualian: bila `RppValidationAndLimitsTest` gagal, **jangan ubah file itu** — itu pekerjaan user yang belum selesai; catat hasilnya untuk dilaporkan.

- [ ] **Step 3: Rapikan dengan Pint (hanya path yang disentuh)**

Run:
```bash
vendor/bin/pint app/Http/Controllers/Api app/Http/Middleware app/Models routes/api.php config/auth.php bootstrap/app.php tests/Feature/Api tests/Feature/AbsensiScanFlowTest.php tests/Feature/PrdComplianceTest.php
```
Expected: selesai tanpa error (file bisa berubah format, bukan logika).

- [ ] **Step 4: Jalankan suite penuh lagi, pastikan masih hijau**

Run: `php artisan test`
Expected: sama dengan Step 2.

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/AbsensiScanFlowTest.php tests/Feature/PrdComplianceTest.php tests/Feature/Api app/Http/Controllers/Api app/Http/Middleware app/Models routes/api.php config/auth.php bootstrap/app.php
git commit -m "konversi test API ke token Sanctum dan rapikan kode dengan Pint"
```

---

### Task 7: Flutter — ApiClient, AuthService, token tersimpan

**Files:**
- Create: `binaro_flutter/lib/config/api_config.dart`, `binaro_flutter/lib/services/api_client.dart`, `binaro_flutter/lib/services/auth_service.dart`, `binaro_flutter/test/api_client_test.dart`
- Modify: `binaro_flutter/pubspec.yaml` (+`shared_preferences`), `binaro_flutter/lib/login_page.dart`, `binaro_flutter/lib/main.dart`

**Interfaces:**
- Consumes: kontrak response Task 2 (`data.token`, `data.user`) & Task 5 (bentuk error `{success:false, message}`).
- Produces (dipakai test & halaman berikutnya):
  - `ApiConfig.baseAddress` — `String.fromEnvironment('API_BASE_URL', defaultValue: 'http://192.168.18.187:8000')`
  - `class ApiException implements Exception` {`final String message; final int statusCode;`} + `class UnauthorizedException extends ApiException`
  - `ApiClient({Future<String?> Function()? tokenProvider, http.Client? httpClient, String? baseUrl})` dengan `Future<Map<String, dynamic>> post(String path, {Map<String, dynamic>? body})` dan `Future<Map<String, dynamic>> get(String path)`
  - `AuthService` dengan `static Future<void> init()`, `static String? get cachedToken`, `Future<Map<String, dynamic>> login({required String nisn, required String password, String namaLengkap = ''})` → mengembalikan `data.user` dan menyimpan token, `Future<void> logout()`

- [ ] **Step 1: Tambah dependensi**

Run (workdir `binaro_flutter`): `flutter pub add shared_preferences`
Expected: `pubspec.yaml` bertambah `shared_preferences`.

- [ ] **Step 2: Tulis test gagal `api_client_test.dart`**

```dart
// binaro_flutter/test/api_client_test.dart
import 'package:binaro_flutter/services/api_client.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

void main() {
  late ApiClient client;

  ApiClient denganRespon(int status, Map<String, dynamic> body, {String? token}) {
    return ApiClient(
      tokenProvider: () async => token,
      httpClient: MockClient((request) async {
        expect(request.headers['Accept'], 'application/json');
        if (token != null) {
          expect(request.headers['Authorization'], 'Bearer $token');
        }
        return http.Response(jsonEncode(body), status,
            headers: {'content-type': 'application/json'});
      }),
    );
  }

  test('respons sukses dikembalikan sebagai map', () async {
    client = denganRespon(200, {'success': true, 'data': {'token': '1|abc'}});
    final hasil = await client.post('/login', body: {'nisn': 'x'});
    expect(hasil['success'], true);
  });

  test('mengirim header Bearer bila token tersedia', () async {
    client = denganRespon(200, {'success': true}, token: '99|xyz');
    await client.get('/siswa/profile'); // expect di dalam MockClient memverifikasi header
  });

  test('401 mengangkat UnauthorizedException dengan pesan server', () async {
    client = denganRespon(401, {'success': false, 'message': 'NISN atau Password salah.'});
    expect(
      client.post('/login', body: {}),
      throwsA(isA<UnauthorizedException>()
          .having((e) => e.message, 'message', 'NISN atau Password salah.')),
    );
  });

  test('status error lain mengangkat ApiException', () async {
    client = denganRespon(429, {'message': 'Terlalu banyak percobaan.'});
    expect(client.post('/login', body: {}),
        throwsA(isA<ApiException>().having((e) => e.statusCode, 'statusCode', 429)));
  });
}
```

(Tambahkan `import 'dart:convert';` di bagian atas file.)

- [ ] **Step 3: Jalankan, pastikan MERAH**

Run (workdir `binaro_flutter`): `flutter test`
Expected: FAIL — class `api_client.dart` belum ada.

- [ ] **Step 4: Implementasi**

- `config/api_config.dart`: class `ApiConfig` dengan `static const String baseAddress = String.fromEnvironment('API_BASE_URL', defaultValue: 'http://192.168.18.187:8000');` dan `static String get endpoint => '$baseAddress/api';`. Docblock: cara override `flutter run --dart-define=API_BASE_URL=http://IP_LAIN:8000`.
- `services/api_client.dart`:
  - `ApiException` / `UnauthorizedException` (properti persis di Interfaces).
  - `ApiClient` menyimpan tokenProvider (default `() async => null`), httpClient (default `http.Client()`), baseUrl (default `ApiConfig.endpoint`).
  - `post`/`get` → `_send(method, path, body)`: header `Accept`/`Content-Type: application/json` + `Authorization: Bearer` bila token tidak null; `timeout(Duration(seconds: 10))`; timeout/koneksi gagal → `ApiException('Gagal terhubung ke server. Pastikan "php artisan serve" aktif!', 0)`; status 2xx → `jsonDecode` berupa Map; 401 → `UnauthorizedException(body['message'] ?? 'Sesi berakhir. Silakan login ulang.', 401)`; selain itu → `ApiException(body['message'] ?? 'Terjadi kesalahan pada server.', status)`.
- `services/auth_service.dart`:
  - `static Future<void> init()` → muat `SharedPreferences`, cache token di `_token`.
  - `static String? get cachedToken`.
  - `login(...)`: `await _client.post('/login', body: {'nisn', 'password', 'nama_lengkap'})`; bila `hasil['success'] != true` → `ApiException(hasil['message'] ?? 'Login gagal.', 400)`; simpan `hasil['data']['token']` ke prefs + cache; return `Map<String, dynamic>.from(hasil['data']['user'] ?? {})`.
  - `logout()`: coba `_client.post('/logout')` dalam try/finally → hapus token dari prefs + cache.
  - `ApiClient` dibuat di constructor dengan `tokenProvider: () async => cachedToken` (tanpa import bolak-balik — `api_client` tidak mengimpor `auth_service`).
- `login_page.dart`: hapus `import 'dart:convert';` dan `import 'package:http/http.dart' as http;`, hapus field `_apiUrl` dan blok `http.post`. Tambah `final AuthService _auth = AuthService();`. Di `_handleLogin`:

```dart
try {
  final userData = await _auth.login(
    nisn: nisn, password: password, namaLengkap: nama);
  if (!mounted) return;
  _showSnackbar('Login Berhasil! Selamat datang, ${userData['nm_siswa'] ?? 'Siswa'}');
  _showSuccessDialog(Map<String, dynamic>.from(userData));
} on ApiException catch (e) {
  _showSnackbar(e.message, isError: true);
} catch (e) {
  _showSnackbar('Gagal terhubung ke server. Pastikan "php artisan serve" aktif!', isError: true);
} finally { ... }
```

UI (warna, layout, dialog) tidak berubah sama sekali.
- `main.dart`: jadi `void main() async { WidgetsFlutterBinding.ensureInitialized(); await AuthService.init(); runApp(const BinaroApp()); }` — nama class mengikuti yang sudah ada.

- [ ] **Step 5: Verifikasi**

Run (workdir `binaro_flutter`):
```bash
flutter test
flutter analyze
dart format lib test
```
Expected: test HIJAU, analyze tanpa issue, format bersih.

- [ ] **Step 6: Commit**

```bash
git add binaro_flutter/pubspec.yaml binaro_flutter/pubspec.lock binaro_flutter/lib binaro_flutter/test
git commit -m "rapihkan akses API Flutter dengan service layer dan simpan token"
```

---

### Task 8: Dokumentasi kontrak API + E2E nyata

**Files:**
- Create: `docs/api.md`

**Interfaces:**
- Consumes: seluruh hasil Task 1–7.
- Produkes: dokumentasi yang bisa dipakai programmer lain tanpa membaca controller.

- [ ] **Step 1: Tulis `docs/api.md`**

Isi minimal:
- Base URL + cara ganti (`--dart-define=API_BASE_URL=...`, kebutuhan `--host=0.0.0.0`, satu jaringan WiFi, `adb reverse tcp:8000 tcp:8000` untuk emulator).
- Autentikasi: cara login, header `Authorization: Bearer <token>`, logout mencabut token aktif.
- Tabel endpoint: `POST /api/login` (publik), `POST /api/logout`, semua `/api/siswa/*` (role siswa), `/api/guru/*` (role guru), `/api/admin/*` (role admin) — kolom: method, role, request body/params, respons sukses.
- Bentuk response: sukses `{success, message, data}`, error `{success, message, errors}`, 422 validasi `{message, errors}`, 401 guard `{message}`, 403 `{success:false, message:'Forbidden'}`.
- Contoh JSON login sukses & gagal (ambil dari hasil E2E Step 2).

- [ ] **Step 2: E2E dengan server berjalan**

```bash
# terminal 1 (background)
php artisan serve --host=127.0.0.1 --port=8000
```
```powershell
# terminal 2 — login
$r = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/login' -Method POST -Headers @{'Accept'='application/json'} -ContentType 'application/json' -Body '{"nisn":"0012345678","password":"siswa123","nama_lengkap":""}'
$token = ($r.Content | ConvertFrom-Json).data.token
# profil dengan token (TANPA session)
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/siswa/profile' -Headers @{'Accept'='application/json'; "Authorization"="Bearer $token"} | Select-Object -Expand Content
# logout → token mati
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/logout' -Method POST -Headers @{'Accept'='application/json'; "Authorization"="Bearer $token"}
# pakai token lama lagi → harus 401
```

Expected: profil 200 dengan data `Budi Santoso` tanpa field password; logout 200; pemakaian ulang token → 401. Selamatkan output sukses sebagai contoh di `docs/api.md`.

- [ ] **Step 3: Commit**

```bash
git add docs/api.md docs/superpowers
git commit -m "tambah dokumentasi kontrak API Binaro"
```

---

## Definition of Done (verifikasi akhir)

1. `php artisan test` — semua hijau (kecuali `RppValidationAndLimitsTest` bila sudah gagal sebelum perubahan ini — laporkan).
2. `vendor/bin/pint <path yang disentuh>` — bersih.
3. `flutter test` + `flutter analyze` + `dart format --output=none --set-exit-if-changed lib test` di `binaro_flutter` — bersih.
4. E2E Task 8 tercatat: login → profil → logout → token mati.
5. `git log` menunjukkan 8 commit per task, tanpa file user yang ikut ter-commit.
