-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2025 at 08:06 AM
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
-- Database: `db_eskul_pasha`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `status` enum('Hadir','Izin','Alpa','Sakit') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `attendance`
--
DELIMITER $$
CREATE TRIGGER `trg_nonaktifkan_siswa` AFTER INSERT ON `attendance` FOR EACH ROW BEGIN
  DECLARE alpa_count INT;

  -- Hitung jumlah Alpa untuk siswa dan ekskul yang sama (berdasarkan schedule)
  SELECT COUNT(*)
  INTO alpa_count
  FROM attendance a
  JOIN eskul_schedule es ON a.schedule_id = es.schedule_id
  WHERE a.student_id = NEW.student_id
    AND a.status = 'Alpa'
    AND es.eskul_id = (SELECT eskul_id FROM eskul_schedule WHERE schedule_id = NEW.schedule_id)
    AND es.semester = (SELECT semester FROM eskul_schedule WHERE schedule_id = NEW.schedule_id)
    AND es.tahun_ajaran = (SELECT tahun_ajaran FROM eskul_schedule WHERE schedule_id = NEW.schedule_id);

  -- Jika 3 kali alpa, ubah status menjadi Nonaktif
  IF alpa_count >= 3 THEN
    UPDATE student_eskul
    SET status = 'Nonaktif',
        tanggal_berhenti = CURRENT_DATE()
    WHERE student_id = NEW.student_id
      AND eskul_id = (SELECT eskul_id FROM eskul_schedule WHERE schedule_id = NEW.schedule_id)
      AND status = 'Aktif';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `eskul`
--

