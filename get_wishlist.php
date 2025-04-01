<?php
session_start();
require_once 'check_session.php';
verifySession();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'etudiant') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

require_once 'db_connection.php';

$studentId = $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare("SELECT offre_id FROM wishlist WHERE etudiant_id = ?");
    $stmt->execute([$studentId]);
    $wishlist = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($wishlist);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}