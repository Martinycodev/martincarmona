CREATE TABLE IF NOT EXISTS `documentos_parcelas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcela_id` int(11) NOT NULL,
  `tipo` enum('escritura','permiso_riego','otro') NOT NULL DEFAULT 'otro',
  `nombre` varchar(255) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `id_user` bigint(20) NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_docs_parcela` (`parcela_id`),
  KEY `idx_docs_user` (`id_user`),
  CONSTRAINT `fk_docs_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
