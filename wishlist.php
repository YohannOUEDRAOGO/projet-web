<?php
require_once 'check_session.php';
verifySession();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'etudiant') {
    header('Location: authentification.php');
    exit();
}

$currentUser = $_SESSION['user'];
$studentId = $currentUser['id'];

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

// Récupérer les offres enregistrées dans la wishlist
$wishlist = $pdo->prepare("
    SELECT o.*, e.nom AS entreprise_nom 
    FROM offres_stage o
    JOIN wishlist w ON o.id = w.offre_id
    JOIN entreprises e ON o.entreprise_id = e.id
    WHERE w.user_id = ?
    ORDER BY w.date_ajout DESC
");
$wishlist->execute([$studentId]);
$wishlist = $wishlist->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Wishlist</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <style>
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
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
            border: 1px solid #ddd;
            position: relative;
        }
        
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
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
                        <?= substr($currentUser['prenom'], 0, 1) . substr($currentUser['nom'], 0, 1); ?>
                    </div>
                    <span class="user-name">
                        <?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>
                        <small>(<?= htmlspecialchars($currentUser['role']) ?>)</small>
                    </span>
                    <span class="dropdown-icon">▼</span>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profil.php" class="dropdown-item">Mon profil</a>
                    <a href="wishlist.php" class="dropdown-item">Wish-list</a>
                    <div class="divider"></div>
                    <a href="authentification.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
                </div>
            </div>
        </nav>
        <nav>
            <a href="candidature.php">Accueil</a> |
            <a href="stage.php">Gestion des offres de stage</a> |
            <strong>Ma Wishlist</strong>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Mes offres enregistrées</h2>
                <div class="offers">
                    <?php if (empty($wishlist)): ?>
                        <p>Vous n'avez aucune offre enregistrée dans votre wishlist.</p>
                    <?php else: ?>
                        <?php foreach ($wishlist as $offre): ?>
                            <div class="offer">
                                <button class="remove-btn" onclick="removeFromWishlist(<?= $offre['id'] ?>)">×</button>
                                <h3><?= htmlspecialchars($offre['titre']) ?></h3>
                                <p class="small">
                                    <?= htmlspecialchars($offre['entreprise_nom']) ?> | 
                                    <?= htmlspecialchars($offre['lieu']) ?> | 
                                    Publiée le <?= date('d/m/Y', strtotime($offre['date_publication'])) ?>
                                </p>
                                <p><?= htmlspecialchars(substr($offre['description'], 0, 100)) ?>...</p>
                                <p><strong>Date limite:</strong> <?= date('d/m/Y', strtotime($offre['date_fin'])) ?></p>
                                <a class="postuler" href="offres-stage-postuler.php?id=<?= $offre['id'] ?>">POSTULER</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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

        function removeFromWishlist(offerId) {
            if (confirm('Retirer cette offre de votre wishlist ?')) {
                fetch('add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `offer_id=${offerId}&action=remove`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Recharger la page après suppression
                        window.location.reload();
                    }
                });
            }
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