-- QUICK FIX: Update existing orders table or create new one
-- Run this if you get "Unknown column 'guest_email'" error

-- Drop existing orders table if it exists (CAREFUL: This deletes all test orders!)
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `order_sequence`;
DROP TABLE IF EXISTS `coupon_usage`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `coupons`;

-- Now run the complete schema from 003_orders_and_coupons.sql:

-- Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) UNIQUE NOT NULL,
  `user_id` INT DEFAULT NULL,
  `guest_email` VARCHAR(255) DEFAULT NULL,

  -- Delivery Info
  `delivery_method` ENUM('delivery', 'pickup') NOT NULL DEFAULT 'delivery',
  `delivery_first_name` VARCHAR(100) NOT NULL,
  `delivery_last_name` VARCHAR(100) NOT NULL,
  `delivery_street` VARCHAR(255) DEFAULT NULL,
  `delivery_postal_code` VARCHAR(20) DEFAULT NULL,
  `delivery_city` VARCHAR(100) DEFAULT NULL,
  `delivery_phone` VARCHAR(50) NOT NULL,
  `delivery_email` VARCHAR(255) NOT NULL,

  -- Payment Info
  `payment_method` ENUM('card', 'twint', 'cash') NOT NULL,
  `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `payment_transaction_id` VARCHAR(255) DEFAULT NULL,

  -- Pricing
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `shipping_cost` DECIMAL(10, 2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `total_amount` DECIMAL(10, 2) NOT NULL,

  -- Coupon
  `coupon_code` VARCHAR(50) DEFAULT NULL,

  -- Status
  `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,

  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_order_number` (`order_number`),
  INDEX `idx_user_orders` (`user_id`, `created_at`),
  INDEX `idx_order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `item_type` ENUM('wine', 'event') NOT NULL DEFAULT 'wine',
  `wine_id` INT DEFAULT NULL,
  `event_id` INT DEFAULT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10, 2) NOT NULL,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `customer_data` TEXT DEFAULT NULL,

  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`wine_id`) REFERENCES `wines`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE SET NULL,
  INDEX `idx_order_items` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupons Table
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) UNIQUE NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` DECIMAL(10, 2) NOT NULL,
  `min_order_amount` DECIMAL(10, 2) DEFAULT 0.00,
  `max_discount_amount` DECIMAL(10, 2) DEFAULT NULL,
  `usage_limit` INT DEFAULT NULL,
  `used_count` INT DEFAULT 0,
  `valid_from` DATETIME DEFAULT NULL,
  `valid_until` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_code_active` (`code`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon Usage Tracking
CREATE TABLE IF NOT EXISTS `coupon_usage` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `coupon_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `order_id` INT NOT NULL,
  `discount_amount` DECIMAL(10, 2) NOT NULL,
  `used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_coupon` (`user_id`, `coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Number Sequence
CREATE TABLE IF NOT EXISTS `order_sequence` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `date` DATE NOT NULL UNIQUE,
  `last_sequence` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Coupons for Testing
INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, is_active) VALUES
('WILLKOMMEN10', '10% Rabatt für Neukunden', 'percentage', 10.00, 50.00, 1),
('SOMMER25', '25 CHF Rabatt ab 100 CHF', 'fixed', 25.00, 100.00, 1),
('GRATIS15', '15% Rabatt ohne Mindestbetrag', 'percentage', 15.00, 0.00, 1);
