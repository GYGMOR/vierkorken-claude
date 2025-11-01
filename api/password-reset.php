<?php
// api/password-reset.php - Password Reset API
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'request':
            // Request Password Reset
            $data = json_decode(file_get_contents('php://input'), true);
            $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

            if (!$email) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Ungültige E-Mail-Adresse']);
                break;
            }

            // Check if user exists
            $email_safe = $db->real_escape_string($email);
            $result = $db->query("SELECT id FROM users WHERE email = '$email_safe'");

            if (!$result || $result->num_rows === 0) {
                // Don't reveal if user exists - security best practice
                echo json_encode([
                    'success' => true,
                    'message' => 'Falls ein Konto mit dieser E-Mail existiert, haben wir einen Reset-Link gesendet.'
                ]);
                break;
            }

            $user = $result->fetch_assoc();
            $user_id = $user['id'];

            // Generate secure token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token to database
            $db->query("INSERT INTO password_reset_tokens (user_id, email, token, expires_at)
                       VALUES ($user_id, '$email_safe', '$token', '$expires_at')");

            // Create reset link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $reset_link = $protocol . '://' . $host . dirname($_SERVER['SCRIPT_NAME']) . '/../?page=reset-password&token=' . $token;

            // Send email (PHP mail function - kann durch SMTP ersetzt werden)
            $subject = 'Passwort zurücksetzen - Vier Korken';
            $message = "
Hallo,

Du hast angefragt, dein Passwort zurückzusetzen.

Klicke auf den folgenden Link, um ein neues Passwort zu setzen:
$reset_link

Dieser Link ist 1 Stunde gültig.

Falls du diese Anfrage nicht gestellt hast, ignoriere diese E-Mail.

Grüsse,
Das Vier Korken Team
            ";

            $headers = "From: noreply@vierkorken.ch\r\n";
            $headers .= "Reply-To: support@vierkorken.ch\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            $email_sent = mail($email, $subject, $message, $headers);

            if ($email_sent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Wir haben dir einen Link zum Zurücksetzen des Passworts per E-Mail gesendet.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'E-Mail konnte nicht gesendet werden. Bitte kontaktiere den Support.'
                ]);
            }
            break;

        case 'verify':
            // Verify Token
            $token = $_GET['token'] ?? '';
            if (empty($token)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Kein Token angegeben']);
                break;
            }

            $token_safe = $db->real_escape_string($token);
            $result = $db->query("SELECT * FROM password_reset_tokens
                                 WHERE token = '$token_safe'
                                 AND used = 0
                                 AND expires_at > NOW()");

            if ($result && $result->num_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Token ist gültig']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Ungültiger oder abgelaufener Token']);
            }
            break;

        case 'reset':
            // Reset Password
            $data = json_decode(file_get_contents('php://input'), true);
            $token = $data['token'] ?? '';
            $new_password = $data['password'] ?? '';

            if (empty($token) || empty($new_password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Token und Passwort erforderlich']);
                break;
            }

            // Validate password strength
            if (strlen($new_password) < 8) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Passwort muss mindestens 8 Zeichen lang sein']);
                break;
            }

            // Verify token
            $token_safe = $db->real_escape_string($token);
            $result = $db->query("SELECT * FROM password_reset_tokens
                                 WHERE token = '$token_safe'
                                 AND used = 0
                                 AND expires_at > NOW()");

            if (!$result || $result->num_rows === 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Ungültiger oder abgelaufener Token']);
                break;
            }

            $reset_token = $result->fetch_assoc();
            $user_id = $reset_token['user_id'];

            // Hash new password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $db->query("UPDATE users SET password_hash = '$password_hash' WHERE id = $user_id");

            // Mark token as used
            $db->query("UPDATE password_reset_tokens SET used = 1 WHERE id = {$reset_token['id']}");

            echo json_encode([
                'success' => true,
                'message' => 'Passwort erfolgreich zurückgesetzt. Du kannst dich jetzt anmelden.'
            ]);
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
