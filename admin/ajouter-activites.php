<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajout Activité</title>
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
    form textarea,
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
    <label>Nom d'activité :</label>
    <input type="text" name='nom' required><br><br>

    <label>Description :</label>
    <textarea name="description" required></textarea><br><br>

    <label>Ville :</label>
    <input type="text" name="ville" required><br><br>

    <label>Adresse :</label>
    <input type="text" name="adresse" required><br><br>

    <label>Prix :</label>
    <input type="number" step="0.01" name="prix" required><br><br>

    <label>Durée (en minutes) :</label>
    <input type="number" name="duree" required><br><br>

    <label>Choisir type activité :</label><br>
    <?php
      $conn = mysqli_connect("localhost", "root", "", "travelguide_t");
      if(!$conn){ die("Erreur lors de la connexion à la base de données !"); }

      $q = "SELECT * FROM typeactivite";
      $res = mysqli_query($conn, $q);
      if($res){
        while($l = mysqli_fetch_assoc($res)){
          echo "<input type='radio' value='".$l['idType']."' name='type'>";
          echo "<label>".$l['nomType']."</label><br>";
        }
      }
    ?>
    <input type="radio" value='autre' name='type'>
    <label>Autre</label>
    <input type="text" name='nvType'><br><br>

    <label>Image aperçu :</label>
    <input type="file" name="img_apercu" accept="image/*" required><br><br>

    <label>Collection des images :</label>
    <input type="file" name="img_collection[]" multiple required accept="image/*"><br><br>

    <input type="submit" name="submit" value="Ajouter">
  </form>

<?php
if (isset($_POST['submit'])) {
  $nom = $_POST['nom'];
  $description = $_POST['description'];
  $ville = $_POST['ville'];
  $adresse = $_POST['adresse'];
  $prix = $_POST['prix'];
  $duree = $_POST['duree'];
  $type = $_POST['type'];

  // Gestion du type d'activité
  if ($type === "autre" && !empty($_POST['nvType'])) {
    $nvType = mysqli_real_escape_string($conn, $_POST['nvType']);
    mysqli_query($conn, "INSERT INTO typeactivite(nomType) VALUES ('$nvType')");
    $type = mysqli_insert_id($conn);
  }

  // Insertion dans la table activites
  $query = "INSERT INTO activites(nomActivite, descriptionA, villeA, adresseA, prixA, dureeA, idType)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ssssdii", $nom, $description, $ville, $adresse, $prix, $duree, $type);
  if ($stmt->execute()) {
    $idH = $stmt->insert_id;
    $dir = "images/activites/$idH/";
    $h_dir = "../" . $dir;
    if (!is_dir($h_dir)) {
      mkdir($h_dir, 0777, true);
    }

    // Image aperçu
    if (isset($_FILES['img_apercu']) && $_FILES['img_apercu']['error'] === 0) {
      $ap_tmp = $_FILES['img_apercu']['tmp_name'];
      $ext = pathinfo($_FILES['img_apercu']['name'], PATHINFO_EXTENSION);
      $ap_nvnm = "activite_" . $idH . "_apercu." . $ext;
      $dest_ap = $dir . $ap_nvnm;
      if (move_uploaded_file($ap_tmp, "../".$dest_ap)) {
        $chemin_ap_sql = mysqli_real_escape_string($conn, $dest_ap);
        mysqli_query($conn, "INSERT INTO imageActivite(cheminImgA, idActivite) VALUES ('$chemin_ap_sql', $idH)");
      }
    }

    // Images collection
    if (isset($_FILES['img_collection'])) {
      foreach ($_FILES['img_collection']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['img_collection']['error'][$index] === 0) {
          $ext = pathinfo($_FILES['img_collection']['name'][$index], PATHINFO_EXTENSION);
          $nm_coll = "activite_" . $idH . "_img_" . $index . "." . $ext;
          $dest_c = $dir . $nm_coll;
          if (move_uploaded_file($tmpName, "../" . $dest_c)) {
            $chemin_coll_sql = mysqli_real_escape_string($conn, $dest_c);
            mysqli_query($conn, "INSERT INTO imageActivite(cheminImgA, idActivite) VALUES ('$chemin_coll_sql', $idH)");
          }
        }
      }
    }

    echo "<p style='color: green;'>Activité ajoutée avec succès !</p>";
  } else {
    echo "<p style='color: red;'>Erreur lors de l'insertion de l'activité.</p>";
  }
}
?>

</body>
</html>
