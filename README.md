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
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Mobile (Flutter)
```bash
cd mobile
flutter pub get
flutter run
```

---

## ⚙️ Konfigurasi Koneksi API
Untuk menghubungkan aplikasi Flutter ke backend Laravel:
* Atur IP server pada file `mobile/lib/utils/constants.dart`:
```dart
static const String baseUrl = 'http://<IP_KOMPUTER_ANDA>:8000/api';
```
