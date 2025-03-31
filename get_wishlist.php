<?php
session_start();
require_once 'check_session.php';
verifySession();

if (!isset($_SESSION['user'])) {
    echo json_encode([]);
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
    echo json_encode([]);
    exit;
}

$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT offre_id FROM wishlist WHERE etudiant_id = ?");
$stmt->execute([$userId]);

$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($results);
?>