<?php
require_once('../fpdf186/fpdf.php');

// Connexion à la base de données (à adapter selon votre configuration)
$conn = mysqli_connect('localhost', 'root', '', 'travelguide_t');
if (!$conn) {
    die("Connexion échouée : " . mysqli_connect_error());
}

// Requête pour obtenir les données des hôtels
$query = "SELECT idHotel, nomHotel, villeH 
          FROM hotels";
$result = mysqli_query($conn, $query);

// Création du PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// En-tête
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(21, 40, 66); // Bleu foncé
$pdf->SetFillColor(250, 250, 252); // Couleur de fond claire
$pdf->Cell(0, 12, 'Rapport des Hôtels', 0, 1, 'C');
$pdf->Ln(5);

// Informations sur le rapport
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, 'Rapport généré le: ' . date('d/m/Y'), 0, 1, 'R');
$pdf->SetTextColor(21, 40, 66);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 7, "Ce rapport présente les informations clés des hôtels dans notre base de données. Les hôtels sont classés par note moyenne décroissante pour vous aider à identifier les établissements les mieux notés.", 0, 'L');
$pdf->Ln(10);

// Tableau des hôtels
$header = ['ID', 'Nom de l\'Hôtel', 'Ville', 'Catégorie', 'Note', 'Prix Moyen'];
$w = [12, 60, 35, 30, 20, 33];

// En-têtes du tableau
$pdf->SetFillColor(64, 101, 130); // Bleu foncé
$pdf->SetTextColor(255);
$pdf->SetDrawColor(160, 174, 192);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('Arial', 'B', 10);

for($i = 0; $i < count($header); $i++) {
    $pdf->Cell($w[$i], 10, $header[$i], 1, 0, 'C', true);
}
$pdf->Ln();

// Couleur et police pour les données
$pdf->SetFillColor(232, 237, 244);
$pdf->SetTextColor(68, 68, 68);
$pdf->SetFont('Arial', '', 9);

// Variables pour alterner la couleur d'arrière-plan
$fill = false;
$color_index = 0;
$colors = [
    [250, 250, 252], // Blanc cassé
    [240, 245, 251] // Bleu très clair
];

// Construction des lignes avec données
$totalHotels = 0;
$totalNotes = 0;
$totalPrix = 0;

while($row = mysqli_fetch_assoc($result)) {
    $totalHotels++;
    $totalNotes += 14;
    $totalPrix += 1650;
    
    // Alternance des couleurs
    $pdf->SetFillColor($colors[$color_index][0], $colors[$color_index][1], $colors[$color_index][2]);
    $fill = true;
    $color_index = ($color_index + 1) % 2;
    
    // ID
    $pdf->Cell($w[0], 8, $row['idHotel'], 'LR', 0, 'C', $fill);
    
    // Nom de l'hôtel
    $pdf->Cell($w[1], 8, substr($row['nomHotel'], 0, 40), 'LR', 0, 'L', $fill);
    
    // Ville
    $pdf->Cell($w[2], 8, $row['villeH'], 'LR', 0, 'C', $fill);
    
    // Catégorie (étoiles)
    $pdf->Cell($w[3], 8, str_repeat('★', 5), 'LR', 0, 'C', $fill);
    
    // Note moyenne (avec indicateur graphique)
    $rating = number_format( 14, 1);
    $ratingVisual = str_repeat('★', floor($rating)) . str_repeat('☆', 20 - ceil($rating));
    $pdf->Cell($w[4], 8, $ratingVisual . ' ' . $rating, 'LR', 0, 'C', $fill);
    
    // Prix moyen
    $pdf->Cell($w[5], 8, number_format(1650, 2) . ' €', 'LR', 0, 'R', $fill);
    
    $pdf->Ln();
}

// Fermeture du tableau
$pdf->SetFillColor(64, 101, 130);
$pdf->SetTextColor(255);
$pdf->Cell(array_sum($w), 0, '', 'T');
$pdf->Ln(12);

// Statistiques
$pdf->SetTextColor(21, 40, 66);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'Statistiques des Hôtels', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 7, 'Nombre total d\'hôtels', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(40, 7, $totalHotels, 0, 1);

if($totalHotels > 0) {
    $averageNote = $totalNotes / $totalHotels;
    $averagePrice = $totalPrix / $totalHotels;
    
    $pdf->Cell(70, 7, 'Note moyenne des hôtels', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(40, 7, number_format($averageNote, 1) . '/5.0', 0, 1);
    
    $pdf->Cell(70, 7, 'Prix moyen par nuit', 0, 0);
    $pdf->Cell(5, 7, ':', 0, 0);
    $pdf->Cell(40, 7, number_format($averagePrice, 2) . ' €', 0, 1);
}

// Légende
$pdf->Ln(8);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$legend = "Catégorie: le nombre d'étoiles de l'hôtel | Note: la note moyenne sur 5 basée sur les avis clients";
$pdf->MultiCell(0, 5, $legend, 0, 'L');

// Pied de page
$pdf->SetY(0 - 30);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . '/{nb}', 0, 0, 'C');

// Génération du PDF
$filename = 'rapport_hotels_' . date('Ymd') . '.pdf';
$pdf->Output('I', $filename);

// Fermeture de la connexion
mysqli_close($conn);
?>
