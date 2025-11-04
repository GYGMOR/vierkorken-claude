# VIERKORKEN - PROJEKT-STRUKTUR

**Version:** 2.0
**Datum:** 04. November 2025
**Status:** ✅ Optimiert & Aufgeräumt

---

## 📁 HAUPT-VERZEICHNISSE

```
vierkorken/
├── api/                    # Backend API Endpoints
├── assets/                 # Frontend Assets (CSS, JS, Images)
├── components/             # Wiederverwendbare PHP Komponenten
├── config/                 # Konfigurationsdateien (gitignored)
├── database/               # Datenbank-bezogene Dateien
├── DB/                     # Datenbank Dumps (gitignored)
├── docs/                   # Projekt-Dokumentation
├── includes/               # Gemeinsame PHP Includes
├── pages/                  # Seiten-Templates
├── utils/                  # Utility Scripts und Tools
├── index.php              # Haupt-Einstiegspunkt
├── CLAUDE.md              # Projekt-Dokumentation für AI
├── CLEANUP_REPORT.md      # Cleanup-Dokumentation
└── STRUCTURE.md           # Dieses Dokument
```

---

## 🔍 DETAILLIERTE STRUKTUR

### 📡 `/api/` - Backend API Endpoints
JSON API Endpunkte für AJAX-Requests

```
api/
├── auth.php                    # User Authentication
├── cart.php                    # Shopping Cart Operations
├── orders.php                  # Order Management
├── coupons.php                 # Coupon/Discount System
├── wishlist.php                # User Wishlist
├── user-addresses.php          # Address Management
├── user-portal.php             # User Portal Operations
├── password-reset.php          # Password Reset
├── edit-content.php            # Content Editing (Admin)
├── create-wine.php             # Wine Creation (Admin)
├── toggle-featured-wine.php    # Featured Wine Toggle
├── remove-featured.php         # Remove Featured Items
├── get-featured-wines.php      # Get Featured Wines
├── upload-banner.php           # Banner Upload
├── events.php                  # Event Management
├── toggle-event-featured.php   # Featured Event Toggle
├── news-items.php              # News Management
├── klara-articles.php          # Klara API: Articles
├── klara-categories.php        # Klara API: Categories
└── klara-products-extended.php # Klara API: Extended Products
```

**Wichtige Hinweise:**
- Alle APIs geben JSON zurück
- Session-basierte Authentifizierung
- Admin-APIs prüfen `$_SESSION['is_admin']`
- User-APIs prüfen `$_SESSION['user_id']`

---

### 🎨 `/assets/` - Frontend Assets

```
assets/
├── css/
│   ├── main.css                # Haupt-Styles
│   ├── icons.css               # Icon-System
│   ├── responsive.css          # Responsive Design
│   ├── user-portal-extended.css # User Portal Styles
│   └── dynamic-colors.php      # Dynamische Theme-Farben (PHP)
│
├── images/
│   ├── banners/                # Banner-Bilder
│   ├── kantone/                # Schweizer Kantone Wappen
│   ├── uploads/                # User-Uploads
│   └── wines/                  # Wein-Produktbilder
│
└── js/
    ├── main.js                 # Globale JavaScript-Funktionen
    ├── cart.js                 # Warenkorb-Logik
    └── user-portal.js          # User Portal Funktionen
```

**Wichtige Hinweise:**
- `dynamic-colors.php` generiert CSS aus Datenbank-Theme-Settings
- localStorage Key: `vier_korken_cart`
- Bilder in `uploads/` und `wines/` sind user-generated

---

### 🧩 `/components/` - PHP Komponenten

```
components/
├── admin-klara-products.php    # Klara Produkt-Verwaltung (Admin)
└── wine-rating-section.php     # Wein-Bewertungs-Komponente
```

**Verwendung:**
```php
include 'components/wine-rating-section.php';
```

---

### ⚙️ `/config/` - Konfiguration

```
config/
├── database.php                # Datenbank-Verbindung (GITIGNORED)
├── keys.php                    # API-Keys & Secrets (GITIGNORED)
└── security.php                # Security-Funktionen
```

**Wichtige Hinweise:**
- ⚠️ `database.php` und `keys.php` sind gitignored!
- Enthalten sensible Credentials
- Niemals committen!

**database.php:**
- Definiert `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`
- Erstellt globale `$db` Verbindung
- Bietet `db_query()` Helper für Prepared Statements

