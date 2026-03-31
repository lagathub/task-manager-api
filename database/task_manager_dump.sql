-- ============================================================
-- Task Manager API - SQL Dump
-- Database: task_manager
-- Generated: 2026-03-28
-- ============================================================

CREATE DATABASE IF NOT EXISTS `task_manager`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `task_manager`;

-- ----------------------------
-- Table: tasks
-- ----------------------------
DROP TABLE IF EXISTS `tasks`;

CREATE TABLE `tasks` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255)    NOT NULL,
  `due_date`   DATE            NOT NULL,
  `priority`   ENUM('low','medium','high') NOT NULL,
  `status`     ENUM('pending','in_progress','done') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tasks_title_due_date_unique` (`title`, `due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: migrations (Laravel internal)
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT          NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` VALUES
(1, '2026_03_28_000001_create_tasks_table', 1);

-- ----------------------------
-- Sample seed data
-- ----------------------------
INSERT INTO `tasks` (`title`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES
('Set up CI/CD pipeline',          DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'high',   'pending',     NOW(), NOW()),
('Write unit tests for auth module',DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'high',   'in_progress', NOW(), NOW()),
('Update API documentation',        DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'medium', 'pending',     NOW(), NOW()),
('Refactor database queries',       DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'medium', 'done',        NOW(), NOW()),
('Clean up unused dependencies',    DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'low',    'pending',     NOW(), NOW());
