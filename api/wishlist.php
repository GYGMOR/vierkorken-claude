<?php
// api/wishlist.php - Wishlist/Merkliste Management
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Nicht eingeloggt']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

// Create wishlist table if it doesn't exist
$db->query("CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    wine_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, wine_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wine_id) REFERENCES wines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

try {
    switch ($action) {
        case 'toggle':
            $wine_id = (int)$_POST['wine_id'];

            // Check if already in wishlist
            $check = $db->query("SELECT id FROM wishlist WHERE user_id = $user_id AND wine_id = $wine_id");

            if ($check->num_rows > 0) {
                // Remove from wishlist
                $db->query("DELETE FROM wishlist WHERE user_id = $user_id AND wine_id = $wine_id");
                echo json_encode(['success' => true, 'added' => false]);
            } else {
                // Add to wishlist
                $db->query("INSERT INTO wishlist (user_id, wine_id) VALUES ($user_id, $wine_id)");
                echo json_encode(['success' => true, 'added' => true]);
            }
            break;

        case 'get_all':
            $result = $db->query("SELECT wine_id FROM wishlist WHERE user_id = $user_id");
            $wine_ids = [];
            while ($row = $result->fetch_assoc()) {
                $wine_ids[] = (int)$row['wine_id'];
            }
            echo json_encode(['success' => true, 'wine_ids' => $wine_ids]);
            break;

        case 'get_wines':
            $result = $db->query("
                SELECT w.* FROM wines w
                JOIN wishlist wl ON w.id = wl.wine_id
                WHERE wl.user_id = $user_id
                ORDER BY wl.created_at DESC
            ");
            $wines = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'wines' => $wines]);
            break;

        case 'remove':
            $wine_id = (int)$_POST['wine_id'];
            $db->query("DELETE FROM wishlist WHERE user_id = $user_id AND wine_id = $wine_id");
            echo json_encode(['success' => true]);
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
