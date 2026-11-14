# Design System Binaro

> **Status: Draft v1** — disusun dari hasil audit UI/UX web (Laravel) & mobile (Flutter), 14 November 2026.
> Dokumen ini adalah **sumber tunggal** untuk warna, tipografi, bentuk, ikon, komponen, dan istilah di semua platform Binaro. Terapkan pada perubahan baru; kode lama dimigrasikan mengikuti `docs/superpowers/plans/2026-11-14-perbaikan-konsistensi-uiux.md`.
>
> Ruang lingkup: web (`laravel/resources/views/**`) dan mobile (`mobile/lib/**`).

---

## Prinsip

1. **Satu identitas, dua platform.** Pengguna yang sama (siswa, guru, admin) berpindah dari web ke HP tanpa merasa ganti produk.
2. **Token dulu, hardcode dilarang.** Warna/font/radius hanya boleh disebut lewat token; nilai hex/angka literal hanya boleh berada di file token.
3. **Jujur pada pengguna.** Tidak ada data demo, toast palsu, atau tombol tanpa fungsi di UI.
4. **Ramah anak SD, tetap rapi.** Sapaan siswa memakai "kamu", guru/admin "Anda"; seluruh teks Bahasa Indonesia (sesuai `CODING_STANDARDS.md`).
5. **Aman offline.** Sekolah mengakses aplikasi via LAN. UI tidak boleh bergantung pada aset dari internet (font, CSS, gambar).

---

## 1. Warna

### 1.1 Keputusan warna primer

**Primary = `#13527D`** (navy yang sudah dipakai web).

Alasan:
- Sudah dipakai web pada ±736 titik (vs `#1D5A86` di mobile ±181 titik) dan menjadi identitas di portal admin/guru/siswa, login, dan cetakan.
- Kontras terhadap putih **8,3:1** (vs `#1D5A86` 7,4:1) — lebih aman untuk teks kecil.
- Turunan gradien brand (`#0E3D5D → #13527D → #1E5D88`) sudah terbentuk di web.

Jika kelak ingin berbalik ke `#1D5A86`, cukup ubah satu baris di file token kedua platform — inilah gunanya token.

### 1.2 Token warna

| Token | Hex | Peran | Web (`@theme` / CSS var) | Mobile (`AppColors`) |
|---|---|---|---|---|
| `primary` | `#13527D` | Warna utama: tombol, sidebar aktif, app bar | `--color-primary` | `AppColors.primary` |
| `primary-hover` | `#0E3D5D` | Hover/pressed, ujung gradien | `--color-primary-hover` | `AppColors.primaryDark` |
| `primary-soft` | `#1E5D88` | Ujung gradien, variasi lembut | `--color-primary-soft` | — (baru) |
| `primary-tint` | `#EFF6FF` | Latar chip/pill seputar primary | `--color-primary-tint` | `AppColors.primaryLight` → diubah ke nilai ini |
| `accent` | `#F59E0B` | Aksen kuning: logo, badge, CTA khusus | `--color-accent` | `AppColors.warning` & aksen |
| `accent-bright` | `#FBBF24` | Highlight, gradien aksen | `--color-accent-bright` | (baru) |
| `accent-deep` | `#D97706` | Pressed/teks aksen di atas putih | `--color-accent-deep` | (baru) |
| `success` | `#16A34A` | Status berhasil | `--color-success` | `AppColors.success` |
| `success-tint` | `#DCFCE7` | Latar status berhasil | `--color-success-tint` | `AppColors.successLight` |
| `warning` | `#F59E0B` | Status peringatan (= accent) | `--color-warning` | `AppColors.warning` |
| `warning-tint` | `#FEF3C7` | Latar peringatan | `--color-warning-tint` | `AppColors.warningLight` |
| `danger` | `#DC2626` | Error/hapus | `--color-danger` | `AppColors.error` |
| `danger-tint` | `#FEE2E2` | Latar error | `--color-danger-tint` | `AppColors.errorLight` |
| `info` | `#0284C7` | Info/netral biru | `--color-info` | `AppColors.info` |
| `info-tint` | `#E0F2FE` | Latar info | `--color-info-tint` | `AppColors.infoLight` |
| `canvas` | `#F8FAFC` | Latar aplikasi | `--color-canvas` | `AppColors.background` |
| `surface` | `#FFFFFF` | Kartu, panel | `--color-surface` | `Colors.white` |
| `surface-alt` | `#F1F5F9` | Latar input/section sekunder | `--color-surface-alt` | `AppColors.surfaceAlt` |
| `line` | `#E2E8F0` | Border kartu, pemisah | `--color-line` | `AppColors.border` |
| `ink` | `#1E293B` | Teks utama | `--color-ink` | `AppColors.textPrimary` |
| `ink-soft` | `#64748B` | Teks sekunder | `--color-ink-soft` | `AppColors.textSecondary` |
| `ink-muted` | `#94A3B8` | Placeholder, teks tersier | `--color-ink-muted` | `AppColors.textMuted` |
| `navy-950` | `#0B2A44` | Latar gelap halaman login | `--color-navy-950` | (baru) |

