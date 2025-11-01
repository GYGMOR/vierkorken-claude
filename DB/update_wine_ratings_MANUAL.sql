-- MANUELLE VERSION - Führe Schritt für Schritt aus
-- Nach jedem Schritt auf "Ausführen" klicken und Ergebnis prüfen

-- ===== SCHRITT 1: Tabellenstruktur anzeigen =====
-- Führe dies zuerst aus um die aktuelle Struktur zu sehen:
SHOW CREATE TABLE `wine_ratings`;

-- ===== SCHRITT 2: Alle Constraints anzeigen =====
SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'wine_ratings';

-- ===== SCHRITT 3: Foreign Keys entfernen =====
-- Ersetze 'CONSTRAINT_NAME' mit dem echten Namen aus Schritt 2
-- Falls kein Foreign Key existiert, überspringe diesen Schritt
-- ALTER TABLE `wine_ratings` DROP FOREIGN KEY `CONSTRAINT_NAME`;

-- ===== SCHRITT 4: Primary Key entfernen =====
-- NUR ausführen wenn wine_id TEIL des Primary Keys ist
-- ALTER TABLE `wine_ratings` DROP PRIMARY KEY;

-- ===== SCHRITT 5: Temporäre Spalte erstellen =====
ALTER TABLE `wine_ratings`
ADD COLUMN `wine_id_new` VARCHAR(50) NULL AFTER `wine_id`;

-- ===== SCHRITT 6: Daten kopieren =====
UPDATE `wine_ratings`
SET `wine_id_new` = CAST(`wine_id` AS CHAR);

-- ===== SCHRITT 7: Prüfen ob alle Daten kopiert wurden =====
SELECT COUNT(*) as total,
       COUNT(wine_id_new) as copied
FROM `wine_ratings`;

-- ===== SCHRITT 8: Alte Spalte löschen =====
ALTER TABLE `wine_ratings`
DROP COLUMN `wine_id`;

-- ===== SCHRITT 9: Neue Spalte umbenennen =====
ALTER TABLE `wine_ratings`
CHANGE COLUMN `wine_id_new` `wine_id` VARCHAR(50) NOT NULL;

-- ===== SCHRITT 10: Primary Key neu erstellen =====
ALTER TABLE `wine_ratings`
ADD PRIMARY KEY (`id`);

-- ===== SCHRITT 11: Indizes erstellen =====
ALTER TABLE `wine_ratings`
ADD INDEX `idx_wine_id` (`wine_id`);

ALTER TABLE `wine_ratings`
ADD INDEX `idx_user_id` (`user_id`);

-- ===== SCHRITT 12: Unique Constraint =====
ALTER TABLE `wine_ratings`
ADD UNIQUE INDEX `unique_wine_user` (`wine_id`, `user_id`);

-- ===== FERTIG! =====
-- Struktur prüfen:
SHOW CREATE TABLE `wine_ratings`;
