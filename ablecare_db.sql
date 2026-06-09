-- ============================================================
--  AbleCare Database Script
--  Database: ablecare_dp
-- ============================================================

CREATE DATABASE IF NOT EXISTS ablecare_dp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ablecare_dp;

-- ------------------------------------------------------------
--  Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  full_name     VARCHAR(150)     NOT NULL,
  email         VARCHAR(191)     NOT NULL,
  password_hash VARCHAR(255)     NOT NULL,
  role          ENUM('lgu_admin','healthcare_provider') NOT NULL,
  created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Optional: seed a default admin for testing
--  Password is: Admin@1234  (bcrypt hash)
-- ------------------------------------------------------------
-- INSERT INTO users (full_name, email, password_hash, role) VALUES
-- ('System Admin', 'admin@ablecare.com',
--  '$2y$12$9LHlGr0OFP0XkAVGGf7s1.xFkT7yJb3D1Hq3K6JzX4LFJFU8n8Iay',
--  'lgu_admin');
