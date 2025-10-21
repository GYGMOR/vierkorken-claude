<?php
// config/security.php
// Zentrale Sicherheitsfunktionen für Vier Korken

// ============================================
// 1. SESSION SECURITY
// ============================================

// Sichere Session-Konfiguration
function init_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Session Cookie Flags
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);

        // Session regenerieren bei kritischen Aktionen
        session_start();

        // Session Fixation Protection
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
            $_SESSION['created_at'] = time();
        }

        // Session Timeout (30 Minuten)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();

        // IP-Binding (optional - kann Probleme mit Proxies geben)
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        } elseif ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            // Möglicher Session Hijacking Versuch
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['security_warning'] = 'Session wurde aus Sicherheitsgründen zurückgesetzt';
        }
    }
}

// ============================================
// 2. CSRF PROTECTION
// ============================================

// CSRF Token generieren
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token validieren
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// CSRF Token HTML Input
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// CSRF Check für POST-Requests
function require_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_REQUEST['csrf_token'] ?? '';
        if (!verify_csrf_token($token)) {
            http_response_code(403);
            die(json_encode(['success' => false, 'error' => 'CSRF-Token ungültig. Bitte versuchen Sie es erneut.']));
        }
    }
}

// ============================================
// 3. XSS PROTECTION
// ============================================

// Erweiterte XSS-Schutzfunktion
function sanitize_output($data, $encoding = 'UTF-8') {
    if (is_array($data)) {
        return array_map('sanitize_output', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, $encoding);
}

// HTML aus Text entfernen (für User-Input)
function strip_all_html($text) {
    return strip_tags($text);
}

// Nur bestimmte Tags erlauben
function allow_safe_html($text, $allowed_tags = '<p><br><strong><em><a>') {
    return strip_tags($text, $allowed_tags);
}

// ============================================
// 4. SQL INJECTION PROTECTION
// ============================================

// Sichere Integer-Konvertierung
function secure_int($value, $default = 0) {
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => $default]]);
}

// Sichere Float-Konvertierung
function secure_float($value, $default = 0.0) {
    return filter_var($value, FILTER_VALIDATE_FLOAT, ['options' => ['default' => $default]]);
}

// Sichere Email-Validierung
function secure_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ============================================
// 5. RATE LIMITING (Einfache Implementierung)
// ============================================

// Rate Limiting für API-Endpunkte
function check_rate_limit($action, $max_attempts = 5, $time_window = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $action . '_' . $ip;

    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }

    $current_time = time();
    $rate_data = $_SESSION['rate_limits'][$key] ?? ['attempts' => 0, 'reset_at' => $current_time + $time_window];

    // Reset wenn Zeit abgelaufen
    if ($current_time > $rate_data['reset_at']) {
        $rate_data = ['attempts' => 0, 'reset_at' => $current_time + $time_window];
    }

    // Limit überschritten?
    if ($rate_data['attempts'] >= $max_attempts) {
        http_response_code(429);
        die(json_encode([
            'success' => false,
            'error' => 'Zu viele Anfragen. Bitte warten Sie ' . ($rate_data['reset_at'] - $current_time) . ' Sekunden.'
        ]));
    }

    // Attempt zählen
    $rate_data['attempts']++;
    $_SESSION['rate_limits'][$key] = $rate_data;

    return true;
}

// ============================================
// 6. FILE UPLOAD SECURITY
// ============================================

// Sichere Datei-Upload Validierung
function validate_upload_file($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $max_size = 5242880) {
    // Prüfe ob Datei existiert
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Keine gültige Datei hochgeladen'];
    }

    // Prüfe Dateigröße
    if ($file['size'] > $max_size) {
        $max_mb = round($max_size / 1048576, 1);
        return ['success' => false, 'error' => "Datei zu groß (max {$max_mb}MB)"];
    }

    // Prüfe MIME-Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_types)) {
        return ['success' => false, 'error' => 'Dateityp nicht erlaubt'];
    }

    // Prüfe Dateiendung
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Dateiendung nicht erlaubt'];
    }

    // Zusätzliche Bildprüfung
    if (strpos($mime, 'image/') === 0) {
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            return ['success' => false, 'error' => 'Keine gültige Bilddatei'];
        }
    }

    return ['success' => true];
}

// Sicherer Dateiname generieren
function generate_safe_filename($original_name) {
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $random = bin2hex(random_bytes(16));
    return $random . '.' . $extension;
}

// ============================================
// 7. PASSWORD SECURITY
// ============================================

// Passwort-Stärke prüfen
function validate_password_strength($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Passwort muss mindestens einen Großbuchstaben enthalten';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Passwort muss mindestens einen Kleinbuchstaben enthalten';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Passwort muss mindestens eine Zahl enthalten';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// ============================================
// 8. SECURE HEADERS
// ============================================

// Security Headers setzen
function set_security_headers() {
    // XSS Protection
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');

    // Content Security Policy (erlaubt Google Maps iframes)
    header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self' data: https:; frame-src 'self' https://www.google.com https://maps.google.com;");

    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Permissions Policy (erlaubt geolocation für Maps)
    header('Permissions-Policy: geolocation=(self "https://www.google.com"), microphone=(), camera=()');
}

// ============================================
// 9. INPUT SANITIZATION
// ============================================

// Sichere String-Bereinigung
function sanitize_string($string) {
    return trim(strip_tags($string));
}

// Sichere URL-Validierung
function sanitize_url($url) {
    $url = filter_var($url, FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
}

// Sichere Telefonnummer-Bereinigung
function sanitize_phone($phone) {
    return preg_replace('/[^0-9+\-\(\) ]/', '', $phone);
}

// ============================================
// 10. LOGGING & MONITORING
// ============================================

// Sicherheitsrelevante Events loggen
function log_security_event($event_type, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $event_type,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'details' => $details
    ];

    // In Produktionsumgebung: In Datenbank oder separates Logfile schreiben
    error_log('SECURITY: ' . json_encode($log_entry));
}

// ============================================
// 11. ADMIN VERIFICATION
// ============================================

// Admin-Zugriff validieren
function require_admin() {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        log_security_event('unauthorized_admin_access', [
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_id' => $_SESSION['user_id'] ?? null
        ]);

        http_response_code(403);
        die(json_encode(['success' => false, 'error' => 'Keine Berechtigung']));
    }
}

// ============================================
// 12. USER AUTHENTICATION
// ============================================

// User-Login validieren
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Nicht eingeloggt', 'redirect' => '?page=home&modal=login']));
    }
}

?>
