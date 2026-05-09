-- KDU Global — starter schema (MySQL 8+)
-- Import via phpMyAdmin or: mysql -u root -p kdu_global < schema.sql

CREATE DATABASE IF NOT EXISTS `kdu_global`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kdu_global`;

-- Programs (matches frontend programs.json shape)
CREATE TABLE IF NOT EXISTS `programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(80) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `level` VARCHAR(40) NOT NULL DEFAULT 'Undergraduate',
  `duration` VARCHAR(50) NOT NULL DEFAULT '',
  `campus` VARCHAR(120) NOT NULL DEFAULT '',
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `tags` JSON NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_programs_slug` (`slug`),
  KEY `idx_programs_level` (`level`),
  FULLTEXT KEY `ft_programs_search` (`title`, `description`)
) ENGINE=InnoDB;

-- News / blog
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `excerpt` VARCHAR(500) NOT NULL DEFAULT '',
  `body` MEDIUMTEXT NULL,
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `object_position` VARCHAR(40) NOT NULL DEFAULT 'center',
  `published_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_news_slug` (`slug`),
  KEY `idx_news_published` (`published_at`),
  FULLTEXT KEY `ft_news_search` (`title`, `excerpt`, `body`)
) ENGINE=InnoDB;

-- Events
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `summary` VARCHAR(500) NOT NULL DEFAULT '',
  `starts_at` DATETIME NOT NULL,
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_events_slug` (`slug`),
  KEY `idx_events_starts` (`starts_at`)
) ENGINE=InnoDB;

-- Contact form submissions (no auth — rate-limit at reverse proxy in production)
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `subject` VARCHAR(200) NOT NULL DEFAULT '',
  `message` TEXT NOT NULL,
  `ip_address` VARBINARY(16) NULL,
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB;

-- Newsletter sign-ups (homepage footer form)
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `source` VARCHAR(80) NOT NULL DEFAULT 'homepage',
  `ip_address` VARBINARY(16) NULL,
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
  `subscribed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`),
  KEY `idx_newsletter_subscribed` (`subscribed_at`)
) ENGINE=InnoDB;

-- Seed examples (optional)
INSERT IGNORE INTO `programs` (`slug`, `title`, `description`, `level`, `duration`, `campus`, `image`, `tags`) VALUES
('smart-computing', 'BSc Smart Computing', 'Modern software engineering and cloud-ready systems.', 'Bachelor', '4 years', 'KDU Global Campus', 'assets/images/program-smart-computing.png', JSON_ARRAY('Software', 'Cloud', 'Systems')),
('artificial-intelligence', 'BSc Artificial Intelligence', 'AI fundamentals and applied machine learning.', 'Bachelor', '4 years', 'KDU Global Campus', 'assets/images/program-artificial-intelligence.png', JSON_ARRAY('AI', 'ML', 'Innovation'));

INSERT IGNORE INTO `news` (`slug`, `title`, `excerpt`, `body`, `image`, `published_at`) VALUES
('welcome-2026', 'Welcome to Spring 2026', 'Orientation and key dates for new students.', 'Full article text here.', 'assets/images/news-1.png', NOW());

INSERT IGNORE INTO `events` (`slug`, `title`, `summary`, `starts_at`, `image`) VALUES
('open-day', 'Campus Open Day', 'Tour labs and meet faculty.', DATE_ADD(NOW(), INTERVAL 14 DAY), 'assets/images/event-1.png');
