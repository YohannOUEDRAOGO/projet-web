<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "gestion_entreprises"; 

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!empty($nom) && !empty($prenom) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (!empty($_POST['id'])) {
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE etudiants SET nom=?, prenom=?, email=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $email, $_POST['id']]);
        } else {
            // Ajout
            $stmt = $pdo->prepare("INSERT INTO etudiants (nom, prenom, email) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email]);
            
            // Envoi d'email (à adapter)
            mail($email, "Inscription", "Votre compte a été créé", "From: no-reply@lebonplan.com");
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Récupération
$etudiants = $pdo->query("SELECT * FROM etudiants")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des Étudiants</title>
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
            <a href="">Gestion des pilotes</a> |
            Gestion des étudiants|
            <a href="">Gestion des candidatures</a>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher un étudiant</h2>
                <input type="text" placeholder="Nom, Prénom ou Email" id="search" onkeyup="searchStudent()" required>
                
                <h2>Ajouter/Modifier un étudiant</h2>
                <form method="POST" id="studentForm">
                    <input type="hidden" id="editId" name="id">
                    
                    <label for="nom">Nom
                        <input type="text" name="nom" id="nom" required>
                    </label>
                    
                    <label for="prenom">Prénom
                        <input type="text" name="prenom" id="prenom" required>
                    </label>
                    
                    <label for="email">Email
                        <input type="email" name="email" id="email" required>
                    </label>
                    
                    <button type="submit" name="ajouter">Enregistrer</button>
                </form>
                
                <h2>Liste des étudiants</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTable">
                        <?php foreach ($etudiants as $etudiant): ?>
                        <tr>
                            <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                            <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                            <td><?= htmlspecialchars($etudiant['email']) ?></td>
                            <td>
                                <button class="edit-btn" onclick="editStudent(
                                    '<?= $etudiant['id'] ?>',
                                    '<?= addslashes($etudiant['nom']) ?>',
                                    '<?= addslashes($etudiant['prenom']) ?>',
                                    '<?= addslashes($etudiant['email']) ?>'
                                )">Modifier</button>
                                <a href="?delete=<?= $etudiant['id'] ?>" onclick="return confirm('Supprimer cet étudiant?')" class="delete-btn">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </section>
    </main>

    <footer></footer>

    <script>
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }

        window.onclick = function(e) {
            if (!e.target.matches('.user-info *')) {
                document.getElementById('dropdownMenu').classList.remove('show');
            }
        }

        function editStudent(id, nom, prenom, email) {
            document.getElementById('editId').value = id;
            document.getElementById('nom').value = nom;
            document.getElementById('prenom').value = prenom;
            document.getElementById('email').value = email;
            window.scrollTo(0, 0);
        }

        function searchStudent() {
            const filter = document.getElementById('search').value.toLowerCase();
            document.querySelectorAll('#studentTable tr').forEach(row => {
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