<?php
// api/klara-categories.php - Proxy für Klara Kategorien API

header('Content-Type: application/json');

// Load Klara API Credentials from config
$keys = require_once '../config/keys.php';
$KLARA_API_BASEURL = $keys['klara_api_baseurl'] ?? 'https://api.klara.ch';
$KLARA_API_KEY = $keys['klara_api_key'] ?? '';

if (!$KLARA_API_KEY) {
    http_response_code(502);
    echo json_encode(['error' => 'No API key provided']);
    exit;
}

// API Request
$url = $KLARA_API_BASEURL . '/core/latest/article-categories?limit=1000';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'Accept-Language: de',
    'X-API-KEY: ' . $KLARA_API_KEY
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    http_response_code(502);
    echo json_encode([
        'error' => 'KLARA categories error',
        'status' => $http_code,
        'body' => $response
    ]);
    exit;
}

$raw = json_decode($response, true);

if (!is_array($raw)) {
    $raw = [];
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

echo json_encode($categories);
