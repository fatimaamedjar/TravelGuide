<?php
session_start();
require_once './includes/connexion.php';
include_once 'includes/entete.html';

function getFilterOptions($conn, $column, $table, $condition = "") {
  $query = "SELECT DISTINCT $column FROM $table $condition ORDER BY $column";
  $result = mysqli_query($conn, $query);
  $options = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $options[] = $row[$column];
  }
  return $options;
}

$villes = getFilterOptions($conn, 'villeH', 'hotels');
$types = ['Hôtel', 'Riad'];
$classes = [1, 2, 3, 4, 5];
$commodites = [
  'avoirPiscine' => 'Piscine',
  'avoirWIFI' => 'Wi-Fi',
  'avoirRestau' => 'Restaurant',
  'avoirParking' => 'Parking'
];

// Préparation des conditions WHERE
$where = [];

if (!empty($_GET['prix'])) {
  $where[] = "ch.prix <= " . intval($_GET['prix']);
}

if (!empty($_GET['ville'])) {
  $ville = mysqli_real_escape_string($conn, $_GET['ville']);
  $where[] = "h.villeH = '$ville'";
}

if (!empty($_GET['type'])) {
  $type = $_GET['type'];
  if ($type === 'Hôtel') {
    $where[] = "h.idClasse IN (1,2,3,4,5)";
  } elseif ($type === 'Riad') {
    $where[] = "h.idClasse IN (6,7,8,9,10)";
  }
}

if (!empty($_GET['classe'])) {
  $classe = intval($_GET['classe']);
  $classeIds = [
    1 => [1, 6],
    2 => [2, 7],
    3 => [3, 8],
    4 => [4, 9],
    5 => [5, 10]
  ];
  if (isset($classeIds[$classe])) {
    $where[] = "h.idClasse IN (" . implode(',', $classeIds[$classe]) . ")";
  }
}

foreach ($commodites as $key => $label) {
  if (!empty($_GET[$key])) {
    $where[] = "h.$key = 1";
  }
}

$where[] = "ch.libelle = 'Standard'";
$where[] = "i.cheminImg LIKE '%apercu%'";

$condition = implode(" AND ", $where);

$query = "SELECT h.*, i.cheminImg, c.libelle AS classe, ch.*
          FROM imagehotel i
          INNER JOIN hotels h ON h.idHotel = i.idHotel
          INNER JOIN classes c ON c.idClasse = h.idClasse
          INNER JOIN chambres ch ON ch.idHotel = h.idHotel
          WHERE $condition";

