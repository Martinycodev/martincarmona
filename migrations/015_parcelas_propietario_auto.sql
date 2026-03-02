-- Migration 015: Auto-vincular propietarios desde parcelas y eliminar campo empresa
-- Ejecutar en XAMPP phpMyAdmin o consola MySQL

-- Paso 1: Crear registros en `propietarios` a partir de los nombres únicos
--         que aparecen en parcelas.propietario y aún no tienen propietario_id asignado.
--         Se usa el primer usuario con rol='empresa' como id_user.
INSERT INTO propietarios (nombre, id_user)
SELECT DISTINCT
    p.propietario,
    (SELECT u.id FROM usuarios u WHERE u.rol = 'empresa' ORDER BY u.id LIMIT 1) AS id_user
FROM parcelas p
WHERE p.propietario IS NOT NULL
  AND p.propietario != ''
  AND p.propietario_id IS NULL
  AND NOT EXISTS (
    SELECT 1 FROM propietarios pr
    WHERE pr.nombre = p.propietario
      AND pr.id_user = (SELECT u.id FROM usuarios u WHERE u.rol = 'empresa' ORDER BY u.id LIMIT 1)
  );

-- Paso 2: Actualizar propietario_id en parcelas usando el nombre como clave de unión.
UPDATE parcelas p
JOIN propietarios pr
  ON  pr.nombre  = p.propietario
  AND pr.id_user = (SELECT u.id FROM usuarios u WHERE u.rol = 'empresa' ORDER BY u.id LIMIT 1)
SET p.propietario_id = pr.id
WHERE p.propietario IS NOT NULL
  AND p.propietario != ''
  AND p.propietario_id IS NULL;

-- Paso 3: Eliminar la columna empresa (ya no necesaria, todo está en id_user a nivel app)
ALTER TABLE `parcelas` DROP COLUMN `empresa`;
