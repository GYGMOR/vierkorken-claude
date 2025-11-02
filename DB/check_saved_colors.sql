-- Prüfe welche Farben für featured Produkte gespeichert sind
SELECT
    klara_article_id,
    is_featured,
    featured_bg_color,
    featured_text_color,
    updated_at
FROM klara_products_extended
WHERE is_featured = 1
ORDER BY updated_at DESC;
