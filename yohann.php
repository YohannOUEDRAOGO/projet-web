<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'projet-web';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Données de l'utilisateur admin à ajouter
    $userEmail = 'stephkengne17@gmail.com';
    $userPassword = 'stephane12345678';
    $userNom = 'KENGNE';
    $userPrenom = 'Stephane';
    
    // Vérification que l'email n'existe pas déjà
    $checkStmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $checkStmt->execute([$userEmail]);
    
    if ($checkStmt->fetch()) {
        die("Erreur: Cet email est déjà utilisé.");
    }
    
    // Hachage sécurisé du mot de passe
    $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);
    
    // Préparation et exécution de la requête
    $stmt = $pdo->prepare("INSERT INTO utilisateurs 
                          (email, password, nom, prenom, role, actif, date_creation) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())");
    
    $stmt->execute([
        $userEmail,
        $hashedPassword,
        $userNom,
        $userPrenom,
        'admin',  // Rôle admin
        true      // Compte actif
    ]);
    
    echo "Utilisateur admin ajouté avec succès!";
    
} catch (PDOException $e) {
    error_log("Erreur d'insertion utilisateur: " . $e->getMessage());
    die("Une erreur technique est survenue. Veuillez réessayer plus tard.");
}
?>