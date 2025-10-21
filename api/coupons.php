<?php
// api/coupons.php - Coupon Validation and Management
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? '';

// Create tables if they don't exist
$db->query("CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10, 2) NOT NULL,
    min_order_amount DECIMAL(10, 2) DEFAULT 0.00,
    max_discount_amount DECIMAL(10, 2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    valid_from DATETIME DEFAULT NULL,
    valid_until DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code_active (code, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    order_id INT NOT NULL,
    discount_amount DECIMAL(10, 2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_coupon (user_id, coupon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

try {
    switch ($action) {
        case 'validate':
            // Validate coupon code
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $subtotal = floatval($_POST['subtotal'] ?? 0);
            $user_id = $_SESSION['user_id'] ?? null;

            if (empty($code)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Bitte gib einen Code ein']);
                break;
            }

            // Fetch coupon
            $code_escaped = $db->real_escape_string($code);
            $result = $db->query("SELECT * FROM coupons WHERE code = '$code_escaped' AND is_active = 1");

            if (!$result || $result->num_rows === 0) {
                echo json_encode(['success' => false, 'error' => 'Ungültiger Gutscheincode']);
                break;
            }

            $coupon = $result->fetch_assoc();

            // Check validity dates
            $now = date('Y-m-d H:i:s');
            if ($coupon['valid_from'] && $now < $coupon['valid_from']) {
                echo json_encode(['success' => false, 'error' => 'Dieser Gutschein ist noch nicht gültig']);
                break;
            }
            if ($coupon['valid_until'] && $now > $coupon['valid_until']) {
                echo json_encode(['success' => false, 'error' => 'Dieser Gutschein ist abgelaufen']);
                break;
            }

            // Check usage limit
            if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
                echo json_encode(['success' => false, 'error' => 'Dieser Gutschein wurde bereits vollständig eingelöst']);
                break;
            }

            // Check minimum order amount
            if ($subtotal < $coupon['min_order_amount']) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Mindestbestellwert von CHF ' . number_format($coupon['min_order_amount'], 2) . ' nicht erreicht'
                ]);
                break;
            }

            // Calculate discount
            $discount = 0;
            if ($coupon['discount_type'] === 'percentage') {
                $discount = ($subtotal * $coupon['discount_value']) / 100;
                // Apply max discount cap if set
                if ($coupon['max_discount_amount'] && $discount > $coupon['max_discount_amount']) {
                    $discount = $coupon['max_discount_amount'];
                }
            } else {
                // Fixed amount
                $discount = $coupon['discount_value'];
            }

            // Don't allow discount to exceed subtotal
            if ($discount > $subtotal) {
                $discount = $subtotal;
            }

            echo json_encode([
                'success' => true,
                'coupon' => [
                    'id' => $coupon['id'],
                    'code' => $coupon['code'],
                    'description' => $coupon['description'],
                    'discount_type' => $coupon['discount_type'],
                    'discount_value' => $coupon['discount_value'],
                    'discount_amount' => round($discount, 2)
                ]
            ]);
            break;

        case 'get_all':
            // Admin only - get all coupons
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $result = $db->query("SELECT * FROM coupons ORDER BY created_at DESC");
            $coupons = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            echo json_encode(['success' => true, 'coupons' => $coupons]);
            break;

        case 'create':
            // Admin only - create new coupon
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $code = strtoupper(trim($db->real_escape_string($_POST['code'] ?? '')));
            $description = $db->real_escape_string($_POST['description'] ?? '');
            $discount_type = $_POST['discount_type'] ?? 'percentage';
            $discount_value = floatval($_POST['discount_value'] ?? 0);
            $min_order_amount = floatval($_POST['min_order_amount'] ?? 0);
            $max_discount_amount = !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : 'NULL';
            $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : 'NULL';
            $valid_from = !empty($_POST['valid_from']) ? "'" . $db->real_escape_string($_POST['valid_from']) . "'" : 'NULL';
            $valid_until = !empty($_POST['valid_until']) ? "'" . $db->real_escape_string($_POST['valid_until']) . "'" : 'NULL';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($code)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Code ist erforderlich']);
                break;
            }

            $query = "INSERT INTO coupons
                (code, description, discount_type, discount_value, min_order_amount,
                 max_discount_amount, usage_limit, valid_from, valid_until, is_active)
                VALUES ('$code', '$description', '$discount_type', $discount_value, $min_order_amount,
                        $max_discount_amount, $usage_limit, $valid_from, $valid_until, $is_active)";

            if ($db->query($query)) {
                echo json_encode(['success' => true, 'id' => $db->insert_id, 'message' => 'Gutschein erstellt']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen: ' . $db->error]);
            }
            break;

        case 'update':
            // Admin only - update coupon
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $id = intval($_POST['id'] ?? 0);
            $description = $db->real_escape_string($_POST['description'] ?? '');
            $discount_value = floatval($_POST['discount_value'] ?? 0);
            $min_order_amount = floatval($_POST['min_order_amount'] ?? 0);
            $max_discount_amount = !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : 'NULL';
            $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : 'NULL';
            $valid_from = !empty($_POST['valid_from']) ? "'" . $db->real_escape_string($_POST['valid_from']) . "'" : 'NULL';
            $valid_until = !empty($_POST['valid_until']) ? "'" . $db->real_escape_string($_POST['valid_until']) . "'" : 'NULL';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $query = "UPDATE coupons SET
                description = '$description',
                discount_value = $discount_value,
                min_order_amount = $min_order_amount,
                max_discount_amount = $max_discount_amount,
                usage_limit = $usage_limit,
                valid_from = $valid_from,
                valid_until = $valid_until,
                is_active = $is_active
                WHERE id = $id";

            if ($db->query($query)) {
                echo json_encode(['success' => true, 'message' => 'Gutschein aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
            }
            break;

        case 'delete':
            // Admin only - delete coupon
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $id = intval($_POST['id'] ?? 0);
            if ($db->query("DELETE FROM coupons WHERE id = $id")) {
                echo json_encode(['success' => true, 'message' => 'Gutschein gelöscht']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen']);
            }
            break;

        case 'toggle_active':
            // Admin only - toggle active status
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $id = intval($_POST['id'] ?? 0);
            if ($db->query("UPDATE coupons SET is_active = NOT is_active WHERE id = $id")) {
                echo json_encode(['success' => true, 'message' => 'Status aktualisiert']);
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
