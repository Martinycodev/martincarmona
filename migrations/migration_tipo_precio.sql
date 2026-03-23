-- Migración: Precio variable en tareas
-- Fecha: 2026-03-23
-- Descripción: Añade campo precio_fijo en tarea_trabajos para tareas con precio variable.
--   - Si precio_fijo IS NOT NULL → el coste de la tarea es ese valor (ignora horas para el cálculo)
--   - Si precio_fijo IS NULL → se usa el cálculo actual: precio_hora × horas
--   Las horas se siguen registrando siempre para tracking de tiempo.

ALTER TABLE `tarea_trabajos`
  ADD COLUMN `precio_fijo` DECIMAL(10,2) DEFAULT NULL AFTER `precio_hora`;
