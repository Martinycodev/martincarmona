-- Añadir categoría a trabajos para colores en el calendario
-- Categorías propuestas para olivar:
-- campo: Laboreo, desbrozar, arar (verde)
-- tratamiento: Herbicida, sulfato, abono (azul)
-- recoleccion: Recoger aceituna (naranja)
-- riego: Abrir/cerrar riego (cian)
-- poda: Podar, desvaretado (morado)
-- mantenimiento: Vehículos, herramientas (amarillo)
-- otro: Tareas generales (gris)
ALTER TABLE `trabajos` ADD COLUMN `categoria` enum('campo','tratamiento','recoleccion','riego','poda','mantenimiento','otro') NOT NULL DEFAULT 'otro' AFTER `descripcion`;