**keys.php:**
```php
return [
    'apiKey' => '...',
    'apiSecret' => '...',
    'klara_api_key' => '...',
    'klara_api_baseurl' => 'https://api.klara.ch'
];
```

**security.php:**
- CSRF Protection
- Session Handling
- `require_admin()`, `require_login()` Funktionen

---

### 💾 `/database/` & `/DB/` - Datenbank

```
database/
└── migrations-archive/         # Archivierte SQL-Migrations
    ├── 001_news_events_system.sql
    ├── 002_user_addresses.sql
    ├── 003_orders_and_coupons.sql
    └── UPDATE_orders_table.sql

DB/                             # Datenbank Dumps (GITIGNORED)
└── vierkorken (1).sql         # Aktueller Datenbank-Dump
```

**Wichtige Hinweise:**
- Migrations sind bereits angewendet (nur Dokumentation)
- `DB/*.sql` sind gitignored
- Aktuelle DB: `vierkorken (1).sql` (2502 Zeilen)

---

### 📖 `/docs/` - Dokumentation

```
docs/
├── guides/                     # Anleitungen & Integrations-Guides
│   ├── KLARA_INTEGRATION.md
│   ├── KLARA_INTEGRATION_PLAN.md
│   ├── SECURITY_QUICKSTART.md
│   └── TODO_SECURITY.md
│
├── reports/                    # Status Reports & Change Logs
│   ├── DEBUGGING_REPORT.md
│   ├── FIXES_APPLIED.md
│   ├── RESPONSIVE_FIXES.md
│   └── SECURITY_REPORT.md
│
└── README.md                   # Dokumentations-Übersicht
```

**Organisation:**
- `guides/` = Wie mache ich X?
- `reports/` = Was wurde geändert?

---

### 🔧 `/includes/` - Gemeinsame PHP Includes

```
includes/
├── header.php                  # Site Header (Navigation, Mobile Menu)
├── footer.php                  # Site Footer
├── functions.php               # Globale Helper-Funktionen
├── editable.php                # Content-Editing System
├── icons.php                   # SVG Icon-System
├── kantone.php                 # Schweizer Kantone Daten
└── login-modal.php             # Login Modal Component
```

**Wichtigste Funktionen (`functions.php`):**
- `get_setting($key, $default)` - Settings aus DB
- `update_setting($key, $value)` - Settings speichern
- `safe_output($text)` - XSS Protection
- `is_admin()` - Admin-Check
- `get_wine_by_id($id)` - Wein aus DB laden
- `get_all_categories()` - Kategorien laden
- Theme-Functions: `get_theme_color()`, `update_theme_color()`

---

### 📄 `/pages/` - Seiten-Templates

```
pages/
├── home.php                    # Startseite
├── shop.php                    # Weinshop (Produktliste)
├── product.php                 # Produkt-Detailseite
├── cart.php                    # Warenkorb
├── checkout.php                # Checkout-Prozess
├── order-confirmation.php      # Bestellbestätigung
├── register-after-order.php    # Registrierung nach Bestellung
├── order-history.php           # Bestellhistorie
├── user-portal.php             # User Account Portal
├── wishlist.php                # Wunschliste
├── events.php                  # Events-Übersicht
├── contact.php                 # Kontakt-Seite
├── newsletter.php              # Newsletter
├── impressum.php               # Impressum
├── agb.php                     # AGB
├── datenschutz.php             # Datenschutz
├── forgot-password.php         # Passwort vergessen
├── reset-password.php          # Passwort zurücksetzen
└── admin-dashboard.php         # Admin Dashboard
```

**Routing:**
Pages werden via `?page=xyz` geladen:
```php
// In index.php:
$allowed_pages = ['home', 'shop', 'product', 'cart', ...];
include "pages/{$page}.php";
```

---

### 🛠️ `/utils/` - Utilities

```
utils/
└── scripts/                    # Utility Scripts (GITIGNORED)
    ├── generate-hash.php       # Passwort-Hash Generator
    ├── toggle-edit.php         # Edit-Mode Toggle
    ├── download_kantone_wappen.php # Kantone Wappen Downloader
    └── analyze_excel.py        # Excel Analyse Tool
```

**Verwendung:**
```bash
# Passwort-Hash generieren
php utils/scripts/generate-hash.php

# Kantone Wappen herunterladen (einmalig)
php utils/scripts/download_kantone_wappen.php

# Edit-Mode togglen (Development)
php utils/scripts/toggle-edit.php
```

