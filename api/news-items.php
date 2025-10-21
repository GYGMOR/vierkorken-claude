<?php
// api/news-items.php - News/Neuheiten Items Management API
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_all':
            $active_only = isset($_GET['active_only']) ? (bool)$_GET['active_only'] : false;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $items = get_all_news_items($limit, $active_only);
            echo json_encode(['success' => true, 'data' => $items]);
            break;

        case 'get_one':
            $id = (int)$_GET['id'];
            $item = get_news_item_by_id($id);
            if ($item) {
                echo json_encode(['success' => true, 'data' => $item]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'News-Item nicht gefunden']);
            }
            break;

        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['title']) || empty($data['type'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Titel und Typ erforderlich']);
                break;
            }

            if (create_news_item($data)) {
                echo json_encode(['success' => true, 'message' => 'News-Item erstellt']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen']);
            }
            break;

        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];

            if (update_news_item($id, $data)) {
                echo json_encode(['success' => true, 'message' => 'News-Item aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
            }
            break;

        case 'delete':
            $id = (int)$_POST['id'];
            if (delete_news_item($id)) {
                echo json_encode(['success' => true, 'message' => 'News-Item gelöscht']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen']);
            }
            break;

        case 'toggle_active':
            $id = (int)$_POST['id'];
            $item = get_news_item_by_id($id);
            if ($item) {
                $new_status = $item['is_active'] ? 0 : 1;
                update_news_item($id, array_merge($item, ['is_active' => $new_status]));
                echo json_encode(['success' => true, 'is_active' => $new_status]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'News-Item nicht gefunden']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ungültige Aktion']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
