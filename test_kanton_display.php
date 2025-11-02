<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/kantone.php';

echo "<h1>Test: Kanton-Wappen Anzeige</h1>";
echo "<style>
body { font-family: Arial; padding: 20px; }
.test-box { border: 2px solid #ccc; padding: 20px; margin: 20px 0; position: relative; width: 300px; height: 200px; background: #f5f5f5; }
.kanton-wappen-badge { position: absolute; bottom: 8px; right: 8px; background: rgba(255,255,255,0.95); border-radius: 4px; padding: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
</style>";

// Test 1: Prüfe ob Produkt 6 einen Kanton hat
echo "<h2>Test 1: Produkt in DB prüfen</h2>";
$product = get_klara_extended_data('6');
if ($product) {
    echo "<pre>";
    print_r($product);
    echo "</pre>";

    if (!empty($product['kanton'])) {
        echo "<p style='color:green'>✓ Kanton in DB: <strong>{$product['kanton']}</strong></p>";
    } else {
        echo "<p style='color:red'>✗ Kein Kanton gespeichert!</p>";
    }
} else {
    echo "<p style='color:red'>✗ Produkt 6 nicht gefunden in Extended-Daten</p>";
}

// Test 2: Wappen-Datei prüfen
echo "<h2>Test 2: Wappen-Datei prüfen</h2>";
if (!empty($product['kanton'])) {
    $wappen_path = "assets/images/kantone/{$product['kanton']}.svg";
    $full_path = __DIR__ . '/' . $wappen_path;

    echo "<p>Erwartet: <code>$wappen_path</code></p>";

    if (file_exists($full_path)) {
        echo "<p style='color:green'>✓ Datei existiert!</p>";
        echo "<p>Dateigröße: " . filesize($full_path) . " Bytes</p>";
    } else {
        echo "<p style='color:red'>✗ Datei NICHT gefunden!</p>";
        echo "<p>Vollständiger Pfad: $full_path</p>";
    }
}

// Test 3: Wappen rendern
echo "<h2>Test 3: Wappen-Render Test</h2>";
if (!empty($product['kanton'])) {
    echo "<div class='test-box'>";
    echo "<p>Simuliertes Weinbild</p>";
    echo "<div class='kanton-wappen-badge'>";
    echo render_kanton_wappen($product['kanton'], 30);
    echo "</div>";
    echo "</div>";

    echo "<h3>Direkter IMG-Test:</h3>";
    $wappen_url = get_kanton_wappen_url($product['kanton']);
    echo "<img src='$wappen_url' style='width:50px;height:50px;border:2px solid red;' onerror=\"this.style.border='3px solid red'; this.alt='FEHLER'\">";
    echo "<p>URL: <code>$wappen_url</code></p>";
}

// Test 4: Shop-Seite Extended-Daten Check
echo "<h2>Test 4: Werden Extended-Daten im Shop geladen?</h2>";
$wines = klara_get_articles(null, null);
$wine_6 = null;
foreach ($wines as $wine) {
    if ($wine['id'] === '6') {
        $wine_6 = $wine;
        break;
    }
}

if ($wine_6) {
    // Merge Extended wie in shop.php
    $klara_id = $wine_6['id'];
    $extended = get_klara_extended_data($klara_id);
    if ($extended) {
        $wine_6 = array_merge($wine_6, $extended);
        $wine_6['id'] = $klara_id;
    }

    echo "<p>Produkt 6 nach Merge:</p>";
    echo "<pre>";
    print_r([
        'id' => $wine_6['id'],
        'name' => $wine_6['name'],
        'kanton' => $wine_6['kanton'] ?? 'NICHT GESETZT',
        'producer' => $wine_6['producer'] ?? 'leer',
        'vintage' => $wine_6['vintage'] ?? 'leer'
    ]);
    echo "</pre>";

    if (!empty($wine_6['kanton'])) {
        echo "<p style='color:green'>✓ Kanton ist nach Merge vorhanden: <strong>{$wine_6['kanton']}</strong></p>";
    } else {
        echo "<p style='color:red'>✗ Kanton fehlt nach Merge!</p>";
    }
}
?>
