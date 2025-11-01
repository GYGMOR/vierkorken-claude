<?php
// api/klara-products-extended.php - Erweiterte Klara-Produkt-Informationen API
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
            // Alle Klara-Artikel mit erweiterten Daten holen
            $articles = klara_get_articles();

            // Erweiterte Daten aus Datenbank mergen
            foreach ($articles as &$article) {
                $extended = get_klara_extended_data($article['id']);
                if ($extended) {
                    $article = array_merge($article, $extended);
                }
            }

            echo json_encode(['success' => true, 'data' => $articles]);
            break;

        case 'get_one':
            $klara_id = $_GET['id'] ?? '';
            if (empty($klara_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID fehlt']);
                break;
            }

            // Klara-Artikel holen
            $articles = klara_get_articles();
            $article = null;
            foreach ($articles as $a) {
                if ($a['id'] === $klara_id) {
                    $article = $a;
                    break;
                }
            }

            if (!$article) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Artikel nicht gefunden']);
                break;
            }

            // Erweiterte Daten mergen
            $extended = get_klara_extended_data($klara_id);
            if ($extended) {
                $article = array_merge($article, $extended);
            }

            echo json_encode(['success' => true, 'data' => $article]);
            break;

        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $klara_id = $data['klara_article_id'] ?? '';

            if (empty($klara_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Klara-ID fehlt']);
                break;
            }

            if (update_klara_extended_data($klara_id, $data)) {
                echo json_encode(['success' => true, 'message' => 'Daten aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
            }
            break;

        case 'delete_extended':
            $klara_id = $_POST['id'] ?? '';
            if (delete_klara_extended_data($klara_id)) {
                echo json_encode(['success' => true, 'message' => 'Erweiterte Daten gelöscht']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen']);
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
