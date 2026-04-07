<?php
include_once 'includes/entete.html';
require_once './includes/connexion.php';

// Récupérer les données nécessaires pour les filtres
$sqlClasses = "SELECT * FROM classes";
$sqlVilles = "SELECT DISTINCT villeH FROM hotels";
$sqlCommodites = [
    'wifi' => "SELECT DISTINCT avoirWIFI FROM hotels WHERE avoirWIFI = 1",
    'piscine' => "SELECT DISTINCT avoirPiscine FROM hotels WHERE avoirPiscine = 1",
    'restaurant' => "SELECT DISTINCT avoirRestau FROM hotels WHERE avoirRestau = 1",
    'parking' => "SELECT DISTINCT avoirParking FROM hotels WHERE avoirParking = 1"
];

$classes = mysqli_query($conn, $sqlClasses);
$villes = mysqli_query($conn, $sqlVilles);
$commodites = [];
foreach ($sqlCommodites as $key => $query) {
    $commodites[$key] = mysqli_query($conn, $query);
}

// Récupérer les hôtels selon les filtres appliqués
$whereClauses = [];
$params = [];
$types = '';

if (isset($_GET['prix_min']) && isset($_GET['prix_max'])) {
    $whereClauses[] = "prix BETWEEN ? AND ?";
    $params[] = $_GET['prix_min'];
    $params[] = $_GET['prix_max'];
    $types .= 'ii';
}

if (isset($_GET['type'])) {
    $whereClauses[] = "idClasse = ?";
    $params[] = $_GET['type'];
    $types .= 'i';
}

if (isset($_GET['ville'])) {
    $whereClauses[] = "villeH = ?";
    $params[] = $_GET['ville'];
    $types .= 's';
}

if (isset($_GET['wifi'])) {
    $whereClauses[] = "avoirWIFI = ?";
    $params[] = $_GET['wifi'];
    $types .= 'i';
}

if (isset($_GET['piscine'])) {
    $whereClauses[] = "avoirPiscine = ?";
    $params[] = $_GET['piscine'];
    $types .= 'i';
}

if (isset($_GET['restaurant'])) {
    $whereClauses[] = "avoirRestau = ?";
    $params[] = $_GET['restaurant'];
    $types .= 'i';
}

if (isset($_GET['parking'])) {
    $whereClauses[] = "avoirParking = ?";
    $params[] = $_GET['parking'];
    $types .= 'i';
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

$sqlHotels = "SELECT * FROM hotels $whereSql";
$stmtHotels = mysqli_prepare($conn, $sqlHotels);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmtHotels, $types, ...$params);
}
mysqli_stmt_execute($stmtHotels);
$resultHotels = mysqli_stmt_get_result($stmtHotels);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtrer les Hôtels</title>
    <style>
        /* Ajoutez ici votre CSS pour le style des filtres et des résultats */
    </style>
</head>
<body>

<div class="filtres">
    <form method="GET" action="">
        <label for="prix_min">Prix minimum :</label>
        <input type="number" id="prix_min" name="prix_min" min="0" value="<?= $_GET['prix_min'] ?? '' ?>">
        <label for="prix_max">Prix maximum :</label>
        <input type="number" id="prix_max" name="prix_max" min="0" value="<?= $_GET['prix_max'] ?? '' ?>">

        <label for="type">Type d'hôtel :</label>
        <select id="type" name="type">
            <option value="">Sélectionner</option>
            <?php while ($row = mysqli_fetch_assoc($classes)): ?>
                <option value="<?= $row['idClasse'] ?>" <?= isset($_GET['type']) && $_GET['type'] == $row['idClasse'] ? 'selected' : '' ?>>
                    <?= $row['libelle'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="ville">Ville :</label>
        <select id="ville" name="ville">
            <option value="">Sélectionner</option>
            <?php while ($row = mysqli_fetch_assoc($villes)): ?>
                <option value="<?= $row['villeH'] ?>" <?= isset($_GET['ville']) && $_GET['ville'] == $row['villeH'] ? 'selected' : '' ?>>
                    <?= $row['villeH'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <fieldset>
            <legend>Commodités :</legend>
            <label for="wifi">Wi-Fi</label>
            <input type="checkbox" id="wifi" name="wifi" value="1" <?= isset($_GET['wifi']) && $_GET['wifi'] == '1' ? 'checked' : '' ?>>
            <label for="piscine">Piscine</label>
            <input type="checkbox" id="piscine" name="piscine" value="1" <?= isset($_GET['piscine']) && $_GET['piscine'] == '1' ? 'checked' : '' ?>>
            <label for="restaurant">Restaurant</label>
            <input type="checkbox" id="restaurant" name="restaurant" value="1" <?= isset($_GET['restaurant']) && $_GET['restaurant'] == '1' ? 'checked' : '' ?>>
            <label for="parking">Parking</label>
            <input type="checkbox" id="parking" name="parking" value="1" <?= isset($_GET['parking']) && $_GET['parking'] == '1' ? 'checked' : '' ?>>
        </fieldset>

        <button type="submit">Filtrer</button>
    </form>
</div>

<div class="resultats">
    <?php while ($hotel = mysqli_fetch_assoc($resultHotels)): ?>
        <div class="hotel">
            <h2><?= htmlspecialchars($hotel['nomHotel']) ?></h2>
            <p>Ville : <?= htmlspecialchars($hotel['villeH']) ?></p>
            <p>Prix : <?= htmlspecialchars($hotel['prix']) ?> DH</p>
            <p>Classe : <?= htmlspecialchars($hotel['idClasse']) ?> étoiles</p>
            <p>Commodités : <?= htmlspecialchars($hotel['avoirWIFI'] ? 'Wi-Fi' : '') ?> <?= htmlspecialchars($hotel['avoirPiscine'] ? 'Piscine' : '') ?> <?= htmlspecialchars($hotel['avoirRestau'] ? 'Restaurant' : '') ?> <?= htmlspecialchars($hotel['avoirParking'] ? 'Parking' : '') ?></p>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
