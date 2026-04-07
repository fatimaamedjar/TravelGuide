<?php
session_start();
include_once 'includes/entete.html';
require_once './includes/connexion.php';

// Récupération des villes disponibles
function getVilles($conn) {
  $villes = [];
  $result = mysqli_query($conn, "SELECT DISTINCT villeA FROM activites ORDER BY villeA");
  while ($row = mysqli_fetch_assoc($result)) {
    $villes[] = $row['villeA'];
  }
  return $villes;
}

$villes = getVilles($conn);

$ville = $_POST['ville'] ?? '';
$prix = $_POST['prix'] ?? 1000;
$duree = $_POST['duree'] ?? 240;
$tri = $_POST['tri'] ?? 'asc';
$order_sql = ($tri === 'desc') ? "ORDER BY a.prixA DESC" : "ORDER BY a.prixA ASC";

$where = ["i.cheminImgA LIKE '%apercu%'"];
if (!empty($ville)) {
  $ville_safe = mysqli_real_escape_string($conn, $ville);
  $where[] = "a.villeA = '$ville_safe'";
}
$where[] = "a.prixA <= " . intval($prix);
$where[] = "a.dureeA <= " . intval($duree);

$condition = implode(" AND ", $where);

$q = "SELECT a.*, i.cheminImgA, t.nomType FROM imageActivite i
      INNER JOIN activites a ON a.idActivite = i.idActivite
      INNER JOIN typeactivite t ON t.idType = a.idType 
      WHERE $condition
      $order_sql";

$res = mysqli_query($conn, $q);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Activités</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
    }

    .triage {
      display: flex;
      justify-content: center;
      padding: 20px;
      background-color: #ffffff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
    }

    .triage form {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .triage select {
      padding: 6px;
      border-radius: 6px;
    }

    .activites-page {
      display: flex;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .filter-sidebar {
      width: 250px;
      background-color: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-right: 20px;
      height: fit-content;
    }

    .filter-sidebar label {
      display: block;
      margin-bottom: 10px;
      font-weight: bold;
      font-size: 14px;
    }

    .filter-sidebar select,
    .filter-sidebar input[type="range"] {
      width: 100%;
      margin-bottom: 20px;
    }

    .filter-sidebar .range-value {
      font-size: 13px;
      color: #333;
      margin-top: -10px;
      margin-bottom: 10px;
    }

    .filter-sidebar button {
      background-color: #002895;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      width: 100%;
    }

    .filter-sidebar button:hover {
      background-color: #001f75;
    }

    .results-container {
      flex: 1;
    }

    .results-container section {
      display: flex;
      justify-content: space-between;
      background-color: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      height: 150px;
    }

    .img-section {
      flex: 1.2;
      min-width: 280px;
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

    .reserve-section {
      flex: 1;
      background-color: #e6ecf4;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      border-left: 1px solid #ddd;
    }

    .reserve-section p {
      font-size: 14px;
      color: #0032bd;
      margin: 4px 0;
    }

    .btn-reserver {
      text-decoration: none;
      background-color: #002895;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-reserver:hover {
      background-color: #001f75;
    }

    @media screen and (max-width: 768px) {
      .activites-page {
        flex-direction: column;
      }

      .filter-sidebar {
        width: 100%;
        margin-bottom: 20px;
      }

      .triage {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

<!-- Tri par prix en haut -->
<div class="triage">
  <form method="post">
    <input type="hidden" name="ville" value="<?= htmlspecialchars($ville) ?>">
    <input type="hidden" name="prix" value="<?= htmlspecialchars($prix) ?>">
    <input type="hidden" name="duree" value="<?= htmlspecialchars($duree) ?>">

    <label for="tri"><strong>Trier par prix :</strong></label>
    <select name="tri" id="tri" onchange="this.form.submit()">
      <option value="asc" <?= ($tri === 'asc') ? 'selected' : '' ?>>Prix croissant</option>
      <option value="desc" <?= ($tri === 'desc') ? 'selected' : '' ?>>Prix décroissant</option>
    </select>
  </form>
</div>

<!-- Page principale -->
<div class="activites-page">

  <!-- Barre latérale filtres -->
  <form method="post" class="filter-sidebar">
    <label for="ville">Ville</label>
    <select name="ville" id="ville">
      <option value="">Toutes</option>
      <?php foreach ($villes as $v): ?>
        <option value="<?= htmlspecialchars($v) ?>" <?= ($ville === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="prix">Prix maximum (Dhs)</label>
    <input type="range" name="prix" id="prix" min="0" max="1000" step="10" value="<?= htmlspecialchars($prix) ?>" oninput="prixValue.innerText = this.value + ' Dhs'">
    <div class="range-value" id="prixValue"><?= htmlspecialchars($prix) ?> Dhs</div>

    <label for="duree">Durée maximum (min)</label>
    <input type="range" name="duree" id="duree" min="0" max="300" step="10" value="<?= htmlspecialchars($duree) ?>" oninput="dureeValue.innerText = this.value + ' min'">
    <div class="range-value" id="dureeValue"><?= htmlspecialchars($duree) ?> min</div>

    <button type="submit">Filtrer</button>
  </form>

  <!-- Résultats -->
  <div class="results-container">
    <?php if ($res && mysqli_num_rows($res) > 0): ?>
      <?php while ($l = mysqli_fetch_assoc($res)): ?>
        <section>
          <div class='img-section'>
            <img src='<?= htmlspecialchars($l['cheminImgA']) ?>' alt='Image activité'>
          </div>
          <div class='details-section'>
            <p><?= htmlspecialchars($l['nomActivite']) ?></p>
            <p><?= htmlspecialchars($l['nomType']) ?></p>
            <p><?= htmlspecialchars($l['descriptionA']) ?></p>
            <p><?= htmlspecialchars($l['villeA']) ?></p>
          </div>
          <div class='reserve-section'>
            <p><strong><?= htmlspecialchars($l['prixA']) ?> Dhs</strong></p>
            <p>Annulation gratuite</p>
            <a href='activite.php?idActivite=<?= intval($l['idActivite']) ?>&nbp=<?= urlencode($_POST['personnes'] ?? 1) ?>&date=<?= urlencode($_POST['depart'] ?? '') ?>' class='btn-reserver'>Voir l'offre</a>
          </div>
        </section>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Aucune activité trouvée.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
