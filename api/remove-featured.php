<?php
// api/remove-featured.php - Entfernt Featured Status von Produkten, Events oder News

header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin-Check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Keine Berechtigung']));
}

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? '';

if (empty($type) || empty($id)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Typ und ID erforderlich']));
}

try {
    $success = false;

    switch ($type) {
        case 'product':
            // Klara-Produkt: is_featured in klara_products_extended auf 0 setzen
            $id = $db->real_escape_string($id);
            $success = $db->query("UPDATE klara_products_extended SET is_featured = 0 WHERE klara_article_id = '$id'");
            break;

        case 'event':
            // Event: is_featured auf 0 setzen
            $id = (int)$id;
            $success = set_event_featured($id, false);
            break;

        case 'news':
            // Custom News: is_featured auf 0 setzen
            $id = (int)$id;
            $success = $db->query("UPDATE custom_news SET is_featured = 0 WHERE id = $id");
            break;

        default:
            http_response_code(400);
            die(json_encode(['success' => false, 'error' => 'Ung\u00fcltiger Typ']));
    }

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Erfolgreich entfernt']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Fehler beim Entfernen']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
