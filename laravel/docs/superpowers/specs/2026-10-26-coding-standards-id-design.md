# Design: CODING_STANDARDS.md (Bahasa Indonesia)

Tanggal: 2026-10-26

## Intent

Menyediakan satu file standar coding Laravel dalam Bahasa Indonesia untuk project `binaro_laravel`, berdasarkan seluruh isi repo [xqsit94/laravel-coding-guidelines](https://github.com/xqsit94/laravel-coding-guidelines). File ini jadi rujukan tim saat menulis dan mereview kode.

## Goals

- Satu file `CODING_STANDARDS.md` di root project.
- Terjemahan penuh seluruh konten sumber: introduction + 20 topik panduan (21 file konten).
- Semua contoh kode, tabel, dan link dipertahankan verbatim (kode tidak diterjemahkan).
- Struktur heading mengikuti urutan sidebar sumber; `avoid-env-variable-directly` (ada di repo tapi tidak masuk sidebar) disisipkan setelah "Use config helper" karena topiknya terkait.
- Atribusi ke sumber dan link live site di bagian atas file.

## Non-goals

- Tidak menyesuaikan atau menambah aturan khusus project (kecuali diminta terpisah nanti).
- Tidak menerjemahkan ke multi-file atau situs dokumentasi.
- Tidak mengubah repo sumber.
- Tidak commit ke git.

## Constraints

- Bahasa: Indonesia (permintaan user).
- Komentar dalam contoh kode tetap bahasa aslinya bila sudah Inggris; label "Buruk/Baik" pada contoh menggantikan "Bad/Good".
- `index.md` sumber tidak dilakukan (homepage VitePress, tidak ada konten).

## Approaches

1. **Terjemahan penuh** — semua isi 21 file sumber diterjemahkan utuh. **Dipilih**: permintaan "berdasarkan standar pada repo ini" paling langsung terpenuhi oleh versi lengkap; mayoritas konten berupa kode sehingga hasilnya tidak terlalu panjang.
2. **Ringkasan praktis** — tiap topik dipadatkan jadi poin aturan + 1 contoh. Ditolak: kehilangan sebagian penjelasan sumber.

## Decisions

- Nama file: `CODING_STANDARDS.md` (default, boleh dikoreksi saat review).
- Lokasi: root project.
- Urutan konten: mengikuti sidebar `.vitepress/config.ts` sumber.
- Sumber kerja: clone lokal repo sumber di temp (sudah ada).

## What's left to the builder

1. Baca ke-21 file sumber.
2. Terjemahkan dan rakit jadi satu file, mengikuti urutan di atas.
3. Verifikasi: jumlah topik, tabel, dan blok kode cocok dengan sumber (tidak ada yang terlewat).
