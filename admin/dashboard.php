<?php
  session_start();
  if(!isset($_SESSION['adm_auth']) || $_SESSION['adm_auth']!="oui" ){
    header("Location: connexion-compte.php");
  }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
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
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      color: #333;
    }

    h1 {
      font-size: 2rem;
      color: #2c3e50;
      margin-bottom: 40px;
      text-align: center;
    }

    .dashboard-container {
      background-color: #ffffff;
      width: 100%;
      max-width: 600px;
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
      transition: all 0.3s ease-in-out;
    }

    .dashboard-container a {
      display: flex;
      align-items: center;
      text-decoration: none;
      background-color: #7fc142;
      color: white;
      padding: 15px 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .dashboard-container a:hover {
      background-color:rgb(113, 168, 60);
      transform: scale(1.02);
    }

    .dashboard-container a i {
      margin-right: 10px;
      font-size: 18px;
    }

  </style>
</head>
<body>
  <h1>Tableau de bord des administrateurs</h1>
  <div class="dashboard-container">
    <a href="ajouter-admins.php"><i class="fas fa-user-plus"></i> Ajouter un administrateur</a>
    <a href="ajouter-hotel.php"><i class="fas fa-hotel"></i> Ajouter un hôtel</a>
    <a href="gerer-commentaires.php"><i class="fas fa-comments"></i> Gérer les commentaires</a>
    <a href="gerer-hotels.php"><i class="fas fa-hotel"></i> Gérer les hôtels</a>
    <a href="gerer-chambres.php"><i class="fas fa-bed"></i> Gérer les chambres</a>
    <a href="deconnexion.php" style="background-color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </div>
  
</body>
</html>
