-- Kanton-Feld zu klara_products_extended hinzufügen
-- Dieses Feld speichert das Kürzel des Kantons (z.B. 'ZH', 'BE', 'GR', etc.)

ALTER TABLE `klara_products_extended`
ADD COLUMN `kanton` VARCHAR(2) DEFAULT NULL COMMENT 'Schweizer Kanton (2-Buchstaben Kürzel)'
AFTER `region`;

-- Prüfen ob erfolgreich
SELECT 'Kanton-Feld erfolgreich hinzugefügt!' as Status;

-- Zeige Struktur
DESCRIBE klara_products_extended;
