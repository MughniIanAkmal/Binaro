# CODING_STANDARDS.md (Bahasa Indonesia) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membuat satu file `CODING_STANDARDS.md` di root project yang berisi terjemahan penuh Bahasa Indonesia dari seluruh isi repo xqsit94/laravel-coding-guidelines.

**Architecture:** Satu file Markdown hasil konsolidasi 21 file sumber. Setiap file sumber menjadi satu section `##` dengan urutan mengikuti sidebar VitePress sumber; kode contoh dipertahankan verbatim; prosa diterjemahkan.

**Tech Stack:** Markdown murni, tanpa tooling tambahan.

**Spec:** `docs/superpowers/specs/2026-10-26-coding-standards-id-design.md`

## Global Constraints

- Sumber kerja: clone lokal `C:\Users\SHAMIL\AppData\Local\Temp\opencode\laravel-coding-guidelines\docs\`. Jika tidak ada, clone ulang `https://github.com/xqsit94/laravel-coding-guidelines.git` ke path tersebut. Jangan ubah isi sumber.
- Output: `CODING_STANDARDS.md` di root project. Jangan sentuh file lain. Jangan commit (user belum meminta).
- Seluruh prosa (judul, paragraf, label, sel tabel berupa kalimat) dalam Bahasa Indonesia. Contoh kode PHP/JS, nama class/method/tool, dan nama proper tetap verbatim bahasa aslinya.
- Komentar di dalam blok kode tidak diterjemahkan.
- Hapus YAML front matter sumber (`---` … `---`) — berlaku untuk `naming-conventions.md`, `shorter-readable-syntax.md`, `use-standard-laravel-tools.md`, `avoid-env-variable-directly.md`.
- Heading: judul utama tiap sumber menjadi `## `; semua sub-heading di dalam sumber menjadi `### ` (normalisasi, tanpa memperdalam).
- Label contoh: sumber memakai `Bad:` / `Good:` — tulis sebagai `Buruk:` / `Baik:` (teks polos sebelum blok kode).
- Tabel: pertahankan jumlah baris dan sel kode apa adanya; sel header dan sel berupa kalimat diterjemahkan; nama tool/teknologi tetap.
- Link markdown sumber dipertahankan persis (URL dan teks link).
- Struktur awal file (Task 1 menulis tepat ini, tanpa section apa pun):

```markdown
# Standar Coding Laravel

Dokumen ini adalah terjemahan Bahasa Indonesia dari [xqsit94/laravel-coding-guidelines](https://github.com/xqsit94/laravel-coding-guidelines) ([situs live](https://xqsit94.github.io/laravel-coding-guidelines/)).
```

(2 link atribusi ini selalu ada sejak Task 1.)

## Urutan 21 section (judul persis untuk heading `##`)

1. Pengenalan — `introduction.md`
2. Ikuti konvensi penamaan Laravel — `naming-conventions.md`
3. Nama variabel yang deskriptif dan mudah dibaca — `readable-descriptive-variable-names.md`
4. Sintaks yang lebih singkat dan mudah dibaca — `shorter-readable-syntax.md`
5. Hindari controller yang terlalu gemuk (fat controller) — `avoid-fat-controllers.md`
6. Logika bisnis di kelas Repository — `business-logics-repositories.md`
7. Prinsip tanggung jawab tunggal (SRP) — `single-responsibility-principle.md`
8. Jangan ulangi diri sendiri (DRY) — `dont-repeat-yourself.md`
9. Utamakan Eloquent dan Laravel Collections — `prefer-eloquent-and-collections.md`
10. Mass assignment — `mass-assignment.md`
11. Hindari query di template Blade — `avoid-queries-blade-template.md`
12. Jangan letakkan JS dan CSS di Blade — `avoid-js-css-blade-template.md`
13. Gunakan deklarasi tipe PHP — `php-type-declaration.md`
14. Gunakan IoC container untuk project jangka panjang — `ioc-container.md`
15. Gunakan konstanta dan language helper — `use-constants-language-helper.md`
16. Gunakan helper config — `config-helper.md`
17. Jangan ambil data dari file .env secara langsung — `avoid-env-variable-directly.md`
18. Gunakan Request class untuk validasi — `validation.md`
19. Gunakan Accessors dan Mutators — `use-accessors-mutators.md`
20. Gunakan tool Laravel standar yang diakui komunitas — `use-standard-laravel-tools.md`
21. Praktik baik lainnya — `other-good-practices.md`

