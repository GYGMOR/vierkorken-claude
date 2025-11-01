-- Update wine_ratings table to support both old MySQL wines (INT) and Klara wines (VARCHAR)
-- Änderung: wine_id von INT zu VARCHAR(50) für Klara-Kompatibilität

-- Schritt 1: Temporäre Spalte erstellen
ALTER TABLE `wine_ratings`
ADD COLUMN `wine_id_new` VARCHAR(50) NULL AFTER `wine_id`;

-- Schritt 2: Alte IDs in neue Spalte kopieren (als String)
UPDATE `wine_ratings`
SET `wine_id_new` = CAST(`wine_id` AS CHAR);

-- Schritt 3: Alte Spalte löschen
ALTER TABLE `wine_ratings`
DROP COLUMN `wine_id`;

-- Schritt 4: Neue Spalte umbenennen
ALTER TABLE `wine_ratings`
CHANGE COLUMN `wine_id_new` `wine_id` VARCHAR(50) NOT NULL;

-- Schritt 5: Index auf wine_id erstellen
ALTER TABLE `wine_ratings`
ADD INDEX `idx_wine_id` (`wine_id`);

-- Schritt 6: Index auf user_id erstellen (falls noch nicht vorhanden)
ALTER TABLE `wine_ratings`
ADD INDEX `idx_user_id` (`user_id`);
