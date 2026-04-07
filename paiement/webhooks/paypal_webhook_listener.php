<?php
// webhooks/paypal_webhook_listener.php
require_once '../config.php'; // Remonter de deux niveaux

// Activer la journalisation pour les webhooks
function log_webhook_message($message) {
    file_put_contents(__DIR__ . '/../logs/paypal_webhook_log.txt', date('[Y-m-d H:i:s]') . ' ' . $message . PHP_EOL, FILE_APPEND);
}

log_webhook_message("Webhook reçu. Requête POST: " . file_get_contents('php://input'));

// Obtenir le contenu brut de la requête POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_webhook_message("Erreur de décodage JSON: " . json_last_error_msg());
    http_response_code(400); // Bad Request
    exit();
}

// --- Validation du Webhook (très important !) ---
// Vous devez vérifier la signature du webhook pour vous assurer qu'il provient bien de PayPal.
// Cela implique d'envoyer la requête POST originale et les en-têtes à PayPal pour validation.
// Pour un exemple complet de validation, consultez la documentation officielle de PayPal.
// Exemple simplifié (non sécurisé sans validation de signature):
/*
$headers_to_validate = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_PAYPAL_') === 0 || $key === 'HTTP_X_PAYPAL_WEBHOOK_ID') {
        $headers_to_validate[str_replace('HTTP_', '', $key)] = $value;
    }
}
// Appel à PayPal pour valider le webhook...
// Si la validation échoue: http_response_code(403); exit();
*/

$event_type = $data['event_type'] ?? '';
$resource = $data['resource'] ?? [];

log_webhook_message("Type d'événement: " . $event_type);

switch ($event_type) {
    case 'CHECKOUT.ORDER.COMPLETED':
    case 'PAYMENT.CAPTURE.COMPLETED':
        $order_id = $resource['id'] ?? $resource['billing_agreement_id'] ?? null;
        $capture_id = $resource['id'] ?? null; // Pour PAYMENT.CAPTURE.COMPLETED

        // L'ID de la réservation peut être dans la propriété 'custom_id' de purchase_units
        $reservation_id = $resource['purchase_units'][0]['custom_id'] ?? null;
        if ($reservation_id === null) {
            // Si non trouvé, essayer via 'reference_id'
            $reservation_id = str_replace('RESERVATION_', '', $resource['purchase_units'][0]['reference_id'] ?? '');
        }


        $payment_status = $resource['status'] ?? 'UNKNOWN';
        $amount_paid = $resource['amount']['value'] ?? ($resource['seller_receivable_breakdown']['gross_amount']['value'] ?? 0);
        $currency_code = $resource['amount']['currency_code'] ?? ($resource['seller_receivable_breakdown']['gross_amount']['currency_code'] ?? 'EUR');
        $payer_email = $resource['payer']['email_address'] ?? '';

        // Si vous avez déjà géré le paiement dans capture_paypal_order.php,
        // cette étape peut servir de re-vérification ou de gestion des cas non gérés.
        // Utilisez l'ID de capture pour vérifier les doublons.

        $conn->begin_transaction();
        try {
            // Vérifier si le paiement est déjà enregistré via l'ID de capture
            $stmt_check_capture = $conn->prepare("SELECT idPaiement FROM paiements WHERE paypal_capture_id = ?");
            $stmt_check_capture->bind_param("s", $capture_id);
            $stmt_check_capture->execute();
            $stmt_check_capture->store_result();

            if ($stmt_check_capture->num_rows == 0) {
                // C'est un nouveau paiement ou une notification pour un paiement non encore enregistré (si capture_paypal_order.php a échoué)
                $stmt_insert = $conn->prepare("INSERT INTO paiements (idReservation, paypal_order_id, paypal_capture_id, payer_email, amount_paid, currency_code, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("isssdss", $reservation_id, $order_id, $capture_id, $payer_email, $amount_paid, $currency_code, $payment_status);
                if (!$stmt_insert->execute()) {
                    throw new Exception("Erreur insertion webhook paiement: " . $stmt_insert->error);
                }
                $stmt_insert->close();

                // Mettre à jour le statut de la réservation
                $stmt_update_reservation = $conn->prepare("UPDATE reservations SET statut = 'payée' WHERE idReservation = ?");
                $stmt_update_reservation->bind_param("i", $reservation_id);
                if (!$stmt_update_reservation->execute()) {
                    throw new Exception("Erreur mise à jour webhook réservation: " . $stmt_update_reservation->error);
                }
                $stmt_update_reservation->close();

                log_webhook_message("Paiement par webhook enregistré et réservation mise à jour pour ID: " . $reservation_id . ", Capture ID: " . $capture_id);
            } else {
                log_webhook_message("Webhook pour Capture ID: " . $capture_id . " déjà traité.");
            }
            $stmt_check_capture->close();
            $conn->commit();

        } catch (Exception $e) {
            $conn->rollback();
            log_webhook_message("Erreur critique webhook: " . $e->getMessage());
            http_response_code(500); // Erreur interne du serveur
            exit();
        }
        break;

    case 'PAYMENT.CAPTURE.REFUNDED':
        // Gérer les remboursements
        $capture_id = $resource['id'] ?? null;
        $refund_amount = $resource['amount']['value'] ?? 0;
        $refund_status = $resource['status'] ?? 'UNKNOWN';

        // Mettre à jour le statut du paiement ou enregistrer le remboursement
        log_webhook_message("Remboursement reçu pour Capture ID: " . $capture_id . ", Montant: " . $refund_amount);
        // Mettre à jour la table 'paiements' ou 'remboursements'
        break;

    // Ajoutez d'autres types d'événements à gérer si nécessaire
    default:
        log_webhook_message("Type d'événement non géré: " . $event_type);
        break;
}

http_response_code(200); // Important: Toujours renvoyer un code 200 OK pour que PayPal sache que le webhook a été reçu.
$conn->close();
?>