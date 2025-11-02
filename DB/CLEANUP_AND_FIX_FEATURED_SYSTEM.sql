-- ================================================================
-- CLEANUP UND FIX: Featured System komplett bereinigen
-- ================================================================
-- Dieses Script:
-- 1. Setzt ALLE Featured-Status auf 0 (bereinigt Duplikate)
-- 2. Entfernt leere/ungültige Einträge aus klara_products_extended
-- 3. Zeigt Statistiken an
--
-- WICHTIG: Führe dieses Script NUR aus, wenn du alle Neuheiten
--          zurücksetzen möchtest!
-- ================================================================

-- Schritt 1: Alle Klara-Produkte Neuheiten entfernen
UPDATE klara_products_extended SET is_featured = 0;
SELECT '✓ Alle Klara-Produkte Neuheiten entfernt' as Status;

-- Schritt 2: Alle Events Neuheiten entfernen
UPDATE events SET is_featured = 0;
SELECT '✓ Alle Events Neuheiten entfernt' as Status;

-- Schritt 3: Alle Custom News Neuheiten entfernen
UPDATE custom_news SET is_featured = 0;
SELECT '✓ Alle Custom News Neuheiten entfernt' as Status;

-- ================================================================
-- OPTIONAL: Leere Einträge bereinigen
-- ================================================================

-- Zeige Einträge mit leeren Werten
SELECT
    'Einträge mit vintage=0' as Problem,
    COUNT(*) as Anzahl
FROM klara_products_extended
WHERE vintage = 0;

SELECT
    'Einträge mit leerem producer' as Problem,
    COUNT(*) as Anzahl
FROM klara_products_extended
WHERE producer = '' OR producer IS NULL;

-- ================================================================
-- Wenn du möchtest, kannst du leere Vintage-Werte auf NULL setzen:
-- (Kommentiere die nächste Zeile aus, um auszuführen)
-- ================================================================
-- UPDATE klara_products_extended SET vintage = NULL WHERE vintage = 0;

-- ================================================================
-- STATISTIK NACH CLEANUP
-- ================================================================

SELECT '=== STATISTIK NACH CLEANUP ===' as Info;

SELECT
    'Klara-Produkte featured' as Tabelle,
    COUNT(*) as Anzahl
FROM klara_products_extended
WHERE is_featured = 1;

SELECT
    'Events featured' as Tabelle,
    COUNT(*) as Anzahl
FROM events
WHERE is_featured = 1;

SELECT
    'Custom News featured' as Tabelle,
    COUNT(*) as Anzahl
FROM custom_news
WHERE is_featured = 1;

SELECT '✓ CLEANUP ABGESCHLOSSEN - Alle Featured-Status zurückgesetzt' as Status;
SELECT 'Du kannst nun im Admin-Panel neue Neuheiten markieren' as Hinweis;
