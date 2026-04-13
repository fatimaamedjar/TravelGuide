<?php
    require_once 'includes/connexion.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelGuide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>


        body {
            background: linear-gradient(135deg, #fafafa, #eaeaea);
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 40px 0;
        }

        .form-button {
            background: #7fc142;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .form-button:hover {
            background:rgb(113, 172, 59);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            opacity: 0;
            height: 0;
            overflow: hidden;
            transition: all 0.5s ease;
        }

        form.active {
            opacity: 1;
            height: auto;
            overflow: visible;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        button[type="submit"] {
            background: #282834;
            color: white;
            border: none;
            padding: 15px 30px;
            width: 100%;
            font-size: 1.1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[type="submit"]:hover {
            background: #282834;
        }


        @media (max-width: 768px) {
            .button-container {
                flex-direction: column;
                align-items: center;
            }
            
            .form-button {
                width: 100%;
                max-width: 300px;
            }

        }
    </style>
</head>
<body>
    <!-- Section 1: Carrousel de paysages et boutons -->
    <section class="section">

        
        <div class="button-container">
            <button class="form-button" id="hotelsBtn">Trouver un hôtel</button>
            <!--<button class="form-button" id="activitiesBtn">Découvrir des activités</button>-->
        </div>
        
        <div class="form-container">

            <form id="hotelsForm" action="hotels.php" method="post">
                <div class="form-group">
                    <label for="ville">Ville:</label>
                    <select name="ville" required>
                        <?php
                            $res = mysqli_query($conn, "SELECT DISTINCT villeH, COUNT(*) AS nbHot FROM hotels GROUP BY villeH ORDER BY nbHot DESC");
                            while($l = mysqli_fetch_assoc($res)){
                                echo "<option value='".$l['villeH']."'>".$l['villeH']."</option>";
                            }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="depart">Date de départ:</label>
                    <input type="date" id="depart" name="depart" required>
                </div>
                
                <div class="form-group">
                    <label for="arrivee">Date d'arrivée:</label>
                    <input type="date" id="arrivee" name="arrivee" required>
                </div>
                
                <div class="form-group">
                    <label for="personnes">Nombre de personnes:</label>
                    <select id="personnes" name="personnes" required>
                        <option value="1">1 personne</option>
                        <option value="2">2 personnes</option>
                        <option value="3">3 personnes</option>
                        <option value="4">4 personnes</option>
                        <option value="5">5 personnes</option>
                        <option value="6">6+ personnes</option>
                    </select>
                </div>
                
                <button type="submit">Rechercher</button>
            </form>
            
            <form id="activitiesForm" action="activites.php" method="post">
                <div class="form-group">
                    <label for="ville-act">Ville:</label>
                    <select name="ville" required>
                        <?php
                            $res = mysqli_query($conn, "SELECT DISTINCT villeA, COUNT(*) AS nbAct FROM activites GROUP BY villeA ORDER BY nbAct DESC");
                            while($l = mysqli_fetch_assoc($res)){
                                echo "<option value='".$l['villeA']."'>".$l['villeA']."</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="depart">Date:</label>
                    <input type="date" id="depart-act" name="depart" required>
                </div>
                
                <div class="form-group">
                    <label for="personnes-act">Nombre de personnes:</label>
                    <select id="personnes-act" name="personnes" required>
                        <option value="1">1 personne</option>
                        <option value="2">2 personnes</option>
                        <option value="3">3 personnes</option>
                        <option value="4">4 personnes</option>
                        <option value="5">5+ personnes</option>
                    </select>
                </div>
                <button type="submit">Rechercher</button>
            </form>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hotelsBtn = document.getElementById('hotelsBtn');
            const activitiesBtn = document.getElementById('activitiesBtn');
            const hotelsForm = document.getElementById('hotelsForm');
            const activitiesForm = document.getElementById('activitiesForm');
            
            hotelsForm.classList.remove('active');
            activitiesForm.classList.remove('active');
            
            hotelsBtn.addEventListener('click', function() {
                hotelsForm.classList.toggle('active');
                if (hotelsForm.classList.contains('active')) {
                    activitiesForm.classList.remove('active');
                }
            });
            
            activitiesBtn.addEventListener('click', function() {
                activitiesForm.classList.toggle('active');
                if (activitiesForm.classList.contains('active')) {
                    hotelsForm.classList.remove('active');
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
