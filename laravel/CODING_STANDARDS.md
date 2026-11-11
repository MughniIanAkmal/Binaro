# Standar Coding Binaro

Dokumen ini menetapkan kebiasaan kerja agar kode Binaro konsisten, mudah ditinjau, dan aman dikembangkan. Terapkan aturan berikut pada perubahan baru; perubahan lama dapat dirapikan saat memang disentuh dan tetap dalam lingkup tugas.

## Prinsip umum

- Pilih solusi yang sederhana dan mudah dipahami; hindari duplikasi dan abstraksi yang belum diperlukan.
- Ikuti pola yang sudah digunakan di area terkait sebelum memperkenalkan pola baru.
- Gunakan nama yang menjelaskan maksud. Nama class menggunakan `PascalCase`, method dan variabel menggunakan `camelCase`, serta kolom dan tabel database mengikuti `snake_case`.
- Jaga setiap perubahan tetap fokus. Jangan mengubah perilaku di luar kebutuhan fitur atau perbaikan.
- Tangani kegagalan secara eksplisit. Jangan menyembunyikan error atau memberi nilai sukses palsu.

## PHP dan Laravel

- Ikuti PSR-12 dan jalankan Laravel Pint pada file PHP yang berubah jika tersedia.
- Gunakan dependency injection untuk dependency aplikasi; hindari mengakses service secara global tanpa alasan.
- Letakkan validasi request pada Form Request ketika aturan validasi mulai kompleks atau dipakai ulang. Untuk validasi sederhana, gunakan validasi Laravel yang sudah ada.
- Otorisasi setiap operasi yang membaca atau mengubah data milik pengguna, kelas, atau peran tertentu. Jangan mengandalkan penyembunyian tombol di antarmuka sebagai kontrol akses.
- Gunakan Eloquent relationship dan query builder untuk akses database. Hindari query berulang di dalam loop; muat relasi yang dibutuhkan secara eksplisit.
- Bungkus beberapa perubahan database yang harus berhasil bersama dalam transaksi.
- Jaga controller tetap berfokus pada HTTP: validasi/otorisasi, koordinasi proses, lalu kembalikan response. Letakkan logika domain yang dapat dipakai ulang pada service atau model sesuai pola terdekat.
- Gunakan route bernama dan middleware yang sesuai. Pertahankan pemeriksaan akses pada server untuk setiap route.
- Simpan konfigurasi melalui berkas `config`; jangan membaca environment variable langsung di luar konfigurasi.

## Blade dan antarmuka

- Gunakan Blade escaping (`{{ }}`) untuk keluaran dinamis. Keluaran HTML tanpa escaping harus memiliki alasan dan sumber tepercaya yang jelas.
- Pisahkan tampilan menjadi partial atau komponen ketika markup digunakan ulang atau membuat halaman sulit dibaca.
- Pertahankan label, pesan validasi, dan notifikasi agar jelas bagi pengguna serta konsisten menggunakan Bahasa Indonesia.
- Pastikan form menampilkan error validasi dan tetap mempertahankan input yang aman setelah validasi gagal.
- Jangan mengandalkan JavaScript atau tampilan untuk menggantikan validasi dan otorisasi server.

## Database dan perubahan skema

- Buat perubahan skema melalui migration yang dapat dijalankan berurutan dan memiliki `down()` yang sesuai.
- Pilih tipe kolom dan constraint yang sesuai, termasuk foreign key dan indeks untuk relasi atau pencarian yang sering digunakan.
- Jangan menghapus atau mengubah data secara destruktif tanpa kebutuhan yang jelas dan pertimbangan kompatibilitas.
- Perubahan model, migration, dan pemakaian query harus konsisten dengan nama relasi serta kolom yang sudah ada.

## Pengujian dan pemeriksaan

- Tambahkan atau perbarui pengujian untuk perilaku yang berubah, terutama validasi, otorisasi, dan alur penting pengguna.
- Uji hasil yang terlihat oleh pengguna, bukan hanya detail implementasi internal.
- Sebelum menyelesaikan perubahan, jalankan pengujian terarah yang relevan dan formatter/linter yang tersedia.
- Jika pemeriksaan tidak dapat dijalankan, jelaskan alasannya dan jangan menyatakan bahwa perubahan sudah terverifikasi.

## Tinjauan perubahan

Sebelum mengirim perubahan, pastikan:

- Perubahan hanya mencakup kebutuhan yang diminta.
- Tidak ada kredensial, data pribadi, atau berkas lokal yang ikut ditambahkan.
- Error dan kasus input tidak valid ditangani dengan benar.
- Dokumentasi dan pengujian terkait sudah diperbarui bila diperlukan.
- Ringkasan perubahan menyebutkan pemeriksaan yang sudah dijalankan dan yang belum.
