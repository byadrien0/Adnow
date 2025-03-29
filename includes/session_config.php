<?php

header('Content-Type: text/html; charset=utf-8');

// Forcer HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Configuration de la session
$cookieParams = session_get_cookie_params();
$cookieParams['domain'] = 'adnow.online'; // Assurez-vous que le domaine est correct
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Désactiver les identifiants de session dans l'URL et activer uniquement les cookies pour les sessions
ini_set('session.use_only_cookies', 1);

// Démarrage sécurisé de la session
session_start([
    'cookie_lifetime' => 3600, // Durée de vie du cookie est de 1 heure
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

?>