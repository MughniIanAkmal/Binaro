-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2026 at 04:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
  `id_absen` bigint(20) UNSIGNED NOT NULL,
  `id_guru` bigint(20) UNSIGNED NOT NULL,
  `id_siswa` bigint(20) UNSIGNED NOT NULL,
  `id_barcode` bigint(20) UNSIGNED DEFAULT NULL,
  `metode` enum('scan_qr','manual_guru') NOT NULL DEFAULT 'scan_qr',
  `status` enum('Hadir','Izin','Sakit','Alpa') NOT NULL DEFAULT 'Hadir',
  `keterangan` varchar(255) DEFAULT NULL,
  `berkas_surat` varchar(255) DEFAULT NULL,
  `waktu_absen` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absen`
--

INSERT INTO `absen` (`id_absen`, `id_guru`, `id_siswa`, `id_barcode`, `metode`, `status`, `keterangan`, `berkas_surat`, `waktu_absen`, `tanggal`) VALUES
(3, 1, 1, 1, 'scan_qr', 'Hadir', 'Sudah pulang', 'surat_izin/xhrRx7JLdt3hJ97i6P5feYn0C2YbvrF5lZUCcB6N.pdf', '2026-09-23 06:28:56', '2026-09-23'),
(4, 1, 2, 2, 'scan_qr', 'Hadir', 'Terlambat', NULL, '2026-09-23 06:23:35', '2026-09-23'),
(5, 1, 3, 3, 'scan_qr', 'Hadir', 'Datang Lebih Awal', NULL, '2026-09-22 23:19:52', '2026-09-23'),
(11, 13, 1, NULL, 'manual_guru', 'Sakit', 'Sakit', NULL, '2026-10-01 14:24:18', '2026-10-01'),
(12, 13, 2, NULL, 'manual_guru', 'Izin', 'nikah', NULL, '2026-10-01 14:24:36', '2026-10-01'),
(13, 13, 3, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-01 15:02:41', '2026-09-30'),
(14, 13, 4, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-01 15:02:41', '2026-09-30'),
(15, 13, 1, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-01 15:02:41', '2026-09-30'),
(16, 13, 2, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-01 15:02:41', '2026-09-30'),
(17, 13, 5, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-01 15:02:41', '2026-09-30'),
(19, 1, 3, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-02 00:43:36', '2026-10-01'),
(20, 1, 4, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-02 00:43:36', '2026-10-01'),
(21, 1, 7, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-02 00:43:36', '2026-10-01'),
(22, 1, 5, NULL, 'manual_guru', 'Alpa', 'Alpha otomatis - tanpa keterangan', NULL, '2026-10-02 00:43:36', '2026-10-01'),
(23, 1, 2, 2, 'scan_qr', 'Hadir', 'Tepat Waktu', NULL, '2026-10-02 00:44:41', '2026-10-02'),
(24, 2, 1, 1, 'scan_qr', 'Hadir', 'Terlambat', NULL, '2026-10-02 01:05:11', '2026-10-02');

-- --------------------------------------------------------

--
-- Table structure for table `absensi_settings`
--

CREATE TABLE `absensi_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `absensi_settings`
--

INSERT INTO `absensi_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'batas_awal', '07:00', NULL, NULL),
(2, 'batas_tepat', '08:00', NULL, '2026-09-23 07:15:01'),
(3, 'batas_tutup', '23:00', NULL, '2026-09-23 08:34:03');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nip`, `nama_admin`, `password`, `created_at`, `updated_at`) VALUES
(1, '19850101001', 'Bpk. Ahmad Fauzi, S.Kom', '$2y$12$DtLgaBEuck0xajioE01k1OyfYeO6nd9nBhRa4nMSvHQ9y9tPnqdu6', '2026-09-20 08:31:06', NULL),
(4, 'admin', 'admin', 'admin123', '2026-10-02 00:24:18', '2026-10-02 00:24:18');

-- --------------------------------------------------------

--
-- Table structure for table `bab`
--