## Review Focus

Kondisi yang paling mungkin gagal dan tidak teruji test (karena ini dokumen), plus langkah verifikasi yang menempelkannya:

1. Blok kode hilang atau berubah saat diterjemahkan → setiap task memverifikasi jumlah pasang fence ` ``` ` per section terhadap angka sumber (total 48 blok).
2. Baris tabel hilang → verifikasi jumlah baris data per tabel (24 / 16 / 19) setelah task pemiliknya.
3. Heading sumber (sub-heading) ikut terbawa level `#` atau front matter ikut tercopy → grep `(?m)^---$` harus 0 dan sub-heading harus `###`.
4. Judul section tidak sama dengan daftar "Urutan 21 section" → Task 7 membandingkan semua `## ` di file terhadap daftar persis di atas.
5. Urutan section menyimpang dari sidebar sumber → Task 7 memeriksa urutan kemunculan ke-21 judul.

---

### Task 1: Kerangka file

**Files:**
- Create: `CODING_STANDARDS.md`

**Interfaces:**
- Produces: file berisi H1 + paragraf atribusi (2 link) yang jadi dasar task berikutnya (task lain hanya menambah section di bawahnya).

- [ ] **Step 1: Tulis kerangka awal**

Buat `CODING_STANDARDS.md` di root dengan tepat dua bagian: H1 `# Standar Coding Laravel` dan paragraf atribusi persis seperti dipotong "Struktur awal file" di Global Constraints (2 link markdown).

- [ ] **Step 2: Verifikasi kerangka**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"fences=" + ([regex]::Matches($c, '(?m)^```')).Count
"links=" + ([regex]::Matches($c, '\]\(')).Count
"sections=" + ([regex]::Matches($c, '(?m)^## ')).Count
```
Expected: `fences=0`, `links=2`, `sections=0`.

---

### Task 2: Section 1–4 (Pengenalan s/d Sintaks yang lebih singkat)

**Files:**
- Modify: `CODING_STANDARDS.md`
- Read only: sumber `introduction.md`, `naming-conventions.md`, `readable-descriptive-variable-names.md`, `shorter-readable-syntax.md`

**Interfaces:**
- Consumes: kerangka Task 1 (append section setelah paragraf atribusi).
- Produces: 4 section `##` baru (judul persis di Step 2 task ini).

- [ ] **Step 1: Baca keempat file sumber**

Baca utuh keempat file. Catat: `naming-conventions.md` punya 1 tabel (24 baris data + header + separator, 2 link), `shorter-readable-syntax.md` punya 1 tabel (16 baris data), `readable-descriptive-variable-names.md` punya 11 blok kode dan 6 sub-heading, `introduction.md` 4 sub-heading dan tanpa kode.

- [ ] **Step 2: Terjemahkan dan tulis 4 section**

Append ke `CODING_STANDARDS.md` empat section `##` berurutan dengan judul persis, menerapkan semua Global Constraints (prosa Indonesia, kode verbatim, front matter dihapus, sub-heading `###`, label `Buruk:`/`Baik:`):

1. `## Pengenalan`
2. `## Ikuti konvensi penamaan Laravel`
3. `## Nama variabel yang deskriptif dan mudah dibaca`
4. `## Sintaks yang lebih singkat dan mudah dibaca`

- [ ] **Step 3: Verifikasi**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"fences=" + ([regex]::Matches($c, '(?m)^```')).Count
"links=" + ([regex]::Matches($c, '\]\(')).Count
"tables=" + ([regex]::Matches($c, '(?m)^\|')).Count
"sections=" + ([regex]::Matches($c, '(?m)^## ')).Count
"frontmatter=" + ([regex]::Matches($c, '(?m)^---$')).Count
```
Expected: `fences=26` (13 blok × 2 penanda), `links=4` (2 atribusi + 2 dari tabel naming), `tables=44` (26 baris tabel naming + 18 baris tabel shorter, semua baris diawali `|`), `sections=4`, `frontmatter=0`.

---

### Task 3: Section 5–8 (Fat controller s/d DRY)

**Files:**
- Modify: `CODING_STANDARDS.md`
- Read only: sumber `avoid-fat-controllers.md`, `business-logics-repositories.md`, `single-responsibility-principle.md`, `dont-repeat-yourself.md`

**Interfaces:**
- Consumes: isi Task 2 (append di bawah section terakhir).
- Produces: 4 section `##` baru (judul persis di Step 2 task ini).

