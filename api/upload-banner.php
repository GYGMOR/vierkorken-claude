<?php
// api/upload-banner.php - Banner und About-Bilder hochladen

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Sicherheit: Nur Admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die(json_encode(['success' => false, 'error' => 'Nicht autorisiert']));
}

if (!isset($_FILES['file']) || !isset($_POST['type'])) {
    die(json_encode(['success' => false, 'error' => 'Keine Datei oder Typ angegeben']));
}

$type = $_POST['type'];
$file = $_FILES['file'];

// Validierung
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    die(json_encode(['success' => false, 'error' => 'Nur Bilder erlaubt (jpg, png, gif, webp)']));
}

if ($file['size'] > 10 * 1024 * 1024) { // 10MB max
    die(json_encode(['success' => false, 'error' => 'Datei zu groß (max 10MB)']));
}

// Ordner erstellen falls nicht vorhanden
$upload_dir = '../assets/images/banners/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Datei speichern
$new_name = time() . '_' . rand(10000, 99999) . '.' . $ext;
$upload_path = $upload_dir . $new_name;
$relative_path = 'assets/images/banners/' . $new_name;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    die(json_encode(['success' => false, 'error' => 'Fehler beim Hochladen']));
}

// In Database speichern
$setting_key = '';
if ($type === 'header_banner') {
    $setting_key = 'header_banner_image';
} elseif ($type === 'about_section') {
    $setting_key = 'about_section_image';
} else {
    die(json_encode(['success' => false, 'error' => 'Unbekannter Bildtyp']));
}

// Altes Bild löschen falls vorhanden
$old_path_result = $db->query("SELECT value FROM settings WHERE key_name = '$setting_key'");
if ($old_path_result && $row = $old_path_result->fetch_assoc()) {
    $old_file = '../' . $row['value'];
    if (file_exists($old_file)) {
        @unlink($old_file);
    }
}

// Neuen Pfad speichern
if (update_setting($setting_key, $relative_path)) {
    die(json_encode([
        'success' => true,
        'message' => 'Bild hochgeladen!',
        'path' => $relative_path
    ]));
} else {
    die(json_encode(['success' => false, 'error' => 'Fehler beim Speichern in der Datenbank']));
}
?>
