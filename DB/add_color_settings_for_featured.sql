-- Farbeinstellungen für Featured Items hinzufügen

-- Klara Products Extended: Farben für Neuheiten
ALTER TABLE `klara_products_extended`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#722c2c' AFTER `is_featured`,
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff' AFTER `featured_bg_color`;

-- Events: Farben für Neuheiten
ALTER TABLE `events`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#2c5282' AFTER `is_featured`,
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff' AFTER `featured_bg_color`;

-- Custom News: Farben bereits vorhanden, aber sicherstellen
ALTER TABLE `custom_news`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#c27c0e' AFTER `is_featured`,
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff' AFTER `featured_bg_color`;
