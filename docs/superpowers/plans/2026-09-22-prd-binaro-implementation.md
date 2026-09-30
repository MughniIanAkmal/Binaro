# Plan Implementasi PRD Binaro

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans atau superpowers:subagent-driven-development. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyesuaikan skema database, API endpoint, alur QR code absensi, autentikasi plain-text multi-role (Siswa, Guru, Admin), proteksi RPP, serta tiga tampilan utama (Mobile Siswa, Web Guru, Dashboard Admin Rekap) sesuai PRD `project_smster_331`.

**Architecture:** Laravel Web & API Controllers dengan model Eloquent berbasis skema `project_smster_331`, middleware autentikasi dan otorisasi role, observer QR otomatis, serta Blade view bergaya Tailwind CSS / Bootstrap UI custom sesuai spesifikasi visual.

**Tech Stack:** PHP 8.2+, Laravel 11, MySQL, Tailwind CSS, JavaScript (Kamera/QR scan API).

---

### Task 1: Harmonisasi Skema Database & Migrasi 

**Files:**
- Modify: `D:\Smster 3\binaro\database\migrations\2026_01_01_000001_create_academic_system_tables.php`
- Modify: `D:\Smster 3\binaro\app\Models\Guru.php`
- Modify: `D:\Smster 3\binaro\app\Models\Siswa.php`
- Modify: `D:\Smster 3\binaro\app\Models\Absen.php`
- Modify: `D:\Smster 3\binaro\app\Models\Barcode.php`
- Modify: `D:\Smster 3\binaro\app\Models\Rpp.php`

- [ ] **Step 1: Pastikan skema migrasi 100% selaras dengan DDL PRD**
  Secara eksplisit pastikan kolom `guru.password`, `siswa.foto_profil`, `barcode.qr_image_path`, `barcode.is_active`, `absen.keterangan`, `absen.berkas_surat`, `absen.metode`, `absen.waktu_absen`, dan `absen.tanggal` lengkap beserta constraint `uq_siswa_tanggal` (UNIQUE KEY).

- [ ] **Step 2: Update Model Eloquent**
  Tambahkan `$fillable` dan relasi pada Model:
  - `Guru`: `$fillable = ['nip', 'nama_guru', 'no_hp', 'password']`
  - `Siswa`: `$fillable = ['id_mapel', 'id_rooms', 'nisn', 'nm_siswa', 'no_hp', 'password', 'foto_profil']`
  - `Absen`: `$fillable = ['id_guru', 'id_siswa', 'id_barcode', 'status', 'keterangan', 'berkas_surat', 'metode', 'waktu_absen', 'tanggal']`
  - `Barcode`: `$fillable = ['id_siswa', 'kode_barcode', 'qr_image_path', 'is_active']`

- [ ] **Step 3: Buat Observer Siswa untuk Generasi Barcode Otomatis**
  Buat/perbarui `App\Observers\SiswaObserver` agar pada event `created`, membuat record `Barcode` dengan format `BIN-{nisn}-{RANDOM6}`.

---

### Task 2: Autentikasi Plain-Text & Proteksi Middleware Multi-Role

**Files:**
- Modify: `D:\Smster 3\binaro\app\Http\Controllers\AuthController.php`
- Create: `D:\Smster 3\binaro\app\Http\Middleware\EnsureRole.php`
- Modify: `D:\Smster 3\binaro\routes\web.php`
- Modify: `D:\Smster 3\binaro\routes\api.php`

- [ ] **Step 1: Sesuaikan AuthController untuk Plain Text Password**
  Ubah pencocokan password di `AuthController::login` menggunakan komparasi langsung `$user->password === $request->password` untuk Admin, Guru, dan Siswa.
  *(Peringatan ditambahkan di log/komentar: Plain text untuk prototipe lokal).*

- [ ] **Step 2: Implementasi Middleware Otorisasi Role**
  Buat `EnsureRole` yang memeriksa `session('user_type')` atau token role (admin, guru, siswa). Tolak akses Siswa ke alur `/guru/rpp/*` atau API RPP.

