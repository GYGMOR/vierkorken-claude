<?php
// TEST: Produkt 6 speichern und laden + Bild-URL testen

require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Test: Produkt 6 - Bild-URL Check</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} img{max-width:200px;border:2px solid #ccc;}</style>";

// Test 1: Prüfen ob Produkt 6 existiert
echo "<h2>Test 1: Existiert Produkt 6 in DB?</h2>";
$existing = get_klara_extended_data('6');
if ($existing) {
    echo "<p class='success'>✓ Ja, existiert bereits</p>";
    echo "<pre>" . print_r($existing, true) . "</pre>";
} else {
    echo "<p class='error'>✗ Nein, existiert noch nicht (wird INSERT machen)</p>";
}

// Test 2: Daten speichern
echo "<h2>Test 2: Daten speichern</h2>";
$test_data = [
    'klara_article_id' => '6',
    'vintage' => 2004,
    'producer' => 'Test Weingut',
    'alcohol_content' => 13.5,
    'short_description' => 'Test Beschreibung für Produkt 6',
    'is_featured' => 1,
    'featured_bg_color' => '#ff5500',
    'featured_text_color' => '#ffffff'
];

echo "<p>Speichere folgende Daten:</p>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

$result = update_klara_extended_data('6', $test_data);

if ($result) {
    echo "<p class='success'>✓ Speichern erfolgreich</p>";
} else {
    echo "<p class='error'>✗ Speichern fehlgeschlagen!</p>";
    echo "<p class='error'>DB Error: " . $db->error . "</p>";
}

// Test 3: Daten wieder laden
echo "<h2>Test 3: Daten wieder laden</h2>";
$loaded = get_klara_extended_data('6');
if ($loaded) {
    echo "<p class='success'>✓ Laden erfolgreich</p>";
    echo "<pre>" . print_r($loaded, true) . "</pre>";

    // Vergleichen
    echo "<h3>Vergleich:</h3>";
    $checks = [
        'vintage' => $loaded['vintage'] == 2004,
        'producer' => $loaded['producer'] == 'Test Weingut',
        'is_featured' => $loaded['is_featured'] == 1,
        'featured_bg_color' => $loaded['featured_bg_color'] == '#ff5500'
    ];

    foreach ($checks as $field => $passed) {
        $icon = $passed ? '✓' : '✗';
        $class = $passed ? 'success' : 'error';
        echo "<p class='$class'>$icon $field: " . ($passed ? 'OK' : 'FEHLER - Wert: ' . $loaded[$field]) . "</p>";
    }
} else {
    echo "<p class='error'>✗ Laden fehlgeschlagen!</p>";
}

// Test 4: Raw SQL checken
echo "<h2>Test 4: Raw SQL Check</h2>";
$raw_result = $db->query("SELECT * FROM klara_products_extended WHERE klara_article_id = '6'");
if ($raw_result && $raw_result->num_rows > 0) {
    $row = $raw_result->fetch_assoc();
    echo "<p class='success'>✓ Eintrag in DB gefunden</p>";
    echo "<pre>" . print_r($row, true) . "</pre>";

    // Test 5: Bild-URL anzeigen
    if (!empty($row['image_url'])) {
        echo "<h2>Test 5: Bild-URL Anzeige</h2>";
        echo "<p><strong>URL:</strong> " . htmlspecialchars($row['image_url']) . "</p>";
        echo "<p>Lade Bild:</p>";
        echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Produkt-Bild' onerror=\"this.style.border='3px solid red'; this.alt='❌ FEHLER: Bild konnte nicht geladen werden'\">";
    } else {
        echo "<h2>Test 5: Bild-URL</h2>";
        echo "<p class='error'>✗ Keine image_url in DB gespeichert!</p>";
    }
} else {
    echo "<p class='error'>✗ Kein Eintrag in DB!</p>";
}
?>
