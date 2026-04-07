-- =============================================================
--  Módulo Planner — Fase 7 del roadmap
--  Crea las 6 tablas del apartado 7.2.
--
--  Convenciones:
--    - Prefijo `planner_` para no colisionar con tablas de la web.
--    - InnoDB + utf8mb4 (consistente con la migración 001).
--    - Orden de creación respetando las foreign keys.
--    - IF NOT EXISTS para que el script sea idempotente.
--
--  Aplicar con:
--    mysql -u USUARIO -p martincarmona < database/migrations/002_create_planner_tables.sql
--  o pegando el contenido completo en phpMyAdmin → SQL.
-- =============================================================

-- ─────────────────────────────────────────────────────────────
-- 1. planner_goals — propósitos a largo plazo del usuario
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `planner_goals` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`         VARCHAR(255)      NOT NULL,
    `description`   TEXT,
    `horizon_weeks` SMALLINT UNSIGNED NOT NULL                COMMENT 'Plazo en semanas',
    `priority`      TINYINT UNSIGNED  NOT NULL DEFAULT 3       COMMENT '1=máxima, 5=mínima',
    `status`        ENUM('active','paused','done','dropped') NOT NULL DEFAULT 'active',
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_status_priority` (`status`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 2. planner_constraints — ventanas de tiempo recurrentes
--    (sueño, comidas, citas fijas, focus_window, no_work)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `planner_constraints` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `label`        VARCHAR(120) NOT NULL,
    `type`         ENUM('sleep','meal','fixed_event','focus_window','no_work') NOT NULL,
    `weekday_mask` TINYINT UNSIGNED NOT NULL COMMENT 'Bitmask 0-127 (L=1, M=2, X=4, J=8, V=16, S=32, D=64)',
    `start_time`   TIME NOT NULL,
    `end_time`     TIME NOT NULL,
    `is_active`    BOOLEAN DEFAULT TRUE,

    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 3. planner_ai_logs — auditoría de cada llamada a Anthropic
--    Se crea ANTES de schedule_blocks porque éste la referencia.
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `planner_ai_logs` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `endpoint`      VARCHAR(64) NOT NULL,
    `model`         VARCHAR(64) NOT NULL,
    `prompt_hash`   CHAR(64)    NOT NULL,
    `request_json`  JSON        NOT NULL,
    `response_json` JSON        NULL,
    `tokens_in`     INT UNSIGNED,
    `tokens_out`    INT UNSIGNED,
    `latency_ms`    INT UNSIGNED,
    `status`        ENUM('ok','validation_failed','api_error','retry') NOT NULL,
    `created_at`    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 4. planner_schedule_blocks — calendario generado por la IA
--    (la verdad operativa del día)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `planner_schedule_blocks` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `goal_id`        INT UNSIGNED NULL,
    `title`          VARCHAR(255) NOT NULL,
    `description`    TEXT,
    `block_type`     ENUM('deep_work','admin','rest','meal','exercise','review') NOT NULL,
    `scheduled_date` DATE NOT NULL,
    `start_at`       DATETIME NOT NULL,
    `end_at`         DATETIME NOT NULL,
    `status`         ENUM('pending','in_progress','done','postponed','skipped') NOT NULL DEFAULT 'pending',
    `generated_by`   ENUM('ai','manual','recalc') NOT NULL,
    `ai_log_id`      INT UNSIGNED NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_date_status` (`scheduled_date`, `status`),
    INDEX `idx_start`       (`start_at`),

    CONSTRAINT `fk_block_goal`
        FOREIGN KEY (`goal_id`) REFERENCES `planner_goals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 5. planner_postpone_log — bitácora de aplazamientos
--    (combustible del check-in diario)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `planner_postpone_log` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `block_id`      INT UNSIGNED NOT NULL,
    `postponed_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reason`        VARCHAR(255) NULL,
    `reschedule_to` DATETIME     NULL,

    INDEX `idx_block` (`block_id`),

    CONSTRAINT `fk_postpone_block`
        FOREIGN KEY (`block_id`) REFERENCES `planner_schedule_blocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────
-- 6. planner_checkins — retrospectivas diarias
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `planner_checkins` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `checkin_date` DATE             NOT NULL UNIQUE,
    `mood`         TINYINT UNSIGNED COMMENT '1-5',
    `energy`       TINYINT UNSIGNED COMMENT '1-5',
    `notes`        TEXT,
    `ai_summary`   TEXT,
    `load_factor`  DECIMAL(3,2)     DEFAULT 1.00 COMMENT 'Multiplicador aplicado a próximos días',
    `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
