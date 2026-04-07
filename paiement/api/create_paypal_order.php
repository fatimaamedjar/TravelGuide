<?php
// api/create_paypal_order.php
require_once '../config.php'; // Remonter d'un niveau pour inclure config.php

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$reservation_id = $input['reservation_id'] ?? null;
$amount_value = $input['amount'] ?? null;
$currency_code = $input['currency'] ?? 'EUR'; // Devise par défaut

if (!$reservation_id || !$amount_value) {
    http_response_code(400);
    echo json_encode(['error' => 'Données de réservation manquantes.']);
    exit();
}

$access_token = getPayPalAccessToken();

if (!$access_token) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossible d\'obtenir l\'Access Token PayPal.']);
    exit();
}

$order_data = [
    'intent' => 'CAPTURE', // Pour capturer le paiement immédiatement après l'approbation
    'purchase_units' => [[
        'reference_id' => 'RESERVATION_' . $reservation_id, // Référence interne pour votre suivi
        'amount' => [
            'currency_code' => $currency_code,
            'value' => $amount_value
        ],
        'description' => 'Paiement pour Réservation #' . $reservation_id
    ]],
    'application_context' => [
        'return_url' => FRONTEND_RETURN_URL,
        'cancel_url' => FRONTEND_CANCEL_URL,
        'shipping_preference' => 'NO_SHIPPING' // Pas d'adresse de livraison pour une réservation
    ]
];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token,
    'PayPal-Request-Id: ' . uniqid('order_create_') // Idempotence key
];

$result = callPayPalApi('POST', '/v2/checkout/orders', $headers, $order_data);

if (isset($result['response']['id'])) {
    http_response_code($result['http_code']);
    echo json_encode(['id' => $result['response']['id']]); // Renvoie l'ID de la commande PayPal au frontend
} else {
    http_response_code($result['http_code'] ?: 500);
    echo json_encode(['error' => 'Erreur lors de la création de la commande PayPal.', 'details' => $result['response'] ?? '']);
}
?>