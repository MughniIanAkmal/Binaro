# Perubahan Kamera Default & Toggle Mirror Absensi Guru - Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:**
Membuat kamera pada fitur Scan QR Absensi Guru default non-mirror dan menyediakan tombol toggle mirror di icon kamera kanan atas. Status mirror selalu kembali ke non-mirror saat fitur scan dibuka ulang.

**Architecture:**
- Modifikasi logic kamera pada halaman QR guru agar default tidak mirror; toggle mirror/non-mirror menggunakan icon kamera yang sudah ada pojok kanan atas.
- State mirror hanya berlaku per sesi scan, tidak disimpan antar sesi/penggunaan ulang.
- Tidak mengubah bagian siswa.

**Tech Stack:**
- Javascript/Typescript frontend (web)
- (Asumsi) Library webcam/camera yang saat ini digunakan (bisa diubah jika nanti ada di kode)

**Spec:**
docs/superpowers/specs/2026-11-11-perubahan-kamera-default-mirror-guru-design.md

## Global Constraints
- Berlaku hanya untuk halaman scan QR absensi guru (bukan siswa)
- Icon kamera di pojok kanan atas digunakan sebagai toggle
- Status mirror selalu reset ke non-mirror setiap fitur scan QR dibuka
- Basis hanya web frontend

## Review Focus
1. Scan QR selalu mulai dalam mode non-mirror – jangan retain status mirror antar sesi.
2. Toggle mirror harus langsung mengganti tampilan kamera tanpa reload atau kehilangan stream.
3. Hanya halaman scan QR guru yang kena perubahan – pastikan bagian siswa tidak ikut terpengaruh.
4. Icon toggle mudah diakses, tidak menutupi elemen penting lainnya.
5. Apabila webcam/library error/unsupported, aplikasi tetap stabil dan menampilkan error yang tepat.

---

### Task 1: Identifikasi & Refactor Logic Kamera QR Guru

**Files:**
- Modifikasi: `path/ke/component/scanQRGuru.js` (atau sesuai file implementasi yang ditemukan nanti)
- Test: Jika ada unit test UI, perbarui file test terkait; jika belum ada, buat test manual di dokumen QA

**Interfaces:**
- Konsumsi: Fungsi dan state controller kamera yang ada
- Produksi: State/props baru `isMirror: boolean` (default: false)

- [ ] Identifikasi kode/komponen yang menangani kamera scan QR di halaman guru.
- [ ] Refactor/atur kamera default tidak mirror (`isMirror` default false).
- [ ] Commit: "refactor: kamera scan QR guru default non-mirror"


### Task 2: Implementasi Toggle Mirror via Icon Kamera Kanan Atas

**Files:**
- Modifikasi: Komponen icon kamera di pojok kanan atas pada halaman scan QR guru
- Test: Manual test (klik icon, kamera toggle mirror)

**Interfaces:**
- Konsumsi: State/props `isMirror` dan event handler baru `toggleMirror()`
- Produksi: Mengubah state `isMirror` pada komponen kamera

- [ ] Ubah fungsi icon kamera kanan atas menjadi tombol toggle mirror (bisa menggunakan icon/animasi berbeda untuk mirror & non-mirror).
- [ ] Implementasi fungsi perubahan state mirror secara real-time tanpa reload.
- [ ] Commit: "feat: tambahkan toggle mirror melalui icon kamera"


### Task 3: Reset Status Mirror Setiap Buka Fitur Scan

**Files:**
- Modifikasi: Komponen/halaman `scanQRGuru.js` (atau setara)
- Test: Manual test (buka tutup scan QR beberapa kali, pastikan selalu mulai non-mirror)

**Interfaces:**
- Konsumsi: State/props inisialisasi halaman
- Produksi: State mirror selalu false setelah mounting halaman

- [ ] Pastikan setiap kali halaman scan QR guru di-load, state mirror di-reset ke false (non-mirror).
- [ ] Commit: "fix: reset status mirror ke default saat scan QR dibuka"


### Task 4: QA Manual & Edge/Error Case Handling

**Files:**
- Modifikasi: Dokumentasi QA/tes manual, tambahkan error handling jika kamera gagal/mirror toggle error

**Interfaces:**
- Konsumsi: Semua flow user di halaman scan QR guru
- Produksi: Manual QA checklist dan handling pesan error

- [ ] Manual QA: Test semua fitur kamera & toggle di halaman scan QR guru
- [ ] Tambahkan/benahi error handling jika webcam/toggle mirror bermasalah
- [ ] Commit: "chore: QA manual absensi guru, error handling kamera"

---