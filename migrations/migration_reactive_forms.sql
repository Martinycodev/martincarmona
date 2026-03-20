-- Añadir tarea_id a riegos para vincular registros creados automáticamente desde el sidebar
ALTER TABLE `riegos` ADD COLUMN `tarea_id` int(11) DEFAULT NULL AFTER `parcela_id`;
ALTER TABLE `riegos` ADD KEY `idx_riegos_tarea` (`tarea_id`);

-- Añadir tarea_id a campana_registros para vincular registros creados automáticamente
ALTER TABLE `campana_registros` ADD COLUMN `tarea_id` int(11) DEFAULT NULL AFTER `parcela_id`;
ALTER TABLE `campana_registros` ADD KEY `idx_campana_reg_tarea` (`tarea_id`);
