<?php
// api/events.php - Events Management API
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get_all':
            $active_only = isset($_GET['active_only']) ? (bool)$_GET['active_only'] : true;
            $future_only = isset($_GET['future_only']) ? (bool)$_GET['future_only'] : false;
            $events = get_all_events($active_only, $future_only);
            echo json_encode(['success' => true, 'data' => $events]);
            break;

        case 'get_one':
            $id = (int)$_GET['id'];
            $event = get_event_by_id($id);
            if ($event) {
                echo json_encode(['success' => true, 'data' => $event]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Event nicht gefunden']);
            }
            break;

        case 'create':
            // Admin only
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['name']) || empty($data['event_date'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Name und Datum erforderlich']);
                break;
            }

            if (create_event($data)) {
                echo json_encode(['success' => true, 'message' => 'Event erstellt']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen']);
            }
            break;

        case 'update':
            // Admin only
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];

            if (update_event($id, $data)) {
                echo json_encode(['success' => true, 'message' => 'Event aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
            }
            break;

        case 'delete':
            // Admin only
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $id = (int)$_POST['id'];
            if (delete_event($id)) {
                echo json_encode(['success' => true, 'message' => 'Event gelöscht']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen']);
            }
            break;

        case 'check_availability':
            $id = (int)$_GET['id'];
            $quantity = (int)$_GET['quantity'];
            $available = get_available_tickets($id);

            echo json_encode([
                'success' => true,
                'available' => $available,
                'can_book' => $available >= $quantity
            ]);
            break;

        case 'book':
            $data = json_decode(file_get_contents('php://input'), true);
            $event_id = (int)$data['event_id'];
            $quantity = (int)$data['quantity'];
            $user_data = [
                'name' => $data['customer_name'] ?? '',
                'email' => $data['customer_email'] ?? '',
                'phone' => $data['customer_phone'] ?? ''
            ];

            $result = book_event_tickets($event_id, $quantity, $user_data);

            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
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
