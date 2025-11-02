-- Prüfe alle drei Tabellen auf featured Spalten

-- Events (sollte funktionieren)
SHOW COLUMNS FROM events LIKE 'featured%';

-- Klara Products Extended
SHOW COLUMNS FROM klara_products_extended LIKE 'featured%';

-- Custom News
SHOW COLUMNS FROM custom_news LIKE 'featured%';
