<?php
session_start();

include_once 'includes/entete.html';
require_once 'includes/connexion.php';

// Sécurisation de l'entrée GET
$idActivite = isset($_GET['idActivite']) ? (int)$_GET['idActivite'] : 1;
$idClient = $_SESSION['id_client'] ?? null;

if ($idActivite <= 0) {
    die("Identifiant d'activité non valide.");
}

$query = "SELECT cheminImgA FROM imageActivite WHERE idActivite = ?";
$stmtImg = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmtImg, "i", $idActivite);
mysqli_stmt_execute($stmtImg);
$result = mysqli_stmt_get_result($stmtImg);
$images_db = mysqli_fetch_all($result, MYSQLI_ASSOC);
$images_count = count($images_db);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'favoris') {
    if ($idClient && $idActivite) {
        $check = "SELECT 1 FROM favoris WHERE idClient = ? AND idActivite = ?";
        $stmt = mysqli_prepare($conn, $check);
        mysqli_stmt_bind_param($stmt, "ii", $idClient, $idActivite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            $insert = "INSERT INTO favoris (idClient, idActivite) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, "ii", $idClient, $idActivite);
            mysqli_stmt_execute($stmt);
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['commentaire']) && $idClient) {
    $commentaire = trim($_POST['commentaire']);
    if (!empty($commentaire)) {
        $date = date('Y-m-d H:i:s');
        $q = "INSERT INTO commentaires (commentaire, datecomm, idClient, idHotel, idActivite) 
              VALUES (?, ?, ?, NULL, ?)";
        $stmt = mysqli_prepare($conn, $q);
        mysqli_stmt_bind_param($stmt, "ssii", $commentaire, $date, $idClient, $idActivite);
        mysqli_stmt_execute($stmt);
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$activiteInfo = null;
$images = [];

$q = "SELECT a.*, i.cheminImgA, t.nomType
      FROM imageactivite i 
      INNER JOIN activites a ON a.idActivite = i.idActivite
      INNER JOIN typeactivite t ON t.idType = a.idType
      WHERE a.idActivite = ?";
$stmt = mysqli_prepare($conn, $q);
mysqli_stmt_bind_param($stmt, "i", $idActivite);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($res && mysqli_num_rows($res) > 0) {
    while ($l = mysqli_fetch_assoc($res)) {
        if (!$activiteInfo) {
            $activiteInfo = $l;
        }
        $images[] = $l['cheminImgA'];
    }
} else {
    die("Aucune activité trouvée avec cet identifiant.");
}

$isFavorite = false;
if ($idClient) {
    $checkFav = "SELECT 1 FROM favoris WHERE idClient = ? AND idActivite = ?";
    $stmtFav = mysqli_prepare($conn, $checkFav);
    mysqli_stmt_bind_param($stmtFav, "ii", $idClient, $idActivite);
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
  <title><?= htmlspecialchars($activiteInfo['nomActivite'] ?? 'Activité') ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
*{
  font-family: Arial, sans-serif;
}

.site-header {
  z-index: 1000;
  position: relative;
  margin: 0;
  background-color: #f8f9fa;
}

.activity-page-wrapper {
  position: relative;
  margin-top: 20px;
  background: white;
  padding: 30px;
  min-height: 100vh;
}

.activity-page {
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
  border-radius: 20px;
  padding: 30px;
  overflow: hidden;
}

.activity-page .header {
  position: relative;
  text-align: left;
  padding: 0 15px 15px;
}

.activity-page .header h1 {
  margin: 10px 0 5px;
  font-size: 2.2rem;
  color: #282834;
  position: relative;
  padding-bottom: 10px;
  display: inline-block;
}

.activity-page .header h1::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 80px;
  height: 4px;
  border-radius: 2px;
}

.activity-page .header p {
  margin: 0;
  font-size: 15px;
  color: #555;
}

.activity-page .header-icons {
  position: absolute;
  top: 10px;
  right: 0;
  display: flex;
  gap: 15px;
  align-items: center;
}

.activity-page .header-icons button {
  width: 40px;
  height: 40px;
  font-size: 22px;
  background: none;
  border: none;
  color: #282834;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}

.activity-page .header-icons button:hover {
  background: rgba(0, 0, 0, 0.05);
}

/* Gallery */
.activity-page .gallery-wrapper {
  display: flex;
  gap: 25px;
  margin: 30px 0;
  flex-wrap: wrap;
}

.activity-page .main-image {
  flex: 1;
  height: 480px;
  border-radius: 15px;
  overflow: hidden;
  background:  #f8f9fa;
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
  position: relative;
}

.activity-page .main-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.activity-page .main-image:hover img {
  transform: scale(1.02);
}

.activity-page .image-info {
  position: absolute;
  bottom: 15px;
  left: 15px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
}

.activity-page .thumbnails-container {
  width: 30%;
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.activity-page .thumbnail-row {
  display: flex;
  gap: 25px;
  height: calc(50% - 12px);
}

.activity-page .thumbnail {
  flex: 1;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  position: relative;
  transition: transform 0.3s ease;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.activity-page .thumbnail:hover {
  transform: translateY(-5px);
}

.activity-page .thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.activity-page .thumbnail.more {
  background: #282834;
  color: white;
  flex-direction: column;
}

.activity-page .thumbnail.more .icon {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

/* Table */
.activity-page .table-logement {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid #e1e8ff;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  margin-top: 40px;
  text-align: left;
  overflow: hidden;
}

.activity-page .table-logement th {
  background: #282834;
  color: white;
  padding: 15px;
  font-weight: 600;
  font-size: 17px;
}

.activity-page .table-logement td {
  padding: 15px;
  vertical-align: top;
  border-top: 1px solid #e8f0ff;
}

.activity-page .table-logement tr:nth-child(even) {
  background-color: #f8fafd;
}

.activity-page .table-logement tr:hover {
  background-color: #eef4ff;
}

.activity-page .table-logement small {
  display: block;
  margin-top: 8px;
  font-size: 14px;
  color: #6c757d;
}

.activity-page .voyageurs i {
  margin-right: 4px;
  color: #4a6582;
}

/* Réserver button */
.activity-page .btn-reserver {
  display: inline-block;
  padding: 10px 25px;
  border-radius: 30px;
  font-weight: bold;
  text-decoration: none;
  background: #282834;
  color: white;
  cursor: pointer;
  border: none;
  transition: all 0.3s ease;
  text-align: center;
}

.activity-page .btn-reserver:hover {
  background: #282834;
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(127, 193, 66, 0.4);
}

/* Carte */
.activity-page #map {
  height: 400px;
  margin-top: 40px;
  border: 1px solid #e0e8ff;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

/* Commentaires */
.activity-page .comments-section {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.07);
  margin-top: 40px;
}

.activity-page .comments-section h2 {
  font-size: 1.8rem;
  margin-bottom: 20px;
  color: #2c3e50;
}

#comment-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

#comment-input {
  width: 100%;
  height: 100px;
  padding: 15px;
  font-size: 16px;
  border-radius: 12px;
  border: 1px solid #d1d8ff;
  resize: vertical;
  line-height: 1.4;
}

#comment-input:focus {
  border-color: #4a6582;
  outline: none;
  box-shadow: 0 0 0 3px rgba(74, 101, 130, 0.1);
}

#comment-form button {
  align-self: flex-end;
  background: #282834;
  color: white;
  padding: 10px 25px;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  transition: all 0.3s ease;
}

#comment-form button:hover {
  background: #282834;
  transform: translateY(-2px);
}

.comment {
  display: flex;
  gap: 15px;
  padding-bottom: 20px;
  margin-bottom: 20px;
  border-bottom: 1px solid #eee;
}

.comment:last-child {
  border-bottom: none;
}

.comment .avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

.comment-content {
  flex: 1;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.comment-header strong {
  font-size: 1.1rem;
  color: #2c3e50;
}

.comment-header span {
  font-size: 0.9rem;
  color: #6c757d;
}

.comment p {
  margin: 0;
  color: #444;
  line-height: 1.6;
}

/* Modal */
.gallery-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.95);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.4s ease;
}

.gallery-modal.active {
  opacity: 1;
  visibility: visible;
}

.modal-content {
  width: 90%;
  max-width: 1200px;
  background: #1a1a1a;
  border-radius: 15px;
  padding: 25px;
  position: relative;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.modal-header h2 {
  color: white;
  font-size: 1.8rem;
}

.close-modal {
  background: none;
  border: none;
  color: white;
  font-size: 2.5rem;
  cursor: pointer;
  transition: 0.3s ease;
}

.close-modal:hover {
  transform: rotate(90deg);
  color: #7fc142;
}

.modal-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
  max-height: 70vh;
  overflow-y: auto;
  padding: 10px;
}

.modal-img {
  width: 100%;
  height: 180px;
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: 0.3s ease;
}

.modal-img:hover {
  transform: scale(1.03);
  box-shadow: 0 10px 25px rgba(127, 193, 66, 0.3);
}

.modal-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Conteneur principal des commentaires */
#comments-display {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

/* Style de chaque commentaire */
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

/* Avatar du client */
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

/* Zone de texte du commentaire */
.comment-content {
    flex-grow: 1;
}

/* En-tête du commentaire (nom client) */
.comment-header {
    font-weight: bold;
    color: #333;
    margin-bottom: 4px;
    font-size: 16px;
}

/* Date de publication */
.comment-date {
    font-size: 12px;
    color: #888;
    position: absolute;
    top: 16px;
    right: 20px;
}

/* Texte du commentaire */
.comment-text {
    font-size: 15px;
    color: #444;
    margin-top: 6px;
    line-height: 1.5;
}

/* Réponse de l'admin */
.admin-reply {
    margin-top: 15px;
    padding: 14px 16px;
    background: #f1f9f4;
    border-left: 4px solid #3cb371;
    border-radius: 10px;
    font-size: 14px;
    color: #1e3d29;
}

/* Responsive */
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



/* Responsive */
@media (max-width: 992px) {
  .activity-page-wrapper {
    padding: 15px;
  }

  .activity-page {
    padding: 20px;
  }

  .activity-page .gallery-wrapper {
    flex-direction: column;
    margin: 20px 0;
  }

  .activity-page .main-image {
    height: 350px;
  }

  .activity-page .thumbnails-container {
    width: 100%;
    flex-direction: row;
    gap: 15px;
  }

  .activity-page .thumbnail-row {
    height: 150px;
  }
}

@media (max-width: 768px) {
  .activity-page .header h1 {
    font-size: 1.8rem;
  }

  .activity-page .main-image {
    height: 300px;
  }

  .modal-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  }
}


  </style>
