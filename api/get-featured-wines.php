<?php
// api/get-featured-wines.php - Liste der Featured Wines laden (für Admin)

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

// Sicherheit: Nur Admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die(json_encode([]));
}

$result = $db->query("SELECT id, name, producer, price FROM wines WHERE is_featured = 1 ORDER BY name ASC");

$wines = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $wines[] = $row;
    }
}

die(json_encode($wines));
?>