-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 21, 2026 at 12:29 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_smster_331`
--

-- --------------------------------------------------------

--
-- Table structure for table `absen`
--

CREATE TABLE `absen` (
  `id_absen` bigint UNSIGNED NOT NULL,
  `id_guru` bigint UNSIGNED NOT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
  `id_barcode` bigint UNSIGNED DEFAULT NULL,
  `metode` enum('scan_qr','manual_guru') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scan_qr',
  `status` enum('Hadir','Izin','Sakit','Alpa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hadir',
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `berkas_surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_absen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absen`
--

INSERT INTO `absen` (`id_absen`, `id_guru`, `id_siswa`, `id_barcode`, `metode`, `status`, `keterangan`, `berkas_surat`, `waktu_absen`, `tanggal`) VALUES
(1, 1, 1, 1, 'scan_qr', 'Hadir', 'Tepat Waktu', NULL, '2026-09-20 08:31:08', '2026-09-20'),
(2, 1, 2, 2, 'scan_qr', 'Hadir', 'Tepat Waktu', NULL, '2026-09-20 08:31:08', '2026-09-20');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` bigint UNSIGNED NOT NULL,
  `nip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_admin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nip`, `nama_admin`, `password`, `created_at`, `updated_at`) VALUES
(1, '19850101001', 'Bpk. Ahmad Fauzi, S.Kom', '$2y$12$DtLgaBEuck0xajioE01k1OyfYeO6nd9nBhRa4nMSvHQ9y9tPnqdu6', '2026-09-20 08:31:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bab`
--

CREATE TABLE `bab` (
  `id_bab` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `nama_bab` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barcode`
--

CREATE TABLE `barcode` (
  `id_barcode` bigint UNSIGNED NOT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
  `kode_barcode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barcode`
--

INSERT INTO `barcode` (`id_barcode`, `id_siswa`, `kode_barcode`, `created_at`, `updated_at`) VALUES
(1, 1, 'QR-SISWA-001', '2026-09-20 08:31:08', NULL),
(2, 2, 'QR-SISWA-002', '2026-09-20 08:31:08', NULL),
(3, 3, 'QR-SISWA-003', '2026-09-20 08:31:08', NULL),
(4, 4, 'QR-SISWA-004', '2026-09-20 08:31:08', NULL),
(5, 5, 'QR-SISWA-005', '2026-09-20 08:31:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id_guru` bigint UNSIGNED NOT NULL,
  `nip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_guru` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id_guru`, `nip`, `nama_guru`, `no_hp`, `password`, `created_at`, `updated_at`) VALUES
(1, '19800101001', 'Siti Nurhaliza, S.Pd', '081234567891', '$2y$12$PTk4wIj/6OK2DRvbvLV5EuvLmNOZ2Re2nT4IMFvwwPYHn/vDty3T2', '2026-09-20 08:31:07', NULL),
(2, '19800101002', 'Budi Santoso, S.Pd', '081234567892', '$2y$12$KtXgUWp1cAkEwQiftaj4Nu601NgPytWggNzsTyBwW/8a7RmyFwDXu', '2026-09-20 08:31:07', NULL),
(3, '19800101003', 'Dewi Lestari, M.Pd', '081234567893', '$2y$12$OKlYQGWkc3r8.4CKaX1Q1ePVD5OLU4ZcXZCqRZcRTM4L5hYphZB8S', '2026-09-20 08:31:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hasil_nilai`
--

CREATE TABLE `hasil_nilai` (
  `id_hasil_nilai` bigint UNSIGNED NOT NULL,
  `id_nilai` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
  `data_nilai` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_nilai` decimal(5,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_mata_pelajaran`
--

CREATE TABLE `jadwal_mata_pelajaran` (
  `id_jadwal` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `id_guru` bigint UNSIGNED NOT NULL,
  `id_rooms` bigint UNSIGNED DEFAULT NULL,
  `hari` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_rooms` bigint UNSIGNED NOT NULL,
  `pararel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_rooms`, `pararel`) VALUES
(1, 'Kelas 1A'),
(2, 'Kelas 1B'),
(3, 'Kelas 2A'),
(4, 'Kelas 3A');

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id_mapel` bigint UNSIGNED NOT NULL,
  `nama_mapel` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`id_mapel`, `nama_mapel`, `deskripsi`) VALUES
(1, 'Matematika', NULL),
(2, 'Bahasa Indonesia', NULL),
(3, 'Ilmu Pengetahuan Alam (IPA)', NULL),
(4, 'Pendidikan Pancasila', NULL),
(5, 'Bahasa Inggris', NULL),
(6, 'Basis Data', 'Basis data atau data base');

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id_materi` bigint UNSIGNED NOT NULL,
  `id_sub_bab` bigint UNSIGNED NOT NULL,
  `id_bab` bigint UNSIGNED NOT NULL,
  `judul_materi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi_materi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_01_01_000001_create_academic_system_tables', 1),
(2, '2026_01_01_000002_add_password_to_guru', 1),
(3, '2026_01_01_000003_add_catatan_revisi_to_rpp', 1),
(4, '2026_01_01_000004_add_deskripsi_to_mata_pelajaran', 2);

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id_nilai` bigint UNSIGNED NOT NULL,
  `id_pr` bigint UNSIGNED DEFAULT NULL,
  `id_quiz` bigint UNSIGNED DEFAULT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `skor` decimal(5,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` bigint UNSIGNED NOT NULL,
  `id_guru` bigint UNSIGNED DEFAULT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
  `id_pr` bigint UNSIGNED DEFAULT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_baca` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr`
--

CREATE TABLE `pr` (
  `id_pr` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `id_guru` bigint UNSIGNED NOT NULL,
  `nama_pr` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tgl_tenggat` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id_quiz` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `id_guru` bigint UNSIGNED NOT NULL,
  `nama_quiz` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpp`
--

CREATE TABLE `rpp` (
  `id_rpp` bigint UNSIGNED NOT NULL,
  `id_guru` bigint UNSIGNED NOT NULL,
  `id_rooms` bigint UNSIGNED DEFAULT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `judul_rpp` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `komponen_checklist` json DEFAULT NULL,
  `status` enum('draft','menunggu_review','terverifikasi','perlu_revisi') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `catatan_revisi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_rpp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rpp`
--

INSERT INTO `rpp` (`id_rpp`, `id_guru`, `id_rooms`, `id_mapel`, `judul_rpp`, `deskripsi`, `komponen_checklist`, `status`, `catatan_revisi`, `file_rpp`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Modul Ajar Bilangan Cacah Sampai 100', 'Pengenalan nilai tempat dan penjumlahan sederhana fase A.', '{\"kktp\": true, \"lkpd\": true, \"video\": true, \"tujuan\": true}', 'terverifikasi', NULL, NULL, '2026-09-20 08:31:08', '2026-09-20 08:31:08'),
(2, 2, 1, 2, 'Membaca Nyaring & Menulis Cerita Bergambar', 'Materi literasi dasar untuk melatih kelancaran membaca siswa.', '{\"kktp\": false, \"lkpd\": true, \"video\": true, \"tujuan\": true}', 'menunggu_review', NULL, NULL, '2026-09-20 08:31:08', '2026-09-20 08:31:08'),
(3, 3, 3, 3, 'Ekosistem dan Rantai Makanan Dasar', 'Materi IPA kelas 2 tentang makhluk hidup dan lingkungannya.', '{\"kktp\": false, \"lkpd\": true, \"video\": true, \"tujuan\": true}', 'perlu_revisi', 'Mohon perbaikan KKTP dan penambahan video interaktif.', NULL, '2026-09-20 08:31:08', '2026-09-20 08:31:08');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED DEFAULT NULL,
  `id_rooms` bigint UNSIGNED DEFAULT NULL,
  `nisn` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nm_siswa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_mapel`, `id_rooms`, `nisn`, `nm_siswa`, `no_hp`, `password`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '0012345601', 'Aditya Pratama', '08111111111', '$2y$12$T0aYIjC7c/UU9LlGfd.IrehkL1jee3ifBMplNsfdmpiMtKX.vHZWW', '2026-09-20 08:31:07', NULL),
(2, 1, 1, '0012345602', 'Bella Safitri', '08111111112', '$2y$12$2u4GwxrHc2aGiPSX.6SQQ./YWEWIE8/lWNJmj7h6FBP6yODKOnxmm', '2026-09-20 08:31:08', NULL),
(3, 2, 1, '0012345603', 'Candra Wijaya', '08111111113', '$2y$12$bYLvx6rLyTNJ1IZaE7IupuHch./40YsS0hWxEWyWv9GBSyjQjZf66', '2026-09-20 08:31:08', NULL),
(4, 2, 2, '0012345604', 'Dina Mariana', '08111111114', '$2y$12$XDTo3in9iOQd4MVy7x0rFOzzSZNc0aGDyY78Zm6qH62DqHKfjtkc.', '2026-09-20 08:31:08', NULL),
(5, 3, 2, '0012345605', 'Eko Prasetyo', '08111111115', '$2y$12$ZHnL514lQ0cJZ0q71rfEvOBADEYoQfiwqpKgiTm9jbT78xEPgJJXK', '2026-09-20 08:31:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sub_bab`
--

CREATE TABLE `sub_bab` (
  `id_sub_bab` bigint UNSIGNED NOT NULL,
  `id_bab` bigint UNSIGNED NOT NULL,
  `nama_sub_bab` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absen`
--
ALTER TABLE `absen`
  ADD PRIMARY KEY (`id_absen`),
  ADD UNIQUE KEY `uq_siswa_tanggal` (`id_siswa`,`tanggal`),
  ADD KEY `absen_id_guru_foreign` (`id_guru`),
  ADD KEY `absen_id_barcode_foreign` (`id_barcode`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `admin_nip_unique` (`nip`);

--
-- Indexes for table `bab`
--
ALTER TABLE `bab`
  ADD PRIMARY KEY (`id_bab`),
  ADD KEY `bab_id_mapel_foreign` (`id_mapel`);

--
-- Indexes for table `barcode`
--
ALTER TABLE `barcode`
  ADD PRIMARY KEY (`id_barcode`),
  ADD UNIQUE KEY `barcode_kode_barcode_unique` (`kode_barcode`),
  ADD KEY `barcode_id_siswa_foreign` (`id_siswa`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`),
  ADD UNIQUE KEY `guru_nip_unique` (`nip`);

--
-- Indexes for table `hasil_nilai`
--
ALTER TABLE `hasil_nilai`
  ADD PRIMARY KEY (`id_hasil_nilai`),
  ADD KEY `hasil_nilai_id_nilai_foreign` (`id_nilai`),
  ADD KEY `hasil_nilai_id_mapel_foreign` (`id_mapel`),
  ADD KEY `hasil_nilai_id_siswa_foreign` (`id_siswa`);

--
-- Indexes for table `jadwal_mata_pelajaran`
--
ALTER TABLE `jadwal_mata_pelajaran`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `jadwal_mata_pelajaran_id_mapel_foreign` (`id_mapel`),
  ADD KEY `jadwal_mata_pelajaran_id_guru_foreign` (`id_guru`),
  ADD KEY `jadwal_mata_pelajaran_id_rooms_foreign` (`id_rooms`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_rooms`);

--
-- Indexes for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD PRIMARY KEY (`id_mapel`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id_materi`),
  ADD KEY `materi_id_sub_bab_foreign` (`id_sub_bab`),
  ADD KEY `materi_id_bab_foreign` (`id_bab`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
  ADD PRIMARY KEY (`id_nilai`),
  ADD KEY `nilai_id_pr_foreign` (`id_pr`),
  ADD KEY `nilai_id_quiz_foreign` (`id_quiz`),
  ADD KEY `nilai_id_mapel_foreign` (`id_mapel`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `notifikasi_id_guru_foreign` (`id_guru`),
  ADD KEY `notifikasi_id_siswa_foreign` (`id_siswa`),
  ADD KEY `notifikasi_id_pr_foreign` (`id_pr`);

--
-- Indexes for table `pr`
--
ALTER TABLE `pr`
  ADD PRIMARY KEY (`id_pr`),
  ADD KEY `pr_id_mapel_foreign` (`id_mapel`),
  ADD KEY `pr_id_guru_foreign` (`id_guru`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id_quiz`),
  ADD KEY `quiz_id_mapel_foreign` (`id_mapel`),
  ADD KEY `quiz_id_guru_foreign` (`id_guru`);

--
-- Indexes for table `rpp`
--
ALTER TABLE `rpp`
  ADD PRIMARY KEY (`id_rpp`),
  ADD KEY `rpp_id_guru_foreign` (`id_guru`),
  ADD KEY `rpp_id_rooms_foreign` (`id_rooms`),
  ADD KEY `rpp_id_mapel_foreign` (`id_mapel`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `siswa_nisn_unique` (`nisn`),
  ADD KEY `siswa_id_mapel_foreign` (`id_mapel`),
  ADD KEY `siswa_id_rooms_foreign` (`id_rooms`);

--
-- Indexes for table `sub_bab`
--
ALTER TABLE `sub_bab`
  ADD PRIMARY KEY (`id_sub_bab`),
  ADD KEY `sub_bab_id_bab_foreign` (`id_bab`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absen`
--
ALTER TABLE `absen`
  MODIFY `id_absen` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bab`
--
ALTER TABLE `bab`
  MODIFY `id_bab` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barcode`
--
ALTER TABLE `barcode`
  MODIFY `id_barcode` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hasil_nilai`
--
ALTER TABLE `hasil_nilai`
  MODIFY `id_hasil_nilai` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_mata_pelajaran`
--
ALTER TABLE `jadwal_mata_pelajaran`
  MODIFY `id_jadwal` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_rooms` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id_mapel` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id_materi` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id_nilai` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pr`
--
ALTER TABLE `pr`
  MODIFY `id_pr` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id_quiz` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpp`
--
ALTER TABLE `rpp`
  MODIFY `id_rpp` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sub_bab`
--
ALTER TABLE `sub_bab`
  MODIFY `id_sub_bab` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absen`
--
ALTER TABLE `absen`
  ADD CONSTRAINT `absen_id_barcode_foreign` FOREIGN KEY (`id_barcode`) REFERENCES `barcode` (`id_barcode`) ON DELETE SET NULL,
  ADD CONSTRAINT `absen_id_guru_foreign` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  ADD CONSTRAINT `absen_id_siswa_foreign` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

--
-- Constraints for table `bab`
--
ALTER TABLE `bab`
  ADD CONSTRAINT `bab_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE;

--
-- Constraints for table `barcode`
--
ALTER TABLE `barcode`
  ADD CONSTRAINT `barcode_id_siswa_foreign` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

--
-- Constraints for table `hasil_nilai`
--
ALTER TABLE `hasil_nilai`
  ADD CONSTRAINT `hasil_nilai_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `hasil_nilai_id_nilai_foreign` FOREIGN KEY (`id_nilai`) REFERENCES `nilai` (`id_nilai`) ON DELETE CASCADE,
  ADD CONSTRAINT `hasil_nilai_id_siswa_foreign` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_mata_pelajaran`
--
ALTER TABLE `jadwal_mata_pelajaran`
  ADD CONSTRAINT `jadwal_mata_pelajaran_id_guru_foreign` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_mata_pelajaran_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_mata_pelajaran_id_rooms_foreign` FOREIGN KEY (`id_rooms`) REFERENCES `kelas` (`id_rooms`) ON DELETE SET NULL;

--
-- Constraints for table `materi`
--
ALTER TABLE `materi`
  ADD CONSTRAINT `materi_id_bab_foreign` FOREIGN KEY (`id_bab`) REFERENCES `bab` (`id_bab`) ON DELETE CASCADE,
  ADD CONSTRAINT `materi_id_sub_bab_foreign` FOREIGN KEY (`id_sub_bab`) REFERENCES `sub_bab` (`id_sub_bab`) ON DELETE CASCADE;

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_id_pr_foreign` FOREIGN KEY (`id_pr`) REFERENCES `pr` (`id_pr`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_id_quiz_foreign` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_id_guru_foreign` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifikasi_id_pr_foreign` FOREIGN KEY (`id_pr`) REFERENCES `pr` (`id_pr`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifikasi_id_siswa_foreign` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

--
-- Constraints for table `pr`
--
ALTER TABLE `pr`
  ADD CONSTRAINT `pr_id_guru_foreign` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  ADD CONSTRAINT `pr_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_id_guru_foreign` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE;

--
-- Constraints for table `rpp`
--
ALTER TABLE `rpp`
  ADD CONSTRAINT `rpp_id_guru_foreign` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  ADD CONSTRAINT `rpp_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `rpp_id_rooms_foreign` FOREIGN KEY (`id_rooms`) REFERENCES `kelas` (`id_rooms`) ON DELETE SET NULL;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE SET NULL,
  ADD CONSTRAINT `siswa_id_rooms_foreign` FOREIGN KEY (`id_rooms`) REFERENCES `kelas` (`id_rooms`) ON DELETE SET NULL;

--
-- Constraints for table `sub_bab`
--
ALTER TABLE `sub_bab`
  ADD CONSTRAINT `sub_bab_id_bab_foreign` FOREIGN KEY (`id_bab`) REFERENCES `bab` (`id_bab`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