CREATE TABLE `bab` (
  `id_bab` bigint(20) UNSIGNED NOT NULL,
  `id_mapel` bigint(20) UNSIGNED NOT NULL,
  `nama_bab` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barcode`
--

CREATE TABLE `barcode` (
  `id_barcode` bigint(20) UNSIGNED NOT NULL,
  `id_siswa` bigint(20) UNSIGNED NOT NULL,
  `kode_barcode` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barcode`
--

INSERT INTO `barcode` (`id_barcode`, `id_siswa`, `kode_barcode`, `created_at`, `updated_at`) VALUES
(1, 1, 'QR-LYKIUEYH', '2026-09-20 08:31:08', '2026-10-02 01:03:52'),
(2, 2, 'QR-H2ILLDIR', '2026-09-20 08:31:08', '2026-10-02 00:36:38'),
(3, 3, 'QR-SISWA-003', '2026-09-20 08:31:08', NULL),
(4, 4, 'QR-SISWA-004', '2026-09-20 08:31:08', NULL),
(5, 5, 'QR-SISWA-005', '2026-09-20 08:31:08', NULL),
(7, 7, 'QR-TDPH72Q9', '2026-10-02 00:40:08', '2026-10-02 00:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id_guru` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(30) NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id_guru`, `nip`, `nama_guru`, `no_hp`, `email`, `alamat`, `jenis_kelamin`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, '19800101001', 'Siti Nurhaliza, S.Pd', '081234567891', NULL, NULL, NULL, NULL, '$2y$12$PTk4wIj/6OK2DRvbvLV5EuvLmNOZ2Re2nT4IMFvwwPYHn/vDty3T2', '2026-09-20 08:31:07', NULL),
(2, '19800101002', 'Budi Santoso, S.Pd', '081234567892', NULL, NULL, NULL, NULL, '$2y$12$KtXgUWp1cAkEwQiftaj4Nu601NgPytWggNzsTyBwW/8a7RmyFwDXu', '2026-09-20 08:31:07', NULL),
(3, '19800101003', 'Dewi Lestari, M.Pd', '081234567893', NULL, NULL, NULL, NULL, '$2y$12$OKlYQGWkc3r8.4CKaX1Q1ePVD5OLU4ZcXZCqRZcRTM4L5hYphZB8S', '2026-09-20 08:31:07', NULL),
(13, '19850101201001', 'Ibu Sarah Wijaya, S.Pd.', '081234567890', 'sarah.wijaya@kalitapen01.sch.id', 'Jl. Kalitapen No. 12', 'P', 'sarahguru', 'guru123', '2026-09-30 04:41:30', '2026-10-02 00:47:56'),
(14, '027819824981327', 'Budi Lesmono', '081239748772', 'Lesmono@gmail.com', NULL, 'L', 'BUDIII', '$2y$12$oYybl7MLcZn0xJK1WGPXIe.d6EGjAdqyWdknkM1i0WhLnOzpoHOwS', '2026-10-02 00:54:15', '2026-10-02 00:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_kuis_siswa`
--

CREATE TABLE `hasil_kuis_siswa` (
  `id_hasil` bigint(20) UNSIGNED NOT NULL,
  `id_quiz` bigint(20) UNSIGNED NOT NULL,
  `id_siswa` bigint(20) UNSIGNED NOT NULL,
  `jumlah_benar` int(11) NOT NULL,
  `jumlah_salah` int(11) NOT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL,
  `waktu_menit` int(11) DEFAULT NULL,
  `nilai_keaktifan` int(11) DEFAULT NULL,
  `catatan_guru` text DEFAULT NULL,
  `status_kirim` tinyint(1) NOT NULL DEFAULT 0,
  `waktu_kirim` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_nilai`
--

CREATE TABLE `hasil_nilai` (
  `id_hasil_nilai` bigint(20) UNSIGNED NOT NULL,
  `id_nilai` bigint(20) UNSIGNED NOT NULL,
  `id_mapel` bigint(20) UNSIGNED NOT NULL,
  `id_siswa` bigint(20) UNSIGNED NOT NULL,
  `data_nilai` varchar(100) DEFAULT NULL,
  `total_nilai` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_mata_pelajaran`
--

CREATE TABLE `jadwal_mata_pelajaran` (
  `id_jadwal` bigint(20) UNSIGNED NOT NULL,
  `id_mapel` bigint(20) UNSIGNED NOT NULL,
  `id_guru` bigint(20) UNSIGNED NOT NULL,
  `id_rooms` bigint(20) UNSIGNED DEFAULT NULL,
  `hari` varchar(20) NOT NULL,
  `jam` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwal_mata_pelajaran`
--

INSERT INTO `jadwal_mata_pelajaran` (`id_jadwal`, `id_mapel`, `id_guru`, `id_rooms`, `hari`, `jam`) VALUES
(1, 1, 2, 4, 'Senin', '07.00-10.00'),
(2, 1, 2, 1, 'Senin', '10.00-12.00'),
(3, 3, 13, 12, 'Selasa', '07.00-08.00');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_rooms` bigint(20) UNSIGNED NOT NULL,
  `pararel` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_rooms`, `pararel`) VALUES
(1, 'Kelas 1A'),
(2, 'Kelas 1B'),
(3, 'Kelas 2A'),
(4, 'Kelas 3A'),
(12, 'Kelas 4B');

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id_mapel` bigint(20) UNSIGNED NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`id_mapel`, `nama_mapel`, `deskripsi`) VALUES
(1, 'Matematika', 'Pembelajaran konsep bilangan.'),
(3, 'Ilmu Pengetahuan Alam (IPA)', NULL),
(5, 'Bahasa Inggris', NULL),
(17, 'Matematika', 'Pembelajaran konsep bilangan, operasi hitung, pecahan, dan geometri dasar.'),
(18, 'Ilmu Pengetahuan Alam (IPA)', 'Eksplorasi sains alam, ekosistem, gaya dan gerak, serta tata surya.'),
(19, 'Bahasa Indonesia', 'Membaca pemahaman, tata bahasa, penulisan cerita, dan apresiasi sastra anak.');

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id_materi` bigint(20) UNSIGNED NOT NULL,
  `id_sub_bab` bigint(20) UNSIGNED NOT NULL,
  `id_bab` bigint(20) UNSIGNED NOT NULL,
  `judul_materi` varchar(200) NOT NULL,
  `isi_materi` text DEFAULT NULL,
  `tipe_materi` enum('video','dokumen','kuis') NOT NULL DEFAULT 'video',
  `url_video` varchar(255) DEFAULT NULL,
  `file_pdf` varchar(255) DEFAULT NULL,
  `id_quiz` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_01_01_000001_create_academic_system_tables', 1),
(2, '2026_01_01_000002_add_password_to_guru', 1),
(3, '2026_01_01_000003_add_catatan_revisi_to_rpp', 1),
(4, '0001_01_01_000000_create_users_table', 2),
(5, '0001_01_01_000001_create_cache_table', 2),
(6, '0001_01_01_000002_create_jobs_table', 2),
(7, '2026_01_01_000004_add_deskripsi_to_mata_pelajaran', 2),
(8, '2026_09_20_000000_create_siswas_table', 2),
(9, '2026_09_20_000001_create_barcodes_table', 2),
(10, '2026_09_20_000002_create_absens_table', 2),
(11, '2026_09_22_032036_create_table_guru', 2),
(12, '2026_09_22_032220_create_table_siswa', 2),
(13, '2026_09_23_000001_create_absensi_settings_table', 2),
(14, '2026_10_01_000001_create_learning_assessment_tables', 3),
(15, '2026_10_01_165804_add_details_to_hasil_kuis_siswa_table', 4),
(16, '2026_10_01_180000_add_status_kirim_to_hasil_kuis_siswa', 5);

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE `nilai` (
  `id_nilai` bigint(20) UNSIGNED NOT NULL,
  `id_pr` bigint(20) UNSIGNED DEFAULT NULL,
  `id_quiz` bigint(20) UNSIGNED DEFAULT NULL,
  `id_mapel` bigint(20) UNSIGNED NOT NULL,
  `skor` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` bigint(20) UNSIGNED NOT NULL,
  `id_guru` bigint(20) UNSIGNED DEFAULT NULL,
  `id_siswa` bigint(20) UNSIGNED NOT NULL,
  `id_pr` bigint(20) UNSIGNED DEFAULT NULL,
  `pesan` text NOT NULL,
  `status_baca` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_guru`, `id_siswa`, `id_pr`, `pesan`, `status_baca`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Status RPP \'Test\' diperbarui menjadi: TERVERIFIKASI', 0, '2026-09-23 07:11:42', '2026-09-23 07:11:42'),
(2, 3, 1, NULL, 'Status RPP \'Ekosistem dan Rantai Makanan Dasar\' diperbarui menjadi: TERVERIFIKASI', 0, '2026-09-23 07:18:28', '2026-09-23 07:18:28');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr`
--

CREATE TABLE `pr` (
  `id_pr` bigint(20) UNSIGNED NOT NULL,
  `id_mapel` bigint(20) UNSIGNED NOT NULL,
  `id_guru` bigint(20) UNSIGNED NOT NULL,
  `nama_pr` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tgl_tenggat` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id_quiz` bigint(20) UNSIGNED NOT NULL,
  `id_sub_bab` bigint(20) UNSIGNED DEFAULT NULL,
  `id_materi` bigint(20) UNSIGNED DEFAULT NULL,
  `judul_quiz` varchar(150) DEFAULT NULL,
  `id_mapel` bigint(20) UNSIGNED DEFAULT NULL,
  `id_guru` bigint(20) UNSIGNED DEFAULT NULL,
  `nama_quiz` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpp`
--

CREATE TABLE `rpp` (
  `id_rpp` bigint(20) UNSIGNED NOT NULL,
  `id_guru` bigint(20) UNSIGNED NOT NULL,
  `id_rooms` bigint(20) UNSIGNED DEFAULT NULL,
  `id_mapel` bigint(20) UNSIGNED NOT NULL,
  `judul_rpp` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `komponen_checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`komponen_checklist`)),
  `status` enum('draft','menunggu_review','terverifikasi','perlu_revisi') NOT NULL DEFAULT 'draft',
  `catatan_revisi` text DEFAULT NULL,
  `file_rpp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rpp`
--

INSERT INTO `rpp` (`id_rpp`, `id_guru`, `id_rooms`, `id_mapel`, `judul_rpp`, `deskripsi`, `komponen_checklist`, `status`, `catatan_revisi`, `file_rpp`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Modul Ajar Bilangan Cacah Sampai 100', 'Pengenalan nilai tempat dan penjumlahan sederhana fase A.', '{\"kktp\": true, \"lkpd\": true, \"video\": true, \"tujuan\": true}', 'terverifikasi', NULL, NULL, '2026-09-20 08:31:08', '2026-09-20 08:31:08'),
(3, 3, 3, 3, 'Ekosistem dan Rantai Makanan Dasar', 'Materi IPA kelas 2 tentang makhluk hidup dan lingkungannya.', '{\"kktp\": false, \"lkpd\": true, \"video\": true, \"tujuan\": true}', 'terverifikasi', NULL, NULL, '2026-09-20 08:31:08', '2026-09-23 07:18:28'),
(5, 3, 3, 3, 'Testing', NULL, '{\"kktp\": false, \"lkpd\": false, \"video\": false, \"tujuan\": true}', 'menunggu_review', NULL, 'rpp_files/5jvM200vM1dLr64J8OeBpjxQCXHYGAaocEKNQ3eC.pdf', '2026-09-22 23:10:31', '2026-09-22 23:10:31');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` bigint(20) UNSIGNED NOT NULL,
  `id_mapel` bigint(20) UNSIGNED DEFAULT NULL,
  `id_rooms` bigint(20) UNSIGNED DEFAULT NULL,
  `nisn` varchar(30) NOT NULL,
  `nm_siswa` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_mapel`, `id_rooms`, `nisn`, `nm_siswa`, `no_hp`, `email`, `alamat`, `jenis_kelamin`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '0012345601', 'Aditya Pratama', '08111111111', 'aditya123@gmail.com', NULL, 'L', 'ADIT', 'siswa1234', '2026-09-20 08:31:07', '2026-10-01 07:12:57'),
(2, 1, 1, '0012345602', 'Bella Safitri', '08111111112', NULL, NULL, NULL, NULL, '$2y$12$2u4GwxrHc2aGiPSX.6SQQ./YWEWIE8/lWNJmj7h6FBP6yODKOnxmm', '2026-09-20 08:31:08', NULL),
(3, NULL, 1, '0012345603', 'Candra Wijaya', '08111111113', NULL, NULL, NULL, NULL, '$2y$12$bYLvx6rLyTNJ1IZaE7IupuHch./40YsS0hWxEWyWv9GBSyjQjZf66', '2026-09-20 08:31:08', NULL),
(4, NULL, 2, '0012345604', 'Dina Mariana', '08111111114', NULL, NULL, NULL, NULL, '$2y$12$XDTo3in9iOQd4MVy7x0rFOzzSZNc0aGDyY78Zm6qH62DqHKfjtkc.', '2026-09-20 08:31:08', NULL),
(5, 3, 2, '0012345605', 'Eko Prasetyo', '08111111115', NULL, NULL, NULL, NULL, '$2y$12$ZHnL514lQ0cJZ0q71rfEvOBADEYoQfiwqpKgiTm9jbT78xEPgJJXK', '2026-09-20 08:31:08', NULL),
(7, 1, 12, '0012345678', 'Budi Santoso', '089876543210', 'budi.santoso@siswa.kalitapen01.sch.id', 'Jl. Merdeka No. 45', 'L', 'budisiswa', 'siswa123', '2026-10-02 00:22:07', '2026-10-02 00:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `soal_quiz`
--

CREATE TABLE `soal_quiz` (
  `id_soal` bigint(20) UNSIGNED NOT NULL,
  `id_quiz` bigint(20) UNSIGNED NOT NULL,
  `pertanyaan` text NOT NULL,
  `opsi_a` varchar(255) NOT NULL,
  `opsi_b` varchar(255) NOT NULL,
  `opsi_c` varchar(255) NOT NULL,
  `opsi_d` varchar(255) NOT NULL,
  `kunci_jawaban` enum('A','B','C','D') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `soal_quiz`
--

INSERT INTO `soal_quiz` (`id_soal`, `id_quiz`, `pertanyaan`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `kunci_jawaban`, `created_at`, `updated_at`) VALUES
(9, 4, 'Berapakah hasil dari 2.450 + 1.320?', '3.770', '3.750', '3.870', '3.670', 'A', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(10, 4, 'Angka 7 pada bilangan 5.724 menempati nilai tempat?', 'Satuan', 'Puluhan', 'Ratusan', 'Ribuan', 'C', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(11, 4, 'Berapakah 500 dikalikan 4?', '1.500', '2.000', '2.500', '3.000', 'B', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(12, 4, 'Hasil dari 1.000 - 375 adalah?', '625', '635', '725', '525', 'A', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(13, 4, 'Bilangan genap antara 11 dan 15 adalah?', '12 dan 13', '12 dan 14', '13 dan 14', '14 dan 15', 'B', '2026-09-30 04:41:30', '2026-09-30 04:41:30'),
(14, 4, 'Berapakah 8 x 7?', '54', '56', '58', '60', 'B', '2026-09-30 04:41:30', '2026-09-30 04:41:30');

-- --------------------------------------------------------

--
-- Table structure for table `sub_bab`
--

CREATE TABLE `sub_bab` (
  `id_sub_bab` bigint(20) UNSIGNED NOT NULL,
  `id_bab` bigint(20) UNSIGNED NOT NULL,
  `nama_sub_bab` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
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
  ADD UNIQUE KEY `guru_nip_unique` (`nip`),
  ADD UNIQUE KEY `guru_email_unique` (`email`),
  ADD UNIQUE KEY `guru_username_unique` (`username`);

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
-- Indexes for table `rpp`
--
ALTER TABLE `rpp`
  ADD PRIMARY KEY (`id_rpp`),
  ADD KEY `rpp_id_guru_foreign` (`id_guru`),
  ADD KEY `rpp_id_rooms_foreign` (`id_rooms`),
  ADD KEY `rpp_id_mapel_foreign` (`id_mapel`);

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
  ADD UNIQUE KEY `siswa_email_unique` (`email`),
  ADD UNIQUE KEY `siswa_username_unique` (`username`),
  ADD KEY `siswa_id_mapel_foreign` (`id_mapel`),
  ADD KEY `siswa_id_rooms_foreign` (`id_rooms`);

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
  MODIFY `id_absen` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `absensi_settings`
--
ALTER TABLE `absensi_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bab`
--
ALTER TABLE `bab`
  MODIFY `id_bab` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `barcode`
--
ALTER TABLE `barcode`
  MODIFY `id_barcode` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `hasil_nilai`
--
ALTER TABLE `hasil_nilai`
  MODIFY `id_hasil_nilai` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_mata_pelajaran`
--
ALTER TABLE `jadwal_mata_pelajaran`
  MODIFY `id_jadwal` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_rooms` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id_mapel` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id_materi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
  MODIFY `id_nilai` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pr`
--
ALTER TABLE `pr`
  MODIFY `id_pr` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id_quiz` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpp`
--
ALTER TABLE `rpp`
  MODIFY `id_rpp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sub_bab`
--
ALTER TABLE `sub_bab`
  MODIFY `id_sub_bab` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
