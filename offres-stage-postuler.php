<?php
// Vérification de session et récupération de l'utilisateur
require_once 'check_session.php';
verifySession();
if (!isset($_SESSION['user'])) {
    header('Location: authentification.php');
    exit();
}

$currentUser = $_SESSION['user'];
$role = $currentUser['role'];
$successMessage = "";
$errorMessage = "";

$offerTitle = htmlspecialchars($_GET['title'] ?? 'Offre de stage');
$company = htmlspecialchars($_GET['company'] ?? '');
$location = htmlspecialchars($_GET['location'] ?? '');
$publishDate = htmlspecialchars($_GET['date'] ?? '');

// Récupérer l'ID de l'offre depuis l'URL
$offerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si l'ID est valide, récupérer les détails complets depuis la base
if ($offerId > 0) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=gestion;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("
            SELECT o.*, e.nom AS entreprise_nom 
            FROM offres_stage o
            LEFT JOIN entreprises e ON o.entreprise_id = e.id
            WHERE o.id = :id
        ");
        $stmt->execute([':id' => $offerId]);
        $offerDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($offerDetails) {
            $offerTitle = htmlspecialchars($offerDetails['titre']);
            $company = htmlspecialchars($offerDetails['entreprise_nom']);
            $location = htmlspecialchars($offerDetails['lieu']);
            $publishDate = date('d/m/Y', strtotime($offerDetails['date_publication']));
            $description = htmlspecialchars($offerDetails['description']);
            $competences = htmlspecialchars($offerDetails['competences_requises']);
            $dateFin = date('d/m/Y', strtotime($offerDetails['date_fin']));
        }
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
} else {
    // Fallback aux paramètres GET si pas d'ID ou échec de la requête
    $offerTitle = htmlspecialchars($_GET['title'] ?? 'Offre de stage');
    $company = htmlspecialchars($_GET['company'] ?? '');
    $location = htmlspecialchars($_GET['location'] ?? '');
    $publishDate = htmlspecialchars($_GET['date'] ?? '');
}

