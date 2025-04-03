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
$canCreateCompany = in_array($role, ['admin', 'pilote']);
$canEditCompany = in_array($role, ['admin', 'pilote']);
$canDeleteCompany = in_array($role, ['admin', 'pilote']);
$canRateCompany = true; // Tous peuvent évaluer

$servername = "172.201.65.180";
$username = "yohann";
$password = "Yohannboss04@";
$dbname = "gestion";
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dans le traitement de l'évaluation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rate_company'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF invalide");
    }
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    // Vérification des droits avant traitement
    if ((!empty($_POST['id']) && !$canEditCompany) || (empty($_POST['id']) && !$canCreateCompany)) {
        die("Action non autorisée");
    }

    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($_POST['telephone']);

    if (!empty($nom) && !empty($description) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (!empty($_POST['id'])) {
            $stmt = $pdo->prepare("UPDATE entreprises SET nom=?, description=?, url=?, email=?, telephone=? WHERE id=?");
            $stmt->execute([$nom, $description, $url, $email, $telephone, $_POST['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO entreprises (nom, description, url, email, telephone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $url, $email, $telephone]);
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rate_company'])) {
    $entreprise_id = (int)$_POST['entreprise_id'];
    $note = (int)$_POST['rating'];
    $user_id = $currentUser['id'];
    
    // Vérifier si l'utilisateur a déjà évalué cette entreprise
    $stmt = $pdo->prepare("SELECT id FROM evaluations WHERE entreprise_id=? AND user_id=?");
    $stmt->execute([$entreprise_id, $user_id]);
    
    if ($stmt->rowCount() == 0) {
        $insert = $pdo->prepare("INSERT INTO evaluations (entreprise_id, user_id, note) VALUES (?, ?, ?)");
        $insert->execute([$entreprise_id, $user_id, $note]);
    } else {
        // Option: permettre la mise à jour de l'évaluation existante
        $update = $pdo->prepare("UPDATE evaluations SET note=? WHERE entreprise_id=? AND user_id=?");
        $update->execute([$note, $entreprise_id, $user_id]);
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    if (!$canDeleteCompany) {
        die("Action non autorisée");
    }
    $stmt = $pdo->prepare("DELETE FROM entreprises WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

$entreprises = $pdo->query("
    SELECT e.*, 
           AVG(ev.note) as moyenne_notes,
           COUNT(ev.id) as nombre_evaluations,
           (SELECT note FROM evaluations WHERE entreprise_id = e.id AND user_id = ".$currentUser['id'].") as user_note
    FROM entreprises e
    LEFT JOIN evaluations ev ON ev.entreprise_id = e.id
    GROUP BY e.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des Entreprises</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <style>
        .rating { unicode-bidi: bidi-override; direction: ltr; }
        .rating > input { display: none; }
        .rating > label { display: inline-block; cursor: pointer; }
        .rating > label:hover,
        .rating > label:hover ~ label,
        .rating > input:checked ~ label { color: gold; }
        .disabled-form { opacity: 0.6; pointer-events: none; }
        .rating-form {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }
    
    .rate-btn {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .rate-btn:hover {
        background: #45a049;
    }
    
    .avg-rating {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
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
                    <?php if ($role === 'etudiant' or $role==='admin'): ?>
                        <a href="wishlist.php" class="dropdown-item">Wish-list</a>
                    <?php endif; ?>
                    <div class="divider"></div>
                    <a href="authentification.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
                </div>
            </div>
        </nav>
        <nav>
            <a href="candidature.php">Accueil</a> |
            <strong>Gestion des entreprises</strong>|
            <a href="stage.php">Gestion des offres de stage</a> |
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
                <h2>Rechercher une entreprise</h2>
                <input type="text" placeholder="Nom, Description ou Email" id="search" onkeyup="searchCompany()" required>
                
                <?php if ($canCreateCompany || $canEditCompany): ?>
                <h2>Ajouter/Modifier une entreprise</h2>
                <form method="POST" id="companyForm" <?php echo (!$canCreateCompany && !$canEditCompany) ? 'class="disabled-form"' : ''; ?>>
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
                    
                    <button type="submit" name="ajouter" <?php echo (!$canCreateCompany && !$canEditCompany) ? 'disabled' : ''; ?>>Enregistrer</button>
                </form>
                <?php endif; ?>
                
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
                                <?php if ($canEditCompany): ?>
                                <button class="edit-btn" onclick="editCompany(
                                    '<?= $entreprise['id'] ?>',
                                    '<?= addslashes($entreprise['nom']) ?>',
                                    '<?= addslashes($entreprise['description']) ?>',
                                    '<?= addslashes($entreprise['url']) ?>',
                                    '<?= addslashes($entreprise['email']) ?>',
                                    '<?= addslashes($entreprise['telephone']) ?>'
                                )">Modifier</button>
                                <?php endif; ?>
                                <?php if ($canDeleteCompany): ?>
                                <a href="?delete=<?= $entreprise['id'] ?>" onclick="return confirm('Supprimer cette entreprise?')" class="delete-btn">Supprimer</a>
                                <?php endif; ?>
                            </td>
                            <td>
                           <?php if ($canRateCompany): ?>
                            <form method="POST" class="rating-form">
                             <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
                            <input type="hidden" name="rate_company" value="1">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <div class="rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                          <input type="radio" id="star<?= $i ?>_<?= $entreprise['id'] ?>" 
                          name="rating" value="<?= $i ?>"
                           <?= (isset($entreprise['user_note']) && $entreprise['user_note'] == $i ? 'checked' : '' )?>>
                         <label for="star<?= $i ?>_<?= $entreprise['id'] ?>" title="<?= $i ?> étoiles">★</label>
                         <?php endfor; ?>
        </div>
        <button type="submit" class="rate-btn">Évaluer</button>
        <?php if (isset($entreprise['moyenne_notes'])): ?>
        <div class="avg-rating">
            Moyenne: <?= number_format($entreprise['moyenne_notes'], 1) ?>/5 
            (<?= $entreprise['nombre_evaluations'] ?> avis)
        </div>
        <?php endif; ?>
    </form>
    <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </section>
    </main>

    <footer>
        <a href="mentions_legales.pdf"><em>2024 - Tous droits réservés - Web4All</em></a>
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
        document.querySelectorAll('.rating-form').forEach(form => {
       form.addEventListener('submit', function(e) {
        if (!confirm('Confirmez-vous cette évaluation ?')) {
            e.preventDefault();
        }
    });
});
    </script>
</body>
</html>