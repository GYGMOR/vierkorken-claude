# Klara POS Integration - Implementierungsplan

## Übersicht

Dieses Dokument beschreibt die komplette Integration zwischen Ihrer Vier Korken Webseite und dem Klara Kassensystem.

## Ziele

1. **Automatischer Datenimport**: Weine aus Klara automatisch in die Webseite importieren
2. **Echtzeit-Synchronisation**: Bestandsänderungen von Klara sofort auf der Webseite anzeigen
3. **Kategorie-Zuordnung**: Weine automatisch den richtigen Kategorien zuordnen
4. **Bidirektionale Sync**: Verkäufe auf der Webseite → Bestand in Klara reduzieren

---

## Phase 1: Datenanalyse & Import

### 1.1 Excel-Dateien analysieren

**Dateien:**
- `database/Artikel_Export.xlsx` - Produktdaten aus Klara
- `database/Delivery_Export_from_1_to_43.xlsx` - Lieferungen/Bestand

**Script:** `analyze_klara_data.php`

**Zu identifizierende Felder:**
- Artikelnummer / SKU
- Produktname
- Kategorie
- Preis
- Bestand / Stock
- Beschreibung
- Jahrgang (Vintage)
- Produzent
- Region
- Alkoholgehalt

### 1.2 Datenbank-Erweiterung

**Neue Felder in `wines` Tabelle:**
```sql
ALTER TABLE wines ADD COLUMN IF NOT EXISTS klara_article_id VARCHAR(50) UNIQUE;
ALTER TABLE wines ADD COLUMN IF NOT EXISTS klara_sku VARCHAR(50);
ALTER TABLE wines ADD COLUMN IF NOT EXISTS last_synced_at TIMESTAMP NULL;
ALTER TABLE wines ADD COLUMN IF NOT EXISTS sync_enabled BOOLEAN DEFAULT 1;
```

**Neue Tabelle für Sync-Logs:**
```sql
CREATE TABLE klara_sync_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_type ENUM('import', 'stock_update', 'new_product', 'price_update'),
    wine_id INT NULL,
    klara_article_id VARCHAR(50),
    old_value TEXT,
    new_value TEXT,
    status ENUM('success', 'error', 'pending'),
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Phase 2: Import-Script

### 2.1 Excel → Datenbank Import

**Script:** `import_wines_from_klara.php`

**Funktionen:**
1. Excel-Datei einlesen (PhpSpreadsheet oder CSV)
2. Kategorien automatisch zuordnen
3. Duplikate erkennen (via Artikelnummer)
4. Weine in DB einfügen oder aktualisieren
5. Log-Bericht erstellen

**Kategorie-Zuordnung (Automatisch):**
```php
function mapCategoryFromName($productName) {
    $name = strtolower($productName);

    // Weißwein
    if (preg_match('/weiss|blanc|chardonnay|sauvignon|riesling/i', $name)) {
        return 3; // Weißwein
    }

    // Rotwein
    if (preg_match('/rot|rouge|merlot|pinot noir|cabernet/i', $name)) {
        return 4; // Rotwein
    }

    // Rosé
    if (preg_match('/ros[eé]|blauburgunder/i', $name)) {
        return 2; // Rosé
    }

    // Schaumwein
    if (preg_match('/schaum|champagner|prosecco|sekt|crémant/i', $name)) {
        return 1; // Schaumwein
    }

    // Dessertwein
    if (preg_match('/dessert|süss|portwein|sherry/i', $name)) {
        return 5; // Dessertwein
    }

    // Alkoholfrei
    if (preg_match('/alkoholfrei|0%|ohne alkohol/i', $name)) {
        return 6; // Alkoholfreie Weine
    }

    // Default: Diverses
    return 8;
}
```

---

## Phase 3: Klara API Integration

### 3.1 Klara API Dokumentation prüfen

**Benötigt:**
- API-Key von Klara
- API-Endpunkte Dokumentation
- Webhook-URL Unterstützung

**Typische Endpunkte:**
- `GET /api/articles` - Alle Artikel abrufen
- `GET /api/articles/{id}` - Einzelner Artikel
- `GET /api/stock/{id}` - Bestand abfragen
- `POST /api/webhooks` - Webhook registrieren

### 3.2 API-Endpunkte auf der Webseite

**Neue Datei:** `api/klara-sync.php`

**Aktionen:**
- `sync_all` - Alle Artikel von Klara importieren
- `sync_single` - Einzelnen Artikel aktualisieren
- `update_stock` - Bestand aktualisieren
- `webhook_receiver` - Webhook von Klara empfangen

**Beispiel:**
```php
// api/klara-sync.php
switch ($_REQUEST['action']) {
    case 'sync_all':
        // Alle Artikel von Klara API holen
        $articles = fetchKlaraArticles();
        $imported = importArticlesToDatabase($articles);
        echo json_encode(['success' => true, 'imported' => $imported]);
        break;

    case 'update_stock':
        $articleId = $_POST['article_id'];
        $newStock = $_POST['stock'];
        updateWineStock($articleId, $newStock);
        break;

    case 'webhook':
        // Webhook von Klara empfangen
        $payload = json_decode(file_get_contents('php://input'), true);
        handleKlaraWebhook($payload);
        break;
}
```

---

## Phase 4: Echtzeit-Synchronisation

### 4.1 Webhook-Integration

**Was sind Webhooks?**
Klara sendet automatisch Benachrichtigungen an Ihre Webseite, wenn sich etwas ändert.

**Webhook-URL:**
```
https://vierkorken.ch/api/klara-sync.php?action=webhook
```

**Events:**
- `article.created` - Neuer Artikel erstellt
- `article.updated` - Artikel aktualisiert
- `stock.changed` - Bestand geändert
- `price.changed` - Preis geändert

**Webhook-Handler:**
```php
function handleKlaraWebhook($payload) {
    $event = $payload['event'];
    $article = $payload['data'];

    switch ($event) {
        case 'article.created':
            createWineFromKlara($article);
            break;

        case 'stock.changed':
            updateWineStock($article['id'], $article['stock']);
            break;

        case 'price.changed':
            updateWinePrice($article['id'], $article['price']);
            break;
    }

    // Log speichern
    logSync('webhook', $article['id'], $event, 'success');
}
```

### 4.2 Cronjob für regelmäßige Syncs

Falls Klara keine Webhooks unterstützt:

**Cron:** Alle 15 Minuten synchronisieren
```bash
*/15 * * * * curl https://vierkorken.ch/api/klara-sync.php?action=sync_all
```

---

## Phase 5: Bidirektionale Synchronisation

### 5.1 Webseite → Klara

**Bei Bestellung auf der Webseite:**
1. Bestand in lokaler DB reduzieren
2. API-Call an Klara senden, um Bestand zu reduzieren
3. Bestellung in Klara erfassen

**Code:**
```php
// Nach erfolgreicher Bestellung
function syncSaleToKlara($orderId) {
    global $db;

    $order = getOrder($orderId);

    foreach ($order['items'] as $item) {
        $wine = getWineById($item['wine_id']);

        if ($wine['klara_article_id']) {
            // Bestand in Klara reduzieren
            $klaraAPI = new KlaraAPI();
            $klaraAPI->reduceStock($wine['klara_article_id'], $item['quantity']);
        }
    }
}
```

---

## Phase 6: Admin-Dashboard Integration

### 6.1 Sync-Panel im Admin-Dashboard

**Neuer Tab:** `Klara Sync`

**Features:**
- Button: "Alle Artikel von Klara importieren"
- Button: "Bestand synchronisieren"
- Sync-Status anzeigen (Letzte Sync, Anzahl Produkte)
- Sync-Logs anzeigen (Tabelle mit allen Sync-Vorgängen)
- Fehlerhafte Syncs anzeigen und manuell korrigieren

**UI:**
```php
<div class="tab-content" id="klara-sync">
    <h2>Klara Kassensystem Integration</h2>

    <div class="sync-actions">
        <button onclick="syncAllFromKlara()">Alle Artikel importieren</button>
        <button onclick="syncStockFromKlara()">Bestand synchronisieren</button>
        <button onclick="testKlaraConnection()">Verbindung testen</button>
    </div>

    <div class="sync-status">
        <p>Letzte Synchronisation: <span id="last-sync">-</span></p>
        <p>Synchronisierte Artikel: <span id="sync-count">0</span></p>
        <p>Status: <span id="sync-status">-</span></p>
    </div>

    <h3>Sync-Logs</h3>
    <table class="sync-log-table">
        <!-- PHP-generierte Tabelle mit Logs -->
    </table>
