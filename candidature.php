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
$userRole = $currentUser['role'];

// Récupération des données selon le rôle
if (in_array($userRole, ['admin', 'pilote'])) {
    // Admin et pilotes voient toutes les candidatures
    $candidatures = $pdo->query("
        SELECT c.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom,
               o.titre AS offre_titre, o.lieu AS offre_lieu,
               ent.nom AS entreprise_nom
        FROM candidatures c
        LEFT JOIN etudiants e ON c.etudiant_id = e.id
        LEFT JOIN offres_stage o ON c.offre_id = o.id
        LEFT JOIN entreprises ent ON o.entreprise_id = ent.id
        ORDER BY c.date_candidature DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} elseif ($userRole === 'etudiant') {
    // Étudiants ne voient que leurs propres candidatures
    $candidatures = $pdo->prepare("
        SELECT c.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom,
               o.titre AS offre_titre, o.lieu AS offre_lieu,
               ent.nom AS entreprise_nom
        FROM candidatures c
        LEFT JOIN etudiants e ON c.etudiant_id = e.id
        LEFT JOIN offres_stage o ON c.offre_id = o.id
        LEFT JOIN entreprises ent ON o.entreprise_id = ent.id
        WHERE c.etudiant_id = ?
        ORDER BY c.date_candidature DESC
    ");
    $candidatures->execute([$currentUser['id']]);
    $candidatures = $candidatures->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Entreprises voient les candidatures pour leurs offres
    $candidatures = $pdo->prepare("
        SELECT c.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom,
               o.titre AS offre_titre, o.lieu AS offre_lieu,
               ent.nom AS entreprise_nom
        FROM candidatures c
        LEFT JOIN etudiants e ON c.etudiant_id = e.id
        LEFT JOIN offres_stage o ON c.offre_id = o.id
        LEFT JOIN entreprises ent ON o.entreprise_id = ent.id
        WHERE o.entreprise_id = ?
        ORDER BY c.date_candidature DESC
    ");
    $candidatures->execute([$currentUser['id']]);
    $candidatures = $candidatures->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les offres de stage disponibles
if ($userRole === 'entreprise') {
    $offres = $pdo->prepare("
        SELECT o.*, e.nom AS entreprise_nom 
        FROM offres_stage o
        LEFT JOIN entreprises e ON o.entreprise_id = e.id
        WHERE o.date_fin >= CURDATE() AND o.entreprise_id = ?
        ORDER BY o.date_publication DESC
    ");
    $offres->execute([$currentUser['id']]);
    $offres = $offres->fetchAll(PDO::FETCH_ASSOC);
} else {
    $offres = $pdo->query("
        SELECT o.*, e.nom AS entreprise_nom 
        FROM offres_stage o
        LEFT JOIN entreprises e ON o.entreprise_id = e.id
        WHERE o.date_fin >= CURDATE()
        ORDER BY o.date_publication DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Candidatures</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <style>
        .rating { unicode-bidi: bidi-override; direction: rtl; }
        .rating > input { display: none; }
        .rating > label { display: inline-block; cursor: pointer; }
        .rating > label:hover,
        .rating > label:hover ~ label,
        .rating > input:checked ~ label { color: gold; }
        .disabled-form { opacity: 0.6; pointer-events: none; }
        .restricted { opacity: 0.6; pointer-events: none; }
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
                        <?= substr($currentUser['prenom'], 0, 1) . substr($currentUser['nom'], 0, 1); ?>
                    </div>
                    <span class="user-name">
                        <?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>
                        <small>(<?= htmlspecialchars($userRole) ?>)</small>
                    </span>
                    <span class="dropdown-icon">▼</span>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profil.php" class="dropdown-item">Mon profil</a>
                    <?php if ($userRole === 'etudiant'): ?>
                        <a href="wishlist.php" class="dropdown-item">Wish-list</a>
                    <?php endif; ?>
                    <div class="divider"></div>
                    <a href="authentification.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
                </div>
            </div>
        </nav>
        <nav>
            <a href="candidature.php">Accueil</a> |
            <?php if (in_array($userRole, ['admin', 'pilote', 'entreprise'])): ?>
                <a href="entreprise.php">Gestion des entreprises</a> |
            <?php endif; ?>
            <a href="stage.php">Gestion des offres de stage</a> |
            <?php if ($userRole === 'admin'): ?>
                <a href="pilote.php">Gestion des pilotes</a> |
            <?php endif; ?>
            <?php if (in_array($userRole, ['admin', 'pilote'])): ?>
                <a href="etudiant.php">Gestion des étudiants</a> |
            <?php endif; ?>
            <strong>Gestion des candidatures</strong>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Liste des Candidatures</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <?php if (in_array($userRole, ['admin', 'pilote'])): ?>
                                <th>Entreprise</th>
                            <?php endif; ?>
                            <th>Offre</th>
                            <th>Lieu</th>
                            <th>Date candidature</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($candidatures)): ?>
                            <tr><td colspan="7">Aucune candidature trouvée</td></tr>
                        <?php else: ?>
                            <?php foreach ($candidatures as $candidature): ?>
                                <tr>
                                    <td><?= htmlspecialchars($candidature['etudiant_prenom'] . ' ' . $candidature['etudiant_nom']) ?></td>
                                    <?php if (in_array($userRole, ['admin', 'pilote'])): ?>
                                        <td><?= htmlspecialchars($candidature['entreprise_nom']) ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($candidature['offre_titre']) ?></td>
                                    <td><?= htmlspecialchars($candidature['offre_lieu']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($candidature['date_candidature'])) ?></td>
                                    <td>
                                        <?php 
                                        $class = 'statut-en-attente';
                                        if ($candidature['statut'] === 'Acceptée') $class = 'statut-accepte';
                                        elseif ($candidature['statut'] === 'Refusée') $class = 'statut-refuse';
                                        ?>
                                        <span class="<?= $class ?>"><?= htmlspecialchars($candidature['statut']) ?></span>
                                    </td>
                                    <td>
                                        <a href="candidature_details.php?id=<?= $candidature['id'] ?>" class="edit-btn">Voir</a>
                                        <?php if (in_array($userRole, ['admin', 'pilote', 'entreprise'])): ?>
                                            <a href="?changer_statut=<?= $candidature['id'] ?>" class="edit-btn">Modifier</a>
                                        <?php endif; ?>
                                        <?php if (in_array($userRole, ['admin', 'pilote'])): ?>
                                            <a href="?supprimer=<?= $candidature['id'] ?>" class="delete-btn">Supprimer</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </article>
        </section>

        <section>
            <article>
                <h2>Offres de stage disponibles</h2>
                <div class="offers">
                    <?php if (empty($offres)): ?>
                        <p>Aucune offre de stage disponible actuellement.</p>
                    <?php else: ?>
                        <?php foreach ($offres as $offre): ?>
                            <div class="offer">
                                <h3><?= htmlspecialchars($offre['titre']) ?></h3>
                                <p class="small">
                                    <?= htmlspecialchars($offre['entreprise_nom']) ?> | 
                                    <?= htmlspecialchars($offre['lieu']) ?> | 
                                    Publiée le <?= date('d/m/Y', strtotime($offre['date_publication'])) ?>
                                </p>
                                <p><?= htmlspecialchars(substr($offre['description'], 0, 100)) ?>...</p>
                                <p><strong>Date limite:</strong> <?= date('d/m/Y', strtotime($offre['date_fin'])) ?></p>
                                <?php if ($userRole === 'etudiant'): ?>
                                    <a class="postuler" href="offres-stage-postuler.php?id=<?= $offre['id'] ?>">POSTULER</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>
        </section>
    </main>

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