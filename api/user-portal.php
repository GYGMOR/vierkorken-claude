<?php
// api/user-portal.php - FINAL VERSION - ALLE FIXES DRIN

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';
require_once '../includes/functions.php';

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Authentifizierung erforderlich']));
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// ============================================
// RATINGS - GET USER RATINGS
// ============================================
if ($action === 'get_ratings' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_login();
    
    $result = $db->query("
        SELECT r.id, r.wine_id, r.rating, r.review_text, r.created_at,
               w.name, w.producer, c.name as category
        FROM wine_ratings r
        JOIN wines w ON r.wine_id = w.id
        JOIN categories c ON w.category_id = c.id
        WHERE r.user_id = $user_id
        ORDER BY r.created_at DESC
    ");
    
    $ratings = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $ratings[] = [
                'id' => (int)$row['id'],
                'wine_id' => (int)$row['wine_id'],
                'wine_name' => $row['name'],
                'producer' => $row['producer'],
                'category' => $row['category'],
                'rating' => (int)$row['rating'],
                'review' => $row['review_text'],
                'date' => date('d.m.Y H:i', strtotime($row['created_at']))
            ];
        }
    }
    
    echo json_encode(['success' => true, 'ratings' => $ratings, 'count' => count($ratings)]);
    exit;
}

