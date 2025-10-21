<?php
// api/toggle-featured-wine.php - Wein als Neuheit markieren/entfernen

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Sicherheit: Nur Admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die(json_encode(['success' => false, 'error' => 'Nicht autorisiert']));
}

$wine_id = isset($_POST['wine_id']) ? (int)$_POST['wine_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$wine_id || !$action) {
    die(json_encode(['success' => false, 'error' => 'Fehlende Parameter']));
}

// Überprüfe ob Wein existiert
$check = $db->query("SELECT id FROM wines WHERE id = $wine_id");
if ($check->num_rows === 0) {
    die(json_encode(['success' => false, 'error' => 'Wein nicht gefunden']));
}

// Toggle featured status
if ($action === 'add') {
    $query = $db->query("UPDATE wines SET is_featured = 1 WHERE id = $wine_id");
} elseif ($action === 'remove') {
    $query = $db->query("UPDATE wines SET is_featured = 0 WHERE id = $wine_id");
} else {
    die(json_encode(['success' => false, 'error' => 'Unbekannte Aktion']));
}

if ($query) {
    error_log("Wein $wine_id als Neuheit " . ($action === 'add' ? 'hinzugefügt' : 'entfernt'));
    die(json_encode(['success' => true, 'message' => 'Erfolg!']));
} else {
    die(json_encode(['success' => false, 'error' => 'Datenbankfehler: ' . $db->error]));
}
?>