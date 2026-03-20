-- Añadir teléfono de aseguradora a vehículos
ALTER TABLE `vehiculos` ADD COLUMN `telefono_aseguradora` varchar(30) DEFAULT NULL AFTER `seguro`;
