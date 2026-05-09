-- Run this in phpMyAdmin if you already imported schema.sql before the newsletter table existed.
-- Select database `kdu_global` then Import this file (or paste and run).

USE `kdu_global`;

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
