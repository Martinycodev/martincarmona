-- Migración: Precio flexible en tareas (hora / fijo / unidad)
-- Fecha: 2026-03-23
-- Descripción: Añade soporte para 3 tipos de tarificación:
--   - 'hora': precio por hora (comportamiento actual)
--   - 'fijo': precio fijo por servicio (ej: 250€ por sulfatar)
--   - 'unidad': precio por unidad (ej: 0.03€/kg de aceituna)

-- Tabla trabajos: tipo de precio por defecto del trabajo
ALTER TABLE `trabajos`
  ADD COLUMN `tipo_precio` ENUM('hora','fijo','unidad') NOT NULL DEFAULT 'hora' AFTER `precio_hora`,
  ADD COLUMN `unidad_label` VARCHAR(20) DEFAULT NULL AFTER `tipo_precio`;

-- Tabla tarea_trabajos: snapshot del tipo de precio (como ya se hace con precio_hora)
ALTER TABLE `tarea_trabajos`
  ADD COLUMN `tipo_precio` ENUM('hora','fijo','unidad') NOT NULL DEFAULT 'hora' AFTER `precio_hora`,
  ADD COLUMN `unidad_label` VARCHAR(20) DEFAULT NULL AFTER `tipo_precio`,
  ADD COLUMN `cantidad_unidades` DECIMAL(10,2) DEFAULT NULL AFTER `unidad_label`;
