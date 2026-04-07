<?php
// api/capture_paypal_order.php
require_once '../config.php'; // Remonter d'un niveau

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$order_id = $input['orderID'] ?? null; // ID de commande PayPal
$reservation_id = $input['reservation_id'] ?? null; // ID de réservation de votre DB

if (!$order_id || !$reservation_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de commande PayPal ou ID de réservation manquant.']);
    exit();
}

$access_token = getPayPalAccessToken();

if (!$access_token) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossible d\'obtenir l\'Access Token PayPal.']);
    exit();
}

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token,
    'PayPal-Request-Id: ' . uniqid('order_capture_') // Idempotence key
];

// Appeler l'API de capture
$result = callPayPalApi('POST', "/v2/checkout/orders/{$order_id}/capture", $headers);

if (isset($result['response']['status']) && $result['response']['status'] === 'COMPLETED') {
    $capture_details = $result['response']['purchase_units'][0]['payments']['captures'][0];
    $paypal_capture_id = $capture_details['id'];
    $payment_status = $capture_details['status']; // Ex: COMPLETED
    $amount_paid = $capture_details['amount']['value'];
    $currency_code = $capture_details['amount']['currency_code'];
    $payer_email = $result['response']['payer']['email_address'];

    // --- Enregistrement du paiement et mise à jour de la réservation ---
    $conn->begin_transaction();
    try {
        // 1. Vérifier si l'ID de capture PayPal n'est pas déjà enregistré (prévention des doublons)
        $stmt_check = $conn->prepare("SELECT idPaiement FROM paiements WHERE paypal_capture_id = ?");
        $stmt_check->bind_param("s", $paypal_capture_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $conn->rollback(); // Annuler la transaction
            http_response_code(200); // Répondre 200 car l'opération est déjà traitée
            echo json_encode(['status' => 'ALREADY_COMPLETED', 'message' => 'Paiement déjà enregistré.', 'id' => $paypal_capture_id]);
            exit();
        }
        $stmt_check->close();

        // 2. Insérer le paiement dans la table `paiements`
        $stmt_insert = $conn->prepare("INSERT INTO paiements (idReservation, paypal_order_id, paypal_capture_id, payer_email, amount_paid, currency_code, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("isssdss", $reservation_id, $order_id, $paypal_capture_id, $payer_email, $amount_paid, $currency_code, $payment_status);
        if (!$stmt_insert->execute()) {
            throw new Exception("Erreur lors de l'insertion du paiement: " . $stmt_insert->error);
        }
        $stmt_insert->close();

        // 3. Mettre à jour le statut de la réservation
        $stmt_update_reservation = $conn->prepare("UPDATE reservations SET statut = 'payée' WHERE idReservation = ?");
        $stmt_update_reservation->bind_param("i", $reservation_id);
        if (!$stmt_update_reservation->execute()) {
            throw new Exception("Erreur lors de la mise à jour de la réservation: " . $stmt_update_reservation->error);
        }
        $stmt_update_reservation->close();

        $conn->commit(); // Commiter la transaction
        http_response_code(200);
        echo json_encode(['status' => 'COMPLETED', 'id' => $paypal_capture_id]);

    } catch (Exception $e) {
        $conn->rollback(); // Annuler la transaction en cas d'erreur
        http_response_code(500);
        echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
    }

} else {
    // Gérer les cas où la capture n'est pas COMPLETED
    http_response_code($result['http_code'] ?: 500);
    echo json_encode(['status' => $result['response']['status'] ?? 'FAILED', 'details' => $result['response'] ?? '']);
}
$conn->close();
?>