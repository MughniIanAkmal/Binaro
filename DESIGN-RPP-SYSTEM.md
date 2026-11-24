# Spesifikasi Fitur Sistem Manajemen RPP — SDN Kalitapen 1

## 1. Gambaran Umum

Sistem manajemen RPP (disebut **Kegiatan Intrakurikuler** sesuai Kurikulum Merdeka) untuk **SDN Kalitapen 1** yang digunakan oleh seluruh guru dan dikelola melalui dua peran: **Admin** dan **Guru**.

- **Kurikulum**: Kurikulum Merdeka (resmi Kemendikbudristek)
- **Target pengguna**: Seluruh guru SDN Kalitapen 1 (semua mata pelajaran)
- **Tipe template**: Single fixed template (hanya untuk satu sekolah)
- **Nama dokumen resmi**: Kegiatan Intrakurikuler / Modul Ajar
- **Jenjang**: Kelas 1–6 (Fase A)
- **Semester**: Ganjil & Genap
- **Tahun ajaran**: 2026/2027 dan seterusnya

---

## 2. User Roles

| Role | Hak Akses |
|------|-----------|
| **Admin** | Kelola semua data: buat/edit/hapus akun guru, kelola master CP, setel identitas sekolah, review & approval RPP seluruh guru, export batch PDF, lihat dashboard statistik |
| **Guru** | Buat/edit draf RPP milik sendiri, submit untuk review, lihat riwayat & status RPP sendiri, export PDF RPP pribadi, kirim komentar kolaborasi |

> Catatan: Tidak ada role Koordinator atau Kepala Sekolah terpisah. Semua tugas persetujuan dilakukan oleh Admin.

---

## 3. Struktur Dokumen RPP (Komponen Wajib)

Sesuai ketentuan Rumah Pendidikan Kemendikbud, komponen Kegiatan Intrakurikuler adalah:

### 3.1 Informasi Umum
| Field | Tipe | Keterangan |
|-------|------|-----------|
| Nama Sekolah | text | SDN Kalitapen 1 (readonly, dari setting sekolah) |
| Nama Guru | text | Auto-fill dari akun guru yang login |
| NIP | text | Auto-fill dari akun |
| Mata Pelajaran | select | Matematika, B. Indonesia, IPA, IPS, PJOK, SBdP, PPKn, B. Inggris, PAI & BP, dll |
| Fase | select | Fase A (Kelas 1-3), Fase B (Kelas 4-6) |
| Kelas | select | 1, 2, 3, 4, 5, 6 |
| Semester | select | Ganjil, Genap |
| Tahun Ajaran | select | 2026/2027, 2027/2028, dst |
| Judul Tema/Subtema | text | Contoh: "Bangun Datar" |
| Alokasi Waktu | number x 35 menit | Contoh: 2 x 35 menit = 70 menit |
| Total Pertemuan | number | Berapa pertemuan dalam topik ini |

### 3.2 Capaian Pembelajaran (CP)
- Diambil otomatis dari dropdown berdasarkan mata pelajaran + fase yang dipilih
- Teks CP ditampilkan sebagai referensi (readonly)
- Guru merujuk ke CP saat merumuskan TP

### 3.3 Tujuan Pembelajaran (TP)
- Minimal 3 TP per pertemuan
- Setiap TP dirumuskan oleh guru berdasarkan CP
- Setiap TP wajib dilink ke minimal 1 dimensi Profil Pelajar Pancasila
- Profil Pelajar Pancasila tersedia dalam checkbox multi-select:
  - Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia
  - Berkebinekaan Global
  - Bergotong Royong
  - Mandiri
  - Bernalar Kritis
  - Kreatif

### 3.4 Pertanyaan Pemantik
- Textarea rich, minimal 1 pertanyaan
- Fungsi: memancing rasa ingin tahu siswa di awal pembelajaran

### 3.5 Langkah Pembelajaran
Terdiri dari 3 fase dengan durasi masing-masing:

**A. Pendahuluan (10–15 menit)**
- Salam & doa
- Presensi
- Apersepsi (mengaitkan materi lama & baru)
- Motivasi
- Penyampaian tujuan pembelajaran
- Isian bebas: uraian kegiatan pendahuluan

**B. Kegiatan Inti (60–70 menit)**
- Model Pembelajaran: [Inquiry / PBL / Kooperatif / Demonstrasi / Lainnya]
- Metode: [Diskusi Kelompok / Ceramah / Praktikum / Bermain Peran / Lainnya]
- Tahapan kegiatan berjenjang (array dinamis):
  - Nama tahap (bebas)
  - Uraian kegiatan (rich text)
  - Durasi (menit)
- [+ Tambah Tahap] — guru bisa menambah/mengurangi tahap

