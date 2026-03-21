-- ============================================================
-- Migración: Añadir categoría a trabajos y asignar valores
-- Fecha: 2026-03-21
-- Descripción: Añade columna 'categoria' a la tabla trabajos
--              y clasifica cada tipo de trabajo en su categoría
--              correspondiente según su función real en campo.
-- Categorías válidas: campo, tratamiento, recoleccion, riego,
--                     poda, mantenimiento, otro
-- ============================================================

-- 1. Añadir columna si no existe (MySQL no tiene IF NOT EXISTS para columnas,
--    usamos procedimiento temporal para hacerlo seguro)
SET @dbname = DATABASE();
SET @tablename = 'trabajos';
SET @columnname = 'categoria';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, " VARCHAR(50) DEFAULT 'otro' AFTER precio_hora")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Asignar categorías según la función de cada trabajo
-- ──────────────────────────────────────────────────────

-- 🔷 RIEGO — Todo lo relacionado con el manejo del agua
UPDATE trabajos SET categoria = 'riego' WHERE nombre IN (
    'Abrir Riego',
    'Cerrar Riego',
    'Echar a andar riego',
    'Estirar gomas',
    'Quitar vareta',
    'Regar plantones'
);

-- 🔵 TRATAMIENTO — Aplicación de productos (herbicidas, abonos, sulfato, fitosanitarios)
UPDATE trabajos SET categoria = 'tratamiento' WHERE nombre IN (
    'Echar sulfato con atomizadora',
    'Echar herbicida con tractor',
    'Echar herbicida con mochila',
    'Echar hoja con tractor',
    'Echar abono con riego',
    'Echar abono con abonadora',
    'Echar abono jarrillos',
    'Echar estiercol',
    'Preparar mochilas',
    'Preparar cuba'
);

-- 🟢 CAMPO — Laboreo del suelo, plantación y mantenimiento del terreno
UPDATE trabajos SET categoria = 'campo' WHERE nombre IN (
    'Desbrozar con tractor',
    'Pasar grada pinches',
    'Pasar Rulo tractor',
    'Pasar Rastra Tractor',
    'Pasar Grada discos tractor',
    'Quitar hierba con desbrozadora',
    'Abrir zanja retro',
    'Hacer suelos con mano hierro',
    'Arreglar Plantones',
    'Plantar plantones'
);

-- 🟠 RECOLECCIÓN — Recogida de aceituna y limpieza de suelo para cosecha
UPDATE trabajos SET categoria = 'recoleccion' WHERE nombre IN (
    'Recoger aceituna',
    'Recoger o acordonar desnate',
    'Cargar palos',
    'Llevar remolque palos',
    'Recoger raigones',
    'Soplar suelo sopladora',
    'Soplar suelo tractor'
);

-- 🟣 PODA — Corte, triturado y gestión de ramas
UPDATE trabajos SET categoria = 'poda' WHERE nombre IN (
    'Escamujar',
    'Cortar desnate',
    'Picar desnate',
    'Picar Ramón',
    'Acordonar Ramón',
    'Corta plantones'
);

-- 🟡 MANTENIMIENTO — Reparaciones, limpieza e inspecciones de maquinaria
UPDATE trabajos SET categoria = 'mantenimiento' WHERE nombre IN (
    'Arreglo tractor',
    'Arreglar Máquinas',
    'Mantenimiento vehiculos',
    'Limpiar cuadra',
    'Pasar itv maquinaria'
);

-- ⚪ OTRO — Gestión, formación y compras (ya es el valor por defecto)
UPDATE trabajos SET categoria = 'otro' WHERE nombre IN (
    'Papeleo y burocracia',
    'Formación',
    'Compras'
);
