-- =============================================================================
-- AbleCare Database Schema
-- Database: ablecare_dp
-- Engine:   InnoDB | Charset: utf8mb4
-- =============================================================================

CREATE DATABASE IF NOT EXISTS ablecare_dp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ablecare_dp;

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- 1. users
-- Central authentication table for caregivers, healthcare providers, and admins.
-- Status 'pending' means waiting for admin approval before login is granted.
-- =============================================================================
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  role          ENUM('caregiver', 'healthcare_provider', 'admin') NOT NULL,
  first_name    VARCHAR(100)    NOT NULL,
  last_name     VARCHAR(100)    NOT NULL,
  email         VARCHAR(191)    NOT NULL,
  password      VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash',
  api_token     VARCHAR(64)     DEFAULT NULL COMMENT 'bearer token, set on login, cleared on logout',
  phone_number  VARCHAR(20)     DEFAULT NULL,
  profile_photo_path VARCHAR(255) DEFAULT NULL,
  status        ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_api_token (api_token),
  INDEX idx_users_role   (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 7. clinics
-- Created before healthcare_providers because providers carry a clinic FK.
-- Stores physical clinics that serve PWD/elderly patients.
-- =============================================================================
CREATE TABLE IF NOT EXISTS clinics (
  id                      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  name                    VARCHAR(200)  NOT NULL,
  address                 TEXT          NOT NULL,
  barangay                VARCHAR(100)  NOT NULL,
  latitude                DECIMAL(10,7) DEFAULT NULL,
  longitude               DECIMAL(10,7) DEFAULT NULL,
  contact_number          VARCHAR(30)   DEFAULT NULL,
  operating_hours         VARCHAR(200)  DEFAULT NULL,
  accepts_walk_ins        TINYINT(1)    NOT NULL DEFAULT 0,
  has_wheelchair_access   TINYINT(1)    NOT NULL DEFAULT 0,
  has_ground_floor_access TINYINT(1)    NOT NULL DEFAULT 0,
  created_at              DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_clinics_barangay (barangay)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 2. caregivers
-- Profile extension for users with role='caregiver'.
-- =============================================================================
CREATE TABLE IF NOT EXISTS caregivers (
  id         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED   NOT NULL,
  address    TEXT           DEFAULT NULL,
  barangay   VARCHAR(100)   DEFAULT NULL,
  latitude   DECIMAL(10,8)  DEFAULT NULL COMMENT 'pinned home/primary location',
  longitude  DECIMAL(11,8)  DEFAULT NULL COMMENT 'pinned home/primary location',
  created_at DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_caregivers_user_id (user_id),
  INDEX idx_caregivers_barangay (barangay),
  CONSTRAINT fk_caregivers_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 3. healthcare_providers
-- Profile extension for users with role='healthcare_provider'.
-- Optionally linked to a clinic.
-- =============================================================================
CREATE TABLE IF NOT EXISTS healthcare_providers (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  user_id        INT UNSIGNED  NOT NULL,
  specialization VARCHAR(150)  DEFAULT NULL COMMENT 'deprecated — use clinic_specializations',
  license_number VARCHAR(100)  DEFAULT NULL,
  prc_id_path    VARCHAR(255)  DEFAULT NULL COMMENT 'uploaded PRC ID image/PDF',
  clinic_id      INT UNSIGNED  DEFAULT NULL COMMENT 'nullable — provider may be independent',
  created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_hp_user_id (user_id),
  INDEX idx_hp_clinic_id (clinic_id),
  CONSTRAINT fk_hp_user
    FOREIGN KEY (user_id)   REFERENCES users   (id) ON DELETE CASCADE,
  CONSTRAINT fk_hp_clinic
    FOREIGN KEY (clinic_id) REFERENCES clinics (id) ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 4. patients
-- A patient is always linked to one caregiver.
-- Deleting a caregiver cascades and deletes all their patients.
-- =============================================================================
CREATE TABLE IF NOT EXISTS patients (
  id                   INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  caregiver_id         INT UNSIGNED   NOT NULL,
  first_name           VARCHAR(100)   NOT NULL,
  last_name            VARCHAR(100)   NOT NULL,
  date_of_birth        DATE           DEFAULT NULL,
  gender               ENUM('male', 'female', 'other') DEFAULT NULL,
  disability_category  ENUM('physical', 'sensory_visual', 'sensory_hearing', 'cognitive') DEFAULT NULL,
  specific_condition   VARCHAR(150)   DEFAULT NULL COMMENT 'e.g. cerebral_palsy, low_vision',
  medical_history      TEXT           DEFAULT NULL,
  created_at           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at           DATETIME       DEFAULT NULL COMMENT 'soft delete — NULL means active',

  PRIMARY KEY (id),
  INDEX idx_patients_caregiver_id        (caregiver_id),
  INDEX idx_patients_disability_category (disability_category),
  CONSTRAINT fk_patients_caregiver
    FOREIGN KEY (caregiver_id) REFERENCES caregivers (id)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 5. health_records
-- Clinical notes, vital signs, symptom logs, and medication entries per patient.
-- Cascades on patient delete.
-- =============================================================================
CREATE TABLE IF NOT EXISTS health_records (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  patient_id   INT UNSIGNED  NOT NULL,
  record_type  ENUM('vitals', 'symptom_log', 'medication', 'general') NOT NULL,
  notes        TEXT          DEFAULT NULL,
  recorded_by  INT UNSIGNED  NOT NULL COMMENT 'FK to users.id',
  recorded_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_hr_patient_id  (patient_id),
  INDEX idx_hr_recorded_by (recorded_by),
  INDEX idx_hr_record_type (record_type),
  CONSTRAINT fk_hr_patient
    FOREIGN KEY (patient_id)  REFERENCES patients (id) ON DELETE CASCADE,
  CONSTRAINT fk_hr_recorded_by
    FOREIGN KEY (recorded_by) REFERENCES users    (id) ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 6. ai_guidance_logs
-- Stores every AI Health Guidance request and Gemini response for audit trail.
-- Cascades on patient or caregiver delete.
-- =============================================================================
CREATE TABLE IF NOT EXISTS ai_guidance_logs (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id    INT UNSIGNED NOT NULL,
  caregiver_id  INT UNSIGNED NOT NULL,
  symptoms_input TEXT        NOT NULL,
  ai_response   TEXT         NOT NULL,
  severity      ENUM('low', 'medium', 'high') NOT NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_agl_patient_id   (patient_id),
  INDEX idx_agl_caregiver_id (caregiver_id),
  INDEX idx_agl_severity     (severity),
  CONSTRAINT fk_agl_patient
    FOREIGN KEY (patient_id)   REFERENCES patients   (id) ON DELETE CASCADE,
  CONSTRAINT fk_agl_caregiver
    FOREIGN KEY (caregiver_id) REFERENCES caregivers (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 8. clinic_specializations
-- Many-to-many linkage between clinics and the disability categories they serve.
-- Cascades when a clinic is deleted.
-- =============================================================================
CREATE TABLE IF NOT EXISTS clinic_specializations (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  clinic_id           INT UNSIGNED NOT NULL,
  disability_category ENUM('physical', 'sensory_visual', 'sensory_hearing', 'cognitive') NOT NULL,
  specific_condition  VARCHAR(150) DEFAULT NULL,

  PRIMARY KEY (id),
  INDEX idx_cs_clinic_id           (clinic_id),
  INDEX idx_cs_disability_category (disability_category),
  CONSTRAINT fk_cs_clinic
    FOREIGN KEY (clinic_id) REFERENCES clinics (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 9. clinic_recommendations
-- Records which clinic was recommended to which patient and its match score.
-- Cascades on patient or clinic delete.
-- =============================================================================
CREATE TABLE IF NOT EXISTS clinic_recommendations (
  id               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  patient_id       INT UNSIGNED   NOT NULL,
  clinic_id        INT UNSIGNED   NOT NULL,
  score            DECIMAL(5,2)   NOT NULL DEFAULT 0.00 COMMENT 'match score 0–100',
  recommended_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_cr_patient_id (patient_id),
  INDEX idx_cr_clinic_id  (clinic_id),
  CONSTRAINT fk_cr_patient
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE,
  CONSTRAINT fk_cr_clinic
    FOREIGN KEY (clinic_id)  REFERENCES clinics  (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 10. consultations
-- Tracks caregiver-initiated consultations with healthcare providers.
-- =============================================================================
CREATE TABLE IF NOT EXISTS consultations (
  id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id              INT UNSIGNED NOT NULL,
  caregiver_id            INT UNSIGNED NOT NULL,
  healthcare_provider_id  INT UNSIGNED NOT NULL,
  status                  ENUM('pending', 'accepted', 'completed', 'declined') NOT NULL DEFAULT 'pending',
  notes                   TEXT         DEFAULT NULL,
  created_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_con_patient_id             (patient_id),
  INDEX idx_con_caregiver_id           (caregiver_id),
  INDEX idx_con_healthcare_provider_id (healthcare_provider_id),
  INDEX idx_con_status                 (status),
  CONSTRAINT fk_con_patient
    FOREIGN KEY (patient_id)             REFERENCES patients              (id) ON DELETE CASCADE,
  CONSTRAINT fk_con_caregiver
    FOREIGN KEY (caregiver_id)           REFERENCES caregivers            (id) ON DELETE CASCADE,
  CONSTRAINT fk_con_hp
    FOREIGN KEY (healthcare_provider_id) REFERENCES healthcare_providers  (id) ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 11. therapy_schedules
-- Individual therapy sessions assigned by a healthcare provider to a patient.
-- =============================================================================
CREATE TABLE IF NOT EXISTS therapy_schedules (
  id                      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  patient_id              INT UNSIGNED  NOT NULL,
  healthcare_provider_id  INT UNSIGNED  NOT NULL,
  session_date            DATE          NOT NULL,
  session_time            TIME          NOT NULL,
  status                  ENUM('scheduled', 'completed', 'missed', 'cancelled') NOT NULL DEFAULT 'scheduled',
  notes                   TEXT          DEFAULT NULL,
  created_at              DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_ts_patient_id             (patient_id),
  INDEX idx_ts_healthcare_provider_id (healthcare_provider_id),
  INDEX idx_ts_session_date           (session_date),
  INDEX idx_ts_status                 (status),
  CONSTRAINT fk_ts_patient
    FOREIGN KEY (patient_id)             REFERENCES patients             (id) ON DELETE CASCADE,
  CONSTRAINT fk_ts_hp
    FOREIGN KEY (healthcare_provider_id) REFERENCES healthcare_providers (id) ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 12. medication_reminders
-- Daily medication reminders tied to a patient.
-- Cascades on patient delete.
-- =============================================================================
CREATE TABLE IF NOT EXISTS medication_reminders (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id       INT UNSIGNED NOT NULL,
  medication_name  VARCHAR(200) NOT NULL,
  dosage           VARCHAR(100) DEFAULT NULL,
  reminder_time    TIME         NOT NULL,
  frequency        VARCHAR(50)  NOT NULL DEFAULT 'daily' COMMENT 'daily, twice_daily, weekly, etc.',
  is_active        TINYINT(1)   NOT NULL DEFAULT 1,
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_mr_patient_id (patient_id),
  INDEX idx_mr_is_active  (is_active),
  CONSTRAINT fk_mr_patient
    FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 13. emergency_alerts
-- Real-time emergency alerts triggered by caregivers with GPS location.
-- notified_responder_id and notified_admin_id are loose references (nullable).
-- =============================================================================
CREATE TABLE IF NOT EXISTS emergency_alerts (
  id                    INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  patient_id            INT UNSIGNED   NOT NULL,
  caregiver_id          INT UNSIGNED   NOT NULL,
  latitude              DECIMAL(10,7)  DEFAULT NULL,
  longitude             DECIMAL(10,7)  DEFAULT NULL,
  status                ENUM('active', 'responded', 'resolved') NOT NULL DEFAULT 'active',
  notified_responder_id INT UNSIGNED   DEFAULT NULL COMMENT 'user who responded',
  notified_admin_id     INT UNSIGNED   DEFAULT NULL COMMENT 'admin notified',
  created_at            DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at           DATETIME       DEFAULT NULL,

  PRIMARY KEY (id),
  INDEX idx_ea_patient_id   (patient_id),
  INDEX idx_ea_caregiver_id (caregiver_id),
  INDEX idx_ea_status       (status),
  CONSTRAINT fk_ea_patient
    FOREIGN KEY (patient_id)   REFERENCES patients   (id) ON DELETE CASCADE,
  CONSTRAINT fk_ea_caregiver
    FOREIGN KEY (caregiver_id) REFERENCES caregivers (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 14. messages
-- Direct messaging between any two users (caregiver ↔ provider, etc.)
-- =============================================================================
CREATE TABLE IF NOT EXISTS messages (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sender_id    INT UNSIGNED NOT NULL,
  receiver_id  INT UNSIGNED NOT NULL,
  message_text TEXT         NOT NULL,
  is_read      TINYINT(1)   NOT NULL DEFAULT 0,
  sent_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_msg_sender_id   (sender_id),
  INDEX idx_msg_receiver_id (receiver_id),
  INDEX idx_msg_is_read     (is_read),
  CONSTRAINT fk_msg_sender
    FOREIGN KEY (sender_id)   REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_receiver
    FOREIGN KEY (receiver_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 15. notifications
-- In-app push notifications for all user types.
-- =============================================================================
CREATE TABLE IF NOT EXISTS notifications (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  title      VARCHAR(200) NOT NULL,
  message    TEXT         NOT NULL,
  type       ENUM('emergency', 'consultation', 'therapy', 'message', 'system') NOT NULL,
  is_read    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_notif_user_id (user_id),
  INDEX idx_notif_is_read (is_read),
  INDEX idx_notif_type    (type),
  CONSTRAINT fk_notif_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 16. activity_logs
-- Audit trail for admin actions (approvals, rejections, data changes, etc.)
-- =============================================================================
CREATE TABLE IF NOT EXISTS activity_logs (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id           INT UNSIGNED NOT NULL COMMENT 'FK to users.id (admin role)',
  action_description TEXT         NOT NULL,
  created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  INDEX idx_al_admin_id (admin_id),
  CONSTRAINT fk_al_admin
    FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;
