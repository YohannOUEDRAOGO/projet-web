<?php
require_once 'check_session.php';
verifySession();
if (!isset($_SESSION['user'])) {
    header('Location: authentification.php');
    exit();
}

// Connexion à la base de données
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
main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .offers {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 2rem;
        }
        
        .offer {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: transform 0.3s;
            border: 1px solid #ddd;
        }
        
        .offer:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .postuler {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }
        
        .postuler:hover {
            background-color: #2980b9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            border: 1px solid #ddd;
        }
        
        th {
            background-color: #34495e;
            color: white;
            padding: 1rem;
            text-align: left;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .statut-en-attente {
            color: #f39c12;
            font-weight: bold;
        }
        
        .statut-accepte {
            color: #2ecc71;
            font-weight: bold;
        }
        
        .statut-refuse {
            color: #e74c3c;
            font-weight: bold;
        }
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

    <!-- [Le reste de votre code HTML/JS] -->
</body>
</html>