-- ============================================================================
-- Rotary School Uran — "Recent Updates" backend
-- Database schema
--
-- Import this file into MySQL before using the admin panel, e.g.:
--   mysql -u root -p rotary_school < database/schema.sql
-- or via phpMyAdmin: Import > choose this file.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS rotary_school
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE rotary_school;

-- ----------------------------------------------------------------------------
-- admins — exactly one admin account, no self-registration.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(60)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- recent_updates — the "Recent Updates" News items shown on Home & News pages.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS recent_updates (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(255) NOT NULL,
  description  TEXT         NOT NULL,
  image        VARCHAR(255) NULL,               -- relative path, e.g. uploads/news/xxxx.jpg
  update_date  DATE         NOT NULL,
  status       ENUM('published','hidden') NOT NULL DEFAULT 'published',
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status_date (status, update_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Default admin account.
--   username: admin
--   password: Admin@12345   (CHANGE THIS IMMEDIATELY AFTER FIRST LOGIN — see
--                             README_BACKEND.md, section "Change the admin
--                             password", for how to generate a new hash)
-- The hash below is a bcrypt hash of "Admin@12345".
-- ----------------------------------------------------------------------------
INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2b$10$QkryBK/712xrfvlUYV7gbO3xTLs1iR.ykKunJkLmE1Om9lqXnokeu')
ON DUPLICATE KEY UPDATE username = username;

-- ----------------------------------------------------------------------------
-- A couple of sample rows so the Home/News pages aren't empty on first run.
-- Safe to delete from the admin dashboard.
-- ----------------------------------------------------------------------------
INSERT INTO recent_updates (title, description, image, update_date, status) VALUES
('Welcome to the new Recent Updates system',
 'This update was added from the MySQL seed data. Log in to the admin panel to edit or delete it, and to add your own updates.',
 NULL, CURDATE(), 'published');
