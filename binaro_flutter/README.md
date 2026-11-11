# Binaro Flutter Mobile App

Aplikasi mobile pembelajaran SD Negeri Kalitapen 1 yang terhubung ke Backend Laravel API (`Binaro`).

## Struktur Folder:
- `lib/main.dart` : Titik awal aplikasi
- `lib/login_page.dart` : Tampilan login dan logika HTTP request ke API Laravel
- `pubspec.yaml` : Konfigurasi dependensi (`http`)

## Konfigurasi Endpoint API:
Buka `lib/login_page.dart` pada baris `_apiUrl`:
- **Jika di HP Fisik**: `http://10.105.118.25:8000/api/login` (pastikan terhubung ke WiFi yang sama dengan laptop)
- **Jika di Android Emulator**: `http://10.0.2.2:8000/api/login`
- **Jika di Browser Chrome**: `http://127.0.0.1:8000/api/login`

## Cara Menjalankan:
1. Pastikan server Laravel aktif di terminal:
   ```bash
   cd E:\Project\Binaro
   php artisan serve --host=0.0.0.0 --port=8000
   ```
2. Buka folder `E:\Project\binaro_flutter` di editor (VS Code / Android Studio).
3. Jalankan:
   ```bash
   flutter pub get
   flutter run
   ```

