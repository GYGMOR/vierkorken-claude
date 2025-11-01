<?php
// test-klara.php - Teste Klara API Verbindung
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Klara API Test</h1>";

// Test Kategorien
echo "<h2>Kategorien:</h2>";
$categories = klara_get_categories();
echo "<p>Anzahl Kategorien: " . count($categories) . "</p>";
echo "<pre>";
print_r(array_slice($categories, 0, 5)); // Erste 5 Kategorien
echo "</pre>";

// Test Artikel
echo "<h2>Artikel:</h2>";
$articles = klara_get_articles();
echo "<p>Anzahl Artikel: " . count($articles) . "</p>";
echo "<pre>";
print_r(array_slice($articles, 0, 3)); // Erste 3 Artikel
echo "</pre>";

// PHP Info für cURL
echo "<h2>cURL Info:</h2>";
if (function_exists('curl_version')) {
    $curl_info = curl_version();
    echo "<pre>";
    print_r($curl_info);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>cURL ist NICHT installiert!</p>";
}
?>
