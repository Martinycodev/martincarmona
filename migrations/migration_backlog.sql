-- =============================================
-- Migración: Backlog de funcionalidades pendientes
-- Fecha: 2026-03-17
-- =============================================

-- 1. Trabajos: precio_hora no puede ser NULL, default 0
UPDATE trabajos SET precio_hora = 0 WHERE precio_hora IS NULL;
ALTER TABLE trabajos MODIFY COLUMN precio_hora FLOAT NOT NULL DEFAULT 0;

-- 2. Trabajadores: añadir campo 'activo' para sistema activo/inactivo mensual
-- 0 = inactivo, 1 = activo. Día 1 de cada mes todos pasan a inactivo.
-- Si realizan una tarea, pasan a activo.
ALTER TABLE trabajadores ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 0;

-- 3. Parcelas: añadir campos catastrales nºMunicipio/nºPolígono/nºParcela
ALTER TABLE parcelas ADD COLUMN IF NOT EXISTS num_municipio VARCHAR(10) DEFAULT NULL;
ALTER TABLE parcelas ADD COLUMN IF NOT EXISTS num_poligono VARCHAR(10) DEFAULT NULL;
ALTER TABLE parcelas ADD COLUMN IF NOT EXISTS num_parcela VARCHAR(10) DEFAULT NULL;

-- 4. Trabajadores: añadir campo 'fecha_baja' para gestionar despidos/bajas
ALTER TABLE trabajadores ADD COLUMN IF NOT EXISTS fecha_baja DATE DEFAULT NULL;

-- 5. Trabajos: añadir campo para documento adjunto con método de trabajo
ALTER TABLE trabajos ADD COLUMN IF NOT EXISTS documento VARCHAR(255) DEFAULT NULL;

-- 6. Proveedores: añadir columnas que faltan en la BD
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS razon_social VARCHAR(255) DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS cif VARCHAR(20) DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS direccion VARCHAR(255) DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS email VARCHAR(150) DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS contacto_principal VARCHAR(150) DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS sector VARCHAR(100) DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS productos_servicios TEXT DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS condiciones_pago VARCHAR(255) DEFAULT NULL;
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS estado VARCHAR(50) NOT NULL DEFAULT 'activo';
ALTER TABLE proveedores ADD COLUMN IF NOT EXISTS notas TEXT DEFAULT NULL;

-- 7. Temporadas de riego (similar a campañas)
CREATE TABLE IF NOT EXISTS temporadas_riego (
    id INT(11) NOT NULL AUTO_INCREMENT,
    anio INT(4) NOT NULL,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    fecha_inicio DATE DEFAULT NULL,
    fecha_fin DATE DEFAULT NULL,
    id_user BIGINT(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_anio_user (anio, id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabla para imágenes de tareas
CREATE TABLE IF NOT EXISTS tarea_imagenes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tarea_id INT(11) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT(11) DEFAULT 0,
    mime_type VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tarea_id (tarea_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Campaña registros: añadir campo calidad (Vuelo/Suelo)
ALTER TABLE campana_registros ADD COLUMN IF NOT EXISTS calidad VARCHAR(20) DEFAULT NULL;
