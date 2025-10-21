<?php
// api/auth.php - Authentication API

header('Content-Type: application/json; charset=utf-8');

require_once '../config/security.php';
init_secure_session();

require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ============================================
// REGISTER - Neuen Benutzer erstellen
// ============================================
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate Limiting: Max 3 Registrierungen pro Stunde
    check_rate_limit('register', 3, 3600);

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $first_name = sanitize_string(trim($_POST['first_name'] ?? ''));
    $last_name = sanitize_string(trim($_POST['last_name'] ?? ''));

    // Validierung
    $email = secure_email($email);
    if (!$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ungültige E-Mail-Adresse']);
        exit;
    }

    // Passwort-Stärke prüfen
    $password_check = validate_password_strength($password);
    if (!$password_check['valid']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Passwort nicht sicher genug', 'details' => $password_check['errors']]);
        exit;
    }

    if (empty($first_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Vorname erforderlich']);
        exit;
    }

    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check && $check->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Diese E-Mail-Adresse ist bereits registriert']);
        exit;
    }

    // Create password hash
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $db->prepare("INSERT INTO users (email, password, first_name, last_name, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $email, $password_hash, $first_name, $last_name);

    if ($stmt->execute()) {
        $user_id = $db->insert_id;

        // Link all guest orders with this email to the new account
        $stmt = $db->prepare("UPDATE orders SET user_id = ? WHERE (guest_email = ? OR delivery_email = ?) AND user_id IS NULL");
        $stmt->bind_param("iss", $user_id, $email, $email);
        $stmt->execute();
        $linked_orders_count = $stmt->affected_rows;

        // Auto-login after registration
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;

        $message = 'Account erfolgreich erstellt!';
        if ($linked_orders_count > 0) {
            $message .= ' ' . $linked_orders_count . ' frühere Bestellung(en) wurden deinem Account zugeordnet.';
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'user_id' => $user_id,
            'linked_orders' => $linked_orders_count
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen des Accounts: ' . $db->error]);
    }
    exit;
}

// ============================================
// LOGIN - Benutzer anmelden
// ============================================
elseif ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate Limiting: Max 5 Login-Versuche pro Minute
    check_rate_limit('login', 5, 60);

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        log_security_event('login_failed', ['reason' => 'empty_credentials', 'email' => $email]);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'E-Mail und Passwort erforderlich']);
        exit;
    }

    $stmt = $db->prepare("SELECT id, email, password, first_name, last_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $user_id = $user['id'];

            // Link all guest orders with this email to the account
            $stmt = $db->prepare("UPDATE orders SET user_id = ? WHERE (guest_email = ? OR delivery_email = ?) AND user_id IS NULL");
            $stmt->bind_param("iss", $user_id, $email, $email);
            $stmt->execute();
            $linked_orders_count = $stmt->affected_rows;

            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            // Check if admin
            $admin_check = $db->query("SELECT id FROM admins WHERE user_id = $user_id");
            if ($admin_check && $admin_check->num_rows > 0) {
                $_SESSION['is_admin'] = true;
            }

            $message = 'Login erfolgreich';
            if ($linked_orders_count > 0) {
                $message .= '. ' . $linked_orders_count . ' frühere Bestellung(en) wurden deinem Account zugeordnet.';
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'linked_orders' => $linked_orders_count
            ]);
        } else {
            log_security_event('login_failed', ['reason' => 'wrong_password', 'email' => $email]);
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Falsches Passwort']);
        }
    } else {
        log_security_event('login_failed', ['reason' => 'user_not_found', 'email' => $email]);
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Benutzer nicht gefunden']);
    }
    exit;
}

// ============================================
// LOGOUT - Benutzer abmelden
// ============================================
elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout erfolgreich']);
    exit;
}

// ============================================
// GET CURRENT USER
// ============================================
elseif ($action === 'get_current_user' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'] ?? '',
                'first_name' => $_SESSION['first_name'] ?? '',
                'last_name' => $_SESSION['last_name'] ?? '',
                'is_admin' => $_SESSION['is_admin'] ?? false
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Nicht angemeldet']);
    }
    exit;
}

// ============================================
// INVALID ACTION
// ============================================
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültige Aktion']);
    exit;
}