</div>
```

---

## Was ich von Ihnen benötige:

### ✅ Schritt 1: Excel-Analyse
Ich brauche folgende Informationen aus den Excel-Dateien:
1. **Welche Spalte** enthält den **Produktnamen**?
2. **Welche Spalte** enthält die **Kategorie** oder **Produktgruppe**?
3. **Welche Spalte** enthält den **Preis**?
4. **Welche Spalte** enthält den **Bestand/Lagerstand**?
5. **Welche Spalte** enthält die **Artikelnummer/SKU**?
6. **Welche Spalte** enthält Beschreibung, Jahrgang, Produzent, Region?

**→ Bitte führen Sie aus:**
Öffnen Sie die Excel-Dateien und teilen Sie mir die Spaltennamen mit, ODER installieren Sie PhpSpreadsheet:

```bash
composer require phpoffice/phpspreadsheet
```

Dann können wir `analyze_klara_data.php` ausführen.

### ✅ Schritt 2: Klara API Zugang
1. Hat Klara eine **REST API**?
2. Haben Sie einen **API-Key** oder **API-Zugangsdaten**?
3. Link zur **Klara API-Dokumentation**?

Wenn Klara keine API hat: Können wir per **Excel-Export + Cronjob** arbeiten.

---

## Zusammenfassung

**Ja, ich kann das komplett umsetzen!**

**Was funktionieren wird:**
1. ✅ Automatischer Import aller Weine aus Klara
2. ✅ Automatische Kategorie-Zuordnung (Weißwein, Rotwein, etc.)
3. ✅ Bestandsverwaltung (Stock wird synchronisiert)
4. ✅ Neue Weine erscheinen automatisch im Shop
5. ✅ Verkäufe reduzieren Bestand automatisch
6. ✅ Admin-Panel zur Überwachung

**Was ich jetzt brauche:**
- Zugriff auf die Excel-Spaltenstruktur (oder PhpSpreadsheet installieren)
- Klara API-Dokumentation (falls vorhanden)

**Soll ich weitermachen?**
