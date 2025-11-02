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
    $category_id = (int)$category_id; // Type casting for security
    $stmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
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
    $category_id = (int)$category_id;
    $limit = (int)$limit;

    if ($category_id > 0) {
        $stmt = $db->prepare("SELECT * FROM wines WHERE category_id = ? AND stock > 0 ORDER BY name ASC LIMIT ?");
        $stmt->bind_param("ii", $category_id, $limit);
    } else {
        $stmt = $db->prepare("SELECT * FROM wines WHERE stock > 0 ORDER BY name ASC LIMIT ?");
        $stmt->bind_param("i", $limit);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// 6. Anzahl Weine in Kategorie
function count_wines_in_category($category_id) {
    global $db;
    $category_id = (int)$category_id;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM wines WHERE category_id = ? AND stock > 0");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
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
    $stmt = $db->prepare("SELECT * FROM wines WHERE id = ?");
    $stmt->bind_param("i", $wine_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

// 10. Admin-Check
function is_admin() {
    if (isset($_SESSION['user_id'])) {
        global $db;
        $user_id = (int)$_SESSION['user_id'];
        $stmt = $db->prepare("SELECT id FROM admins WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
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
    $stmt = $db->prepare("SELECT value FROM settings WHERE key_name = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        return $row['value'];
    }
    return $default;
}

function update_setting($key, $value) {
    global $db;

    $stmt = $db->prepare("SELECT id FROM settings WHERE key_name = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $stmt = $db->prepare("UPDATE settings SET value = ?, updated_at = NOW() WHERE key_name = ?");
        $stmt->bind_param("ss", $value, $key);
        return $stmt->execute();
    } else {
        $stmt = $db->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)");
        $stmt->bind_param("ss", $key, $value);
        return $stmt->execute();
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
    $stmt = $db->prepare("SELECT setting_value FROM theme_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}

function update_theme_color($key, $value) {
    global $db;

    $stmt = $db->prepare("SELECT id FROM theme_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $stmt = $db->prepare("UPDATE theme_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
        $stmt->bind_param("ss", $value, $key);
        return $stmt->execute();
    } else {
        $stmt = $db->prepare("INSERT INTO theme_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $key, $value);
        return $stmt->execute();
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
    $limit = (int)$limit;

    if ($active_only) {
        $stmt = $db->prepare("SELECT * FROM news_items WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
    } else {
        $stmt = $db->prepare("SELECT * FROM news_items ORDER BY display_order ASC, created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_news_item_by_id($id) {
    global $db;
    $id = (int)$id;
    $stmt = $db->prepare("SELECT * FROM news_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

function create_news_item($data) {
    global $db;
    $title = $data['title'];
    $content = $data['content'] ?? '';
    $type = $data['type'];
    $image_url = $data['image_url'] ?? '';
    $link_url = $data['link_url'] ?? '';
    $reference_id = isset($data['reference_id']) ? (int)$data['reference_id'] : null;
    $display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;

    $stmt = $db->prepare("INSERT INTO news_items (title, content, type, image_url, link_url, reference_id, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssii", $title, $content, $type, $image_url, $link_url, $reference_id, $display_order);
    return $stmt->execute();
}

function update_news_item($id, $data) {
    global $db;
    $id = (int)$id;
    $title = $data['title'];
    $content = $data['content'] ?? '';
    $type = $data['type'];
    $image_url = $data['image_url'] ?? '';
    $link_url = $data['link_url'] ?? '';
    $reference_id = isset($data['reference_id']) ? (int)$data['reference_id'] : null;
    $display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;
    $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

    $stmt = $db->prepare("UPDATE news_items SET
            title = ?,
            content = ?,
            type = ?,
            image_url = ?,
            link_url = ?,
            reference_id = ?,
            display_order = ?,
            is_active = ?
            WHERE id = ?");
    $stmt->bind_param("sssssiiii", $title, $content, $type, $image_url, $link_url, $reference_id, $display_order, $is_active, $id);
    return $stmt->execute();
}

function delete_news_item($id) {
    global $db;
    $id = (int)$id;
    $stmt = $db->prepare("DELETE FROM news_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
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
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
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
    $featured_bg_color = $db->real_escape_string($data['featured_bg_color'] ?? '#2c5282');
    $featured_text_color = $db->real_escape_string($data['featured_text_color'] ?? '#ffffff');

    $sql = "INSERT INTO events (name, description, event_date, location, price, image_url, max_participants, available_tickets, category_id, featured_bg_color, featured_text_color)
            VALUES ('$name', '$description', '$event_date', '$location', $price, '$image_url', $max_participants, $available_tickets, $category_id, '$featured_bg_color', '$featured_text_color')";

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
    $featured_bg_color = $db->real_escape_string($data['featured_bg_color'] ?? '#2c5282');
    $featured_text_color = $db->real_escape_string($data['featured_text_color'] ?? '#ffffff');

    $sql = "UPDATE events SET
            name = '$name',
            description = '$description',
            event_date = '$event_date',
            location = '$location',
            price = $price,
            image_url = '$image_url',
            max_participants = $max_participants,
            available_tickets = $available_tickets,
            is_active = $is_active,
            featured_bg_color = '$featured_bg_color',
            featured_text_color = '$featured_text_color'
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

// ============================================
// KLARA API FUNCTIONS
// ============================================

// Klara API Credentials
define('KLARA_API_BASEURL', 'https://api.klara.ch');
define('KLARA_API_KEY', '01c11c3e-c484-4ce7-bca0-3f52eb3772af');

// Direkter Klara API Call
function klara_api_call($endpoint) {
    $url = KLARA_API_BASEURL . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Für lokale Tests
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Accept-Language: de',
        'X-API-KEY: ' . KLARA_API_KEY
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Klara API Error: " . $error);
        return null;
    }

    if ($http_code !== 200) {
        error_log("Klara API HTTP Error: " . $http_code);
        return null;
    }

    $data = json_decode($response, true);
    return $data;
}

// Fetch Klara Kategorien
function klara_get_categories() {
    $raw = klara_api_call('/core/latest/article-categories?limit=1000');

    if (!is_array($raw)) {
        return [];
    }

    // Kategorien aufräumen & vereinheitlichen
    $categories = [];
    foreach ($raw as $c) {
        $categories[] = [
            'id' => (string)$c['id'],
            'name' => $c['nameDE'] ?? $c['nameEN'] ?? 'Kategorie',
            'order' => $c['order'] ?? null,
            'active' => ($c['active'] ?? true) !== false
        ];
    }

    // Sortieren wie in KLARA
    usort($categories, function($a, $b) {
        $orderA = $a['order'] ?? 9999;
        $orderB = $b['order'] ?? 9999;
        return $orderA - $orderB;
    });

    return $categories;
}

// Fetch Klara Artikel
function klara_get_articles($categoryId = null, $search = null) {
    $raw = klara_api_call('/core/latest/articles?limit=1000');

    if (!is_array($raw)) {
        return [];
    }

    // Artikel aufbereiten
    $articles = [];
    foreach ($raw as $a) {
        // Preis aus pricePeriods holen
        $price = null;
        if (isset($a['pricePeriods']) && is_array($a['pricePeriods']) && count($a['pricePeriods']) > 0) {
            $price = (float)($a['pricePeriods'][0]['price'] ?? 0);
        }

        // Kategorie-IDs sammeln
        $catIds = [];
        if (isset($a['posCategories']) && is_array($a['posCategories'])) {
            foreach ($a['posCategories'] as $c) {
                if (isset($c['id'])) {
                    $catIds[] = (string)$c['id'];
                }
            }
        }

        $article = [
            'id' => (string)$a['id'],
            'articleNumber' => $a['articleNumber'] ?? null,
            'name' => $a['nameDE'] ?? $a['nameEN'] ?? 'Artikel',
            'price' => $price,
            'image_url' => null,
            'categories' => $catIds,
            'description' => $a['descriptionDE'] ?? $a['descriptionEN'] ?? '',
            'stock' => 999,
            'producer' => $a['producer'] ?? '',
            'vintage' => null,
            'region' => '',
            'alcohol_content' => null,
            'avg_rating' => null,
            'rating_count' => 0
        ];

        // Kategorie-Filter anwenden
        if ($categoryId !== null && $categoryId !== '') {
            if (!in_array($categoryId, $catIds)) {
                continue;
            }
        }

        // Such-Filter anwenden
        if ($search !== null && $search !== '') {
            $searchLower = mb_strtolower($search);
            $nameLower = mb_strtolower($article['name']);
            $articleNumberLower = mb_strtolower($article['articleNumber'] ?? '');

            if (strpos($nameLower, $searchLower) === false &&
                strpos($articleNumberLower, $searchLower) === false) {
                continue;
            }
        }

        $articles[] = $article;
    }

    return $articles;
}

// Count Artikel in Klara Kategorie
function klara_count_articles_in_category($categoryId) {
    $articles = klara_get_articles($categoryId);
    return count($articles);
}

// ============================================
// KLARA EXTENDED DATA FUNCTIONS
// ============================================

// Get erweiterte Daten für einen Klara-Artikel
function get_klara_extended_data($klara_article_id) {
    global $db;
    $klara_article_id = $db->real_escape_string($klara_article_id);

    $result = $db->query("SELECT * FROM klara_products_extended WHERE klara_article_id = '$klara_article_id'");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Update oder Insert erweiterte Daten
function update_klara_extended_data($klara_article_id, $data) {
    global $db;
    $klara_article_id = $db->real_escape_string($klara_article_id);

    // Check if exists
    $existing = get_klara_extended_data($klara_article_id);

    $image_url = $db->real_escape_string($data['image_url'] ?? '');
    $producer = $db->real_escape_string($data['producer'] ?? '');
    $vintage = isset($data['vintage']) ? (int)$data['vintage'] : 'NULL';
    $region = $db->real_escape_string($data['region'] ?? '');
    $alcohol_content = isset($data['alcohol_content']) ? (float)$data['alcohol_content'] : 'NULL';
    $short_description = $db->real_escape_string($data['short_description'] ?? '');
    $extended_description = $db->real_escape_string($data['extended_description'] ?? '');
    $is_featured = isset($data['is_featured']) ? (int)$data['is_featured'] : 0;
    $custom_price = isset($data['custom_price']) && $data['custom_price'] !== '' ? (float)$data['custom_price'] : 'NULL';
    $featured_bg_color = $db->real_escape_string($data['featured_bg_color'] ?? '#722c2c');
    $featured_text_color = $db->real_escape_string($data['featured_text_color'] ?? '#ffffff');

    if ($existing) {
        // Update
        $sql = "UPDATE klara_products_extended SET
                image_url = '$image_url',
                producer = '$producer',
                vintage = $vintage,
                region = '$region',
                alcohol_content = $alcohol_content,
                short_description = '$short_description',
                extended_description = '$extended_description',
                is_featured = $is_featured,
                custom_price = $custom_price,
                featured_bg_color = '$featured_bg_color',
                featured_text_color = '$featured_text_color'
                WHERE klara_article_id = '$klara_article_id'";
    } else {
        // Insert
        $sql = "INSERT INTO klara_products_extended
                (klara_article_id, image_url, producer, vintage, region, alcohol_content, short_description, extended_description, is_featured, custom_price, featured_bg_color, featured_text_color)
                VALUES ('$klara_article_id', '$image_url', '$producer', $vintage, '$region', $alcohol_content, '$short_description', '$extended_description', $is_featured, $custom_price, '$featured_bg_color', '$featured_text_color')";
    }

    return $db->query($sql);
}

// Delete erweiterte Daten
function delete_klara_extended_data($klara_article_id) {
    global $db;
    $klara_article_id = $db->real_escape_string($klara_article_id);
    return $db->query("DELETE FROM klara_products_extended WHERE klara_article_id = '$klara_article_id'");
}

// Get alle featured Klara-Produkte
function get_klara_featured_products($limit = 6) {
    global $db;
    $limit = (int)$limit;

    $result = $db->query("SELECT klara_article_id FROM klara_products_extended WHERE is_featured = 1 ORDER BY updated_at DESC LIMIT $limit");

    if (!$result) {
        return [];
    }

    $featured_ids = [];
    while ($row = $result->fetch_assoc()) {
        $featured_ids[] = $row['klara_article_id'];
    }

    // Klara-Artikel holen
    $all_articles = klara_get_articles();
    $featured = [];

    foreach ($all_articles as $article) {
        if (in_array($article['id'], $featured_ids)) {
            // Erweiterte Daten mergen (enthält Farben!)
            $extended = get_klara_extended_data($article['id']);
            if ($extended) {
                // Merge extended data, extended values take precedence
                $article = array_merge($article, $extended);
            }

            // Stelle sicher, dass Farben gesetzt sind (Fallback)
            if (!isset($article['featured_bg_color']) || empty($article['featured_bg_color'])) {
                $article['featured_bg_color'] = '#722c2c';
            }
            if (!isset($article['featured_text_color']) || empty($article['featured_text_color'])) {
                $article['featured_text_color'] = '#ffffff';
            }

            $featured[] = $article;
        }
    }

    return $featured;
}

// ============================================
// FEATURED EVENTS FUNCTIONS
// ============================================

// Get featured events
function get_featured_events($limit = 6) {
    global $db;
    $limit = (int)$limit;

    $result = $db->query("SELECT * FROM events WHERE is_featured = 1 ORDER BY event_date DESC LIMIT $limit");

    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Mark event as featured
function set_event_featured($event_id, $is_featured) {
    global $db;
    $event_id = (int)$event_id;
    $is_featured = $is_featured ? 1 : 0;

    return $db->query("UPDATE events SET is_featured = $is_featured WHERE id = $event_id");
}

// ============================================
// CUSTOM NEWS FUNCTIONS
// ============================================

// Get custom news
function get_custom_news($limit = 6) {
    global $db;
    $limit = (int)$limit;

    $result = $db->query("SELECT * FROM custom_news WHERE is_featured = 1 ORDER BY created_at DESC LIMIT $limit");

    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get single news by ID
function get_custom_news_by_id($id) {
    global $db;
    $id = (int)$id;

    $result = $db->query("SELECT * FROM custom_news WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

// Create or update custom news
function save_custom_news($id, $title, $content, $image_url, $is_featured, $active, $featured_bg_color = '#c27c0e', $featured_text_color = '#ffffff') {
    global $db;

    $title = $db->real_escape_string($title);
    $content = $db->real_escape_string($content);
    $image_url = $db->real_escape_string($image_url);
    $is_featured = $is_featured ? 1 : 0;
    $active = $active ? 1 : 0;
    $featured_bg_color = $db->real_escape_string($featured_bg_color);
    $featured_text_color = $db->real_escape_string($featured_text_color);

    if ($id > 0) {
        // Update
        $id = (int)$id;
        $sql = "UPDATE custom_news SET
                title = '$title',
                content = '$content',
                image_url = '$image_url',
                is_featured = $is_featured,
                active = $active,
                featured_bg_color = '$featured_bg_color',
                featured_text_color = '$featured_text_color'
                WHERE id = $id";
    } else {
        // Insert
        $sql = "INSERT INTO custom_news (title, content, image_url, is_featured, active, featured_bg_color, featured_text_color)
                VALUES ('$title', '$content', '$image_url', $is_featured, $active, '$featured_bg_color', '$featured_text_color')";
    }

    return $db->query($sql);
}

// Delete custom news
function delete_custom_news($id) {
    global $db;
    $id = (int)$id;

    return $db->query("DELETE FROM custom_news WHERE id = $id");
}

?>