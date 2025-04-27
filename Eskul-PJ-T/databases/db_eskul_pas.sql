-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2025 at 10:05 PM
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
-- Database: `db_eskul_pas`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `HitungKehadiran_pas` (IN `p_eskul_id` INT, IN `p_siswa_id` INT, OUT `persentase_kehadiran` DECIMAL(5,2))   BEGIN
    DECLARE total_sesi INT;
    DECLARE sesi_hadir INT;

    SELECT COUNT(*) INTO total_sesi
    FROM absensi_pas
    WHERE eskul_id_pas = p_eskul_id AND siswa_id_pas = p_siswa_id;

    SELECT COUNT(*) INTO sesi_hadir
    FROM absensi_pas
    WHERE eskul_id_pas = p_eskul_id AND siswa_id_pas = p_siswa_id AND status_pas = 'Hadir';

    IF total_sesi > 0 THEN
        SET persentase_kehadiran = (sesi_hadir / total_sesi) * 100;
    ELSE
        SET persentase_kehadiran = 0;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `HitungRataRataNilai_pas` (IN `p_eskul_id` INT, IN `p_siswa_id` INT, OUT `rata_rata_nilai` DECIMAL(5,2))   BEGIN
    DECLARE total_nilai DECIMAL(10,2);
    DECLARE jumlah_penilaian INT;

    SELECT SUM(nilai_pas), COUNT(*) INTO total_nilai, jumlah_penilaian
    FROM nilai_pas
    WHERE eskul_id_pas = p_eskul_id AND siswa_id_pas = p_siswa_id;

    IF jumlah_penilaian > 0 THEN
        SET rata_rata_nilai = total_nilai / jumlah_penilaian;
    ELSE
        SET rata_rata_nilai = 0;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TambahAbsensi_pas` (IN `p_eskul_id` INT, IN `p_siswa_id` INT, IN `p_tanggal` DATE, IN `p_status` ENUM('Hadir','Absen','Terlambat','Izin'), IN `p_keterangan` TEXT)   BEGIN
    INSERT INTO absensi_pas (eskul_id_pas, siswa_id_pas, tanggal_pas, status_pas, keterangan_absen_pas)
    VALUES (p_eskul_id, p_siswa_id, p_tanggal, p_status, p_keterangan);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TambahSiswaEskul_pas` (IN `p_eskul_id` INT, IN `p_siswa_id` INT)   BEGIN
    IF NOT EXISTS (SELECT * FROM eskul_siswa_pas WHERE eskul_id_pas = p_eskul_id AND siswa_id_pas = p_siswa_id) THEN
        INSERT INTO eskul_siswa_pas (eskul_id_pas, siswa_id_pas, status_pas)
        VALUES (p_eskul_id, p_siswa_id, 'Aktif');
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `absensi_pas`
--

