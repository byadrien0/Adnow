<?php
// Inclure le paramètre de la session et du site

include $_SERVER['DOCUMENT_ROOT'] . '/includes/session_config.php';

include $_SERVER['DOCUMENT_ROOT'] . '/includes/database.php';

// Génération d'un token CSRF pour protéger contre les attaques CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

$user_id = $_SESSION['user_id'] ?? NULL;

include $_SERVER['DOCUMENT_ROOT'] . '/includes/function.php';

// Gérer les utilisateurs non connectés
$non_connected_user = handleNonConnectedUser();
$no_connect_user_id = $non_connected_user['no_connect_user_id'];
$no_connect_ip = $non_connected_user['no_connect_ip'];

// Récupérer les informations utilisateur
$user_info = getAccountUserInfo($con);

if ($user_info !== null) {
    extract($user_info); // Extrait les variables à partir du tableau associatif
}

// Vérifier le parrainage et insérer dans la table affiliates si nécessaire
checkAndInsertParrainage($con);


// Récupère les notifications non lues pour l'utilisateur
$notifications = getUnreadNotifications($con, $user_id, $no_connect_user_id, $no_connect_ip);

// Définir la fonction de gestion des erreurs personnalisée
set_error_handler("errorHandler");

// Activer le rapport d'erreurs pour les erreurs restantes
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


?>