<?php
// api/edit-content.php - Handle inline content editing

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Sicherheit: Nur Admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die(json_encode(['success' => false, 'error' => 'Nicht autorisiert']));
}

$data = json_decode(file_get_contents('php://input'), true);
$key = $data['key'] ?? '';
$value = $data['value'] ?? '';
$type = $data['type'] ?? 'text';

if (empty($key)) {
    die(json_encode(['success' => false, 'error' => 'Keine Key']));
}

// Sicherheit: Value escapen
$value_safe = $db->real_escape_string($value);

// In Settings-Tabelle speichern
if (update_setting($key, $value)) {
    // Log speichern
    error_log("✅ Admin Edit: $key = $value");
    
    die(json_encode(['success' => true, 'message' => 'Gespeichert!']));
} else {
    die(json_encode(['success' => false, 'error' => 'Fehler beim Speichern']));
}
?>