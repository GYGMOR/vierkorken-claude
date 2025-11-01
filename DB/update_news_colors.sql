-- SQL Update: Farben-Spalten für News Items hinzufügen
-- Datum: 2025-11-01

ALTER TABLE `news_items`
ADD COLUMN `bg_color` VARCHAR(7) DEFAULT '#722c2c' COMMENT 'Hintergrundfarbe (Hex)' AFTER `display_order`,
ADD COLUMN `text_color` VARCHAR(7) DEFAULT '#ffffff' COMMENT 'Textfarbe (Hex)' AFTER `bg_color`;

-- Bestehende Einträge mit Default-Farben aktualisieren
UPDATE `news_items` SET
    `bg_color` = '#722c2c',
    `text_color` = '#ffffff'
WHERE `bg_color` IS NULL OR `text_color` IS NULL;
