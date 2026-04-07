<?php
require_once '../includes/fpdf186/fpdf.php';

class PDF extends FPDF {

    function Header() {
        $this->Image('../images/logo/TG_logo_vert.png', 80, 10, 40);
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(127, 193, 66);
        $this->Line(10, 35, 200, 35);
    }

    function Corps() {
        
        require_once '../includes/connexion.php';

        $this->SetXY(10, 45);
        $this->SetFont('Arial','B',25);
        $this->SetTextColor(40, 40, 52);
        
        $this->Cell(0,10, mb_convert_encoding('BON DE RÉSERVATION', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

        
        $paypal_order_id = mysqli_real_escape_string($conn, $_GET['id_order']);

        $q = "SELECT r.*, p.* FROM reservations r
              INNER JOIN paiements p ON r.idReservation = p.idReservation
              WHERE p.paypal_order_id = '" . $paypal_order_id . "'";
        $res = mysqli_query($conn, $q);

        if (!$res) {
            
            error_log("Database query failed: " . mysqli_error($conn));
            $this->SetFont('Arial','B',12);
            $this->SetTextColor(255, 0, 0);
            $this->Cell(0,10, mb_convert_encoding('Erreur lors de la récupération des données.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            return; 
        }

        $l = mysqli_fetch_assoc($res);

        if (!$l) {
            
            $this->SetFont('Arial','B',12);
            $this->SetTextColor(255, 0, 0);
            $this->Cell(0,10, mb_convert_encoding('Aucune réservation trouvée pour cet ID de commande.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            return; 
        }

        $this->Image('../images/logo/TG_logo_icon.png', 63, 60, 7);
        $this->SetXY(70, 59);
        $this->SetFont('Arial','B',15);
        $this->SetTextColor(127, 193, 66);
        $this->Cell(60,10, mb_convert_encoding('Numéro de réservation :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $this->SetTextColor(40, 40, 52);
        $this->Cell(0,10,'   '.$l['idReservation'],0,1,'L');


        if ($l['typeres'] == 'Activite') {
            
            $idActivite = mysqli_real_escape_string($conn, $l['idActivite']);
            $qu = "SELECT a.*, c.*, r.* FROM reservations r
                   INNER JOIN activites a ON r.idActivite = a.idActivite
                   INNER JOIN clients c ON c.idClient = r.idClient
                   WHERE r.idReservation = " . $l['idReservation']; 
            $res = mysqli_query($conn, $qu);

            if (!$res) {
                error_log("Database query failed (Activite): " . mysqli_error($conn));
                $this->SetFont('Arial','B',12);
                $this->SetTextColor(255, 0, 0);
                $this->Cell(0,10, mb_convert_encoding('Erreur lors de la récupération des détails de l\'activité.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                return;
            }

            $activite = mysqli_fetch_assoc($res);

            if (!$activite) {
                $this->SetFont('Arial','B',12);
                $this->SetTextColor(255, 0, 0);
                $this->Cell(0,10, mb_convert_encoding('Détails d\'activité non trouvés.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                return;
            }

            $this->SetXY(30, 90);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Nom Complet :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '. mb_convert_encoding($activite['nomC']." ".$activite['prenomC'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

            $this->SetXY(30, 105);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Activité :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '. mb_convert_encoding($activite['nomActivite'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

            $this->SetXY(30, 120);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Durée (Minutes) :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$activite['dureeA'],0,1,'L');

            $this->SetXY(30, 135);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding("Date : ", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$activite['dateArrive'],0,1,'L');

            $this->SetXY(30, 150);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Nombre de personne(s) :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$activite['nbPersonnes'],0,1,'L');

            $this->SetXY(30, 165);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Prix :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$activite['prix'],0,1,'L');

        } else { 
            
            $idChambre = mysqli_real_escape_string($conn, $l['idChambre']);
            $qu = "SELECT ch.libelle, c.nomC, c.prenomC , r.*, h.nomHotel FROM reservations r
                   INNER JOIN chambres ch ON r.idChambre = ch.idChambre
                   INNER JOIN hotels h ON h.idHotel = ch.idHotel
                   INNER JOIN clients c ON c.idClient = r.idClient
                   WHERE r.idReservation = " . $l['idReservation']; 
            $res = mysqli_query($conn, $qu);

            if (!$res) {
                error_log("Database query failed (Hotel): " . mysqli_error($conn));
                $this->SetFont('Arial','B',12);
                $this->SetTextColor(255, 0, 0);
                $this->Cell(0,10, mb_convert_encoding('Erreur lors de la récupération des détails de l\'hôtel.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                return;
            }

            $hotel = mysqli_fetch_assoc($res);

            if (!$hotel) {
                $this->SetFont('Arial','B',12);
                $this->SetTextColor(255, 0, 0);
                $this->Cell(0,10, mb_convert_encoding('Détails de l\'hôtel non trouvés.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                return;
            }

            $this->SetXY(30, 90);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Nom Complet :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '. mb_convert_encoding($hotel['nomC']." ".$hotel['prenomC'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

            $this->SetXY(30, 105);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Hôtel :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '. mb_convert_encoding($hotel['nomHotel'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

            $this->SetXY(30, 120);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Chambre :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '. mb_convert_encoding($hotel['libelle'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

            $this->SetXY(30, 135);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding("Date d'arrivée : ", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$hotel['dateArrive'],0,1,'L');

            $this->SetXY(30, 150);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Date de départ : ', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$hotel['dateDepart'],0,1,'L');

            $this->SetXY(30, 165);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Nombre de personne(s) :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$hotel['nbPersonnes'],0,1,'L');

            $this->SetXY(30, 180);
            $this->SetFont('Arial','B',15);
            $this->SetTextColor(127, 193, 66);
            $this->Cell(60,10, mb_convert_encoding('Prix :', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
            $this->SetTextColor(40, 40, 52);
            $this->Cell(0,10,'   '.$hotel['prix'],0,1,'L');
        }

        $this->SetXY(10, 235);
        $this->SetFont('Arial','I',10);

        $texte_original = "Les réservations ne peuvent être ni échangées, ni remboursées.\n";
        $texte_original .= "\nCe bon de réservation est valide uniquement pour la durée de la réservation.\n Après cette période, le bon de réservation ne sera plus valable.\n";
        $texte_original .= "\nPour plus d'informations, veuillez nous contacter à l'adresse suivante : TravelGuide2MCW@protonmail.com.";

        $texte_pour_pdf = mb_convert_encoding($texte_original, 'ISO-8859-1', 'UTF-8');

        
        $this->MultiCell(190, 5, $texte_pour_pdf, 0, 'C');
    }


    function Footer() {
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(127, 193, 66);
        $this->Line(10, 280, 200, 280);
    }
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdf = new PDF();
$pdf->AddPage();
$pdf->Corps(); 
$fchemin = "../reservations/";
if(!file_exists($fchemin)){
    mkdir($fchemin);
}
$fnom = $fchemin.$_GET['id_order'].".pdf";
$pdf->Output('F', $fnom);
header("Location: ".$fnom);
exit();

?>