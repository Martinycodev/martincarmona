-- Script para actualizar la tabla trabajadores con nuevos campos
-- Ejecutar este script en la base de datos para añadir los campos necesarios

-- Añadir campo foto si no existe
ALTER TABLE trabajadores 
ADD COLUMN foto VARCHAR(255) DEFAULT NULL COMMENT 'Ruta de la foto del trabajador';

-- Añadir campos adicionales si no existen
ALTER TABLE trabajadores 
ADD COLUMN apellidos VARCHAR(100) DEFAULT NULL COMMENT 'Apellidos del trabajador';

ALTER TABLE trabajadores 
ADD COLUMN telefono VARCHAR(20) DEFAULT NULL COMMENT 'Teléfono del trabajador';

ALTER TABLE trabajadores 
ADD COLUMN email VARCHAR(100) DEFAULT NULL COMMENT 'Email del trabajador';

ALTER TABLE trabajadores 
ADD COLUMN direccion TEXT DEFAULT NULL COMMENT 'Dirección del trabajador';

ALTER TABLE trabajadores 
ADD COLUMN especialidad VARCHAR(100) DEFAULT NULL COMMENT 'Especialidad del trabajador';

-- Campo salario_hora eliminado - los trabajadores se pagan por tareas, no por horas

ALTER TABLE trabajadores 
ADD COLUMN fecha_contratacion DATE DEFAULT NULL COMMENT 'Fecha de contratación';

ALTER TABLE trabajadores 
ADD COLUMN estado ENUM('activo', 'inactivo') DEFAULT 'activo' COMMENT 'Estado del trabajador';

-- Crear tabla de relación trabajos-trabajadores si no existe
CREATE TABLE trabajos_trabajadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trabajo_id INT NOT NULL,
    trabajador_id INT NOT NULL,
    precio_tarea DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio acordado por la tarea',
    pagado DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Cantidad ya pagada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trabajo_id) REFERENCES trabajos(id) ON DELETE CASCADE,
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_trabajo_trabajador (trabajo_id, trabajador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_trabajadores_estado ON trabajadores(estado);
CREATE INDEX idx_trabajadores_id_user ON trabajadores(id_user);
CREATE INDEX idx_trabajos_trabajadores_trabajador ON trabajos_trabajadores(trabajador_id);
CREATE INDEX idx_trabajos_trabajadores_trabajo ON trabajos_trabajadores(trabajo_id);
