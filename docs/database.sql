-- ============================================================================
-- QuizMaster — Automated Assessment & Analytics System
-- Database Schema (MySQL 8.0+ / MariaDB)
-- Jira Issue: QM-7
-- Author: Abdalrahman
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `quizmaster_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `quizmaster_db`;

-- ----------------------------------------------------------------------------
-- 1. Table: users (Administrators & Students)
-- ------------------------------------------------------------------------------ ============================================================================
-- QuizMaster — Automated Assessment & Analytics System
-- Database Schema (MySQL 8.0+ / MariaDB)
-- Jira Issue: QM-7
-- Author: Abdalrahman
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `quizmaster_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `quizmaster_db`;

-- ----------------------------------------------------------------------------
-- 1. Table: users (Administrators & Students)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table: quizzes (Assessments created by Admins)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `access_code` VARCHAR(20) NOT NULL,
  `status` ENUM('active', 'closed') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quizzes_access_code` (`access_code`),
  KEY `idx_quizzes_admin_id` (`admin_id`),
  CONSTRAINT `fk_quizzes_admin`
    FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Table: questions (Multiple-Choice Questions per Quiz)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` INT UNSIGNED NOT NULL,
  `question_text` TEXT NOT NULL,
  `option_a` VARCHAR(255) NOT NULL,
  `option_b` VARCHAR(255) NOT NULL,
  `option_c` VARCHAR(255) NOT NULL,
  `option_d` VARCHAR(255) NOT NULL,
  `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `points` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_questions_quiz_id` (`quiz_id`),
  CONSTRAINT `fk_questions_quiz`
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table: attempts (Student Submissions & Server-Graded Scores)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `quiz_id` INT UNSIGNED NOT NULL,
  `score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `total_points` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_quiz_attempt` (`student_id`, `quiz_id`),
  KEY `idx_attempts_quiz_id` (`quiz_id`),
  CONSTRAINT `fk_attempts_student`
    FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_attempts_quiz`
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Seed Data (Sample Accounts & Quiz for Testing)
-- Default password for both accounts: Admin@123 / Student@123 (hashed)
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`) VALUES
('Dr. Ahmed Salem', 'admin@quizmaster.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Abdalrahman Student', 'student@quizmaster.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

INSERT INTO `quizzes` (`admin_id`, `title`, `description`, `access_code`, `status`) VALUES
(1, 'Software Engineering & SDLC Fundamentals', 'Midterm assessment covering Agile, SRS, UML, and DFD concepts.', 'SE2026', 'active');

INSERT INTO `questions` (`quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `points`) VALUES
(1, 'Which diagram models the system as a single central process?', 'Use Case Diagram', 'Level 0 DFD (Context Diagram)', 'Sequence Diagram', 'Class Diagram', 'B', 2),
(1, 'Which constraint prevents duplicate quiz attempts at the database level?', 'FOREIGN KEY', 'DEFAULT', 'Composite UNIQUE Key', 'AUTO_INCREMENT', 'C', 2),
(1, 'Where must quiz grading logic execute to prevent client tampering?', 'Browser JavaScript', 'CSS Media Queries', 'Server-Side PHP Backend', 'HTML Form Attributes', 'C', 2);
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table: quizzes (Assessments created by Admins)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `access_code` VARCHAR(20) NOT NULL,
  `status` ENUM('active', 'closed') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quizzes_access_code` (`access_code`),
  KEY `idx_quizzes_admin_id` (`admin_id`),
  CONSTRAINT `fk_quizzes_admin`
    FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Table: questions (Multiple-Choice Questions per Quiz)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` INT UNSIGNED NOT NULL,
  `question_text` TEXT NOT NULL,
  `option_a` VARCHAR(255) NOT NULL,
  `option_b` VARCHAR(255) NOT NULL,
  `option_c` VARCHAR(255) NOT NULL,
  `option_d` VARCHAR(255) NOT NULL,
  `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `points` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_questions_quiz_id` (`quiz_id`),
  CONSTRAINT `fk_questions_quiz`
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table: attempts (Student Submissions & Server-Graded Scores)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `quiz_id` INT UNSIGNED NOT NULL,
  `score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `total_points` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_quiz_attempt` (`student_id`, `quiz_id`),
  KEY `idx_attempts_quiz_id` (`quiz_id`),
  CONSTRAINT `fk_attempts_student`
    FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_attempts_quiz`
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Seed Data (Sample Accounts & Quiz for Testing)
-- Default password for both accounts: Admin@123 / Student@123 (hashed)
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `role`) VALUES
('Dr. Ahmed Salem', 'admin@quizmaster.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Abdalrahman Student', 'student@quizmaster.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

INSERT INTO `quizzes` (`admin_id`, `title`, `description`, `access_code`, `status`) VALUES
(1, 'Software Engineering & SDLC Fundamentals', 'Midterm assessment covering Agile, SRS, UML, and DFD concepts.', 'SE2026', 'active');

INSERT INTO `questions` (`quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `points`) VALUES
(1, 'Which diagram models the system as a single central process?', 'Use Case Diagram', 'Level 0 DFD (Context Diagram)', 'Sequence Diagram', 'Class Diagram', 'B', 2),
(1, 'Which constraint prevents duplicate quiz attempts at the database level?', 'FOREIGN KEY', 'DEFAULT', 'Composite UNIQUE Key', 'AUTO_INCREMENT', 'C', 2),
(1, 'Where must quiz grading logic execute to prevent client tampering?', 'Browser JavaScript', 'CSS Media Queries', 'Server-Side PHP Backend', 'HTML Form Attributes', 'C', 2);