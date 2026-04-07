<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/entete.html';
require_once 'includes/connexion.php';
?>

<?php
  require_once 'includes/entete.html';
  require_once 'includes/connexion.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TravelGuide</title>
  <link rel="stylesheet" href="./css/styleind.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css" integrity="sha512-UTNP5BXLIptsaj5WdKFrkFov94lDx+eBvbKyoe1YAfjeRPC+gT5kyZ10kOHCfNZqEui1sxmqvodNUx3KbuYI/A==" crossorigin="anonymous"
    referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
</head>

<body>

  <section class="home">
    <div class="content">
      <div class="owl-carousel owl-theme">
        <div class="item">
          <img src="images/galerie/banner-1.png" alt="">
          <div class="text">
            <h1>Planifier votre séjour</h1>
            <p>Réserver vos hôtels et activités, et consulter le chatbot afin de vous guider.</p>
            <div class="flex">
              <button class="primary-btn"><a href="index.php#Reservation">RESERVER</a></button>
              <button class="secondary-btn"><a href="chatbot.html">CHATBOT</a></button>
            </div>
          </div>
        </div>
        <div class="item">
          <img src="images/galerie/banner-2.png" alt="">
          <div class="text">
            <h1>Planifier votre séjour</h1>
            <p>Réserver vos hôtels et activités, et consulter le chatbot afin de vous guider.</p>
            <div class="flex">
              <button class="primary-btn"><a href="index.php#Reservation">RESERVER</a></button>
              <button class="secondary-btn"><a href="chatbot.html">CHATBOT</a></button>
            </div>
          </div>
        </div>
        <div class="item">
          <img src="images/galerie/banner-3.png" alt="">
          <div class="text">
            <h1>Planifier votre séjour</h1>
            <p>Réserver vos hôtels et activités, et consulter le chatbot afin de vous guider.</p>
            <div class="flex">
              <button class="primary-btn"><a href="index.php#Reservation">RESERVER</a></button>
              <button class="secondary-btn"><a href="chatbot.html">CHATBOT</a></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.js" integrity="sha512-gY25nC63ddE0LcLPhxUJGFxa2GoIyA5FLym4UJqHDEMHjp8RET6Zn/SHo1sltt3WuVtqfyxECP38/daUc/WVEA==" crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
 


  <div class="contenaire">
        <div class="texte">
            <h3>Bienvenue chez Travel<span>Guide</span></h3>
            <h2>Réservez votre séjour dès aujourd'hui !</h2>
        </div>

        <div class="images">
            <img src="images/galerie/8ff04bc13b043c28f53e72351690dfcd.jpg" alt="Hotel View" class="image-transition">
            <img src="images/galerie/background-2.png" alt="Customer" class="image-transition">
        </div>
    </div>


      <div id="Reservation">
      <br>
      <br>
      <br>
      
      <?php
        include_once 'includes/ind.php';
      ?>
      </div>

  <section class="rooms">
    <div class="container">

      <div class=" header-section">
        <div class="header-text">
          <h2 class="main-title">NOS HÔTELS</h2>
        </div>
      </div>

      <div class="owl-carousel owl-theme">
        <!-- Service Card 1 -->
      <?php
        require_once 'includes/connexion.php';
        $q = "SELECT h.*, i.cheminImg, c.libelle, ch.prix
              FROM hotels h
              INNER JOIN imagehotel i ON i.idHotel = h.idHotel
              INNER JOIN classes c ON c.idClasse = h.idClasse
              INNER JOIN chambres ch ON ch.idHotel = h.idHotel
              WHERE i.cheminImg LIKE '%apercu%'
              AND ch.libelle = 'Standard'
              ORDER BY RAND() LIMIT 0, 5";
        $res = mysqli_query($conn, $q);
        while($l = mysqli_fetch_assoc($res)){
          echo "<div class='item service-card'>
                  <div class='image'>
                    <img src='".$l['cheminImg']."' alt='' class='card-image'>
                  </div>
                  <div class='card-content'>
                    <h2 class='card-title'>".$l['nomHotel']."</h2>
                    <div class='rate flex'>
                      <p>".$l['libelle']."</p>
                    </div>
                    <div class='button flex'>
                      <a href='hotel.php?idHotel=".$l['idHotel']."' class='primary-btn'>RESERVER</a>
                      <h3>".$l['prix']." DHs<span> <br> par nuit </span> </h3>
                    </div>
                  </div>
                </div>";
        }
      ?>        

      </div>
    </div>
  </section>

  <section class="gallery">
    <div class="container top">
      <div class="heading">
        <h1>GALLERIE</h1><br>
        <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
      </div>
    </div>

    <div class="content mtop">
      <div class="owl-carousel owl-carousel1 owl-theme">
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-1.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-2.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-3.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-4.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-5.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-6.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-4.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-3.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-1.png" alt="">
          </div>
        </div>
        <div class="items">
          <div class="img">
            <img src="images/galerie/gallery-6.png" alt="">
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="services top">
    <div class="container">

      <div class="header-section">
        <div class="header-text">
            
        </div>
    </div>
     
        <div class="owl-carousel owl-theme">

        <?php
          $q = "SELECT a.*, i.cheminImgA
                FROM activites a
                INNER JOIN imageactivite i ON i.idActivite = a.idActivite
                WHERE i.cheminImgA LIKE '%apercu%'
                ORDER BY RAND() LIMIT 0, 5";

          $res = mysqli_query($conn, $q);
          while($l = mysqli_fetch_assoc($res)){
            echo "<div class='item service-card'>
                    <img src='".$l['cheminImgA']."' class='card-image'>
                    <div class='card-content'>
                      <div class='card-title'>".$l['nomActivite']."</div>
                      <p class='card-description'>".$l['descriptionA']."</p>
                      <a href='activite.php?idActivite=".$l['idActivite']."' class='read-more'>Voir plus <i class='fas fa-arrow-right'></i></a>
                    </div>
                  </div>";
          }
          
        ?>

  </div>
</div>
</section>

  <?php
    require_once 'includes/pied.php';
  ?>

  <script src="https://kit.fontawesome.com/032d11eac3.js" crossorigin="anonymous"></script>
  <script src="index.js"></script>

</body>

</html>