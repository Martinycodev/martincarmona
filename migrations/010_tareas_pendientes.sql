-- Migration 010: Tareas pendientes sin fecha
-- Ejecutar en XAMPP phpMyAdmin o consola MySQL

-- 1. Hacer fecha nullable (las tareas pendientes no tienen fecha aún)
ALTER TABLE `tareas` MODIFY `fecha` date DEFAULT NULL;

-- 2. Añadir campo estado
ALTER TABLE `tareas`
  ADD COLUMN `estado` enum('realizada','pendiente') NOT NULL DEFAULT 'realizada' AFTER `fecha`;

-- Las tareas existentes (todas tienen fecha) quedan como 'realizada' automáticamente.
