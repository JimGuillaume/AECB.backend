-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 27, 2026 at 11:21 AM
-- Server version: 8.4.7
-- PHP Version: 8.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aecb_attendance`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_calculate_monthly_hours`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_calculate_monthly_hours` (IN `p_user_id` INT, IN `p_year` INT, IN `p_month` INT)   BEGIN
    DECLARE v_daily_hours DECIMAL(4,2);
    DECLARE v_total_hours DECIMAL(10,2);
    DECLARE v_total_worked_days INT;
    
    -- Get daily hour requirement
    SELECT daily_hours INTO v_daily_hours FROM daily_hour_requirements
    WHERE active = TRUE ORDER BY effective_from DESC LIMIT 1;
    
    -- Count days marked with codes that count as worked
    SELECT COALESCE(SUM(hours_value), 0) INTO v_total_hours
    FROM attendance_records ar
    JOIN work_codes wc ON ar.code_id = wc.code_id
    WHERE ar.user_id = p_user_id
        AND MONTH(ar.attendance_date) = p_month
        AND YEAR(ar.attendance_date) = p_year
        AND wc.is_counted_as_worked = TRUE;
    
    SELECT v_total_hours AS total_hours_worked, v_daily_hours AS required_daily_hours;
END$$

DROP PROCEDURE IF EXISTS `sp_get_team_attendance_summary`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_team_attendance_summary` (IN `p_team_id` INT, IN `p_year` INT, IN `p_month` INT)   BEGIN
    SELECT 
        u.user_id,
        u.first_name,
        u.last_name,
        ar.attendance_date,
        wc.code_name,
        ar.hours_value,
        ar.notes
    FROM attendance_records ar
    JOIN users u ON ar.user_id = u.user_id
    JOIN work_codes wc ON ar.code_id = wc.code_id
    WHERE ar.team_id = p_team_id
        AND YEAR(ar.attendance_date) = p_year
        AND MONTH(ar.attendance_date) = p_month
    ORDER BY ar.attendance_date, u.last_name, u.first_name;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `team_id` int NOT NULL,
  `attendance_date` date NOT NULL,
  `code_id` int NOT NULL,
  `hours_value` decimal(5,2) NOT NULL COMMENT 'Can be decimal for overtime',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `uk_user_date` (`user_id`,`attendance_date`),
  KEY `fk_attendance_code` (`code_id`),
  KEY `fk_attendance_created_by` (`created_by`),
  KEY `idx_attendance_date` (`attendance_date`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_team_id` (`team_id`)
) ;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`attendance_id`, `user_id`, `team_id`, `attendance_date`, `code_id`, `hours_value`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-05-03', 1, 8.00, NULL, 1, '2026-05-03 11:51:43', '2026-05-03 11:51:43');

--
-- Triggers `attendance_records`
--
DROP TRIGGER IF EXISTS `tr_calculate_overtime_after_attendance`;
DELIMITER $$
CREATE TRIGGER `tr_calculate_overtime_after_attendance` AFTER INSERT ON `attendance_records` FOR EACH ROW BEGIN
    DECLARE v_monthly_hours DECIMAL(10,2);
    DECLARE v_required_hours DECIMAL(10,2);
    DECLARE v_overtime DECIMAL(10,2);
    DECLARE v_month INT;
    DECLARE v_year INT;
    DECLARE v_daily_hours DECIMAL(4,2);
    DECLARE v_is_counted BOOLEAN;
    
    -- Get work code details
    SELECT is_counted_as_worked, decimal_value INTO v_is_counted, v_daily_hours
    FROM work_codes WHERE code_id = NEW.code_id;
    
    -- Extract month and year from attendance date
    SET v_month = MONTH(NEW.attendance_date);
    SET v_year = YEAR(NEW.attendance_date);
    
    -- Only process if it's a counted work code
    IF v_is_counted THEN
        -- Use provided hours_value or default from code
        SET v_monthly_hours = COALESCE(NEW.hours_value, v_daily_hours);
        
        -- Get daily hour requirements
        SELECT daily_hours INTO v_daily_hours FROM daily_hour_requirements 
        WHERE active = TRUE ORDER BY effective_from DESC LIMIT 1;
        
        SET v_required_hours = v_daily_hours;
        
        -- Calculate overtime for this day (if more than daily requirement)
        IF v_monthly_hours > v_required_hours THEN
            SET v_overtime = v_monthly_hours - v_required_hours;
        ELSE
            SET v_overtime = 0;
        END IF;
        
        -- Insert or update overtime tracking
        INSERT INTO overtime_tracking (user_id, month, year, hours_earned, balance, calculated_at)
        VALUES (NEW.user_id, v_month, v_year, v_overtime, v_overtime, NOW())
        ON DUPLICATE KEY UPDATE
            hours_earned = hours_earned + VALUES(hours_earned),
            balance = balance + VALUES(hours_earned),
            updated_at = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `daily_hour_requirements`
--

DROP TABLE IF EXISTS `daily_hour_requirements`;
CREATE TABLE IF NOT EXISTS `daily_hour_requirements` (
  `requirement_id` int NOT NULL AUTO_INCREMENT,
  `company_id` int DEFAULT '1',
  `daily_hours` decimal(4,2) NOT NULL DEFAULT '8.00' COMMENT '7.6 or 8',
  `commission_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `effective_from` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`requirement_id`),
  KEY `idx_effective_from` (`effective_from`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_hour_requirements`
--

INSERT INTO `daily_hour_requirements` (`requirement_id`, `company_id`, `daily_hours`, `commission_type`, `active`, `effective_from`, `created_at`) VALUES
(1, 1, 8.00, 'Standard', 1, '2025-01-01', '2026-05-03 11:46:42'),
(2, 1, 7.60, 'Special Commission', 1, '2025-01-01', '2026-05-03 11:46:42');

-- --------------------------------------------------------

--
-- Table structure for table `holidays_leaves`
--

DROP TABLE IF EXISTS `holidays_leaves`;
CREATE TABLE IF NOT EXISTS `holidays_leaves` (
  `holiday_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `holiday_date` date NOT NULL,
  `type` enum('legal','company','sick_day','weather','economic') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'NULL = applies to all users',
  `team_id` int DEFAULT NULL COMMENT 'NULL = company-wide',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`holiday_id`),
  KEY `fk_holidays_user` (`user_id`),
  KEY `fk_holidays_team` (`team_id`),
  KEY `fk_holidays_created_by` (`created_by`),
  KEY `idx_holiday_date` (`holiday_date`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overtime_tracking`
--

DROP TABLE IF EXISTS `overtime_tracking`;
CREATE TABLE IF NOT EXISTS `overtime_tracking` (
  `overtime_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `month` int NOT NULL COMMENT '1-12',
  `year` int NOT NULL,
  `hours_earned` decimal(10,2) DEFAULT '0.00',
  `hours_used` decimal(10,2) DEFAULT '0.00',
  `balance` decimal(10,2) DEFAULT '0.00',
  `calculated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`overtime_id`),
  UNIQUE KEY `uk_user_month_year` (`user_id`,`month`,`year`),
  KEY `idx_user_id` (`user_id`)
) ;

--
-- Dumping data for table `overtime_tracking`
--

INSERT INTO `overtime_tracking` (`overtime_id`, `user_id`, `month`, `year`, `hours_earned`, `hours_used`, `balance`, `calculated_at`, `updated_at`) VALUES
(1, 1, 5, 2026, 0.40, 0.00, 0.40, '2026-05-03 11:51:43', '2026-05-03 11:51:43');

--
-- Triggers `overtime_tracking`
--
DROP TRIGGER IF EXISTS `tr_prevent_negative_overtime`;
DELIMITER $$
CREATE TRIGGER `tr_prevent_negative_overtime` BEFORE UPDATE ON `overtime_tracking` FOR EACH ROW BEGIN
    IF NEW.balance < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot reduce overtime balance below 0';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
CREATE TABLE IF NOT EXISTS `teams` (
  `team_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialization` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_team_id` int DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`team_id`),
  KEY `fk_teams_created_by` (`created_by`),
  KEY `idx_parent_team_id` (`parent_team_id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `name`, `specialization`, `parent_team_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Team A', 'Carpentry', NULL, 1, '2026-05-03 11:50:49', '2026-05-03 11:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','manager','team_leader','worker') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'worker',
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `active`, `created_at`, `updated_at`) VALUES
(1, 'admin@aecb.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Admin', 'User', 'admin', 1, '2026-05-03 11:50:13', '2026-05-03 11:50:13'),
(2, 'test@test.com', 'azerty', 'Test', 'Test', '', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users_teams`
--

DROP TABLE IF EXISTS `users_teams`;
CREATE TABLE IF NOT EXISTS `users_teams` (
  `user_team_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `team_id` int NOT NULL,
  `schedule_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_team_id`),
  UNIQUE KEY `uk_user_team` (`user_id`,`team_id`),
  KEY `fk_users_teams_schedule` (`schedule_id`),
  KEY `idx_team_id` (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users_teams`
--

INSERT INTO `users_teams` (`user_team_id`, `user_id`, `team_id`, `schedule_id`, `assigned_at`) VALUES
(1, 1, 1, 1, '2026-05-03 11:51:10');

-- --------------------------------------------------------

--
-- Table structure for table `work_codes`
--

DROP TABLE IF EXISTS `work_codes`;
CREATE TABLE IF NOT EXISTS `work_codes` (
  `code_id` int NOT NULL AUTO_INCREMENT,
  `code_name` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'P, C, CC, CS, M, MLD, CE, CI, AT, R, A',
  `decimal_value` decimal(5,2) NOT NULL COMMENT 'Default value in hours',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_counted_as_worked` tinyint(1) DEFAULT '1' COMMENT 'Counts toward daily hours',
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code_id`),
  UNIQUE KEY `code_name` (`code_name`),
  KEY `idx_code_name` (`code_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_codes`
--

INSERT INTO `work_codes` (`code_id`, `code_name`, `decimal_value`, `description`, `is_counted_as_worked`, `active`, `created_at`, `updated_at`) VALUES
(1, 'P', 8.00, 'Prestation sur le chantier (ou valeur décimale pour heures supplémentaires)', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(2, 'C', 8.00, 'Congés payés (compté comme jour presté)', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(3, 'CC', 8.00, 'Congés de circonstance (mariage, communion, etc.)', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(4, 'CS', 0.00, 'Congé sans solde (suspension du contrat)', 0, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(5, 'M', 8.00, 'Congé maladie (ponctuel, max 1 mois)', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(6, 'MLD', 8.00, 'Congé maladie longue durée (> 1 mois)', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(7, 'CE', 8.00, 'Chômage économique', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(8, 'CI', 8.00, 'Chômage intempérie', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(9, 'AT', 8.00, 'Accident de travail (compté comme presté)', 1, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(10, 'R', -8.00, 'Récupération heures supplémentaires (réduit le pot)', 0, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42'),
(11, 'A', 0.00, 'Absence non justifiée (non rémunérée)', 0, 1, '2026-05-03 11:46:42', '2026-05-03 11:46:42');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

DROP TABLE IF EXISTS `work_schedules`;
CREATE TABLE IF NOT EXISTS `work_schedules` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fraction` decimal(5,4) NOT NULL COMMENT 'e.g., 1.0 (full), 0.5 (half), 0.333 (1/3)',
  `daily_hours` decimal(4,2) NOT NULL DEFAULT '8.00' COMMENT '7.6 or 8 hours',
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`schedule_id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_fraction` (`fraction`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_schedules`
--

INSERT INTO `work_schedules` (`schedule_id`, `name`, `fraction`, `daily_hours`, `active`, `created_at`) VALUES
(1, 'Temps plein', 1.0000, 8.00, 1, '2026-05-03 11:46:42'),
(2, '9/10 temps', 0.9000, 8.00, 1, '2026-05-03 11:46:42'),
(3, '7/10 temps', 0.7000, 8.00, 1, '2026-05-03 11:46:42'),
(4, '3/4 temps', 0.7500, 8.00, 1, '2026-05-03 11:46:42'),
(5, '2/3 temps', 0.6667, 8.00, 1, '2026-05-03 11:46:42'),
(6, '3/5 temps', 0.6000, 8.00, 1, '2026-05-03 11:46:42'),
(7, '1/2 temps', 0.5000, 8.00, 1, '2026-05-03 11:46:42'),
(8, '2/5 temps', 0.4000, 8.00, 1, '2026-05-03 11:46:42'),
(9, '1/3 temps', 0.3333, 8.00, 1, '2026-05-03 11:46:42'),
(10, '3/10 temps', 0.3000, 8.00, 1, '2026-05-03 11:46:42'),
(11, '1/4 temps', 0.2500, 8.00, 1, '2026-05-03 11:46:42'),
(12, '1/5 temps', 0.2000, 8.00, 1, '2026-05-03 11:46:42');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `fk_attendance_code` FOREIGN KEY (`code_id`) REFERENCES `work_codes` (`code_id`),
  ADD CONSTRAINT `fk_attendance_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_attendance_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`),
  ADD CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `holidays_leaves`
--
ALTER TABLE `holidays_leaves`
  ADD CONSTRAINT `fk_holidays_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_holidays_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_holidays_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `overtime_tracking`
--
ALTER TABLE `overtime_tracking`
  ADD CONSTRAINT `fk_overtime_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_teams_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_teams_parent` FOREIGN KEY (`parent_team_id`) REFERENCES `teams` (`team_id`) ON DELETE SET NULL;

--
-- Constraints for table `users_teams`
--
ALTER TABLE `users_teams`
  ADD CONSTRAINT `fk_users_teams_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `work_schedules` (`schedule_id`),
  ADD CONSTRAINT `fk_users_teams_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_users_teams_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
