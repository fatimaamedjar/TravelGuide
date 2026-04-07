<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajouter Hôtel</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #ece9e6, #ffffff);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 20px;
    }

    form {
      background-color: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 700px;
    }

    form fieldset {
      border: 0;
      margin-bottom: 30px;
    }

    form h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #2c3e50;
      font-size: 22px;
    }

    form label {
      display: block;
      font-weight: 500;
      margin-top: 20px;
      margin-bottom: 8px;
      color: #34495e;
    }

    form input[type="text"],
    form input[type="number"],
    form input[type="email"],
    form input[type="file"],
    form select {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 14px;
      transition: border-color 0.3s ease;
    }

    form input:focus,
    form select:focus {
      outline: none;
      border-color: #7fc142;
    }

    .radio-group {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 5px;
    }

    form input[type="submit"] {
      width: 100%;
      margin-top: 30px;
      padding: 14px;
      background-color: #7fc142;
      border: none;
      color: white;
      font-weight: bold;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    form input[type="submit"]:hover {
      background-color: rgb(113, 168, 60);
    }

    .success-message,
    .error-message {
      margin-top: 20px;
      text-align: center;
      font-weight: 500;
    }

    .success-message {
      color: #27ae60;
    }

    .error-message {
      color: #e74c3c;
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

  <form action="" method="POST" enctype="multipart/form-data">
    
    <h1>Ajouter un Hôtel</h1>

    <fieldset>
      <legend>Détails de l'hôtel</legend>
      <label for="nom">Nom d'hôtel :</label>
      <input type="text" name="nom" id="nom" required>

      <label for="adresse">Adresse :</label>
      <input type="text" name="adresse" id="adresse" required>

      <label for="ville">Ville :</label>
      <input type="text" name="ville" id="ville" required>

      <label for="tel">Téléphone :</label>
      <input type="text" name="tel" id="tel" required>

      <label for="classe">Classe :</label>
      <select name="classe" id="classe" required>
        <?php
          require_once '../includes/connexion.php';
          $q = "SELECT * FROM classes";
          $res = mysqli_query($conn, $q);
          while($l = mysqli_fetch_assoc($res)){
            echo "<option value='".$l['idClasse']."'>".$l['libelle']."</option>";
          }
        ?>
      </select>
    </fieldset>

    <fieldset>
      <legend>Tarifs par catégorie</legend>
      <label for="prixBas">Prix Standard :</label>
      <input type="number" name="prixBas" id="prixBas" required>

      <label for="prixMoy">Prix Supérieur :</label>
      <input type="number" name="prixMoy" id="prixMoy" required>

      <label for="prixPrem">Prix Premium :</label>
      <input type="number" name="prixPrem" id="prixPrem" required>
    </fieldset>

    <fieldset>
      <legend>Commodités disponibles</legend>

      <label>L'hôtel a une piscine ?</label>
      <div class="radio-group">
        <input type="radio" name="avoirPiscine" value="oui" id="piscine-oui" required>
        <label for="piscine-oui">Oui</label>
        <input type="radio" name="avoirPiscine" value="non" id="piscine-non">
        <label for="piscine-non">Non</label>
      </div>

      <label>L'hôtel a WiFi ?</label>
      <div class="radio-group">
        <input type="radio" name="avoirWIFI" value="oui" id="wifi-oui" required>
        <label for="wifi-oui">Oui</label>
        <input type="radio" name="avoirWIFI" value="non" id="wifi-non">
        <label for="wifi-non">Non</label>
      </div>

      <label>Restaurant disponible ?</label>
      <div class="radio-group">
        <input type="radio" name="avoirRestau" value="oui" id="restau-oui" required>
        <label for="restau-oui">Oui</label>
        <input type="radio" name="avoirRestau" value="non" id="restau-non">
        <label for="restau-non">Non</label>
      </div>

      <label>Parking disponible ?</label>
      <div class="radio-group">
        <input type="radio" name="avoirParking" value="oui" id="parking-oui" required>
        <label for="parking-oui">Oui</label>
        <input type="radio" name="avoirParking" value="non" id="parking-non">
        <label for="parking-non">Non</label>
      </div>
    </fieldset>

    <fieldset>
      <legend>Images de l'hôtel</legend>
      <label for="img_apercu">Image d’aperçu :</label>
      <input type="file" name="img_apercu" id="img_apercu" accept="image/*" required>

      <label for="img_collection">Collection d’images :</label>
      <input type="file" name="img_collection[]" id="img_collection" multiple accept="image/*" required>
    </fieldset>

    <input type="submit" name="submit" value="Ajouter">
  </form>

<?php
if (isset($_POST['submit'])) {
    $idH = $_POST['hotel'];

    $dir = "images/hotels/$idH/";
    $h_dir = "../".$dir;
    if (!is_dir($h_dir)) {
        mkdir($h_dir, 0777, true);
    }

    $nom = $_POST['nom'];
    $adresse = $_POST['adresse'];
    $ville = $_POST['ville'];
    $tel = $_POST['tel'];

    $prixBas = $_POST['prixBas'];
    $prixMoy = $_POST['prixMoy'];
    $prixPrem = $_POST['prixPrem'];

    $classe = $_POST['classe'];

    $avoirPiscine = $_POST['avoirPiscine'] == "oui" ? TRUE : FALSE;
    $avoirWIFI = $_POST['avoirWIFI'] == "oui" ? TRUE : FALSE;
    $avoirRestau = $_POST['avoirRestau'] == "oui" ? TRUE : FALSE;
    $avoirParking = $_POST['avoirParking'] == "oui" ? TRUE : FALSE;


    $q = "INSERT INTO hotels(nomHotel, adresseH, villeH, telHotel, idClasse, avoirPiscine, avoirWIFI, avoirRestau, avoirParking) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "ssssibbbb", $nom, $adresse, $ville, $tel, $classe, $avoirPiscine, $avoirWIFI, $avoirRestau, $avoirParking);
    if(mysqli_stmt_execute($stmt)){
      echo "✅ Les informations ont été enregistrées<br>";
    }
    else{
      echo "❌ Échec de l'enregistrement ds informations.<br>";
    }

    $q = "INSERT INTO hotels(nomHotel, adresseH, villeH, telHotel, idClasse, avoirPiscine, avoirWIFI, avoirRestau, avoirParking) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "ssssibbbb", $nom, $adresse, $ville, $tel, $classe, $avoirPiscine, $avoirWIFI, $avoirRestau, $avoirParking);
    if(mysqli_stmt_execute($stmt)){
      echo "✅ Les informations ont été enregistrées<br>";
    }
    else{
      echo "❌ Échec de l'enregistrement ds informations.<br>";
    }


    //Image aperçu
    if (isset($_FILES['img_apercu']) && $_FILES['img_apercu']['error'] === 0) {
        $ap_tmp = $_FILES['img_apercu']['tmp_name'];
        $ext = pathinfo($_FILES['img_apercu']['name'], PATHINFO_EXTENSION);
        $ap_nvnm = "hotel_" . $idH . "_apercu." . $ext;
        $dest_ap = $dir . $ap_nvnm;

        if (move_uploaded_file($ap_tmp, "../".$dest_ap)) {
            echo "✅ Image aperçu enregistrée : $ap_nvnm<br>";
            $chemin_ap_sql = mysqli_real_escape_string($conn, $dest_ap);
            $q = "INSERT INTO imagehotel(cheminImg, idHotel) VALUES ('$chemin_ap_sql', $idH)";
            mysqli_query($conn, $q);
        } else {
            echo "❌ Échec de l'enregistrement de l'image aperçu.<br>";
        }
    }

    //Images collection
    if (isset($_FILES['img_collection'])) {
        foreach ($_FILES['img_collection']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['img_collection']['error'][$index] === 0) {
                $ext = pathinfo($_FILES['img_collection']['name'][$index], PATHINFO_EXTENSION);
                $nm_coll = "hotel_" . $idH . "_img_" . $index . "." . $ext;
                $dest_c = $dir . $nm_coll;

                if (move_uploaded_file($tmpName, "../".$dest_c)) {
                    echo "✅ Image de collection enregistrée : $nm_coll<br>";
                    $chemin_coll_sql = mysqli_real_escape_string($conn, $dest_c);
                    $q = "INSERT INTO imagehotel(cheminImg, idHotel) VALUES ('$chemin_coll_sql', $idH)";
                    mysqli_query($conn, $q);
                } else {
                    echo "❌ Échec de l'enregistrement de l'image $nm_coll<br>";
                }
            }
        }
    }
}
?>


</body>
</html>
