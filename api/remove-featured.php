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

// DEBUG LOG
error_log("remove-featured.php called with type=$type, id=$id");

if (empty($type) || empty($id)) {
    error_log("remove-featured.php: Missing type or id");
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Typ und ID erforderlich']));
}

try {
    $success = false;

    switch ($type) {
        case 'product':
            // Klara-Produkt: is_featured in klara_products_extended auf 0 setzen
            $id = $db->real_escape_string($id);
            $sql = "UPDATE klara_products_extended SET is_featured = 0 WHERE klara_article_id = '$id'";
            error_log("remove-featured.php: Executing SQL: $sql");
            $success = $db->query($sql);
            if (!$success) {
                error_log("remove-featured.php: SQL error: " . $db->error);
            }
            break;

        case 'event':
            // Event: is_featured auf 0 setzen
            $id = (int)$id;
            error_log("remove-featured.php: Calling set_event_featured($id, false)");
            $success = set_event_featured($id, false);
            break;

        case 'news':
            // Custom News: is_featured auf 0 setzen
            $id = (int)$id;
            $sql = "UPDATE custom_news SET is_featured = 0 WHERE id = $id";
            error_log("remove-featured.php: Executing SQL: $sql");
            $success = $db->query($sql);
            if (!$success) {
                error_log("remove-featured.php: SQL error: " . $db->error);
            }
            break;

        default:
            error_log("remove-featured.php: Invalid type: $type");
            http_response_code(400);
            die(json_encode(['success' => false, 'error' => 'Ungültiger Typ']));
    }

    error_log("remove-featured.php: Success = " . ($success ? 'true' : 'false'));

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Erfolgreich entfernt']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Fehler beim Entfernen', 'db_error' => $db->error]);
    }

} catch (Exception $e) {
    error_log("remove-featured.php: Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
