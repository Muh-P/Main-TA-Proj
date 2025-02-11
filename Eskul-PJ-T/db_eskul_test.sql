-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2025 at 09:02 AM
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
-- Database: `db_eskul_test`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddAttendance` (IN `p_eskul_id` INT, IN `p_student_id` INT, IN `p_status` ENUM('Present','Absent','Late','Excused'))   BEGIN
    IF NOT EXISTS (SELECT * FROM attendance WHERE eskul_id = p_eskul_id AND student_id = p_student_id AND date = CURDATE()) THEN
        INSERT INTO attendance (eskul_id, student_id, date, status)
        VALUES (p_eskul_id, p_student_id, CURDATE(), p_status);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddStudentToEskul` (IN `p_eskul_id` INT, IN `p_student_id` INT)   BEGIN
    IF NOT EXISTS (SELECT * FROM eskul_students WHERE eskul_id = p_eskul_id AND student_id = p_student_id) THEN
        INSERT INTO eskul_students (eskul_id, student_id, status)
        VALUES (p_eskul_id, p_student_id, 'Active');
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CalculateAttendancePoints` (IN `p_eskul_id` INT, IN `p_student_id` INT, OUT `attendance_percentage` DECIMAL(5,2), OUT `attendance_points` DECIMAL(5,2))   BEGIN
    DECLARE total_sessions INT;
    DECLARE attended_sessions INT;

    SELECT COUNT(*) INTO total_sessions
    FROM attendance
    WHERE eskul_id = p_eskul_id AND student_id = p_student_id;

    SELECT COUNT(*) INTO attended_sessions
    FROM attendance
    WHERE eskul_id = p_eskul_id AND student_id = p_student_id AND status = 'Present';

    IF total_sessions > 0 THEN
        SET attendance_percentage = (attended_sessions / total_sessions) * 100;
        SET attendance_points = (attendance_percentage / 100) * 10;
    ELSE
        SET attendance_percentage = 0;
        SET attendance_points = 0;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CalculateAverageGrade` (IN `p_eskul_id` INT, OUT `avg_grade` DECIMAL(5,2))   BEGIN
    SELECT COALESCE(AVG(grade), 0) INTO avg_grade
    FROM grades
    WHERE eskul_id = p_eskul_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `MarkStudentExited` (IN `p_eskul_id` INT, IN `p_student_id` INT)   BEGIN
    UPDATE eskul_students
    SET status = 'Exited'
    WHERE eskul_id = p_eskul_id AND student_id = p_student_id;
END$$

DELIMITER ;

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

--
-- Triggers `attendance`
--
DELIMITER $$
CREATE TRIGGER `trg_update_attendance_date` BEFORE UPDATE ON `attendance` FOR EACH ROW BEGIN
    IF NEW.status != OLD.status THEN
        SET NEW.date = CURDATE();
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

--
-- Dumping data for table `eskul`
--

INSERT INTO `eskul` (`eskul_id`, `name`, `description`, `created_at`) VALUES
(3, 'Basketball', 'Basketball extracurricular activities', '2025-02-11 11:45:14'),
(4, 'Music', 'Music extracurricular activities', '2025-02-11 11:45:14'),
(5, 'Futsal', 'Ekskul olahraga Futsal', '2025-02-11 13:30:37'),
(6, 'Drama', 'Ekskul seni Drama', '2025-02-11 13:30:37'),
(7, 'Pencak Silat', 'Ekskul bela diri Pencak Silat', '2025-02-11 13:30:37'),
(8, 'RoboTech', 'Ekskul teknologi & robotika', '2025-02-11 13:30:37');

-- --------------------------------------------------------

--
-- Table structure for table `eskul_announcements`
--

CREATE TABLE `eskul_announcements` (
  `announcement_id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eskul_schedule`
--

CREATE TABLE `eskul_schedule` (
  `schedule_id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
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

--
-- Dumping data for table `eskul_students`
--

INSERT INTO `eskul_students` (`id`, `eskul_id`, `student_id`, `status`) VALUES
(1, 3, 4, 'Active'),
(2, 3, 5, 'Active'),
(3, 5, 6, 'Active'),
(4, 6, 6, 'Active'),
(5, 7, 7, 'Active'),
(6, 8, 7, 'Active'),
(7, 5, 8, 'Active'),
(8, 8, 8, 'Active'),
(9, 6, 9, 'Active'),
(10, 7, 9, 'Active'),
(11, 4, 4, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `eskul_teachers`
--

CREATE TABLE `eskul_teachers` (
  `id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eskul_teachers`
--

INSERT INTO `eskul_teachers` (`id`, `eskul_id`, `teacher_id`) VALUES
(1, 3, 6),
(2, 5, 7),
(3, 6, 8),
(4, 7, 9),
(5, 8, 10),
(6, 4, 11);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `eskul_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `remarks` text DEFAULT NULL
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

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `name`, `class`, `email`, `phone`, `user_id`) VALUES
(4, 'Jane Smith', '10A', 'jane@example.com', '1112223334', 20),
(5, 'Asep Rudi', '10B', 'asep@example.com', '5555666677', 21),
(6, 'Ali Saputra', '10A', 'ali@example.com', '081111111111', 26),
(7, 'Budi Santoso', '10B', 'budi@example.com', '082222222222', 27),
(8, 'Citra Lestari', '10C', 'citra@example.com', '083333333333', 28),
(9, 'Dian Permana', '10D', 'dian@example.com', '084444444444', 29);

--
-- Triggers `students`
--
DELIMITER $$
CREATE TRIGGER `trg_delete_student` BEFORE DELETE ON `students` FOR EACH ROW BEGIN
    DELETE FROM eskul_students WHERE student_id = OLD.student_id;
    DELETE FROM attendance WHERE student_id = OLD.student_id;
    DELETE FROM grades WHERE student_id = OLD.student_id;
END
$$
DELIMITER ;

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

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `name`, `email`, `phone`, `user_id`) VALUES
(6, 'Ahmad Surajat', 'ahmad@example.com', '081234567890', 19),
(7, 'Pak Faisal', 'futsal@example.com', '081234567890', 22),
(8, 'Bu Erni', 'drama@example.com', '081234567891', 23),
(9, 'Pak Liau', 'silat@example.com', '081234567892', 24),
(10, 'Bu Rahma', 'robot@example.com', '081234567893', 25),
(11, 'Bu Syintia', 'Music@gmail.com', '1123456789', 30);

