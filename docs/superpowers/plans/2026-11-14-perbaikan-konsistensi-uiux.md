# Perbaikan Konsistensi UI/UX Binaro (Web + Mobile) - Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:**
Menyatukan identitas UI/UX antara web Laravel (admin/guru/siswa) dan mobile Flutter (siswa): satu warna primer, satu font, satu set token, komponen reusable, branding lengkap, serta menghapus UI palsu/data demo. Sumber analisis: audit UI/UX 2026-11-14; token baku: `docs/design-system.md`.

**Architecture:**
- Token didefinisikan satu kali per platform (`@theme` di `laravel/resources/css/app.css`, `AppColors` di `mobile/lib/utils/constants.dart`), lalu seluruh halaman dimigrasikan memakai token.
- Komponen berulang diekstrak: Blade components (web) dan widget bersama (mobile).
- Kejujuran UI diperbaiki lebih dulu (Fase 1) sebelum konsolidasi gaya (Fase 2–4).

**Tech Stack:**
- Web: Laravel 11 Blade, Tailwind v4 (Vite), Font Awesome 6.5.1
- Mobile: Flutter Material 3, shared_preferences (baru)

**Spec:**
docs/design-system.md

## Global Constraints

- Jangan mengubah perilaku fungsional di luar yang tercantum di tiap tugas.
- Ikuti `laravel/CODING_STANDARDS.md`; semua teks UI Bahasa Indonesia.
- Nilai hex/angka hanya boleh ada di file token setelah migrasi (Prinsip 2 design-system.md).
- Aset tidak boleh dimuat dari internet (font, CSS, gambar) — aplikasi dipakai via LAN sekolah.
- Setiap tugas diakhiri verifikasi; jangan lanjut tugas berikut bila verifikasi gagal.

## Keputusan Terbuka (konfirmasi sebelum Fase 2)

Lihat `docs/design-system.md` bagian 9: **D1** warna primary `#13527D`, **D2** font mobile Plus Jakarta Sans, **D3** istilah "Kata Sandi", **D4** nama sekolah. Fase 1 boleh dikerjakan tanpa menunggu keputusan ini.

---

## Fase 1 — Kritis (stabilitas & kejujuran UI)

### T1. Web: aktifkan build Tailwind v4, hapus Play CDN — effort: M

- [x] Jalankan `npm install` lalu `npm run build` di `laravel/`; pastikan `public/build/` terbentuk tanpa error.
- [x] Ganti `<script src="https://cdn.tailwindcss.com"></script>` dengan `@vite(['resources/css/app.css','resources/js/app.js'])` di `resources/views/layouts/app.blade.php:8`, `layouts/guru.blade.php:8`, `layouts/siswa.blade.php:8`.
- [x] Lakukan hal sama pada halaman cetak: `guru/absen/cetak_pdf.blade.php:8`, `rpp_guru/cetak.blade.php`, `admin/qr_siswa/cetak.blade.php` (cetak boleh tetap inline CSS bila perlu offline total — catat keputusannya).
- [x] Pindahkan variabel `:root` (`--binaro-navy`, dll.) dari masing-masing layout ke `resources/css/app.css` sebagai satu blok token (sekaligus persiapan T6).
- [x] Periksa kelas khusus v4 yang tadinya no-op di CDN v3 (`shadow-xs` 105×, `shadow-2xs` 26×, `backdrop-blur-xs`) kini ter-render benar; sesuaikan bila tampilan menyimpang.
- [x] Hapus atau rombak `resources/views/welcome.blade.php` (halaman starter Laravel yang off-brand dan tidak terjangkau route).
- [x] **Verifikasi:** login + dashboard admin/guru/siswa tampil utuh tanpa koneksi internet (blokir CDN di devtools); `grep -r "cdn.tailwindcss" resources/views` = 0 hasil.

### T2. Web: hapus data demo & label salah di dashboard guru — effort: S

