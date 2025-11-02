-- Tabelle für Custom News/Aktionen erstellen
CREATE TABLE IF NOT EXISTS `custom_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_featured` (`is_featured`, `active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events Tabelle um is_featured Spalte erweitern (falls nicht vorhanden)
ALTER TABLE `events`
ADD COLUMN `is_featured` tinyint(1) DEFAULT 0 AFTER `active`;

-- Index für bessere Performance
ALTER TABLE `events`
ADD INDEX `idx_featured` (`is_featured`, `active`);
