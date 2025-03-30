<?php
session_start();

function checkSessionTimeout() {
    $inactivity_limit = 10800; // 3 heures en secondes
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactivity_limit)) {
        // Session expirée
        session_unset();
        session_destroy();
        header('Location: authentification.php?error=session_expired');
        exit();
    }
    
    // Mettre à jour le timestamp à chaque activité
    $_SESSION['last_activity'] = time();
}

// Vérification supplémentaire de sécurité
function verifySession() {
    checkSessionTimeout();
    
    if (!isset($_SESSION['user'])) {
        header('Location: authentification.php');
        exit();
    }
    
    // Vérification de l'IP et du navigateur
    if ($_SESSION['user']['ip'] !== $_SERVER['REMOTE_ADDR'] || 
        $_SESSION['user']['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        header('Location: authentification.php?error=session_hijacked');
        exit();
    }
}
?>