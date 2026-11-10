-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 18, 2026 at 02:57 PM
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
CREATE DATABASE IF NOT EXISTS `project_smster_331` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `project_smster_331`;

-- --------------------------------------------------------

--
-- Table structure for table `absen`
--

DROP TABLE IF EXISTS `absen`;
CREATE TABLE IF NOT EXISTS `absen` (
  `id_absen` int NOT NULL AUTO_INCREMENT,
  `id_guru` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_barcode` int NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpa') DEFAULT 'Hadir',
  `waktu_absen` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_absen`),
  KEY `id_guru` (`id_guru`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_barcode` (`id_barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `nip` (`nip`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `nip`, `username`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Admin Utama', '', 'admin', 'admin123', '2026-09-18 07:19:35', '2026-09-18 07:19:35');

-- --------------------------------------------------------

--
-- Table structure for table `bab`
--

DROP TABLE IF EXISTS `bab`;
CREATE TABLE IF NOT EXISTS `bab` (
  `id_bab` int NOT NULL AUTO_INCREMENT,
  `id_mapel` int NOT NULL,
  `nama_bab` varchar(150) NOT NULL,
  PRIMARY KEY (`id_bab`),
  KEY `id_mapel` (`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barcode`
--

DROP TABLE IF EXISTS `barcode`;
CREATE TABLE IF NOT EXISTS `barcode` (
  `id_barcode` int NOT NULL AUTO_INCREMENT,
  `id_siswa` int NOT NULL,
  `kode_barcode` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_barcode`),
  UNIQUE KEY `kode_barcode` (`kode_barcode`),
  KEY `id_siswa` (`id_siswa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

DROP TABLE IF EXISTS `guru`;
CREATE TABLE IF NOT EXISTS `guru` (
  `id_guru` int NOT NULL AUTO_INCREMENT,
  `nip` varchar(30) NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `nip` (`nip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_nilai`
--

DROP TABLE IF EXISTS `hasil_nilai`;
CREATE TABLE IF NOT EXISTS `hasil_nilai` (
  `id_hasil_nilai` int NOT NULL AUTO_INCREMENT,
  `id_nilai` int NOT NULL,
  `id_mapel` int NOT NULL,
  `data_nilai` varchar(100) DEFAULT NULL,
  `total_nilai` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`id_hasil_nilai`),
  KEY `id_nilai` (`id_nilai`),
  KEY `id_mapel` (`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_mata_pelajaran`
--

DROP TABLE IF EXISTS `jadwal_mata_pelajaran`;
CREATE TABLE IF NOT EXISTS `jadwal_mata_pelajaran` (
  `id_jadwal` int NOT NULL AUTO_INCREMENT,
  `id_mapel` int NOT NULL,
  `id_guru` int NOT NULL,
  `id_kelas` int DEFAULT NULL,
  `hari` varchar(20) NOT NULL,
  `jam` varchar(50) NOT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_guru` (`id_guru`),
  KEY `fk_jadwal_kelas` (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

DROP TABLE IF EXISTS `kelas`;
CREATE TABLE IF NOT EXISTS `kelas` (
  `id_kelas` int NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kelas`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `created_at`, `updated_at`) VALUES
(1, 'XI RPL 1', '2026-09-18 07:19:35', '2026-09-18 07:19:35'),
(2, 'XI RPL 2', '2026-09-18 07:19:35', '2026-09-18 07:19:35'),
(3, 'XII TKJ 1', '2026-09-18 07:19:35', '2026-09-18 07:19:35');

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

DROP TABLE IF EXISTS `mata_pelajaran`;
CREATE TABLE IF NOT EXISTS `mata_pelajaran` (
  `id_mapel` int NOT NULL AUTO_INCREMENT,
  `nama_mapel` varchar(100) NOT NULL,
  PRIMARY KEY (`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

DROP TABLE IF EXISTS `materi`;
CREATE TABLE IF NOT EXISTS `materi` (
  `id_materi` int NOT NULL AUTO_INCREMENT,
  `id_mapel` int DEFAULT NULL,
  `id_sub_bab` int NOT NULL,
  `id_guru` int NOT NULL,
  `judul_materi` varchar(200) NOT NULL,
  `konten` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_materi`),
  KEY `id_sub_bab` (`id_sub_bab`),
  KEY `id_guru` (`id_guru`),
  KEY `fk_materi_mapel` (`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

DROP TABLE IF EXISTS `nilai`;
CREATE TABLE IF NOT EXISTS `nilai` (
  `id_nilai` int NOT NULL AUTO_INCREMENT,
  `id_pr` int DEFAULT NULL,
  `id_quiz` int DEFAULT NULL,
  `id_mapel` int NOT NULL,
  `skor` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_nilai`),
  KEY `id_pr` (`id_pr`),
  KEY `id_quiz` (`id_quiz`),
  KEY `id_mapel` (`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id_notifikasi` int NOT NULL AUTO_INCREMENT,
  `id_guru` int DEFAULT NULL,
  `id_siswa` int NOT NULL,
  `id_pr` int DEFAULT NULL,
  `pesan` text NOT NULL,
  `status_baca` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notifikasi`),
  KEY `id_guru` (`id_guru`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_pr` (`id_pr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr`
--

DROP TABLE IF EXISTS `pr`;
CREATE TABLE IF NOT EXISTS `pr` (
  `id_pr` int NOT NULL AUTO_INCREMENT,
  `id_mapel` int NOT NULL,
  `id_guru` int NOT NULL,
  `nama_pr` varchar(150) NOT NULL,
  `deskripsi` text,
  `tgl_tenggat` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pr`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_guru` (`id_guru`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

DROP TABLE IF EXISTS `quiz`;
CREATE TABLE IF NOT EXISTS `quiz` (
  `id_quiz` int NOT NULL AUTO_INCREMENT,
  `id_mapel` int NOT NULL,
  `id_guru` int NOT NULL,
  `nama_quiz` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_quiz`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_guru` (`id_guru`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpp`
--

DROP TABLE IF EXISTS `rpp`;
CREATE TABLE IF NOT EXISTS `rpp` (
  `id_rpp` int NOT NULL AUTO_INCREMENT,
  `id_guru` int NOT NULL,
  `judul_rpp` varchar(150) NOT NULL,
  `deskripsi` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rpp`),
  KEY `id_guru` (`id_guru`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

DROP TABLE IF EXISTS `siswa`;
CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa` int NOT NULL AUTO_INCREMENT,
  `id_mapel` int DEFAULT NULL,
  `id_kelas` int DEFAULT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `nis` (`nis`),
  KEY `fk_siswa_mapel` (`id_mapel`),
  KEY `fk_siswa_kelas` (`id_kelas`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_mapel`, `id_kelas`, `nis`, `nama`, `password`, `foto`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, '2024001', 'Ahmad Siswa', '123456', NULL, '2026-09-18 07:19:36', '2026-09-18 07:19:36'),
(2, NULL, 2, '2024002', 'Budi Santoso', '123456', NULL, '2026-09-18 07:19:36', '2026-09-18 07:19:36');

-- --------------------------------------------------------

--
-- Table structure for table `sub_bab`
--

DROP TABLE IF EXISTS `sub_bab`;
CREATE TABLE IF NOT EXISTS `sub_bab` (
  `id_sub_bab` int NOT NULL AUTO_INCREMENT,
  `id_bab` int NOT NULL,
  `nama_sub_bab` varchar(150) NOT NULL,
  PRIMARY KEY (`id_sub_bab`),
  KEY `id_bab` (`id_bab`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absen`
--
ALTER TABLE `absen`
  ADD CONSTRAINT `absen_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE,
  ADD CONSTRAINT `absen_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `absen_ibfk_3` FOREIGN KEY (`id_barcode`) REFERENCES `barcode` (`id_barcode`) ON DELETE CASCADE;

--
-- Constraints for table `bab`
--
ALTER TABLE `bab`
  ADD CONSTRAINT `bab_ibfk_1` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE;

--
-- Constraints for table `barcode`
--
ALTER TABLE `barcode`
  ADD CONSTRAINT `barcode_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

--
-- Constraints for table `hasil_nilai`
--
ALTER TABLE `hasil_nilai`
  ADD CONSTRAINT `hasil_nilai_ibfk_1` FOREIGN KEY (`id_nilai`) REFERENCES `nilai` (`id_nilai`) ON DELETE CASCADE,
  ADD CONSTRAINT `hasil_nilai_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_mata_pelajaran`
--
ALTER TABLE `jadwal_mata_pelajaran`
  ADD CONSTRAINT `fk_jadwal_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jadwal_mata_pelajaran_ibfk_1` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_mata_pelajaran_ibfk_2` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE;

--
-- Constraints for table `materi`
--
ALTER TABLE `materi`
  ADD CONSTRAINT `fk_materi_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `materi_ibfk_1` FOREIGN KEY (`id_sub_bab`) REFERENCES `sub_bab` (`id_sub_bab`) ON DELETE CASCADE,
  ADD CONSTRAINT `materi_ibfk_2` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE;

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`id_pr`) REFERENCES `pr` (`id_pr`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id_quiz`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_3` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifikasi_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifikasi_ibfk_3` FOREIGN KEY (`id_pr`) REFERENCES `pr` (`id_pr`) ON DELETE SET NULL;

--
-- Constraints for table `pr`
--
ALTER TABLE `pr`
  ADD CONSTRAINT `pr_ibfk_1` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `pr_ibfk_2` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE;

--
-- Constraints for table `rpp`
--
ALTER TABLE `rpp`
  ADD CONSTRAINT `rpp_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sub_bab`
--
ALTER TABLE `sub_bab`
  ADD CONSTRAINT `sub_bab_ibfk_1` FOREIGN KEY (`id_bab`) REFERENCES `bab` (`id_bab`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
