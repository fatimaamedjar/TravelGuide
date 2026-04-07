<?php
session_start();

include_once 'includes/entete.html';

require_once 'includes/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'favoris') {
    $idHotel = isset($_POST['idHotel']) ? (int)$_POST['idHotel'] : null;
    $idClient = $_SESSION['id_client'] ?? null;

    if ($idClient && $idHotel) {
        $check = "SELECT * FROM favoris WHERE idClient = ? AND idHotel = ?";
        $stmt = mysqli_prepare($conn, $check);
        mysqli_stmt_bind_param($stmt, "ii", $idClient, $idHotel);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            $insert = "INSERT INTO favoris (idClient, idHotel) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, "ii", $idClient, $idHotel);
            mysqli_stmt_execute($stmt);
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

$idHotel = isset($_GET['idHotel']) ? (int)$_GET['idHotel'] : 1;
$idClient = $_SESSION['id_client'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['commentaire']) && $idClient && $idHotel) {
    $commentaire = trim($_POST['commentaire']);
    $date = date('Y-m-d H:i:s');

    $q = "INSERT INTO commentaires (commentaire, datecomm, idClient, idHotel, idActivite) 
          VALUES (?, ?, ?, ?, NULL)";
    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "ssii", $commentaire, $date, $idClient, $idHotel);
    mysqli_stmt_execute($stmt);

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$hotelInfo = null;
$images = [];

if ($idHotel > 0) {
    $q = "SELECT h.*, i.cheminImg, c.cheminEtoile
          FROM imagehotel i 
          INNER JOIN hotels h ON h.idHotel = i.idHotel
          INNER JOIN classes c ON c.idClasse = h.idClasse
          WHERE h.idHotel = ?";
    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "i", $idHotel);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) > 0) {
        while ($l = mysqli_fetch_assoc($res)) {
            if (!$hotelInfo) {
                $hotelInfo = $l;
            }
            $images[] = $l['cheminImg'];
        }
    } else {
        die("Aucun hôtel trouvé avec cet identifiant.");
    }
} else {
    die("Identifiant d'hôtel non valide.");
}

