<?php
// api/orders.php - Order Processing and Management
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? '';

// Create tables if they don't exist
$db->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT DEFAULT NULL,
    guest_email VARCHAR(255) DEFAULT NULL,
    delivery_method ENUM('delivery', 'pickup') NOT NULL DEFAULT 'delivery',
    delivery_first_name VARCHAR(100) NOT NULL,
    delivery_last_name VARCHAR(100) NOT NULL,
    delivery_street VARCHAR(255) DEFAULT NULL,
    delivery_postal_code VARCHAR(20) DEFAULT NULL,
    delivery_city VARCHAR(100) DEFAULT NULL,
    delivery_phone VARCHAR(50) NOT NULL,
    delivery_email VARCHAR(255) NOT NULL,
    payment_method ENUM('card', 'twint', 'cash') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_transaction_id VARCHAR(255) DEFAULT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    coupon_code VARCHAR(50) DEFAULT NULL,
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_user_orders (user_id, created_at),
    INDEX idx_order_status (order_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_type ENUM('wine', 'event') NOT NULL DEFAULT 'wine',
    wine_id INT DEFAULT NULL,
    event_id INT DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    customer_data TEXT DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (wine_id) REFERENCES wines(id) ON DELETE SET NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL,
    INDEX idx_order_items (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS order_sequence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    last_sequence INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Helper function to generate unique order number
function generate_order_number() {
    global $db;
    $today = date('Y-m-d');

    // Lock row for update
    $db->query("START TRANSACTION");

    $result = $db->query("SELECT last_sequence FROM order_sequence WHERE date = '$today' FOR UPDATE");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sequence = $row['last_sequence'] + 1;
        $db->query("UPDATE order_sequence SET last_sequence = $sequence WHERE date = '$today'");
    } else {
        $sequence = 1;
        $db->query("INSERT INTO order_sequence (date, last_sequence) VALUES ('$today', 1)");
    }

    $db->query("COMMIT");

    // Format: VK-YYYYMMDD-XXXX
    return 'VK-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
}

try {
    switch ($action) {
        case 'create':
            // Create new order (called from checkout)
            $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            // Get POST data
            $cart_items = json_decode($_POST['cart_items'] ?? '[]', true);
            $delivery_method = $_POST['delivery_method'] ?? 'delivery';
            $payment_method = $_POST['payment_method'] ?? 'card';

            // Address data
            $first_name = $db->real_escape_string($_POST['first_name'] ?? '');
            $last_name = $db->real_escape_string($_POST['last_name'] ?? '');
            $street = $db->real_escape_string($_POST['street'] ?? '');
            $postal_code = $db->real_escape_string($_POST['postal_code'] ?? '');
            $city = $db->real_escape_string($_POST['city'] ?? '');
            $phone = $db->real_escape_string($_POST['phone'] ?? '');
            $email = $db->real_escape_string($_POST['email'] ?? '');

            // Pricing
            $subtotal = floatval($_POST['subtotal'] ?? 0);
            $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
            $discount_amount = floatval($_POST['discount_amount'] ?? 0);
            $total_amount = floatval($_POST['total_amount'] ?? 0);
            $coupon_code = isset($_POST['coupon_code']) ? $db->real_escape_string($_POST['coupon_code']) : null;

            // Notes
            $notes = isset($_POST['notes']) ? $db->real_escape_string($_POST['notes']) : null;

            // Validation
            if (empty($cart_items)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Warenkorb ist leer']);
                break;
            }

            if (empty($first_name) || empty($last_name) || empty($phone) || empty($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Fehlende Pflichtfelder']);
                break;
            }

            if ($delivery_method === 'delivery' && (empty($street) || empty($postal_code) || empty($city))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Lieferadresse unvollständig']);
                break;
            }

            // Generate order number
            $order_number = generate_order_number();

            // Guest email tracking
            $guest_email = $user_id ? 'NULL' : "'$email'";
            $user_id_sql = $user_id ? $user_id : 'NULL';

            // For pickup, address fields can be NULL
            if ($delivery_method === 'pickup') {
                $street = $postal_code = $city = '';
                $shipping_cost = 0;
            }

            $coupon_sql = $coupon_code ? "'$coupon_code'" : 'NULL';
            $notes_sql = $notes ? "'$notes'" : 'NULL';

            // Insert order
            $order_query = "INSERT INTO orders (
                order_number, user_id, guest_email,
                delivery_method, delivery_first_name, delivery_last_name,
                delivery_street, delivery_postal_code, delivery_city,
                delivery_phone, delivery_email,
                payment_method, payment_status,
                subtotal, shipping_cost, discount_amount, total_amount,
                coupon_code, order_status, notes
            ) VALUES (
                '$order_number', $user_id_sql, $guest_email,
                '$delivery_method', '$first_name', '$last_name',
                '$street', '$postal_code', '$city',
                '$phone', '$email',
                '$payment_method', 'pending',
                $subtotal, $shipping_cost, $discount_amount, $total_amount,
                $coupon_sql, 'pending', $notes_sql
            )";

            if (!$db->query($order_query)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen der Bestellung: ' . $db->error]);
                break;
            }

            $order_id = $db->insert_id;

            // Insert order items
            foreach ($cart_items as $item) {
                $item_type = $item['type'] ?? 'wine';
                $product_name = $db->real_escape_string($item['name']);
                $quantity = (int)$item['quantity'];
                $unit_price = floatval($item['price']);
                $total_price = $unit_price * $quantity;

                $wine_id = 'NULL';
                $event_id = 'NULL';
                $customer_data_sql = 'NULL';

                if ($item_type === 'wine') {
                    $wine_id = (int)$item['id'];
                } elseif ($item_type === 'event') {
                    $event_id = (int)$item['id'];
                    if (isset($item['customerData'])) {
                        $customer_data = $db->real_escape_string(json_encode($item['customerData']));
                        $customer_data_sql = "'$customer_data'";
                    }
                }

                $item_query = "INSERT INTO order_items (
                    order_id, item_type, wine_id, event_id,
                    product_name, quantity, unit_price, total_price, customer_data
                ) VALUES (
                    $order_id, '$item_type', $wine_id, $event_id,
                    '$product_name', $quantity, $unit_price, $total_price, $customer_data_sql
                )";

                $db->query($item_query);
            }

            // Update coupon usage if used
            if ($coupon_code) {
                $db->query("UPDATE coupons SET used_count = used_count + 1 WHERE code = '$coupon_code'");

                // Track coupon usage
                $coupon_result = $db->query("SELECT id FROM coupons WHERE code = '$coupon_code'");
                if ($coupon_result && $coupon_result->num_rows > 0) {
                    $coupon_id = $coupon_result->fetch_assoc()['id'];
                    $user_id_for_coupon = $user_id ?? 'NULL';
                    $db->query("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount)
                                VALUES ($coupon_id, $user_id_for_coupon, $order_id, $discount_amount)");
                }
            }

            // Send confirmation email
            send_order_confirmation_email($order_id);

            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'order_number' => $order_number,
                'message' => 'Bestellung erfolgreich erstellt'
            ]);
            break;

        case 'get_by_number':
            // Get order by order number
            $order_number = $db->real_escape_string($_GET['order_number'] ?? '');

            if (empty($order_number)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Bestellnummer fehlt']);
                break;
            }

            $result = $db->query("SELECT * FROM orders WHERE order_number = '$order_number'");

            if (!$result || $result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Bestellung nicht gefunden']);
                break;
            }

            $order = $result->fetch_assoc();

            // Get order items
            $items_result = $db->query("SELECT * FROM order_items WHERE order_id = " . $order['id']);
            $order['items'] = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

            echo json_encode(['success' => true, 'order' => $order]);
            break;

        case 'get_user_orders':
            // Get all orders for logged-in user
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Nicht eingeloggt']);
                break;
            }

            $user_id = (int)$_SESSION['user_id'];
            $result = $db->query("
                SELECT o.*, COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = $user_id
                GROUP BY o.id
                ORDER BY o.created_at DESC
            ");

            $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            echo json_encode(['success' => true, 'orders' => $orders]);
            break;

        case 'get_order_details':
            // Get order details with items
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Nicht eingeloggt']);
                break;
            }

            $order_id = (int)$_GET['id'];
            $user_id = (int)$_SESSION['user_id'];

            // Verify ownership
            $result = $db->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");

            if (!$result || $result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Bestellung nicht gefunden']);
                break;
            }

            $order = $result->fetch_assoc();

            // Get items
            $items_result = $db->query("SELECT * FROM order_items WHERE order_id = $order_id");
            $order['items'] = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

            echo json_encode(['success' => true, 'order' => $order]);
            break;

        case 'update_status':
            // Admin only - update order status
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $order_id = (int)$_POST['id'];
            $new_status = $db->real_escape_string($_POST['status'] ?? '');

            if ($db->query("UPDATE orders SET order_status = '$new_status' WHERE id = $order_id")) {
                echo json_encode(['success' => true, 'message' => 'Status aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
            }
            break;

        case 'update_payment_status':
            // Update payment status (called after payment gateway callback)
            // This would typically be authenticated via webhook signature
            $order_id = (int)$_POST['order_id'];
            $payment_status = $db->real_escape_string($_POST['payment_status'] ?? 'pending');
            $transaction_id = $db->real_escape_string($_POST['transaction_id'] ?? '');

            $transaction_sql = $transaction_id ? "'$transaction_id'" : 'NULL';

            if ($db->query("UPDATE orders SET payment_status = '$payment_status', payment_transaction_id = $transaction_sql WHERE id = $order_id")) {
                // If payment completed, update order status
                if ($payment_status === 'completed') {
                    $db->query("UPDATE orders SET order_status = 'processing' WHERE id = $order_id");
                }
                echo json_encode(['success' => true, 'message' => 'Zahlungsstatus aktualisiert']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Aktualisieren']);
            }
            break;

        case 'get_all_orders':
            // Admin only - get all orders
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $status_filter = isset($_GET['status']) ? $db->real_escape_string($_GET['status']) : null;
            $where = $status_filter ? "WHERE order_status = '$status_filter'" : "";

            $result = $db->query("
                SELECT o.*, COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                $where
                GROUP BY o.id
                ORDER BY o.created_at DESC
            ");

            $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            echo json_encode(['success' => true, 'orders' => $orders]);
            break;

        case 'get_order_details_admin':
            // Admin only - get full order details
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $order_id = (int)($_GET['order_id'] ?? 0);
            if ($order_id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Ungültige Bestellnummer']);
                break;
            }

            // Get order details
            $order_result = $db->query("SELECT * FROM orders WHERE id = $order_id");
            if (!$order_result || $order_result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Bestellung nicht gefunden']);
                break;
            }
            $order = $order_result->fetch_assoc();

            // Get order items with product details
            $items_result = $db->query("
                SELECT oi.*,
                       oi.product_name as name,
                       w.producer,
                       w.vintage,
                       w.region,
                       c.name as category
                FROM order_items oi
                LEFT JOIN wines w ON oi.wine_id = w.id AND oi.item_type = 'wine'
                LEFT JOIN categories c ON w.category_id = c.id
                WHERE oi.order_id = $order_id
            ");

            $items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items
            ]);
            break;

        case 'delete_order':
            // Admin only - delete/cancel order
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
                break;
            }

            $order_id = (int)($_POST['order_id'] ?? 0);
            if ($order_id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Ungültige Bestellnummer']);
                break;
            }

            // Check if order exists
            $order_check = $db->query("SELECT id, order_status FROM orders WHERE id = $order_id");
            if (!$order_check || $order_check->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Bestellung nicht gefunden']);
                break;
            }

            // Delete order (cascade will delete order_items)
            if ($db->query("DELETE FROM orders WHERE id = $order_id")) {
                echo json_encode(['success' => true, 'message' => 'Bestellung gelöscht']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen: ' . $db->error]);
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

// Helper function to send order confirmation email
function send_order_confirmation_email($order_id) {
    global $db;

    $result = $db->query("SELECT * FROM orders WHERE id = $order_id");
    if (!$result || $result->num_rows === 0) return false;

    $order = $result->fetch_assoc();
    $items_result = $db->query("SELECT * FROM order_items WHERE order_id = $order_id");
    $items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

    $to = $order['delivery_email'];
    $subject = "Bestellbestätigung - " . $order['order_number'];

    // Build email HTML
    $items_html = '';
    foreach ($items as $item) {
        $items_html .= "<tr>
            <td>{$item['product_name']}</td>
            <td>{$item['quantity']}</td>
            <td>CHF " . number_format($item['unit_price'], 2) . "</td>
            <td>CHF " . number_format($item['total_price'], 2) . "</td>
        </tr>";
    }

    $delivery_info = $order['delivery_method'] === 'delivery'
        ? "{$order['delivery_street']}<br>{$order['delivery_postal_code']} {$order['delivery_city']}"
        : "Abholung in der Filiale<br>Vier Korken Weinlounge, Zürich";

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #722c2c; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #722c2c; color: white; }
            .total { font-size: 1.2em; font-weight: bold; color: #722c2c; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Vielen Dank für Ihre Bestellung!</h1>
            </div>
            <div class='content'>
                <p>Hallo {$order['delivery_first_name']} {$order['delivery_last_name']},</p>
                <p>Wir haben Ihre Bestellung erhalten und bearbeiten sie umgehend.</p>

                <h2>Bestellnummer: {$order['order_number']}</h2>

                <h3>Bestellte Artikel:</h3>
                <table>
                    <tr>
                        <th>Produkt</th>
                        <th>Menge</th>
                        <th>Preis</th>
                        <th>Gesamt</th>
                    </tr>
                    $items_html
                </table>

                <p><strong>Zwischensumme:</strong> CHF " . number_format($order['subtotal'], 2) . "</p>
                <p><strong>Versandkosten:</strong> CHF " . number_format($order['shipping_cost'], 2) . "</p>";

    if ($order['discount_amount'] > 0) {
        $message .= "<p><strong>Rabatt ({$order['coupon_code']}):</strong> -CHF " . number_format($order['discount_amount'], 2) . "</p>";
    }

    $message .= "
                <p class='total'><strong>Gesamtbetrag:</strong> CHF " . number_format($order['total_amount'], 2) . "</p>

                <h3>Lieferinformation:</h3>
                <p>$delivery_info</p>

                <h3>Zahlungsmethode:</h3>
                <p>" . get_payment_method_name($order['payment_method']) . "</p>

                <p>Bei Fragen zu Ihrer Bestellung kontaktieren Sie uns bitte unter info@vierkorken.ch</p>

                <p>Mit freundlichen Grüssen,<br>Ihr Vier Korken Team</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Vier Korken <noreply@vierkorken.ch>\r\n";

    // Send email
    return mail($to, $subject, $message, $headers);
}

function get_payment_method_name($method) {
    $methods = [
        'card' => 'Kreditkarte / Debitkarte',
        'twint' => 'TWINT',
        'cash' => 'Barzahlung bei Abholung'
    ];
    return $methods[$method] ?? $method;
}
?>