**Gradien resmi (hanya dua):**
- `brand-gradient`: `115deg, #0E3D5D 0% → #13527D 55% → #1E5D88 100%` (hero, banner).
- `accent-gradient`: `135deg, #FBBF24 0% → #F59E0B 60% → #D97706 100%` (tombol tengah bottom nav, logo chip).

### 1.3 Pemetaan warna lama → token

| Nilai lama | Di mana | Token baru |
|---|---|---|
| `#1D5A86` | mobile (181×) | `primary` |
| `#154566` | mobile | `primary-hover` |
| `#E8F1F8` | mobile `primaryLight` | `primary-tint` |
| `#F2A007`, `#F7C948` | web login | `accent`, `accent-bright` |
| `#1E6091`, `#1E7FA8`, `#0F263B`, `#132F4C` | login (kedua platform) | `primary-soft` / `navy-950` sesuai peran |
| `#E6EBF2` (186×), `#EFF2F7` (45×), `#DDE3EE` | web | `line` |
| `#869AB8` | mobile login border | `line` (border input memakai `line`, fokus `primary`) |
| emerald/rose/sky/amber-* (web status) | web | token status di 1.2 |
| `#0B1E2E`, `#16324F`, `#0D253A` | mobile login gradien | `navy-950` + `primary-hover` |
| Hijau `#059669`/`#15803D`/`#047857` dsb., biru `#2563EB`/`#DBEAFE`/`#BFDBFE` | mobile ad-hoc | token status/`primary-tint` |
| `#C0C0C0` (avatar placeholder) | mobile | `line` |

### 1.4 Aturan warna

- Dilarang menulis hex/`Color(0xFF…)` literal di luar file token (`@theme` di `resources/css/app.css`, `AppColors` di `mobile/lib/utils/constants.dart`).
- Transparansi memakai modifier (`/80`, `withValues(alpha: …)`) dari token, bukan warna baru.
- Status mengikuti pasangan token–tint (contoh: pill sukses = latar `success-tint`, teks `success` dipertebal bila perlu).

---

## 2. Tipografi

**Font resmi: Plus Jakarta Sans** (400, 500, 600, 700, 800) — sudah dipakai web; mobile wajib **membundle file TTF** di `mobile/assets/fonts/` (bukan fetch internet).

### 2.1 Skala

| Peran | Ukuran/tinggi | Bobot | Web | Mobile |
|---|---|---|---|---|
| `display` | 24/32 | 800 | `text-2xl font-extrabold` | 24, w800 |
| `h1` | 20/28 | 700 | `text-xl font-bold` | 20, w700 |
| `h2` | 18/26 | 700 | `text-lg font-bold` | 18, w700 |
| `h3` | 16/24 | 600 | `text-base font-semibold` | 16, w600 |
| `body` | 13/20 | 400–500 | `text-[13px]` | 13, w400–500 |
| `body-sm` | 12/16 | 400–500 | `text-xs` | 12, w400–500 |
| `label` | 11/16 | 600 | `text-[11px] font-semibold` | 11, w600 |
| `caption` | 10/14 | 500 | `text-[10px]` | 10, w500 |
| `kicker` | 10/14 | 800 + `tracking 0.8`, kapital | (baru) | pola "SAHABAT BELAJAR" yang ada |
| `stat` | 20/28 | 800 | `text-xl font-extrabold` | 20, w800 (angka KPI) |

### 2.2 Aturan

- Dilarang: ukuran setengah poin (10.5, 11.5, 14.5, …), bobot `w900`, teks < 10px, dan lebih dari satu keluarga font.
- Kicker "SAHABAT BELAJAR" hanya di app bar mobile dan hero/login — bukan pengganti judul halaman.
- Teks pada dokumen cetak (RPP, daftar hadir) tetap Times New Roman 12pt sesuai standar surat sekolah; di luar cakupan design system ini.

---

## 3. Radius & Spasi

### 3.1 Radius

| Token | Nilai | Tailwind | Flutter | Penggunaan |
|---|---|---|---|---|
| `radius-sm` | 8px | `rounded-lg` | 8 | chip kecil, thumbnail, elemen inline |
| `radius-md` | 12px | `rounded-xl` | 12 | **tombol, input, tab, kartu kecil** |
| `radius-lg` | 16px | `rounded-2xl` | 16 | **kartu, panel** |
| `radius-xl` | 24px | `rounded-3xl` | 24 | modal, dialog, bottom sheet, bar navigasi |
| `radius-full` | 9999px | `rounded-full` | Stadium | pill, badge, avatar |

