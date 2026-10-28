-- ==============================================================
-- Database Structure & Initial Data for Engineering Department Mail System
-- Database Name: engineering_db
-- Compatible with: MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- Generated Date: 2026-10-12
-- ==============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- 1. Create Database if not exists
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `engineering_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `engineering_db`;

-- --------------------------------------------------------
-- 2. Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT '',
  `role` varchar(20) DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table structure for table `letters`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_no` varchar(50) NOT NULL,
  `letter_no` varchar(100) DEFAULT NULL,
  `sent_date` date DEFAULT NULL,
  `received_date` datetime DEFAULT NULL,
  `sender_name` varchar(150) DEFAULT NULL,
  `sender_designation` varchar(150) DEFAULT NULL,
  `sender_institute` varchar(150) DEFAULT NULL,
  `sender_address` varchar(255) DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `mail_type` varchar(50) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Unresolved',
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_no` (`serial_no`),
  KEY `idx_letters_added_by` (`added_by`),
  CONSTRAINT `letters_fk_users` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `section_heads`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `section_heads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Table structure for table `primary_assignments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `primary_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `letter_id` int(11) NOT NULL,
  `serial_no` varchar(50) DEFAULT NULL,
  `letter_no` varchar(100) DEFAULT NULL,
  `received_date` varchar(50) DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `sender_name` varchar(150) DEFAULT NULL,
  `sender_designation` varchar(150) DEFAULT NULL,
  `sender_institute` varchar(150) DEFAULT NULL,
  `sender_address` varchar(255) DEFAULT NULL,
  `mail_type` varchar(50) DEFAULT NULL,
  `assignment_date` date DEFAULT NULL,
  `section_head` varchar(100) DEFAULT NULL,
  `task` varchar(150) DEFAULT NULL,
  `primary_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_primary_letter_id` (`letter_id`),
  CONSTRAINT `primary_assign_fk_letters` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Table structure for table `secondary_assignments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `secondary_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `letter_id` int(11) NOT NULL,
  `serial_no` varchar(50) DEFAULT NULL,
  `letter_no` varchar(100) DEFAULT NULL,
  `subject` text DEFAULT NULL,
  `sender_details` text DEFAULT NULL,
  `primary_assignment` varchar(255) DEFAULT NULL,
  `primary_date` varchar(50) DEFAULT NULL,
  `primary_note` text DEFAULT NULL,
  `section_head` varchar(100) DEFAULT NULL,
  `secondary_date` date DEFAULT NULL,
  `secondary_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sec_letter_id` (`letter_id`),
  CONSTRAINT `sec_assign_fk_letters` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Table structure for table `institute_contacts`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `institute_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institute_name` varchar(150) NOT NULL,
  `person_name` varchar(150) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `institute_name` (`institute_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Table structure for table `officers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `officers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `section` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 9. Table structure for table `tasks`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 10. Table structure for table `assignments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `letter_id` int(11) NOT NULL,
  `section_head_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `primary_notes` text DEFAULT NULL,
  `assignment_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ass_letter_id` (`letter_id`),
  KEY `idx_ass_section_head_id` (`section_head_id`),
  KEY `idx_ass_task_id` (`task_id`),
  CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`section_head_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- Seed Data / Initial Data
-- ==============================================================

-- --------------------------------------------------------
-- Dumping data for table `users`
-- Default Login: admin / admin123
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `name`, `role`) VALUES
(1, 'admin', 'admin@engineering.gov.lk', '$2y$10$w0f5u4y1fBvI3l9Wc7xMkuP8kH/8mQ2N1WzU9F7rPqR2S0Y9kE0K6', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE `role` = VALUES(`role`);

-- --------------------------------------------------------
-- Dumping data for table `section_heads`
-- --------------------------------------------------------
INSERT INTO `section_heads` (`id`, `name`) VALUES
(1, 'AD- Additional director'),
(2, 'AO- Administrative officer'),
(3, 'Chief Accountant'),
(4, 'Electric Engineer'),
(5, 'Chief Architecture'),
(6, 'Chief engineer (Edu)'),
(7, 'Chief Engineer(Health)'),
(8, 'Chief Engineer (Other)'),
(9, 'QS-Quanty serviyor')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------
-- Dumping data for table `institute_contacts`
-- --------------------------------------------------------
INSERT INTO `institute_contacts` (`id`, `institute_name`, `person_name`, `designation`, `email`, `phone`, `address`) VALUES
(1, 'NWP Chief Secretariat', 'P. B. Wijerathne', 'Chief Secretary', 'chiefsec@nwp.gov.lk', '037-2222123', 'Chief Secretarial Office, Kurunegala'),
(2, 'Ministry of Education NWP', 'K. M. Fernando', 'Secretary - Education', 'edu@nwp.gov.lk', '037-2223456', 'Provincial Education Office, Kurunegala'),
(3, 'Ministry of Health NWP', 'Dr. S. K. Jayasinghe', 'Provincial Director of Health Services', 'health@nwp.gov.lk', '037-2224567', 'Provincial Health Dept, Kurunegala'),
(4, 'Road Development Authority (RDA)', 'Eng. R. M. Perera', 'Executive Engineer', 'rda_nwp@rda.gov.lk', '037-2227890', 'RDA Provincial Office, Kurunegala'),
(5, 'Ceylon Electricity Board (CEB)', 'Eng. T. Wickramasinghe', 'Area Electrical Engineer', 'ceb_kurunegala@ceb.lk', '037-2225678', 'CEB Regional Office, Kurunegala'),
(6, 'National Water Supply & Drainage Board', 'Eng. D. Bandara', 'Regional Manager', 'waterboard_nw@waterboard.lk', '037-2226789', 'NWSDB Regional Office, Kurunegala')
ON DUPLICATE KEY UPDATE `institute_name` = VALUES(`institute_name`);

-- --------------------------------------------------------
-- Sample Letters (Demo Data)
-- --------------------------------------------------------
INSERT INTO `letters` (`id`, `letter_no`, `sent_date`, `received_date`, `sender_name`, `sender_designation`, `sender_institute`, `sender_address`, `subject`, `mail_type`, `added_by`, `status`) VALUES
(1, 'TEST_123', '2026-10-10', '2026-10-10 00:00:00', 'Test Name', 'Director', 'General Hospital', 'Colombo 07', 'Test Sub', 'Normal', 1, 'Unresolved'),
(2, 'ENG_2026_9999', '2026-10-10', '2026-10-10 00:00:00', 'User 1', 'Accountant', 'NWP Council', 'Kurunegala', 'Test Post Subject', 'Registered', 1, 'Unresolved'),
(3, 'ENG_2026_5432', '2026-10-16', '2026-10-17 00:00:00', 'ඉං.පී ආර් ඩබ් හේවාවිතාරණ', 'ප්‍රධාන ඉංජිනේරු', 'මහාමාර්ග අමාත්‍යාංශය', 'කුරුණෑගල', 'පාසල් ගොඩනැගිලි සංවර්ධන කටයුතු', 'Normal', 1, 'Unresolved')
ON DUPLICATE KEY UPDATE `letter_no` = VALUES(`letter_no`);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