</head>
<body>

<?php include_once 'includes/entete.html'; ?>

<div class="activity-page-wrapper">
  <div class="activity-page">
    <div class="header">
      <p><?= $activiteInfo['nomType'] ?? '' ?></p>
      <h1><?= htmlspecialchars($activiteInfo['nomActivite'] ?? '') ?></h1>
      <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($activiteInfo['adresseA'] ?? '') ?></p>
      
      <div class="header-icons">
        <form method="post">
          <input type="hidden" name="action" value="favoris">
          <input type="hidden" name="idActivite" value="<?= $idActivite ?>">
          <button type="submit" title="Ajouter aux favoris">
            <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
          </button>
        </form>

        <button class="share-icon" onclick="copyUrlToClipboard()" title="Copier le lien">
          <i class="fas fa-share-alt"></i>
        </button>
      </div>
    </div>

    <div class="gallery-wrapper">
      <div class="main-image">
        <img src="<?= $images_count ? $images_db[0]['cheminImgA'] : 'https://via.placeholder.com/800x600?text=Image+Manquante' ?>" id="main-img">
        <div class="image-info">1/<?= $images_count ?></div>
      </div>
      
      <div class="thumbnails-container">
        <div class="thumbnail-row">
          <?php
          $max_thumb = min(2, $images_count - 1);
          for ($i = 1; $i <= $max_thumb; $i++): 
          ?>
            <div class="thumbnail" onclick="changeMain('<?= $images_db[$i]['cheminImgA'] ?>', <?= $i+1 ?>)">
              <img src="<?= $images_db[$i]['cheminImgA'] ?>">
            </div>
          <?php endfor; ?>
          
          <?php if ($max_thumb < 2): ?>
            <div class="thumbnail">
              <i class="fas fa-image" style="font-size: 2rem; color: #ccc;"></i>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="thumbnail-row">
          <?php if ($images_count > 3): ?>
            <div class="thumbnail" onclick="changeMain('<?= $images_db[3]['cheminImgA'] ?>', 4)">
              <img src="<?= $images_db[3]['cheminImgA'] ?>">
            </div>
          <?php else: ?>
            <div class="thumbnail">
              <i class="fas fa-image" style="font-size: 2rem; color: #ccc;"></i>
            </div>
          <?php endif; ?>
          
          <div class="thumbnail more" onclick="openModal()">
            <div class="icon"><i class="fas fa-plus-circle"></i></div>
            <div class="text">+<?= max(0, $images_count - 4) ?></div>
          </div>
        </div>
      </div>
    </div>

    <table class="table-logement">
      <thead>
        <tr>
          <th>Type d'activité</th>
          <th>Durée d'activité (Minutes)</th>
          <th>Prix</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php 
        if ($idActivite): 
          $nbp = $_GET['nbp'] ?? null;
          $date = $_GET['date'] ?? null;
          echo "<tr>
                <td>
                  <p>".htmlspecialchars($activiteInfo['nomType'])."</p>
                  <small>".htmlspecialchars($activiteInfo['descriptionA'])."</small>
                </td>
                <td class='voyageurs'>";
          echo "<p>".$activiteInfo['dureeA']."</p>";
          echo "</td>
                <td>".htmlspecialchars($activiteInfo['prixA'])." DHs</td>
                <td><a href='reserver.php?idActivite=".urlencode($activiteInfo['idActivite'])."&nbp=".$nbp."&date=".$date."&prix=".$activiteInfo['prixA']."' class='btn-reserver'>Réserver</a></td>
                </tr>";
            
        else: ?>
          <tr>
            <td colspan="4" style="text-align:center; padding: 20px; color: #666;">
              Information sur l'actvité non disponible
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div id="map"></div>

    <div class="comments-section">
      <h2>Commentaires</h2>

      <?php if (isset($idClient) && $idClient): ?>
      <form id="comment-form" method="post">
        <textarea id="comment-input" name="commentaire" rows="2" placeholder="Ajoutez un commentaire..." required></textarea>
        <button type="submit">Publier</button>
      </form>
      <?php else: ?>
        <p style="padding: 10px 0;"><a href="login.php" style="color: #4a6582;">Connectez-vous</a> pour ajouter un commentaire</p>
      <?php endif; ?>
     
