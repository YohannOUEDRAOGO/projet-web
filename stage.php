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
$canViewStats = true;
$canViewDetails = true; // Tous peuvent voir les détails

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
$entreprises = $pdo->query("SELECT id, nom FROM entreprises ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les domaines disponibles
$domaines = $pdo->query("SELECT DISTINCT domaine FROM offres_stage")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gestion de l'ajout/modification
    if (isset($_POST['ajouter'])) {
        if ((!empty($_POST['id']) && !$canEditOffer) || (empty($_POST['id']) && !$canCreateOffer)) {
            die("Action non autorisée");
        }

        // Récupération et validation des données
        $titre = htmlspecialchars($_POST['titre'] ?? '');
        $description = htmlspecialchars($_POST['description'] ?? '');
        $competences = htmlspecialchars($_POST['competences'] ?? '');
        $domaine = htmlspecialchars($_POST['domaine'] ?? '');
        $entreprise_id = (int)$_POST['entreprise_id'];
        $lieu = htmlspecialchars($_POST['lieu'] ?? '');
        $base_remuneration = htmlspecialchars($_POST['base_remuneration'] ?? '');
        $duree = htmlspecialchars($_POST['duree'] ?? '');
        $date_publication = $_POST['date_publication'] ?? date('Y-m-d');
        $date_fin = $_POST['date_fin'] ?? date('Y-m-d', strtotime('+1 month'));

        // Validation des champs obligatoires
        $errors = [];
        if (empty($titre)) $errors[] = "Le titre est obligatoire";
        if (empty($description)) $errors[] = "La description est obligatoire";
        if (empty($domaine)) $errors[] = "Le domaine est obligatoire";
        if (empty($entreprise_id)) $errors[] = "L'entreprise est obligatoire";
        if (empty($lieu)) $errors[] = "Le lieu est obligatoire";
        if (empty($duree)) $errors[] = "La durée est obligatoire";
        if (empty($date_publication)) $errors[] = "La date de publication est obligatoire";
        if (empty($date_fin)) $errors[] = "La date de fin est obligatoire";

        if (empty($errors)) {
            try {
                if (!empty($_POST['id'])) {
                    $stmt = $pdo->prepare("UPDATE offres_stage SET titre=?, description=?, competences_requises=?, domaine=?, entreprise_id=?, lieu=?, base_remuneration=?, duree=?, date_publication=?, date_fin=? WHERE id=?");
                    $stmt->execute([$titre, $description, $competences, $domaine, $entreprise_id, $lieu, $base_remuneration, $duree, $date_publication, $date_fin, $_POST['id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO offres_stage (titre, description, competences_requises, domaine, entreprise_id, lieu, base_remuneration, duree, date_publication, date_fin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$titre, $description, $competences, $domaine, $entreprise_id, $lieu, $base_remuneration, $duree, $date_publication, $date_fin]);
                }
                header("Location: ".$_SERVER['PHP_SELF']);
                exit;
            } catch (PDOException $e) {
                $errors[] = "Erreur de base de données : " . $e->getMessage();
            }
        }
    }
    
    // Gestion de la recherche avancée
    if (isset($_POST['rechercher'])) {
        $filtre_titre = $_POST['filtre_titre'] ?? '';
        $filtre_entreprise = $_POST['filtre_entreprise'] ?? '';
        $filtre_domaine = $_POST['filtre_domaine'] ?? '';
        $filtre_lieu = $_POST['filtre_lieu'] ?? '';
        
        $query = "SELECT o.*, e.nom AS entreprise_nom FROM offres_stage o LEFT JOIN entreprises e ON o.entreprise_id = e.id WHERE 1=1";
        $params = [];
        
        if (!empty($filtre_titre)) {
            $query .= " AND o.titre LIKE ?";
            $params[] = "%$filtre_titre%";
        }
        if (!empty($filtre_entreprise)) {
            $query .= " AND e.nom LIKE ?";
            $params[] = "%$filtre_entreprise%";
        }
        if (!empty($filtre_domaine)) {
            $query .= " AND o.domaine = ?";
            $params[] = $filtre_domaine;
        }
        if (!empty($filtre_lieu)) {
            $query .= " AND o.lieu LIKE ?";
            $params[] = "%$filtre_lieu%";
        }
        
        $query .= " ORDER BY o.date_publication DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

// Récupération des statistiques
$stats = $pdo->query("
    SELECT 
        domaine, 
        COUNT(*) as nombre,
        AVG(LENGTH(competences_requises) - LENGTH(REPLACE(competences_requises, ',', '')) + 1) as moy_competences,
        MIN(date_publication) as plus_ancienne,
        MAX(date_publication) as plus_recente
    FROM offres_stage 
    GROUP BY domaine
")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des offres si pas déjà fait
if (!isset($offres)) {
    $offres = $pdo->query("
        SELECT o.*, e.nom AS entreprise_nom 
        FROM offres_stage o 
        LEFT JOIN entreprises e ON o.entreprise_id = e.id
        ORDER BY o.date_publication DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour compter les offres par durée
function compterOffresParDuree($pdo) {
    $durees = [
        '1-2' => "duree IN ('1', '2')",
        '3-6' => "duree IN ('3', '4', '5', '6')",
        '7-9' => "duree IN ('7', '8', '9')"
    ];
    
    $resultats = [];
    foreach ($durees as $key => $condition) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM offres_stage WHERE $condition");
        $stmt->execute();
        $resultats[$key] = $stmt->fetchColumn();
    }
    return $resultats;
}

$offresParDuree = compterOffresParDuree($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des offres de stage</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .disabled-form { opacity: 0.6; pointer-events: none; }
        .hidden { display: none; }
        select, input[type="text"], input[type="date"], textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-container {
            width: 100%;
            height: 300px;
            margin: 20px 0;
        }
        .details-row {
            background-color: #f9f9f9;
        }
        .details-content {
            padding: 15px;
            border-top: 1px solid #ddd;
        }
        .search-advanced {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .toggle-search {
            cursor: pointer;
            color: #007bff;
            margin-bottom: 10px;
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
                    <?php if ($role === 'etudiant'): ?>
                        <a href="wishlist.php" class="dropdown-item">Wish-list</a>
                    <?php endif; ?>
                    <div class="divider"></div>
                    <a href="logout.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
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
            <a href="candidatures.php">Mes candidatures</a>
        </nav>
    </header>

    <main>
        <section>
            <article>
                <h2>Rechercher une offre de stage</h2>
                <input type="text" placeholder="Titre, compétences, lieu..." id="search" onkeyup="searchOffre()" required>
                
                <div class="toggle-search" onclick="toggleAdvancedSearch()">
                    <i class="fas fa-search-plus"></i> Recherche avancée
                </div>
                
                <div id="advancedSearch" class="search-advanced hidden">
                    <form method="POST">
                        <label for="filtre_titre">Titre :
                            <input type="text" name="filtre_titre" id="filtre_titre" value="<?= htmlspecialchars($_POST['filtre_titre'] ?? '') ?>">
                        </label>
                        
                        <label for="filtre_entreprise">Entreprise :
                            <input type="text" name="filtre_entreprise" id="filtre_entreprise" value="<?= htmlspecialchars($_POST['filtre_entreprise'] ?? '') ?>">
                        </label>
                        
                        <label for="filtre_domaine">Domaine :
                            <select name="filtre_domaine" id="filtre_domaine">
                                <option value="">-- Tous domaines --</option>
                                <?php foreach ($domaines as $domaine): ?>
                                    <option value="<?= htmlspecialchars($domaine) ?>" 
                                        <?= (isset($_POST['filtre_domaine']) && $_POST['filtre_domaine'] == $domaine) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($domaine) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        
                        <label for="filtre_lieu">Lieu :
                            <input type="text" name="filtre_lieu" id="filtre_lieu" value="<?= htmlspecialchars($_POST['filtre_lieu'] ?? '') ?>">
                        </label>
                        
                        <button type="submit" name="rechercher">Appliquer les filtres</button>
                        <button type="button" onclick="resetSearch()">Réinitialiser</button>
                    </form>
                </div>
                
                <?php if ($canCreateOffer || $canEditOffer): ?>
                <h2>Ajouter/Modifier une offre de stage</h2>
                <form method="POST" id="offreForm" <?php echo (!$canCreateOffer && !$canEditOffer) ? 'class="disabled-form"' : ''; ?>>
                    <input type="hidden" id="editId" name="id" value="<?= $_POST['id'] ?? '' ?>">
                    
                    <label for="titre">Titre*
                        <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
                    </label>
                    
                    <label for="description">Description*
                        <textarea name="description" id="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </label>
                    
                    <label for="competences">Compétences requises*
                        <textarea name="competences" id="competences" required><?= htmlspecialchars($_POST['competences'] ?? '') ?></textarea>
                    </label>
                    
                    <label for="domaine">Domaine*
                        <select name="domaine" id="domaine" required>
                            <option value="">-- Sélectionner un domaine --</option>
                            <?php foreach ($domaines as $domaine): ?>
                                <option value="<?= htmlspecialchars($domaine) ?>" 
                                    <?= (isset($_POST['domaine']) && $_POST['domaine'] == $domaine) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($domaine) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    
                    <label for="entreprise_id">Entreprise*
                        <select name="entreprise_id" id="entreprise_id" required>
                            <option value="">-- Sélectionner une entreprise --</option>
                            <?php foreach ($entreprises as $entreprise): ?>
                                <option value="<?= $entreprise['id'] ?>" 
                                    <?= (isset($_POST['entreprise_id']) && $_POST['entreprise_id'] == $entreprise['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($entreprise['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    
                    <label for="lieu">Lieu*
                        <input type="text" name="lieu" id="lieu" value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>" required>
                    </label>
                    
                    <label for="base_remuneration">Base de rémunération
                        <input type="text" name="base_remuneration" id="base_remuneration" value="<?= htmlspecialchars($_POST['base_remuneration'] ?? '') ?>">
                    </label>
                    
                    <label for="duree">Durée (mois)*
                        <select name="duree" id="duree" required>
                            <option value="1" <?= (isset($_POST['duree']) && $_POST['duree'] == '1') ? 'selected' : '' ?>>1-2 mois</option>
                            <option value="3" <?= (isset($_POST['duree']) && $_POST['duree'] == '3') ? 'selected' : '' ?>>3-6 mois</option>
                            <option value="7" <?= (isset($_POST['duree']) && $_POST['duree'] == '7') ? 'selected' : '' ?>>7-9 mois</option>
                        </select>
                    </label>
                    
                    <label for="date_publication">Date de publication*
                        <input type="date" name="date_publication" id="date_publication" value="<?= htmlspecialchars($_POST['date_publication'] ?? date('Y-m-d')) ?>" required>
                    </label>
                    
                    <label for="date_fin">Date de fin*
                        <input type="date" name="date_fin" id="date_fin" value="<?= htmlspecialchars($_POST['date_fin'] ?? date('Y-m-d', strtotime('+1 month'))) ?>" required>
                    </label>
                    
                    <button type="submit" name="ajouter" <?php echo (!$canCreateOffer && !$canEditOffer) ? 'disabled' : ''; ?>>Enregistrer</button>
                    <button type="button" onclick="resetForm()" class="secondary">Annuler</button>
                </form>
                <?php endif; ?>
                
                <?php if ($canViewStats): ?>
                <h2>Statistiques des offres</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Total des offres</h3>
                        <p><?= count($offres) ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Offres actives</h3>
                        <p><?= count(array_filter($offres, function($o) { return strtotime($o['date_fin']) >= time(); })) ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Entreprises partenaires</h3>
                        <p><?= count($entreprises) ?></p>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="domaineChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <canvas id="dureeChart"></canvas>
                </div>
                <?php endif; ?>
                
                <h2>Liste des offres de stage</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Entreprise</th>
                            <th>Domaine</th>
                            <th>Lieu</th>
                            <th>Date publication</th>
                            <th>Date fin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="offreTable">
                        <?php foreach ($offres as $offre): ?>
                        <tr class="main-row" onclick="toggleDetails(<?= $offre['id'] ?>)">
                            <td><?= htmlspecialchars($offre['titre']) ?></td>
                            <td><?= htmlspecialchars($offre['entreprise_nom']) ?></td>
                            <td><?= htmlspecialchars($offre['domaine']) ?></td>
                            <td><?= htmlspecialchars($offre['lieu']) ?></td>
                            <td><?= date('d/m/Y', strtotime($offre['date_publication'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($offre['date_fin'])) ?></td>
                            <td>
                                <?php if ($canEditOffer): ?>
                                <button class="edit-btn" onclick="event.stopPropagation(); editOffre(
                                    '<?= $offre['id'] ?>',
                                    '<?= addslashes($offre['titre']) ?>',
                                    '<?= addslashes($offre['description']) ?>',
                                    '<?= addslashes($offre['competences_requises']) ?>',
                                    '<?= addslashes($offre['domaine']) ?>',
                                    '<?= $offre['entreprise_id'] ?>',
                                    '<?= addslashes($offre['lieu']) ?>',
                                    '<?= addslashes($offre['base_remuneration']) ?>',
                                    '<?= $offre['duree'] ?>',
                                    '<?= date('Y-m-d', strtotime($offre['date_publication'])) ?>',
                                    '<?= date('Y-m-d', strtotime($offre['date_fin'])) ?>'
                                )"><i class="fas fa-edit"></i></button>
                                <?php endif; ?>
                                <?php if ($canDeleteOffer): ?>
                                <a href="?delete=<?= $offre['id'] ?>" onclick="event.stopPropagation(); return confirm('Supprimer cette offre de stage?')" class="delete-btn"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                                <?php if ($role === 'etudiant'): ?>
                                <button class="wishlist-btn" onclick="event.stopPropagation(); addToWishlist(<?= $offre['id'] ?>)"><i class="fas fa-heart"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr id="details-<?= $offre['id'] ?>" class="details-row hidden">
                            <td colspan="7" class="details-content">
                                <h4>Détails de l'offre</h4>
                                <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($offre['description'])) ?></p>
                                <p><strong>Compétences requises :</strong> <?= nl2br(htmlspecialchars($offre['competences_requises'])) ?></p>
                                <p><strong>Base de rémunération :</strong> <?= htmlspecialchars($offre['base_remuneration'] ?? 'Non précisée') ?></p>
                                <p><strong>Durée :</strong> 
                                    <?php 
                                        switch($offre['duree']) {
                                            case '1': echo '1-2 mois'; break;
                                            case '3': echo '3-6 mois'; break;
                                            case '7': echo '7-9 mois'; break;
                                            default: echo htmlspecialchars($offre['duree']);
                                        }
                                    ?>
                                </p>
                                <?php if ($role === 'etudiant'): ?>
                                <button onclick="postuler(<?= $offre['id'] ?>)" class="apply-btn">Postuler</button>
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
        // Fonction pour afficher/masquer la recherche avancée
        function toggleAdvancedSearch() {
            const advancedSearch = document.getElementById('advancedSearch');
            advancedSearch.classList.toggle('hidden');
        }
        
        // Fonction pour réinitialiser la recherche
        function resetSearch() {
            window.location.href = window.location.pathname;
        }
        
        // Fonction pour réinitialiser le formulaire
        function resetForm() {
            document.getElementById('offreForm').reset();
            document.getElementById('editId').value = '';
            window.scrollTo(0, 0);
        }
        
        // Fonction pour afficher/masquer les détails d'une offre
        function toggleDetails(id) {
            const detailsRow = document.getElementById(`details-${id}`);
            detailsRow.classList.toggle('hidden');
        }
        
        // Fonction pour éditer une offre
        function editOffre(id, titre, description, competences, domaine, entreprise_id, lieu, base_remuneration, duree, date_publication, date_fin) {
            document.getElementById('editId').value = id;
            document.getElementById('titre').value = titre;
            document.getElementById('description').value = description;
            document.getElementById('competences').value = competences;
            document.getElementById('domaine').value = domaine;
            document.getElementById('entreprise_id').value = entreprise_id;
            document.getElementById('lieu').value = lieu;
            document.getElementById('base_remuneration').value = base_remuneration;
            document.getElementById('duree').value = duree;
            document.getElementById('date_publication').value = date_publication;
            document.getElementById('date_fin').value = date_fin;
            window.scrollTo(0, 0);
        }
        
        // Fonction de recherche instantanée
        function searchOffre() {
            const filter = document.getElementById('search').value.toLowerCase();
            document.querySelectorAll('#offreTable .main-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
                
                // Masquer aussi les détails si la ligne principale est masquée
                const id = row.getAttribute('onclick').match(/\d+/)[0];
                const detailsRow = document.getElementById(`details-${id}`);
                if (detailsRow) {
                    detailsRow.style.display = text.includes(filter) ? '' : 'none';
                }
            });
        }
        
        // Fonction pour ajouter à la wishlist
        function addToWishlist(offreId) {
            fetch('add_to_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ offre_id: offreId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Offre ajoutée à votre wishlist !');
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue');
            });
        }
        
        // Fonction pour postuler
        function postuler(offreId) {
            if (confirm('Voulez-vous vraiment postuler à cette offre ?')) {
                window.location.href = `postuler.php?offre_id=${offreId}`;
            }
        }
        
        // Initialisation des graphiques
        window.onload = function() {
            // Graphique par domaine
            const domaineCtx = document.getElementById('domaineChart').getContext('2d');
            const domaineLabels = <?= json_encode(array_column($stats, 'domaine')) ?>;
            const domaineData = <?= json_encode(array_column($stats, 'nombre')) ?>;
            
            new Chart(domaineCtx, {
                type: 'bar',
                data: {
                    labels: domaineLabels,
                    datasets: [{
                        label: 'Nombre d\'offres par domaine',
                        data: domaineData,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Graphique par durée
            const dureeCtx = document.getElementById('dureeChart').getContext('2d');
            const dureeLabels = ['1-2 mois', '3-6 mois', '7-9 mois'];
            const dureeData = <?= json_encode(array_values($offresParDuree)) ?>;
            
            new Chart(dureeCtx, {
                type: 'pie',
                data: {
                    labels: dureeLabels,
                    datasets: [{
                        label: 'Répartition par durée',
                        data: dureeData,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        };
        
        // Gestion du menu utilisateur
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }
        
        window.onclick = function(e) {
            if (!e.target.matches('.user-info *')) {
                document.getElementById('dropdownMenu').classList.remove('show');
            }
        };
    </script>
</body>
</html>