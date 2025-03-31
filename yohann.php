<?php
// Configuration
$host = 'localhost';
$dbname = 'projet-web';
$username = 'root';
$password = '';

// Données étudiant
$userEmail = 'stephkengne17@gmail.comg';
$userPassword = '12345678';
$userNom = 'DUPONT';
$userPrenom = 'Jean';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Vérification email
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $check->execute([$userEmail]);
    
    if ($check->fetch()) {
        die("Email existe déjà");
    }

    // Insertion
    $stmt = $pdo->prepare("INSERT INTO utilisateurs 
                          (email, password, nom, prenom, role, actif) 
                          VALUES (?, ?, ?, ?, 'pilote', 1)");
    $stmt->execute([
        $userEmail,
        password_hash($userPassword, PASSWORD_DEFAULT),
        $userNom,
        $userPrenom
    ]);

    echo "Étudiant ajouté avec rôle 'etudiant'";
    
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>