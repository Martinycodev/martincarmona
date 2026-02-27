-- Añadir columna propietario_id (nullable, para no romper registros existentes)
ALTER TABLE `parcelas`
  ADD COLUMN `propietario_id` int(11) DEFAULT NULL AFTER `propietario`;

-- Añadir FK constraint
ALTER TABLE `parcelas`
  ADD CONSTRAINT `fk_parcelas_propietario`
  FOREIGN KEY (`propietario_id`) REFERENCES `propietarios` (`id`)
  ON DELETE SET NULL;
