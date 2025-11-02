<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/kantone.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Wappen Debug Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .debug-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }

        /* Gleiche CSS wie auf der echten Seite */
        .wine-card {
            position: relative;
            width: 300px;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px 0;
        }
        .wine-image-container {
            position: relative;
            width: 100%;
            height: 300px;
            background: #f5f5f5;
            overflow: hidden;
        }
        .wine-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Wappen Badge CSS */
        .kanton-wappen-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0;
            padding: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 100;
            pointer-events: none;
            border-top-left-radius: 4px;
        }

        .kanton-wappen-badge img {
            display: block;
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <h1>🔍 Wappen Debug Test</h1>";

// TEST 1: Klara Produkte mit Kanton prüfen
echo "<div class='debug-section'>
    <h2>Test 1: Klara Produkte mit Kanton in DB</h2>";

$result = $db->query("SELECT klara_article_id, kanton, image_url FROM klara_products_extended WHERE kanton IS NOT NULL AND kanton != ''");
$products_with_kanton = [];

if ($result && $result->num_rows > 0) {
    echo "<p class='success'>✓ Gefunden: " . $result->num_rows . " Produkte mit Kanton in DB</p>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Klara ID</th><th>Kanton</th><th>Image URL</th></tr>";

    while ($row = $result->fetch_assoc()) {
        $products_with_kanton[] = $row;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['klara_article_id']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['kanton']) . "</strong></td>";
        echo "<td>" . (empty($row['image_url']) ? '<span class="error">LEER</span>' : htmlspecialchars(substr($row['image_url'], 0, 50)) . '...') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ KEINE Produkte mit Kanton gefunden!</p>";
    echo "<p class='info'>Bitte im Admin ein Produkt bearbeiten und einen Kanton auswählen.</p>";
}

echo "</div>";

// TEST 2: Wappen-Dateien prüfen
echo "<div class='debug-section'>
    <h2>Test 2: Wappen SVG-Dateien</h2>";

$wappen_dir = __DIR__ . '/assets/images/kantone';
if (is_dir($wappen_dir)) {
    echo "<p class='success'>✓ Verzeichnis existiert: assets/images/kantone/</p>";

    $files = glob($wappen_dir . '/*.svg');
    echo "<p class='info'>Gefundene SVG-Dateien: " . count($files) . "</p>";

    if (count($files) > 0) {
        echo "<div style='display: flex; flex-wrap: wrap; gap: 10px;'>";
        foreach ($files as $file) {
            $basename = basename($file);
            $code = str_replace('.svg', '', $basename);
            echo "<div style='text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 4px;'>";
            echo "<img src='assets/images/kantone/{$basename}' style='width: 40px; height: 40px;'><br>";
            echo "<small>{$code}</small>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p class='error'>✗ Keine SVG-Dateien gefunden!</p>";
    }
} else {
    echo "<p class='error'>✗ Verzeichnis existiert NICHT!</p>";
}

echo "</div>";

// TEST 3: Shop-Seite Simulation
echo "<div class='debug-section'>
    <h2>Test 3: Shop-Seite Simulation</h2>";

if (count($products_with_kanton) > 0) {
    $test_product_id = $products_with_kanton[0]['klara_article_id'];
    echo "<p class='info'>Teste mit Produkt-ID: <strong>{$test_product_id}</strong></p>";

    // Klara API laden
    $all_wines = klara_get_articles();
    $test_wine = null;

    foreach ($all_wines as $wine) {
        if ($wine['id'] === $test_product_id) {
            $test_wine = $wine;
            break;
        }
    }

    if ($test_wine) {
        echo "<p class='success'>✓ Produkt aus Klara API geladen</p>";

        // Extended Daten laden
        $extended = get_klara_extended_data($test_product_id);

        if ($extended) {
            echo "<p class='success'>✓ Extended Daten geladen</p>";
            echo "<p>Kanton aus Extended: <strong>" . ($extended['kanton'] ?? 'NICHT GESETZT') . "</strong></p>";

            // Merge wie in shop.php
            foreach ($extended as $key => $value) {
                if ($key !== 'id' && !empty($value)) {
                    $test_wine[$key] = $value;
                }
            }
            $test_wine['id'] = $test_product_id;

            echo "<p>Kanton nach Merge: <strong>" . ($test_wine['kanton'] ?? 'NICHT GESETZT') . "</strong></p>";

            // Render wie auf Shop-Seite
            echo "<h3>Simulation der Wine-Card:</h3>";
            echo "<div class='wine-card'>";
            echo "<div class='wine-image-container'>";

            if (!empty($test_wine['image_url'])) {
                echo "<img src='" . htmlspecialchars($test_wine['image_url']) . "' alt='Wein'>";
            } else {
                echo "<div style='display: flex; align-items: center; justify-content: center; height: 100%; color: #999;'>Kein Bild</div>";
            }

            // Wappen-Code wie in shop.php
            if (!empty($test_wine['kanton'])) {
                echo "<!-- WAPPEN SOLLTE HIER SEIN -->";
                echo "<div class='kanton-wappen-badge'>";
                echo render_kanton_wappen($test_wine['kanton'], 32);
                echo "</div>";
            } else {
                echo "<!-- KEIN KANTON GESETZT -->";
            }

            echo "</div>";
            echo "<div style='padding: 15px;'>";
            echo "<h4>" . htmlspecialchars($test_wine['name']) . "</h4>";
            echo "<p>Kanton: " . ($test_wine['kanton'] ?? 'Kein Kanton') . "</p>";
            echo "</div>";
            echo "</div>";

        } else {
            echo "<p class='error'>✗ Keine Extended Daten gefunden</p>";
        }

    } else {
        echo "<p class='error'>✗ Produkt nicht in Klara API gefunden</p>";
    }
} else {
    echo "<p class='info'>Keine Produkte mit Kanton zum Testen verfügbar.</p>";
}

echo "</div>";

// TEST 4: render_kanton_wappen() Funktion direkt testen
echo "<div class='debug-section'>
    <h2>Test 4: render_kanton_wappen() Funktion</h2>";

$test_kantone = ['ZH', 'BE', 'GR', 'VS'];
echo "<p>Teste mit: " . implode(', ', $test_kantone) . "</p>";

echo "<div style='display: flex; gap: 20px; margin-top: 10px;'>";
foreach ($test_kantone as $code) {
    echo "<div style='text-align: center;'>";
    echo "<div style='width: 100px; height: 100px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;'>";
    $wappen_html = render_kanton_wappen($code, 40);
    echo $wappen_html;
    echo "</div>";
    echo "<p><strong>{$code}</strong></p>";
    echo "<small>" . get_schweizer_kantone()[$code] . "</small>";
    echo "</div>";
}
echo "</div>";

echo "</div>";

// TEST 5: HTML Source Code Check
echo "<div class='debug-section'>
    <h2>Test 5: Generierter HTML-Code</h2>";

if (!empty($test_wine['kanton'])) {
    $generated_html = render_kanton_wappen($test_wine['kanton'], 32);
    echo "<p>Generierter HTML für Wappen:</p>";
    echo "<pre>" . htmlspecialchars($generated_html) . "</pre>";
}

echo "</div>";

echo "</body></html>";
?>
