<?php
// api/get-featured-wines.php - Liste der Featured Wines laden (für Admin) - KLARA INTEGRATION

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Sicherheit: Nur Admins
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die(json_encode([]));
}

// Klara Featured Products laden
$featured_wines = klara_get_featured_products();

die(json_encode($featured_wines));
?>