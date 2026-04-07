<?php
  ob_start();

  require 'vendor/autoload.php';

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;

  $secret = '6Leb9zUrAAAAAOdCbZy2Zwk2mCjH31Cmuf-HX9hA';

  if($_SERVER['REQUEST_METHOD']=="POST"){
    $nomcomplet = $_POST['prenom']." ".$_POST['nom'];
    $email = $_POST['email'] ?? null;
    $msg = $_POST['msg'] ?? null;
    //$message= str_replace(["\r\n", "\r", "\n"], "\\n", $msg);
    $message = nl2br($msg);

    $captcha = $_POST['g-recaptcha-response'] ?? null;

    if (!$captcha) {
      //echo "❌ Veuillez valider le captcha.";
      header("Location: index.php?capcol=red&cap=Veuillez valider le captcha");
      exit;
    }

    $mail = new PHPMAILER(true);

    $fp = fsockopen("smtp.gmail.com", 587, $errno, $errstr, 10);
    if (!$fp) {
      echo "Erreur : $errstr ($errno)";
    } else {
      echo "Connexion réussie";
      fclose($fp);
    }

    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha");
    $responseData = json_decode($verifyResponse);

    if ($responseData->success) {
      try{
        //Server settings
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->Username = 'amedjarfatima6@gmail.com'; 
        $mail->Password = 'jspf qrgp kwgf pond';

        //Recipients
        $mail->setFrom($email, $nomcomplet);
        $mail->addAddress('amedjarfatima6@gmail.com', 'TravelGuide');

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Formulaire de contact';
        $body = "<table>
                  <tr><th>Nom Complet : </th><td>$nomcomplet</td></tr>
                  <tr><th>E-mail : </th><td>$email</td></tr>
                  <tr><th>Message : </th><td>$message</td></tr>
                </table>";
        $mail->MsgHTML($body);
        $mail->AltBody = "Nom Complet : $nomcomplet\nE-mail : $email\n Message : \n$message";


        $mail->send();
        //echo 'Votre message est envoyé';
        header("Location: index.php?affcol=green&aff=Message envoyé avec succès !");
        exit();

      }
      catch(Exception $e){
        //echo "Votre message ne peut pas être envoyer : {$mail->ErrorInfo}";
        header("Location: index.php?affcol=red&aff=Erreur lors de l'envoi : " . urlencode($mail->ErrorInfo));
        exit();
      }

    } else {
        //echo "❌ Le captcha est invalide. Merci de réessayer.";
        header("Location: index.php?capcol=red&cap=Le captcha est invalide. Merci de réessayer.");
        exit;
    }
  }

?>