$isFavorite = false;
if ($idClient) {
    $checkFav = "SELECT * FROM favoris WHERE idClient = ? AND idHotel = ?";
    $stmtFav = mysqli_prepare($conn, $checkFav);
    mysqli_stmt_bind_param($stmtFav, "ii", $idClient, $idHotel);
    mysqli_stmt_execute($stmtFav);
    $resFav = mysqli_stmt_get_result($stmtFav);
    $isFavorite = mysqli_num_rows($resFav) > 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($hotelInfo['nomHotel']) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>

  <style>
    .hotel-page {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #f8f9fa;
    }

    .hotel-page .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 20px;
      text-align: center;
    }

    .hotel-page .header {
      position: relative;
      text-align: left;
    }

    .hotel-page .header h1 {
      margin: 10px 0 5px;
      color: 282834;
    }

    .hotel-page .header p {
      margin: 0;
      color: #555;
      font-size: 15px;
    }

    .hotel-page .stars {
      height: 20px;
    }


    .hotel-page .images {
      display: flex;
      margin-top: 20px;
      gap: 10px;
    }

    .hotel-page .main-image {
      flex: 2;
    }

    .hotel-page .main-image img {
      width: 100%;
      height: 415px;
      border-radius: 10px;
      object-fit: cover;
    }

    .hotel-page .small-images {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .hotel-page .small-images img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
    }

    .hotel-page .features {
      display: flex;
      flex-wrap: wrap;
      margin-top: 30px;
      justify-content: space-around;
    }

    .hotel-page .features div {
      background: white;
      padding: 10px 15px;
      border-radius: 25px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      font-size: 15px;
      gap: 8px;
    }

    .hotel-page .comments-section {
      max-width: 700px;
      margin: 40px auto 0;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
      text-align: left;
    }

    .hotel-page .comments-section h2 {
      margin-bottom: 15px;
      color: #333;
    }

    .hotel-page #comment-form {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .hotel-page #comment-input {
      width: 100%;
      height: 100px;
      padding: 10px;
      font-size: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      resize: vertical;
      line-height: 1.4;
    }

    .hotel-page #comment-form button {
      align-self: flex-end;
      background-color: #7fc142;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .hotel-page #comment-form button:hover {
      background-color:rgb(118, 179, 61);
    }

    .hotel-page #comments-display {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .hotel-page .comment {
      display: flex;
      gap: 12px;
      background: #f0f2f5;
      padding: 12px 15px;
      border-radius: 10px;
      align-items: flex-start;
    }

    .hotel-page .comment img.avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      object-fit: cover;
    }

    .hotel-page .comment-content {
      flex: 1;
    }

    .hotel-page .comment-header {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      color: #555;
      margin-bottom: 6px;
    }

    .hotel-page .comment p {
      margin: 0;
      font-size: 15px;
      color: #333;
    }

    .hotel-page .table-logement {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #ccc;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 0 5px #ccc;
      margin-top: 40px;
      text-align: left;
    }

    .hotel-page .table-logement th {
      background-color:rgb(107, 173, 44);
      color: white;
      padding: 12px;
      text-align: left;
    }

    .hotel-page .table-logement td {
      padding: 12px;
      vertical-align: top;
      border-top: 1px solid #eee;
    }

    .hotel-page .table-logement td a {
      color: white;
      font-weight: bold;
      text-decoration: none;
    }

    .hotel-page .table-logement td p {
      color: black;
      display: inline;
      font-weight: bold;
    }

    .hotel-page .table-logement td small {
      display: block;
      color: #555;
      margin-top: 4px;
    }

    .hotel-page .voyageurs i {
      margin-right: 4px;
      color: #222;
    }

    .hotel-page .btn-reserver {
      display: inline-block;
      text-align: center;
      font-weight: bold;
      text-decoration: none;
      background-color: #7fc142;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 5px;
      cursor: pointer;
    }

    .hotel-page .btn-reserver:hover {
      background-color: rgb(118, 179, 61);
    }

    .hotel-page #map {
      height: 500px;
      border: 1px solid #ccc; 
      margin-top : 40px;
      box-sizing: border-box;
      text-align: center;
      width: 100%;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .hotel-page .header-icons {
      position: absolute;
      top: 0;
      right: 0;
      display: flex;
      gap: 15px;
      align-items: center;
      margin-top: 10px;
      margin-right: 10px;
    }

    .hotel-page .header-icons form {
      margin: 0;
    }

    .hotel-page .header-icons button {
      background: none;
      border: none;
      font-size: 22px;
      cursor: pointer;
      color: #282834;
    }

    .hotel-page .header-icons .share-icon {
      font-size: 22px;
      color: #282834;
    }

#comments-display {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.comment-box {
    background: #fff;
    border: 1px solid #e3e3e3;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    display: flex;
    gap: 15px;
    position: relative;
}

.comment-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}

.comment-content {
    flex-grow: 1;
}

.comment-header {
    font-weight: bold;
    color: #333;
    margin-bottom: 4px;
    font-size: 16px;
}

.comment-date {
    font-size: 12px;
    color: #888;
    position: absolute;
    top: 16px;
    right: 20px;
}

.comment-text {
    font-size: 15px;
    color: #444;
    margin-top: 6px;
    line-height: 1.5;
}

.admin-reply {
    margin-top: 15px;
    padding: 14px 16px;
    background: #f1f9f4;
    border-left: 4px solid #3cb371;
    border-radius: 10px;
    font-size: 14px;
    color: #1e3d29;
}

@media (max-width: 600px) {
    .comment-box {
        flex-direction: column;
        align-items: flex-start;
    }

    .comment-date {
        position: static;
        margin-top: 10px;
    }
}



  </style>
