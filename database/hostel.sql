-- Hostel Management System Database Schema
-- Created for Production Environment
CREATE DATABASE IF NOT EXISTS `hostel_management`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hostel_management`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- TABLE: users
-- Description: Stores all user accounts with roles
-- =====================================================
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(15),
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'warden', 'student') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: hostels
-- Description: Hostel/Building information
-- =====================================================
CREATE TABLE `hostels` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `location` VARCHAR(255) NOT NULL,
  `capacity` INT NOT NULL,
  `warden_id` INT,
  `contact_phone` VARCHAR(15),
  `email` VARCHAR(100),
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`warden_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: rooms
-- Description: Room information within hostels
-- =====================================================
CREATE TABLE `rooms` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `hostel_id` INT NOT NULL,
  `room_number` VARCHAR(20) NOT NULL,
  `room_type` ENUM('single', 'double', 'triple', 'four-bed') NOT NULL,
  `capacity` INT NOT NULL,
  `beds_available` INT NOT NULL,
  `rent_per_month` DECIMAL(10, 2) NOT NULL,
  `description` TEXT,
  `status` ENUM('available', 'occupied', 'maintenance') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`hostel_id`) REFERENCES `hostels`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `unique_hostel_room` (`hostel_id`, `room_number`),
  INDEX `idx_status` (`status`),
  INDEX `idx_hostel_id` (`hostel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: students
-- Description: Student profile information
-- =====================================================
CREATE TABLE `students` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `roll_number` VARCHAR(50) NOT NULL UNIQUE,
  `registration_number` VARCHAR(50) NOT NULL UNIQUE,
  `course` VARCHAR(100) NOT NULL,
  `year` ENUM('1st', '2nd', '3rd', '4th') NOT NULL,
  `semester` INT,
  `date_of_birth` DATE,
  `gender` ENUM('male', 'female', 'other'),
  `father_name` VARCHAR(100),
  `mother_name` VARCHAR(100),
  `parent_phone` VARCHAR(15),
  `permanent_address` TEXT,
  `current_address` TEXT,
  `admission_date` DATE,
  `photo_path` VARCHAR(255),
  `status` ENUM('active', 'inactive', 'left') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_roll_number` (`roll_number`),
  INDEX `idx_status` (`status`),
  INDEX `idx_course` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: room_allocations
-- Description: Track student room assignments
-- =====================================================
CREATE TABLE `room_allocations` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `room_id` INT NOT NULL,
  `hostel_id` INT NOT NULL,
  `allocation_date` DATE NOT NULL,
  `vacate_date` DATE,
  `bed_number` INT,
  `status` ENUM('active', 'vacated', 'transferred') NOT NULL DEFAULT 'active',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`hostel_id`) REFERENCES `hostels`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_student_id` (`student_id`),
  INDEX `idx_room_id` (`room_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: fees
-- Description: Fee management and tracking
-- =====================================================
CREATE TABLE `fees` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `allocation_id` INT NOT NULL,
  `fee_type` ENUM('rent', 'mess', 'maintenance', 'other') NOT NULL DEFAULT 'rent',
  `amount` DECIMAL(10, 2) NOT NULL,
  `due_date` DATE NOT NULL,
  `paid_date` DATE,
  `payment_method` ENUM('cash', 'cheque', 'online', 'bank_transfer') DEFAULT 'cash',
  `transaction_id` VARCHAR(100),
  `status` ENUM('pending', 'partial', 'paid', 'overdue', 'waived') NOT NULL DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`allocation_id`) REFERENCES `room_allocations`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_student_id` (`student_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: visitors
