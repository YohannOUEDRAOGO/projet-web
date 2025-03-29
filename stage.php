<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "projet-web";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $titre= htmlspecialchars($_POST['titre']);
    $descriptions= htmlspecialchars($_POST['descriptions']);
    $competences = htmlspecialchars($_POST['competences']);
    $entreprise = htmlspecialchars($_POST['entreprise']);
    $baserenumeration= htmlspecialchars($_POST['baserenumeration']);
    $nbetudiants= htmlspecialchars($_POST['nbetudiants']);
    $dateoffre= htmlspecialchars($_POST['dateoffre']);

    if (!empty($titre) && !empty($descriptions) && !empty($competences) && !empty($entreprise) && !empty($baserenumerationl) && !empty($nbetudiants) && !empty($dateoffre)) {
        if (!empty($_POST['id'])) {
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE offresstages SET titre=?, descriptions=?, competences=?, entreprise=?, baserenumeration=?, nbetudiants=?, dateoffre=? WHERE id=?");
            $stmt->execute([$titre, $descriptions, $competences, $entreprise, $baserenumeration, $nbetudiants, $dateoffre, $_POST['id']]);
        } else {
            // Ajout
            $stmt = $pdo->prepare("INSERT INTO offresstages (titre, descriptions, competences, entreprise, baserenumeration, nbetudiants, dateoffre) VALUES (?, ?)");
            $stmt->execute([$titre, $descriptions, $competences, $entreprise, $baserenumeration, $nbetudiants, $dateoffre]);
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM offresstages WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Récupération
$offres = $pdo->query("SELECT * FROM offresstages")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des offres de stage</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <center>
                <img src="image/logo-lbp-header.png" alt="Trouve ton stage en un click avec Lebonplan">
            </center>
            <div class="user-menu" id="userMenu">
                <div class="user-info" onclick="toggleMenu()">
                    <div class="user-avatar">YR</div>
                    <span class="user-name">Yohann Romarick</span>
                    <span class="dropdown-icon">▼</span>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="#" class="dropdown-item">Mon profil</a>
                    <a href="#" class="dropdown-item">Wish-list</a>
                    <div class="divider"></div>
                    <a href="#" class="dropdown-item" id="logoutBtn">Déconnexion</a>
                </div>
            </div>
        </nav>
        <nav>
            <a href="">Accueil</a> |
            <a href="entreprise.php">Gestion des entreprises</a> |
            <a href="stage.php">Gestion des offres de stage</a> |
            <a href="pilote.php">Gestion des pilotes</a> |
            <a href="etudiant.php">Gestion des étudiants</a> |
            <a href="">Gestion des candidatures</a>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher une offre de stage</h2>
                <input type="text" placeholder="Titre, compétences, descriptions, base de rénumération, nombre d'étudiants ayant postulé, date de l'offre" id="search" onkeyup="searchOffre()" required>
                
                <h2>Ajouter/Modifier une offre de stage</h2>
                <form method="POST" id="offreForm">
                    <input type="hidden" id="editId" name="id">
                    
                    <label for="titre">Titre
                        <input type="text" name="titre" id="titre" required>
                    </label>
                    <label for="descriptions">Description
                        <input type="text" name="descriptions" id="descriptions" required>
                    </label>
                    <label for="competences">Compétences
                        <input type="text" name="competences" id="competences" required>
                    </label>
                    <label for="entreprise">Entreprise
                        <input type="text" name="entreprise" id="entreprise" required>
                    </label>
                    <label for="baserenumeration">Base de rénumération
                        <input type="text" name="baserenumeration" id="baserenumeration" required>
                    </label>
                    <label for="nbetudiants">Nombre d'étudiants ayant postulé
                        <input type="text" name="nbetudiants" id="nbetudiants" required>
                    </label>
                    <label for="dateoffre">Date de l'offre
                        <input type="date" name="dateoffre" id="dateoffre" required>
                    </label>
                    
                    <button type="submit" name="ajouter">Enregistrer</button>
                </form>
                
                <h2>Liste des offres de stage</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Compétences</th>
                            <th>Entreprise</th>
                            <th>Base de rémunération</th>
                            <th>Nombre d'étudiants ayant déjà postulé</th>
                            <th>Dates de l'offre</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="offreTable">
                        <?php foreach ($offres as $offre): ?>
                        <tr>
                            <td><?= htmlspecialchars($offre['titre']) ?></td>
                            <td><?= htmlspecialchars($offre['descriptions']) ?></td>
                            <td><?= htmlspecialchars($offre['competences']) ?></td>
                            <td><?= htmlspecialchars($offre['entreprise']) ?></td>
                            <td><?= htmlspecialchars($offre['baserenumeration']) ?></td>
                            <td><?= htmlspecialchars($offre['nbetudiants']) ?></td>
                            <td><?= htmlspecialchars($offre['dateoffre']) ?></td>
                            <td>
                                <button class="edit-btn" onclick="editOffre(
                                    '<?= $offre['id'] ?>',
                                    '<?= addslashes($offre['titre']) ?>',
                                    '<?= addslashes($offre['descriptions']) ?>',
                                    '<?= addslashes($offre['competences']) ?>',
                                    '<?= addslashes($offre['entreprise']) ?>'
                                    '<?= addslashes($offre['baserenumeration']) ?>',
                                    '<?= addslashes($offre['nbetudiants']) ?>',
                                    '<?= addslashes($offre['dateoffre']) ?>',
                                )">Modifier</button>
                                <a href="?delete=<?= $offre['id'] ?>" onclick="return confirm('Supprimer cette offre de stage?')" class="delete-btn">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </section>
    </main>

    <footer class="navbar footer">
        <hr>
        <em>2024 - Tous droits réservés - Web4All</em>
    </footer>

    <script>
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }

        window.onclick = function(e) {
            if (!e.target.matches('.user-info *')) {
                document.getElementById('dropdownMenu').classList.remove('show');
            }
        }

        function editOffre(id, titre, descriptions, competences, entreprise, baserenumeration, nbetudiants, dateoffre) {
            document.getElementById('editId').value = id;
            document.getElementById('titre').value = titre;
            document.getElementById('descriptions').value = descriptions;
            document.getElementById('competences').value = competences;
            document.getElementById('entreprise').value = entreprise;
            document.getElementById('baserenumeration').value = baserenumeration;
            document.getElementById('nbetudiants').value = nbetudiants;
            document.getElementById('dateoffre').value = dateoffre;
            window.scrollTo(0, 0);
        }

        function searchOffre() {
            const filter = document.getElementById('search').value.toLowerCase();
            document.querySelectorAll('#offreTable tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Déconnexion ?')) {
                window.location.href = 'logout.php';
            }
        });
    </script>
</body>
</html>
