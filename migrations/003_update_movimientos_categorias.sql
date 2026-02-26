-- Migración 003: Actualizar ENUM categorias en tabla movimientos
-- Fecha: 2026-02-26
-- Descripción: Añadir categorías específicas para gastos e ingresos del módulo Economía.
--   Gastos:   compras, reparaciones, inversiones, seguros, gestoria
--   Ingresos: labores_terceros, subvenciones, liquidacion_aceite
--   Se conservan los valores antiguos para no romper datos existentes.

ALTER TABLE `movimientos` MODIFY COLUMN `categoria` ENUM(
  -- Valores legacy (datos existentes)
  'personal','pago','impuestos','maquinaria','parcela','servicios','subvencion','otros',
  -- Categorías de gastos
  'compras','reparaciones','inversiones','seguros','gestoria',
  -- Categorías de ingresos
  'labores_terceros','subvenciones','liquidacion_aceite'
) NOT NULL;
