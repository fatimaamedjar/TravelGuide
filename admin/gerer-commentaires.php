<?php
session_start();
require_once '../includes/connexion.php';

// Vérifie si l'admin est connecté
if (!isset($_SESSION['adm_auth'])) {
    header('Location: connexion-compte.php');
    exit();
}

// Supprimer un commentaire
if (isset($_GET['supprimer'])) {
    $idComm = (int) $_GET['supprimer'];
    $stmt = $conn->prepare("DELETE FROM commentaires WHERE idComm = ?");
    $stmt->bind_param("i", $idComm);
    $stmt->execute();
}

// Répondre à un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repondre'])) {
    $idComm = (int) $_POST['idComm'];
    $reponse = htmlspecialchars($_POST['reponse']);
    $idAdmin = $_SESSION['admin_id'];

    $stmt = $conn->prepare("INSERT INTO responseadm (reponse, dateRep, idComm, idAdmin) VALUES (?, NOW(), ?, ?)");
    $stmt->bind_param("sii", $reponse, $idComm, $idAdmin);
    $stmt->execute();
}

// Récupérer les commentaires avec info client et réponse
$sql = "SELECT c.idComm, c.commentaire, c.datecomm, cl.nomC, cl.prenomC, cl.emailC, cl.photoC,
               COALESCE(h.nomHotel, '') AS nomHotel,
               COALESCE(a.nomActivite, '') AS nomActivite,
               ra.reponse, ra.daterep
        FROM commentaires c
        JOIN clients cl ON c.idClient = cl.idClient
        LEFT JOIN hotels h ON c.idHotel = h.idHotel
        LEFT JOIN activites a ON c.idActivite = a.idActivite
        LEFT JOIN responseadm ra ON ra.idComm = c.idComm
        ORDER BY c.datecomm DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Commentaires - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .comment-box {
            background: #f1f4f9;
            padding: 16px;
            margin-bottom: 20px;
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: relative;
        }

        .comment-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            margin-right: 14px;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .comment-content {
            flex-grow: 1;
        }

        .comment-header {
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .comment-date {
            position: absolute;
            top: 12px;
            right: 16px;
            font-size: 12px;
            color: #666;
        }

        .comment-text {
            margin-top: 4px;
            font-size: 14px;
            color: #222;
        }

        .admin-reply {
            margin-top: 10px;
            padding: 10px 12px;
            background: #e0f5e9;
            border-left: 4px solid #28a745;
            border-radius: 8px;
            font-size: 14px;
            color: #1c452b;
        }

        textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-top: 10px;
            resize: vertical;
            font-size: 14px;
        }

        button {
            padding: 8px 12px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 6px;
            border: none;
            background-color: #007bff;
            color: white;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #dc3545;
            margin-left: 10px;
        }

        .delete-btn:hover {
            background-color: #a71d2a;
        }
        .rtr-btn{
        text-decoration: none;
        color: white;
        background-color: #7fc142;
        font-weight: bold;
        font-family: 'Arial';
        padding: 15px 20px;
        position: absolute;
        top: 20px;
        left: 20px;
        border-radius: 10px;
        }
        
        .rtr-btn i{
        margin-right: 10px;
        }

    </style>
</head>
<body>

<a class="rtr-btn" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Retour à l'acceuil</a>

<h1>Gestion des Commentaires</h1>

<?php while ($row = $result->fetch_assoc()): ?>
    <div class="comment-box">
        <div class="comment-avatar">
            <?= $row['photoC'] ? '<img src="../' . htmlspecialchars($row['photoC']) . '" style="width:100%; height:100%; border-radius:50%;">' : '😊' ?>
        </div>
        <div class="comment-content">
            <div class="comment-header">
                <?= htmlspecialchars($row['prenomC'] . ' ' . strtoupper($row['nomC'])) ?>
            </div>
            <div class="comment-text">
                <?= nl2br(htmlspecialchars($row['commentaire'])) ?>
                <br>
                <?php if ($row['nomHotel']): ?>
                    <small>Hôtel : <?= htmlspecialchars($row['nomHotel']) ?></small>
                <?php endif; ?>
                <?php if ($row['nomActivite']): ?>
                    <small> | Activité : <?= htmlspecialchars($row['nomActivite']) ?></small>
                <?php endif; ?>
            </div>

            <?php if ($row['reponse']): ?>
                <div class="admin-reply">
                    <strong>Réponse de l'admin :</strong><br>
                    <?= nl2br(htmlspecialchars($row['reponse'])) ?><br>
                    <small>Répondu le : <?= htmlspecialchars($row['daterep']) ?></small>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="idComm" value="<?= (int)$row['idComm'] ?>">
                    <textarea name="reponse" placeholder="Écrire une réponse..."></textarea>
                    <br>
                    <button type="submit" name="repondre">Répondre</button>
                    <a href="?supprimer=<?= (int)$row['idComm'] ?>" onclick="return confirm('Supprimer ce commentaire ?')">
                        <button type="button" class="delete-btn">Supprimer</button>
                    </a>
                </form>
            <?php endif; ?>
        </div>
        <div class="comment-date"><?= htmlspecialchars($row['datecomm']) ?></div>
    </div>
<?php endwhile; ?>

</body>
</html>
