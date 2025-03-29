<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'projet-web'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur de la base de données
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Vérification des données du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        header('Location: authentification.php ?error=missing_fields');
        exit();
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Recherche de l'utilisateur dans la base de données
    $stmt = $pdo->prepare("SELECT id, password FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        
        // Redirection vers la page d'accueil ou tableau de bord
        header('Location: entreprise.php');
        exit();
    } else {
        // Identifiants incorrects
        header('Location: authentification.php?error=invalid_credentials');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - LEBONPLAN</title>
    <link href="style/style_authentification.css" rel="stylesheet"/>
</head>
<body>

    <header>
        <img src="image/logo-lbp-header.png" alt="Lebonplan - Le meilleur site de recherche de stage" class="logo">
    </header>

    <div class="form-container">
        <h1>Bienvenue sur Lebonplan</h1>
        <p>Ne perdez plus de temps à chercher un stage : avec Lebonplan, accédez aux meilleures offres rapidement et efficacement !</p>

        <h2>Identification</h2>
        <form action="authentification.php" method="POST">
            <label for="email">Email</label>
            <input name="email" type="email" placeholder="Entrez votre email" required>

            <label for="password">Mot de passe</label>
            <input name="password" type="password" placeholder="Mot de passe (8 caractères minimum)" minlength="8" required>

            <button type="submit">Je me connecte</button>
        </form>

        <div id="error-message" style="color: red; margin-top: 10px;"></div>

        <p>En cas de problème, veuillez contacter  
            <a href="mailto:Yohannodg@gmail.com">l'administrateur</a>
        </p>
    </div>

    <script>
        // Gestion des erreurs côté client
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        if (error) {
            document.getElementById('error-message').textContent = 
                error === 'invalid_credentials' ? 'Email ou mot de passe incorrect' :
                error === 'missing_fields' ? 'Veuillez remplir tous les champs' :
                'Une erreur est survenue';
        }
    </script>
</body>
</html>