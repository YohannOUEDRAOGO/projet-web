<?php
session_start();
require_once 'check_session.php';
verifySession();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'etudiant') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

require_once 'db_connection.php';

$offerId = $_POST['offer_id'] ?? null;
$action = $_POST['action'] ?? null;
$studentId = $_SESSION['user']['id'];

if (!$offerId || !in_array($action, ['add', 'remove'])) {
    header('HTTP/1.1 400 Bad Request');
    exit();
}

try {
    if ($action === 'add') {
        // Vérifier si l'offre est déjà dans la wishlist
        $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE etudiant_id = ? AND offre_id = ?");
        $stmt->execute([$studentId, $offerId]);
        
        if ($stmt->rowCount() === 0) {
            // Ajouter à la wishlist
            $insert = $pdo->prepare("INSERT INTO wishlist (etudiant_id, offre_id, date_ajout) VALUES (?, ?, NOW())");
            $insert->execute([$studentId, $offerId]);
        }
    } else {
        // Retirer de la wishlist
        $delete = $pdo->prepare("DELETE FROM wishlist WHERE etudiant_id = ? AND offre_id = ?");
        $delete->execute([$studentId, $offerId]);
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}