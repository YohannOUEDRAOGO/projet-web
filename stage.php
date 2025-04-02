<?php
require_once 'check_session.php';
verifySession();
if (!isset($_SESSION['user'])) {
    header('Location: authentification.php');
    exit();
}

$currentUser = $_SESSION['user'];
$role = $currentUser['role'];

// Vérification des droits selon la matrice CDC V4
$canCreateOffer = in_array($role, ['admin', 'pilote']);
$canEditOffer = in_array($role, ['admin', 'pilote']);
$canDeleteOffer = in_array($role, ['admin', 'pilote']);
$canViewStats = true; // Tous peuvent voir les stats

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

// Récupérer la liste des entreprises pour le select
$entreprises = $pdo->query("SELECT id, nom FROM entreprises")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    // Vérification des droits avant traitement
    if ((!empty($_POST['id']) && !$canEditOffer) || (empty($_POST['id']) && !$canCreateOffer)) {
        die("Action non autorisée");
    }

    // Récupération et validation des données
    $titre = htmlspecialchars($_POST['titre'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');
    $competences = htmlspecialchars($_POST['competences'] ?? '');
    $nom_entreprise = htmlspecialchars($_POST['nom_entreprise'] ?? '');
    $lieu = htmlspecialchars($_POST['lieu'] ?? '');
    $base_remuneration = htmlspecialchars($_POST['base_remuneration'] ?? '');
    $date_publication = $_POST['date_publication'] ?? date('Y-m-d');
    $date_fin = $_POST['date_fin'] ?? date('Y-m-d', strtotime('+1 month'));

    // Validation des champs obligatoires
    $errors = [];
    if (empty($titre)) $errors[] = "Le titre est obligatoire";
    if (empty($description)) $errors[] = "La description est obligatoire";
    if (empty($nom_entreprise)) $errors[] = "Le nom de l'entreprise est obligatoire";
    if (empty($lieu)) $errors[] = "Le lieu est obligatoire";
    if (empty($date_publication)) $errors[] = "La date de publication est obligatoire";
    if (empty($date_fin)) $errors[] = "La date de fin est obligatoire";

    if (empty($errors)) {
        try {
            // Vérifier si l'entreprise existe déjà
            $stmt = $pdo->prepare("SELECT id FROM entreprises WHERE nom = ?");
            $stmt->execute([$nom_entreprise]);
            $entreprise = $stmt->fetch();

            if (!$entreprise) {
                // Créer la nouvelle entreprise si elle n'existe pas
                $stmt = $pdo->prepare("INSERT INTO entreprises (nom) VALUES (?)");
                $stmt->execute([$nom_entreprise]);
                $entreprise_id = $pdo->lastInsertId();
            } else {
                $entreprise_id = $entreprise['id'];
            }

            if (!empty($_POST['id'])) {
                $stmt = $pdo->prepare("UPDATE offres_stage SET titre=?, description=?, competences_requises=?, entreprise_id=?, lieu=?, base_remuneration=?, date_publication=?, date_fin=? WHERE id=?");
                $stmt->execute([$titre, $description, $competences, $entreprise_id, $lieu, $base_remuneration, $date_publication, $date_fin, $_POST['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO offres_stage (titre, description, competences_requises, entreprise_id, lieu, base_remuneration, date_publication, date_fin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titre, $description, $competences, $entreprise_id, $lieu, $base_remuneration, $date_publication, $date_fin]);
            }
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données : " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        echo '<div class="error-message">';
        echo '<p>Des erreurs ont été détectées :</p>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>'.htmlspecialchars($error).'</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}

if (isset($_GET['delete'])) {
    if (!$canDeleteOffer) {
        die("Action non autorisée");
    }
    $stmt = $pdo->prepare("DELETE FROM offres_stage WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$offres = $pdo->query("
    SELECT o.*, e.nom AS entreprise_nom 
    FROM offres_stage o 
    LEFT JOIN entreprises e ON o.entreprise_id = e.id
    ORDER BY o.date_publication DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des offres de stage</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <style>
        .disabled-form { opacity: 0.6; pointer-events: none; }
        .hidden { display: none; }
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
                        <?php echo substr($currentUser['prenom'], 0, 1) . substr($currentUser['nom'], 0, 1); ?>
                    </div>
                    <span class="user-name">
                        <?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?>
                        <small>(<?php echo htmlspecialchars($role); ?>)</small>
                    </span>
                    <span class="dropdown-icon">▼</span>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profil.php" class="dropdown-item">Mon profil</a>
                    <?php if ($role === 'etudiant'): ?>
                        <a href="wishlist.php" class="dropdown-item">Wish-list</a>
                    <?php endif; ?>
                    <div class="divider"></div>
                    <a href="authentification.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
                </div>
            </div>
        </nav>
        <nav>
            <a href="candidature.php">Accueil</a> |
            <a href="entreprise.php">Gestion des entreprises</a> |
            <strong>Gestion des offres de stage</strong> |
            <?php if ($role === 'admin'): ?>
                <a href="pilote.php">Gestion des pilotes</a> |
            <?php endif; ?>
            <?php if (in_array($role, ['admin', 'pilote'])): ?>
                <a href="etudiant.php">Gestion des étudiants</a> |
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher une offre de stage</h2>
                <input type="text" placeholder="Titre, compétences, lieu..." id="search" onkeyup="searchOffre()" required>
                
                <?php if ($canCreateOffer || $canEditOffer): ?>
                <h2>Ajouter/Modifier une offre de stage</h2>
                <form method="POST" id="offreForm" <?php echo (!$canCreateOffer && !$canEditOffer) ? 'class="disabled-form"' : ''; ?>>
                    <input type="hidden" id="editId" name="id" value="<?= $_POST['id'] ?? '' ?>">
                    
                    <label for="titre">Titre
                        <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
                    </label>
                    
                    <label for="description">Description
                        <textarea name="description" id="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </label>
                    
                    <label for="competences">Compétences requises
                        <textarea name="competences" id="competences"><?= htmlspecialchars($_POST['competences'] ?? '') ?></textarea>
                    </label>
                    
                    <label for="nom_entreprise">Nom de l'entreprise
                        <input type="text" name="nom_entreprise" id="nom_entreprise" value="<?= htmlspecialchars($_POST['nom_entreprise'] ?? '') ?>" required>
                    </label>
                    
                    <label for="lieu">Lieu
                        <input type="text" name="lieu" id="lieu" value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>" required>
                    </label>
                    
                    <label for="base_remuneration">Base de rémunération
                        <input type="text" name="base_remuneration" id="base_remuneration" value="<?= htmlspecialchars($_POST['base_remuneration'] ?? '') ?>">
                    </label>
                    
                    <label for="date_publication">Date de publication
                        <input type="date" name="date_publication" id="date_publication" value="<?= htmlspecialchars($_POST['date_publication'] ?? date('Y-m-d')) ?>" required>
                    </label>
                    
                    <label for="date_fin">Date de fin
                        <input type="date" name="date_fin" id="date_fin" value="<?= htmlspecialchars($_POST['date_fin'] ?? date('Y-m-d', strtotime('+1 month'))) ?>" required>
                    </label>
                    
                    <button type="submit" name="ajouter" <?php echo (!$canCreateOffer && !$canEditOffer) ? 'disabled' : ''; ?>>Enregistrer</button>
                </form>
                <?php endif; ?>
                
                <h2>Liste des offres de stage</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Entreprise</th>
                            <th>Lieu</th>
                            <th>Date publication</th>
                            <th>Date fin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="offreTable">
                        <?php foreach ($offres as $offre): ?>
                        <tr>
                            <td><?= htmlspecialchars($offre['titre']) ?></td>
                            <td><?= htmlspecialchars($offre['entreprise_nom']) ?></td>
                            <td><?= htmlspecialchars($offre['lieu']) ?></td>
                            <td><?= date('d/m/Y', strtotime($offre['date_publication'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($offre['date_fin'])) ?></td>
                            <td>
                                <?php if ($canEditOffer): ?>
                                <button class="edit-btn" onclick="editOffre(
                                    '<?= $offre['id'] ?>',
                                    '<?= addslashes($offre['titre']) ?>',
                                    '<?= addslashes($offre['description']) ?>',
                                    '<?= addslashes($offre['competences_requises']) ?>',
                                    '<?= addslashes($offre['entreprise_nom']) ?>',
                                    '<?= addslashes($offre['lieu']) ?>',
                                    '<?= addslashes($offre['base_remuneration']) ?>',
                                    '<?= date('Y-m-d', strtotime($offre['date_publication'])) ?>',
                                    '<?= date('Y-m-d', strtotime($offre['date_fin'])) ?>'
                                )">Modifier</button>
                                <?php endif; ?>
                                <?php if ($canDeleteOffer): ?>
                                <a href="?delete=<?= $offre['id'] ?>" onclick="return confirm('Supprimer cette offre de stage?')" class="delete-btn">Supprimer</a>
                                <?php endif; ?>
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

        function editOffre(id, titre, description, competences, entreprise_nom, lieu, base_remuneration, date_publication, date_fin) {
            document.getElementById('editId').value = id;
            document.getElementById('titre').value = titre;
            document.getElementById('description').value = description;
            document.getElementById('competences').value = competences;
            document.getElementById('nom_entreprise').value = entreprise_nom;
            document.getElementById('lieu').value = lieu;
            document.getElementById('base_remuneration').value = base_remuneration;
            document.getElementById('date_publication').value = date_publication;
            document.getElementById('date_fin').value = date_fin;
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
                window.location.href = 'authentification.php';
            }
        });
    </script>
</body>
</html>