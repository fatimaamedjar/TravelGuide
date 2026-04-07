<?php
  session_start();
  if($_SERVER['REQUEST_METHOD']=="GET"){
    if(!empty($_GET['idHotel'])){
      $q = "ALTER FROM hotels WHERE idHotel=?";
      $stmt = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($stmt, 'i', $_GET['idHotel']);
      mysqli_stmt_execute($stmt);
    }
    elseif(!empty($_GET['idActivite'])){
      $q = "ALTER FROM activites WHERE idActivite=?";
      $stmt = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($stmt, 'i', $_GET['idActivite']);
      mysqli_stmt_execute($stmt);
    }
    elseif(!empty($_GET['idChambre'])){
      $q = "ALTER FROM chambres WHERE idChambre=?";
      $stmt = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($stmt, 'i', $_GET['idChambre']);
      mysqli_stmt_execute($stmt);
    }
    elseif(!empty($_GET['idCommentaire'])){
      $q = "ALTER FROM commentaires WHERE idComm=?";
      $stmt = mysqli_prepare($conn, $q);
      mysqli_stmt_bind_param($stmt, 'i', $_GET['idCommentaire']);
      mysqli_stmt_execute($stmt);
    }
  }
?>