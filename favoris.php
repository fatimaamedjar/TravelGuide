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
  <title>Favoris</title>
  <style>
    body, html{
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
    }
    body{
      background-color: #f5f5f5;
    }
    .titre{
      font-family: 'Arial';
      color: #282834;
      text-align: center;
      margin-bottom: 50px;
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

    .favs-page .img-section {
      flex: 1.2;
      position: relative;
      min-width: 280px;
      max-height: 200px;
      overflow: hidden;
    }

    .favs-page .img-section img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .favs-page .details-section {
      flex: 2;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .favs-page .details-section p {
      margin: 4px 0;
      font-size: 14px;
    }

    .favs-page .details-section p:first-child {
      font-size: 18px;
      font-weight: bold;
    }

    .favs-page .reserve-section {
      flex: 1;
      background-color:rgb(224, 224, 224);
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      align-items: center;
      text-align: center;
      border-left: 1px solid #ddd;
    }

    .favs-page .reserve-section p {
      font-size: 14px;
      color:rgb(107, 107, 107);
      margin: 4px 0;
    }

    .favs-page .btn-reserver {
      text-decoration: none;
      background-color:rgb(136, 136, 136);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      display: inline-block;
    }

    .favs-page .btn-reserver:hover {
      background-color:rgb(95, 95, 95);
    }
  </style>
</head>
<body>

<div class="favs-page">
  <div class="container">

    <h1 class="titre">Mes Favoris</h1>

    <?php
    $sqlFavoris = "SELECT * FROM favoris WHERE idClient = ?";
    $stmtFavoris = mysqli_prepare($conn, $sqlFavoris);
    mysqli_stmt_bind_param($stmtFavoris, "i", $_SESSION['id_client']);

    if (mysqli_stmt_execute($stmtFavoris)) {
      $resultFavoris = mysqli_stmt_get_result($stmtFavoris);

      while ($favori = mysqli_fetch_assoc($resultFavoris)) {

        if (is_null($favori['idActivite'])) {
          $idHotel = intval($favori['idHotel']);
          $sqlHotel = "SELECT h.*, i.cheminImg, c.libelle AS classe, ch.* 
                       FROM imagehotel i
                       INNER JOIN hotels h ON h.idHotel = i.idHotel
                       INNER JOIN classes c ON c.idClasse = h.idClasse
                       INNER JOIN chambres ch ON ch.idHotel = h.idHotel
                       WHERE i.cheminImg LIKE '%apercu%'
                         AND ch.libelle = 'Standard'
                         AND h.idHotel = ?";

          $stmtHotel = mysqli_prepare($conn, $sqlHotel);
          mysqli_stmt_bind_param($stmtHotel, "i", $idHotel);

          $nbp = $_POST['personnes'] ?? '';
          $depart = $_POST['depart'] ?? '';
          $arrivee = $_POST['arrivee'] ?? '';

          if (mysqli_stmt_execute($stmtHotel)) {
            $resultHotel = mysqli_stmt_get_result($stmtHotel);
            while ($hotel = mysqli_fetch_assoc($resultHotel)) {
              echo "
              <section>
                <div class='img-section'>
                  <img src='" . htmlspecialchars($hotel['cheminImg']) . "' alt='Photo d\'hôtel'>
                </div>
                <div class='details-section'>
                  <p>" . htmlspecialchars($hotel['nomHotel']) . "</p>
                  <p><strong>" . htmlspecialchars($hotel['classe']) . "</strong></p>
                  <p>" . htmlspecialchars($hotel['adresseH']) . "</p>
                  <p>" . htmlspecialchars($hotel['villeH']) . "</p>
                </div>
                <div class='reserve-section'>
                  <p><strong>" . htmlspecialchars($hotel['prix']) . " Dhs / nuit</strong></p>
                  <p>Annulation gratuite</p>
                  <a href='hotel.php?idHotel=" . intval($hotel['idHotel']) . "&nbp=" . urlencode($nbp) . "&depart=" . urlencode($depart) . "&arrivee=" . urlencode($arrivee) . "' class='btn-reserver'>Voir l'offre</a>
                </div>
              </section>
              ";
            }
          }

        } else {
          $idActivite = intval($favori['idActivite']);
          $sqlActivite = "SELECT a.*, i.cheminImgA, t.nomType 
                          FROM imageActivite i
                          INNER JOIN activites a ON a.idActivite = i.idActivite
                          INNER JOIN typeactivite t ON t.idType = a.idType 
                          WHERE i.cheminImgA LIKE '%apercu%'
                            AND a.idActivite = ?";

          $stmtActivite = mysqli_prepare($conn, $sqlActivite);
          mysqli_stmt_bind_param($stmtActivite, "i", $idActivite);

          $nbp = $_POST['personnes'] ?? '';
          $date = $_POST['depart'] ?? '';

          if (mysqli_stmt_execute($stmtActivite)) {
            $resultActivite = mysqli_stmt_get_result($stmtActivite);
            while ($activite = mysqli_fetch_assoc($resultActivite)) {
              echo "
              <section>
                <div class='img-section'>
                  <img src='" . htmlspecialchars($activite['cheminImgA']) . "' alt='Image activité'>
                </div>
                <div class='details-section'>
                  <p>" . htmlspecialchars($activite['nomActivite']) . "</p>
                  <p>" . htmlspecialchars($activite['nomType']) . "</p>
                  <p>" . htmlspecialchars($activite['descriptionA']) . "</p>
                  <p>" . htmlspecialchars($activite['villeA']) . "</p>
                </div>
                <div class='reserve-section'>
                  <p><strong>" . htmlspecialchars($activite['prixA']) . " Dhs</strong></p>
                  <p>Annulation gratuite</p>
                  <a href='activite.php?idActivite=" . intval($activite['idActivite']) . "&nbp=" . urlencode($nbp) . "&date=" . urlencode($date) . "' class='btn-reserver'>Voir l'offre</a>
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
