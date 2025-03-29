<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "projet-web";
$dbname = "projet-web";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email= htmlspecialchars($_POST['email']);

    if (!empty($nom) && !empty($prenom && !empty($email))) {
        if (!empty($_POST['id'])) {
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE pilotes SET nom=?, prenom=?, email=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $email, $_POST['id']]);
        } else {
            // Ajout
            $stmt = $pdo->prepare("INSERT INTO pilotes (nom, prenom, email) VALUES (?, ?)");
            $stmt->execute([$nom, $prenom, $email]);
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM pilotes WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Récupération
$pilotes = $pdo->query("SELECT * FROM pilotes")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des Pilotes</title>
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
            <a href="stage.html">Gestion des offres de stage</a> |
            <strong>Gestion des pilotes |</strong>
            <a href="etudiant.php">Gestion des étudiants</a> |
            <a href="">Gestion des candidatures</a>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher un pilote</h2>
                <input type="text" placeholder="Email, nom ou Prénom" id="search" onkeyup="searchPilote()" required>
                
                <h2>Ajouter/Modifier un pilote</h2>
                <form method="POST" id="piloteForm">
                    <input type="hidden" id="editId" name="id">
                    
                    <label for="nom">Nom
                        <input type="text" name="nom" id="nom" required>
                    </label>
                    
                    <label for="prenom">Prénom
                        <input type="text" name="prenom" id="prenom" required>
                    </label>
                    <label for="email">Email
                        <input type="text" name="email" id="email" required>
                    </label>
                    
                    <button type="submit" name="ajouter">Enregistrer</button>
                </form>
                
                <h2>Liste des pilotes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="piloteTable">
                        <?php foreach ($pilotes as $pilote): ?>
                        <tr>
                            <td><?= htmlspecialchars($pilote['nom']) ?></td>
                            <td><?= htmlspecialchars($pilote['prenom']) ?></td>
                            <td><?= htmlspecialchars($pilote['email']) ?></td>
                            <td>
                                <button class="edit-btn" onclick="editPilote(
                                    '<?= $pilote['id'] ?>',
                                    '<?= addslashes($pilote['nom']) ?>',
                                    '<?= addslashes($pilote['prenom']) ?>'
                                    '<?= addslashes($pilote['email']) ?>',
                                )">Modifier</button>
                                <a href="?delete=<?= $pilote['id'] ?>" onclick="return confirm('Supprimer ce pilote?')" class="delete-btn">Supprimer</a>
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

        function editPilote(id, email, nom, prenom) {
            document.getElementById('editId').value = id;
            document.getElementById('nom').value = nom;
            document.getElementById('prenom').value = prenom;
            document.getElementById('email').value = email;
            window.scrollTo(0, 0);
        }

        function searchPilote() {
            const filter = document.getElementById('search').value.toLowerCase();
            document.querySelectorAll('#piloteTable tr').forEach(row => {
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