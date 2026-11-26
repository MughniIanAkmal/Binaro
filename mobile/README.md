# Binaro Flutter Mobile App

Aplikasi mobile pembelajaran SD Negeri Kalitapen 1 yang terhubung ke Backend Laravel API (`Binaro`).

## Struktur Folder

- `lib/main.dart` : Titik awal aplikasi
- `lib/screens/` : Seluruh halaman (login, dashboard, mapel, materi, kuis, ujian, profil)
- `lib/utils/api_client.dart` : Klien HTTP terpusat (header + token Sanctum + header ngrok)
- `lib/utils/session.dart` : Penyimpanan sesi login & token di `SharedPreferences`
- `lib/utils/constants.dart` : Konfigurasi `baseUrl`, timeout, warna
- `pubspec.yaml` : Konfigurasi dependensi (`http`, `shared_preferences`, dll)

## Konfigurasi Endpoint API

Base URL ada di `lib/utils/constants.dart` (`AppConstants.baseUrl`). Default-nya
adalah tunnel **ngrok**, jadi `flutter run` langsung terhubung dari jaringan mana
pun selama tunnel hidup. Bisa dioverride tanpa mengubah kode:

```bash
# Default: ngrok (dua proses backend harus hidup, lihat di bawah)
flutter run

# Pakai IP LAN (lebih cepat, HP harus di Wi-Fi yang sama)
flutter run --dart-define=BINARO_API_BASE_URL=http://10.10.9.118:8000/api

# Android emulator
flutter run --dart-define=BINARO_API_BASE_URL=http://10.10.9.118:8000/api
```

## Cara Menjalankan

1. Siapkan backend Laravel (terminal 1):

   ```bash
   cd laravel
   php artisan migrate --seed     # sekali saja (atau setelah reset DB)
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. Nyalakan tunnel ngrok (terminal 2):

   ```bash
   ngrok http 8000 --url=https://pluck-secular-kebab.ngrok-free.dev
   ```

   Buka **http://127.0.0.1:4040** untuk melihat URL tunnel yang aktif
   (dipakai kalau domain statisnya berubah/diganti).

3. Jalankan aplikasi mobile (terminal 3):

   ```bash
   cd mobile
   flutter pub get
   flutter run -d chrome     # atau -d windows / -d edge
   ```

   Tanpa `-d`, Flutter menampilkan menu pilihan device. Untuk melewati menu itu
   secara permanen, gunakan salah satu cara berikut:

   - **VS Code**: buka folder `mobile`, tekan `F5`, lalu pilih salah satu
     konfigurasi yang sudah tersedia di `.vscode/launch.json`
     (`Binaro - Chrome (ngrok)`, `Binaro - Windows (ngrok)`, dst).
   - **Terminal**: tentukan device secara eksplisit dengan `-d <id>`.
   - **Tanpa browser sama sekali** (hanya server lokal, buka URL sendiri):

     ```bash
     flutter run -d web-server --web-port=8080
     ```

   Daftar device yang tersedia: `flutter devices`.

Akun demo siswa: NISN `0012345678`, kata sandi `siswa123`.

> Kalau aplikasi menampilkan error koneksi, cek dulu `https://<domain-ngrok>/up`
> di browser. Balasan `ERR_NGROK_3200` berarti tunnel mati, bukan kode aplikasi.

## Alur Autentikasi

1. `POST /api/login` mengembalikan `data.token` (Sanctum) + `data.user`.
2. Token dan data user disimpan oleh `AppSession.save(...)`.
3. `ApiClient` otomatis menambahkan `Authorization: Bearer <token>` pada setiap
   request, sehingga endpoint ber-middleware `auth:sanctum` berfungsi:
   `GET /api/siswa/absensi-hari-ini`, `POST /api/siswa/absensi-scan`,
   `POST /api/siswa/profile`.
4. Logout memanggil `POST /api/logout` untuk mencabut token, lalu menghapus sesi lokal.
