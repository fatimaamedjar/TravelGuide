<?php
require_once 'config.php';

/* =======================
   TRAITEMENT PAIEMENT MANUEL (PROCÉDER)
   ======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_payment'])) {

    header('Content-Type: application/json');

    $reservation_id = (int)$_POST['reservation_id'];

    $check = $conn->prepare("SELECT statut FROM reservations WHERE idReservation = ?");
    $check->bind_param("i", $reservation_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Réservation introuvable'
        ]);
        exit;
    }

    $row = $result->fetch_assoc();

    if ($row['statut'] === 'payée') {
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Réservation déjà payée'
        ]);
        exit;
    }

    // Mettre à jour le statut de la réservation
    $update = $conn->prepare("UPDATE reservations SET statut = 'payée' WHERE idReservation = ?");
    $update->bind_param("i", $reservation_id);
    $update->execute();

    echo json_encode([
        'status' => 'COMPLETED',
        'transaction_id' => 'MANUAL_' . time(),
        'redirect_url' => 'success.php?reservation_id=' . $reservation_id // ← redirection après paiement
    ]);
    exit;
}

$reservation = null;
$reservation_id_to_pay = $_GET['idRes'] ?? 0; // ajout d'une sécurité si idRes non défini

$stmt = $conn->prepare("SELECT r.idReservation, r.idClient, r.idChambre, r.idActivite, r.prix, r.statut, c.nomC, c.prenomC 
                        FROM reservations r 
                        INNER JOIN clients c ON c.idClient = r.idClient
                        WHERE idReservation = ?");
$stmt->bind_param("i", $reservation_id_to_pay);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc();
    if ($reservation['statut'] === 'payée' || $reservation['statut'] === 'annulée') {
        echo "Cette réservation est déjà payée ou annulée.";
        $reservation = null;
    }
} else {
    echo "Réservation non trouvée.";
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement Réservation</title>

    <style>
        * { box-sizing: border-box; margin:0; padding:0; }

        body {
            font-family: Arial, sans-serif;
            background:#f5f7fa;
            padding:40px;
        }

        h1 {
            text-align:center;
            margin-bottom:30px;
            color:#2e7d32;
        }

        .reservation-details {
            background:#fff;
            max-width:500px;
            margin:0 auto 30px;
            padding:30px;
            border-radius:10px;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
        }

        #paypal-button-container {
            max-width:350px;
            margin:0 auto;
        }

        #proceedBtn {
            margin-top:20px;
            background:#2e7d32;
            color:#fff;
            border:none;
            padding:12px 30px;
            font-size:16px;
            border-radius:6px;
            cursor:pointer;
        }
    </style>
</head>

<body>

<h1>Paiement de la Réservation</h1>

<?php if ($reservation): ?>

<div class="reservation-details">
    <p><b>ID Réservation :</b> <?php echo $reservation['idReservation']; ?></p>
    <p><b>Client :</b> <?php echo $reservation['prenomC'].' '.$reservation['nomC']; ?></p>
    <p><b>Montant :</b> MAD <?php echo number_format($reservation['prix'],2); ?></p>
    <p><b>Statut :</b> <?php echo $reservation['statut']; ?></p>
</div>

<div id="paypal-button-container"></div>

<div style="text-align:center;">
    <button id="proceedBtn">Procéder (Paiement complet)</button>
</div>

<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=EUR"></script>

<script>
paypal.Buttons({

    createOrder: function () {
        return fetch('api/create_paypal_order.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                reservation_id: <?php echo $reservation['idReservation']; ?>,
                amount: "<?php echo number_format($reservation['prix'],2,'.',''); ?>"
            })
        }).then(res => res.json()).then(order => order.id);
    },

    onApprove: function (data) {
        return fetch('api/capture_paypal_order.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                orderID: data.orderID,
                reservation_id: <?php echo $reservation['idReservation']; ?>
            })
        }).then(res => res.json()).then(details => {
            if(details.status === "COMPLETED"){
                alert("Paiement PayPal réussi");
                window.location.href = "success.php?reservation_id=<?php echo $reservation['idReservation']; ?>";
            } else {
                alert(details.message || "Erreur lors du paiement PayPal");
            }
        });
    }

}).render('#paypal-button-container');

/* ===== BOUTON PROCÉDER ===== */
document.getElementById("proceedBtn").addEventListener("click", function () {

    if(!confirm("Confirmer le paiement complet ?")) return;

    const formData = new FormData();
    formData.append("manual_payment", "1");
    formData.append("reservation_id", "<?php echo $reservation['idReservation']; ?>");

    fetch("", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "COMPLETED"){
            alert("Paiement effectué avec succès");
            // redirection vers success.php
            window.location.href = data.redirect_url;
        } else {
            alert(data.message);
        }
    });
});
</script>

<?php endif; ?>

<?php $conn->close(); ?>

</body>
</html>