**C. Penutup (10–15 menit)**
- Refleksi bersama
- Kesimpulan materi
- Pemberian tugas rumah
- Doa penutup
- Isian bebas: uraian kegiatan penutup

### 3.6 Media / Sumber Belajar
| Tipe | Field |
|------|-------|
| Media Fisik | daftar teks (buku paket, kartu kata, alat peraga) |
| Media Digital | link URL, nama file, deskripsi |
| File Unggahan | max 5 MB per file (JPG, PNG, PDF) |

### 3.7 Rencana Asesmen
| Jenis | Instrumen | Detail |
|-------|-----------|--------|
| Diagnostik (Awal) | Tes lisan / tertulis / observasi | Pertanyaan atau instrumen |
| Formatif (Proses) | Observasi / lembar kerja / unjuk kerja / diskusi | Rubrik penilaian |
| Sumatif (Akhir) | Tes akhir / portofolio / produk | Soal atau kriteria penilaian |

### 3.8 Diferensiasi (Wajib disebutkan jika ada)
Tiga jenis diferensiasi (guru memilih yang relevan):
- **Diferensiasi Konten**: materi yang disesuaikan (teks mudah/sulit, video/audio)
- **Diferensiasi Proses**: cara mengajar yang berbeda (kelompok heterogen, tutor sebaya, scaffolding)
- **Diferensiasi Produk**: output yang berbeda (paper, poster, presentasi lisan, karya seni)

Setiap jenis diferensiasi dilengkapi uraian kegiatan.

---

## 4. Alur Kerja (Workflow)

```
┌─────────────────────────────────────────────────────────────────────┐
│                        ALUR KEGIATAN RPP                            │
└─────────────────────────────────────────────────────────────────────┘

  ┌──────────┐     Simpan      ┌──────────┐     Submit      ┌──────────────┐
  │  GURU    │ ─────────────▶ │  DRAF    │ ──────────────▶ │  MENUNGGU    │
  │  Membuat │                │          │                 │  ADMIN       │
  │  RPP Baru│                │          │                 │  REVIEW      │
  └──────────┘                └────┬─────┘                 └──────┬───────┘
       ▲                           │                              │
       │                           ▼                              ▼
       │                  ┌──────────────┐               ┌──────────────┐
       │                  │  DIPERBAIKI  │◀──────────────│  PERLU       │
       │                  │  (oleh Guru) │   Admin tolak  │  REVISI     │
       │                  └──────────────┘               │  DARI ADMIN │
       │                                                  └──────────────┘
       │
       │                  ┌──────────────┐               ┌──────────────┐
       │                  │  DITOLAK     │◀──────────────│  DITOLAK     │
       │                  │  OLEH ADMIN  │   Admin tolak  │  (catatan)  │
       │                  └──────────────┘               └──────────────┘
       │
       │                  ┌──────────────┐
       └─────────────────▶│  VALID       │   Admin setujui
                          │  (Disetujui) │
                          └──────────────┘
                                │
                                ▼
                     ┌──────────────────┐
                     │  Export PDF Resmi │
                     │  (+ Tanda Tangan) │
                     └──────────────────┘
```

### Status RPP

| Status | Kode | Keterangan |
|--------|------|-----------|
| Draf | `draft` | Tersimpan tapi belum dikirim |
| Menunggu Review | `pending_review` | Sudah di-submit, menunggu Admin |
| Perlu Revisi | `revision_required` | Dikembalikan Admin dengan catatan |
| Valid / Disetujui | `valid` | Diterima Admin, siap cetak PDF |
| Ditolak | `rejected` | Ditolak permanen (opsional) |

---

## 5. Skema Database

### 5.1 Tabel `users`