</head>
<body>
  <div class="hotel-page">
    <div class="container">
    
    <div class="header">
      <img class="stars" src="<?= htmlspecialchars($hotelInfo['cheminEtoile']) ?>">
      <h1><?= htmlspecialchars($hotelInfo['nomHotel']) ?></h1>
      <p><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($hotelInfo['adresseH']) ?></p>
      
      <div class="header-icons">
        <form method="post">
          <input type="hidden" name="action" value="favoris">
          <input type="hidden" name="idHotel" value="<?= $idHotel ?>">
          <button type="submit" title="Ajouter aux favoris">
            <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
          </button>
        </form>

        <button class="share-icon" id="share-btn" title="Copier le lien">
          <i class="fas fa-share-alt"></i>
        </button>

        <script>
          function copyUrlToClipboard() {
              const url = window.location.href;
              
              const textarea = document.createElement('textarea');
              textarea.value = url;
              document.body.appendChild(textarea);
              
              textarea.select();
              textarea.setSelectionRange(0, 99999);
              
              document.execCommand('copy');
              
              document.body.removeChild(textarea);
              
              const shareBtn = document.getElementById('share-btn');
              const originalTitle = shareBtn.title;
              shareBtn.title = "Copié !";
              
              setTimeout(() => {
                  shareBtn.title = originalTitle;
              }, 2000);
          }

          document.getElementById('share-btn').addEventListener('click', copyUrlToClipboard);
        </script>


      </div>
    </div>

    <div class="images">
      <div class="main-image">
        <?php if (!empty($images)): ?>
          <img src="<?= htmlspecialchars($images[0]) ?>" alt="Hôtel">
        <?php endif; ?>
      </div>
      <div class="small-images">
      <?php
        for($i = 1; $i < min(5, count($images)); $i++){
          echo "<img src='".htmlspecialchars($images[$i])."' alt='Photo secondaire $i'>";
        }
      ?>
      </div>
    </div>

    <div class="features">
      <div><i class="fas fa-mug-hot"></i> Petit-déjeuner</div>
      <?= $hotelInfo['avoirPiscine'] ? '<div><i class="fas fa-swimming-pool"></i> Piscine extérieure</div>' : '' ?>
      <?= $hotelInfo['avoirWIFI'] ? '<div><i class="fas fa-wifi"></i> Connexion Wi-Fi gratuite</div>' : '' ?>
      <?= $hotelInfo['avoirRestau'] ? '<div><i class="fas fa-utensils"></i> Restaurant</div>' : '' ?>
      <?= $hotelInfo['avoirParking'] ? '<div><i class="fas fa-parking"></i> Parking privé gratuit</div>' : '' ?>
    </div>

    <table class="table-logement">
    <thead>
      <tr>
        <th>Type de logement</th>
        <th>Nombre de voyageurs</th>
        <th>Tarifs chambres</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php 
      $q = "SELECT * FROM chambres WHERE idHotel = ?";
      $stmt = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($stmt, "i", $idHotel);
      mysqli_stmt_execute($stmt);
      $res = mysqli_stmt_get_result($stmt);

      $nbp = $_GET['nbp'] ?? null;
      $depart = $_GET['depart'] ?? null;
      $arrivee = $_GET['arrivee'] ?? null;
      
      if (mysqli_num_rows($res) === 0) {
        echo "<tr><td colspan='4'>Aucune chambre disponible</td></tr>";
      } else {
        while($l = mysqli_fetch_assoc($res)){
          echo "<tr>
                <td>
                  <p>".htmlspecialchars($l['libelle'])."</p><br>
                  <small>".htmlspecialchars($l['descriptionCh'])."</small>
                </td>
                <td class='voyageurs'>";
          for($i = 0; $i < $l['capacite']; $i++){
            echo "<i class='fas fa-user'></i>";
          }
          echo "</td>
                <td>".htmlspecialchars($l['prix'])." DHs </td>
                <td><a href='reserver.php?idChambre=".urlencode($l['idChambre'])."&capacite=".$l['capacite']."&nbp=".$nbp."&depart=".$depart."&arrivee=".$arrivee."&prix=".$l['prix']."' class='btn-reserver'>Réserver</a></td>
                </tr>";
        }
      }
    ?> 
    </tbody>
  </table>

