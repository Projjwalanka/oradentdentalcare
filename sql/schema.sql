-- ---------------------------------------------------------------------------
-- ORADENT Dental Care — database schema
--
-- Import once into the MySQL database you create in Hostinger hPanel:
--   hPanel → Databases → phpMyAdmin → Import → schema.sql
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;
SET time_zone = '+05:30';

-- ------------------------------------------------------------ appointments --
CREATE TABLE IF NOT EXISTS `appointments` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference`      VARCHAR(16)  NOT NULL,
  `patient_name`   VARCHAR(120) NOT NULL,
  `phone`          VARCHAR(15)  NOT NULL,
  `email`          VARCHAR(190) DEFAULT NULL,
  `service_slug`   VARCHAR(64)  NOT NULL,
  `appt_date`      DATE         NOT NULL,
  `appt_time`      TIME         NOT NULL,
  `notes`          TEXT         DEFAULT NULL,
  `status`         ENUM('confirmed','completed','cancelled','no_show')
                                NOT NULL DEFAULT 'confirmed',
  `is_first_visit` TINYINT(1)   NOT NULL DEFAULT 1,
  `source`         VARCHAR(20)  NOT NULL DEFAULT 'website',
  `ip`             VARCHAR(45)  DEFAULT NULL,
  `user_agent`     VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,

  -- Generated column used purely to enforce "one live booking per slot".
  -- Cancelled rows get NULL, and MySQL allows unlimited NULLs in a UNIQUE key,
  -- so a cancelled slot frees itself while live slots stay collision-proof
  -- even under two simultaneous requests.
  `slot_guard`     TINYINT(1) GENERATED ALWAYS AS
                     (CASE WHEN `status` = 'cancelled' THEN NULL ELSE 1 END) STORED,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_reference` (`reference`),
  UNIQUE KEY `uniq_live_slot` (`appt_date`, `appt_time`, `slot_guard`),
  KEY `idx_date_status` (`appt_date`, `status`),
  KEY `idx_phone_date`  (`phone`, `appt_date`),
  KEY `idx_created`     (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------- blocked_slots ----
-- A row with block_time = NULL closes the whole day (holiday, leave).
CREATE TABLE IF NOT EXISTS `blocked_slots` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `block_date` DATE         NOT NULL,
  `block_time` TIME         DEFAULT NULL,
  `reason`     VARCHAR(190) DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_block` (`block_date`, `block_time`),
  KEY `idx_block_date` (`block_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------- admin_users ----
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(60)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name`  VARCHAR(120) NOT NULL,
  `last_login_at` DATETIME     DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------- login_attempts ----
-- Backs the brute-force lockout on the admin login.
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`           VARCHAR(45)  NOT NULL,
  `username`     VARCHAR(60)  DEFAULT NULL,
  `attempted_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------ contact_messages ----
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `phone`      VARCHAR(15)  NOT NULL,
  `email`      VARCHAR(190) DEFAULT NULL,
  `subject`    VARCHAR(190) DEFAULT NULL,
  `message`    TEXT         NOT NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `ip`         VARCHAR(45)  DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_read_created` (`is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