// ============================================
// RATINGS - CREATE/UPDATE
// ============================================
elseif ($action === 'rate_wine' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $wine_id = (int)($_POST['wine_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $review = $db->real_escape_string(trim($_POST['review'] ?? ''));
    
    if ($wine_id <= 0 || $rating < 1 || $rating > 5) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'Ungültige Eingabedaten']));
    }
    
    $existing = $db->query("SELECT id FROM wine_ratings WHERE user_id = $user_id AND wine_id = $wine_id");
    
    if ($existing && $existing->num_rows > 0) {
        $sql = "UPDATE wine_ratings SET rating = $rating, review_text = '$review', updated_at = NOW() WHERE user_id = $user_id AND wine_id = $wine_id";
        $msg = 'Bewertung aktualisiert';
    } else {
        $sql = "INSERT INTO wine_ratings (wine_id, user_id, rating, review_text) VALUES ($wine_id, $user_id, $rating, '$review')";
        $msg = 'Bewertung erstellt';
    }
    
    if ($db->query($sql)) {
        $avg = $db->query("SELECT ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as count FROM wine_ratings WHERE wine_id = $wine_id");
        $data = $avg->fetch_assoc();
        
        $db->query("UPDATE wines SET avg_rating = {$data['avg_rating']}, rating_count = {$data['count']} WHERE id = $wine_id");
        
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// RATINGS - DELETE
// ============================================
elseif ($action === 'delete_rating' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $rating_id = (int)($_POST['rating_id'] ?? 0);
    $rating = $db->query("SELECT wine_id, user_id FROM wine_ratings WHERE id = $rating_id");
    
    if (!$rating || $rating->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Bewertung nicht gefunden']);
        exit;
    }
    
    $r = $rating->fetch_assoc();
    if ($r['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        exit;
    }
    
    $wine_id = $r['wine_id'];
    
    if ($db->query("DELETE FROM wine_ratings WHERE id = $rating_id")) {
        $avg = $db->query("SELECT ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as count FROM wine_ratings WHERE wine_id = $wine_id");
        $data = $avg->fetch_assoc();
        
        $db->query("UPDATE wines SET avg_rating = " . ($data['count'] > 0 ? $data['avg_rating'] : 'NULL') . ", rating_count = {$data['count']} WHERE id = $wine_id");
        
        echo json_encode(['success' => true, 'message' => 'Bewertung gelöscht']);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// FAVORITES - GET
// ============================================
elseif ($action === 'get_favorites' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_login();
    
    $result = $db->query("
        SELECT w.* FROM user_favorites uf
        JOIN wines w ON uf.wine_id = w.id
        WHERE uf.user_id = $user_id
        ORDER BY uf.created_at DESC
    ");
    
    $favorites = [];
    if ($result) {
        $favorites = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    echo json_encode(['success' => true, 'favorites' => $favorites, 'count' => count($favorites)]);
    exit;
}

// ============================================
// FAVORITES - ADD
// ============================================
elseif ($action === 'add_favorite' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $wine_id = (int)($_POST['wine_id'] ?? 0);
    
    if ($wine_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Wein-ID erforderlich']);
        exit;
    }
    
    $check = $db->query("SELECT id FROM user_favorites WHERE user_id = $user_id AND wine_id = $wine_id");
    
    if ($check && $check->num_rows > 0) {
        http_response_code(409);
        die(json_encode(['success' => false, 'error' => 'Dieser Wein ist bereits in deinen Favoriten', 'code' => 'DUPLICATE']));
    }
    
    if ($db->query("INSERT INTO user_favorites (user_id, wine_id) VALUES ($user_id, $wine_id)")) {
        $db->query("INSERT INTO user_activity (user_id, wine_id, action_type) VALUES ($user_id, $wine_id, 'added_to_favorites')");
        echo json_encode(['success' => true, 'message' => 'Zu Favoriten hinzugefügt']);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// FAVORITES - REMOVE
// ============================================
elseif ($action === 'remove_favorite' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $wine_id = (int)($_POST['wine_id'] ?? 0);
    
    if ($db->query("DELETE FROM user_favorites WHERE user_id = $user_id AND wine_id = $wine_id")) {
        echo json_encode(['success' => true, 'message' => 'Aus Favoriten entfernt']);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// ORDERS - GET LIST
// ============================================
elseif ($action === 'get_orders' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_login();

    $result = $db->query("
        SELECT o.id, o.order_number, o.total_amount, o.order_status, o.created_at, o.delivery_city,
               COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = $user_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");

    $orders = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = [
                'id' => (int)$row['id'],
                'number' => $row['order_number'],
                'total' => (float)$row['total_amount'],
                'status' => $row['order_status'],
                'items_count' => (int)$row['item_count'],
                'city' => $row['delivery_city'],
                'date' => date('d.m.Y', strtotime($row['created_at'])),
                'time' => date('H:i', strtotime($row['created_at']))
            ];
        }
    }

    echo json_encode(['success' => true, 'orders' => $orders, 'count' => count($orders)]);
    exit;
}

// ============================================
// ORDERS - GET DETAIL
// ============================================
elseif ($action === 'get_order_details' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_login();
    
    $order_id = (int)($_GET['order_id'] ?? 0);
    
    $order = $db->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
    
    if (!$order || $order->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Bestellung nicht gefunden']);
        exit;
    }
    
    $o = $order->fetch_assoc();
    
    $items = $db->query("
        SELECT oi.*, oi.product_name as name, oi.quantity, oi.unit_price as price_at_purchase, oi.total_price as subtotal,
               w.producer, c.name as category
        FROM order_items oi
        LEFT JOIN wines w ON oi.wine_id = w.id AND oi.item_type = 'wine'
        LEFT JOIN categories c ON w.category_id = c.id
        WHERE oi.order_id = $order_id
    ");
    
    $item_list = [];
    if ($items) {
        $item_list = $items->fetch_all(MYSQLI_ASSOC);
    }
    
    echo json_encode(['success' => true, 'order' => $o, 'items' => $item_list]);
    exit;
}

// ============================================
// ADDRESSES - GET
// ============================================
elseif ($action === 'get_addresses' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_login();
    
    $result = $db->query("SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY is_default DESC, created_at ASC");
    
    $addresses = [];
    if ($result) {
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    echo json_encode(['success' => true, 'addresses' => $addresses, 'count' => count($addresses)]);
    exit;
}

// ============================================
// ADDRESSES - ADD
// ============================================
elseif ($action === 'add_address' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $first_name = $db->real_escape_string(trim($_POST['first_name'] ?? ''));
    $last_name = $db->real_escape_string(trim($_POST['last_name'] ?? ''));
    $street = $db->real_escape_string(trim($_POST['street'] ?? ''));
    $city = $db->real_escape_string(trim($_POST['city'] ?? ''));
    $postal_code = $db->real_escape_string(trim($_POST['postal_code'] ?? ''));
    $phone = $db->real_escape_string(trim($_POST['phone'] ?? ''));
    $country = $db->real_escape_string(trim($_POST['country'] ?? 'Schweiz'));
    $label = $db->real_escape_string(trim($_POST['label'] ?? 'Hauptadresse'));
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if (empty($first_name) || empty($last_name) || empty($street) || empty($city) || empty($postal_code) || empty($phone)) {
        echo json_encode(['success' => false, 'error' => 'Alle Pflichtfelder sind erforderlich (Vorname, Nachname, Straße, PLZ, Stadt, Telefon)']);
        exit;
    }

    $dup = $db->query("SELECT id FROM user_addresses WHERE user_id = $user_id AND first_name = '$first_name' AND last_name = '$last_name' AND street = '$street' AND city = '$city' AND postal_code = '$postal_code'");

    if ($dup && $dup->num_rows > 0) {
        http_response_code(409);
        die(json_encode(['success' => false, 'error' => 'Diese Adresse existiert bereits', 'code' => 'DUPLICATE_ADDRESS']));
    }

    if ($is_default) {
        $db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id");
    }

    if ($db->query("INSERT INTO user_addresses (user_id, first_name, last_name, street, city, postal_code, phone, country, label, is_default) VALUES ($user_id, '$first_name', '$last_name', '$street', '$city', '$postal_code', '$phone', '$country', '$label', $is_default)")) {
        echo json_encode(['success' => true, 'message' => 'Adresse hinzugefügt', 'id' => $db->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// ADDRESSES - UPDATE
// ============================================
elseif ($action === 'update_address' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $address_id = (int)($_POST['address_id'] ?? 0);
    $addr = $db->query("SELECT user_id FROM user_addresses WHERE id = $address_id");
    
    if (!$addr || $addr->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Adresse nicht gefunden']);
        exit;
    }
    
    $a = $addr->fetch_assoc();
    if ($a['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        exit;
    }
    
    $first_name = $db->real_escape_string(trim($_POST['first_name'] ?? ''));
    $last_name = $db->real_escape_string(trim($_POST['last_name'] ?? ''));
    $street = $db->real_escape_string(trim($_POST['street'] ?? ''));
    $city = $db->real_escape_string(trim($_POST['city'] ?? ''));
    $postal_code = $db->real_escape_string(trim($_POST['postal_code'] ?? ''));
    $phone = $db->real_escape_string(trim($_POST['phone'] ?? ''));
    $country = $db->real_escape_string(trim($_POST['country'] ?? 'Schweiz'));
    $label = $db->real_escape_string(trim($_POST['label'] ?? 'Hauptadresse'));
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if (empty($first_name) || empty($last_name) || empty($street) || empty($city) || empty($postal_code) || empty($phone)) {
        echo json_encode(['success' => false, 'error' => 'Alle Pflichtfelder sind erforderlich (Vorname, Nachname, Straße, PLZ, Stadt, Telefon)']);
        exit;
    }

    if ($is_default) {
        $db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $user_id");
    }

    if ($db->query("UPDATE user_addresses SET first_name = '$first_name', last_name = '$last_name', street = '$street', city = '$city', postal_code = '$postal_code', phone = '$phone', country = '$country', label = '$label', is_default = $is_default WHERE id = $address_id")) {
        echo json_encode(['success' => true, 'message' => 'Adresse aktualisiert']);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// ADDRESSES - DELETE
// ============================================
elseif ($action === 'delete_address' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $address_id = (int)($_POST['address_id'] ?? 0);
    $addr = $db->query("SELECT user_id FROM user_addresses WHERE id = $address_id");
    
    if (!$addr || $addr->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Adresse nicht gefunden']);
        exit;
    }
    
    $a = $addr->fetch_assoc();
    if ($a['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        exit;
    }
    
    if ($db->query("DELETE FROM user_addresses WHERE id = $address_id")) {
        echo json_encode(['success' => true, 'message' => 'Adresse gelöscht']);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// PROFILE - UPDATE
// ============================================
elseif ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $first_name = $db->real_escape_string(trim($_POST['first_name'] ?? ''));
    $last_name = $db->real_escape_string(trim($_POST['last_name'] ?? ''));
    $phone = $db->real_escape_string(trim($_POST['phone'] ?? ''));
    
    if (empty($first_name)) {
        echo json_encode(['success' => false, 'error' => 'Vorname erforderlich']);
        exit;
    }
    
    if ($db->query("UPDATE users SET first_name = '$first_name', last_name = '$last_name', phone = '$phone' WHERE id = $user_id")) {
        echo json_encode(['success' => true, 'message' => 'Profil aktualisiert']);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

// ============================================
// PASSWORD - CHANGE
// ============================================
elseif ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'error' => 'Passwörter stimmen nicht überein']);
        exit;
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Passwort muss mindestens 6 Zeichen lang sein']);
        exit;
    }
    
    $user = $db->query("SELECT password FROM users WHERE id = $user_id");
    $u = $user->fetch_assoc();
    
    if (!password_verify($old_password, $u['password'])) {
        echo json_encode(['success' => false, 'error' => 'Aktuelles Passwort ist falsch']);
        exit;
    }
    
    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    if ($db->query("UPDATE users SET password = '$new_hash' WHERE id = $user_id")) {
        echo json_encode(['success' => true, 'message' => 'Passwort geändert']);
    } else {
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unbekannte Action']);
}
?>