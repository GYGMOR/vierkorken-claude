# Klara API Integration - Dokumentation

## Übersicht

Die Webseite ist jetzt vollständig mit der Klara API integriert. Alle Produkte und Kategorien kommen direkt von Klara.

## Wichtige Dateien

### 1. API-Funktionen
**Datei:** `includes/functions.php` (Zeilen 459-603)

- `klara_api_call($endpoint)` - Direkter API-Call zu Klara
- `klara_get_categories()` - Holt alle Kategorien
- `klara_get_articles($categoryId, $search)` - Holt Artikel mit optionalen Filtern
- `klara_count_articles_in_category($categoryId)` - Zählt Artikel pro Kategorie

### 2. Shop-Seite
**Datei:** `pages/shop.php`

- Verwendet `klara_get_categories()` und `klara_get_articles()`
- Mobile Kategorie-Filter unter Suchbalken
- Responsive Design für alle Geräte

### 3. Test-Seite
**Datei:** `test-klara.php`

Öffne im Browser: `http://deine-domain.ch/test-klara.php`

Diese Seite zeigt:
- Anzahl gefundener Kategorien
- Anzahl gefundener Artikel
- cURL-Status
- Erste 3-5 Einträge als Beispiel

## API-Credentials

**In der Datei:** `includes/functions.php` (Zeilen 464-465)

```php
define('KLARA_API_BASEURL', 'https://api.klara.ch');
define('KLARA_API_KEY', '01c11c3e-c484-4ce7-bca0-3f52eb3772af');
```

## Funktionsweise

### Automatische Synchronisation

1. **Neue Produkte in Klara** → Erscheinen sofort im Shop
2. **Neue Kategorien in Klara** → Werden automatisch angezeigt
3. **Änderungen an Produkten** → Werden beim nächsten Seitenaufruf aktualisiert

### Filterung

- **Nach Kategorie:** `?page=shop&category=KATEGORIE_ID`
- **Suche:** `?page=shop&search=SUCHBEGRIFF`
- **Kombiniert:** Beide Filter zusammen

## Mobile Navigation

### Desktop (>1024px)
- Sidebar links mit allen Kategorien
- Sticky Position beim Scrollen

### Tablet/Mobile (≤1024px)
- Aufklappbarer "Kategorien"-Button unter Suchbalken
- 3-Striche Icon + Pfeil-Animation
- Smooth Slide-Down Animation

## Debugging

### Debug-Modus aktivieren
Füge `&debug=1` zur URL hinzu:
```
?page=shop&debug=1
```

Dies zeigt HTML-Kommentare mit:
- Anzahl gefundener Kategorien
- Anzahl gefundener Artikel

### Fehlersuche

**Problem: Keine Produkte sichtbar**

1. Öffne `test-klara.php` im Browser
2. Prüfe ob Kategorien und Artikel angezeigt werden
3. Prüfe cURL-Status
4. Schaue in PHP Error-Log

**Problem: Kategorien klappen nicht auf**

1. Öffne Browser-Konsole (F12)
2. Klicke auf "Kategorien"-Button
3. Schaue nach JavaScript-Fehlern

**Problem: Slow Performance**

Die Klara API wird bei jedem Seitenaufruf aufgerufen. Für bessere Performance:
- Implementiere Caching (z.B. 5 Minuten)
- Nutze PHP Session oder Redis

## Server-Anforderungen

### Mindestanforderungen
- PHP 7.4 oder höher
- cURL Extension aktiviert
- SSL Support für HTTPS-Verbindungen

### Prüfen ob cURL aktiviert ist:
```php
<?php
if (function_exists('curl_version')) {
    echo "cURL ist aktiviert";
    print_r(curl_version());
} else {
    echo "cURL ist NICHT aktiviert";
}
?>
```

## Responsive Breakpoints

- **Desktop:** >1024px - Sidebar sichtbar
- **Tablet:** 768px - 1024px - Mobile Kategorie-Filter
- **Mobile:** 480px - 768px - Optimiertes Layout
- **Small Mobile:** 360px - 480px - 2-Spalten Grid
- **Extra Small:** <360px - 1-Spalte Grid

## Zukünftige Verbesserungen

### Empfehlungen:
1. **Caching implementieren** - Reduziert API-Calls
2. **Bilder von Klara laden** - Artikel-Bilder anzeigen
3. **Lagerbestand-Sync** - Echte Stock-Zahlen von Klara
4. **Preis-Updates** - Automatische Preis-Synchronisation

### Optionale Features:
- Webhook von Klara für Echtzeit-Updates
- Admin-Dashboard für Klara-Sync-Status
- Fehler-Logging & Monitoring

## Support

Bei Problemen:
1. Teste `test-klara.php`
2. Aktiviere Debug-Modus
3. Prüfe PHP Error-Log
4. Prüfe Browser-Konsole

## Changelog

### Version 1.0 (2025-11-01)
- ✅ Klara API Integration
- ✅ Kategorien von Klara
- ✅ Artikel von Klara (~170 Produkte)
- ✅ Mobile Kategorie-Filter
- ✅ Responsive Design
- ✅ Such- und Filter-Funktionen
