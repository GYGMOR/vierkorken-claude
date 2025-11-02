-- SQL Befehle um Farbspalten zu den Tabellen hinzuzufügen
-- Führe diese Befehle einzeln aus in PHPMyAdmin

-- Zuerst prüfen welche Spalten bereits existieren:
-- SHOW COLUMNS FROM events LIKE 'featured%';
-- SHOW COLUMNS FROM klara_products_extended LIKE 'featured%';
-- SHOW COLUMNS FROM custom_news LIKE 'featured%';

-- Events: Farben für Neuheiten hinzufügen
-- Nur ausführen wenn die Spalten NICHT existieren!
ALTER TABLE `events`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#2c5282',
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff';

-- Klara Products Extended: Farben für Neuheiten
-- Nur ausführen wenn die Spalten NICHT existieren!
ALTER TABLE `klara_products_extended`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#722c2c',
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff';

-- Custom News: Farben für Neuheiten
-- Nur ausführen wenn die Spalten NICHT existieren!
ALTER TABLE `custom_news`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#c27c0e',
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff';