-- Description: Visitor entry and exit tracking
-- =====================================================
CREATE TABLE `visitors` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `visitor_name` VARCHAR(100) NOT NULL,
  `visitor_phone` VARCHAR(15),
  `relationship` VARCHAR(50),
  `id_proof_type` VARCHAR(50),
  `id_proof_number` VARCHAR(100),
  `entry_date` DATETIME NOT NULL,
  `exit_date` DATETIME,
  `entry_time` TIME,
  `exit_time` TIME,
  `purpose` TEXT,
  `notes` TEXT,
  `status` ENUM('entered', 'exited', 'pending') NOT NULL DEFAULT 'entered',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_student_id` (`student_id`),
  INDEX `idx_entry_date` (`entry_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: complaints
-- Description: Student complaint management
-- =====================================================
CREATE TABLE `complaints` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `category` ENUM('room_condition', 'plumbing', 'electrical', 'cleanliness', 'noise', 'bullying', 'other') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `status` ENUM('open', 'in_progress', 'resolved', 'closed', 'rejected') NOT NULL DEFAULT 'open',
  `assigned_to` INT,
  `admin_reply` TEXT,
  `replied_date` DATETIME,
  `resolved_date` DATETIME,
  `attachment_path` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_student_id` (`student_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: leave_requests
-- Description: Student leave requests and approval workflow
-- =====================================================
CREATE TABLE `leave_requests` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `from_date` DATE NOT NULL,
  `to_date` DATE NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT NULL,
  `reviewed_at` DATETIME NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_leave_student` (`student_id`),
  INDEX `idx_leave_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: activity_logs
-- Description: Track all user activities
-- =====================================================
CREATE TABLE `activity_logs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50),
  `description` TEXT,
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE: settings
-- Description: System configuration settings
-- =====================================================
CREATE TABLE `settings` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` LONGTEXT,
  `description` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DUMMY DATA
-- =====================================================

-- Admin User
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `status`) VALUES
('Admin User', 'admin@hostel.com', '9876543210', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'admin', 'active');

-- Warden Users
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `status`) VALUES
('John Warden', 'warden1@hostel.com', '9876543211', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'warden', 'active'),
('Sarah Warden', 'warden2@hostel.com', '9876543212', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'warden', 'active');

-- Student Users
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `status`) VALUES
('Rajesh Kumar', 'rajesh@student.com', '9876543213', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'student', 'active'),
('Priya Singh', 'priya@student.com', '9876543214', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'student', 'active'),
('Amit Patel', 'amit@student.com', '9876543215', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'student', 'active'),
('Neha Verma', 'neha@student.com', '9876543216', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'student', 'active'),
('Vikram Singh', 'vikram@student.com', '9876543217', '$2y$10$VE.mwbecn8TFJuAeD3mA4eM1FD7B8uEWn/qKgTItF2rF9Jy685qa.', 'student', 'active');

-- Hostels
INSERT INTO `hostels` (`name`, `location`, `capacity`, `warden_id`, `contact_phone`, `email`, `status`) VALUES
('Boys Hostel A', 'Campus North Wing', 100, 2, '9876543211', 'hostel.a@college.com', 'active'),
('Girls Hostel B', 'Campus South Wing', 80, 3, '9876543212', 'hostel.b@college.com', 'active');

-- Rooms
INSERT INTO `rooms` (`hostel_id`, `room_number`, `room_type`, `capacity`, `beds_available`, `rent_per_month`, `description`, `status`) VALUES
(1, 'A101', 'double', 2, 1, 5000.00, 'Room with AC and attached bathroom', 'available'),
(1, 'A102', 'triple', 3, 3, 7500.00, 'Spacious room with window', 'available'),
(1, 'A103', 'four-bed', 4, 2, 10000.00, 'Large room with study area', 'occupied'),
(2, 'B101', 'double', 2, 2, 5000.00, 'Room with fan and shared bathroom', 'available'),
(2, 'B102', 'triple', 3, 1, 7500.00, 'Modern room with WiFi', 'occupied');

