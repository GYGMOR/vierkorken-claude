<?php
// api/auth.php - Authentication API

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ============================================
// REGISTER - Neuen Benutzer erstellen
// ============================================
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    // Validierung
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ungültige E-Mail-Adresse']);
        exit;
    }

    if (empty($password) || strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Passwort muss mindestens 6 Zeichen lang sein']);
        exit;
    }

    if (empty($first_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Vorname erforderlich']);
        exit;
    }

    // Escape values
    $email_safe = $db->real_escape_string($email);
    $first_name_safe = $db->real_escape_string($first_name);
    $last_name_safe = $db->real_escape_string($last_name);

    // Check if user already exists
    $check = $db->query("SELECT id FROM users WHERE email = '$email_safe'");
    if ($check && $check->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Diese E-Mail-Adresse ist bereits registriert']);
        exit;
    }

    // Create password hash
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $sql = "INSERT INTO users (email, password, first_name, last_name, created_at)
            VALUES ('$email_safe', '$password_hash', '$first_name_safe', '$last_name_safe', NOW())";

    if ($db->query($sql)) {
        $user_id = $db->insert_id;

        // Auto-login after registration
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;

        echo json_encode([
            'success' => true,
            'message' => 'Account erfolgreich erstellt!',
            'user_id' => $user_id
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
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'E-Mail und Passwort erforderlich']);
        exit;
    }

    $email_safe = $db->real_escape_string($email);

    $result = $db->query("SELECT id, email, password, first_name, last_name FROM users WHERE email = '$email_safe'");

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            // Check if admin
            $admin_check = $db->query("SELECT id FROM admins WHERE user_id = {$user['id']}");
            if ($admin_check && $admin_check->num_rows > 0) {
                $_SESSION['is_admin'] = true;
            }

            echo json_encode(['success' => true, 'message' => 'Login erfolgreich']);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Falsches Passwort']);
        }
    } else {
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