--
-- Triggers `teachers`
--
DELIMITER $$
CREATE TRIGGER `trg_delete_teacher` BEFORE DELETE ON `teachers` FOR EACH ROW BEGIN
    DELETE FROM eskul_teachers WHERE teacher_id = OLD.teacher_id;
    DELETE FROM eskul_announcements WHERE teacher_id = OLD.teacher_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`) VALUES
(18, 'admin', '$2y$10$YqIZQpGSGCzwc2lnrHV6jO0LeaQ7JjJ8Vk0FsFQJLHQXxFZOOlIfO', 'Admin User', 'admin'),
(19, 'teacher_basket', '$2y$10$/QXiZ82mCIzQAs8xKU.vMeq/.3pYxPcpXHm3iAa/fGnkq/I4l5pGW', 'Ahmad Surajat', 'teacher'),
(20, 'student_jane', '123', 'Jane Smith', 'student'),
(21, 'student_asep', '123', 'Asep Rudi', 'student'),
(22, 'teacher_futsal', '$2y$10$RCh4qnyNVjopI0BXKrlCuu97UOAv2f4snu67UHPExLYsVkMgdFUWO', 'Pak Faisal', 'teacher'),
(23, 'teacher_drama', '$2y$10$zBNfkgzOMXFDZewPPbydce9muuBJ307ID6ISRmTm.QObd/kSGoP86', 'Bu Erni', 'teacher'),
(24, 'teacher_silat', '$2y$10$IQfS8pO42eh59kvdL9CjjeAM5jR4Ona2jdxMtHMt1DFEfqJ26/wMi', 'Pak Liau', 'teacher'),
(25, 'teacher_robot', '$2y$10$FQUSvMEuFqcfGAhJMbB3Wu.3rscSrBbR03lskrjXYHdb6Njc3luNS', 'Bu Rahma', 'teacher'),
(26, 'student_ali', '123', 'Ali Saputra', 'student'),
(27, 'student_budi', '123', 'Budi Santoso', 'student'),
(28, 'student_citra', '123', 'Citra Lestari', 'student'),
(29, 'student_dian', '$2y$10$8V1hKfUdWb7Z5/NH9ZGoOeW45MoBzGhuu06IfYhJrrUkjwIpZH6A.', 'Dian Permana', 'student'),
(30, 'teacher_music', '$2y$10$wTjp6IjX65WzG/07cD1xveDto3MvIJlcoFheLYpkFfsI4wt6zrWXS', 'Bu Syintia', 'teacher');

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
-- Triggers `user_activity_log`
--
DELIMITER $$
CREATE TRIGGER `trg_user_activity` AFTER INSERT ON `user_activity_log` FOR EACH ROW BEGIN
    INSERT INTO user_activity_log (user_id, activity, timestamp)
    VALUES (NEW.user_id, CONCAT('Aktivitas: ', NEW.activity), NOW());
END
$$
DELIMITER ;

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
-- Indexes for table `eskul_announcements`
--
ALTER TABLE `eskul_announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `eskul_id` (`eskul_id`),
  ADD KEY `teacher_id` (`teacher_id`);

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
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD KEY `eskul_id` (`eskul_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`),
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
  MODIFY `eskul_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `eskul_announcements`
--
ALTER TABLE `eskul_announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_schedule`
--
ALTER TABLE `eskul_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eskul_students`
--
ALTER TABLE `eskul_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `eskul_teachers`
--
ALTER TABLE `eskul_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
-- Constraints for table `eskul_announcements`
--
ALTER TABLE `eskul_announcements`
  ADD CONSTRAINT `eskul_announcements_ibfk_1` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eskul_announcements_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

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
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`eskul_id`) REFERENCES `eskul` (`eskul_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

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
