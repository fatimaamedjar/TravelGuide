
<?php
session_start();
require_once '../includes/connexion.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];

    $q = 'SELECT emailC FROM clients WHERE emailC = ?';
    $stmt = mysqli_prepare($conn, $q);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_store_result($stmt);

        $nb_res = mysqli_stmt_num_rows($stmt);

        if ($nb_res == 1) {
            header("Location: login.php?email=" . urlencode($email));
            exit();
        } else {
            header("Location: creer-compte.php?email=" . urlencode($email));
            exit();
        }
    } else {
        echo "Erreur lors de la préparation de la requête.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte</title>
    <link rel="stylesheet" href="../css/connexion-compte.css">
    <style>
      header{
        margin-bottom: 60px;
      }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Veuillez entrer votre e-mail</h1>
        </header>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label for="email">Adresse E-mail*</label>
                <input type="email" id="email" name="email" placeholder="example@email.com" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
            </div>
            <button type="submit" class="submit-button">Continuer</button>
        </form>
    </div>
</body>
</html>