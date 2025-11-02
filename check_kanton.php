<?php
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Kanton Check</h1>";

// Prüfe welche Produkte einen Kanton haben
$result = $db->query("SELECT klara_article_id, kanton, producer, vintage FROM klara_products_extended WHERE kanton IS NOT NULL AND kanton != '' LIMIT 10");

echo "<h2>Produkte mit Kanton in DB:</h2>";

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Klara ID</th><th>Kanton</th><th>Produzent</th><th>Jahrgang</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['klara_article_id'] . "</td>";
        echo "<td><strong style='color:green;'>" . $row['kanton'] . "</strong></td>";
        echo "<td>" . ($row['producer'] ?? '-') . "</td>";
        echo "<td>" . ($row['vintage'] ?? '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red; font-weight:bold;'>❌ KEINE Produkte mit Kanton gefunden!</p>";
    echo "<p>Das ist das Problem! Du musst im Admin bei den Klara-Produkten einen Kanton auswählen.</p>";
}

// Zeige alle Produkte
echo "<h2>Alle Produkte in Extended-Tabelle:</h2>";
$all = $db->query("SELECT klara_article_id, kanton, producer, image_url FROM klara_products_extended LIMIT 20");

if ($all && $all->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Klara ID</th><th>Kanton</th><th>Produzent</th><th>Hat Bild?</th></tr>";

    while ($row = $all->fetch_assoc()) {
        $has_kanton = !empty($row['kanton']);
        echo "<tr>";
        echo "<td>" . $row['klara_article_id'] . "</td>";
        echo "<td>" . ($has_kanton ? "<strong style='color:green;'>" . $row['kanton'] . "</strong>" : "<span style='color:red;'>leer</span>") . "</td>";
        echo "<td>" . ($row['producer'] ?? '-') . "</td>";
        echo "<td>" . (!empty($row['image_url']) ? '✓' : '✗') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Keine Extended-Daten gefunden.</p>";
}
?>
