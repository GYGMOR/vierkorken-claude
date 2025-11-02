-- ========================================
-- KOMPLETTES AUFRÄUMEN - ALLE NEUHEITEN ENTFERNEN
-- ========================================

-- Schritt 1: Entferne ALLE Klara-Produkte Neuheiten
UPDATE klara_products_extended SET is_featured = 0;

-- Schritt 2: Entferne ALLE Events Neuheiten
UPDATE events SET is_featured = 0;

-- Schritt 3: Entferne ALLE Custom News Neuheiten
UPDATE custom_news SET is_featured = 0;

-- ========================================
-- ÜBERPRÜFUNG: Sollte 0 Ergebnisse zeigen
-- ========================================

SELECT 'Klara Produkte' as typ, COUNT(*) as anzahl FROM klara_products_extended WHERE is_featured = 1
UNION ALL
SELECT 'Events', COUNT(*) FROM events WHERE is_featured = 1
UNION ALL
SELECT 'Custom News', COUNT(*) FROM custom_news WHERE is_featured = 1;
