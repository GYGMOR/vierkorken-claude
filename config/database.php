<?php
// config/database.php
// Datenbankverbindung für Vier Korken Weinshop

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datenbank-Daten (VON HOSTTEC)
define('DB_HOST', 'localhost');
define('DB_USER', 'joel.hediger');
define('DB_PASSWORD', '5eOUGqc5ad%y?tr3');
define('DB_NAME', 'vierkorken');
define('DB_PORT', 3306);

// MySQLi Connection
$connection = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME,
    DB_PORT
);

// Fehlerbehandlung - Verbindung testen
if ($connection->connect_error) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Datenbankverbindung fehlgeschlagen',
        'message' => $connection->connect_error
    ]));
}

// UTF-8 Encoding setzen
$connection->set_charset("utf8mb4");

// Globale Variable für Datenbankzugriff
$db = $connection;

// Hilfsfunktion für sichere Queries
function db_query($sql, $params = []) {
    global $db;
    
    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        return [
            'error' => true,
            'message' => $db->error
        ];
    }
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        return $stmt->get_result();
    } else {
        return [
            'error' => true,
            'message' => $stmt->error
        ];
    }
}

error_log("✅ Datenbankverbindung erfolgreich!");
?>