<!DOCTYPE html>
<html lang="en">
<head>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    /* Add the ID prefix to all selectors to ensure specificity */
    #tg-footer * {
      font-family: Arial, Helvetica, sans-serif;
      box-sizing: border-box;
    }

    #tg-footer .newsletter {
      position: relative;
      z-index: 2;
      /* Adjust margin-bottom if it causes content to be hidden */
      /*margin-bottom: -80px;*/
      margin-top: 40px; /* This value might need tweaking based on overall layout */
    }

    #tg-footer .newsletter .container {
      background: #7fc142;
      padding: 40px 30px;
      width: 40%;
      margin: 0 auto;
      border-radius: 4px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    #tg-footer .newsletter h1 {
      font-size: 24px;
      font-weight: 500;
      color: #fff;
      margin: 0 0 15px 0;
      flex: 1 1 100%; /* Ensures heading takes full width */
    }

    #tg-footer .newsletter input[type="email"] {
      padding: 15px 20px;
      border: none;
      border-radius: 4px;
      background-color: rgba(255, 255, 255, 0.6);
      color: #333;
      flex: 1 1 70%; /* Allows email input to take a good portion of width */
      margin-right: 15px;
      width: 500px; /* Keep this in mind for responsiveness */
      max-width: 100%; /* Add this for better responsiveness */
    }

    #tg-footer .newsletter input[type="submit"] {
      background: #fff;
      color: #111;
      border: none;
      padding: 15px 30px;
      border-radius: 4px;
      font-weight: 700;
      cursor: pointer;
      text-transform: uppercase;
      transition: background 0.3s ease;
    }

    #tg-footer .newsletter input[type="submit"]:hover {
      background: #eee;
    }

    #tg-footer footer {
      background: #282834;
      color: #fff;
      padding: 140px 8% 60px; /* Top padding is large to account for negative margin of newsletter */
      position: relative;
      z-index: 1;
    }

    #tg-footer footer .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 40px;
    }

    #tg-footer footer .box img {
      width: 45px;
      height: 45px;
      margin-bottom: 0;
    }

    #tg-footer footer .box h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    #tg-footer footer .box p {
      font-size: 14px;
      color: #ccc;
      line-height: 1.6;
    }

    #tg-footer footer ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    #tg-footer footer li a {
      font-size: 14px;
      color: #ccc;
      margin-bottom: 10px;
      transition: color 0.3s;
      text-decoration: none;
      display: block; /* Ensures the whole link area is clickable */
    }

    #tg-footer footer li a:hover {
      color: #7fc142;
    }

    #tg-footer footer .icon {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
    }

    #tg-footer footer .icon i {
      font-size: 16px;
      color: #7fc142;
      margin-right: 10px;
    }

    #tg-footer footer .icon p,
    #tg-footer footer .icon a {
      font-size: 14px;
      color: #ccc;
      margin: 0;
    }

    #tg-footer footer .social-icons {
      margin-top: 20px;
    }

    #tg-footer footer .social-icons i {
      font-size: 18px;
      margin-right: 15px;
      color: #ccc;
      transition: color 0.3s;
    }

    #tg-footer footer .social-icons i:hover {
      color: #7fc142;
    }

    #tg-footer .legal {
      background: #111;
      color: #aaa;
      text-align: center;
      padding: 15px 0;
      font-size: 13px;
      margin-top: 30px;
    }

    /* Responsive adjustments for the newsletter */
    @media (max-width: 768px) {
      #tg-footer .newsletter .container {
        width: 80%; /* Make newsletter wider on smaller screens */
        flex-direction: column; /* Stack elements vertically */
        padding: 30px 20px;
      }
      #tg-footer .newsletter h1 {
        text-align: center;
        margin-bottom: 20px;
      }
      #tg-footer .newsletter {
        position: relative;
        z-index: 10; /* FORCE la newsletter au-dessus du footer */
        margin-bottom: -80px;
        margin-top: 40px;
      }
      #tg-footer .newsletter input {
        opacity: 1;
        visibility: visible;
      }


      #tg-footer footer {
        padding-top: 100px; /* Adjust if newsletter pushes it too low */
      }
    }
  </style>
</head>
<body>

<div id="tg-footer">
  <section class="newsletter">
    <div class="container">
      <h1>Subscribe to Our Newsletter</h1>
      <form action="" method="POST">
        <input type="email" name="email" placeholder="Your Email">
        <input type="submit" value="Subscribe">
      </form>
    </div>
  </section>

  <footer>
    <div class="container grid">
      <div class="box">
        <img src="./images/logo/TG_logo_trns_icon.png" alt="TravelGuide Logo">
        <p>
          <br>
          <br>
          Ce site web offre la réservation de nombreux hotels au Maroc.
        </p>
      </div>

      <div class="box">
        <h2>Links</h2>
        <ul>
          <li><a href="./nos_hotels.php">Hotels</a></li><br>
          <!--<li><a href="./nos_activites.php">Activites</a></li><br>-->
          <li><a href="./contact.php">Contact Us</a></li>
        </ul>
      </div>

      <div class="box">
        <h2>Contact Us</h2>
        <p>Pour plus d'informations, veuillez nous contacter à l'adresse suivante :</p>
        <!--<i class="fa fa-envelope"></i>-->
        <label>TravelGuide@gmail.com</label>
      </div>
    </div>
  </footer>
</div>
</body>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/connexion.php'; 
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL); 
    if (!$email) {
        echo "<script>alert('Veuillez entrer une adresse email valide.')</script>";
        
        exit; 
    }
    $date_join = date("Y-m-d H:i:s"); 
    $q = "INSERT INTO newsletter (emailNL, date_join) VALUES (?, ?)";
    if ($stmt = mysqli_prepare($conn, $q)) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $date_join);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Bienvenue à bord ! Préparez-vous à recevoir nos meilleures offres d\'hôtels, les activités incontournables et toutes nos actualités directement dans votre boîte mail.')</script>";
        } else {   
            error_log("Error executing statement: " . mysqli_stmt_error($stmt));
            echo "<script>alert('Une erreur est survenue lors de l\'inscription. Veuillez réessayer plus tard.')</script>";
        }    
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error preparing statement: " . mysqli_error($conn));
        echo "<script>alert('Une erreur interne est survenue. Veuillez réessayer.')</script>";
    }
    mysqli_close($conn);
}
?>

</html>