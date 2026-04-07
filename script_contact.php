<?php
  ob_start();

  require 'vendor/autoload.php';

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;

  if($_SERVER['REQUEST_METHOD']=="POST"){
    $nomcomplet = $_POST['prenom']." ".$_POST['nom'];
    $email = $_POST['email'];
    $msg = $_POST['msg'];
    //$message= str_replace(["\r\n", "\r", "\n"], "\\n", $msg);
    $message = nl2br($msg);

    $mail = new PHPMAILER(true);

    $fp = fsockopen("smtp.gmail.com", 587, $errno, $errstr, 10);
    if (!$fp) {
        echo "Erreur : $errstr ($errno)";
    } else {
        echo "Connexion réussie";
        fclose($fp);
    }

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
      header("Location: index.php?aff=Message envoyé avec succès !");
      exit();

    }
    catch(Exception $e){
      //echo "Votre message ne peut pas être envoyer : {$mail->ErrorInfo}";
      header("Location: index.php?aff=Erreur lors de l'envoi : " . urlencode($mail->ErrorInfo));
      exit();
    }

  }

?>