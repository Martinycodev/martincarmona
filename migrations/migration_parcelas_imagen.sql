-- Añadir imagen a parcelas
ALTER TABLE `parcelas` ADD COLUMN `imagen` varchar(255) DEFAULT NULL AFTER `num_parcela`;
