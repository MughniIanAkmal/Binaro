# Design: Perbaikan API Binaro (Laravel ↔ Flutter)

Tanggal: 2026-11-07
Status: Menunggu persetujuan
Pendekatan terpilih: **A — Laravel Sanctum (token tersimpan di DB)**

---

## 1. Intent & Mengapa

API Binaro saat ini **tidak bisa dipakai klien nyata seperti Flutter**, meski endpoint-nya ada dan login merespons 200. Audit menemukan akar masalah berlapis:

1. `laravel/sanctum` tidak terpasang → guard `auth:sanctum` tidak pernah mengenali Bearer token (terbukti: selalu 401).
2. Token login dibuat acak (`bin2hex(random_bytes(32))`) tapi tidak pernah disimpan → token palsu.
3. Controller API membaca identitas dari `Session::` (cookie web) → klien stateless tidak punya session, jadi tetap 401 walau middleware lolos.
4. Test API "hijau" karena memakai `withSession()` — menutupi kegagalan di atas.
5. Keamanan: hash password bocor di response, IDOR `?id=`, tidak ada cek role di route admin.
6. Sisi Flutter: IP hardcoded salah, token tidak disimpan/dikirim.

Hasil yang diinginkan: **API yang benar-benar bisa dipakai Flutter (dan klien lain) dengan autentikasi token yang sah, aman, teruji, serta kode yang rapi dan mudah dipel programmer lain.**

---

## 2. Goals

- Autentikasi API berbasis Sanctum token: login menghasilkan token nyata, logout mencabutnya, route terproteksi menerima `Authorization: Bearer`.
- Identitas user diambil dari token (`$request->user()`), bukan session.
- Cek role per prefix route (`siswa`/`guru`/`admin`) → 403 JSON.
- Tidak ada hash password yang bocor; tidak ada IDOR di profil siswa.
- Bentuk response seragam: `{success, message, data}` (+ `errors` untuk 422).
- Test API meniru klien nyata (token tanpa session); seluruh suite tetap hijau.
- Sisi Flutter: base URL terpusat, token disimpan & dikirim, service layer rapi.
- Kode sesuai standar: Laravel Pint (PSR-12), `dart format`, komentar & struktur file konsisten dengan gaya project.

## 3. Non-Goals (di luar lingkup)

- Membangun halaman fitur Flutter (profile, absensi, RPP, dst.) — pekerjaan terpisah.
- Mengubah alur login/session **web** yang sudah berfungsi.
- Mengganti AbsensiService, skema database lama, atau logika bisnis absensi.
- Migrasi ke JWT.

## 4. Anti-Goals

- Jangan memalsukan test dengan `withSession()` lagi untuk route `/api/*`.
- Jangan mengubah bentuk response login yang sudah dibaca Flutter (`data.token`, `data.user.*`, `success`, `message`).
- Jangan menyentuh file test yang sedang dimodifikasi user (`tests/Feature/RppValidationAndLimitsTest.php`).
- Jangan menambah dependensi berat ke Flutter (dio/riverpod) — cukup `http` + `shared_preferences`.

## 5. Constraints

- Laravel 12, PHP ≥ 8.2; `laravel/pint` sudah ada (formatter standar).
- Database punya data password campuran: plain text, MD5, bcrypt → verifikasi multi-format **tetap dipertahankan**.
- Flutter: `http ^1.2.0` sudah ada; `flutter_lints ^3.0.0`.
- HP dan laptop harus satu jaringan (soal koneksi, bukan kode).

---

## 6. Approaches

| # | Pendekatan | Keputusan |
|---|-----------|-----------|
| **A** | **Sanctum personal access tokens** | **DIPILIH.** Standar Laravel untuk mobile, token bisa dicabut, role bisa dicek, teruji dengan Bearer sungguhan. |
| B | JWT stateless (tymon/jwt-auth) | Ditolak: token tidak bisa dicabut saat logout, tambah dependensi pihak ketiga. |
| C | Pertahankan session, Flutter kirim cookie | Ditolak: rapuh untuk mobile, stateful, tidak lazim — justru akar masalah yang diperbaiki. |

---

## 7. Rencana Perbaikan (yang memutuskan how)

### 7.1 Fondasi autentikasi (Laravel)

1. `composer require laravel/sanctum` + publish migration `personal_access_tokens`.
2. `config/auth.php`: **hapus** entri guard palsu `'sanctum' => ['driver' => 'session', ...]` agar guard Sanctum yang dipakai framework (verifikasi lewat test: token sah → 200, tanpa token → 401).
3. Model `Siswa`, `Guru`, `Admin`: tambah trait `HasApiTokens` + `protected $hidden = ['password']`.
4. `AuthApiController::login`:
   - Verifikasi password tetap multi-format (plain/MD5/bcrypt).
   - **Upgrade hash**: jika password tersimpan plain/MD5, setelah login berhasil tulis ulang sebagai bcrypt (sekali jalan, aman untuk data legacy).
   - Token: `$user->createToken('binaro-app')->plainTextToken` → struktur response login **tidak berubah**.
5. `AuthApiController::logout`: pindah ke dalam group `auth:sanctum`; cabut token aktif saja (`currentAccessToken()->delete()`), supaya perangkat lain tetap login.

### 7.2 Controller & otorisasi

