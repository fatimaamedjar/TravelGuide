<?php
// config.php

// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'travelguide_t');

// Paramètres PayPal API (Sandbox)
define('PAYPAL_SANDBOX_CLIENT_ID', 'ASLinaF-LswSkKsVX58m2_UPfw82Qfqp2xSztAnR7H-OpNBibNrsQ5KBz5NJfkqsdR-jSZGQCL4r39-E'); // Remplacez par votre Client ID Sandbox
define('PAYPAL_SANDBOX_CLIENT_SECRET', 'ECWKzX1P-NxNS2P7AK0b6gKl8Hb8i35UabvkW5rMrUe6MeEOADNXttlI1pfxKmJdu0UBG6X4x3LJBFbo'); // Remplacez par votre Client Secret Sandbox

// Basculer entre Sandbox et Live
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' ou 'live'

if (PAYPAL_MODE === 'sandbox') {
    define('PAYPAL_CLIENT_ID', PAYPAL_SANDBOX_CLIENT_ID);
    define('PAYPAL_CLIENT_SECRET', PAYPAL_SANDBOX_CLIENT_SECRET);
    define('PAYPAL_API_BASE_URL', 'https://api-m.sandbox.paypal.com'); // API REST Sandbox
    define('PAYPAL_CHECKOUT_URL', 'https://www.sandbox.paypal.com/checkoutnow'); // URL de redirection pour le checkout
}

// URLs de redirection front-end (depuis PayPal vers votre site)
// Utilisez l'URL de base de votre site (avec ngrok si en local)
define('BASE_URL', 'http://localhost/pfe/'); // ex: 'http://yourdomain.com' ou 'http://your_ngrok_url.ngrok-free.app'
define('FRONTEND_RETURN_URL', BASE_URL . 'paiement/success.php');
define('FRONTEND_CANCEL_URL', BASE_URL . 'paiement/cancel.php');

// URL pour les Webhooks (si vous les implémentez, recommandé pour la production)
define('PAYPAL_WEBHOOK_URL', BASE_URL . '/webhooks/paypal_webhook_listener.php');

// Connexion à la base de données
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Fonction utilitaire pour les appels cURL
function callPayPalApi($method, $endpoint, $headers = [], $body = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_API_BASE_URL . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($body) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body) : $body);
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        // Enregistrer l'erreur de cURL
        file_put_contents(__DIR__ . '/logs/paypal_api_log.txt', date('[Y-m-d H:i:s]') . " cURL Error ($endpoint): " . $error . PHP_EOL, FILE_APPEND);
        return ['error' => $error, 'http_code' => $http_code];
    }
    
    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE && $response) {
        // Si ce n'est pas du JSON valide, enregistrer la réponse brute
        file_put_contents(__DIR__ . '/logs/paypal_api_log.txt', date('[Y-m-d H:i:s]') . " Non-JSON Response ($endpoint): " . $response . PHP_EOL, FILE_APPEND);
    }
    
    file_put_contents(__DIR__ . '/logs/paypal_api_log.txt', date('[Y-m-d H:i:s]') . " API Call ($endpoint) - HTTP $http_code - Response: " . print_r($decoded_response ?? $response, true) . PHP_EOL, FILE_APPEND);

    return ['response' => $decoded_response, 'http_code' => $http_code];
}

// Fonction pour obtenir l'Access Token PayPal
function getPayPalAccessToken() {
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode(PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET)
    ];
    $body = 'grant_type=client_credentials';

    $result = callPayPalApi('POST', '/v1/oauth2/token', $headers, $body);

    if (isset($result['response']['access_token'])) {
        return $result['response']['access_token'];
    }
    // Gérer l'erreur si l'access token n'est pas obtenu
    file_put_contents(__DIR__ . '/logs/paypal_api_log.txt', date('[Y-m-d H:i:s]') . " Failed to get Access Token. Response: " . print_r($result, true) . PHP_EOL, FILE_APPEND);
    return null;
}

// Assurez-vous que le dossier logs existe et est inscriptible
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

?>