```sql
CREATE TABLE users (
    id              SERIAL PRIMARY KEY,
    nip             VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap    VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'guru')),
    mata_pelajaran  VARCHAR(100),
    kelas_diampu    VARCHAR(20)[],
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.2 Tabel `schools`

```sql
CREATE TABLE schools (
    id          SERIAL PRIMARY KEY,
    nama_sekolah VARCHAR(150) NOT NULL DEFAULT 'SDN Kalitapen 1',
    alamat      TEXT,
    kecamatan   VARCHAR(100),
    kabupaten   VARCHAR(100),
    npsn        VARCHAR(20) UNIQUE,
    logo_url    VARCHAR(500),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.3 Tabel `cp_master` (Capaian Pembelajaran)

```sql
CREATE TABLE cp_master (
    id               SERIAL PRIMARY KEY,
    mata_pelajaran   VARCHAR(100) NOT NULL,
    fase             VARCHAR(20) NOT NULL CHECK (fase IN ('A','B','C','D','E','F')),
    elemen           VARCHAR(100),
    cp_text          TEXT NOT NULL,
    urutan           INTEGER DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.4 Tabel `rpp_headers` (Header RPP)

```sql
CREATE TABLE rpp_headers (
    id                    SERIAL PRIMARY KEY,
    nomor_rpp             VARCHAR(30) UNIQUE NOT NULL,
    guru_id               INTEGER NOT NULL REFERENCES users(id),
    judul_tema            VARCHAR(200) NOT NULL,
    mata_pelajaran        VARCHAR(100) NOT NULL,
    fase                  VARCHAR(20) NOT NULL,
    kelas                 INTEGER NOT NULL CHECK (kelas BETWEEN 1 AND 6),
    semester              VARCHAR(10) NOT NULL CHECK (semester IN ('ganjil','genap')),
    tahun_ajaran          VARCHAR(20) NOT NULL,
    alokasi_waktu_menit   INTEGER NOT NULL,
    total_pertemuan       INTEGER NOT NULL DEFAULT 1,
    status                VARCHAR(30) NOT NULL DEFAULT 'draft'
        CHECK (status IN ('draft','pending_review','revision_required','valid','rejected')),
    versi_terkini         INTEGER DEFAULT 1,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.5 Tabel `rpp_sessions` (Per Pertemuan)

```sql
CREATE TABLE rpp_sessions (
    id                          SERIAL PRIMARY KEY,
    rpp_id                      INTEGER NOT NULL REFERENCES rpp_headers(id) ON DELETE CASCADE,
    nomor_pertemuan             INTEGER NOT NULL,
    durasi_pendahuluan_menit    INTEGER DEFAULT 10,
    durasi_inti_menit           INTEGER DEFAULT 65,
    durasi_penutup_menit        INTEGER DEFAULT 10,

    cp_selected_text            TEXT,
    cp_element                  VARCHAR(100),

    tp_list                     JSONB NOT NULL DEFAULT '[]',
    pertanyaan_pemantik         JSONB NOT NULL DEFAULT '[]',

    model_pembelajaran          VARCHAR(100),
    metode_pembelajaran         JSONB,
    langkah_pendahuluan         JSONB,
    langkah_inti                JSONB,
    langkah_penutup             JSONB,

    media_fisik                 JSONB DEFAULT '[]',
    media_digital               JSONB DEFAULT '[]',
    file_uploads                JSONB DEFAULT '[]',

    asesmen_diagnostik          JSONB DEFAULT '{}',
    asesmen_formatif            JSONB DEFAULT '{}',
    asesmen_sumatif             JSONB DEFAULT '{}',

    diferensiasi_jenis          VARCHAR(50)[],
    diferensiasi_konten         JSONB DEFAULT '{}',
    diferensiasi_proses         JSONB DEFAULT '{}',
    diferensiasi_produk         JSONB DEFAULT '{}',

    catatan_guru                TEXT,
    created_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.6 Tabel `approval_logs`

```sql
CREATE TABLE approval_logs (
    id              SERIAL PRIMARY KEY,
    rpp_id          INTEGER NOT NULL REFERENCES rpp_headers(id) ON DELETE CASCADE,
    action          VARCHAR(30) NOT NULL,
    -- 'submit', 'approve', 'reject', 'request_revision'
    reviewer_id     INTEGER NOT NULL REFERENCES users(id),
    catatan         TEXT,
    old_status      VARCHAR(30),
    new_status      VARCHAR(30),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.7 Tabel `version_history`

```sql
CREATE TABLE version_history (
    id              SERIAL PRIMARY KEY,
    session_id      INTEGER NOT NULL REFERENCES rpp_sessions(id) ON DELETE CASCADE,
    versi           INTEGER NOT NULL,
    snapshot        JSONB NOT NULL,
    perubahan_apa   TEXT,
    dibuat_oleh     INTEGER NOT NULL REFERENCES users(id),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.8 Tabel `collaboration_comments`

```sql
CREATE TABLE collaboration_comments (
    id          SERIAL PRIMARY KEY,
    rpp_id      INTEGER NOT NULL REFERENCES rpp_headers(id) ON DELETE CASCADE,
    session_id  INTEGER REFERENCES rpp_sessions(id) ON DELETE CASCADE,
    sender_id   INTEGER NOT NULL REFERENCES users(id),
    receiver_id INTEGER NOT NULL REFERENCES users(id),
    message     TEXT NOT NULL,
    is_private  BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 6. API Endpoints (RESTful)

### 6.1 Autentikasi

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| POST | `/api/auth/login` | all | Login NIP + password, return JWT |
| POST | `/api/auth/change-password` | all | Ubah password |
| GET | `/api/auth/me` | all | Ambil profil pengguna aktif |

### 6.2 Manajemen Pengguna (Admin Only)

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| GET | `/api/users` | admin | Daftar semua pengguna |
| POST | `/api/users` | admin | Tambah guru baru |
| PUT | `/api/users/:id` | admin | Update data guru |
| DELETE | `/api/users/:id` | admin | Nonaktifkan guru |

### 6.3 Data Sekolah (Admin Only)

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| GET | `/api/school` | admin | Ambil data sekolah |
| PUT | `/api/school` | admin | Update data sekolah |
| GET | `/api/cp` | all | List semua CP |
| GET | `/api/cp/:mapel/:fase` | all | CP per mapel + fase |
| POST | `/api/cp` | admin | Input CP baru |
| PUT | `/api/cp/:id` | admin | Edit CP |

### 6.4 CRUD RPP

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| GET | `/api/rpp` | all | List RPP (guru: miliknya saja, admin: semua) |
| GET | `/api/rpp/:id` | all | Detail RPP lengkap + semua sesi |
| POST | `/api/rpp` | guru | Buat header RPP baru |
| PUT | `/api/rpp/:id` | guru | Update info umum RPP |
| DELETE | `/api/rpp/:id` | guru | Hapus RPP (hanya status draft) |

### 6.5 Session (Pertemuan)

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| POST | `/api/rpp/:id/session` | guru | Buat sesi pertemuan baru |
| PUT | `/api/rpp/:id/session/:sid` | guru | Simpan/draft sesi |
| DELETE | `/api/rpp/:id/session/:sid` | guru | Hapus sesi (hanya draf) |

### 6.6 Approval Workflow

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| POST | `/api/rpp/:id/submit` | guru | Submit RPP untuk review |
| POST | `/api/rpp/:id/approve` | admin | Setujui RPP |
| POST | `/api/rpp/:id/reject` | admin | Tolak RPP + alasan |
| POST | `/api/rpp/:id/request-revision` | admin | Kembalikan untuk revisi |
| GET | `/api/rpp/:id/history` | all | Riwayat aktivitas & approval |

### 6.7 Kolaborasi

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| POST | `/api/rpp/:id/comments` | all | Kirim komentar |
| GET | `/api/rpp/:id/comments` | all | List komentar |

### 6.8 Dashboard & Export

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| GET | `/api/dashboard/stats` | admin | Statistik RPP (per bulan, per mapel, per status) |
| GET | `/api/export/pdf/:id` | guru, admin | Download PDF RPP individual |
| GET | `/api/export/batch-pdf` | admin | Batch export semua RPP valid |
| GET | `/api/export/csv` | admin | Export data RPP ke CSV |

### 6.9 Filter & Search

| Method | Endpoint | Role | Deskripsi |
|--------|----------|------|-----------|
| GET | `/api/rpp?filter=...` | all | Pencarian + filter kombinasi |
| GET | `/api/rpp?mapel=&kelas=&semester=&tahun=&status=` | all | Parameter query support |

---

## 7. UI/UX Wireframe

### 7.1 Landing Page (Login)

```
┌─────────────────────────────────────────────┐
│                                             │
│         [LOGO SDN KALITAPEN 1]              │
│                                             │
│      Sistem Manajemen RPP                   │
│         Kegiatan Intrakurikuler             │
│                                             │
│   ┌─────────────────────────────────┐      │
│   │  NIP                            │      │
│   │  [___________________________]  │      │
│   │                                 │      │
│   │  Kata Sandi                     │      │
│   │  [___________________________]  │      │
│   │                                 │      │
│   │  [ Lupa Password? ]             │      │
│   │                                 │      │
│   │      [ MASUK ]                  │      │
│   └─────────────────────────────────┘      │
│                                             │
│        © SDN Kalitapen 1 — 2026             │
└─────────────────────────────────────────────┘
```

### 7.2 Dashboard Guru

```
┌──────────────────────────────────────────────────────────────┐
│  [LOGO] SDN Kalitapen 1    RPP Management      [Nama▼] [Logout] │
├──────────────┬───────────────────────────────────────────────┤
│ 📊 Dashboard │                                               │
│ ➕ Buat RPP  │  Selamat datang, Ibu Sari!                    │
│ 📋 RPP Saya  │                                               │
│ 💬 Komentar  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌───────┐│
│ ⚙️ Pengaturan│  │✅ Valid │ │⏳ Pending│ │✏️ Draf  │ │❌ Tolak││
│              │  │   8     │ │   3     │ │   4     │ │   1   ││
│              │  └─────────┘ └─────────┘ └─────────┘ └───────┘│
│              │                                               │
│              │  📅 RPP Terbaru                               │
│              │  ──────────────────────────────────           │
│              │  • Matematika · Fase A · Kelas 4 · Pert. 3    │
│              │    Status: ✅ Valid    2 jam lalu             │
│              │  • B. Indo · Fase A · Kelas 2 · Pert. 1      │
│              │    Status: ⏳ Pending    1 hari lalu          │
│              │  • IPA · Fase A · Kelas 5 · Pert. 2          │
│              │    Status: ✏️ Draf    3 hari lalu            │
│              │                                               │
│              │  [+ Buat RPP Baru]                            │
└──────────────┴───────────────────────────────────────────────┘
```

### 7.3 Wizard Pembuatan RPP (Step-by-Step)

```
Step Indicator: ① Info Umum → ② CP & TP → ③ Pemantik → ④ Langkah → ⑤ Media → ⑥ Asesmen → ⑦ Diferensiasi → ⑧ Review & Submit

═══════════════════════════════════════════════════════════════
  STEP 1 OF 8 — INFORMASI UMUM
═══════════════════════════════════════════════════════════════

  Nama Sekolah    : SDN Kalitapen 1  [📝 Edit]
  Nama Guru       : Sari Aminah, S.Pd.  (auto)
  NIP             : 199001012015032001  (auto)

  Mata Pelajaran  : [Matematika ▼]
  Fase / Kelas    : [Fase A] / [Kelas 4 ▼]
  Semester        : [Ganjil ▼]
  Tahun Ajaran    : [2026/2027 ▼]
  Judul Tema      : [Bangun Datar ................................]
  Alokasi Waktu   : [2] x 35 menit
  Total Pertemuan : [4] pertemuan

  [← Sebelumnya]                    [Selanjutnya →]

═══════════════════════════════════════════════════════════════
  STEP 2 OF 8 — CAPAIAN PEMBELAJARAN & TUJUAN
═══════════════════════════════════════════════════════════════

  ☑ Tampilkan Capaian Pembelajaran

  ┌─────────────────────────────────────────┐
  │  CP — Matematika, Fase A, Kelas 4       │
  │                                         │
  │  Murid menunjukkan pemahaman tentang    │
  │  bilangan bulat dan operasi hitungnya.  │
  │  Murid mampu melakukan operasi hitung   │
  │  bilangan bulat dalam kehidupan sehari-  │
  │  hari secara tepat dan akurat.          │
  └─────────────────────────────────────────┘

  Tujuan Pembelajaran (minimal 3):

  TP 1:
  [Murid mampu menyebutkan sifat-sifat bangun datar ..............]
  Profil Pelajar Pancasila:
  [☑ Bernalar Kritis] [☑ Kreatif] [☐ Gotong Royong]

  TP 2:
  [Murid mampu menghitung luas bangun datar sederhana .........]
  Profil Pelajar Pancasila:
  [☑ Mandiri] [☑ Bernalar Kritis]

  TP 3:
  [Murid mampu menyelesaikan soal cerita bangun datar .........]
  Profil Pelajar Pancasila:
  [☑ Bergotong Royong]

  [+ Tambah Tujuan Pembelajaran]

  [← Sebelumnya]                    [Selanjutnya →]
```

### 7.4 Step Langkah Pembelajaran

```
═══════════════════════════════════════════════════════════════
  STEP 4 OF 8 — LANGKAH PEMBELAJARAN
  Pertemuan ke- 1 dari 4
═══════════════════════════════════════════════════════════════

  ── A. PENDAHULUAN (12 menit) ──────────────────────────────
  ☐ Salam & doa     ☐ Presensi     ☐ Apersepsi
  ☐ Motivasi        ☐ Tujuan ajar

  Uraian kegiatan:
  ┌─────────────────────────────────────────────┐
  │  Guru membuka pelajaran dengan salam dan    │
  │  memimpin doa. kemudian guru mengecek       │
  │  kehadiran siswa dan menggaitkan materi     │
  │  sebelumnya dengan tema hari ini.           │
  └─────────────────────────────────────────────┘

  ── B. KEGIATAN INTI (63 menit) ─────────────────────────────
  Model Pembelajaran: [Inquiry ▼]
  Metode: [Diskusi Kelompok] [Demonstrasi]

  Tahapan:

  Tahap 1: Stimulasi
  Durasi: [15] menit
  Uraian:
  ┌─────────────────────────────────────────────┐
  │  Guru menampilkan berbagai bangun datar    │
  │  beserta sifat-sifatnya menggunakan slide │
  │  PowerPoint dan video animasi. Siswa       │
  │  mengamati dan mencatat perbedaan tiap     │
  │  bangun datar.                             │
  └─────────────────────────────────────────────┘

  Tahap 2: Identifikasi Masalah
  Durasi: [10] menit
  Uraian: [...]

  [+ Tambah Tahap]

  ── C. PENUTUP (10 menit) ────────────────────────────────────
  ☐ Refleksi     ☐ Kesimpulan     ☐ Tugas rumah     ☐ Doa

  Uraian kegiatan:
  ┌─────────────────────────────────────────────┐
  │                                             │
  └─────────────────────────────────────────────┘

  [← Sebelumnya]                    [Selanjutnya →]
```

### 7.5 Step Asesmen & Diferensiasi

```
═══════════════════════════════════════════════════════════════
  STEP 6 OF 8 — RENCANA ASESMEN
═══════════════════════════════════════════════════════════════

  DIAGNOSTIK (Awal)
  ☐ Tes Lisan    ☐ Tes Tertulis    ☐ Observasi
  ┌─────────────────────────────────────────────┐
  │  Pertanyaan:                                │
  │  1. Apakah kamu ingat bentuk-bentuk        │
  │     bangun datar yang sudah dipelajari?    │
  │  2. Coba sebutkan sifat persegi!           │
  └─────────────────────────────────────────────┘

  FORMATIF (Proses)
  ☐ Observasi     ☐ Lembar Kerja     ☐ Unjuk Kerja     ☐ Diskusi
  Rubrik Penilaian:
  ┌─────────────────────────────────────────────┐
  │  Skor 4: Mampu menjelaskan 4 sifat         │
  │  Skor 3: Mampu menjelaskan 3 sifat         │
  │  Skor 2: Mampu menjelaskan 2 sifat         │
  │  Skor 1: Belum mampu menjelaskan           │
  └─────────────────────────────────────────────┘

  SUMATIF (Akhir)
  ☐ Tes Tertulis     ☐ Portofolio     ☐ Produk
  ┌─────────────────────────────────────────────┐
  │  Contoh soal:                               │
  │  1. Hitunglah luas persegi dengan sisi     │
  │     12 cm!                                  │
  │  2. Sebuah taman berbentuk persegipanjang   │
  │     berukuran 15m x 8m. Berapa luasnya?    │
  └─────────────────────────────────────────────┘

═══════════════════════════════════════════════════════════════
  STEP 7 OF 8 — DIFERENSIASI
═══════════════════════════════════════════════════════════════

  Pilih jenis diferensiasi yang diterapkan:

  ○ Diferensiasi Konten
    [Siswa yang cepat paham diberikan soal tantangan        ]
    [Siswa yang butuh bimbingan diberikan modul ringkas     ]
    [_____________________________________________________]

  ● Diferensiasi Proses
    [Kelompok heterogen: siswa pintar mendampingi teman     ]
    [Tutor sebaya untuk siswa yang belum memahami konsep    ]
    [_____________________________________________________]

  ○ Diferensiasi Produk
    [Output bervariasi: paper, poster, atau presentasi      ]
    [_____________________________________________________]

  [← Sebelumnya]                    [Selesai & Preview]
```

### 7.6 Halaman Review Admin

```
┌──────────────────────────────────────────────────────────────┐
│  [LOGO] SDN Kalitapen 1    RPP Management        [Admin▼] [Logout] │
├──────────────┬───────────────────────────────────────────────┤
│ 📊 Dashboard │                                               │
│ ➕ Buat RPP  │  Review RPP — Menunggu Persetujuan            │
│ 📋 RPP Saya  │                                               │
│ 🔍 Review    │  Filter: [Semua Mapel▼] [Semua Kelas▼] [Search...]│
│ 💬 Komentar  │                                               │
│ ⚙️ Setelan   │  ──────────────────────────────────────────   │
│              │  No │ Guru      │ Mapel │ Kelas │ Tgl   │ Stat │
│              │  1  │ Siti A.   │ Mat   │  4    │ 20Nov │ ⏳   │
│              │  2  │ Budi S.   │ IPA   │  5    │ 19Nov │ ⏳   │
│              │  3  │ Diani R.  │ Indo  │  3    │ 18Nov │ ⏳   │
│              │  4  │ Andi W.   │ PJOK  │  6    │ 17Nov │ ⏳   │
│              │                                               │
│              │  [+ Export Semua]  [Review Semua]            │
└──────────────┴───────────────────────────────────────────────┘

Detail Review (Modal / Panel):

  RPP #RPP-2026-0042 — Bangun Datar
  Guru: Siti Aminah | Matematika | Kelas 4 | Ganjil

  [Info Umum] [CP&TP] [Langkah] [Media] [Asesmen] [Differ] [Riwayat]

  ── Catatan Admin ────────────────────────────────────────────
  ┌─────────────────────────────────────────────────────────┐
  │ TP 2 belum dilengkapi link Profil Pelajar Pancasila     │
  │ Langkah inti kurang detail pada tahap implementasi     │
  └─────────────────────────────────────────────────────────┘

  [✅ Approve]  [↩️ Request Revision]  [❌ Reject]
```

### 7.7 Detail RPP (Read-Only untuk Guru)

```
┌──────────────────────────────────────────────────────────────┐
│  RPP #RPP-2026-0042                         Status: ✅ Valid │
├──────────────────────────────────────────────────────────────┤
│  Tab: [Info Umum] [CP&TP] [Langkah] [Media] [Asesmen] [Diff] │
│                                                                       │
│  🖨 Cetak PDF  ⬇ Download  💬 Komentar  ↩️ Edit (jika draf)       │
│                                                                       │
│  ══ A. INFORMASI UMUM ══                                       │
│  MadPel: Matematika  |  Fase: A  |  Kelas: 4                     │
│  Sem: Ganjil  |  TA: 2026/2027                                   │
│  Pertemuan: 1 dari 4  |  Alokasi: 2x35 menit                      │
│                                                                       │
│  ══ B. CAPAIAN PEMBELAJARAN ══                                   │
│  [accordion: CP Matematika Fase A Kelas 4]                       │
│                                                                       │
│  ══ C. TUJUAN PEMBELAJARAN ══                                     │
│  TP1: Murid mampu menyebutkan sifat-sifat bangun datar ✓ PP: BK   │
│  TP2: Murid mampu menghitung luas bangun datar        ✓ PP: MK    │
│  TP3: Murid mampu menyelesaikan soal cerita            ✓ PP: GR   │
│                                                                       │
│  ══ D. LANGKAH PEMBELAJARAN ══                                      │
│  [accordion: Pendahuluan (12 mnt)]                                   │
│  [accordion: Kegiatan Inti (63 mnt)]                                 │
│  [accordion: Penutup (10 mnt)]                                       │
│                                                                       │
│  ══ E. MEDIA & SUMBER BELAJAR ══                                    │
│  Fisik: Buku Paket Matematika Kelas 4, Kartu Bangun Datar            │
│  Digital: Video YouTube "Mengenal Bangun Datar"                      │
│                                                                       │
│  ══ F. RENCANA ASESMEN ══                                           │
│  [accordion: Diagnostik] [Formatif] [Sumatif]                        │
│                                                                       │
│  ══ G. DIFERENSIASI ══                                              │
│  Diferensiasi Proses: Tutor sebaya, kelompok heterogen               │
│                                                                       │
│  ══ RIWAYAT AKTIVITAS ══                                            │
│  · 20 Nov 2026 14:00 — Dibuat oleh Siti Aminah (Draft)               │
│  · 21 Nov 2026 08:30 — Submitted untuk review                        │
│  · 21 Nov 2026 10:00 — Disetujui Admin (Valid)                       │
└──────────────────────────────────────────────────────────────┘
```

---

## 8. Spesifikasi Ekspor PDF

### 8.1 Struktur Dokumen

```
┌────────────────────────────────────────────┐
│       [LOGO SEKOLAH]  SDN KALITAPEN 1      │
│            Jalan Contoh No. 1              │
│      Kecamatan Jelbuk, Kabupaten Jember   │
├────────────────────────────────────────────┤
│                                            │
│      KEGIATAN INTRAKURIKULER               │
│      (Rencana Pelaksanaan Pembelajaran)    │
│                                            │
│   Mata Pelajaran  : Matematika             │
│   Fase/Kelas      : A / 4                  │
│   Semester        : Ganjil                 │
│   Tahun Ajaran    : 2026/2027              │
│   Pertemuan ke-   : 1 dari 4               │
│   Alokasi Waktu   : 2 x 35 menit           │
│                                            │
├────────────────────────────────────────────┤
│   A. INFORMASI UMUM                        │
│      ...                                 │
│                                            │
│   B. CAPAIAN PEMBELAJARAN (CP)             │
│      ...                                 │
│                                            │
│   C. TUJUAN PEMBELAJARAN (TP)              │
│      TP 1: ...  (PP: Bernalar Kritis)      │
│      TP 2: ...  (PP: Mandiri)              │
│      TP 3: ...  (PP: Gotong Royong)        │
│                                            │
│   D. PROFIL PELAJAR PANCASILA              │
│      (Ringkasan link TP → Dimensi PP)      │
│                                            │
│   E. PERTANYAAN PEMANTIK                   │
│      1. ...                               │
│      2. ...                               │
│                                            │
│   F. LANGKAH PEMBELAJARAN                  │
│      A. Pendahuluan (12 menit)             │
│         ...                               │
│      B. Kegiatan Inti (63 menit)           │
│         Tahap 1: Stimulasi (15 menit)      │
│         Tahap 2: ... (10 menit)            │
│      C. Penutup (10 menit)                 │
│         ...                               │
│                                            │
│   G. MEDIA & SUMBER BELAJAR                │
│      ...                                 │
│                                            │
│   H. RENCANA ASESMEN                       │
│      1. Diagnostik: ...                    │
│      2. Formatif: ...                      │
│      3. Sumatif: ...                       │
│                                            │
│   I. DIFERENSIASI                          │
│      ...                                 │
│                                            │
│                                             │
│   Kalitapen, 20 November 2026              │
│                                             │
│   ________________________                 │
│   (Siti Aminah, S.Pd.)                     │
│   NIP. ..................................  │
│                                             │
│                                             │
│   Mengetahui,                              │
│   Kepala SDN Kalitapen 1                   │
│                                             │
│   ________________________                 │
│   (Dr. Contoh, M.Pd.)                      │
│   NIP. ..................................  │
│                                             │
└────────────────────────────────────────────┘
```

### 8.2 Spesifikasi Teknis PDF
- Ukuran kertas: **A4** (210mm × 297mm)
- Font: Times New Roman ukuran 11pt
- Margin: 2.5 cm (kiri, atas, kanan), 3 cm (bawah)
- Kop surat: Logo sekolah + nama + alamat
- Header per halaman: Nomor RPP + Judul Tema
- Penanda tangan: placeholder untuk tanda tangan basah
- Nomor halaman: pojok kanan bawah
- Batch export: beberapa RPP menjadi satu file PDF

---

## 9. Keputus an Teknis

### 9.1 Stack Teknologi

| Layer | Pilihan | Alasan |
|-------|---------|--------|
| Backend | **Laravel (PHP)** | Populer di Indonesia, komunitas besar, migration & ORM kuat |
| Frontend | **Vue.js 3 + Tailwind CSS** | Ringan, reusable components, familiar developer lokal |
| Database | **PostgreSQL** | Support JSONB untuk struktur dinamis (TP, PP links, asesmen, dll) |
| PDF Engine | **Dompdf** atau **Snappy** (wkhtmltopdf) | Support tabel & layout kompleks, gratis |
| Storage | **Local filesystem** | Mudah dikelola di server sekolah |
| Auth | **Laravel Sanctum (JWT)** | Stateless, cocok SPA + REST API |

### 9.2 Keputus an Penting

1. **JSONB untuk field dinamis** — Struktur TP, asesmen, dan diferensiasi bersifat unik tiap guru,不适合 hardcode kolom database.

2. **Nomor RPP auto-generate** — Format: `RPP-{TAHUN}-{SEQ}`, contoh `RPP-2026-0042`. Unik & mudah dibaca.

3. **Version history menggunakan copy-on-write** — Setiap simpan draf membuat entri baru di `version_history`, bukan overwrite. Audit trail sempurna.

4. **Single approval flow (Admin)** — Sesuai permintaan, tidak ada role koordinator/kepsek terpisah. Admin menangani semua review & approval.

5. **RBAC berbasis middleware** — Middleware Laravel membedakan akses route berdasarkan role. Guru hanya mengakses data dirinya sendiri.

6. **Soft delete untuk RPP** — Gunakan `deleted_at` timestamp agar data tidak hilang permanen dan bisa direstore.

### 9.3 Keamanan
- Password di-hash menggunakan bcrypt (Laravel default)
- Semua endpoint dilindungi middleware `auth:sanctum`
- CSRF protection aktif
- Rate limiting login (max 5 percobaan/menit)
- File upload dibatasi: JPG/PNG/PDF, maks 5 MB
- Input sanitasi di semua form (XSS prevention)

---

## 10. Daftar Cakupan Pengembangan (Phased Approach)

### Phase 1 — Foundation
- Setup project Laravel + Vue
- Migrasi database (semua tabel di atas)
- Autentikasi (login, register via admin, JWT)
- RBAC middleware (admin vs guru)
- Seed master data (CP, sekolah, user admin)

### Phase 2 — Core RPP
- Wizard pembuatan RPP 8 step
- CRUD RPP & Session
- Autosave draft
- Numbering otomatis `RPP-{TAHUN}-{SEQ}`

### Phase 3 — Approval & Collaboration
- Submit RPP → change status
- Admin review panel (approve / reject / request revision)
- Approval logs table
- Comments & collaboration system

### Phase 4 — Dashboard & Export
- Dashboard statistik (admin & guru)
- PDF generation (individual + batch)
- CSV export untuk reporting
- Search & filter (maps, kelas, semester, tahun, status)

### Phase 5 — Polish
- Responsive design (mobile-friendly)
- Notification (email/jaringan internal saat RPP diapprove/ditolak)
- Backup & restore feature
- Documentation & training material
