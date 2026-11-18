# Binaro - Sistem Akademik & Belajar Siswa

Monorepo resmi untuk proyek **Binaro**, yang terdiri dari web backend Laravel dan aplikasi mobile Flutter.

---

## 📁 Struktur Monorepo

```text
Binaro/
├── laravel/          # Laravel 11 Backend (Web Dashboard Guru/Admin & REST API)
└── mobile/           # Flutter Mobile App (Aplikasi Belajar & Ujian Siswa)
```

---

## 🚀 Panduan Memulai

### 1. Backend (Laravel)
```bash
cd laravel
composer install
cp .env.example .env    # jika belum ada .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=8000
```

Database default proyek ini adalah **SQLite** (`laravel/database/database.sqlite`),
jadi tidak perlu menjalankan service MySQL. Bila ingin memakai MySQL, salin
kembali konfigurasi dari `.env.backup-mysql-*` ke `.env`.

Akun demo siswa: NISN `0012345678`, kata sandi `siswa123`.

### 2. Mobile (Flutter)
```bash
cd mobile
flutter pub get
flutter run
```

---

## ⚙️ Konfigurasi Koneksi API

Base URL ada di `mobile/lib/utils/constants.dart` dan default-nya menunjuk ke
tunnel ngrok, sehingga `flutter run` langsung terhubung. Bisa dioverride tanpa
mengubah kode:

```bash
flutter run                                                                    # ngrok (default)
flutter run --dart-define=BINARO_API_BASE_URL=http://192.168.18.187:8000/api    # IP LAN
flutter run --dart-define=BINARO_API_BASE_URL=http://10.0.2.2:8000/api         # emulator
```

Backend + tunnel harus hidup bersamaan:

```bash
# terminal 1
cd laravel && php artisan serve --host=0.0.0.0 --port=8000

# terminal 2
ngrok http 8000 --url=https://pluck-secular-kebab.ngrok-free.dev
```

URL tunnel aktif dapat dilihat di **http://127.0.0.1:4040**. Satu domain ngrok
melayani web Laravel (guru/admin) **dan** API mobile, karena keduanya berasal
dari aplikasi Laravel yang sama.
