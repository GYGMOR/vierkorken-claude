-- Farbspalten zu custom_news Tabelle hinzufügen
ALTER TABLE `custom_news`
ADD COLUMN `featured_bg_color` varchar(20) DEFAULT '#c27c0e',
ADD COLUMN `featured_text_color` varchar(20) DEFAULT '#ffffff';