- [x] `admin/guru/dashboard.blade.php`: hapus fallback `{{ $totalMapel ?? 4 }}`, `{{ $totalMateri ?? 38 }}`, `{{ $prAktif ?? 2 }}` — tampilkan nilai nyata; bila data belum ada tampilkan 0 + empty state.
- [x] Ganti teks hardcode "Semester Ganjil" dengan `{{ config('binaro.semester') }} {{ config('binaro.tahun_ajaran') }}`.
- [x] Cari pola serupa (`?? ` fallback angka di view lain) dan bersihkan yang menampilkan data palsu.
- [x] **Verifikasi:** dashboard guru dengan data kosong menampilkan angka nyata (0) dan empty state, bukan demo.

### T3. Mobile: simpan sesi login — effort: S

- [x] Tambah `shared_preferences` ke `mobile/pubspec.yaml`.
- [x] Setelah login sukses (`mobile/lib/screens/login_page.dart`), simpan token/data sesi; `BinaroApp` (`main.dart`) memeriksa sesi → langsung ke `DashboardPage` bila valid.
- [x] Logout menghapus sesi (`profile_page.dart`).
- [x] **Verifikasi:** login → tutup app → buka lagi: masuk dashboard tanpa login ulang; logout kembali ke login.

### T4. Mobile: hapus UI palsu, tangani error dengan jujur — effort: M

- [x] `profile_page.dart`: hapus toast palsu "Kartu siap dicetak" / "Barcode berhasil disimpan ke galeri" — implementasi simpan/cetak sungguhan atau sembunyikan tombol sampai fitur siap.
- [x] `profile_page.dart`, `dashboard_page.dart`: tombol mati (`onPressed: () {}` — "Buka Layar Absen Penuh", "Lihat Semua Tugas", bel notifikasi) diberi aksi nyata atau disembunyikan.
- [x] Hapus fallback demo (`AppConstants.defaultSiswaId`, "Budi Santoso", barcode statis): `catch (_) {}` di `dashboard_page.dart:69` dan `profile_page.dart:69` diganti tampilan error + tombol "Coba Lagi" (ulang fetch).
- [x] Buat satu widget `ErrorState` + `EmptyState` di `mobile/lib/widgets/` dan gunakan pada layar yang bermasalah.
- [x] **Verifikasi:** matikan server → buka app: muncul pesan error + Coba Lagi, tanpa data demo; nyalakan server → Coba Lagi memuat data.

### T5. Mobile: satukan bottom navigation — effort: S

- [x] Jadikan `DynamicBottomNav` (`mobile/lib/widgets/dynamic_bottom_nav.dart`) satu-satunya nav; hapus nav inline `dashboard_page.dart:790-889`.
- [x] Samakan set ikon dengan keputusan design-system (Beranda, Pembelajaran, Ujian, Jadwal, Profil); tab Jadwal memakai snackbar "Segera hadir" yang sudah ada.
- [x] **Verifikasi:** navigasi dari semua tab konsisten (animasi, ikon, perilaku pushReplacement).

---

## Fase 2 — Token & Komponen (setelah D1–D3 disetujui)

### T6. Web: satu sumber token warna & migrasi hex — effort: L

- [ ] Definisikan token design-system.md §1.2 di `@theme` (`resources/css/app.css`) + CSS var untuk pemakaian non-Tailwind.
- [ ] Migrasi massal: `#13527D`/`#0E3D5D`/`#1E5D88` → kelas token (`bg-primary`, dll.); `#E6EBF2`/`#EFF2F7`/`#DDE3EE` → `border-line`; status emerald/rose/sky → token status.
- [ ] Hapus gaya inline `style="background: var(--binaro-navy)"` (29×) → kelas token; hapus kelas util lama `.sidebar-brand-bg`, `.active-menu-pill` warna hardcode.
- [ ] Konsolidasikan 4 subsistem desain (admin/guru, siswa, login, `qr_absen/_styles.blade.php`) ke token bersama; pertahankan perilaku khusus (dark mode QR) sebagai pengecualian terdokumentasi.
- [ ] **Verifikasi:** `grep -rE "#13527D|#1D5A86|#E6EBF2" resources/views` hanya menyisakan file token; visual semua portal tidak berubah (screenshot bandingkan).

