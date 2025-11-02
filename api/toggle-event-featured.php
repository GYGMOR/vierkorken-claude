<?php
// api/toggle-event-featured.php - Toggle Featured Status für Events

header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin-Check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Keine Berechtigung']));
}

$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$is_featured = isset($_POST['is_featured']) ? (int)$_POST['is_featured'] : 0;

if ($event_id <= 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Ungültige Event-ID']));
}

try {
    $success = set_event_featured($event_id, $is_featured);

    if ($success) {
        echo json_encode(['success' => true, 'message' => $is_featured ? 'Als Neuheit markiert' : 'Von Neuheiten entfernt']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
