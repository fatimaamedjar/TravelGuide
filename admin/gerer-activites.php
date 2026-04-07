<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Hôtels</title>
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
      background-color:rgb(244, 230, 230);
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
      color:rgb(127, 26, 26);
      margin: 4px 0;
    }

    .hotels-page .btn-reserver {
      text-decoration: none;
      background-color:rgb(149, 0, 0);
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
      background-color:rgb(116, 0, 0);
    }

    .rtr-btn{
      text-decoration: none;
      color: white;
      background-color: #7fc142;
      font-weight: bold;
      font-family: 'Arial';
      padding: 15px 20px;
      position: absolute;
      top: 20px;
      left: 20px;
      border-radius: 10px;
    }
    .rtr-btn i{
      margin-right: 10px;
    }
  </style>
</head>
<body>

<a class="rtr-btn" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Retour à l'acceuil</a>

<div class="hotels-page">
  <div class="container">

    <?php
      require_once '../includes/connexion.php';
      $q = "SELECT * FROM imageactivite i
            INNER JOIN activites a ON a.idActivite = i.idActivite
            INNER JOIN typeactivite t ON t.idType = a.idType
            WHERE i.cheminImgA LIKE '%apercu%'";

      $res = mysqli_query($conn, $q);
      if($res){
        while($l = mysqli_fetch_assoc($res)){
          echo "
            <section>
              <div class='img-section'>
                <img src='../".$l['cheminImgA']."' alt='Photo d'hôtel'>
              </div>

              <div class='details-section'>
                <p>".$l['nomActivite']."</p>
                <p><strong>".$l['nomType']."</strong></p>
                <p>".$l['adresseA']."</p>
                <p>".$l['villeA']."</p>
              </div>

              <div class='reserve-section'>
                <a href='supprimer.php?idActivite=".$l['idActivite']."' class='btn-reserver'>Supprimer</a>
              </div>
            </section>
          ";
        }
      }
    ?>

  </div>
</div>

</body>
</html>