---

## 🚀 HAUPT-DATEIEN (Root)

### `index.php` - Main Entry Point
- Single Entry Point für gesamte Anwendung
- Query-Parameter Routing: `?page=xyz`
- Session Management
- Theme Loading
- Security Headers

### `CLAUDE.md` - AI-Dokumentation
- Projekt-Übersicht für Claude Code
- Architektur-Erklärung
- Development Patterns
- Wichtigste Funktionen

### `CLEANUP_REPORT.md` - Cleanup-Dokumentation
- Übersicht aller gelöschten Dateien
- Optimierungen & Bug-Fixes
- Empfehlungen für weitere Verbesserungen

### `STRUCTURE.md` - Dieses Dokument
- Vollständige Ordnerstruktur
- Beschreibung aller Verzeichnisse
- Verwendungshinweise

---

## 📊 DATEISTATISTIK

| Kategorie | Anzahl |
|-----------|--------|
| **API Endpoints** | 23 |
| **Pages** | 18 |
| **CSS Dateien** | 5 |
| **JavaScript Dateien** | 3 |
| **PHP Components** | 2 |
| **Includes** | 6 |
| **Utility Scripts** | 4 |
| **Dokumentation** | 12 |

---

## 🔐 SICHERHEIT & .gitignore

### Gitignored (nicht committed):
- `config/database.php` - DB Credentials
- `config/keys.php` - API Keys
- `DB/*.sql` - Datenbank Dumps
- `utils/scripts/*.php` - Utility Scripts
- `utils/scripts/*.py` - Python Tools
- `.claude/settings.local.json` - Claude Settings

### Committed:
- Alle Code-Dateien (PHP, JS, CSS)
- Assets (außer user-uploads)
- Dokumentation
- Migrations-Archive (nur Doku)

---

## 🎯 VERWENDUNG

### Neue Seite hinzufügen:
1. Erstelle `pages/meine-seite.php`
2. Füge `'meine-seite'` zu `$allowed_pages` in `index.php` hinzu
3. Füge Title-Mapping in `index.php` hinzu
4. Verlinke: `<a href="?page=meine-seite">Link</a>`

### Neue API hinzufügen:
1. Erstelle `api/mein-endpoint.php`
2. Header: `header('Content-Type: application/json');`
3. Includes: `require_once '../config/database.php';`
4. Auth-Check falls nötig
5. Return JSON: `echo json_encode(['success' => true]);`

### Assets hinzufügen:
- **CSS**: `assets/css/dateiname.css` → In `index.php` einbinden
- **JS**: `assets/js/dateiname.js` → In `index.php` einbinden
- **Bilder**: `assets/images/kategorie/bild.jpg`

---

## 📞 SUPPORT & WEITERENTWICKLUNG

### Dokumentation aktualisieren:
1. **Projekt-Änderungen** → Update `CLAUDE.md`
2. **Neue Features** → Dokumentiere in `docs/guides/`
3. **Bug-Fixes** → Update `docs/reports/FIXES_APPLIED.md`
4. **Struktur-Änderungen** → Update `STRUCTURE.md`

### Vor Git Commit:
1. Prüfe `.gitignore` für sensible Dateien
2. Update Dokumentation falls nötig
3. Teste alle geänderten Funktionen
4. Prüfe ob API-Keys committed werden (NIEMALS!)

---

## ✅ BEST PRACTICES

### Code-Qualität:
- ✅ Verwende Prepared Statements für DB-Queries
- ✅ `safe_output()` für User-Input
- ✅ Session-Checks für geschützte Bereiche
- ✅ Konsistente Error-Handling in APIs

### Datei-Organisation:
- ✅ APIs in `/api/`
- ✅ Pages in `/pages/`
- ✅ Wiederverwendbare Components in `/components/`
- ✅ Utilities in `/utils/scripts/`
- ✅ Dokumentation in `/docs/`

### Security:
- ✅ Credentials in `config/` (gitignored)
- ✅ CSRF Protection aktiviert
- ✅ Input-Validierung auf Server-Seite
- ✅ XSS-Protection mit `safe_output()`

---

**Erstellt:** 04. November 2025
**Version:** 2.0
**Status:** ✅ Production-Ready
**Maintainer:** Vierkorken Development Team