CREATE TABLE `eskul` (
  `eskul_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_schedule`
--

CREATE TABLE `eskul_schedule` (
  `schedule_id` int(11) NOT NULL,
  `eskul_id` int(11) DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `tahun_ajaran` varchar(10) DEFAULT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_students`
--

CREATE TABLE `eskul_students` (
  `id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_teachers`
--

CREATE TABLE `eskul_teachers` (
  `id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `kelas_id` int(11) NOT NULL,
  `nama_kelas` varchar(100) DEFAULT NULL,
  `tingkat` enum('X','XI','XII') DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `tahun_ajaran` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_eskul`
--

CREATE TABLE `penilaian_eskul` (
  `penilaian_id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `tahun_ajaran` varchar(9) NOT NULL,
  `nilai_akhir` enum('Kurang','Cukup','Baik','Sangat Baik') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_keaktifan`
--

CREATE TABLE `penilaian_keaktifan` (
  `keaktifan_id` int(11) NOT NULL,
  `penilaian_id` int(11) DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_komentar`
--

CREATE TABLE `penilaian_komentar` (
  `komentar_id` int(11) NOT NULL,
  `penilaian_id` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_rekomendasi`
--

CREATE TABLE `penilaian_rekomendasi` (
  `rekomendasi_id` int(11) NOT NULL,
  `penilaian_id` int(11) DEFAULT NULL,
  `rekomendasi` enum('Kurang','Cukup','Baik','Sangat Baik') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prestasi`
--

CREATE TABLE `prestasi` (
  `prestasi_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `eskul_id` int(11) DEFAULT NULL,
  `judul_prestasi` varchar(255) DEFAULT NULL,
  `tingkat` enum('Sekolah','Kota','Provinsi','Nasional','Internasional') DEFAULT NULL,
  `poin` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `tahun_ajaran` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `prestasi`
--
DELIMITER $$
CREATE TRIGGER `set_prestasi_poin` BEFORE INSERT ON `prestasi` FOR EACH ROW BEGIN
    SET NEW.poin = CASE NEW.tingkat
        WHEN 'Sekolah' THEN 1
        WHEN 'Kota' THEN 2
        WHEN 'Provinsi' THEN 3
        ELSE 4  -- Nasional dan Internasional jadi 4
    END;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `rekap_penilaian_eskul`
-- (See below for the actual view)
--
CREATE TABLE `rekap_penilaian_eskul` (
`student_id` int(11)
,`eskul_id` int(11)
,`nama_siswa` varchar(100)
,`nama_eskul` varchar(100)
,`komentar` text
,`rekomendasi` enum('Kurang','Cukup','Baik','Sangat Baik')
,`nilai_keaktifan` int(11)
,`semester` enum('Ganjil','Genap')
,`tahun_ajaran` varchar(9)
);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_eskul`
--

CREATE TABLE `student_eskul` (
  `student_eskul_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `eskul_id` int(11) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_berhenti` date DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'fotos/profile/default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `profile_picture`) VALUES
(1, 'ADMIN', '$2y$10$1LX3C9DfgRXHRZTkHf1SR.h6S8bH5ztUn6VNC4unY2VnWkXHaS.D6', 'Kang Admin', 'admin', 'fotos/profile/default.png\r\n');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_delete` AFTER DELETE ON `users` FOR EACH ROW BEGIN
    -- Hapus dari students
    DELETE FROM students
    WHERE user_id = OLD.id;

    -- Hapus dari teachers
    DELETE FROM teachers
    WHERE user_id = OLD.id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_insert_student` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'student' THEN
        INSERT INTO students 
            (name,email, phone, user_id)
        VALUES
            (NEW.full_name,'Belum diatur', 'Belum diatur', NEW.id);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_insert_teacher` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'teacher' THEN
        INSERT INTO teachers 
            (name, email, phone, user_id)
        VALUES
            (NEW.full_name, 'Belum diatur', 'Belum diatur', NEW.id);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_update_name` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF OLD.full_name != NEW.full_name THEN
        -- Update nama di students
        UPDATE students
        SET name = NEW.full_name
        WHERE user_id = NEW.id;

        -- Update nama di teachers
        UPDATE teachers
        SET name = NEW.full_name
        WHERE user_id = NEW.id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `rekap_penilaian_eskul`
--
DROP TABLE IF EXISTS `rekap_penilaian_eskul`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `rekap_penilaian_eskul`  AS SELECT `pe`.`student_id` AS `student_id`, `pe`.`eskul_id` AS `eskul_id`, `u`.`full_name` AS `nama_siswa`, `e`.`name` AS `nama_eskul`, `pk`.`komentar` AS `komentar`, `pr`.`rekomendasi` AS `rekomendasi`, `pkf`.`nilai` AS `nilai_keaktifan`, `pe`.`semester` AS `semester`, `pe`.`tahun_ajaran` AS `tahun_ajaran` FROM ((((((`penilaian_eskul` `pe` left join `students` `s` on(`pe`.`student_id` = `s`.`student_id`)) left join `users` `u` on(`s`.`user_id` = `u`.`id`)) left join `eskul` `e` on(`pe`.`eskul_id` = `e`.`eskul_id`)) left join `penilaian_komentar` `pk` on(`pe`.`penilaian_id` = `pk`.`penilaian_id`)) left join `penilaian_rekomendasi` `pr` on(`pe`.`penilaian_id` = `pr`.`penilaian_id`)) left join `penilaian_keaktifan` `pkf` on(`pe`.`penilaian_id` = `pkf`.`penilaian_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `eskul`
--
ALTER TABLE `eskul`
  ADD PRIMARY KEY (`eskul_id`);

--
-- Indexes for table `eskul_schedule`
--
ALTER TABLE `eskul_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `eskul_id` (`eskul_id`);

--
-- Indexes for table `eskul_students`
--
ALTER TABLE `eskul_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eskul_id` (`eskul_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `eskul_teachers`
--
ALTER TABLE `eskul_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eskul_id` (`eskul_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`kelas_id`);

--
-- Indexes for table `penilaian_eskul`
--
ALTER TABLE `penilaian_eskul`
  ADD PRIMARY KEY (`penilaian_id`),
  ADD KEY `eskul_id` (`eskul_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `penilaian_keaktifan`
--
ALTER TABLE `penilaian_keaktifan`
  ADD PRIMARY KEY (`keaktifan_id`),
  ADD KEY `penilaian_id` (`penilaian_id`);

--
-- Indexes for table `penilaian_komentar`
--
ALTER TABLE `penilaian_komentar`
  ADD PRIMARY KEY (`komentar_id`),
  ADD KEY `penilaian_id` (`penilaian_id`);

--
-- Indexes for table `penilaian_rekomendasi`
--
ALTER TABLE `penilaian_rekomendasi`
  ADD PRIMARY KEY (`rekomendasi_id`),
  ADD KEY `penilaian_id` (`penilaian_id`);

--
-- Indexes for table `prestasi`
--
ALTER TABLE `prestasi`
  ADD PRIMARY KEY (`prestasi_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `eskul_id` (`eskul_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `student_eskul`
--
ALTER TABLE `student_eskul`
  ADD PRIMARY KEY (`student_eskul_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `eskul_id` (`eskul_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `eskul`
--
ALTER TABLE `eskul`
  MODIFY `eskul_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `eskul_schedule`
--
ALTER TABLE `eskul_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `eskul_students`
--
ALTER TABLE `eskul_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `eskul_teachers`
--
ALTER TABLE `eskul_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `kelas_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penilaian_eskul`
--
ALTER TABLE `penilaian_eskul`
  MODIFY `penilaian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `penilaian_keaktifan`
--
ALTER TABLE `penilaian_keaktifan`
  MODIFY `keaktifan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penilaian_komentar`
--
ALTER TABLE `penilaian_komentar`
  MODIFY `komentar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penilaian_rekomendasi`
--
ALTER TABLE `penilaian_rekomendasi`
  MODIFY `rekomendasi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prestasi`
--
ALTER TABLE `prestasi`
  MODIFY `prestasi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_eskul`
--
ALTER TABLE `student_eskul`
  MODIFY `student_eskul_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `eskul_schedule` (`schedule_id`) ON DELETE CASCADE;

--
-- Constraints for table `eskul_schedule`
--
ALTER TABLE `eskul_schedule`
  ADD CONSTRAINT `eskul_schedule_ibfk_1` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE;

--
-- Constraints for table `eskul_students`
--
ALTER TABLE `eskul_students`
  ADD CONSTRAINT `eskul_students_ibfk_1` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eskul_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `eskul_teachers`
--
ALTER TABLE `eskul_teachers`
  ADD CONSTRAINT `eskul_teachers_ibfk_1` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eskul_teachers_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `penilaian_eskul`
--
ALTER TABLE `penilaian_eskul`
  ADD CONSTRAINT `penilaian_eskul_ibfk_1` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penilaian_eskul_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `penilaian_keaktifan`
--
ALTER TABLE `penilaian_keaktifan`
  ADD CONSTRAINT `penilaian_keaktifan_ibfk_1` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian_eskul` (`penilaian_id`) ON DELETE CASCADE;

--
-- Constraints for table `penilaian_komentar`
--
ALTER TABLE `penilaian_komentar`
  ADD CONSTRAINT `penilaian_komentar_ibfk_1` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian_eskul` (`penilaian_id`) ON DELETE CASCADE;

--
-- Constraints for table `penilaian_rekomendasi`
--
ALTER TABLE `penilaian_rekomendasi`
  ADD CONSTRAINT `penilaian_rekomendasi_ibfk_1` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian_eskul` (`penilaian_id`) ON DELETE CASCADE;

--
-- Constraints for table `prestasi`
--
ALTER TABLE `prestasi`
  ADD CONSTRAINT `prestasi_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prestasi_ibfk_2` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`kelas_id`) ON DELETE SET NULL;

--
-- Constraints for table `student_eskul`
--
ALTER TABLE `student_eskul`
  ADD CONSTRAINT `student_eskul_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_eskul_ibfk_2` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teachers_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
