ALTER TABLE `trabajadores`
  ADD COLUMN `baja_ss` date DEFAULT NULL AFTER `alta_ss`,
  ADD COLUMN `imagen_dni_anverso` varchar(255) DEFAULT NULL,
  ADD COLUMN `imagen_dni_reverso` varchar(255) DEFAULT NULL,
  ADD COLUMN `imagen_ss` varchar(255) DEFAULT NULL;