// Appliquer le tri par prix si défini
if (!empty($_GET['tri']) && in_array($_GET['tri'], ['asc', 'desc'])) {
  $query .= " ORDER BY ch.prix " . strtoupper($_GET['tri']);
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Hôtels</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
    }

    .container {
      display: flex;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .filters {
      width: 250px;
      background-color: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-right: 20px;
      height: fit-content;
    }

    .hotels {
      flex: 1;
    }

    .sort-bar {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 20px;
    }

    .sort-bar select {
      padding: 8px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-left: 10px;
    }

    .hotels section {
      display: flex;
      justify-content: space-between;
      background-color: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      max-width: 100%;
      height: 150px;
      margin-bottom: 20px;
    }

    .img-section {
      flex: 1.2;
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
      padding-left: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .reserve-section {
      flex: 1;
      background-color: #e6f4ea;
      padding: 16px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      border-left: 1px solid #ddd;
    }

    .btn-reserver {
      background-color: #0a9500;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 8px;
      margin-top: 10px;
    }

    .btn-reserver:hover {
      background-color: #067400;
    }

    .filters label {
      display: block;
      margin: 10px 0 5px;
      font-size: 14px;
      color: #333;
    }

    .filters input[type="range"],
    .filters select {
      width: 100%;
      padding: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .filters button {
      margin-top: 15px;
      background-color: #0a9500;
      color: white;
      border: none;
      padding: 10px;
      width: 100%;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }

    .filters button:hover {
      background-color: #067400;
    }

  </style>
</head>
<body>

<div class="container">
  <!-- Filtres à gauche -->
  <form class="filters" method="get">
    <h3>Filtres</h3>

    <label>Ville
      <select name="ville">
        <option value="">Toutes</option>
        <?php foreach ($villes as $ville): ?>
          <option value="<?= $ville ?>" <?= isset($_GET['ville']) && $_GET['ville'] === $ville ? 'selected' : '' ?>><?= $ville ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Type
      <select name="type">
        <option value="">Tous</option>
        <?php foreach ($types as $type): ?>
          <option value="<?= $type ?>" <?= isset($_GET['type']) && $_GET['type'] === $type ? 'selected' : '' ?>><?= $type ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Classe</label>
    <?php foreach ($classes as $class): ?>
      <label><input type="radio" name="classe" value="<?= $class ?>" <?= (isset($_GET['classe']) && $_GET['classe'] == $class) ? 'checked' : '' ?>> <?= $class ?> étoile<?= $class > 1 ? 's' : '' ?></label>
    <?php endforeach; ?>

    <label>Prix maximum <br><br><span id="prixAffiche"><?= isset($_GET['prix']) ? $_GET['prix'] : '10000' ?></span> Dhs
      <input type="range" name="prix" id="prixRange" min="500" max="10000" step="100" value="<?= isset($_GET['prix']) ? $_GET['prix'] : '10000' ?>">
    </label>

    <label>Commodités</label>
    <?php foreach ($commodites as $key => $label): ?>
      <label><input type="checkbox" name="<?= $key ?>" <?= isset($_GET[$key]) ? 'checked' : '' ?>> <?= $label ?></label>
    <?php endforeach; ?>

    <button type="submit">Appliquer</button>
  </form>

  <!-- Résultats à droite -->
  <div class="hotels">
    <!-- Barre de tri au-dessus -->
    <form method="get" class="sort-bar">
      <?php
      // Préserver les filtres existants dans le tri
      foreach ($_GET as $key => $value) {
        if ($key !== 'tri') {
          echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        }
      }
      ?>
      <label for="tri">Trier par prix :</label>
      <select name="tri" id="tri" onchange="this.form.submit()">
        <option value="">Aucun</option>
        <option value="asc" <?= (isset($_GET['tri']) && $_GET['tri'] == 'asc') ? 'selected' : '' ?>>Prix croissant</option>
        <option value="desc" <?= (isset($_GET['tri']) && $_GET['tri'] == 'desc') ? 'selected' : '' ?>>Prix décroissant</option>
      </select>
    </form>

    <!-- Liste des hôtels -->
    <?php if ($result && mysqli_num_rows($result) > 0):
      while ($l = mysqli_fetch_assoc($result)): ?>
        <section>
          <div class='img-section'>
            <img src='<?= $l['cheminImg'] ?>' alt='Photo d'hôtel'>
          </div>
          <div class='details-section'>
            <p><?= $l['nomHotel'] ?></p>
            <p><strong><?= $l['classe'] ?></strong></p>
            <p><?= $l['adresseH'] ?></p>
            <p><?= $l['villeH'] ?></p>
          </div>
          <div class='reserve-section'>
            <p><strong><?= $l['prix'] ?> Dhs / nuit</strong></p>
            <a class='btn-reserver' href='hotel.php?idHotel=<?= $l['idHotel'] ?>'>Voir l'offre</a>
          </div>
        </section>
    <?php endwhile;
    else:
      echo "<p>Aucun hôtel trouvé.</p>";
    endif; ?>
  </div>
</div>

<script>
  const prixRange = document.getElementById("prixRange");
  const prixAffiche = document.getElementById("prixAffiche");
  prixRange.addEventListener("input", () => {
    prixAffiche.textContent = prixRange.value;
  });
</script>

</body>
</html>