- [ ] **Step 1: Baca keempat file sumber**

Masing-masing 2 blok kode, tanpa tabel, tanpa link, tanpa sub-heading.

- [ ] **Step 2: Terjemahkan dan tulis 4 section**

Append empat section `##` berurutan, judul persis:

1. `## Hindari controller yang terlalu gemuk (fat controller)`
2. `## Logika bisnis di kelas Repository`
3. `## Prinsip tanggung jawab tunggal (SRP)`
4. `## Jangan ulangi diri sendiri (DRY)`

Label `Buruk:`/`Baik:`, kode verbatim.

- [ ] **Step 3: Verifikasi**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"fences=" + ([regex]::Matches($c, '(?m)^```')).Count
"links=" + ([regex]::Matches($c, '\]\(')).Count
"sections=" + ([regex]::Matches($c, '(?m)^## ')).Count
```
Expected: `fences=42`, `links=4`, `sections=8`.

---

### Task 4: Section 9–12 (Eloquent s/d JS & CSS di Blade)

**Files:**
- Modify: `CODING_STANDARDS.md`
- Read only: sumber `prefer-eloquent-and-collections.md`, `mass-assignment.md`, `avoid-queries-blade-template.md`, `avoid-js-css-blade-template.md`

**Interfaces:**
- Consumes: isi Task 3.
- Produces: 4 section `##` baru (judul persis di Step 2 task ini).

- [ ] **Step 1: Baca keempat file sumber**

Hitung blok kode: `prefer-eloquent` 4 (punya 2 sub-heading `##`), `mass-assignment` 2, `avoid-queries` 2 (punya 1 sub-heading `###`), `avoid-js-css` 3 (php, php, javascript).

- [ ] **Step 2: Terjemahkan dan tulis 4 section**

Append empat section `##` berurutan, judul persis:

1. `## Utamakan Eloquent dan Laravel Collections`
2. `## Mass assignment`
3. `## Hindari query di template Blade`
4. `## Jangan letakkan JS dan CSS di Blade`

Sub-heading sumber menjadi `###`. Kode (termasuk blok javascript) verbatim.

- [ ] **Step 3: Verifikasi**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"fences=" + ([regex]::Matches($c, '(?m)^```')).Count
"sections=" + ([regex]::Matches($c, '(?m)^## ')).Count
```
Expected: `fences=64` (22 blok baru × 2), `sections=12`.

---

### Task 5: Section 13–17 (Tipe PHP s/d .env)

**Files:**
- Modify: `CODING_STANDARDS.md`
- Read only: sumber `php-type-declaration.md`, `ioc-container.md`, `use-constants-language-helper.md`, `config-helper.md`, `avoid-env-variable-directly.md`

**Interfaces:**
- Consumes: isi Task 4.
- Produces: 5 section `##` baru (judul persis di Step 2 task ini).

- [ ] **Step 1: Baca kelima file sumber**

Hitung blok kode: `php-type-declaration` 4, `ioc-container` 2, `use-constants-language-helper` 2, `config-helper` 2 (punya 1 sub-heading `###`), `avoid-env-variable-directly` 2 (punya front matter + judul sumber `##` yang harus dinormalisasi jadi `##` di output).

- [ ] **Step 2: Terjemahkan dan tulis 5 section**

Append lima section `##` berurutan, judul persis:

1. `## Gunakan deklarasi tipe PHP`
2. `## Gunakan IoC container untuk project jangka panjang`
3. `## Gunakan konstanta dan language helper`
4. `## Gunakan helper config`
5. `## Jangan ambil data dari file .env secara langsung`

Urutan persis daftar; jangan lupa front matter `avoid-env-variable-directly.md` dihapus.

