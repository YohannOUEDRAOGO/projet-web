<?php
require_once 'check_session.php';
verifySession();

if (!isset($_SESSION['user'])) {
    header('Location: authentification.php');
    exit();
}

$currentUser = $_SESSION['user'];
$userId = $currentUser['id'];
$userRole = $currentUser['role'];
$offerId = $_POST['offer_id'] ?? null;
$action = $_POST['action'] ?? null;

// Validation des données
if (!is_numeric($userId) || !is_numeric($offerId) || !in_array($action, ['add', 'remove'])) {
    die(json_encode(['success' => false, 'message' => 'Données invalides']));
}

// Seuls les étudiants peuvent gérer une wishlist
if ($userRole !== 'etudiant') {
    die(json_encode(['success' => false, 'message' => 'Action non autorisée']));
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifiez que l'utilisateur existe et est bien un étudiant
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE id = ? AND role = 'etudiant'");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        throw new Exception("L'utilisateur avec l'ID $userId n'existe pas ou n'est pas un étudiant");
    }

    // Vérifiez que l'offre existe
    $stmt = $pdo->prepare("SELECT id FROM offres_stage WHERE id = ?");
    $stmt->execute([$offerId]);
    if (!$stmt->fetch()) {
        throw new Exception("L'offre avec l'ID $offerId n'existe pas");
    }

    if ($action === 'add') {
        // Vérifier d'abord si l'offre n'est pas déjà dans la wishlist
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND offre_id = ?");
        $stmt->execute([$userId, $offerId]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, offre_id, date_ajout) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $offerId]);
        }
    } else {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND offre_id = ?");
        $stmt->execute([$userId, $offerId]);
    }
    header('Location: candidature.php');
    exit;


} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}