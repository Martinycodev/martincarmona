-- Migration 013: Módulo Fitosanitarios
-- Ejecutar en XAMPP phpMyAdmin o consola MySQL

-- Inventario de productos fitosanitarios
CREATE TABLE IF NOT EXISTS `fitosanitarios_inventario` (
  `id`           int(11)        NOT NULL AUTO_INCREMENT,
  `producto`     varchar(255)   NOT NULL,
  `fecha_compra` date           DEFAULT NULL,
  `cantidad`     decimal(10,2)  DEFAULT NULL,
  `unidad`       varchar(50)    DEFAULT NULL,   -- 'litros', 'kg', 'unidades'...
  `proveedor_id` int(11)        DEFAULT NULL,
  `id_user`      bigint(20)     NOT NULL,
  `created_at`   timestamp      NULL DEFAULT current_timestamp(),
  `updated_at`   timestamp      NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_fitosan_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historial de aplicaciones (manual + auto-generadas desde tareas)
CREATE TABLE IF NOT EXISTS `fitosanitarios_aplicaciones` (
  `id`         int(11)       NOT NULL AUTO_INCREMENT,
  `parcela_id` int(11)       DEFAULT NULL,
  `producto`   varchar(255)  NOT NULL,
  `fecha`      date          NOT NULL,
  `cantidad`   decimal(10,2) DEFAULT NULL,
  `tarea_id`   int(11)       DEFAULT NULL,   -- FK a tareas (NULL si es manual)
  `id_user`    bigint(20)    NOT NULL,
  `created_at` timestamp     NULL DEFAULT current_timestamp(),
  `updated_at` timestamp     NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_fitosan_ap_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fitosan_ap_tarea`   FOREIGN KEY (`tarea_id`)   REFERENCES `tareas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
