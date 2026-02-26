-- Migración 001: Añadir campo `cuenta` a tabla `movimientos`
-- Fecha: 2026-02-26
-- Descripción: Distinguir si el movimiento afecta a cuenta bancaria o efectivo

ALTER TABLE `movimientos`
  ADD COLUMN `cuenta` ENUM('banco','efectivo') NOT NULL DEFAULT 'banco' AFTER `estado`;
