-- Añadir columna id_user a movimientos para que cada usuario vea solo sus registros
ALTER TABLE movimientos
    ADD COLUMN id_user INT(11) NULL AFTER id;

-- Asignar usuario 1 (empresa principal) a los registros existentes
UPDATE movimientos SET id_user = 1 WHERE id_user IS NULL;

-- Hacer NOT NULL después de rellenar
ALTER TABLE movimientos
    MODIFY COLUMN id_user INT(11) NOT NULL;

-- Índice para filtrar rápidamente por usuario
ALTER TABLE movimientos
    ADD INDEX idx_movimientos_user (id_user);
