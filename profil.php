<?php
require_once 'check_session.php';
verifySession();

if (!isset($_SESSION['user'])) {
    header('Location: authentification.php');
    exit();
}

$currentUser = $_SESSION['user'];
$role = $currentUser['role'];

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

$successMessage = "";
$errorMessage = "";

// Traitement du changement de mot de passe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Vérifier que le nouveau mot de passe et la confirmation correspondent
    if ($newPassword !== $confirmPassword) {
        $errorMessage = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        // Vérifier l'ancien mot de passe
        $stmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($currentPassword, $user['password'])) {
            // Mettre à jour le mot de passe
            $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
            $stmt->execute([$newPasswordHash, $currentUser['id']]);
            
            $successMessage = "Mot de passe mis à jour avec succès.";
        } else {
            $errorMessage = "Mot de passe actuel incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mon Profil - Lebonplan</title>
    <link href="style/style_entreprises.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .profile-section, .password-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
        }
        
        .info-label {
            font-weight: 500;
            color: #555;
        }
        
        .info-value {
            padding: 0.5rem;
            background: #f9f9f9;
            border-radius: 4px;
            margin-top: 0.3rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .success-message {
            color: #2ecc71;
            font-weight: bold;
            margin: 1rem 0;
            text-align: center;
        }
        
        .error-message {
            color: #e74c3c;
            font-weight: bold;
            margin: 1rem 0;
            text-align: center;
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
        <div class="profile-container">
            <h1>Mon Profil</h1>
            
            <?php if ($successMessage): ?>
                <div class="success-message"><?= $successMessage ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="error-message"><?= $errorMessage ?></div>
            <?php endif; ?>
            
            <div class="profile-section">
                <h2>Informations personnelles</h2>
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Nom</div>
                        <div class="info-value"><?= htmlspecialchars($currentUser['nom']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Prénom</div>
                        <div class="info-value"><?= htmlspecialchars($currentUser['prenom']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($currentUser['email']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Rôle</div>
                        <div class="info-value"><?= htmlspecialchars($role) ?></div>
                    </div>
                </div>
            </div>
            
            <div class="password-section">
                <h2>Changer mon mot de passe</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    <button type="submit" name="change_password" class="btn">Mettre à jour le mot de passe</button>
                </form>
            </div>
        </div>
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

        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Déconnexion ?')) {
                window.location.href = 'authentification.php';
            }
        });
    </script>
</body>
</html>