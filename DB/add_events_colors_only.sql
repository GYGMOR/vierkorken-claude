-- Farbspalten NUR für Events Tabelle hinzufügen
-- Führe diesen Befehl in PHPMyAdmin aus

ALTER TABLE `events`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#2c5282',
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff';
