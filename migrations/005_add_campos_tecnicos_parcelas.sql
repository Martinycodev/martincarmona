ALTER TABLE `parcelas`
  ADD COLUMN `referencia_catastral` varchar(50) DEFAULT NULL AFTER `descripcion`,
  ADD COLUMN `tipo_olivos` varchar(100) DEFAULT NULL AFTER `referencia_catastral`,
  ADD COLUMN `año_plantacion` year(4) DEFAULT NULL AFTER `tipo_olivos`,
  ADD COLUMN `tipo_plantacion` enum('tradicional','intensivo','superintensivo') DEFAULT NULL AFTER `año_plantacion`,
  ADD COLUMN `riego_secano` enum('riego','secano') DEFAULT NULL AFTER `tipo_plantacion`,
  ADD COLUMN `corta` enum('par','impar','siempre') DEFAULT NULL AFTER `riego_secano`;
