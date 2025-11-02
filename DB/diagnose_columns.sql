-- Diagnose: Zeige alle Spalten der events Tabelle
DESCRIBE events;

-- Alternativ: Zeige nur die featured Spalten
SHOW COLUMNS FROM events LIKE 'featured%';

-- Wenn die Spalten existieren aber falsch sind, erst löschen:
-- ALTER TABLE events DROP COLUMN featured_bg_color;
-- ALTER TABLE events DROP COLUMN featured_text_color;

-- Dann neu hinzufügen:
-- ALTER TABLE events ADD COLUMN featured_bg_color VARCHAR(20) DEFAULT '#2c5282';
-- ALTER TABLE events ADD COLUMN featured_text_color VARCHAR(20) DEFAULT '#ffffff';