Pemetaan lama → baru: 4/5/6 → 8 · 10/14 → 12 · 17/18/20 → 16 · 22/26/30/32 → 24.

### 3.2 Spasi

Skala: **4, 8, 12, 16, 20, 24, 32, 40** px (Tailwind: 1, 2, 3, 4, 5, 6, 8, 10).
- Padding konten halaman: web `px-4 sm:px-7 py-6`, mobile `EdgeInsets.symmetric(horizontal: 20)`.
- Jarak antarkartu: 12–16. Jarak section: 24–32.

---

## 4. Ikon

- Web: **Font Awesome 6.5.1** (satukan versi di semua halaman, termasuk cetak yang masih 6.4.0). Mobile: **Material Icons varian `_rounded`/`_outlined`**.
- Kedua set dipilih karena karakternya bulat — jangan mencampur varian (outline vs solid) untuk peran sama dalam satu halaman.
- Ukuran: ikon nav web `w-4` (16px), ikon konten 14–16px, ikon fitur 20–24px; mobile standar 20–24px, nav 22–26px.
- **Emoji dilarang sebagai ikon UI** (🟢🔴💾👤👋🔒 dsb.) — ganti ikon library atau teks.
- Ikon dekoratif diberi `aria-hidden="true"` (web).

---

## 5. Komponen Inti

Spesifikasi minimum; detail implementasi mengikuti pola yang sudah ada di platform masing-masing.

### 5.1 Tombol

| Varian | Web | Mobile |
|---|---|---|
| Primary | `bg-primary hover:bg-primary-hover text-white text-xs font-bold px-4 py-2 rounded-xl transition` | `ElevatedButton` bg `primary`, fg putih, radius 12, tinggi 46–50, elevation 0 |
| Secondary | `bg-surface hover:bg-surface-alt text-ink border border-line rounded-xl` | OutlinedButton, side `line`, radius 12 |
| Danger | `bg-danger-tint text-danger border border-danger/20 rounded-xl` | bg `danger-tint`, fg `danger`, radius 12 |
| Accent CTA | `bg-accent text-ink rounded-xl shadow` (dipakai hemat, 1 per halaman) | bg `accent`, fg `ink` |

- Satu ukuran padding per varian; hover/pressed wajib ada; disabled = opasitas 60%.
- Label tombol: kata kerja aktif (Simpan, Batal, Hapus, Coba Lagi).

### 5.2 Kartu

- Web: `.card` — `bg-surface border border-line rounded-2xl` (tanpa shadow atau `shadow-sm`).
- Mobile: `Container` putih, radius 16, border `line` 1px, shadow `black 2% blur 6 offset(0,2)` — diekstrak jadi satu widget `AppCard`.
- Padding dalam kartu: 16 (mobile) / `p-4`–`p-6` (web).

### 5.3 Input

- Latar `surface-alt` (web `bg-slate-50`, mobile `#F8FAFC`), border `line` 1px, radius 12, fokus border `primary` (+ ring lembut web `focus:ring-2 ring-primary/20`).
- **Satu spesifikasi untuk semua layar**, termasuk login (saat ini login kedua platform punya spesifikasi sendiri).
- Label selalu di atas field; pesan error di bawah field dengan warna `danger`.

### 5.4 Badge / Pill

- Pill status: `rounded-full px-2.5 py-1 text-[11px] font-bold`, pasangan token–tint.
- Badge angka (notifikasi): `accent`, teks `ink`, minimal 16px diameter.

### 5.5 Alert / Snackbar

- Web (flash banner): `bg-{token}-tint border border-{token}/20 text-{token}(-800) text-xs rounded-xl` + tombol tutup — **satu partial Blade** dipakai 3 layout.
- Mobile: `SnackBar` floating, radius 12, latar solid token status (atau `primary` untuk info), durasi standar 3 detik, **satu helper** di util (bukan 3 salinan).
- Error selalu memberi tahu apa yang terjadi + apa yang bisa dilakukan ("Gagal memuat data. Periksa koneksi lalu coba lagi.").

### 5.6 Modal / Dialog

- Web: overlay `bg-slate-900/50 backdrop-blur-sm`, panel `bg-surface border border-line rounded-2xl max-w-2xl` — satu komponen modal.
- Mobile: dialog blur (pola `showGeneralDialog` yang ada) radius 24 untuk semua konfirmasi; hapus campuran `AlertDialog` r16 vs dialog r26.
- Konfirmasi destruktif selalu: judul, penjelasan konsekuensi, tombol `danger` + `Batal`.

