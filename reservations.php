<?php
session_start();
if (!isset($_SESSION['id_client'])) {
  header("Location: compte/index.php");
  exit();
}
include_once 'includes/entete.html';
require_once './includes/connexion.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réservations</title>
  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
    }
    body {
      background-color: #f5f5f5;
    }
    .titre {
      font-family: 'Arial';
      color: #282834;
      text-align: center;
      margin: 50px 0;
    }
    .favs-page {
      font-family: Arial, sans-serif;
    }
    .favs-page .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 20px;
    }
    .favs-page section {
      display: flex;
      justify-content: space-between;
      background-color: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      max-width: 1000px;
      height: 150px;
      margin-bottom: 20px;
    }
    .img-section {
      flex: 1.2;
      position: relative;
      min-width: 280px;
      max-height: 200px;
      overflow: hidden;
    }
    .img-section img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .details-section {
      flex: 2;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .details-section p {
      margin: 4px 0;
      font-size: 14px;
    }
    .details-section p:first-child {
      font-size: 18px;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="favs-page">
  <div class="container">
    <h1 class="titre">Mes Réservations</h1>

    <?php
    $sql = "SELECT * FROM reservations WHERE idClient = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_client']);

    if (mysqli_stmt_execute($stmt)) {
      $reservations = mysqli_stmt_get_result($stmt);

      while ($res = mysqli_fetch_assoc($reservations)) {
        if ($res['idActivite'] === null) {
          // Réservation d'hôtel
          $sqlHotel = "SELECT h.nomHotel, h.villeH, h.adresseH, c.libelle AS classe, i.cheminImg
                       FROM chambres ch
                       JOIN hotels h ON ch.idHotel = h.idHotel
                       JOIN classes c ON h.idClasse = c.idClasse
                       JOIN imagehotel i ON h.idHotel = i.idHotel
                       WHERE ch.idChambre = ? AND i.cheminImg LIKE '%apercu%' LIMIT 1";
          $stmtHotel = mysqli_prepare($conn, $sqlHotel);
          mysqli_stmt_bind_param($stmtHotel, "i", $res['idChambre']);
          if (mysqli_stmt_execute($stmtHotel)) {
            $infoHotel = mysqli_stmt_get_result($stmtHotel);
            if ($hotel = mysqli_fetch_assoc($infoHotel)) {
              echo "
              <section>
                <div class='img-section'>
                  <img src='" . htmlspecialchars($hotel['cheminImg']) . "' alt='Photo hôtel'>
                </div>
                <div class='details-section'>
                  <p>" . htmlspecialchars($hotel['nomHotel']) . "</p>
                  <p><strong>" . htmlspecialchars($hotel['classe']) . "</strong></p>
                  <p>" . htmlspecialchars($res['nbPersonnes']) . " personne(s)</p>
                  <p>Du " . htmlspecialchars($res['dateArrive']) . " au " . htmlspecialchars($res['dateDepart']) . "</p>
                  <p><strong>" . htmlspecialchars($res['prix']) . " Dhs</strong></p>
                </div>
              </section>
              ";
            }
          }
        } else {
          // Réservation d’activité
          $sqlActivite = "SELECT a.nomActivite, a.villeA, a.descriptionA, t.nomType, i.cheminImgA
                          FROM activites a
                          JOIN typeactivite t ON a.idType = t.idType
                          JOIN imageActivite i ON a.idActivite = i.idActivite
                          WHERE a.idActivite = ? AND i.cheminImgA LIKE '%apercu%' LIMIT 1";
          $stmtAct = mysqli_prepare($conn, $sqlActivite);
          mysqli_stmt_bind_param($stmtAct, "i", $res['idActivite']);
          if (mysqli_stmt_execute($stmtAct)) {
            $infoAct = mysqli_stmt_get_result($stmtAct);
            if ($act = mysqli_fetch_assoc($infoAct)) {
              echo "
              <section>
                <div class='img-section'>
                  <img src='" . htmlspecialchars($act['cheminImgA']) . "' alt='Image activité'>
                </div>
                <div class='details-section'>
                  <p>" . htmlspecialchars($act['nomActivite']) . "</p>
                  <p>" . htmlspecialchars($act['nomType']) . " - " . htmlspecialchars($act['villeA']) . "</p>
                  <p>" . htmlspecialchars($res['nbPersonnes']) . " personne(s)</p>
                  <p>Date : " . htmlspecialchars($res['dateArrive']) . "</p>
                  <p><strong>" . htmlspecialchars($res['prix']) . " Dhs</strong></p>
                </div>
              </section>
              ";
            }
          }
        }
      }
    }
    ?>
  </div>
</div>

</body>
</html>
