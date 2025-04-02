<?php
ini_set('session.gc_maxlifetime', 10800); // 3 heures en secondes
ini_set('session.cookie_lifetime', 10800);
session_start();

// Vérifier l'inactivité
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 10800)) {
    // Session expirée
    session_unset();
    session_destroy();
    header('Location: authentification.php?error=session_expired');
    exit();
}

// Protection contre les attaques de fixation de session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}



// Configuration de la base de données
$host = 'localhost';
$dbname = 'gestion';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Une erreur technique est survenue. Veuillez réessayer plus tard.");
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        die("Token de sécurité invalide");
    }

    // Validation des entrées
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: authentification.php?error=missing_fields');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: authentification.php?error=invalid_email');
        exit();
    }

    // Recherche de l'utilisateur
    try {
        $stmt = $pdo->prepare("SELECT id, password, nom, prenom, role, actif FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Vérification compte actif
            if (!$user['actif']) {
                header('Location: authentification.php?error=account_inactive');
                exit();
            }

            // Vérification mot de passe
            if (password_verify($password, $user['password'])) {
                // Connexion réussie
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $email,
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'role' => $user['role'],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'login_time' => time() 
                ];

                // Régénération de session
                session_regenerate_id(true);

                // Mise à jour dernière connexion
                $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?")
                    ->execute([$user['id']]);

                // Redirection sécurisée
                header('Location: candidature.php');
                exit();
            }
        }

        // Échec connexion
        sleep(rand(1, 3)); // Délai aléatoire contre brute force
        header('Location: authentification.php?error=invalid_credentials');
        exit();

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: authentification.php?error=technical_error');
        exit();
    }
}

// Affichage du formulaire si méthode GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - LEBONPLAN</title>
    <link href="style/style_authentification.css" rel="stylesheet">
    <link rel="icon" href="favicon.ico">
</head>
<body>
    <header>
        <img src="image/logo-lbp-header.png" alt="Lebonplan" class="logo">
    </header>

    <div class="form-container">
        <h1>Bienvenue sur Lebonplan</h1>
        <p>Ne perdez plus de temps à chercher un stage : avec Lebonplan, accédez aux meilleures offres rapidement et efficacement !</p>

        <h2>Identification</h2>
        <form action="authentification.php" method="POST" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <label for="email">Email</label>
            <input name="email" type="email" placeholder="Entrez votre email" required autofocus>
            
            <label for="password">Mot de passe</label>
            <input name="password" type="password" placeholder="Mot de passe (8 caractères minimum)" minlength="8" required>
            
            <button type="submit">Je me connecte</button>
        </form>

        <div id="error-message">
            <?php
            if (isset($_GET['error'])) {
                $messages = [
                    'invalid_credentials' => 'Email ou mot de passe incorrect',
                    'missing_fields' => 'Veuillez remplir tous les champs',
                    'invalid_email' => 'Email invalide',
                    'account_inactive' => 'Compte désactivé',
                    'technical_error' => 'Erreur technique, veuillez réessayer'
                ];
                echo '<p class="error">' . ($messages[$_GET['error']] ?? 'Une erreur est survenue') . '</p>';
            }
            ?>
        </div>

        <p class="help-text">
            <a href="motdepasse-oublie.php">Mot de passe oublié ?</a> | 
            <a href="mailto:Yohannodg@gmail.com">Contact administrateur</a>
        </p>
    </div>

    <script>
        // Focus automatique sur le premier champ erreur
        document.addEventListener('DOMContentLoaded', function() {
            const error = new URLSearchParams(window.location.search).get('error');
            if (error) {
                const field = error === 'invalid_email' ? 'email' : 
                            error === 'missing_fields' ? 
                                (document.querySelector('input[name="email"]').value ? 'password' : 'email') : null;
                if (field) document.querySelector(`input[name="${field}"]`).focus();
            }
        });
    </script>
</body>
</html>
<?php } ?>