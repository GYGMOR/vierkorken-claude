<?php
// includes/functions.php
// Globale Hilfsfunktionen für die ganze App

// 1. Sichere Text-Ausgabe (gegen XSS)
function safe_output($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// 2. Format Preis (CHF)
function format_price($price) {
    return number_format($price, 2, '.', '') . ' CHF';
}

// 3. Kategorie-Name abrufen
function get_category_name($category_id) {
    global $db;
    $result = $db->query("SELECT name FROM categories WHERE id = $category_id");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['name'];
    }
    return 'Unbekannt';
}

// 4. Alle Kategorien abrufen
function get_all_categories() {
    global $db;
    $result = $db->query("SELECT * FROM categories ORDER BY name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// 5. Weine nach Kategorie abrufen
function get_wines_by_category($category_id, $limit = 50) {
    global $db;
    if ($category_id > 0) {
        $query = "SELECT * FROM wines WHERE category_id = $category_id AND stock > 0 ORDER BY name ASC LIMIT $limit";
    } else {
        $query = "SELECT * FROM wines WHERE stock > 0 ORDER BY name ASC LIMIT $limit";
    }
    $result = $db->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// 6. Anzahl Weine in Kategorie
function count_wines_in_category($category_id) {
    global $db;
    $result = $db->query("SELECT COUNT(*) as count FROM wines WHERE category_id = $category_id AND stock > 0");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// 7. Gesamtanzahl Weine
function count_total_wines() {
    global $db;
    $result = $db->query("SELECT COUNT(*) as count FROM wines WHERE stock > 0");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// 8. Ein Wein abrufen nach ID
function get_wine_by_id($wine_id) {
    global $db;
    $wine_id = (int)$wine_id;
    $result = $db->query("SELECT * FROM wines WHERE id = $wine_id");
    return $result->fetch_assoc();
}

// 10. Admin-Check
function is_admin() {
    if (isset($_SESSION['user_id'])) {
        global $db;
        $user_id = (int)$_SESSION['user_id'];
        $result = $db->query("SELECT * FROM admins WHERE user_id = $user_id");
        return $result->num_rows > 0;
    }
    return false;
}

// 11. Redirect Funktion
function redirect($page) {
    header("Location: ?page=$page");
    exit;
}

// 12. Error Message anzeigen
function show_error($message) {
    echo "<div class='alert alert-error'>" . safe_output($message) . "</div>";
}

// 13. Success Message anzeigen
function show_success($message) {
    echo "<div class='alert alert-success'>" . safe_output($message) . "</div>";
}

// 14. Slug aus Text generieren (für URLs)
function generate_slug($text) {
    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

// 15. DB Error Handler
function db_error($error_msg) {
    error_log("DB ERROR: " . $error_msg);
    die("Datenbankfehler: " . safe_output($error_msg));
}

// 16. SETTINGS MANAGEMENT
function get_setting($key, $default = '') {
    global $db;
    $key_safe = $db->real_escape_string($key);
    $result = $db->query("SELECT value FROM settings WHERE key_name = '$key_safe'");
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['value'];
    }
    return $default;
}

function update_setting($key, $value) {
    global $db;
    $key_safe = $db->real_escape_string($key);
    $value_safe = $db->real_escape_string($value);
    
    $check = $db->query("SELECT id FROM settings WHERE key_name = '$key_safe'");
    
    if ($check->num_rows > 0) {
        return $db->query("UPDATE settings SET value = '$value_safe', updated_at = NOW() WHERE key_name = '$key_safe'");
    } else {
        return $db->query("INSERT INTO settings (key_name, value) VALUES ('$key_safe', '$value_safe')");
    }
}

// 17. Alle Settings laden
function get_all_settings() {
    global $db;
    $result = $db->query("SELECT key_name, value FROM settings");
    $settings = [];
    
    while ($row = $result->fetch_assoc()) {
        $settings[$row['key_name']] = $row['value'];
    }
    
    return $settings;
}

// 18. Bild-Upload Funktion
function handle_image_upload($file_input, $destination_folder) {
    if (!isset($_FILES[$file_input])) {
        return ['success' => false, 'error' => 'Keine Datei hochgeladen'];
    }
    
    $file = $_FILES[$file_input];
    
    // Validierung
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'error' => 'Nur Bilder erlaubt (jpg, png, gif, webp)'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        return ['success' => false, 'error' => 'Datei zu groß (max 5MB)'];
    }
    
    // Neuer Name
    $new_name = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $upload_path = $destination_folder . $new_name;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $new_name, 'path' => $upload_path];
    } else {
        return ['success' => false, 'error' => 'Fehler beim Hochladen'];
    }
}

// 19. THEME COLORS MANAGEMENT (NEU!)
function get_theme_color($key, $default = '') {
    global $db;
    $key_safe = $db->real_escape_string($key);
    $result = $db->query("SELECT setting_value FROM theme_settings WHERE setting_key = '$key_safe'");
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}

function update_theme_color($key, $value) {
    global $db;
    $key_safe = $db->real_escape_string($key);
    $value_safe = $db->real_escape_string($value);
    
    $check = $db->query("SELECT id FROM theme_settings WHERE setting_key = '$key_safe'");
    
    if ($check->num_rows > 0) {
        return $db->query("UPDATE theme_settings SET setting_value = '$value_safe', updated_at = NOW() WHERE setting_key = '$key_safe'");
    } else {
        return $db->query("INSERT INTO theme_settings (setting_key, setting_value) VALUES ('$key_safe', '$value_safe')");
    }
}

// 20. Alle Theme Settings laden
function get_all_theme_settings() {
    global $db;
    $result = $db->query("SELECT setting_key, setting_value FROM theme_settings");
    $settings = [];

    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}

// 21. NEWS/NEUHEITEN MANAGEMENT
function get_all_news_items($limit = 6, $active_only = true) {
    global $db;
    $where = $active_only ? "WHERE is_active = 1" : "";
    $query = "SELECT * FROM news_items $where ORDER BY display_order ASC, created_at DESC LIMIT $limit";
    $result = $db->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_news_item_by_id($id) {
    global $db;
    $id = (int)$id;
    $result = $db->query("SELECT * FROM news_items WHERE id = $id");
    return $result ? $result->fetch_assoc() : null;
}

function create_news_item($data) {
    global $db;
    $title = $db->real_escape_string($data['title']);
    $content = $db->real_escape_string($data['content'] ?? '');
    $type = $db->real_escape_string($data['type']);
    $image_url = $db->real_escape_string($data['image_url'] ?? '');
    $link_url = $db->real_escape_string($data['link_url'] ?? '');
    $reference_id = isset($data['reference_id']) ? (int)$data['reference_id'] : 'NULL';
    $display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;

    $sql = "INSERT INTO news_items (title, content, type, image_url, link_url, reference_id, display_order)
            VALUES ('$title', '$content', '$type', '$image_url', '$link_url', $reference_id, $display_order)";

    return $db->query($sql);
}

function update_news_item($id, $data) {
    global $db;
    $id = (int)$id;
    $title = $db->real_escape_string($data['title']);
    $content = $db->real_escape_string($data['content'] ?? '');
    $type = $db->real_escape_string($data['type']);
    $image_url = $db->real_escape_string($data['image_url'] ?? '');
    $link_url = $db->real_escape_string($data['link_url'] ?? '');
    $reference_id = isset($data['reference_id']) ? (int)$data['reference_id'] : 'NULL';
    $display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;
    $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

    $sql = "UPDATE news_items SET
            title = '$title',
            content = '$content',
            type = '$type',
            image_url = '$image_url',
            link_url = '$link_url',
            reference_id = $reference_id,
            display_order = $display_order,
            is_active = $is_active
            WHERE id = $id";

    return $db->query($sql);
}

function delete_news_item($id) {
    global $db;
    $id = (int)$id;
    return $db->query("DELETE FROM news_items WHERE id = $id");
}

// 22. EVENTS MANAGEMENT
function get_all_events($active_only = true, $future_only = false) {
    global $db;
    $where = [];

    if ($active_only) {
        $where[] = "is_active = 1";
    }

    if ($future_only) {
        $where[] = "event_date >= NOW()";
    }

    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    $query = "SELECT * FROM events $where_clause ORDER BY event_date ASC";
    $result = $db->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_event_by_id($id) {
    global $db;
    $id = (int)$id;
    $result = $db->query("SELECT * FROM events WHERE id = $id");
    return $result ? $result->fetch_assoc() : null;
}

function create_event($data) {
    global $db;
    $name = $db->real_escape_string($data['name']);
    $description = $db->real_escape_string($data['description'] ?? '');
    $event_date = $db->real_escape_string($data['event_date']);
    $location = $db->real_escape_string($data['location'] ?? '');
    $price = (float)($data['price'] ?? 0);
    $image_url = $db->real_escape_string($data['image_url'] ?? '');
    $max_participants = (int)($data['max_participants'] ?? 0);
    $available_tickets = (int)($data['available_tickets'] ?? 0);
    $category_id = isset($data['category_id']) ? (int)$data['category_id'] : 9;

    $sql = "INSERT INTO events (name, description, event_date, location, price, image_url, max_participants, available_tickets, category_id)
            VALUES ('$name', '$description', '$event_date', '$location', $price, '$image_url', $max_participants, $available_tickets, $category_id)";

    return $db->query($sql);
}

function update_event($id, $data) {
    global $db;
    $id = (int)$id;
    $name = $db->real_escape_string($data['name']);
    $description = $db->real_escape_string($data['description'] ?? '');
    $event_date = $db->real_escape_string($data['event_date']);
    $location = $db->real_escape_string($data['location'] ?? '');
    $price = (float)($data['price'] ?? 0);
    $image_url = $db->real_escape_string($data['image_url'] ?? '');
    $max_participants = (int)($data['max_participants'] ?? 0);
    $available_tickets = (int)($data['available_tickets'] ?? 0);
    $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

    $sql = "UPDATE events SET
            name = '$name',
            description = '$description',
            event_date = '$event_date',
            location = '$location',
            price = $price,
            image_url = '$image_url',
            max_participants = $max_participants,
            available_tickets = $available_tickets,
            is_active = $is_active
            WHERE id = $id";

    return $db->query($sql);
}

function delete_event($id) {
    global $db;
    $id = (int)$id;
    return $db->query("DELETE FROM events WHERE id = $id");
}

function get_available_tickets($event_id) {
    global $db;
    $event_id = (int)$event_id;
    $result = $db->query("SELECT available_tickets FROM events WHERE id = $event_id");
    if ($result && $row = $result->fetch_assoc()) {
        return (int)$row['available_tickets'];
    }
    return 0;
}

function book_event_tickets($event_id, $quantity, $user_data = []) {
    global $db;
    $event_id = (int)$event_id;
    $quantity = (int)$quantity;

    // Check availability
    $available = get_available_tickets($event_id);
    if ($available < $quantity) {
        return ['success' => false, 'error' => 'Nicht genügend Tickets verfügbar'];
    }

    // Get event price
    $event = get_event_by_id($event_id);
    if (!$event) {
        return ['success' => false, 'error' => 'Event nicht gefunden'];
    }

    $total_price = $event['price'] * $quantity;

    // Create booking
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'NULL';
    $customer_name = $db->real_escape_string($user_data['name'] ?? '');
    $customer_email = $db->real_escape_string($user_data['email'] ?? '');
    $customer_phone = $db->real_escape_string($user_data['phone'] ?? '');

    $sql = "INSERT INTO event_bookings (event_id, user_id, ticket_quantity, total_price, customer_name, customer_email, customer_phone)
            VALUES ($event_id, $user_id, $quantity, $total_price, '$customer_name', '$customer_email', '$customer_phone')";

    if ($db->query($sql)) {
        // Update available tickets
        $new_available = $available - $quantity;
        $db->query("UPDATE events SET available_tickets = $new_available WHERE id = $event_id");

        return ['success' => true, 'booking_id' => $db->insert_id];
    }

    return ['success' => false, 'error' => 'Buchung fehlgeschlagen'];
}

?>