- [ ] **Step 3: Verifikasi**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"fences=" + ([regex]::Matches($c, '(?m)^```')).Count
"sections=" + ([regex]::Matches($c, '(?m)^## ')).Count
"frontmatter=" + ([regex]::Matches($c, '(?m)^---$')).Count
```
Expected: `fences=88` (24 blok baru × 2), `sections=17`, `frontmatter=0`.

---

### Task 6: Section 18–21 (Validasi s/d Praktik baik lainnya)

**Files:**
- Modify: `CODING_STANDARDS.md`
- Read only: sumber `validation.md`, `use-accessors-mutators.md`, `use-standard-laravel-tools.md`, `other-good-practices.md`

**Interfaces:**
- Consumes: isi Task 5.
- Produces: 4 section `##` baru di bagian akhir file (judul persis di Step 2 task ini).

- [ ] **Step 1: Baca keempat file sumber**

Hitung: `validation` 2 blok + 1 sub-heading `###`; `use-accessors-mutators` 2 blok; `use-standard-laravel-tools` front matter + 1 tabel (19 baris data), 0 blok kode; `other-good-practices` 0 blok kode + 1 link (Pint).

- [ ] **Step 2: Terjemahkan dan tulis 4 section**

Append empat section `##` berurutan (bagian akhir file), judul persis:

1. `## Gunakan Request class untuk validasi`
2. `## Gunakan Accessors dan Mutators`
3. `## Gunakan tool Laravel standar yang diakui komunitas`
4. `## Praktik baik lainnya`

Sel tabel `use-standard-laravel-tools` berupa kalimat diterjemahkan, nama tool (Laravel Mix, Homestead, PHPUnit, dst.) tetap.

- [ ] **Step 3: Verifikasi**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"fences=" + ([regex]::Matches($c, '(?m)^```')).Count
"links=" + ([regex]::Matches($c, '\]\(')).Count
"tables=" + ([regex]::Matches($c, '(?m)^\|')).Count
"sections=" + ([regex]::Matches($c, '(?m)^## ')).Count
"frontmatter=" + ([regex]::Matches($c, '(?m)^---$')).Count
```
Expected: `fences=96` (48 blok total sumber × 2), `links=5` (4 + 1 Pint), `tables=65` (44 + 21 baris tabel standard-tools), `sections=21`, `frontmatter=0`.

---

### Task 7: Verifikasi kelengkapan menyeluruh

**Files:**
- Modify: `CODING_STANDARDS.md` (hanya baca + perbaikan bila ada selisih)
- Read only: seluruh 21 file sumber

**Interfaces:**
- Consumes: hasil Task 1–6.
- Produces: `CODING_STANDARDS.md` final yang lolos seluruh pemeriksaan.

- [ ] **Step 1: Cocokkan urutan dan judul section**

Run:
```powershell
Select-String -Path CODING_STANDARDS.md -Pattern '(?m)^## ' | ForEach-Object { $_.Line }
```
Expected: tepat 21 baris, urutan dan teks judulnya identik dengan daftar "Urutan 21 section" (nomor 1–21).

- [ ] **Step 2: Pemeriksaan sisaan artefak sumber**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"badgood=" + ([regex]::Matches($c, '(?m)^(Bad|Good):')).Count
"frontmatter=" + ([regex]::Matches($c, '(?m)^---$')).Count
"psr2=" + ([regex]::Matches($c, 'php-fig\.org/psr/psr-2')).Count
```
Expected: `badgood=0`, `frontmatter=0`, `psr2=1` (link PSR sumber tetap ada).

- [ ] **Step 3: Baca ulang seluruh file dan bandingkan per section dengan sumber**

Baca `CODING_STANDARDS.md` utuh, lalu untuk tiap section bandingkan dengan file sumbernya: setiap paragraf sumber punya padanan terjemahan, tidak ada blok kode yang hilang/berubah, tabel lengkap, sub-heading `###` di tempat yang benar. Perbaiki selisih yang ditemukan.

- [ ] **Step 4: Verifikasi akhir**

Run:
```powershell
$c = Get-Content CODING_STANDARDS.md -Raw
"fences=" + ([regex]::Matches($c, '(?m)^```')).Count
"links=" + ([regex]::Matches($c, '\]\(')).Count
"tables=" + ([regex]::Matches($c, '(?m)^\|')).Count
"sections=" + ([regex]::Matches($c, '(?m)^## ')).Count
```
Expected: `fences=96`, `links=5`, `tables=65`, `sections=21`.
