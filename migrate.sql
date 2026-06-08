-- ============================================================
-- Our Lady of Fatima VTC — Full Database Migration
-- Run once: mysql -u ourladyo_db -p ourladyo_db < migrate.sql
-- ============================================================

-- 1. admin_users
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`                VARCHAR(200) NOT NULL UNIQUE,
    `password`             VARCHAR(255) NOT NULL,
    `role`                 ENUM('admin','superadmin','uploader') NOT NULL DEFAULT 'admin',
    `is_active`            TINYINT(1) NOT NULL DEFAULT 1,
    `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. events
CREATE TABLE IF NOT EXISTS `events` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `image`       VARCHAR(255) DEFAULT NULL,
    `event_date`  DATE NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. contact_messages
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(120) NOT NULL,
    `email`        VARCHAR(200) NOT NULL,
    `subject`      VARCHAR(255) NOT NULL,
    `message`      TEXT NOT NULL,
    `is_read`      TINYINT(1) NOT NULL DEFAULT 0,
    `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX(`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. newsletter_subscribers
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`         VARCHAR(200) NOT NULL UNIQUE,
    `subscribed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. password_resets (for forgot password)
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(200) NOT NULL,
    `token`      VARCHAR(100) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    INDEX(`token`),
    INDEX(`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. gallery_images (optional DB tracking)
CREATE TABLE IF NOT EXISTS `gallery_images` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `filename`    VARCHAR(255) NOT NULL,
    `category`    VARCHAR(100) NOT NULL,
    `uploaded_by` INT UNSIGNED DEFAULT NULL,
    `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Safe column additions for existing installs
ALTER TABLE `admin_users`
    ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS `must_change_password` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `events`
    ADD COLUMN IF NOT EXISTS `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Default superadmin (password: Admin@1234 — CHANGE IMMEDIATELY after first login)
INSERT IGNORE INTO `admin_users` (`email`,`password`,`role`,`is_active`,`must_change_password`)
VALUES ('admin@ourladyoffatimavtc.co.zw',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'superadmin',1,1);