### 5.7 Empty, Loading, Error state

- Empty state: ikon + judul + 1 kalimat + (opsional) tombol aksi — **satu komponen/widget** di tiap platform.
- Loading: skeleton atau spinner `primary`; tombol submit memakai spinner di dalam tombol.
- Error muat data: **wajib** ada tampilan error + tombol "Coba Lagi". Dilarang fallback diam-diam ke data demo.

### 5.8 Navigasi

- Web: sidebar tetap per role; di layar < 640px sidebar siswa digantikan tombol menu + drawer.
- Mobile: satu bottom nav (widget `DynamicBottomNav`) — label: **Beranda, Pembelajaran, Ujian, Jadwal, Profil**; tab yang belum ada fiturnya tetap tampil tapi jujur ("Segera hadir"), bukan mati diam-diam.
- Label nav web siswa diselaraskan: Beranda, Pembelajaran, Ujian, Jadwal Pelajaran, Notifikasi PR, Profil.

---

## 6. Branding

| Elemen | Ketentuan |
|---|---|
| Nama produk | "Binaro" (kapital di awal saja). Wordmark semua portal memakai casing ini. |
| Tagline | "Sahabat Belajar" — kicker di app bar mobile; boleh muncul di login/hero. |
| Nama sekolah | Satu sumber: `config('binaro.nama_sekolah')` (`SDN Kalitapen 01`). Mobile mengambil dari API atau menyamakan konstantanya. **Verifikasi nama resmi ke sekolah.** |
| Logo | Belum ada aset logo asli di kedua platform (sekarang ikon library). Buat 1 logo (SVG + PNG), turunkan: favicon web, app icon Android, splash mobile. |
| Favicon | `<link rel="icon" href="{{ asset('favicon.ico') }}">` di semua layout web. |
| Versi aplikasi | Sumber tunggal `pubspec.yaml` (`1.0.0+1`) → ditampilkan via `AppConstants.appVersion`; hapus teks `v1.2.0-SD` yang hardcode. |
| App icon & splash | Ganti dari default Flutter: adaptive icon warna `primary`, splash latar `canvas` + logo. |

---

## 7. Kamus Istilah & Gaya Bahasa

| Istilah baku | Hindari / catatan |
|---|---|
| Beranda | "Home", "Dashboard" (untuk siswa; admin/guru boleh "Dashboard") |
| Mata Pelajaran | "Mapel" hanya di teks naratif singkat, bukan label nav |
| Ujian | Nav label; judul halaman boleh "Ujian Online" |
| Kata Sandi | "Password" (pilih satu di seluruh UI — rekomendasi: "Kata Sandi") |
| Keluar | "Logout", "Keluar Portal", "Keluar dari Akun" |
| Tenggat | "Deadline" |
| Coba Lagi | "Retry" |
| NISN / NIS | Konsisten di semua form login |
| Guru Pengampu | — |

- **Sapaan:** siswa → "kamu" (termasuk akhiran -mu); guru & admin → "Anda". Jangan campur dalam satu layar.
- Kalimat error memakai pola: *apa yang gagal + apa yang harus dilakukan*.
- Angka, tanggal, dan waktu memakai format Indonesia (13 November 2026, 07.30).

---

## 8. Aksesibilitas

- Kontras teks minimal 4,5:1 (teks kecil wajib memakai `ink-soft` atau lebih gelap — bukan `ink-muted` untuk isi penting).
- Indikator fokus keyboard selalu terlihat (web: jangan `focus:outline-none` tanpa pengganti; contoh benar: `focus-visible:outline-2 outline-primary`).
- Nav aktif diberi `aria-current="page"` (web).
- Target sentuh mobile minimal 44×44dp; ukuran teks menghormati skala sistem (`MediaQuery.textScalerOf`, tanpa `maxLines: 1` pada teks penting).
- Label `alt`/semantik untuk gambar bermakna (QR, foto).

---

## 9. Keputusan (disetujui 14 November 2026)

| # | Keputusan | Hasil | Alternatif yang ditolak |
|---|---|---|---|
| D1 | Warna primary | `#13527D` (alasan di 1.1) | `#1D5A86` (mobile sekarang) |
| D2 | Font mobile | Bundle Plus Jakarta Sans | Tetap Roboto |
| D3 | Label sandi | "Kata Sandi" | "Password" |
| D4 | Nama sekolah | Ikuti `config('binaro.nama_sekolah')` = "SDN Kalitapen 01" | Ejaan lain (verifikasi ke sekolah saat ada kesempatan) |

Eksekusi mengikuti `docs/superpowers/plans/2026-11-14-perbaikan-konsistensi-uiux.md`.
