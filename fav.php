<?php
session_start();
if($_SESSION['id_client']===null){
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
    .hotels-page {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
    }

    .hotels-page .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 20px;
    }

    .hotels-page section {
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

    .hotels-page .img-section {
      flex: 1.2;
      position: relative;
      min-width: 280px;
      max-height: 200px;
      overflow: hidden;
    }

    .hotels-page .img-section img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .hotels-page .details-section {
      flex: 2;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .hotels-page .details-section p {
      margin: 4px 0;
      font-size: 14px;
    }

    .hotels-page .details-section p:first-child {
      font-size: 18px;
      font-weight: bold;
    }

    .hotels-page .reserve-section {
      flex: 1;
      background-color: #e6f4ea;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      align-items: center;
      text-align: center;
      border-left: 1px solid #ddd;
    }

    .hotels-page .reserve-section p {
      font-size: 14px;
      color: #1a7f37;
      margin: 4px 0;
    }

    .hotels-page .btn-reserver {
      text-decoration: none;
      background-color: #0a9500;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      display: inline-block;
    }

    .hotels-page .btn-reserver:hover {
      background-color: #067400;
    }


    .activites-page section {
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

    .activites-page .img-section {
      flex: 1.2;
      position: relative;
      min-width: 280px;
      max-height: 200px;
      overflow: hidden;
    }

    .activites-page .img-section img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .activites-page .details-section {
      flex: 2;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .activites-page .details-section p {
      margin: 4px 0;
      font-size: 14px;
    }

    .activites-page .details-section p:first-child {
      font-size: 18px;
      font-weight: bold;
    }

    .activites-page .reserve-section {
      flex: 1;
      background-color: #e6ecf4;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      align-items: center;
      text-align: center;
      border-left: 1px solid #ddd;
    }

    .activites-page .reserve-section p {
      font-size: 14px;
      color: #0032bd;
      margin: 4px 0;
    }

    .activites-page .btn-reserver {
      text-decoration: none;
      background-color: #002895;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      display: inline-block;
    }

    .activites-page .btn-reserver:hover {
      background-color: #001f75;
    }
  </style>
</head>
<body>

<div class="hotels-page">
  <div class="container">

    <?php
      $q = "SELECT * FROM favoris WHERE idClient = ?";
      $stmt = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_client']);
      if(mysqli_stmt_execute($stmt)){
        $res = mysqli_stmt_get_result($stmt);
        while($l = mysqli_fetch_assoc($res)){
          
          if($l['idActivite']==null){
            $q = "SELECT h.*, i.cheminImg, c.libelle AS classe , ch.* FROM imagehotel i
                  INNER JOIN hotels h ON h.idHotel = i.idHotel
                  INNER JOIN classes c ON c.idClasse = h.idClasse
                  INNER JOIN chambres ch ON ch.idHotel = h.idHotel
                  WHERE i.cheminImg LIKE '%apercu%'
                  AND ch.libelle = 'Standard'
                  AND h.idHotel = ".$l['idHotel'];

            $nbp = $_POST['personnes'] ?? null;
            $depart = $_POST['depart'] ?? null;
            $arrivee = $_POST['arrivee'] ?? null;

            $r = mysqli_query($conn, $q);
            if($r){
              while($l = mysqli_fetch_assoc($r)){
                echo "
                  <section>
                    <div class='img-section'>
                      <img src='".$l['cheminImg']."' alt='Photo d'hôtel'>
                    </div>

                    <div class='details-section'>
                      <p>".$l['nomHotel']."</p>
                      <p><strong>".$l['classe']."</strong></p>
                      <p>".$l['adresseH']."</p>
                      <p>".$l['villeH']."</p>
                    </div>

                    <div class='reserve-section'>
                      <p><strong>".$l['prix']." Dhs / nuit</strong></p>
                      <p>Annulation gratuite</p>
                      <a href='hotel.php?idHotel=".$l['idHotel']."&nbp=".$nbp."&depart=".$depart."&arrivee=".$arrivee."' class='btn-reserver'>Voir l'offre</a>
                    </div>
                  </section>
                ";
              }
            }
          }
          else{
            $q = "SELECT a.*, i.cheminImgA, t.nomType FROM imageActivite i
                  INNER JOIN activites a ON a.idActivite = i.idActivite
                  INNER JOIN typeactivite t ON t.idType = a.idType 
                  WHERE i.cheminImgA LIKE '%apercu%'
                  AND a.idActivite = ".$l['idActivite'];

            $nbp = $_POST['personnes'] ?? null;
            $date = $_POST['depart'] ?? null;

            $res = mysqli_query($conn, $q);
            if($res){
              while($l = mysqli_fetch_assoc($res)){
                echo "
                  <section>

                    <div class='img-section'>
                      <img src='".htmlspecialchars($l['cheminImgA'])."' alt='Image activité'>
                    </div>

                    <div class='details-section'>
                      <p>".htmlspecialchars($l['nomActivite'])."</p>
                      <p>".htmlspecialchars($l['nomType'])."</p>
                      <p>".htmlspecialchars($l['descriptionA'])."</p>
                      <p>".htmlspecialchars($l['villeA'])."</p>
                    </div>

                    <div class='reserve-section'>
                      <p><strong>".htmlspecialchars($l['prixA'])." Dhs</strong></p>
                      <p>Annulation gratuite</p>
                      <a href='activite.php?idActivite=".intval($l['idActivite'])."&nbp=".$nbp."&date=".$date."' class='btn-reserver'>Voir l'offre</a>
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





    .activites-page .reserve-section {
      flex: 1;
      background-color: #e6ecf4;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      align-items: center;
      text-align: center;
      border-left: 1px solid #ddd;
    }

    .activites-page .reserve-section p {
      font-size: 14px;
      color: #0032bd;
      margin: 4px 0;
    }

    .activites-page .btn-reserver {
      text-decoration: none;
      background-color: #002895;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      display: inline-block;
    }

    .activites-page .btn-reserver:hover {
      background-color: #001f75;
    }
