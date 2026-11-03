-- Restricciones por plan. Ejecutar una sola vez en la base de datos, antes de desplegar el código.

ALTER TABLE `plan`
  ADD COLUMN `clasespermitidas` varchar(100) DEFAULT NULL COMMENT 'Ids de clase separados por coma; NULL = todas' AFTER `clase_id`,
  ADD COLUMN `claselimite_id` int DEFAULT NULL COMMENT 'Clase con costo y máximo propios' AFTER `clasespermitidas`,
  ADD COLUMN `creditosclaselimite` int DEFAULT NULL COMMENT 'Créditos que consume esa clase' AFTER `claselimite_id`,
  ADD COLUMN `limiteclases` int DEFAULT NULL COMMENT 'Máximo de esa clase por periodo; NULL = sin máximo' AFTER `creditosclaselimite`,
  ADD COLUMN `periodolimite` varchar(10) DEFAULT NULL COMMENT 'MES = mes calendario, 30DIAS = cada 30 días desde el inicio del plan' AFTER `limiteclases`;

-- Créditos que consumió cada reserva: es lo que se devuelve al cancelar. Las reservas existentes consumieron 1.
ALTER TABLE `reserva`
  ADD COLUMN `creditos` int NOT NULL DEFAULT 1 AFTER `ubicacion`;

-- Clases: 1 Baile, 3 Pilates, 4 Cardio HIIT, 5 Localizado
UPDATE `plan` SET `clasespermitidas` = '1'     WHERE `id` = 2;           -- Experiencia
UPDATE `plan` SET `clasespermitidas` = '3'     WHERE `id` IN (10, 13);   -- Experiencia Pilates, Full Pilates
UPDATE `plan` SET `clasespermitidas` = '1,4,5' WHERE `id` IN (1, 16);    -- Essence, FULL Dance

UPDATE `plan` SET `claselimite_id` = 3, `creditosclaselimite` = 2, `limiteclases` = 5, `periodolimite` = 'MES'    WHERE `id` = 15;  -- Full
UPDATE `plan` SET `claselimite_id` = 3, `creditosclaselimite` = 2, `limiteclases` = 5, `periodolimite` = '30DIAS' WHERE `id` = 8;   -- Full 4 Meses
