<?php
session_start();
require_once 'check_session.php';
verifySession();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

$offerId = $_POST['offer_id'] ?? null;
$action = $_POST['action'] ?? null;
$userId = $_SESSION['user']['id'];

if (!$offerId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

if ($action === 'add') {
    // Vérifier si l'offre est déjà dans la wishlist
    $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE etudiant_id = ? AND offre_id = ?");
    $stmt->execute([$userId, $offerId]);
    
    if ($stmt->rowCount() === 0) {
        $insert = $pdo->prepare("INSERT INTO wishlist (etudiant_id, offre_id) VALUES (?, ?)");
        $insert->execute([$userId, $offerId]);
    }
} elseif ($action === 'remove') {
    $delete = $pdo->prepare("DELETE FROM wishlist WHERE etudiant_id = ? AND offre_id = ?");
    $delete->execute([$userId, $offerId]);
}

echo json_encode(['success' => true]);
?>