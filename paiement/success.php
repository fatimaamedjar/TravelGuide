
<!DOCTYPE html>

<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiement Réussi</title>
<link rel="stylesheet" href="style.css">
<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f0f2f5;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
}

.form-container {
  background-color: #ffffff;
  padding: 40px 30px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  max-width: 500px;
  width: 100%;
  text-align: center;
}

h1 {
  color: #2e7d32;
  font-size: 28px;
  margin-bottom: 25px;
}

.success-message {
  background-color: #e6f4ea;
  border: 1px solid #c8e6c9;
  padding: 20px;
  border-radius: 8px;
  color: #2e7d32;
  text-align: left;
  margin-bottom: 30px;
  font-size: 16px;
}

a.button {
  display: block;
  background-color: #7fc142;
  color: white;
  text-decoration: none;
  padding: 14px 20px;
  border-radius: 6px;
  font-size: 16px;
  margin: 10px auto;
  transition: background-color 0.3s ease;
  width: 100%;
  max-width: 300px;
}

a.button:hover {
  background-color: #6db536;
  text-decoration: none;
}

@media (max-width: 500px) {
  .form-container {
    padding: 30px 20px;
  }

  h1 {
    font-size: 22px;
  }
}


</style>
</head>
<body>
<div class="form-container">
<h1>Paiement Réussi !</h1>
<div class="success-message">
<p>Merci pour votre paiement. Votre transaction a été complétée.</p>
<p>ID de commande PayPal: <?php echo htmlspecialchars($_GET['orderID'] ?? 'Non disponible'); ?></p>
</div>

<a class="button" href="./pdf.php?id_order=<?= $_GET['orderID'] ?>">Télécharger le bon de réservation</a>
<a class="button" href="../index.php">Retour à la page d'accueil</a>

</body>
</html>