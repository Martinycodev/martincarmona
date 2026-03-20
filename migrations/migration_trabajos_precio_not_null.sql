-- Migración: Forzar precio_hora NOT NULL DEFAULT 0 en tabla trabajos
-- Los registros con precio_hora = NULL se actualizan a 0.00

-- Paso 1: Actualizar los registros existentes que tengan NULL
UPDATE trabajos SET precio_hora = 0.00 WHERE precio_hora IS NULL;

-- Paso 2: Cambiar la columna para que no permita NULL y tenga valor por defecto 0
ALTER TABLE trabajos MODIFY COLUMN precio_hora DECIMAL(10,2) NOT NULL DEFAULT 0.00;
