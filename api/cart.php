<?php
// api/cart.php - Warenkorb API Handler
// Kommunikation zwischen Frontend (localStorage) und Backend

header('Content-Type: application/json');

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? '';
$response = ['success' => false, 'error' => 'Unbekannte Aktion'];

try {
    if ($action === 'add') {
        // Produkt zum Warenkorb hinzufügen
        $wine_id = (int)($_POST['wine_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($wine_id <= 0) {
            throw new Exception('Ungültige Wein-ID');
        }
        
        if ($quantity <= 0) {
            throw new Exception('Ungültige Menge');
        }
        
        // Wein aus DB prüfen
        $wine = get_wine_by_id($wine_id);
        if (!$wine) {
            throw new Exception('Wein nicht gefunden');
        }
        
        if ($wine['stock'] < $quantity) {
            throw new Exception('Nicht genug auf Lager');
        }
        
        // In DB speichern (optional, für Bestellungen später)
        // Hier würde man die cart_items Tabelle updaten
        
        $response = [
            'success' => true,
            'message' => 'In den Warenkorb hinzugefügt',
            'wine' => [
                'id' => $wine['id'],
                'name' => $wine['name'],
                'price' => $wine['price'],
                'quantity' => $quantity
            ]
        ];
    }
    
    elseif ($action === 'get_count') {
        // Anzahl der Items im Warenkorb abrufen
        // Das ist vom Frontend (localStorage) abhängig
        $response = [
            'success' => true,
            'count' => 0  // Frontend zählt selbst mit cart.js
        ];
    }
    
    elseif ($action === 'validate') {
        // Prüfe ob Wein noch verfügbar ist
        $wine_id = (int)($_GET['wine_id'] ?? 0);
        
        if ($wine_id <= 0) {
            throw new Exception('Ungültige Wein-ID');
        }
        
        $wine = get_wine_by_id($wine_id);
        if (!$wine) {
            throw new Exception('Wein nicht gefunden');
        }
        
        $response = [
            'success' => true,
            'available' => $wine['stock'] > 0,
            'stock' => $wine['stock']
        ];
    }
    
    else {
        throw new Exception('Unbekannte Aktion: ' . $action);
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    http_response_code(400);
}

echo json_encode($response);
?>