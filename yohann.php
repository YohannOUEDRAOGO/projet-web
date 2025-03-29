<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'projet-web'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur de la base de données
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Données de l'utilisateur à ajouter
    $userEmail = 'yohannodg@gmail.com';
    $userPassword = 'yohann123456789';
    
    // Hachage sécurisé du mot de passe
    $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);
    
    // Préparation de la requête
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, password) VALUES (?, ?)");
    $stmt->execute([$userEmail, $hashedPassword]);
    
    echo "Utilisateur ajouté avec succès!";
    
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>