6. `SiswaApiController`, `GuruApiController`, `AdminApiController`: semua extends `ApiController`.
7. Ganti semua `Session::get('user_id'|'guru_id')` → `$request->user()` (model hasil token). `GuruApiController::authGuruId()` cukup return `$request->user()->id_guru`.
8. **Middleware baru** `EnsureApiRole` (`app/Http/Middleware/EnsureApiRole.php`):
   - Dipakai: `Route::middleware(['auth:sanctum', 'api.role:siswa'])` per prefix di `routes/api.php`.
   - Role ditentukan dari kelas model token (`Siswa`→siswa, `Guru`→guru, `Admin`→admin) — satu sumber kebenaran, tanpa klaim role terpisah.
   - Bukan role → `403 {success:false, message:'Forbidden'}` (bukan redirect HTML).
   - Middleware web `EnsureRole` tidak diubah (masih untuk web).
9. `SiswaApiController::profile`: **hapus parameter `?id=`** → profil selalu milik token sendiri (menutup IDOR). Cross-student lookup bila dibutuhkan nanti = endpoint admin dengan role check.
10. `GuruApiController::scanQr`: `catatScan($kode, $request->user()->id_guru, ...)` — absensi selalu tercatat dengan guru yang benar.

### 7.3 Kontrak response

11. Semua response API pakai `ApiController::success/error`: `{success, message, data}`.
    - `AdminApiController::rekapAbsensi` → `data: {summary: {...}, absensi: [...], meta: {...}}` (pagination jadi `meta`).
    - Error validasi 422 tetap format Laravel `{message, errors}` — didokumentasikan.
12. Middleware baru `EnsureJsonRequests` → `appendToGroup('api', ...)` di `bootstrap/app.php`: paksa `Accept: application/json` untuk `/api/*` agar error tidak pernah berubah jadi redirect HTML.
13. Rate limit login: logika `RateLimiter` yang sudah ada dipertahankan (sudah benar).

### 7.4 Sisi Flutter

14. Dependensi baru: `shared_preferences` (penyimpanan token).
15. Struktur file baru:
    ```
    binaro_flutter/lib/
    ├── config/api_config.dart      # base URL (String.fromEnvironment, default IP saat ini)
    ├── services/api_client.dart    # http client: header JSON + Bearer, timeout, ApiException
    ├── services/auth_service.dart  # login/logout, simpan-hapus token
    ├── login_page.dart             # pakai service, tanpa logika HTTP langsung
    └── main.dart                   # inisialisasi, cek token awal
    ```
16. `login_page.dart`: panggil `AuthService.login()`; pesan error dari server diteruskan; token disimpan; UI tidak diubah (tetap dialog sukses).
17. `api_client.dart` menangani 401 → sinyalkan `UnauthorizedException` (pemicu logout navigasi di layar berikutnya; navigasi penuh menunggu halaman home).

### 7.5 Dokumentasi

18. `docs/api.md`: kontrak endpoint (method, auth, request, response sukses/gagal, contoh) — agar programmer lain tidak perlu membaca controller untuk memahami API.

### 7.6 Standar kode & file

19. PHP: `vendor/bin/pint` di akhir (PSR-12), docblock pada semua method controller/middleware baru, komentar Indonesia mengikuti gaya project.
20. Dart: `dart format lib/` + `flutter analyze` bersih.
21. Test baru di `tests/Feature/Api/` (folder terpisah dari test web).

---

## 8. Strategi Test (TDD — test gagal dulu, baru implementasi)

| Test | Membuktikan |
|------|------------|
| `Api/AuthApiTest` | login sukses → token nyata + user tanpa field password; password salah → 401; field kosong → 422 JSON |
| `Api/ProtectedRouteTest` | Bearer valid → 200; tanpa token → 401; token siswa → route admin → 403 |
| `Api/SiswaProfileTest` | profil = milik token sendiri; `?id=123` tidak mengambil data siswa lain; response tanpa password |
| `Api/GuruRppScanTest` | RPP & scan-qr memakai id_guru dari token; absensi tercatat dengan guru benar |
| **E2E nyata** | login via HTTP → pakai token hasilnya → akses `/api/siswa/profile` (tanpa session sama sekali) |
| Regression | seluruh suite lama tetap hijau; test API lama yang pakai `withSession` diubah ke token |

Flutter: unit test `api_client` dengan `MockClient` (`package:http/testing.dart`, tanpa dependensi baru) untuk parsing sukses/gagal + `UnauthorizedException`.

## 9. Urutan Eksekusi

1. Tulis test gagal (Auth + ProtectedRoute) → jalankan → merah.
2. Fondasi Sanctum (composer, migration, config, trait, $hidden).
3. Login/logout token nyata → test hijau.
4. Middleware role + konversi controller ke `$request->user()` → test hijau.
5. Standarisasi response + `EnsureJsonRequests` + hapus IDOR.
6. Ubah test lama ke token; `php artisan test` penuh hijau.
7. Flutter: config/client/service + test → login pakai service.
8. `vendor/bin/pint`, `dart format`, `flutter analyze`.
9. `docs/api.md` + verifikasi E2E dengan server berjalan.

## 10. Risiko & Mitigasi

| Risiko | Mitigasi |
|--------|----------|
| Guard `sanctum` di config menghalangi Sanctum bekerja | Hapus entri; buktikan dengan test token nyata |
| Data password legacy terkunci setelah perubahan | Verifikasi multi-format dipertahankan + upgrade bcrypt on-login |
| Test lama bergantung session | Konversi ke token; web test tidak disentuh |
| File test user sedang dimodifikasi | Jangan sentuh `RppValidationAndLimitsTest.php`; bila gagal karena perubahan user, laporkan |
| IP berubah lagi di jaringan lain | Base URL bisa lewat `--dart-define=API_BASE_URL=...` tanpa edit kode |

## 11. Kriteria Selesai

- `php artisan test` hijau penuh, `vendor/bin/pint` bersih.
- `flutter analyze` + `dart format` bersih, unit test Flutter hijau.
- E2E: login → token → profil terproteksi berhasil dari HTTP nyata.
- `docs/api.md` lengkap.
