<?php
session_start();
require_once '../includes/connexion.php';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $prenom = ucwords(trim($_POST['prenom']));
    $nom = strtoupper(trim($_POST['nom']));
    $ddn = $_POST['ddn'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];
    $mdp_h = password_hash($mdp, PASSWORD_BCRYPT);

    $codep = $_POST['codepays'] ?? null;
    $tel = $_POST['tel'] ?? null;
    $num = ($codep && $tel) ? "+".$codep.$tel : null;

    $pays = $_POST['pays'];
    $err = [];

    $path_ch = "../images/profiles/utilisateurs/";
    $photo_ch = "./images/profiles/default-pfp.png"; // Chemin pour la base de données

    if (isset($_FILES['pdp']) && $_FILES['pdp']['error'] === 0) {
        $tmpPath = $_FILES['pdp']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['pdp']['name'], PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $srcImage = ($extension === 'png') ? imagecreatefrompng($tmpPath) : imagecreatefromjpeg($tmpPath);
            if (!$srcImage) {
                $err[] = "Erreur lors de la lecture de l'image.";
            } else {
                $width = imagesx($srcImage);
                $height = imagesy($srcImage);
                $size = min($width, $height);
                $x = ($width - $size) / 2;
                $y = ($height - $size) / 2;

                $cropped = imagecrop($srcImage, ['x' => $x, 'y' => $y, 'width' => $size, 'height' => $size]);

                if ($cropped !== false) {
                    $photoName = uniqid() . '.png';
                    $fullPath = $path_ch . $photoName; // Exemple: ../images/profiles/utilisateurs/abc123.png
                    $dbPath = "./images/profiles/utilisateurs/" . $photoName; // Pour base de données

                    if (imagepng($cropped, $fullPath)) {
                        $photo_ch = $dbPath; // Ce chemin ira en base
                    } else {
                        $err[] = "Erreur lors de l'enregistrement de l'image.";
                    }

                    imagedestroy($cropped);
                    imagedestroy($srcImage);
                } else {
                    $err[] = "Erreur lors du recadrage de l'image.";
                }
            }
        } else {
            $err[] = "Format d'image non pris en charge.";
        }
    }


    if (empty($err)) {
        $q = "INSERT INTO clients(nomC, prenomC, dateNaissC, telC, emailC, photoC, motPasseC, idPays)
        VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $q);
        mysqli_stmt_bind_param($stmt, "sssssssi", $nom, $prenom, $ddn, $num, $email, $photo_ch, $mdp_h, $pays);
        if (mysqli_stmt_execute($stmt)) {
            $idC = mysqli_insert_id($conn);
            $_SESSION['id_client'] = $idC;
            $_SESSION['email_client'] = $email;
            $_SESSION['auth_client'] = 'oui';
            header("Location: ../index.php");
            exit;
        } else {
            $err[] = "Erreur lors de la création de votre compte.";
        }
    }

    if (!empty($err)) {
        echo "<div class='erreurs'>";
        foreach ($err as $e) {
            echo "<p style='color:red;'>$e</p>";
        }
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Création de compte</title>
  <link rel="stylesheet" href="../css/creer-compte.css">
  <script type="text/javascript">
    function validate() {
      var msg;
      var mdp = document.getElementById("mdp").value;
      /*if (mdp.match(/[0-9]/g) && mdp.match(/[A-Z]/g) && mdp.match(/[a-z]/g) && mdp.match(/[^a-zA-Z\d]/g) && mdp.length >= 8) {
        msg = "<p style='color:green'>Mot de passe fort.</p>";
      } else {
        msg = "<p style='color:red'>Mot de passe faible.</p>";
      }
      document.getElementById("msg").innerHTML = msg;*/
    }

    function verifMdp() {
      var mdp1 = document.getElementById("mdp").value;
      var mdp2 = document.getElementById("mdp2").value;
      var msgMdp;
      if (mdp1 != mdp2) {
        msgMdp = "<p style='color:red'>Les mots de passe ne correspondent pas!</p>";
      } else {
        msgMdp = "<p style='color:green'>Les mots de passe correspondent!</p>";
      }
      document.getElementById("msgMdp").innerHTML = msgMdp;
    }

    var loadFile = function(event) {
      var output = document.getElementById('output');
      output.src = URL.createObjectURL(event.target.files[0]);
      output.onload = function() {
        URL.revokeObjectURL(output.src);
      };
    };
  </script>
</head>
<body>
<div class="container">
  <header>
    <h1>Créer votre compte</h1>
    <p>Afin de poursuivre votre expérience</p>
  </header>

  <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
    <div class="name-fields">
      <div class="form-group">
        <label for="prenom">Prénom*</label>
        <input type="text" id="prenom" name="prenom" required>
      </div>
      <div class="form-group">
        <label for="nom">Nom*</label>
        <input type="text" id="nom" name="nom" required>
      </div>
    </div>

    <div class="form-group">
      <label for="ddn">Date de naissance*</label>
      <input type="date" id="ddn" name="ddn" required>
    </div>

    <?php @$email = $_GET['email'] ?? ''; ?>

    <div class="form-group">
      <label for="email">Adresse E-mail*</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
    </div>

    <div class="password-field">
      <div class="form-group">
        <label for="mdp">Mot de passe*</label>
        <input type="password" id="mdp" name="mdp" oninput="validate()" required>
        <span id="msg"></span>
      </div>
      <div class="form-group">
        <label for="mdp2">Ressaisir le mot de passe*</label>
        <input type="password" id="mdp2" name="mdp2" oninput="verifMdp()" required>
        <span id="msgMdp"></span>
      </div>
    </div>

    <div class="phone-fields">
      <div class="form-group">
        <label for="codepays">Code de pays</label>
        <select id="codepays" name="codepays">
          <?php
          $q = "SELECT * FROM pays";
          $res = mysqli_query($conn, $q);
          if ($res) {
            while ($l = mysqli_fetch_assoc($res)) {
              echo "<option value='" . $l['indicatif'] . "'>" . $l['nomPays'] . " (" . $l['indicatif'] . ")</option>";
            }
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="tel">N° de téléphone</label>
        <input type="tel" id="tel" name="tel" placeholder="(XXX) XXX XXXX">
      </div>
    </div>

    <div class="form-group">
      <label for="pays">Pays*</label>
      <select id="pays" name="pays" required>
        <?php
        $res = mysqli_query($conn, "SELECT * FROM pays");
        if ($res) {
          while ($l = mysqli_fetch_assoc($res)) {
            echo "<option value='" . $l['idPays'] . "'>" . $l['nomPays'] . "</option>";
          }
        }
        ?>
      </select>
    </div>

    <div class="form-group">
      <label for="pdp">Photo de profil</label>
      <div class="profiles">
        <img id="img-defaut" src="../images/profiles/default-pfp.png">
        <img id="output"/>
      </div>
      <input type="file" accept="image/*" onchange="loadFile(event)" name="pdp">
    </div>

    <div class="form-group agreement">
      <input type="checkbox" id="agreement" name="agreement" required>
      <label for="agreement">J'accepte les <a href="#">Conditions d'utilisation</a> et la <a href="#">Politique de confidentialité</a>.</label>
    </div>

    <button type="submit" class="submit-button">Créer le compte</button>
  </form>

</div>
</body>
</html>
