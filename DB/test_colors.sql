-- Test: Zeige alle Klara-Produkte die als Neuheit markiert sind MIT Farben
SELECT
    klara_article_id,
    is_featured,
    featured_bg_color,
    featured_text_color,
    producer,
    created_at,
    updated_at
FROM klara_products_extended
WHERE is_featured = 1
ORDER BY updated_at DESC;

-- Test: Zeige alle Events die als Neuheit markiert sind MIT Farben
SELECT
    id,
    name,
    is_featured,
    featured_bg_color,
    featured_text_color,
    created_at,
    updated_at
FROM events
WHERE is_featured = 1
ORDER BY event_date DESC;

-- Test: Aktualisiere manuell die Farben für ein Testprodukt
-- Ersetze 'ARTIKEL_ID_HIER' mit einer echten Klara-Artikel-ID
-- UPDATE klara_products_extended
-- SET featured_bg_color = '#ff0000', featured_text_color = '#ffff00'
-- WHERE klara_article_id = 'ARTIKEL_ID_HIER';
