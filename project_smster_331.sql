-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 06, 2026 at 12:29 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

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
  `status` enum('Hadir','Izin','Sakit','Alpa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hadir',
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `berkas_surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metode` enum('scan_qr','manual_guru') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scan_qr',
  `waktu_absen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absen`
--

INSERT INTO `absen` (`id_absen`, `id_guru`, `id_siswa`, `id_barcode`, `status`, `keterangan`, `berkas_surat`, `metode`, `waktu_absen`, `tanggal`) VALUES
(7, 13, 7, NULL, 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, 'manual_guru', '2026-10-01 15:27:50', '2026-10-01'),
(8, 13, 22, 22, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:10:00', '2026-11-02'),
(9, 13, 23, 23, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:08:00', '2026-11-02'),
(10, 13, 24, 24, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:05:00', '2026-11-02'),
(11, 13, 25, 25, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:12:00', '2026-11-02'),
(12, 13, 26, 26, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:06:00', '2026-11-02'),
(13, 13, 27, 27, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:09:00', '2026-11-02'),
(14, 13, 28, 28, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:11:00', '2026-11-02'),
(15, 13, 7, 21, 'Hadir', 'Scan QR Siswa', NULL, 'scan_qr', '2026-11-02 00:14:00', '2026-11-02'),
(16, 13, 29, 29, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:02:00', '2026-11-02'),
(17, 13, 30, 30, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:04:00', '2026-11-02'),
(18, 13, 31, NULL, 'Sakit', 'Surat Dokter terlampir', NULL, 'manual_guru', '2026-11-02 00:00:00', '2026-11-02'),
(19, 13, 32, 32, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:07:00', '2026-11-02'),
(20, 13, 33, 33, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:13:00', '2026-11-02'),
(21, 13, 35, 35, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:01:00', '2026-11-02'),
(22, 13, 36, 36, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:03:00', '2026-11-02'),
(23, 13, 37, 37, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:08:00', '2026-11-02'),
(24, 13, 38, 38, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:09:00', '2026-11-02'),
(25, 13, 39, NULL, 'Izin', 'Acara Keluarga di Luar Kota', NULL, 'manual_guru', '2026-11-02 00:00:00', '2026-11-02'),
(26, 13, 40, 40, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:10:00', '2026-11-02'),
(27, 13, 41, 41, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:06:00', '2026-11-02'),
(28, 13, 42, 42, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:11:00', '2026-11-02'),
(29, 13, 43, 43, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:05:00', '2026-11-02'),
(30, 13, 44, 44, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:07:00', '2026-11-02'),
(31, 13, 45, 45, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:08:00', '2026-11-02'),
(32, 13, 46, 46, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:04:00', '2026-11-02'),
(33, 13, 47, 47, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:09:00', '2026-11-02'),
(34, 13, 48, 48, 'Hadir', 'Hadir Tepat Waktu', NULL, 'scan_qr', '2026-11-02 00:12:00', '2026-11-02');

-- --------------------------------------------------------

--
-- Table structure for table `absensi_settings`
--

CREATE TABLE `absensi_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi_settings`
--

INSERT INTO `absensi_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'batas_awal', '07:00', NULL, NULL),
(2, 'batas_tepat', '08:00', NULL, NULL),
(3, 'batas_tutup', '12:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` bigint UNSIGNED NOT NULL,
  `nip` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_admin` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nip`, `nama_admin`, `password`, `created_at`, `updated_at`) VALUES
(3, '19850101001', 'Administrator Utama', 'admin123', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(4, 'admin', 'admin', 'admin123', '2026-09-30 04:41:30', '2026-09-30 04:41:30');

-- --------------------------------------------------------

--
-- Table structure for table `bab`
--

CREATE TABLE `bab` (
  `id_bab` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `nama_bab` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bab`
--

INSERT INTO `bab` (`id_bab`, `id_mapel`, `nama_bab`) VALUES
(6, 17, 'Bab 1: Operasi Hitung Bilangan Cacah'),
(8, 17, 'Bab 2 Okeokeoke'),
(9, 17, 'Bab 2 Okeokeokee');

-- --------------------------------------------------------

--
-- Table structure for table `barcode`
--

CREATE TABLE `barcode` (
  `id_barcode` bigint UNSIGNED NOT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
  `kode_barcode` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barcode`
--

INSERT INTO `barcode` (`id_barcode`, `id_siswa`, `kode_barcode`, `qr_image_path`, `is_active`, `created_at`, `updated_at`) VALUES
(21, 7, 'BIN-0012345678-2RBTDA', NULL, 1, '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(22, 22, 'BIN-0128471920-Q2YQIF', NULL, 1, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(23, 23, 'BIN-0128471932-BZ9Y7W', NULL, 1, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(24, 24, 'BIN-0128471933-QHGQIS', NULL, 1, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(25, 25, 'BIN-0128471934-1URO1O', NULL, 1, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(26, 26, 'BIN-0128471935-ITZCMZ', NULL, 1, '2026-11-02 00:42:01', '2026-11-02 00:42:01'),
(27, 27, 'BIN-0128471936-HZQR02', NULL, 1, '2026-11-02 00:42:01', '2026-11-02 00:42:01'),
(28, 28, 'BIN-0128471937-9D2XYG', NULL, 1, '2026-11-02 00:42:01', '2026-11-02 00:42:01'),
(29, 29, 'BIN-0128471939-LDHXQX', NULL, 1, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(30, 30, 'BIN-0128471940-C8HIRX', NULL, 1, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(31, 31, 'BIN-0128471950-MSFB6J', NULL, 1, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(32, 32, 'BIN-0128471942-Q16BOS', NULL, 1, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(33, 33, 'BIN-0128471943-NR2TN9', NULL, 1, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(34, 34, 'BIN-0128471970-VEC4QZ', NULL, 1, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(35, 35, 'BIN-0128471945-S53BVN', NULL, 1, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(36, 36, 'BIN-0128471946-TVTFIJ', NULL, 1, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(37, 37, 'BIN-0128471947-MHR5JP', NULL, 1, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(38, 38, 'BIN-0128471948-NQW2LX', NULL, 1, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(39, 39, 'BIN-0128471962-XSLD9K', NULL, 1, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(40, 40, 'BIN-0128471951-HYFVWS', NULL, 1, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(41, 41, 'BIN-0128471952-OJX4RX', NULL, 1, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(42, 42, 'BIN-0128471953-6JDSB6', NULL, 1, '2026-11-02 00:42:05', '2026-11-02 00:42:05'),
(43, 43, 'BIN-0128471954-XJX1UR', NULL, 1, '2026-11-02 00:42:05', '2026-11-02 00:42:05'),
(44, 44, 'BIN-0128471955-GMJM4Y', NULL, 1, '2026-11-02 00:42:05', '2026-11-02 00:42:05'),
(45, 45, 'BIN-0128471956-48FJK7', NULL, 1, '2026-11-02 00:42:06', '2026-11-02 00:42:06'),
(46, 46, 'BIN-0128471957-ZP2IGZ', NULL, 1, '2026-11-02 00:42:06', '2026-11-02 00:42:06'),
(47, 47, 'BIN-0128471958-XSOHAJ', NULL, 1, '2026-11-02 00:42:06', '2026-11-02 00:42:06'),
(48, 48, 'BIN-0128471959-3LSREL', NULL, 1, '2026-11-02 00:42:06', '2026-11-02 00:42:06'),
(49, 5, 'QR-QOJTNR6K', NULL, 1, '2026-11-03 00:08:57', '2026-11-03 00:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id_guru` bigint UNSIGNED NOT NULL,
  `nip` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_guru` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id_guru`, `nip`, `nama_guru`, `email`, `no_hp`, `alamat`, `jenis_kelamin`, `username`, `password`, `created_at`, `updated_at`) VALUES
(13, '198501012010012001', 'Ibu Sarah Wijaya, S.Pd.', 'sarah.wijaya@kalitapen01.sch.id', '081234567890', 'Jl. Kalitapen No. 12', 'P', 'sarahguru', 'guru123', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(14, '199001012020011002', 'Guru Kedua Percobaan', NULL, NULL, NULL, NULL, NULL, 'password123', '2026-10-02 02:45:06', '2026-10-02 02:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_kuis_siswa`
--

CREATE TABLE `hasil_kuis_siswa` (
  `id_hasil` bigint UNSIGNED NOT NULL,
  `id_quiz` bigint UNSIGNED NOT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
  `jumlah_benar` int NOT NULL,
  `jumlah_salah` int NOT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL,
  `waktu_menit` int DEFAULT NULL,
  `nilai_keaktifan` int DEFAULT NULL,
  `catatan_guru` text COLLATE utf8mb4_unicode_ci,
  `status_kirim` tinyint(1) NOT NULL DEFAULT '0',
  `waktu_kirim` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hasil_kuis_siswa`
--

INSERT INTO `hasil_kuis_siswa` (`id_hasil`, `id_quiz`, `id_siswa`, `jumlah_benar`, `jumlah_salah`, `nilai_akhir`, `waktu_menit`, `nilai_keaktifan`, `catatan_guru`, `status_kirim`, `waktu_kirim`, `created_at`, `updated_at`) VALUES
(3, 4, 7, 2, 3, '40.00', NULL, NULL, NULL, 0, NULL, '2026-09-30 12:39:38', '2026-09-30 12:39:38'),
(6, 9, 7, 0, 2, '0.00', 1, NULL, NULL, 0, NULL, '2026-10-02 01:16:53', '2026-10-02 01:16:53'),
(7, 10, 1, 2, 0, '100.00', 60, NULL, NULL, 1, '2026-10-02 05:11:49', '2026-10-02 05:11:49', '2026-10-02 05:11:49'),
(8, 9, 1, 1, 1, '50.00', 1, NULL, NULL, 0, NULL, '2026-10-02 05:23:14', '2026-10-02 05:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_nilai`
--

CREATE TABLE `hasil_nilai` (
  `id_hasil_nilai` bigint UNSIGNED NOT NULL,
  `id_nilai` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
  `data_nilai` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `hari` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwal_mata_pelajaran`
--

INSERT INTO `jadwal_mata_pelajaran` (`id_jadwal`, `id_mapel`, `id_guru`, `id_rooms`, `hari`, `jam`) VALUES
(1, 18, 13, 12, 'Senin', '06.00-08.00');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_rooms` bigint UNSIGNED NOT NULL,
  `pararel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_rooms`, `pararel`) VALUES
(1, 'Kelas 4A'),
(2, 'Kelas 5A'),
(12, 'Kelas 4B');

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id_mapel` bigint UNSIGNED NOT NULL,
  `nama_mapel` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`id_mapel`, `nama_mapel`, `deskripsi`) VALUES
(3, 'Bahasa Indonesia', 'Membaca pemahaman, tata bahasa, dan penulisan cerita.'),
(17, 'Matematika', 'Pembelajaran konsep bilangan, operasi hitung, pecahan, dan geometri dasar.'),
(18, 'Ilmu Pengetahuan Alam (IPA)', 'Eksplorasi sains alam, ekosistem, gaya dan gerak, serta tata surya.');

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id_materi` bigint UNSIGNED NOT NULL,
  `id_sub_bab` bigint UNSIGNED NOT NULL,
  `id_bab` bigint UNSIGNED NOT NULL,
  `judul_materi` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi_materi` text COLLATE utf8mb4_unicode_ci,
  `tipe_materi` enum('video','dokumen','kuis') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'video',
  `url_video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_quiz` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`id_materi`, `id_sub_bab`, `id_bab`, `judul_materi`, `isi_materi`, `tipe_materi`, `url_video`, `file_pdf`, `id_quiz`, `created_at`, `updated_at`) VALUES
(5, 6, 6, 'Video Penjelasan Nilai Tempat Puluhan & Ribuan', 'Simak video penjelasan berikut mengenai cara menentukan nilai tempat bilangan ribuan.', 'video', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', NULL, NULL, '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(6, 6, 6, 'Kuis Evaluasi Bilangan Cacah', 'Kerjakan kuis 5 soal pilihan ganda berikut untuk menguji pemahaman Anda.', 'kuis', NULL, NULL, 4, '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(12, 8, 8, 'Gitar Mahal', NULL, 'video', 'https://www.youtube.com/watch?v=AR__qzglDdc&list=RDAR__qzglDdc&start_radio=1', NULL, NULL, '2026-10-01 18:54:15', '2026-10-01 18:55:19'),
(13, 8, 8, '123', NULL, 'kuis', NULL, NULL, 6, '2026-10-01 18:56:11', '2026-10-01 18:56:11'),
(14, 9, 9, 'Anjay Mabar', NULL, 'video', 'https://www.youtube.com/watch?v=ObWUXRmizjc&list=RDObWUXRmizjc&start_radio=1', NULL, NULL, '2026-10-02 05:05:14', '2026-10-02 05:05:14'),
(15, 9, 9, '223232', NULL, 'dokumen', NULL, 'materi_pdf/3fzH3b14hAE7c8xx6pamTgIscFcg8tbh0X0tW08v.pdf', NULL, '2026-10-02 05:09:39', '2026-10-02 05:09:39'),
(16, 9, 9, 'kaskjdsadsakjdsa', NULL, 'kuis', NULL, NULL, 10, '2026-10-02 05:10:06', '2026-10-02 05:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000001_create_academic_system_tables', 1),
(5, '2026_01_01_000002_add_password_to_guru', 1),
(6, '2026_01_01_000003_add_catatan_revisi_to_rpp', 1),
(7, '2026_01_01_000004_add_deskripsi_to_mata_pelajaran', 1),
(8, '2026_09_20_000000_create_siswas_table', 1),
(9, '2026_09_20_000001_create_barcodes_table', 1),
(10, '2026_09_20_000002_create_absens_table', 1),
(11, '2026_09_22_032036_create_table_guru', 1),
(12, '2026_09_22_032220_create_table_siswa', 1),
(13, '2026_10_01_000001_create_learning_assessment_tables', 1),
(14, '2026_10_01_000002_update_quiz_columns_nullable', 1),
(15, '2026_09_23_000001_create_absensi_settings_table', 2),
(16, '2026_10_01_135253_add_details_to_rpp_table', 2),
(17, '2026_10_01_165804_add_details_to_hasil_kuis_siswa_table', 2),
(18, '2026_10_01_174500_add_ujian_online_features', 2),
(19, '2026_10_01_180000_add_status_kirim_to_hasil_kuis_siswa', 3),
(20, '2026_10_02_080000_add_tingkat_kesulitan_to_soal_quiz', 4);

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
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_baca` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_guru`, `id_siswa`, `id_pr`, `pesan`, `status_baca`, `created_at`, `updated_at`) VALUES
(2, 13, 7, NULL, 'Tugas PR Baru: \'kawkewaekwaew\' (Bahasa Indonesia). Batas pengumpulan: 23 Okt 2026, 07:30 WIB. Silakan periksa instruksi tugas dan kumpulkan tepat waktu.', 1, '2026-10-01 20:00:36', '2026-10-01 20:00:50'),
(3, 13, 7, NULL, 'Hasil Ujian [Matekmatek]: Nilai Anda adalah 0.', 0, '2026-10-02 01:00:27', '2026-10-02 01:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr`
--

CREATE TABLE `pr` (
  `id_pr` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `id_guru` bigint UNSIGNED NOT NULL,
  `nama_pr` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
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
  `id_sub_bab` bigint UNSIGNED DEFAULT NULL,
  `id_materi` bigint UNSIGNED DEFAULT NULL,
  `judul_quiz` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tingkat_level` enum('mudah','sedang','susah') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sedang',
  `durasi_menit` int NOT NULL DEFAULT '60',
  `target_tipe` enum('semua','pilihan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'semua',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `id_mapel` bigint UNSIGNED DEFAULT NULL,
  `id_guru` bigint UNSIGNED DEFAULT NULL,
  `nama_quiz` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id_quiz`, `id_sub_bab`, `id_materi`, `judul_quiz`, `tingkat_level`, `durasi_menit`, `target_tipe`, `deskripsi`, `id_mapel`, `id_guru`, `nama_quiz`, `created_at`, `updated_at`) VALUES
(4, 6, NULL, 'Kuis Evaluasi: Operasi Bilangan', 'sedang', 60, 'semua', NULL, 17, NULL, 'Kuis Evaluasi: Operasi Bilangan', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(6, 8, NULL, 'Kuis: 123', 'sedang', 60, 'semua', NULL, 17, NULL, 'Kuis: 123', '2026-10-01 18:56:11', '2026-10-01 18:56:11'),
(9, NULL, NULL, 'Matekmatek', 'sedang', 60, 'semua', NULL, 17, 13, 'Matekmatek', '2026-10-02 01:15:32', '2026-10-02 01:15:32'),
(10, 9, NULL, 'Kuis: kaskjdsadsakjdsa', 'sedang', 60, 'semua', NULL, 17, NULL, 'Kuis: kaskjdsadsakjdsa', '2026-10-02 05:10:06', '2026-10-02 05:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_target_siswa`
--

CREATE TABLE `quiz_target_siswa` (
  `id_quiz` bigint UNSIGNED NOT NULL,
  `id_siswa` bigint UNSIGNED NOT NULL,
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
  `id_jadwal` bigint UNSIGNED DEFAULT NULL,
  `id_mapel` bigint UNSIGNED NOT NULL,
  `judul_rpp` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `alokasi_waktu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fase` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Fase B',
  `modul_ke` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_jadwal` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruang` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_tanggal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `komponen_checklist` json DEFAULT NULL,
  `status` enum('draft','menunggu_review','terverifikasi','perlu_revisi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `catatan_revisi` text COLLATE utf8mb4_unicode_ci,
  `file_rpp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rpp`
--

INSERT INTO `rpp` (`id_rpp`, `id_guru`, `id_rooms`, `id_jadwal`, `id_mapel`, `judul_rpp`, `deskripsi`, `alokasi_waktu`, `fase`, `modul_ke`, `target_jadwal`, `ruang`, `target_tanggal`, `komponen_checklist`, `status`, `catatan_revisi`, `file_rpp`, `created_at`, `updated_at`) VALUES
(3, 13, 12, NULL, 17, 'Testing', NULL, NULL, 'Fase B', NULL, NULL, NULL, NULL, '{\"kktp\": false, \"lkpd\": true, \"tags\": [], \"audio\": false, \"video\": true, \"tujuan\": true, \"soal_proyektor\": true}', 'terverifikasi', NULL, 'rpp_files/gwLYlBnozrVgNzmgGraT1K2UbRnsKGFPC7J8sMk2.docx', '2026-10-01 19:00:30', '2026-10-01 19:43:43');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('c3ft017aXtDgrVfKzmvRBdRERMzaagkXNtccM3qF', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MDp7fX1zOjY6Il90b2tlbiI7czo0MDoiTjFDYXR6QmJtM0dSVGM2QTQ3ZDl0d2diRENSekY5SlVTeHZobWFuSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9xci1zaXN3YSI7czo1OiJyb3V0ZSI7czoxNDoiYWRtaW4ucXIuaW5kZXgiO31zOjc6InVzZXJfaWQiO2k6NDtzOjk6InVzZXJfdHlwZSI7czo1OiJhZG1pbiI7czo5OiJ1c2VyX25hbWUiO3M6NToiYWRtaW4iO3M6NzoiaWRfdXNlciI7aTo0O30=', 1793673364),
('f1Ye4iHXuOZoB58oQ0pg18WwRmaDzHoBw6oeFdlc', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTozOntzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MDp7fX1zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfdG9rZW4iO3M6NDA6IkV5dFo0ck5hVlVFMW9xdlVXQTF2T0pVUFZ4N3FNYmZiaW9MMllOQ04iO30=', 1793149688),
('odi7p4uO4JEGyGkyZ35lUGkokAwt0bMaDzsU7Aq9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiWW5FbGNMRllqejNPMHRxTGNieDMySVE1ckxhU1BIemJlR2ZRM0s0QyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndXJ1L3JwcCI7czo1OiJyb3V0ZSI7czoxNDoiZ3VydS5ycHAuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjc6InVzZXJfaWQiO2k6MTM7czo5OiJ1c2VyX3R5cGUiO3M6NDoiZ3VydSI7czo5OiJ1c2VyX25hbWUiO3M6MjM6IklidSBTYXJhaCBXaWpheWEsIFMuUGQuIjtzOjc6ImlkX3VzZXIiO2k6MTM7fQ==', 1793578866),
('vrhPFhkmRCGAOZ7EusQFEWodQqJt8mPGLenMAiK0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.1591', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN1FuMldmQTVFUU0zYU8yaTJKS2hIcGdMSVlkSVpIUmFBZ1hacFN6ZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1793148677),
('y6QzLeCaYtGRAOP0k0FkepkaLvCFMgIpE1RvEe6A', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.1591', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRVE0emRRNFFSN0sxNWl6RHNVeGJ0QVJycW5YZFd1NlNZYTdXOUQ1NyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1793148683);

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` bigint UNSIGNED NOT NULL,
  `id_mapel` bigint UNSIGNED DEFAULT NULL,
  `id_rooms` bigint UNSIGNED DEFAULT NULL,
  `nisn` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nm_siswa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_mapel`, `id_rooms`, `nisn`, `nm_siswa`, `email`, `no_hp`, `alamat`, `jenis_kelamin`, `username`, `password`, `foto_profil`, `created_at`, `updated_at`) VALUES
(1, 17, 1, '0012345601', 'Aditya Pratama', 'aditya123@gmail.com', '08111111111', NULL, 'L', 'ADIT', 'siswa1234', NULL, '2026-09-20 01:31:07', '2026-10-01 00:12:57'),
(2, 17, 1, '0012345602', 'Bella Safitri', NULL, '08111111112', NULL, 'P', NULL, '$2y$12$2u4GwxrHc2aGiPSX.6SQQ./YWEWIE8/lWNJmj7h6FBP6yODKOnxmm', NULL, '2026-09-20 01:31:08', '2026-10-02 02:42:37'),
(3, NULL, 1, '0012345603', 'Candra Wijaya', NULL, '08111111113', NULL, 'L', NULL, '$2y$12$bYLvx6rLyTNJ1IZaE7IupuHch./40YsS0hWxEWyWv9GBSyjQjZf66', NULL, '2026-09-20 01:31:08', '2026-10-02 02:42:37'),
(4, NULL, 2, '0012345604', 'Dina Mariana', NULL, '08111111114', NULL, 'P', NULL, '$2y$12$XDTo3in9iOQd4MVy7x0rFOzzSZNc0aGDyY78Zm6qH62DqHKfjtkc.', NULL, '2026-09-20 01:31:08', '2026-10-02 02:42:37'),
(5, 3, 2, '0012345605', 'Eko Prasetyo', NULL, '08111111115', NULL, 'L', NULL, '$2y$12$ZHnL514lQ0cJZ0q71rfEvOBADEYoQfiwqpKgiTm9jbT78xEPgJJXK', NULL, '2026-09-20 01:31:08', '2026-10-02 02:42:37'),
(7, 17, 12, '0012345678', 'Budi Santoso', 'budi.santoso@siswa.kalitapen01.sch.id', '089876543210', 'Jl. Merdeka No. 45', 'L', 'budisiswa', '$2y$12$GM/Rbau9l4MkrBOsqR4Mse7.LCo.vYt/Qk/7eAdXANM/yuwQrDFMu', NULL, '2026-10-01 17:22:07', '2026-11-02 00:42:01'),
(22, NULL, 12, '0128471920', 'Siti Aminah', NULL, NULL, NULL, 'P', NULL, '$2y$12$cw5lXTX2F50b5/IoWudOvu13L1hy9ViZp2AFmMEgHr7g6QSGobH3e', NULL, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(23, NULL, 12, '0128471932', 'Ahmad Zaki', NULL, NULL, NULL, 'L', NULL, '$2y$12$EsPYTQLSieEG5ZVXEHnqJuXlXJQ6beYia7m7sQBAXBVsBjBQZglKm', NULL, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(24, NULL, 12, '0128471933', 'Aisyah Putri', NULL, NULL, NULL, 'P', NULL, '$2y$12$Gn0Jg6RCdEkOZyaiVRA29esG8sHy9NLlnNZaIft9oi6kSI3g0RYOy', NULL, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(25, NULL, 12, '0128471934', 'Alif Ramadan', NULL, NULL, NULL, 'L', NULL, '$2y$12$eAx1Yqbc9hZd1oSVb43TGeind6mBfbp8i77bfCnGhYzAk7BR83lGS', NULL, '2026-11-02 00:42:00', '2026-11-02 00:42:00'),
(26, NULL, 12, '0128471935', 'Anisa Rahma', NULL, NULL, NULL, 'P', NULL, '$2y$12$.iV81uRlcKuH88313L.IHOGej81TL0TINnBYKS7i2KFQNHLGnQwum', NULL, '2026-11-02 00:42:01', '2026-11-02 00:42:01'),
(27, NULL, 12, '0128471936', 'Bagas Pratama', NULL, NULL, NULL, 'L', NULL, '$2y$12$p9lsv8TWCfqzBpXq5smyTe5Z2.RbLBjIQuGOisvbP8ycFjLat73AG', NULL, '2026-11-02 00:42:01', '2026-11-02 00:42:01'),
(28, NULL, 12, '0128471937', 'Bayu Nugroho', NULL, NULL, NULL, 'L', NULL, '$2y$12$Fg8qhGobvAfS2M3HOTNKAOP5y8Hp7qZmJEPsks7m9irDL1clxDBI.', NULL, '2026-11-02 00:42:01', '2026-11-02 00:42:01'),
(29, NULL, 12, '0128471939', 'Cantika Dewi', NULL, NULL, NULL, 'P', NULL, '$2y$12$suxFA5XdJc21LyzQN48tXeGmEx07R2ufGN4DqabWRxeKp6/DYMaL.', NULL, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(30, NULL, 12, '0128471940', 'Dimas Anggara', NULL, NULL, NULL, 'L', NULL, '$2y$12$Xo1mdQO1hsFotOOtxXIUZ.ulTiqAx1D4QYcNwbjl2oZ7CbXzHHruW', NULL, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(31, NULL, 12, '0128471950', 'Maya Puspita', NULL, NULL, NULL, 'P', NULL, '$2y$12$ruYKfqHZ0VI1KYQEt9/VdOilBO8NHeC.P70QNAPWH4Zg9w5EEQVFG', NULL, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(32, NULL, 12, '0128471942', 'Dinda Kirana', NULL, NULL, NULL, 'P', NULL, '$2y$12$XTj9HHfvQF9O25FOVHt9z.WNU4DoKgDuBHII73of77amR9Qq4KOvm', NULL, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(33, NULL, 12, '0128471943', 'Fajar Hidayat', NULL, NULL, NULL, 'L', NULL, '$2y$12$VbDH4uf10cPq.dZ7Uw2EvucjlymeBFb921S.zTiLehDCoDnIbecmO', NULL, '2026-11-02 00:42:02', '2026-11-02 00:42:02'),
(34, NULL, 12, '0128471970', 'Doni Prasetyo', NULL, NULL, NULL, 'L', NULL, '$2y$12$MuVxhacGi2A2F.IRSmH6bOkU4xptKzqIylWbH9ePQz1AWzpuhryky', NULL, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(35, NULL, 12, '0128471945', 'Gilang Ramadhan', NULL, NULL, NULL, 'L', NULL, '$2y$12$N4buBgS6ydfRjNlT6FU4XO9hRQ1FuLCgGNqOwpgbiqMZCzO.fbQYu', NULL, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(36, NULL, 12, '0128471946', 'Hana Safira', NULL, NULL, NULL, 'P', NULL, '$2y$12$WLHd4l2WPNs/KcB1KkN7nOCvU0Qd9zU5RO3oDIBPpKl8xvvUwmJi6', NULL, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(37, NULL, 12, '0128471947', 'Indah Permata', NULL, NULL, NULL, 'P', NULL, '$2y$12$HqimDdCQ4IRiufUck7LHM.CiXQ5lSv.7SdBS493N44W0QyJWCz2vO', NULL, '2026-11-02 00:42:03', '2026-11-02 00:42:03'),
(38, NULL, 12, '0128471948', 'Joko Susilo', NULL, NULL, NULL, 'L', NULL, '$2y$12$23fRlx1k0vaJtlt31Z.wAOMgEk720yzCcafIVGoyMAEj7TDueQv2m', NULL, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(39, NULL, 12, '0128471962', 'Rafi Firdaus', NULL, NULL, NULL, 'L', NULL, '$2y$12$tbMnu5LtWX9l/OGUzeACIurwhsJYsZRR8fmCmYEvofuY96pBv8HVa', NULL, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(40, NULL, 12, '0128471951', 'Kevin Sanjaya', NULL, NULL, NULL, 'L', NULL, '$2y$12$K.NKDWqUx75Pt0Q0P5RoMenkUpZEoJ.p0Osf8sWgGIzzchxCFOgXy', NULL, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(41, NULL, 12, '0128471952', 'Larasati Putri', NULL, NULL, NULL, 'P', NULL, '$2y$12$08hZ6mv1uMh0caKPkPrVhO7BD3pZaUf/iMvwF88lVXilrWgEx/ac.', NULL, '2026-11-02 00:42:04', '2026-11-02 00:42:04'),
(42, NULL, 12, '0128471953', 'Muhammad Rizky', NULL, NULL, NULL, 'L', NULL, '$2y$12$NvE.2r3BWN2XIKg9/eDMl.7gAOufY9GDQtPURdTqf71wyZeYxn91G', NULL, '2026-11-02 00:42:05', '2026-11-02 00:42:05'),
(43, NULL, 12, '0128471954', 'Nadya Zahra', NULL, NULL, NULL, 'P', NULL, '$2y$12$ZsFjbczmJZ22/T4QvFe/nehBhDi1pLUZJr0V38Qev3ymIa/41iSLO', NULL, '2026-11-02 00:42:05', '2026-11-02 00:42:05'),
(44, NULL, 12, '0128471955', 'Putri Maharani', NULL, NULL, NULL, 'P', NULL, '$2y$12$xs7vPWV56PH4ZjLNkig7LeNT.UrFwjUwlF1nL.0WlkSHRSRgId3Y6', NULL, '2026-11-02 00:42:05', '2026-11-02 00:42:05'),
(45, NULL, 12, '0128471956', 'Raditya Pratama', NULL, NULL, NULL, 'L', NULL, '$2y$12$W7cpO3Yess7wh7h56Tr0HepIxnc4gvBWHVp/UY5E5bSPB2nZMa6Uq', NULL, '2026-11-02 00:42:06', '2026-11-02 00:42:06'),
(46, NULL, 12, '0128471957', 'Rian Kurniawan', NULL, NULL, NULL, 'L', NULL, '$2y$12$wl12X/9vz99Q7XAjpraZ2.S9tUXwoPjr2f3/HSVPX8coVGLMY4lxy', NULL, '2026-11-02 00:42:06', '2026-11-02 00:42:06'),
(47, NULL, 12, '0128471958', 'Salsabila Azzahra', NULL, NULL, NULL, 'P', NULL, '$2y$12$mT9mQp6pNVP8ruFwTQbZfOBvvDRrW3CSEVq.ky.1FHMMYKA09Ijxu', NULL, '2026-11-02 00:42:06', '2026-11-02 00:42:06'),
(48, NULL, 12, '0128471959', 'Tiara Andini', NULL, NULL, NULL, 'P', NULL, '$2y$12$k24aDDkmZuPZhrr6vBvYZuPBGf3gIf3sF.YWZhvvp3UuIwEeXgx9S', NULL, '2026-11-02 00:42:06', '2026-11-02 00:42:06');

-- --------------------------------------------------------

--
-- Table structure for table `soal_quiz`
--

CREATE TABLE `soal_quiz` (
  `id_soal` bigint UNSIGNED NOT NULL,
  `id_quiz` bigint UNSIGNED NOT NULL,
  `pertanyaan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opsi_a` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opsi_b` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opsi_c` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `opsi_d` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kunci_jawaban` enum('A','B','C','D') COLLATE utf8mb4_unicode_ci NOT NULL,
  `bobot_nilai` int NOT NULL DEFAULT '10',
  `tingkat_kesulitan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sedang',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `soal_quiz`
--

INSERT INTO `soal_quiz` (`id_soal`, `id_quiz`, `pertanyaan`, `gambar`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `kunci_jawaban`, `bobot_nilai`, `tingkat_kesulitan`, `created_at`, `updated_at`) VALUES
(9, 4, 'Berapakah hasil dari 2.450 + 1.320?', NULL, '3.770', '3.750', '3.870', '3.670', 'A', 10, 'sedang', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(10, 4, 'Angka 7 pada bilangan 5.724 menempati nilai tempat?', NULL, 'Satuan', 'Puluhan', 'Ratusan', 'Ribuan', 'C', 10, 'sedang', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(11, 4, 'Berapakah 500 dikalikan 4?', NULL, '1.500', '2.000', '2.500', '3.000', 'B', 10, 'sedang', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(12, 4, 'Hasil dari 1.000 - 375 adalah?', NULL, '625', '635', '725', '525', 'A', 10, 'sedang', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(13, 4, 'Bilangan genap antara 11 dan 15 adalah?', NULL, '12 dan 13', '12 dan 14', '13 dan 14', '14 dan 15', 'B', 10, 'sedang', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(14, 4, 'Berapakah 8 x 7?', NULL, '54', '56', '58', '60', 'B', 10, 'sedang', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(15, 6, 'Berapakah 2 + 2?', NULL, '3', '4', '5', '6', 'B', 10, 'sedang', '2026-10-01 18:56:11', '2026-10-01 18:56:11'),
(16, 6, 'Ibu kota Indonesia adalah?', NULL, 'Bandung', 'Surabaya', 'Jakarta', 'Medan', 'C', 10, 'sedang', '2026-10-01 18:56:11', '2026-10-01 18:56:11'),
(21, 9, 'joiwqjowewe123213', NULL, '123', '13231232132', '123213', '123213213', 'A', 10, 'sedang', '2026-10-02 01:15:32', '2026-10-02 01:15:32'),
(22, 9, 'saijewaeijwaejiwae', NULL, '123', '12341235', '123', '45645', 'A', 10, 'sedang', '2026-10-02 01:15:32', '2026-10-02 01:15:32'),
(23, 10, 'Berapakah 2 + 2?', NULL, '3', '4', '5', '6', 'B', 10, 'sedang', '2026-10-02 05:10:06', '2026-10-02 05:10:06'),
(24, 10, 'Ibu kota Indonesia adalah?', NULL, 'Bandung', 'Surabaya', 'Jakarta', 'Medan', 'C', 10, 'sedang', '2026-10-02 05:10:06', '2026-10-02 05:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `sub_bab`
--

CREATE TABLE `sub_bab` (
  `id_sub_bab` bigint UNSIGNED NOT NULL,
  `id_bab` bigint UNSIGNED NOT NULL,
  `nama_sub_bab` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_bab`
--

INSERT INTO `sub_bab` (`id_sub_bab`, `id_bab`, `nama_sub_bab`) VALUES
(6, 6, 'Sub-Bab 1.1: Nilai Tempat dan Penjumlahan Ribuan'),
(8, 8, '12321321321321323321'),
(9, 9, '12321321321321323321');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
-- Indexes for table `absensi_settings`
--
ALTER TABLE `absensi_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `absensi_settings_key_unique` (`key`);

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
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`),
  ADD UNIQUE KEY `guru_nip_unique` (`nip`);

--
-- Indexes for table `hasil_kuis_siswa`
--
ALTER TABLE `hasil_kuis_siswa`
  ADD PRIMARY KEY (`id_hasil`),
  ADD UNIQUE KEY `hasil_kuis_siswa_id_quiz_id_siswa_unique` (`id_quiz`,`id_siswa`),
  ADD KEY `hasil_kuis_siswa_id_siswa_foreign` (`id_siswa`);

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
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `materi_id_bab_foreign` (`id_bab`),
  ADD KEY `materi_id_quiz_foreign` (`id_quiz`);

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
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

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
  ADD KEY `quiz_id_guru_foreign` (`id_guru`),
  ADD KEY `quiz_id_sub_bab_foreign` (`id_sub_bab`);

--
-- Indexes for table `quiz_target_siswa`
--
ALTER TABLE `quiz_target_siswa`
  ADD PRIMARY KEY (`id_quiz`,`id_siswa`),
  ADD KEY `quiz_target_siswa_id_siswa_foreign` (`id_siswa`);

--
-- Indexes for table `rpp`
--
ALTER TABLE `rpp`
  ADD PRIMARY KEY (`id_rpp`),
  ADD KEY `rpp_id_rooms_foreign` (`id_rooms`),
  ADD KEY `rpp_id_mapel_foreign` (`id_mapel`),
  ADD KEY `rpp_id_guru_index` (`id_guru`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `siswa_nisn_unique` (`nisn`),
  ADD KEY `siswa_id_mapel_foreign` (`id_mapel`),
  ADD KEY `siswa_id_rooms_foreign` (`id_rooms`);

--
-- Indexes for table `soal_quiz`
--
ALTER TABLE `soal_quiz`
  ADD PRIMARY KEY (`id_soal`),
  ADD KEY `soal_quiz_id_quiz_foreign` (`id_quiz`);

--
-- Indexes for table `sub_bab`
--
ALTER TABLE `sub_bab`
  ADD PRIMARY KEY (`id_sub_bab`),
  ADD KEY `sub_bab_id_bab_foreign` (`id_bab`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absen`
--
ALTER TABLE `absen`
  MODIFY `id_absen` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `absensi_settings`
--
ALTER TABLE `absensi_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bab`
--
ALTER TABLE `bab`
  MODIFY `id_bab` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `barcode`
--
ALTER TABLE `barcode`
  MODIFY `id_barcode` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hasil_kuis_siswa`
--
ALTER TABLE `hasil_kuis_siswa`
  MODIFY `id_hasil` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hasil_nilai`
--
ALTER TABLE `hasil_nilai`
  MODIFY `id_hasil_nilai` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_mata_pelajaran`
--
ALTER TABLE `jadwal_mata_pelajaran`
  MODIFY `id_jadwal` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_rooms` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id_mapel` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id_materi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id_nilai` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pr`
--
ALTER TABLE `pr`
  MODIFY `id_pr` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id_quiz` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rpp`
--
ALTER TABLE `rpp`
  MODIFY `id_rpp` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `soal_quiz`
--
ALTER TABLE `soal_quiz`
  MODIFY `id_soal` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sub_bab`
--
ALTER TABLE `sub_bab`
  MODIFY `id_sub_bab` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `hasil_kuis_siswa`
--
ALTER TABLE `hasil_kuis_siswa`
  ADD CONSTRAINT `hasil_kuis_siswa_id_quiz_foreign` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE CASCADE,
  ADD CONSTRAINT `hasil_kuis_siswa_id_siswa_foreign` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `materi_id_quiz_foreign` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE SET NULL,
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
  ADD CONSTRAINT `quiz_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_id_sub_bab_foreign` FOREIGN KEY (`id_sub_bab`) REFERENCES `sub_bab` (`id_sub_bab`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_target_siswa`
--
ALTER TABLE `quiz_target_siswa`
  ADD CONSTRAINT `quiz_target_siswa_id_quiz_foreign` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_target_siswa_id_siswa_foreign` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

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
-- Constraints for table `soal_quiz`
--
ALTER TABLE `soal_quiz`
  ADD CONSTRAINT `soal_quiz_id_quiz_foreign` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE CASCADE;

--
-- Constraints for table `sub_bab`
--
ALTER TABLE `sub_bab`
  ADD CONSTRAINT `sub_bab_id_bab_foreign` FOREIGN KEY (`id_bab`) REFERENCES `bab` (`id_bab`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