### T7. Mobile: migrasi ke AppColors — effort: M

- [ ] Perbarui `AppColors` sesuai design-system §1.2 (primary `#13527D`, dst.); tambah `accentBright`, `accentDeep`, `navy950`.
- [ ] Ganti ±590 `Color(0xFF…)` di 16 screen ke `AppColors.*` (search-replace per nilai sesuai tabel pemetaan §1.3).
- [ ] **Verifikasi:** `grep -rE "Color\(0xFF" mobile/lib | grep -v constants.dart` = 0 (kecuali pengecualian tercatat); app jalan normal.

### T8. Mobile: font & TextTheme — effort: S

- [ ] Unduh Plus Jakarta Sans 400–800 ke `mobile/assets/fonts/`, daftarkan di `pubspec.yaml`, set `fontFamily` di `main.dart`.
- [ ] Bangun `TextTheme` di `ThemeData` sesuai skala design-system §2.1; ganti 229 `TextStyle` inline bertahap mulai dari pola berulang (judul kartu 14.5→h3/body-sm, dst.).
- [ ] **Verifikasi:** tampilan tidak bergeser drastis; tidak ada ukuran setengah poin baru.

### T9. Web: ekstrak Blade components — effort: L

- [ ] Buat `resources/views/components/`: `<x-button>`, `<x-card>`, `<x-badge>`, `<x-alert>` (ganti 3 salinan flash banner), `<x-modal>`, `<x-input>`, `<x-empty-state>`.
- [ ] Refactor halaman per portal bertahap, mulai dari yang paling berulang (dashboard admin/guru, tabel-tabel).
- [x] Styling pagination default `$x->links()` → view pagination kustom satu kali.
- [ ] **Verifikasi:** halaman yang direfaktor tampil identik; markup berulang menyusut (spot-check 3 halaman).

### T10. Mobile: ekstrak widget bersama — effort: M

- [ ] Buat di `mobile/lib/widgets/`: `AppCard`, `AppBar` kustom (ganti 5 duplikasi), `PrimaryButton`, `AppTextField` (satu spesifikasi, termasuk login), `showAppDialog` (radius 24), `showAppSnackbar` (satu helper), `AppBadge`.
- [ ] Ganti pemakaian di layar satu per satu; radius kartu 16/18/20 → 16, tombol 12/14 → 12.
- [ ] **Verifikasi:** tidak ada `ElevatedButton.styleFrom` inline tersisa di screens (kecuali varian khusus terdokumentasi).

### T11. Terapkan kamus istilah & sapaan — effort: S

- [ ] Terapkan design-system §7: "Kata Sandi" (mobile `profile_page.dart:1299` vs `:340` disamakan), "Keluar" (web "Keluar Portal", mobile "Keluar dari Akun" → satu istilah), label nav mobile "Mapel"→"Pembelajaran", "Ujian".
- [ ] Samakan sapaan: layar siswa "kamu", guru/admin "Anda" (periksa empty state & dialog).
- [ ] **Verifikasi:** `grep -ri "password\|logout\|keluar portal" resources/views mobile/lib` hanya menyisakan istilah baku.

---

## Fase 3 — Branding

### T12. Logo & aset brand — effort: M (butuh desain)

- [x] Buat logo Binaro (SVG master + PNG ekspor) — bisa dimulai dari mark yang ada (topi wisuda / kotak amber).
- [x] Pasang di: sidebar 3 portal web, login web (ganti huruf "B" gradien), app bar mobile (ganti ikon Material), splash.
- [x] **Verifikasi:** tidak ada lagi ikon library yang berperan sebagai logo.

### T13. Favicon, app icon, splash — effort: S

- [x] Web: tambah `<link rel="icon">` di 3 layout + halaman cetak.
- [x] Mobile: generate adaptive icon (warna `primary`) dari logo; isi `launch_background.xml` + logo (hapus komentar template).
- [x] **Verifikasi:** tab browser menampilkan favicon; install APK: ikon & splash ber-brand.

### T14. Rapikan identitas tersebar — effort: S

