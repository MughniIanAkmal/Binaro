# Sistem Informasi Sekolah

Aplikasi Laravel untuk pengelolaan pembelajaran, jadwal, absensi, ujian, nilai, dan akun admin, guru, serta siswa.

## Peta Proyek

```text
app/
  Http/
    Controllers/       Controller bersama dan controller per peran/API
    Middleware/        Pemeriksaan login dan hak akses
  Models/              Model Eloquent
  Observers/            Observer model
  Providers/            Service provider aplikasi
database/
  migrations/           Struktur tabel dan perubahan skema
  seeders/              Data awal/demo
  factories/            Factory untuk test
  imports/              Dump SQL referensi/impor lama
resources/
  views/                Halaman Blade, dikelompokkan berdasarkan fitur/peran
  css/                  Style aplikasi
  js/                   JavaScript aplikasi
routes/
  web.php               Route web
  api.php               Route API
tests/
  Feature/              Test alur HTTP dan fitur
  Unit/                 Test unit
docs/
  superpowers/          Dokumen rencana
  reference/            Catatan referensi proyek
  archive/              Fragmen/output lama, bukan kode runtime
scripts/
  debug/                Skrip pemeriksaan manual, bukan bagian dari aplikasi
```

## Menjalankan Lokal

1. Pasang dependensi PHP dan JavaScript:

   ```sh
   composer install
   npm install
   ```

2. Salin `.env.example` ke `.env`, lalu atur koneksi database.
3. Buat application key dan jalankan migrasi:

   ```sh
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Jalankan aplikasi dan Vite di terminal terpisah:

   ```sh
   php artisan serve
   npm run dev
   ```

## Pemeriksaan

Jalankan test dengan:

```sh
php artisan test
```

Skrip di `scripts/debug` digunakan manual untuk inspeksi lokal. Jangan jalankan pada database produksi tanpa meninjau isi skrip dan konfigurasi `.env` terlebih dahulu.
