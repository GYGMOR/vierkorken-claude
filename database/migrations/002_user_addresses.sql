-- User Addresses Table for Checkout
-- Run this migration to enable address management in checkout

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `street` VARCHAR(255) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_default` (`user_id`, `is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing (adjust user_id as needed)
-- INSERT INTO user_addresses (user_id, first_name, last_name, street, postal_code, city, phone, is_default) VALUES
-- (1, 'Max', 'Mustermann', 'Bahnhofstrasse 123', '8001', 'Zürich', '+41 79 123 45 67', 1);
