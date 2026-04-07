<?php
session_start();
if($_SESSION['id_client']===null){
  header("Location: compte/index.php");
  exit();
}
include_once 'includes/entete.html';
$pdo = new PDO("mysql:host=localhost;dbname=travelguide_t", "root", ""); 

$idClient = $_SESSION['id_client']; 
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["modifier"])) {
        $nom = strtoupper($_POST["nomC"]);
        $prenom = ucwords($_POST["prenomC"]);
        $dateNaiss = $_POST["dateNaissC"];
        $tel = $_POST["telC"];
        $email = $_POST["emailC"];
        $idPays = $_POST["idPays"];

        $photoPath = null;

        if (!empty($_FILES["photoC"]["name"])) {
            $targetDir = "images/profiles/utilisateurs/";
            if (!is_dir($targetDir)) mkdir($targetDir);
            $photoPath = $targetDir . basename($_FILES["photoC"]["name"]);
            move_uploaded_file($_FILES["photoC"]["tmp_name"], $photoPath);
        }

        $sql = "UPDATE clients SET nomC=?, prenomC=?, dateNaissC=?, telC=?, emailC=?, idPays=?" .
               ($photoPath ? ", photoC=?" : "") . " WHERE idClient=?";
        $params = [$nom, $prenom, $dateNaiss, $tel, $email, $idPays];
        if ($photoPath) $params[] = $photoPath;
        $params[] = $idClient;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $msg = "Profil mis à jour avec succès.";
    }

    if (isset($_POST["changer_mdp"])) {
        $oldPassword = $_POST["old_password"];
        $newPassword = $_POST["new_password"];
        $new2Password = $_POST["new2_password"];

        $stmt = $pdo->prepare("SELECT motPasseC FROM clients WHERE idClient = ?");
        $stmt->execute([$idClient]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($oldPassword, $result['motPasseC'])) {
            if ($newPassword === $new2Password) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE clients SET motPasseC = ? WHERE idClient = ?");
                $stmt->execute([$hashedPassword, $idClient]);

                $msg = "Mot de passe changé avec succès.";
            } else {
                $msg = "Les nouveaux mots de passe ne correspondent pas.";
            }
        } else {
            $msg = "Ancien mot de passe incorrect.";
        }
    }


    if (isset($_POST["supprimer"])) {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE idClient=?");
        $stmt->execute([$idClient]);
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM clients WHERE idClient=?");
$stmt->execute([$idClient]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon compte</title>
    <style>
        .profil-page * {
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        }

        .profil-page {
        margin: 0;
        background-color: #f5f7fa;
        padding: 40px;
        }

        .profil-page .container {
        max-width: 800px;
        margin: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        padding: 40px;
        }

        .profil-page h2 {
        font-size: 26px;
        margin-bottom: 30px;
        color: #111827;
        }

        .profil-page .section {
        margin-bottom: 35px;
        }

        .profil-page label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #374151;
        }

        .profil-page input[type="text"],
        .profil-page input[type="email"],
        .profil-page input[type="date"],
        .profil-page input[type="tel"],
        .profil-page input[type="password"],
        .profil-page select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        background-color: #f9fafb;
        transition: border-color 0.2s ease-in-out;
        }

        .profil-page input[type="text"]:focus,
        .profil-page input[type="email"]:focus,
        .profil-page input[type="date"]:focus,
        .profil-page input[type="tel"]:focus,
        .profil-page input[type="password"]:focus {
        border-color: #2563eb;
        outline: none;
        }

        .profil-page img#preview {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        margin-top: 15px;
        border: 2px solid #ccc;
        display: block;
        object-fit: cover;
        }

        .profil-page input[type="file"] {
        margin-top: 10px;
        }

        .profil-page button {
        padding: 10px 20px;
        font-size: 14px;
        background-color: #7fc142;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s;
        }

        .profil-page button:hover {
        background-color: rgb(116, 177, 59);
        }

        .profil-page .delete-btn, .profil-page .dcnx-btn {
        background-color: #ef4444;
        }

        .profil-page .delete-btn:hover, .profil-page .dcnx-btn:hover {
        background-color: #dc2626;
        }

        .profil-page .success-msg {
        background-color: #d1fae5;
        color: #065f46;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #10b981;
        }

        .profil-page .btn-maj {
        margin-top: -30px;
        margin-bottom: 20px;
        }


    </style>
</head>
<body>

<div class="profil-page">
    <div class="container">
        <h2>Paramétres du compte</h2>

        <?php if ($msg): ?>
            <div class="success-msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="section">
                <h3>Informations du profile</h3>
                <label>Prénom</label>
                <input type="text" name="prenomC" value="<?= htmlspecialchars($client['prenomC']) ?>" required>

                <label>Nom</label>
                <input type="text" name="nomC" value="<?= htmlspecialchars($client['nomC']) ?>" required>

                <label>Image</label>
                <input type="file" name="photoC" accept="image/*" onchange="previewImage(event)">
                <?php if ($client['photoC']): ?>
                    <img id="preview" src="<?= $client['photoC'] ?>" alt="Image de profil">
                <?php else: ?>
                    <img id="preview" src="#" style="display:none;">
                <?php endif; ?>
            </div>

            <hr>

            <div class="section">
                <h3>Informations du compte</h3>

                <label>Date de Naissance</label>
                <input type="date" name="dateNaissC" value="<?= $client['dateNaissC'] ?>">

                <label>Téléphone</label>
                <input type="tel" name="telC" value="<?= htmlspecialchars($client['telC']) ?>">

                <label>E-mail</label>
                <input type="email" name="emailC" value="<?= htmlspecialchars($client['emailC']) ?>" required>

                <label>Pays</label>
                <select name="idPays">
                    <?php
                        require_once 'includes/connexion.php';
                        $q = "SELECT * FROM pays";
                        $res = mysqli_query($conn, $q);
                        while($l = mysqli_fetch_assoc($res)){
                            $selected = ($client['idPays'] == $l['idPays']) ? 'selected' : '';
                            echo "<option value='" . $l['idPays'] . "' $selected>" . $l['nomPays'] . "</option>";
                        }
                    ?>
                </select>

            </div>

            <button type="submit" class='btn-maj' name="modifier">Mettre à jour</button>
        </form>

        <hr>

        <form method="post">
            <div class="section">
                <h3>Changer le mot de passe</h3>
                <label>Ancien mot de passe</label>
                <input type="password" name="old_password" required>
                <label>Nouveau mot de passe</label>
                <input type="password" name="new_password" required>
                <label>Reecrire le nouveau mot de passe</label>
                <input type="password" name="new2_password" required>
                <button type="submit" name="changer_mdp">Changer le mot de passe</button>
            </div>
        </form>

        <hr>

        <h3>Déconnexion</h3>
        <form method="post" action="./compte/deconnexion.php">
            <button type="submit" name="deconnexion" class="dcnx-btn">Déconnecter</button>
        </form>

        <hr>

        <h3>Suppression de compte</h3>
        <form method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ?')">
            <button type="submit" name="supprimer" class="delete-btn">Supprimer le compte</button>
        </form>
    </div>
</div>

    <script>
        function previewImage(event) {
            const output = document.getElementById('preview');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.style.display = 'block';
        }
    </script>
</body>
</html>
