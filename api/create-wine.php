<?php
// api/create-wine.php - Neuen Wein erstellen und als Featured markieren

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Sicherheit: Nur Admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die(json_encode(['success' => false, 'error' => 'Nicht autorisiert']));
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (empty($name)) {
    die(json_encode(['success' => false, 'error' => 'Weinname erforderlich']));
}

// Sicherheit: Input escapen
$name_safe = $db->real_escape_string($name);

// Überprüfe ob Wein bereits existiert
$check = $db->query("SELECT id FROM wines WHERE name = '$name_safe'");
if ($check->num_rows > 0) {
    die(json_encode(['success' => false, 'error' => 'Dieser Wein existiert bereits']));
}

// Neue Wein mit Standard-Werten erstellen
$query = "INSERT INTO wines (
    name,
    category_id,
    description,
    price,
    stock,
    is_featured,
    created_at
) VALUES (
    '$name_safe',
    1,
    'Neuer Wein - Bitte ergänzen',
    0.00,
    0,
    1,
    NOW()
)";

if ($db->query($query)) {
    $wine_id = $db->insert_id;
    
    error_log("Neuer Wein erstellt: $name (ID: $wine_id)");
    
    die(json_encode([
        'success' => true,
        'message' => 'Wein erstellt!',
        'wine_id' => $wine_id
    ]));
} else {
    die(json_encode(['success' => false, 'error' => 'Datenbankfehler: ' . $db->error]));
}
?>

