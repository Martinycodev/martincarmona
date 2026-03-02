-- Migration 014: Añadir roles y vínculos a tabla usuarios
-- Ejecutar en XAMPP phpMyAdmin o consola MySQL

ALTER TABLE `usuarios`
  ADD COLUMN `rol` ENUM('empresa','admin','propietario','trabajador') NOT NULL DEFAULT 'empresa' AFTER `email`,
  ADD COLUMN `propietario_id` int(11) DEFAULT NULL AFTER `rol`,
  ADD COLUMN `trabajador_id`  int(11) DEFAULT NULL AFTER `propietario_id`,
  ADD CONSTRAINT `fk_usuarios_propietario` FOREIGN KEY (`propietario_id`) REFERENCES `propietarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuarios_trabajador`  FOREIGN KEY (`trabajador_id`)  REFERENCES `trabajadores` (`id`) ON DELETE SET NULL;

-- El usuario Martin existente queda automáticamente como 'empresa' por el DEFAULT
-- Los nuevos usuarios propietario/trabajador se crean desde el panel /admin/usuarios
