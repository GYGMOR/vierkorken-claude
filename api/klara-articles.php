<?php
// api/klara-articles.php - Proxy für Klara Artikel API

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

// Optional: Kategorie-Filter aus Query-Parameter
$categoryId = isset($_GET['categoryId']) ? trim($_GET['categoryId']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

// API Request - WICHTIG: limit=1000 für alle Artikel
$url = $KLARA_API_BASEURL . '/core/latest/articles?limit=1000';

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
        'error' => 'KLARA articles error',
        'status' => $http_code,
        'body' => $response
    ]);
    exit;
}

$raw = json_decode($response, true);

if (!is_array($raw)) {
    $raw = [];
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
        'image' => null, // Bilder später hinzufügen falls benötigt
        'categories' => $catIds,
        'description' => $a['descriptionDE'] ?? $a['descriptionEN'] ?? '',
        'stock' => 999, // Klara hat keinen Stock in dieser API - setzen wir auf 999
        'producer' => '', // Später aus anderen Feldern mappen falls vorhanden
        'vintage' => null,
        'region' => '',
        'alcohol_content' => null
    ];

    // Kategorie-Filter anwenden
    if ($categoryId !== null && $categoryId !== '') {
        if (!in_array($categoryId, $catIds)) {
            continue; // Artikel überspringen
        }
    }

    // Such-Filter anwenden
    if ($search !== null && $search !== '') {
        $searchLower = mb_strtolower($search);
        $nameLower = mb_strtolower($article['name']);
        $articleNumberLower = mb_strtolower($article['articleNumber'] ?? '');

        if (strpos($nameLower, $searchLower) === false &&
            strpos($articleNumberLower, $searchLower) === false) {
            continue; // Artikel überspringen
        }
    }

    $articles[] = $article;
}

echo json_encode($articles);
