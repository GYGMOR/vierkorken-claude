<?php
// TEST SCRIPT - Featured System komplett testen
// Aufrufen: /test_featured_system.php

require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Featured System - Debug Test</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } pre { background: #f5f5f5; padding: 10px; } .success { color: green; } .error { color: red; }</style>";

// Test 1: Produkt 6 als Featured setzen
echo "<h2>Test 1: Produkt als Featured setzen</h2>";
$test_data = [
    'klara_article_id' => '6',
    'producer' => 'Test Produzent',
    'vintage' => 2020,
    'region' => 'Test Region',
    'alcohol_content' => 13.5,
    'short_description' => 'Test Beschreibung',
    'extended_description' => 'Erweiterte Test Beschreibung',
    'is_featured' => 1,
    'custom_price' => 25.50,
    'featured_bg_color' => '#ff0000',
    'featured_text_color' => '#ffffff'
];

$result = update_klara_extended_data('6', $test_data);
echo $result ? "<p class='success'>✓ Update erfolgreich</p>" : "<p class='error'>✗ Update fehlgeschlagen: " . $db->error . "</p>";

// Test 2: Daten aus DB lesen
echo "<h2>Test 2: Daten aus DB lesen</h2>";
$loaded = get_klara_extended_data('6');
echo "<pre>";
print_r($loaded);
echo "</pre>";

// Überprüfung
$checks = [
    'is_featured' => $loaded['is_featured'] == 1,
    'vintage' => $loaded['vintage'] == 2020,
    'featured_bg_color' => $loaded['featured_bg_color'] == '#ff0000',
    'featured_text_color' => $loaded['featured_text_color'] == '#ffffff',
    'producer' => $loaded['producer'] == 'Test Produzent'
];

echo "<h3>Überprüfung:</h3>";
foreach ($checks as $field => $passed) {
    $icon = $passed ? '✓' : '✗';
    $class = $passed ? 'success' : 'error';
    echo "<p class='$class'>$icon $field: " . ($passed ? 'OK' : 'FEHLER') . "</p>";
}

// Test 3: Featured Produkte holen
echo "<h2>Test 3: Featured Produkte holen</h2>";
$featured = get_klara_featured_products(10);
echo "<p>Anzahl Featured Produkte: " . count($featured) . "</p>";

if (count($featured) > 0) {
    $product = $featured[0];
    echo "<h3>Erstes Featured Produkt:</h3>";
    echo "<pre>";
    print_r($product);
    echo "</pre>";

    echo "<h3>Farben-Check:</h3>";
    $color_check = [
        'featured_bg_color exists' => isset($product['featured_bg_color']),
        'featured_bg_color value' => $product['featured_bg_color'] ?? 'NOT SET',
        'featured_text_color exists' => isset($product['featured_text_color']),
        'featured_text_color value' => $product['featured_text_color'] ?? 'NOT SET'
    ];

    foreach ($color_check as $key => $value) {
        echo "<p><strong>$key:</strong> " . (is_bool($value) ? ($value ? 'YES' : 'NO') : $value) . "</p>";
    }
}

echo "<hr>";
echo "<h2>Aufräumen</h2>";
echo "<p><a href='?cleanup=1'>Klick hier um Test-Daten zu entfernen</a></p>";

if (isset($_GET['cleanup'])) {
    $db->query("UPDATE klara_products_extended SET is_featured = 0 WHERE klara_article_id = '6'");
    echo "<p class='success'>✓ Aufgeräumt! Test-Featured entfernt.</p>";
}
?>
