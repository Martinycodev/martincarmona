-- Migración 002: Crear tabla `pagos_mensuales_trabajadores`
-- Fecha: 2026-02-26
-- Descripción: Registro de deuda mensual por trabajador (generado al cerrar mes)

CREATE TABLE `pagos_mensuales_trabajadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trabajador_id` int(11) NOT NULL,
  `month` tinyint(2) NOT NULL COMMENT '1-12',
  `year` smallint(4) NOT NULL COMMENT 'Ej: 2026',
  `importe_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pagado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_pago` date DEFAULT NULL,
  `id_user` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_trabajador_month_year` (`trabajador_id`, `month`, `year`),
  KEY `fk_pmt_trabajador` (`trabajador_id`),
  KEY `fk_pmt_user` (`id_user`),
  CONSTRAINT `fk_pmt_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pmt_user` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
