<?php
session_start();

// Nur Admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ?page=home');
    exit;
}

// Toggle
if (isset($_GET['toggle-edit'])) {
    if ($_GET['toggle-edit'] == '1') {
        $_SESSION['edit_mode'] = true;
    } else {
        $_SESSION['edit_mode'] = false;
    }
}

// Zurück zur vorherigen Seite
$referer = $_SERVER['HTTP_REFERER'] ?? '?page=home';
header('Location: ' . $referer);
exit;
?>