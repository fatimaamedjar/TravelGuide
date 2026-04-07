<?php
include './includes/entete.html';
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contactez-nous</title>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <style>
    .contact-page {
      font-family: sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }

    .contact-page form {
      margin-left: auto;
      margin-right: auto;
    }

    .contact-page .container {
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      width: 80%;
      max-width: 600px;
    }

    .contact-page header {
      text-align: center;
      margin-bottom: 20px;
    }

    .contact-page header h1 {
      color: #333;
      margin-bottom: 5px;
    }

    .contact-page header p {
      color: #777;
      font-size: 0.9em;
    }

    .contact-page .form-group {
      margin-bottom: 20px;
    }

    .contact-page .form-group label {
      display: block;
      margin-bottom: 5px;
      color: #555;
      font-size: 0.95em;
      font-weight: bold;
    }

    .contact-page .form-group input[type="text"],
    .contact-page .form-group input[type="email"],
    .contact-page textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1em;
      box-sizing: border-box;
    }

    .contact-page textarea {
      height: 200px;
    }

    .contact-page .form-group select {
      appearance: none;
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 16px;
    }

    .contact-page .password-field {
      display: flex;
      gap: 25px;
      width: 100%;
    }

    .contact-page .submit-button {
      background-color: #333;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1.1em;
      width: 100%;
      transition: background-color 0.3s ease;
      margin-top: 20px;
    }

    .contact-page .submit-button:hover {
      background-color: #555;
    }
  </style>
</head>
<body>

  <div class="contact-page">
    <div class="container">

      <header>
        <h1>Contactez-nous</h1>
        <p>Pour contacter le service client, nous vous recommandons de privilégier le formulaire en ligne :</p>
      </header>

      <form action="env-email.php" method="POST">

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
          <label for="email">Adresse E-mail*</label>
          <input type="email" id="email" name="email" placeholder="example@email.com" required>
        </div>

        <div class="form-group">
          <label for="msg">Quelle est votre question ?</label>
          <textarea name="msg" id="msg" required></textarea>
        </div>

        <div class="g-recaptcha" data-sitekey="6Leb9zUrAAAAAJNp5fRsGqeLjWiPcIy7ZbzElp3p"></div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['cap']) && isset($_GET['capcol'])) {
          $cap = htmlspecialchars($_GET['cap']);
          $cap_col = htmlspecialchars($_GET['capcol']);
          echo "<div class='form-group'>
                  <div style='width: 80%; text-align: left; color: $cap_col; font-size: 20px;'>$cap</div>
                </div>";
        }
        ?>

        <button type="submit" class="submit-button">Envoyer</button>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['aff'])) {
          $aff = htmlspecialchars($_GET['aff']);
          $aff_col = htmlspecialchars($_GET['affcol']);
          echo "<div class='form-group'>
                  <div style='width: 80%; text-align: center; margin: 20px auto 0; color: green; font-size: 20px;'>$aff</div>
                </div>";
        }
        ?>
      </form>
    </div>
  </div>

</body>
</html>
