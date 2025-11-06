-- ============================================
-- Task Reminder Application - Database Schema
-- ============================================
-- Database: task_reminder
-- Created: 2025
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `task_reminder` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `task_reminder`;

-- ============================================
-- Table: users
-- ============================================
-- User accounts for authentication
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    `full_name` VARCHAR(100) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'Profile picture path',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_username` (`username`),
    KEY `idx_email` (`email`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: tasks
-- ============================================
-- Main table for storing tasks
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `due_date` DATETIME DEFAULT NULL,
    `user_id` INT(11) NOT NULL COMMENT 'Owner of the task',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_due_date` (`due_date`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: reminders
-- ============================================
-- Stores reminders for tasks
CREATE TABLE IF NOT EXISTS `reminders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `task_id` INT(11) NOT NULL,
    `reminder_time` DATETIME NOT NULL,
    `is_sent` TINYINT(1) NOT NULL DEFAULT 0,
    `reminder_type` ENUM('email', 'notification', 'both') NOT NULL DEFAULT 'notification',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_task_id` (`task_id`),
    KEY `idx_reminder_time` (`reminder_time`),
    KEY `idx_is_sent` (`is_sent`),
    CONSTRAINT `fk_reminders_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: task_history
-- ============================================
-- Audit trail for task changes
CREATE TABLE IF NOT EXISTS `task_history` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `task_id` INT(11) NOT NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'created, updated, deleted, status_changed, priority_changed',
    `old_value` TEXT DEFAULT NULL,
    `new_value` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_task_id` (`task_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_history_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample Data (Optional - for testing)
-- ============================================

-- Insert sample user (password: admin123)
-- Password hash: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`) VALUES
('admin', 'admin@taskreminder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
('demo', 'demo@taskreminder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User');

-- Note: Sample tasks will be created after user login
-- You can manually insert tasks with user_id = 1 for testing

-- ============================================
-- Indexes Summary
-- ============================================
-- Users table indexes:
--   - Primary: id
--   - Unique: username, email
--   - Indexes: is_active
--
-- Tasks table indexes:
--   - Primary: id
--   - Foreign: user_id → users.id
--   - Indexes: status, priority, due_date, user_id, created_at
--
-- Reminders table indexes:
--   - Primary: id
--   - Foreign: task_id → tasks.id
--   - Indexes: reminder_time, is_sent
--
-- Task_history table indexes:
--   - Primary: id
--   - Foreign: task_id → tasks.id
--   - Indexes: action, created_at
-- ============================================

