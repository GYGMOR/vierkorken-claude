-- SQL: Erweiterte Produkt-Informationen für Klara-Artikel
-- Datum: 2025-11-01

CREATE TABLE IF NOT EXISTS `klara_products_extended` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `klara_article_id` varchar(50) NOT NULL COMMENT 'ID des Klara-Artikels',
  `image_url` varchar(500) DEFAULT NULL COMMENT 'Bild-URL',
  `producer` varchar(255) DEFAULT NULL COMMENT 'Produzent',
  `vintage` int(4) DEFAULT NULL COMMENT 'Jahrgang',
  `region` varchar(255) DEFAULT NULL COMMENT 'Region',
  `alcohol_content` decimal(4,2) DEFAULT NULL COMMENT 'Alkoholgehalt %',
  `description` text DEFAULT NULL COMMENT 'Erweiterte Beschreibung',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT 'Als Neuheit markiert',
  `custom_price` decimal(10,2) DEFAULT NULL COMMENT 'Überschreibt Klara-Preis falls gesetzt',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `klara_article_id` (`klara_article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index für schnelle Suche nach Featured-Produkten
CREATE INDEX idx_is_featured ON klara_products_extended(is_featured);
