-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2026 at 03:44 AM
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

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_id_mapel_foreign` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE SET NULL,
  ADD CONSTRAINT `siswa_id_rooms_foreign` FOREIGN KEY (`id_rooms`) REFERENCES `kelas` (`id_rooms`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
