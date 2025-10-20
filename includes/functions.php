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
    echo "<div class='alert alert-error'>✕ " . safe_output($message) . "</div>";
}

// 13. Success Message anzeigen
function show_success($message) {
    echo "<div class='alert alert-success'>✓ " . safe_output($message) . "</div>";
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

?>