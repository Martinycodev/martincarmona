-- Migración: Tabla para rate limiting en login
-- Fase 13 - Seguridad avanzada
-- Max 5 intentos por IP cada 15 minutos

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,       -- Soporta IPv6
    email VARCHAR(255) DEFAULT NULL,        -- Email intentado (para log)
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
