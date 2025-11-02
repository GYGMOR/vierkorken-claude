-- SCHRITT 1: Zeige alle Featured Produkte (um zu sehen welche doppelt sind)
SELECT
    klara_article_id,
    is_featured,
    featured_bg_color,
    featured_text_color,
    vintage,
    producer,
    created_at,
    updated_at
FROM klara_products_extended
WHERE is_featured = 1
ORDER BY klara_article_id;

-- SCHRITT 2: Entferne ALLE Neuheiten-Markierungen (zum Aufräumen)
-- VORSICHT: Dies entfernt ALLE is_featured Markierungen!
-- Nur ausführen wenn du wirklich alle zurücksetzen willst!
-- UPDATE klara_products_extended SET is_featured = 0;

-- SCHRITT 3: Markiere nur DIESE Produkte als Neuheiten (Beispiel)
-- Ersetze die IDs mit den Produkten die du wirklich als Neuheit willst
-- UPDATE klara_products_extended SET is_featured = 1 WHERE klara_article_id IN ('6', '2', '39');