- [ ] **Step 3: Daftarkan Rute Web & API**
  Susun endpoint API sesuai PRD:
  - `/api/siswa/*` (profile, absensi hari ini, scan)
  - `/api/guru/*` (profile, rpp, siswa, scan-qr, absen-massal)
  - `/api/admin/*` (absensi/rekap, qr/regenerate/{siswa_id})

---

### Task 3: Controller API & Alur Scan QR Absensi Anti-Duplikat

**Files:**
- Create/Modify: `D:\Smster 3\binaro\app\Http\Controllers\Api\SiswaApiController.php`
- Create/Modify: `D:\Smster 3\binaro\app\Http\Controllers\Api\GuruApiController.php`
- Create/Modify: `D:\Smster 3\binaro\app\Http\Controllers\Api\AdminApiController.php`
- Modify: `D:\Smster 3\binaro\app\Http\Controllers\Guru\AbsenScanController.php`

- [ ] **Step 1: Implementasi API Scan QR Guru (`POST /api/guru/absensi/scan-qr`)**
  Validasi `kode_barcode`, cek status aktif barcode, ambil `id_siswa`.
  Periksa keberadaan data di tabel `absen` berdasarkan `id_siswa` dan `tanggal` hari ini. Jika ada, kembalikan HTTP 409 (Duplikat). Jika belum ada, simpan data absen status `Hadir`, metode `scan_qr`, waktu absen saat ini, lalu kembalikan HTTP 201.

- [ ] **Step 2: Implementasi Rekap Absensi Admin (`GET /api/admin/absensi/rekap`)**
  Terima filter `fecha`/`tanggal`, `id_rooms`, dan `status`.
  Hitung ringkasan (`total_siswa`, `hadir`, `izin`, `sakit`, `alpa`) hari ini, dan kembalikan data terpaginasi.

- [ ] **Step 3: Implementasi Regenerate QR Admin (`GET /api/admin/qr/regenerate/{siswa_id}`)**
  Pastikan batas maksimum 1x/hari per siswa dan buat kode baru.

---

### Task 4: Antarmuka UI/UX (Siswa Mobile, Guru Web, Admin Rekap)

**Files:**
- Create/Modify: `D:\Smster 3\binaro\resources\views\siswa\dashboard.blade.php`
- Create/Modify: `D:\Smster 3\binaro\resources\views\guru\dashboard.blade.php`
- Create/Modify: `D:\Smster 3\binaro\resources\views\admin\absensi\rekap.blade.php`
- Create/Modify: `D:\Smster 3\binaro\resources\views\layouts\guru.blade.php`

- [ ] **Step 1: Dashboard Mobile Siswa**
  Header `#1E6091`, info siswa ("Halo, Budi Santoso!"), 3 shortcut, section Tugas & PR Mendatang, grid Mapel 2 kolom, dan bottom navigation bar.

- [ ] **Step 2: Dashboard Web Guru**
  Sidebar fixed 240px (`#165B96`), menu aktif `#19639D`, banner profil, 3 KPI cards, layout split 65%:35% (Materi & Jadwal), dan grid PR berjalan di bawah.

- [ ] **Step 3: Dashboard Admin Rekap Absensi**
  KPI Cards ringkasan, baris filter (Tanggal, Kelas, Status), tabel lengkap pencatatan (Siswa, NISN, Kelas, Status, Waktu, Guru, Metode), serta tombol ekspor.

---

### Task 5: Validasi, Pengujian, dan Verifikasi Checklist PRD

**Files:**
- Create: `D:\Smster 3\binaro\tests\Feature\PrdComplianceTest.php`

- [ ] **Step 1: Buat Uji Otomatis Pengujian PRD**
  Tulis pengujian Feature Laravel untuk memastikan:
  - Barcode otomatis terbuat saat Siswa dibuat.
  - Password tersimpan dan terverifikasi secara plain-text.
  - Scan QR menghasilkan HTTP 201 pertama kali dan HTTP 409 jika discan ulang di hari yang sama.
  - Siswa dilarang mengakses modul RPP.
  - Endpoint Rekap Admin mengembalikan summary dan data terpaginasi.

- [ ] **Step 2: Jalankan Migration & Suite Pengujian**
  Jalankan `php artisan migrate:fresh` dan `php artisan test`.

---
