-- Migration: News/Events System für Vier Korken
-- Datum: 2025-10-21
-- Beschreibung: Erweitert das System um News-Items und Events

-- 1. Tabelle für News/Neuheiten Items
CREATE TABLE IF NOT EXISTS `news_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT,
  `type` ENUM('wine', 'event', 'general') NOT NULL DEFAULT 'general',
  `image_url` VARCHAR(500),
  `link_url` VARCHAR(500),
  `reference_id` INT DEFAULT NULL COMMENT 'ID des Weins oder Events',
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_type (`type`),
  INDEX idx_active (`is_active`),
  INDEX idx_order (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Events Kategorie hinzufügen (wenn nicht vorhanden)
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `description`)
VALUES (9, 'Events', 'events', 'Weinverkostungen, Veranstaltungen und besondere Anlässe');

-- 3. Tabelle für Events
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `event_date` DATETIME NOT NULL,
  `location` VARCHAR(255),
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image_url` VARCHAR(500),
  `max_participants` INT DEFAULT 0 COMMENT '0 = unbegrenzt',
  `available_tickets` INT DEFAULT 0,
  `category_id` INT DEFAULT 9 COMMENT 'Standardmäßig Events-Kategorie',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX idx_active (`is_active`),
  INDEX idx_event_date (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabelle für Event-Buchungen/Tickets
CREATE TABLE IF NOT EXISTS `event_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `user_id` INT,
  `order_id` INT,
  `ticket_quantity` INT NOT NULL DEFAULT 1,
  `total_price` DECIMAL(10,2) NOT NULL,
  `booking_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
  `customer_name` VARCHAR(255),
  `customer_email` VARCHAR(255),
  `customer_phone` VARCHAR(50),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
  INDEX idx_event (`event_id`),
  INDEX idx_user (`user_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Theme Settings Tabelle verbessern (falls noch nicht vorhanden)
CREATE TABLE IF NOT EXISTS `theme_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Standard Theme-Farben einfügen (falls nicht vorhanden)
INSERT IGNORE INTO `theme_settings` (`setting_key`, `setting_value`) VALUES
('primary_color', '#722c2c'),
('primary_dark', '#561111'),
('accent_gold', '#d4a574'),
('bg_light', '#f8f4f0'),
('text_dark', '#333333'),
('text_light', '#666666');

-- 7. Beispiel-Events einfügen
INSERT INTO `events` (`name`, `description`, `event_date`, `location`, `price`, `max_participants`, `available_tickets`, `image_url`) VALUES
('Weinverkostung: Schweizer Rotweine', 'Entdecken Sie die Vielfalt der Schweizer Rotweine bei unserer exklusiven Verkostung. Ein erfahrener Sommelier führt Sie durch 6 ausgewählte Weine.', '2025-11-15 18:00:00', 'Vier Korken Weinlounge, Zürich', 45.00, 20, 20, 'assets/images/events/weinverkostung-rotwein.jpg'),
('Käse & Wein Pairing Workshop', 'Lernen Sie die perfekte Kombination von Schweizer Käse und Wein kennen. Ein unvergessliches kulinarisches Erlebnis.', '2025-11-22 19:00:00', 'Vier Korken Weinlounge, Zürich', 65.00, 15, 15, 'assets/images/events/kaese-wein-pairing.jpg'),
('Weihnachts-Weindegustation', 'Besondere Weine für die Festtage. Finden Sie den perfekten Begleiter für Ihr Weihnachtsmenü.', '2025-12-10 17:00:00', 'Vier Korken Weinlounge, Zürich', 55.00, 25, 25, 'assets/images/events/weihnachts-degustation.jpg');

-- 8. Beispiel News-Items einfügen
INSERT INTO `news_items` (`title`, `content`, `type`, `image_url`, `link_url`, `display_order`) VALUES
('Neue Rotweine eingetroffen!', 'Entdecken Sie unsere neuesten Rotweine aus dem Wallis. Limitierte Auflage!', 'general', 'assets/images/news/neue-rotweine.jpg', '?page=shop&category=1', 1),
('Weinverkostung am 15. November', 'Jetzt Tickets sichern für unsere exklusive Rotwein-Verkostung!', 'event', 'assets/images/events/weinverkostung-rotwein.jpg', '?page=events', 2),
('20% Rabatt auf Weißweine', 'Nur diese Woche: Alle Weißweine mit 20% Rabatt!', 'general', 'assets/images/news/weisswein-aktion.jpg', '?page=shop&category=2', 3);

COMMIT;
