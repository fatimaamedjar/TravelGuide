<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="container">

        <header>
            <h1>Accéder à votre compte</h1>
            <p>Afin de poursuivre votre expérience</p>
        </header>

        <form action="" method="POST">
            <?php
            $email = '';

            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['email'])) {
                $email = $_GET['email'];
            } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
                $email = $_POST['email'];
            }
            ?>

            <div class="form-group">
                <label for="email">Adresse E-mail*</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="mdp">Mot de passe*</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>

            <button type="submit" class="submit-button">Accéder</button>
        </form>

        <?php
        require_once '../includes/connexion.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $mdp = $_POST['mdp'];

            $q = "SELECT idClient, emailC, motPasseC FROM clients WHERE emailC = ?";
            $stmt = mysqli_prepare($conn, $q);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            if ($res && mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if (password_verify($mdp, $row['motPasseC'])) {
                    $_SESSION['client_auth'] = 'oui';
                    $_SESSION['email_client'] = $row['emailC'];
                    $_SESSION['id_client'] = $row['idClient'];
                    header("Location: ../index.php");
                    exit();
                } else {
                    echo "<p style='color:red;'>Mot de passe incorrect.</p>";
                }
            } else {
                echo "<p style='color:red;'>Adresse e-mail non reconnue.</p>";
            }
        }
        ?>

    </div>
</body>
</html>
