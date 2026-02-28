-- Migration 011: Adjuntos para vehículos y herramientas
-- Ejecutar en XAMPP phpMyAdmin o consola MySQL

-- Vehículos: ficha técnica y póliza de seguro
ALTER TABLE `vehiculos`
  ADD COLUMN `ficha_tecnica` varchar(255) DEFAULT NULL,
  ADD COLUMN `poliza_seguro` varchar(255) DEFAULT NULL;

-- Herramientas: PDF de instrucciones
ALTER TABLE `herramientas`
  ADD COLUMN `instrucciones_pdf` varchar(255) DEFAULT NULL;
