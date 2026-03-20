-- Tabla de recordatorios / notificaciones
-- Almacena tanto recordatorios automáticos como personalizados
CREATE TABLE IF NOT EXISTS `recordatorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) NOT NULL,
  `tipo` enum('itv','cuentas','fitosanitario','jornadas','personalizado') NOT NULL DEFAULT 'personalizado',
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_aviso` date NOT NULL COMMENT 'Fecha en la que se debe mostrar el recordatorio',
  `fecha_referencia` date DEFAULT NULL COMMENT 'Fecha del evento real (ej: fecha ITV)',
  `entidad_id` int(11) DEFAULT NULL COMMENT 'ID del vehículo, trabajador, etc. relacionado',
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'El usuario puede desactivar tipos de recordatorio',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_fecha` (`id_user`, `fecha_aviso`),
  KEY `idx_user_leido` (`id_user`, `leido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preferencias de notificaciones por usuario
CREATE TABLE IF NOT EXISTS `notificaciones_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) NOT NULL,
  `tipo` varchar(50) NOT NULL COMMENT 'itv, cuentas, fitosanitario, personalizado',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `dias_antelacion` int(11) NOT NULL DEFAULT 7 COMMENT 'Días antes del evento para avisar',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_tipo` (`id_user`, `tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Si la tabla ya existe, añadir el tipo 'jornadas' al enum
-- ALTER TABLE `recordatorios` MODIFY COLUMN `tipo` enum('itv','cuentas','fitosanitario','jornadas','personalizado') NOT NULL DEFAULT 'personalizado';

-- Insertar config por defecto (se ejecuta al primer login o por seed)
-- INSERT IGNORE INTO notificaciones_config (id_user, tipo, activo, dias_antelacion) VALUES
-- (USER_ID, 'itv', 1, 15),
-- (USER_ID, 'cuentas', 1, 1),
-- (USER_ID, 'fitosanitario', 1, 7),
-- (USER_ID, 'jornadas', 1, 1);