<div id="comments-display">
  <?php 
    if ($idClient) {
      $sql = "SELECT c.*, cl.nomC, cl.prenomC, cl.photoC, ra.reponse, ra.daterep, a.nomActivite 
              FROM commentaires c
              JOIN clients cl ON c.idClient = cl.idClient
              LEFT JOIN activites a ON c.idActivite = a.idActivite
              LEFT JOIN responseadm ra ON c.idComm = ra.idComm
              WHERE c.idActivite = ?
              ORDER BY c.datecomm DESC";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $idActivite);
      $stmt->execute();
      $result = $stmt->get_result();
      
      if ($result->num_rows === 0) {
        echo "<p style='text-align:center; color:#666; padding:20px;'>Soyez le premier à commenter cette activité !</p>";
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
      echo "<p style='text-align:center; color:#666; padding:20px;'>Aucun commentaire pour cette activité.</p>";
    }
  ?>
</div>

    
  </div>
</div>


<div class="gallery-modal" id="gallery-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Galerie Complète</h2>
      <button class="close-modal" onclick="closeModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div class="modal-grid">
      <?php if ($images_count): ?>
        <?php foreach ($images_db as $index => $img): ?>
          <div class="modal-img" onclick="changeMain('<?= $img['cheminImgA'] ?>', <?= $index+1 ?>)">
            <img src="<?= $img['cheminImgA'] ?>">
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column:1/-1; text-align:center; color:white;">Aucune image disponible</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  function copyUrlToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url);
    
    const notification = document.createElement('div');
    notification.textContent = 'Lien copié !';
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: #4a6582;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      z-index: 1000;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
      transition: transform 0.3s, opacity 0.3s;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.style.transform = 'translateY(-30px)';
      notification.style.opacity = '0';
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 2000);
  }

  function changeMain(imgSrc, index) {
    const mainImg = document.getElementById('main-img');
    mainImg.src = imgSrc;
    
    mainImg.style.opacity = 0;
    setTimeout(() => {
      mainImg.style.opacity = 1;
      mainImg.style.transition = 'opacity 0.3s';
    }, 10);
    
    document.querySelector('.image-info').textContent = index + '/' + <?= $images_count ?>;
    
    closeModal();
  }

  function openModal() {
    document.getElementById('gallery-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    document.getElementById('gallery-modal').classList.remove('active');
    document.body.style.overflow = '';
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
  });

  document.addEventListener('click', e => {
    if (e.target.classList.contains('gallery-modal')) closeModal();
  });
  
  const hotelAddress = "<?= addslashes($activiteInfo['villeA'] ?? '') ?>";
  const mapElement = document.getElementById('map');
  
  if (hotelAddress && mapElement && hotelAddress.trim() !== '') {
    const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(hotelAddress)}`;
    
    fetch(nominatimUrl)
    .then(response => response.json())
    .then(geoData => {
      if (geoData && geoData.length > 0) {
        const lat = geoData[0].lat;
        const lon = geoData[0].lon;
        
        const map = L.map('map').setView([lat, lon], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        L.marker([lat, lon]).addTo(map)
          .bindPopup(`<b>${hotelAddress}</b>`)
          .openPopup();
      } else {
        mapElement.innerHTML = `<div style="height:100%; display:flex; align-items:center; justify-content:center;">
          <p style="color:#666; text-align:center;">Position non trouvée : ${hotelAddress}</p>
        </div>`;
      }
    })
    .catch(() => {
      mapElement.innerHTML = `<div style="height:100%; display:flex; align-items:center; justify-content:center;">
        <p style="color:#666; text-align:center;">Erreur de chargement de la carte</p>
      </div>`;
    });
  } else if (mapElement) {
    if (!hotelAddress) {
      mapElement.innerHTML = `<div style="height:100%; display:flex; align-items:center; justify-content:center;">
        <p style="color:#666; text-align:center;">Aucune adresse disponible pour cette activité</p>
      </div>`;
    } else {
      mapElement.style.display = 'none';
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.activity-page').style.opacity = 0;
    setTimeout(() => {
      document.querySelector('.activity-page').style.transition = 'opacity 0.5s ease';
      document.querySelector('.activity-page').style.opacity = 1;
    }, 100);
  });
</script>

</body>
</html>
