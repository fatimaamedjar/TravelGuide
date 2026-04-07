<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Document</title>
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
    max-width: 500px;
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
  form input[type="password"],
  form input[type="email"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: border-color 0.3s ease;
  }

  form input[type="text"]:focus,
  form input[type="password"]:focus,
  form input[type="email"]:focus {
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

  <form action="ajouter-admins.php" method="POST">
    <h1>Ajouter admin</h1>
    <label>Nom</label>
    <input type="text" name="nom" id="nom">
    <br>
    <label>Login</label>
    <input type="text" name="login" id="login">
    <br>
    <label>E-mail</label>
    <input type="email" name="email" id="email">
    <br>
    <label>Mot de passe</label>
    <input type="password" name="mdp" id="mdp">
    <br>
    <input type="submit" value="Ajouter">
    <br>
  </form>
  <?php
    require_once '../includes/connexion.php';

    if($_SERVER['REQUEST_METHOD']=="POST"){
      $nom = $_POST['nom'];
      $login = $_POST['login'];
      $email = $_POST['email'];
      $mdp = password_hash($_POST['mdp'], PASSWORD_BCRYPT);

      $q = "INSERT INTO admins(nomA, loginA, emailA, motPasseA) VALUES(?, ?, ?, ?)";
      $stmt = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($stmt, "ssss", $nom, $login, $email, $mdp);
      if(mysqli_stmt_execute($stmt)){
        echo "Admin ajoutee";
      }
    }

  ?>
</body>
</html>