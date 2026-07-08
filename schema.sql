-- =====================================================
-- Expense Tracker Database Schema
-- 5cs045 Coursework
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT(11)        NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50)    NOT NULL,
    `email`      VARCHAR(100)   NOT NULL,
    `password`   VARCHAR(255)   NOT NULL,
    `budget`     DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Expenses table
CREATE TABLE IF NOT EXISTS `expense_tracker` (
    `id`           INT(11)       NOT NULL AUTO_INCREMENT,
    `user_id`      INT(11)       NOT NULL,
    `name`         VARCHAR(100)  NOT NULL,
    `amount`       DECIMAL(10,2) NOT NULL,
    `category`     VARCHAR(50)   NOT NULL,
    `date_created` DATE          NOT NULL,
    `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
