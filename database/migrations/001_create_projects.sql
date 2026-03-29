-- Tabla de proyectos del portfolio
CREATE TABLE IF NOT EXISTS `projects` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(255)  NOT NULL,
    `slug`        VARCHAR(255)  NOT NULL UNIQUE,
    `category`    ENUM('web', 'diseño', 'fotografía', 'video') NOT NULL,
    `description` TEXT,
    `content`     LONGTEXT,
    `cover`       VARCHAR(500),
    `images`      JSON,
    `tags`        JSON,
    `url`         VARCHAR(500),
    `featured`    TINYINT(1)    NOT NULL DEFAULT 0,
    `published`   TINYINT(1)    NOT NULL DEFAULT 1,
    `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_category`   (`category`),
    INDEX `idx_published`  (`published`),
    INDEX `idx_featured`   (`featured`),
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
