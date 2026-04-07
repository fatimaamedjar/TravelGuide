<?php
session_start();
require_once 'includes/connexion.php';

$idClient     = $_SESSION['id_client'] ?? null;
$idActivite   = $_GET['idActivite'] ?? null;
$idChambre    = $_GET['idChambre'] ?? null;
$date         = $_GET['date'] ?? null;
$depart       = $_GET['depart'] ?? null;
$arrivee      = $_GET['arrivee'] ?? null;
$nbPersonnes  = $_GET['nbp'] ?? null;
$prix         = $_GET['prix'] ?? null;
$capacite     = $_GET['capacite'] ?? null;

if ($idClient === null) {
    header("Location: compte/index.php");
    exit();
}

if (
    empty($nbPersonnes) ||
    ((empty($depart) || empty($arrivee)) && $idChambre !== null) ||  
    (empty($date) && $idActivite !== null)                          
) {
    header("Location: index.php?erreur=infos-incompletes#Reservation");
    exit();
}

$prixTicket = 0;

if ($idChambre !== null && $arrivee !== null && $depart !== null && $nbPersonnes !== null && $prix !== null && $capacite !== null) {
    
    $prixTicket = ($nbPersonnes > $capacite)
        ? $prix * ceil($nbPersonnes / $capacite)
        : $prix;

    $query = "INSERT INTO reservations(idClient, idChambre, dateDepart, dateArrive, nbPersonnes, prix, statut, typeres)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);
    $statut = 'en_cours';
    $typeres = 'Hotel';

    mysqli_stmt_bind_param($stmt, "iissidss", $idClient, $idChambre, $arrivee, $depart, $nbPersonnes, $prixTicket, $statut, $typeres);

    if (mysqli_stmt_execute($stmt)) {
        $idReservation = mysqli_insert_id($conn);
        header("Location: paiement/pay_reservation_api.php?idRes=" . $idReservation);
        exit();
    }

} elseif ($idActivite !== null && $date !== null && $nbPersonnes !== null && $prix !== null) {

    $prixTicket = $prix * $nbPersonnes;

    $query = "INSERT INTO reservations(idClient, idActivite, dateArrive, nbPersonnes, prix, statut, typeres)
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);
    $statut = 'en_cours';
    $typeres = 'Activite';

    mysqli_stmt_bind_param($stmt, "iisidss", $idClient, $idActivite, $date, $nbPersonnes, $prixTicket, $statut, $typeres);

    if (mysqli_stmt_execute($stmt)) {
        $idReservation = mysqli_insert_id($conn);
        header("Location: paiement/pay_reservation_api.php?idRes=" . $idReservation);
        exit();
    }
}

header("Location: index.php?erreur=reservation-invalide");
exit();
?>
