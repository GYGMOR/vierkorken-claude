-- Add short_description and extended_description to klara_products_extended table
-- This allows wine sellers to provide both short descriptions for casual readers
-- and detailed descriptions for wine connoisseurs

ALTER TABLE `klara_products_extended`
ADD COLUMN `short_description` TEXT DEFAULT NULL AFTER `description`,
ADD COLUMN `extended_description` TEXT DEFAULT NULL AFTER `short_description`;

-- Migrate existing description to short_description
UPDATE `klara_products_extended`
SET `short_description` = `description`
WHERE `description` IS NOT NULL AND `description` != '';