<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const hotelAddress = "Rue Abou Abbas El Sebti, Marrakech";
    const hotelName = <?= json_encode($hotelInfo['nomHotel']) ?>;
    const mapElement = document.getElementById('map');

    if (!mapElement) {
        return;
    }

    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(hotelAddress)}&limit=1`;

    fetch(url, {
        headers: {
            'Accept-Language': 'fr',
            'User-Agent': 'HotelApp/1.0'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.length) {
            mapElement.innerHTML = "<p style='padding:20px'>Localisation introuvable</p>";
            return;
        }

        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);

        const map = L.map('map').setView([lat, lon], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lon])
            .addTo(map)
            .bindPopup(`<b>${hotelName}</b><br>${hotelAddress}`)
            .openPopup();
    })
    .catch(() => {
        mapElement.innerHTML = "<p style='padding:20px'>Erreur de chargement de la carte</p>";
    });
});
</script>





    <div class="comments-section">
    <h2>Commentaires</h2>

    <?php if ($idClient): ?>
    <form id="comment-form" method="post">
      <textarea id="comment-input" name="commentaire" rows="2" placeholder="Ajoutez un commentaire..." required></textarea>
      <button type="submit">Publier</button>
    </form>
    <?php else: ?>
      <p><a href="login.php">Connectez-vous</a> pour ajouter un commentaire</p>
    <?php endif; ?>
   
<div id="comments-display">
  <?php 
    if ($idClient) {
      $sql = "SELECT c.*, cl.nomC, cl.prenomC, cl.photoC, ra.reponse, ra.daterep, a.nomHotel 
              FROM commentaires c
              JOIN clients cl ON c.idClient = cl.idClient
              LEFT JOIN hotels a ON c.idHotel = a.idHotel
              LEFT JOIN responseadm ra ON c.idComm = ra.idComm
              WHERE c.idHotel = ?
              ORDER BY c.datecomm DESC";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $idHotel);
      $stmt->execute();
      $result = $stmt->get_result();
      
      if ($result->num_rows === 0) {
        echo "<p style='text-align:center; color:#666; padding:20px;'>Soyez le premier à commenter cet hôtel !</p>";
      } else {
        while ($row = $result->fetch_assoc()): ?>
            <div class="comment-box">
                <div class="comment-avatar">
                    <?= $row['photoC'] ? '<img src="' . htmlspecialchars($row['photoC']) . '" alt="avatar" style="width:100%; height:100%; border-radius:50%;">' : '😊' ?>
                </div>
                <div class="comment-content">
                    <div class="comment-header">
                        <?= htmlspecialchars($row['prenomC'] . ' ' . strtoupper($row['nomC'])) ?>
                    </div>
                    <div class="comment-text">
                        <?= nl2br(htmlspecialchars($row['commentaire'])) ?>
                    </div>

                    <?php if (!empty($row['reponse'])): ?>
                        <div class="admin-reply">
                            <strong>Réponse de l'admin :</strong><br>
                            <?= nl2br(htmlspecialchars($row['reponse'])) ?><br>
                            <small>Répondu le : <?= htmlspecialchars($row['daterep']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="comment-date"><?= htmlspecialchars($row['datecomm']) ?></div>
            </div>
        <?php endwhile;
      }
    } else {
      echo "<p style='text-align:center; color:#666; padding:20px;'>Aucun commentaire pour cet hôtel.</p>";
    }
  ?>
</div>
  </div>

    </div>
  </div>
</body>
</html>
