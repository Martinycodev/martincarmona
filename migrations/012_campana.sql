-- Migration 012: Módulo Campaña (recolección de aceituna)
-- Ejecutar en XAMPP phpMyAdmin o consola MySQL

-- Tabla de campañas (ej. '25/26')
CREATE TABLE IF NOT EXISTS `campanas` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `nombre`      varchar(20)    NOT NULL,           -- '25/26', '26/27' ...
  `fecha_inicio` date          NOT NULL,
  `fecha_fin`   date           DEFAULT NULL,
  `activa`      tinyint(1)     NOT NULL DEFAULT 1,
  `precio_venta` decimal(8,4)  DEFAULT NULL,       -- €/kg aceite, se fija al cerrar
  `id_user`     bigint(20)     NOT NULL,
  `created_at`  timestamp      NULL DEFAULT current_timestamp(),
  `updated_at`  timestamp      NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registros diarios de recolección
CREATE TABLE IF NOT EXISTS `campana_registros` (
  `id`             int(11)       NOT NULL AUTO_INCREMENT,
  `campana_id`     int(11)       NOT NULL,
  `parcela_id`     int(11)       DEFAULT NULL,
  `fecha`          date          NOT NULL,
  `kilos`          decimal(10,2) NOT NULL DEFAULT 0.00,
  `rendimiento_pct` decimal(5,2) DEFAULT NULL,      -- % aceite/kg oliva
  `beneficio`      decimal(12,2) DEFAULT NULL,      -- kilos * rendimiento_pct/100 * precio_venta
  `id_user`        bigint(20)    NOT NULL,
  `created_at`     timestamp     NULL DEFAULT current_timestamp(),
  `updated_at`     timestamp     NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_campana_registros_campana` FOREIGN KEY (`campana_id`) REFERENCES `campanas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_campana_registros_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
