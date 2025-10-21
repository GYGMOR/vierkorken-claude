<?php
// api/user-addresses.php - User Address Management for Checkout
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

// Create user_addresses table if it doesn't exist
$db->query("CREATE TABLE IF NOT EXISTS user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    street VARCHAR(255) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_default (user_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

try {
    switch ($action) {
        case 'get_all':
            // Get all addresses for the logged-in user
            $result = $db->query("
                SELECT * FROM user_addresses
                WHERE user_id = $user_id
                ORDER BY is_default DESC, created_at DESC
            ");
            $addresses = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            echo json_encode(['success' => true, 'addresses' => $addresses]);
            break;

        case 'get_one':
            $address_id = (int)$_GET['id'];
            $result = $db->query("
                SELECT * FROM user_addresses
                WHERE id = $address_id AND user_id = $user_id
            ");

            if ($result && $result->num_rows > 0) {
                $address = $result->fetch_assoc();
                echo json_encode(['success' => true, 'address' => $address]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Adresse nicht gefunden']);
            }
            break;

        case 'create':
            $first_name = $db->real_escape_string($_POST['first_name'] ?? '');
            $last_name = $db->real_escape_string($_POST['last_name'] ?? '');
            $street = $db->real_escape_string($_POST['street'] ?? '');
            $postal_code = $db->real_escape_string($_POST['postal_code'] ?? '');
            $city = $db->real_escape_string($_POST['city'] ?? '');
            $phone = $db->real_escape_string($_POST['phone'] ?? '');
            $is_default = isset($_POST['is_default']) ? 1 : 0;

            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($street) ||
                empty($postal_code) || empty($city) || empty($phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Alle Felder sind erforderlich']);
                break;
            }

            // If setting as default, unset other defaults
            if ($is_default) {
                $db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id");
            }

            // Insert new address
            $query = "INSERT INTO user_addresses
                (user_id, first_name, last_name, street, postal_code, city, phone, is_default)
                VALUES ($user_id, '$first_name', '$last_name', '$street', '$postal_code', '$city', '$phone', $is_default)";

            if ($db->query($query)) {
                $new_id = $db->insert_id;
                echo json_encode(['success' => true, 'id' => $new_id, 'message' => 'Adresse gespeichert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Speichern']);
            }
            break;

        case 'update':
            $address_id = (int)$_POST['id'];
            $first_name = $db->real_escape_string($_POST['first_name'] ?? '');
            $last_name = $db->real_escape_string($_POST['last_name'] ?? '');
            $street = $db->real_escape_string($_POST['street'] ?? '');
            $postal_code = $db->real_escape_string($_POST['postal_code'] ?? '');
            $city = $db->real_escape_string($_POST['city'] ?? '');
            $phone = $db->real_escape_string($_POST['phone'] ?? '');
            $is_default = isset($_POST['is_default']) ? 1 : 0;

            // Validate ownership
            $check = $db->query("SELECT id FROM user_addresses WHERE id = $address_id AND user_id = $user_id");
            if (!$check || $check->num_rows === 0) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            // If setting as default, unset other defaults
            if ($is_default) {
                $db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id");
            }

            // Update address
            $query = "UPDATE user_addresses SET
                first_name = '$first_name',
                last_name = '$last_name',
                street = '$street',
                postal_code = '$postal_code',
                city = '$city',
                phone = '$phone',
                is_default = $is_default
                WHERE id = $address_id AND user_id = $user_id";

            if ($db->query($query)) {
                echo json_encode(['success' => true, 'message' => 'Adresse aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
            }
            break;

        case 'delete':
            $address_id = (int)$_POST['id'];

            // Validate ownership
            $check = $db->query("SELECT id FROM user_addresses WHERE id = $address_id AND user_id = $user_id");
            if (!$check || $check->num_rows === 0) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            if ($db->query("DELETE FROM user_addresses WHERE id = $address_id AND user_id = $user_id")) {
                echo json_encode(['success' => true, 'message' => 'Adresse gelöscht']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen']);
            }
            break;

        case 'set_default':
            $address_id = (int)$_POST['id'];

            // Validate ownership
            $check = $db->query("SELECT id FROM user_addresses WHERE id = $address_id AND user_id = $user_id");
            if (!$check || $check->num_rows === 0) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            // Unset all defaults
            $db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id");

            // Set new default
            if ($db->query("UPDATE user_addresses SET is_default = 1 WHERE id = $address_id AND user_id = $user_id")) {
                echo json_encode(['success' => true, 'message' => 'Standardadresse aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
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
