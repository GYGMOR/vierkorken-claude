<?php
// test-db.php - Teste die Datenbankverbindung
// Öffne diese Datei im Browser: https://vierkorken.ch/vierkorken/test-db.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Datenbankverbindungs-Test</h2>";
echo "<hr>";

// Verbindungsdaten (VON HOSTTEC):
$db_host = 'localhost';
$db_user = 'joel.hediger';
$db_password = '5eOUGqc5ad%y?tr3';
$db_name = 'vierkorken';

echo "<p><strong>Verbindungsdaten:</strong></p>";
echo "Host: " . $db_host . "<br>";
echo "User: " . $db_user . "<br>";
echo "DB-Name: " . $db_name . "<br>";
echo "<hr>";

// Verbindung testen
$connection = @new mysqli($db_host, $db_user, $db_password, $db_name);

if ($connection->connect_error) {
    echo "<h3 style='color: red;'>FEHLER!</h3>";
    echo "<p><strong>Verbindung fehlgeschlagen:</strong></p>";
    echo "<p>" . $connection->connect_error . "</p>";
    echo "<hr>";
    echo "<p><strong>Was du tun kannst:</strong></p>";
    echo "<ul>";
    echo "<li>1. Überprüfe DB_HOST (oft 'localhost' oder spezifische IP)</li>";
    echo "<li>2. Überprüfe DB_USER und DB_PASSWORD</li>";
    echo "<li>3. Überprüfe DB_NAME</li>";
    echo "<li>4. Versuche es mit einem anderen Host (frag Hosttec Support)</li>";
    echo "</ul>";
} else {
    echo "<h3 style='color: green;'>ERFOLG!</h3>";
    echo "<p>Datenbankverbindung funktioniert!</p>";
    
    // Tabellen anzeigen
    $result = $connection->query("SHOW TABLES");
    echo "<p><strong>Tabellen in der Datenbank:</strong></p>";
    echo "<ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Test Query
    $wines_count = $connection->query("SELECT COUNT(*) FROM wines");
    $count_row = $wines_count->fetch_row();
    echo "<p><strong>Weine in DB:</strong> " . $count_row[0] . "</p>";
}

$connection->close();
?>