<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Candidatures</title>
    <link rel="stylesheet" href="assets/style_base.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        main {
            margin-left: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            max-width: 600px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        input[type="submit"] {
            width: auto;
            background-color: #4CAF50;
            color: white;
            border: none;
            margin-top: 15px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        @media (max-width: 600px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            th, td {
                padding: 10px;
                text-align: right;
            }
        }
    </style>
</head>
<body>
    <div>
        <h1 class="center">Gestion des Candidatures</h1>
    </div>

    <!-- Liste des candidatures -->
    <h3>Liste des Candidatures</h3>
    <table>
        <thead>
            <tr>
                <th>Entreprise</th>
                <th>Offre</th>
                <th>Date de candidature</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $file = 'candidatures.json';
                if (file_exists($file)) {
                    $jsonData = file_get_contents($file);
                    $candidatures = json_decode($jsonData, true);

                    foreach ($candidatures as $candidature) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($candidature['entreprise']) . "</td>";
                        echo "<td>" . htmlspecialchars($candidature['offre']) . "</td>";
                        echo "<td>" . htmlspecialchars($candidature['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($candidature['statut']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Aucune candidature pour le moment.</td></tr>";
                }
            ?>
        </tbody>
    </table>

    <!-- Formulaire de candidature -->

    <main>
        <div>
            <h1 class="center">Postuler à nos offres disponibles</h1>
            <h2>Voici vos offres !</h2>
        </div>
        <div class="offers">
            <div class="offer clickable">
                <h1 class="center">Stage - Administrateur Système et Réseau H/F</h1>
                <h2 class="small center">IBM | Pornichet - 44 | Publiée le 13/01/2025</h2>
                <h1 class="center">
                    <a class="postuler" href="offres-stage-postuler.php?title=Stage%20-%20Administrateur%20Système%20et%20Réseau%20H/F&company=IBM&location=Pornichet%20-%2044&date=13/01/2025">POSTULER</a>
                </h1>
            </div>
            <div class="offer clickable">
                <h1 class="center">Stage - Ingénieur Qualité H/F</h1>
                <h2 class="small center">ABC | Pornichet - 44 | Publiée le 20/01/2025</h2>
                <h1 class="center">
                    <a class="postuler" href="offres-stage-postuler.php?title=Stage%20-%20Ingénieur%20Qualité%20H/F&company=ABC&location=Pornichet%20-%2044&date=20/01/2025">POSTULER</a>
                </h1>
            </div>
            <div class="offer clickable">
                <h1 class="center">Stage - Développement WEB H/F</h1>
                <h2 class="small center">IBM | Nantes - 44 | Publiée le 23/02/2025</h2>
                <h1 class="center">
                    <a class="postuler" href="offres-stage-postuler.php?title=Stage%20-%20Développement%20WEB%20H/F&company=IBM&location=Nantes%20-%2044&date=23/02/2025">POSTULER</a>
                </h1>
            </div>
            <div class="offer clickable">
                <h1 class="center">Stage - Systèmes Embarqués H/F</h1>
                <h2 class="small center">IBM | Pornichet - 44 | Publiée le 10/02/2025</h2>
                <h1 class="center">
                    <a class="postuler" href="offres-stage-postuler.php?title=Stage%20-%20Systèmes%20Embarqués%20H/F&company=IBM&location=Pornichet%20-%2044&date=10/02/2025">POSTULER</a>
                </h1>
            </div>
        </div>
    </main>
<!--     <form id="candidatureForm" enctype="multipart/form-data">
        <label for="entreprise">Entreprise :</label>
        <input type="text" id="entreprise" name="entreprise" required>

        <label for="offre">Offre :</label>
        <input type="text" id="offre" name="offre" required>

        <label for="date">Date :</label>
        <input type="date" id="date" name="date" required>

        <label for="cv">CV :</label>
        <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx" required>

        <label for="motivation">Lettre de motivation :</label>
        <textarea id="motivation" name="motivation" rows="5" required></textarea>

        <input type="submit" value="Postuler">
    </form> -->

    <script>
        const form = document.getElementById('candidatureForm');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêche le rechargement

            // Vérification des champs
            const entreprise = document.getElementById('entreprise').value.trim();
            const offre = document.getElementById('offre').value.trim();
            const date = document.getElementById('date').value;
            const cv = document.getElementById('cv').files[0];
            const motivation = document.getElementById('motivation').value.trim();

            if (!entreprise || !offre || !date || !cv || !motivation) {
                alert("Veuillez remplir tous les champs !");
                return;
            }

            alert("Votre candidature a bien été envoyée !");
            
            // Optionnel : Envoyer les données au serveur via fetch() ou AJAX
            // Exemple d'affichage en console
            console.log({
                entreprise,
                offre,
                date,
                cvName: cv.name,
                motivation
            });

            form.reset(); // Réinitialisation du formulaire
        });
    </script>

</body>
</html>
