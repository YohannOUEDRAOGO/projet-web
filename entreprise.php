<?php
session_start(); // Ajoutez cette ligne au tout début

// Vérification de la connexion
if (!isset($_SESSION['user'])) {
    header('Location: authentification.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des informations de l'utilisateur connecté
$currentUser = $_SESSION['user'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($_POST['telephone']);

    if (!empty($nom) && !empty($description) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (!empty($_POST['id'])) {
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE entreprises SET nom=?, description=?, url=?, email=?, telephone=? WHERE id=?");
            $stmt->execute([$nom, $description, $url, $email, $telephone, $_POST['id']]);
        } else {
            // Ajout
            $stmt = $pdo->prepare("INSERT INTO entreprises (nom, description, url, email, telephone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $url, $email, $telephone]);
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM entreprises WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Récupération
$entreprises = $pdo->query("SELECT * FROM entreprises")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des Entreprises</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <style>
        .rating { unicode-bidi: bidi-override; direction: rtl; }
        .rating > input { display: none; }
        .rating > label { display: inline-block; cursor: pointer; }
        .rating > label:hover,
        .rating > label:hover ~ label,
        .rating > input:checked ~ label { color: gold; }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <center>
                <img src="image/logo-lbp-header.png" alt="Trouve ton stage en un click avec Lebonplan">
            </center>
            <div class="user-menu" id="userMenu">
    <div class="user-info" onclick="toggleMenu()">
        <div class="user-avatar">
            <?php 
                echo substr($currentUser['prenom'], 0, 1) . substr($currentUser['nom'], 0, 1); 
            ?>
        </div>
        <span class="user-name">
            <?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?>
        </span>
        <span class="dropdown-icon">▼</span>
    </div>
    <div class="dropdown-menu" id="dropdownMenu">
        <a href="profil.php" class="dropdown-item">Mon profil</a>
        <?php if ($currentUser['role'] === 'etudiant'): ?>
            <a href="wishlist.php" class="dropdown-item">Wish-list</a>
        <?php endif; ?>
        <div class="divider"></div>
        <a href="authentification.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
    </div>
</div>
        </nav>
        <nav>
            <a href="">Accueil</a> |
            <strong>Gestion des entreprises</strong>|
            <a href="stage.php">Gestion des offres de stage</a> |
            <a href="pilote.php">Gestion des pilotes</a> |
            <a href="etudiant.php">Gestion des étudiants</a> |
            <a href="">Gestion des candidatures</a>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher une entreprise</h2>
                <input type="text" placeholder="Nom, Description ou Email" id="search" onkeyup="searchCompany()" required>
                
                <h2>Ajouter/Modifier une entreprise</h2>
                <form method="POST" id="companyForm">
                    <input type="hidden" id="editId" name="id">
                    
                    <label for="nom">Nom
                        <input type="text" name="nom" id="nom" required>
                    </label>
                    
                    <label for="description">Description
                        <input type="text" name="description" id="description" required>
                    </label>
                    
                    <label for="url">URL
                        <input type="url" name="url" id="url">
                    </label>
                    
                    <label for="email">Email
                        <input type="email" name="email" id="email" required>
                    </label>
                    
                    <label for="telephone">Téléphone
                        <input type="text" name="telephone" id="telephone" required>
                    </label>
                    
                    <button type="submit" name="ajouter">Enregistrer</button>
                </form>
                
                <h2>Liste des entreprises</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                            <th>Évaluation</th>
                        </tr>
                    </thead>
                    <tbody id="companyTable">
                        <?php foreach ($entreprises as $entreprise): ?>
                        <tr>
                            <td><a href="<?= htmlspecialchars($entreprise['url']) ?>" target="_blank"><?= htmlspecialchars($entreprise['nom']) ?></a></td>
                            <td><?= htmlspecialchars($entreprise['description']) ?></td>
                            <td><?= htmlspecialchars($entreprise['email']) ?></td>
                            <td><?= htmlspecialchars($entreprise['telephone']) ?></td>
                            <td>
                                <button class="edit-btn" onclick="editCompany(
                                    '<?= $entreprise['id'] ?>',
                                    '<?= addslashes($entreprise['nom']) ?>',
                                    '<?= addslashes($entreprise['description']) ?>',
                                    '<?= addslashes($entreprise['url']) ?>',
                                    '<?= addslashes($entreprise['email']) ?>',
                                    '<?= addslashes($entreprise['telephone']) ?>'
                                )">Modifier</button>
                                <a href="?delete=<?= $entreprise['id'] ?>" onclick="return confirm('Supprimer cette entreprise?')" class="delete-btn">Supprimer</a>
                            </td>
                            <td>
                                <div class="rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?= $i ?>_<?= $entreprise['id'] ?>" name="rating_<?= $entreprise['id'] ?>" value="<?= $i ?>" <?= ($i == 3) ? 'checked' : '' ?>>
                                    <label for="star<?= $i ?>_<?= $entreprise['id'] ?>" title="<?= $i ?> étoiles">★</label>
                                    <?php endfor; ?>
                                </div>
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

        function editCompany(id, nom, description, url, email, telephone) {
            document.getElementById('editId').value = id;
            document.getElementById('nom').value = nom;
            document.getElementById('description').value = description;
            document.getElementById('url').value = url;
            document.getElementById('email').value = email;
            document.getElementById('telephone').value = telephone;
            window.scrollTo(0, 0);
        }

        function searchCompany() {
            const filter = document.getElementById('search').value.toLowerCase();
            document.querySelectorAll('#companyTable tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Déconnexion ?')) {
                window.location.href = 'authentification.php';
            }
        });
    </script>
</body>
</html>