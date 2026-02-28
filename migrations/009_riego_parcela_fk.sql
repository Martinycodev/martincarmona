-- Migration 009: Añadir parcela_id FK a riegos
-- Ejecutar en XAMPP phpMyAdmin o consola MySQL

ALTER TABLE `riegos`
  ADD COLUMN `parcela_id` int(11) DEFAULT NULL AFTER `propiedad`,
  ADD CONSTRAINT `fk_riegos_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcelas` (`id`) ON DELETE SET NULL;

-- propiedad (texto) se mantiene por datos históricos
