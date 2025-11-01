-- Update wine_ratings table to support both old MySQL wines (INT) and Klara wines (VARCHAR)
-- FIXED VERSION - Korrigiert für Primary/Foreign Keys
-- Änderung: wine_id von INT zu VARCHAR(50) für Klara-Kompatibilität

-- Schritt 1: Alle Foreign Keys auf wine_id entfernen (falls vorhanden)
ALTER TABLE `wine_ratings` DROP FOREIGN KEY IF EXISTS `wine_ratings_ibfk_1`;
ALTER TABLE `wine_ratings` DROP FOREIGN KEY IF EXISTS `fk_wine_ratings_wine_id`;

-- Schritt 2: Primary Key anpassen falls wine_id Teil davon ist
-- Zuerst prüfen wir ob es einen zusammengesetzten Primary Key gibt
-- Falls ja, entfernen wir ihn temporär
SET @has_pk = (SELECT COUNT(*)
               FROM information_schema.TABLE_CONSTRAINTS
               WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'wine_ratings'
               AND CONSTRAINT_TYPE = 'PRIMARY KEY');

-- Primary Key entfernen falls vorhanden
ALTER TABLE `wine_ratings` DROP PRIMARY KEY;

-- Schritt 3: Temporäre Spalte erstellen
ALTER TABLE `wine_ratings`
ADD COLUMN `wine_id_new` VARCHAR(50) NULL AFTER `wine_id`;

-- Schritt 4: Alte IDs in neue Spalte kopieren (als String)
UPDATE `wine_ratings`
SET `wine_id_new` = CAST(`wine_id` AS CHAR);

-- Schritt 5: Alte Spalte löschen
ALTER TABLE `wine_ratings`
DROP COLUMN `wine_id`;

-- Schritt 6: Neue Spalte umbenennen
ALTER TABLE `wine_ratings`
CHANGE COLUMN `wine_id_new` `wine_id` VARCHAR(50) NOT NULL;

-- Schritt 7: Primary Key neu erstellen (id + wine_id + user_id als zusammengesetzter Key)
ALTER TABLE `wine_ratings`
ADD PRIMARY KEY (`id`);

-- Schritt 8: Indizes erstellen
ALTER TABLE `wine_ratings`
ADD INDEX `idx_wine_id` (`wine_id`),
ADD INDEX `idx_user_id` (`user_id`);

-- Schritt 9: Unique constraint für wine_id + user_id (ein User kann einen Wein nur einmal bewerten)
ALTER TABLE `wine_ratings`
ADD UNIQUE INDEX `unique_wine_user` (`wine_id`, `user_id`);
