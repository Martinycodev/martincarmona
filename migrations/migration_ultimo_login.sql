-- ============================================================
-- Migración: Registrar último login de usuarios
-- Fecha: 2026-03-21
-- Descripción: Añade columnas ultimo_login y ultimo_login_ip
--              para saber cuándo y desde dónde accedió cada usuario.
-- ============================================================

-- Añadir ultimo_login (DATETIME)
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'ultimo_login'
    ) > 0,
    'SELECT 1',
    'ALTER TABLE usuarios ADD COLUMN ultimo_login DATETIME DEFAULT NULL'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Añadir ultimo_login_ip (para saber desde qué IP accedió)
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'ultimo_login_ip'
    ) > 0,
    'SELECT 1',
    'ALTER TABLE usuarios ADD COLUMN ultimo_login_ip VARCHAR(45) DEFAULT NULL'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