- [x] Samakan casing wordmark "Binaro" di semua portal (siswa saat ini "BINARO").
- [x] Sinkron versi: hapus `v1.2.0-SD` (`profile_page.dart:28`) → tampilkan dari `AppConstants.appVersion` = pubspec.
- [x] Satukan Font Awesome 6.4.0 → 6.5.1 di `rpp_guru/cetak.blade.php:6`, `guru/absen/cetak_pdf.blade.php:9`.
- [x] Ganti emoji ikon (🟢🔴🟡 web ujian, 👤 mobile `ujian_page.dart:587`) dengan ikon library.
- [x] **Verifikasi:** grep emoji di UI = 0; versi tampil konsisten.

---

## Fase 4 — Poles

### T15. Radius & spasi seragam — effort: M

- [ ] Terapkan pemetaan radius design-system §3.1 di kedua platform (mobile: 14 nilai → 4 token; web: tombol `rounded-lg`→`rounded-xl` untuk varian primary).
- [ ] **Verifikasi:** spot-check tombol/kartu/modal di 5 halaman per platform.

### T16. Navigasi web di layar kecil — effort: M

- [ ] Siswa portal: sidebar `hidden sm:flex` saat ini meninggalkan tanpa navigasi di < 640px — tambah header tombol menu + drawer/sidebar overlay mobile.
- [ ] Admin/guru: sidebar fixed `ml-64` diberi mode collapse di layar kecil.
- [ ] **Verifikasi:** uji 375px viewport: navigasi terjangkau di semua portal.

### T17. Aksesibilitas — effort: M

- [ ] Web: ganti `focus:outline-none` polos dengan ring terlihat; tambah `aria-current="page"` pada nav aktif; naikkan teks < 10px minimal ke 10px.
- [ ] Mobile: dukung text scaling (`MediaQuery.textScalerOf`, clamp), target sentuh ≥ 44dp pada nav & chip, perbaiki kontras `white70`/`#94A3B8` untuk teks penting.
- [ ] **Verifikasi:** navigasi keyboard web masuk akal; teks sistem besar tidak membuat overflow.

### T18. Rapikan teknis — effort: S

- [ ] Mobile: samakan alur login dengan web (field tunggal "NISN / NIS" — verifikasi dulu kebutuhan API `login`); arahkan dashboard/profile memakai `ApiService` (bukan `http` inline).
- [ ] Mobile: perbaiki `test/widget_test.dart` (masih counter test default, akan gagal).
- [ ] Web: satukan empty state tabel (4 gaya → 1 komponen).
- [ ] **Verifikasi:** `flutter test` lulus; grep `http://` di screens = 0 (semua via ApiService).

---

## Urutan & Estimasi

| Tugas | Fase | Effort | Dependensi |
|---|---|---|---|
| T1 pipeline CSS | 1 | M | — |
| T2 data demo web | 1 | S | — |
| T3 sesi mobile | 1 | S | — |
| T4 error/empty mobile | 1 | M | — |
| T5 bottom nav | 1 | S | — |
| T6 token web | 2 | L | D1, T1 |
| T7 token mobile | 2 | M | D1 |
| T8 font mobile | 2 | S | D2 |
| T9 Blade components | 2 | L | T6 |
| T10 widget mobile | 2 | M | T7 |
| T11 istilah | 2 | S | D3 |
| T12 logo | 3 | M | aset desain |
| T13 favicon/icon/splash | 3 | S | T12 |
| T14 identitas tersebar | 3 | S | T12 |
| T15 radius/spasi | 4 | M | T6, T7 |
| T16 nav web kecil | 4 | M | — |
| T17 aksesibilitas | 4 | M | — |
| T18 rapikan teknis | 4 | S | T4 |

## Review Focus

1. Tidak ada regresi visual setelah migrasi token — bandingkan screenshot sebelum/sesudah per portal.
2. UI tidak lagi bergantung internet: uji dengan CDN/internet diblokir.
3. Tidak tersisa data demo, toast palsu, atau tombol mati setelah Fase 1.
4. Setelah Fase 2, tidak ada hex/angka literal di luar file token di kode yang tersentuh.