// Dans la partie POST du fichier offres-stage-postuler.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitBtn'])) {
    // Validation des champs
    $title = htmlspecialchars($_POST['title'] ?? '');
    $lastname = htmlspecialchars($_POST['lastname'] ?? '');
    $surname = htmlspecialchars($_POST['surname'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $feedbacks = htmlspecialchars($_POST['feedbacks'] ?? '');
    $majeur = $_POST['majeur'] ?? '';
    $categories = isset($_POST['category']) ? implode(", ", $_POST['category']) : 'Aucune';
    
    // Créer un identifiant unique pour l'utilisateur
    $userFolder = mb_strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $lastname . '_' . $surname));
    $uploadDir = 'uploads/' . $userFolder . '/';
    
    // Créer le répertoire s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Gestion du fichier CV
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['cv']['name']);
        $uniqueFilename = time() . '_' . $filename;
        $uploadFile = $uploadDir . $uniqueFilename;

        if ($_FILES['cv']['size'] > 2 * 1024 * 1024) {
            $errorMessage = "Erreur : Le fichier est trop volumineux (2Mo max).";
        } else {
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $uploadFile)) {
                // Créer un fichier texte avec les informations du candidat
                $infoContent = "Candidature pour: $offerTitle\n";
                $infoContent .= "Entreprise: $company\n\n";
                $infoContent .= "Informations candidat:\n";
                $infoContent .= "Civilite: " . ($title === 'ms' ? 'Madame' : 'Monsieur') . "\n";
                $infoContent .= "Nom: $lastname\n";
                $infoContent .= "Prénom: $surname\n";
                $infoContent .= "Email: $email\n";
                $infoContent .= "Majeur: " . ($majeur === 'yes' ? 'Oui' : 'Non') . "\n";
                $infoContent .= "Informations complémentaires: $categories\n";
                $infoContent .= "Message pour le recruteur:\n$feedbacks\n";
                $infoContent .= "Date de candidature: " . date('d/m/Y H:i:s') . "\n";
                $infoContent .= "CV: $uniqueFilename";
                
                file_put_contents($uploadDir . 'info_candidature.txt', $infoContent);

                $successMessage = "Votre candidature a bien été envoyée !";
                
                // Enregistrement dans le fichier JSON
                $newCandidature = [
                    "entreprise" => $company,
                    "offre" => $offerTitle,
                    "date" => date("d/m/Y"),
                    "statut" => "En attente",
                    "dossier" => $userFolder
                ];

                $file = 'candidatures.json';
                $candidatures = [];

                if (file_exists($file)) {
                    $jsonData = file_get_contents($file);
                    $candidatures = json_decode($jsonData, true);
                }

                $candidatures[] = $newCandidature;
                file_put_contents($file, json_encode($candidatures, JSON_PRETTY_PRINT));
            } else {
                $errorMessage = "Erreur lors du téléchargement du CV.";
            }
        }
    } else {
        $errorMessage = "Veuillez ajouter votre CV.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postuler à une offre - Lebonplan</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <style>
        .formulaire {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }
        
        .formulaire label {
            display: block;
            margin-bottom: 1rem;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .formulaire input[type="text"],
        .formulaire input[type="email"],
        .formulaire select,
        .formulaire textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        
        .formulaire textarea {
            min-height: 150px;
        }
        
        .checkbox, .checkbox2 {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .upload-btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .upload-btn:hover {
            background-color: #2980b9;
        }
        
        .buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .buttons button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        .buttons button:hover {
            background-color: #2980b9;
        }
        
        .buttons input[type="reset"] {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        .buttons input[type="reset"]:hover {
            background-color: #c0392b;
        }
        
        .offer-details {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        #file-name {
            color: #3498db;
            font-weight: bold;
            margin-top: 0.5rem;
        }
        
        .success-message {
            color: #2ecc71;
            font-weight: bold;
            text-align: center;
            margin: 1rem 0;
        }
        
        .error-message {
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            margin: 1rem 0;
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
                <a href="authentification.php" class="dropdown-item" id="logoutBtn">Déconnexion</a>
            </div>
        </div>
    </nav>
    <nav>
        <a href="candidature.php">Accueil</a> |
        <a href="entreprise.php">Gestion des entreprises</a> |
        <a href="stage.php">Gestion des offres de stage</a> |
        <?php if ($role === 'admin'): ?>
            <a href="pilote.php">Gestion des pilotes</a> |
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'pilote'])): ?>
            <a href="etudiant.php">Gestion des étudiants</a> |
        <?php endif; ?>
        <a href="candidature.php">Gestion des candidatures</a>
    </nav>
</header>

    <main>
        <section>
            <article>
                <h2>Postuler à une offre de stage</h2>
                <p>Vous pouvez ici répondre directement à l'offre de stage qui a été déposée par l'entreprise. Soyez le plus précis possible dans vos réponses !</p>
                
                <div class="offer-details">
                    <h3><?= $offerTitle ?></h3>
                    <p class="small"><?= $company ?> | <?= $location ?> | Publiée le <?= $publishDate ?></p>
                    <?php if (isset($description)): ?>
                        <p><strong>Description :</strong> <?= $description ?></p>
                    <?php endif; ?>
                    <?php if (isset($competences)): ?>
                        <p><strong>Compétences requises :</strong> <?= $competences ?></p>
                    <?php endif; ?>
                    <?php if (isset($dateFin)): ?>
                        <p><strong>Date limite :</strong> <?= $dateFin ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($successMessage): ?>
                    <div class="success-message"><?= $successMessage ?></div>
                <?php elseif ($errorMessage): ?>
                    <div class="error-message"><?= $errorMessage ?></div>
                <?php endif; ?>

                <form class="formulaire" id="postulerForm" action="" method="POST" enctype="multipart/form-data">
                    <label>CIVILITÉ
                        <select name="title" id="title" required>
                            <option value="">--Choisissez--</option>
                            <option value="ms">Madame</option>
                            <option value="mr">Monsieur</option>
                        </select>
                    </label>

                    <label>NOM
                        <input name="lastname" id="lastname" type="text" required>
                    </label>

                    <label>PRÉNOM
                        <input name="surname" id="surname" type="text" required>
                    </label>

                    <label>COURRIEL
                        <input name="email" id="email" type="email" required>
                        <input type="hidden" name="offer_id" value="<?= $offerId ?>">
                    </label>

                    <label>A PROPOS DE VOUS
                        <div class="checkbox">
                            <div class="box"><input type="checkbox" name="category[]" value="permis">Permis B</div>
                            <div class="box"><input type="checkbox" name="category[]" value="car">Véhiculé</div>
                            <div class="box"><input type="checkbox" name="category[]" value="certification">Certifications (Microsoft, Cisco)</div>
                        </div>
                    </label>

                    <label>
                        JE SUIS MAJEUR
                        <div class="checkbox2">
                            <div class="box"><input type="radio" name="majeur" value="yes" required>Oui</div>
                            <div class="box"><input type="radio" name="majeur" value="no">Non</div>
                        </div>
                    </label>

                    <label>VOTRE MESSAGE AU RECRUTEUR
                        <textarea name="feedbacks" id="feedbacks" required></textarea>
                    </label>

                    <label>
                        CV
                        <input type="file" id="file-upload" name="cv" hidden accept=".pdf, .doc, .docx, .odt, .rtf, .jpg, .png" required>
                        <label for="file-upload" class="upload-btn">AJOUTER MON CV</label>
                        <p class="specifications">Poids max 2Mo - Formats .pdf, .doc, .docx, .odt, .rtf, .jpg ou png</p>
                        <p id="file-name"></p>
                    </label>

                    <div class="buttons">
                        <button name="submitBtn" type="submit">POSTULER</button>
                        <input type="reset" value="RÉINITIALISER" onclick="resetFileName()">
                    </div>

                    <p class="smaller">En cliquant sur 'Postuler', vous acceptez les <a href="#">CGU</a> et déclarez avoir pris connaissance de la <a href="#">politique de protection des données</a> de notre site.</p>
                </form>
            </article>
        </section>
    </main>

    <footer></footer>

    <script>
        // Menu utilisateur
        function toggleMenu() {
            document.getElementById('dropdownMenu').classList.toggle('show');
        }

        window.onclick = function(e) {
            if (!e.target.matches('.user-info *')) {
                document.getElementById('dropdownMenu').classList.remove('show');
            }
        }

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Déconnexion ?')) {
                window.location.href = 'authentification.php';
            }
        });

        // Gestion du fichier
        const fileUpload = document.getElementById('file-upload');
        const fileNameDisplay = document.getElementById('file-name');

        fileUpload.addEventListener('change', function () {
            if (fileUpload.files.length > 0) {
                const fileName = fileUpload.files[0].name;
                fileNameDisplay.textContent = "CV sélectionné : " + fileName;
            }
        });

        function resetFileName() {
            fileNameDisplay.textContent = "";
        }
    </script>
</body>
</html>