<!DOCTYPE html>

<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiement Annulé</title>
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
  color: #d32f2f;
  font-size: 28px;
  margin-bottom: 25px;
}

.cancel-message {
  background-color: #fdecea;
  border: 1px solid #f5c6cb;
  padding: 20px;
  border-radius: 8px;
  color: #b71c1c;
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
  margin: 0 auto;
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
  <h1>Paiement Annulé</h1>
  <div class="cancel-message">
    <p>Votre paiement a été annulé. Aucun frais n'a été prélevé.</p>
  </div>
  <a class="button" href="./pay_reservation_api.php">Retour à la page de paiement</a>
</div>
</body>
</html>