CREATE TABLE `absensi_pas` (
  `absensi_id_pas` int(11) NOT NULL,
  `eskul_id_pas` int(11) NOT NULL,
  `siswa_id_pas` int(11) NOT NULL,
  `tanggal_pas` date NOT NULL,
  `status_pas` enum('Hadir','Absen','Terlambat','Izin') NOT NULL,
  `keterangan_absen_pas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_guru_pas`
--

CREATE TABLE `eskul_guru_pas` (
  `id_pas` int(11) NOT NULL,
  `eskul_id_pas` int(11) NOT NULL,
  `guru_id_pas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_pas`
--

CREATE TABLE `eskul_pas` (
  `eskul_id_pas` int(11) NOT NULL,
  `nama_pas` varchar(100) NOT NULL,
  `deskripsi_pas` text DEFAULT NULL,
  `tanggal_dibuat_pas` datetime DEFAULT current_timestamp(),
  `kuota_pas` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_siswa_pas`
--

CREATE TABLE `eskul_siswa_pas` (
  `id_pas` int(11) NOT NULL,
  `eskul_id_pas` int(11) NOT NULL,
  `siswa_id_pas` int(11) NOT NULL,
  `status_pas` enum('Aktif','Keluar') DEFAULT 'Aktif',
  `tanggal_pendaftaran_pas` datetime DEFAULT current_timestamp(),
  `status_pendaftaran_pas` enum('Pending','Diterima','Ditolak') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `eskul_siswa_pas`
--
DELIMITER $$
CREATE TRIGGER `update_status_siswa_pas` AFTER UPDATE ON `eskul_siswa_pas` FOR EACH ROW BEGIN
    IF OLD.status_pas = 'Aktif' AND NEW.status_pas = 'Keluar' THEN
        DELETE FROM absensi_pas WHERE eskul_id_pas = OLD.eskul_id_pas AND siswa_id_pas = OLD.siswa_id_pas;
        DELETE FROM nilai_pas WHERE eskul_id_pas = OLD.eskul_id_pas AND siswa_id_pas = OLD.siswa_id_pas;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `guru_pas`
--

CREATE TABLE `guru_pas` (
  `guru_id_pas` int(11) NOT NULL,
  `nama_pas` varchar(100) NOT NULL,
  `email_pas` varchar(100) DEFAULT NULL,
  `telepon_pas` varchar(15) DEFAULT NULL,
  `user_id_pas` int(11) DEFAULT NULL,
  `foto_profil_pas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `guru_pas`
--
DELIMITER $$
CREATE TRIGGER `hapus_guru_pas` BEFORE DELETE ON `guru_pas` FOR EACH ROW BEGIN
    DELETE FROM eskul_guru_pas WHERE guru_id_pas = OLD.guru_id_pas;
    DELETE FROM pengumuman_pas WHERE guru_id_pas = OLD.guru_id_pas;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_eskul_pas`
--

CREATE TABLE `jadwal_eskul_pas` (
  `jadwal_id_pas` int(11) NOT NULL,
  `eskul_id_pas` int(11) NOT NULL,
  `hari_pas` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai_pas` time NOT NULL,
  `jam_selesai_pas` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas_pas`
--

CREATE TABLE `log_aktivitas_pas` (
  `log_id_pas` int(11) NOT NULL,
  `user_id_pas` int(11) NOT NULL,
  `aktivitas_pas` text NOT NULL,
  `timestamp_pas` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_pas`
--

CREATE TABLE `nilai_pas` (
  `nilai_id_pas` int(11) NOT NULL,
  `eskul_id_pas` int(11) NOT NULL,
  `siswa_id_pas` int(11) NOT NULL,
  `nilai_pas` decimal(5,2) NOT NULL,
  `keterangan_pas` text DEFAULT NULL,
  `kategori_penilaian_pas` enum('Pengetahuan','Keterampilan','Sikap') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman_pas`
--

CREATE TABLE `pengumuman_pas` (
  `pengumuman_id_pas` int(11) NOT NULL,
  `eskul_id_pas` int(11) NOT NULL,
  `guru_id_pas` int(11) NOT NULL,
  `judul_pas` varchar(255) NOT NULL,
  `isi_pas` text NOT NULL,
  `tanggal_dibuat_pas` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profil_data`
--

CREATE TABLE `profil_data` (
  `profil_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan','Lainnya') DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa_pas`
--

CREATE TABLE `siswa_pas` (
  `siswa_id_pas` int(11) NOT NULL,
  `nama_pas` varchar(100) NOT NULL,
  `kelas_pas` varchar(50) DEFAULT NULL,
  `email_pas` varchar(100) DEFAULT NULL,
  `telepon_pas` varchar(15) DEFAULT NULL,
  `user_id_pas` int(11) DEFAULT NULL,
  `foto_profil_pas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `siswa_pas`
--
DELIMITER $$
CREATE TRIGGER `hapus_siswa_pas` BEFORE DELETE ON `siswa_pas` FOR EACH ROW BEGIN
    DELETE FROM eskul_siswa_pas WHERE siswa_id_pas = OLD.siswa_id_pas;
    DELETE FROM absensi_pas WHERE siswa_id_pas = OLD.siswa_id_pas;
    DELETE FROM nilai_pas WHERE siswa_id_pas = OLD.siswa_id_pas;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users_pas`
--

CREATE TABLE `users_pas` (
  `user_id_pas` int(11) NOT NULL,
  `username_pas` varchar(50) NOT NULL,
  `password_pas` varchar(255) NOT NULL,
  `nama_lengkap_pas` varchar(100) NOT NULL,
  `peran_pas` enum('admin','guru','siswa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `users_pas`
--
DELIMITER $$
CREATE TRIGGER `hapus_user_pas` BEFORE DELETE ON `users_pas` FOR EACH ROW BEGIN
    DELETE FROM profil_data WHERE user_id = OLD.user_id_pas;
    DELETE FROM log_aktivitas_pas WHERE user_id_pas = OLD.user_id_pas;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi_pas`
--
ALTER TABLE `absensi_pas`
  ADD PRIMARY KEY (`absensi_id_pas`),
  ADD KEY `eskul_id_pas` (`eskul_id_pas`),
  ADD KEY `siswa_id_pas` (`siswa_id_pas`);

--
-- Indexes for table `eskul_guru_pas`
--
ALTER TABLE `eskul_guru_pas`
  ADD PRIMARY KEY (`id_pas`),
  ADD KEY `eskul_id_pas` (`eskul_id_pas`),
  ADD KEY `guru_id_pas` (`guru_id_pas`);

--
-- Indexes for table `eskul_pas`
--
ALTER TABLE `eskul_pas`
  ADD PRIMARY KEY (`eskul_id_pas`);

--
-- Indexes for table `eskul_siswa_pas`
--
ALTER TABLE `eskul_siswa_pas`
  ADD PRIMARY KEY (`id_pas`),
  ADD KEY `eskul_id_pas` (`eskul_id_pas`),
  ADD KEY `siswa_id_pas` (`siswa_id_pas`);

--
-- Indexes for table `guru_pas`
--
ALTER TABLE `guru_pas`
  ADD PRIMARY KEY (`guru_id_pas`),
  ADD UNIQUE KEY `email_pas` (`email_pas`),
  ADD UNIQUE KEY `user_id_pas` (`user_id_pas`);

--
-- Indexes for table `jadwal_eskul_pas`
--
ALTER TABLE `jadwal_eskul_pas`
  ADD PRIMARY KEY (`jadwal_id_pas`),
  ADD KEY `eskul_id_pas` (`eskul_id_pas`);

--
-- Indexes for table `log_aktivitas_pas`
--
ALTER TABLE `log_aktivitas_pas`
  ADD PRIMARY KEY (`log_id_pas`),
  ADD KEY `user_id_pas` (`user_id_pas`);

--
-- Indexes for table `nilai_pas`
--
ALTER TABLE `nilai_pas`
  ADD PRIMARY KEY (`nilai_id_pas`),
  ADD KEY `eskul_id_pas` (`eskul_id_pas`),
  ADD KEY `siswa_id_pas` (`siswa_id_pas`);

--
-- Indexes for table `pengumuman_pas`
--
ALTER TABLE `pengumuman_pas`
  ADD PRIMARY KEY (`pengumuman_id_pas`),
  ADD KEY `eskul_id_pas` (`eskul_id_pas`),
  ADD KEY `guru_id_pas` (`guru_id_pas`);

--
-- Indexes for table `profil_data`
--
ALTER TABLE `profil_data`
  ADD PRIMARY KEY (`profil_id`),
  ADD KEY `fk_profil_user` (`user_id`);

--
-- Indexes for table `siswa_pas`
--
ALTER TABLE `siswa_pas`
  ADD PRIMARY KEY (`siswa_id_pas`),
  ADD UNIQUE KEY `email_pas` (`email_pas`),
  ADD UNIQUE KEY `user_id_pas` (`user_id_pas`);

--
-- Indexes for table `users_pas`
--
ALTER TABLE `users_pas`
  ADD PRIMARY KEY (`user_id_pas`),
  ADD UNIQUE KEY `username_pas` (`username_pas`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi_pas`
--
ALTER TABLE `absensi_pas`
  MODIFY `absensi_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_guru_pas`
--
ALTER TABLE `eskul_guru_pas`
  MODIFY `id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_pas`
--
ALTER TABLE `eskul_pas`
  MODIFY `eskul_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_siswa_pas`
--
ALTER TABLE `eskul_siswa_pas`
  MODIFY `id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guru_pas`
--
ALTER TABLE `guru_pas`
  MODIFY `guru_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_eskul_pas`
--
ALTER TABLE `jadwal_eskul_pas`
  MODIFY `jadwal_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_aktivitas_pas`
--
ALTER TABLE `log_aktivitas_pas`
  MODIFY `log_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_pas`
--
ALTER TABLE `nilai_pas`
  MODIFY `nilai_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumuman_pas`
--
ALTER TABLE `pengumuman_pas`
  MODIFY `pengumuman_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profil_data`
--
ALTER TABLE `profil_data`
  MODIFY `profil_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa_pas`
--
ALTER TABLE `siswa_pas`
  MODIFY `siswa_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_pas`
--
ALTER TABLE `users_pas`
  MODIFY `user_id_pas` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi_pas`
--
ALTER TABLE `absensi_pas`
  ADD CONSTRAINT `absensi_pas_ibfk_1` FOREIGN KEY (`eskul_id_pas`) REFERENCES `eskul_pas` (`eskul_id_pas`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_pas_ibfk_2` FOREIGN KEY (`siswa_id_pas`) REFERENCES `siswa_pas` (`siswa_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `eskul_guru_pas`
--
ALTER TABLE `eskul_guru_pas`
  ADD CONSTRAINT `eskul_guru_pas_ibfk_1` FOREIGN KEY (`eskul_id_pas`) REFERENCES `eskul_pas` (`eskul_id_pas`) ON DELETE CASCADE,
  ADD CONSTRAINT `eskul_guru_pas_ibfk_2` FOREIGN KEY (`guru_id_pas`) REFERENCES `guru_pas` (`guru_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `eskul_siswa_pas`
--
ALTER TABLE `eskul_siswa_pas`
  ADD CONSTRAINT `eskul_siswa_pas_ibfk_1` FOREIGN KEY (`eskul_id_pas`) REFERENCES `eskul_pas` (`eskul_id_pas`) ON DELETE CASCADE,
  ADD CONSTRAINT `eskul_siswa_pas_ibfk_2` FOREIGN KEY (`siswa_id_pas`) REFERENCES `siswa_pas` (`siswa_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `guru_pas`
--
ALTER TABLE `guru_pas`
  ADD CONSTRAINT `guru_pas_ibfk_1` FOREIGN KEY (`user_id_pas`) REFERENCES `users_pas` (`user_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_eskul_pas`
--
ALTER TABLE `jadwal_eskul_pas`
  ADD CONSTRAINT `jadwal_eskul_pas_ibfk_1` FOREIGN KEY (`eskul_id_pas`) REFERENCES `eskul_pas` (`eskul_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `log_aktivitas_pas`
--
ALTER TABLE `log_aktivitas_pas`
  ADD CONSTRAINT `log_aktivitas_pas_ibfk_1` FOREIGN KEY (`user_id_pas`) REFERENCES `users_pas` (`user_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `nilai_pas`
--
ALTER TABLE `nilai_pas`
  ADD CONSTRAINT `nilai_pas_ibfk_1` FOREIGN KEY (`eskul_id_pas`) REFERENCES `eskul_pas` (`eskul_id_pas`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_pas_ibfk_2` FOREIGN KEY (`siswa_id_pas`) REFERENCES `siswa_pas` (`siswa_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `pengumuman_pas`
--
ALTER TABLE `pengumuman_pas`
  ADD CONSTRAINT `pengumuman_pas_ibfk_1` FOREIGN KEY (`eskul_id_pas`) REFERENCES `eskul_pas` (`eskul_id_pas`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengumuman_pas_ibfk_2` FOREIGN KEY (`guru_id_pas`) REFERENCES `guru_pas` (`guru_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `profil_data`
--
ALTER TABLE `profil_data`
  ADD CONSTRAINT `fk_profil_user` FOREIGN KEY (`user_id`) REFERENCES `users_pas` (`user_id_pas`) ON DELETE CASCADE;

--
-- Constraints for table `siswa_pas`
--
ALTER TABLE `siswa_pas`
  ADD CONSTRAINT `siswa_pas_ibfk_1` FOREIGN KEY (`user_id_pas`) REFERENCES `users_pas` (`user_id_pas`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