-- Students
INSERT INTO `students` (`user_id`, `roll_number`, `registration_number`, `course`, `year`, `semester`, `date_of_birth`, `gender`, `father_name`, `mother_name`, `parent_phone`, `permanent_address`, `admission_date`, `status`) VALUES
(4, 'CS001', 'REG001', 'Computer Science', '1st', 1, '2004-05-15', 'male', 'Mr. Kumar', 'Mrs. Kumar', '9876543290', 'Delhi', '2023-08-01', 'active'),
(5, 'CS002', 'REG002', 'Computer Science', '2nd', 3, '2003-10-20', 'female', 'Mr. Singh', 'Mrs. Singh', '9876543291', 'Mumbai', '2022-08-01', 'active'),
(6, 'EC001', 'REG003', 'Electronics', '1st', 1, '2004-12-10', 'male', 'Mr. Patel', 'Mrs. Patel', '9876543292', 'Ahmedabad', '2023-08-01', 'active'),
(7, 'CS003', 'REG004', 'Computer Science', '3rd', 5, '2002-03-25', 'female', 'Mr. Verma', 'Mrs. Verma', '9876543293', 'Bangalore', '2021-08-01', 'active'),
(8, 'ME001', 'REG005', 'Mechanical', '2nd', 3, '2003-07-18', 'male', 'Mr. Singh', 'Mrs. Singh', '9876543294', 'Pune', '2022-08-01', 'active');

-- Room Allocations
INSERT INTO `room_allocations` (`student_id`, `room_id`, `hostel_id`, `allocation_date`, `bed_number`, `status`) VALUES
(1, 3, 1, '2023-08-15', 1, 'active'),
(2, 5, 2, '2023-08-15', 1, 'active'),
(3, 1, 1, '2023-08-15', 1, 'active'),
(5, 3, 1, '2023-08-15', 2, 'active');

-- Fees
INSERT INTO `fees` (`student_id`, `allocation_id`, `fee_type`, `amount`, `due_date`, `status`, `created_at`) VALUES
(1, 1, 'rent', 5000.00, DATE_ADD(CURDATE(), INTERVAL -5 DAY), 'overdue', NOW()),
(1, 1, 'mess', 2000.00, DATE_ADD(CURDATE(), INTERVAL -5 DAY), 'paid', NOW()),
(2, 2, 'rent', 7500.00, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'pending', NOW()),
(3, 3, 'rent', 5000.00, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'pending', NOW());

-- Visitors
INSERT INTO `visitors` (`student_id`, `visitor_name`, `visitor_phone`, `relationship`, `entry_date`, `exit_date`, `purpose`, `status`) VALUES
(1, 'Father Kumar', '9876543300', 'Father', '2024-06-15 10:30:00', '2024-06-15 15:30:00', 'Family visit', 'exited'),
(2, 'Sister Singh', '9876543301', 'Sister', '2024-06-16 14:00:00', NULL, 'Meeting', 'entered');

-- Complaints
INSERT INTO `complaints` (`student_id`, `category`, `title`, `description`, `priority`, `status`, `assigned_to`, `created_at`) VALUES
(1, 'room_condition', 'Wall Damage', 'There is a visible crack on the wall near the window', 'high', 'open', 2, NOW()),
(2, 'plumbing', 'Water Leakage', 'The bathroom tap is leaking constantly', 'medium', 'in_progress', 3, NOW()),
(3, 'cleanliness', 'Bathroom Cleanliness', 'Bathroom needs cleaning urgently', 'medium', 'resolved', 2, NOW());

-- Settings
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('app_name', 'Hostel Management System', 'Application Name'),
('app_version', '1.0.0', 'Current Version'),
('session_timeout', '1800', 'Session timeout in seconds'),
('max_login_attempts', '5', 'Maximum login attempts allowed'),
('lock_time', '300', 'Account lock time in seconds'),
('page_title', 'HMS - Hostel Management System', 'Default page title'),
('system_email', 'noreply@hostel.com', 'System email address');

COMMIT;
