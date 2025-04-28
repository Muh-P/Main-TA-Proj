-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 08:47 PM
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
  `eskul_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Excused') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `eskul_id` int(11) NOT NULL,
  `day_of_week` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `tahun_ajaran` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_students`
--

CREATE TABLE `eskul_students` (
  `id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Active','Exited') DEFAULT 'Active'
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
-- Table structure for table `penilaian_eskul`
--

CREATE TABLE `penilaian_eskul` (
  `penilaian_id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `tahun_ajaran` varchar(9) NOT NULL,
  `kehadiran_persen` int(3) DEFAULT 0,
  `keaktifan_persen` int(3) DEFAULT 0,
  `prestasi_poin` int(3) DEFAULT 0,
  `total_poin` int(3) DEFAULT 0,
  `rekomendasi` enum('Kurang','Cukup','Baik','Sangat Baik') DEFAULT NULL,
  `nilai_akhir` enum('Kurang','Cukup','Baik','Sangat Baik') DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `class` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
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
            (name, class, email, phone, user_id)
        VALUES
            (NEW.full_name, 'Belum diatur', 'Belum diatur', 'Belum diatur', NEW.id);
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `eskul_id` (`eskul_id`),
  ADD KEY `student_id` (`student_id`);

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
-- Indexes for table `penilaian_eskul`
--
ALTER TABLE `penilaian_eskul`
  ADD PRIMARY KEY (`penilaian_id`),
  ADD KEY `eskul_id` (`eskul_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

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
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul`
--
ALTER TABLE `eskul`
  MODIFY `eskul_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_schedule`
--
ALTER TABLE `eskul_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_students`
--
ALTER TABLE `eskul_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_teachers`
--
ALTER TABLE `eskul_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penilaian_eskul`
--
ALTER TABLE `penilaian_eskul`
  MODIFY `penilaian_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

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
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
