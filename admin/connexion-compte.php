<?php
session_start();

if (isset($_SESSION['adm_auth']) && $_SESSION['adm_auth'] == 'oui') {
    header("Location: dashboard.php");
    exit();
}

require_once '../includes/connexion.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $login = $_POST['login'];
    $mdp = $_POST['mdp'];

    $q = "SELECT loginA, motPasseA FROM admins WHERE loginA = ?";
    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "s", $login);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) > 0) {
        $l = mysqli_fetch_assoc($res);
        if ($mdp == $l['motPasseA']) {
            $_SESSION['adm_auth'] = 'oui';
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Mot de passe incorrect.";
        }
    } else {
        $message = "Login n'existe pas.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin-login.css">
  <style>    
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
    }

    
    body {
    background: linear-gradient(135deg, #ece9e6, #ffffff);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 30px;
    }

    
    form {
    background-color: #fff;
    padding: 30px 40px;
    border-radius: 16px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
    }

    h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #2c3e50;
    }

    
    form label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
    color: #2c3e50;
    }

    form input[type="text"],
    form input[type="password"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: border-color 0.3s ease;
    }

    form input[type="text"]:focus,
    form input[type="password"]:focus {
    outline: none;
    border-color: #7fc142;
    }

    
    form input[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #7fc142;
    border: none;
    color: white;
    font-weight: bold;
    font-size: 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    }

    form input[type="submit"]:hover {
    background-color: rgb(113, 168, 60);
    }

    
    p {
    text-align: center;
    margin-top: 15px;
    color: red;
    font-weight: 500;
    }

  </style>
</head>
<body>
  <form action="" method="POST">
    <h1>Connexion admin</h1>
    <label>Login</label>
    <input type="text" name="login" id="login" required>
    <br>
    <label>Mot de passe</label>
    <input type="password" name="mdp" id="mdp" required>
    <br>
    <input type="submit" value="Connexion">
  </form>

  <?php
    if (!empty($message)) {
        echo "<p style='color:red;'>$message</p>";
    }
  ?>
</body>
</html>
