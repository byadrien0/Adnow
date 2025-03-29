<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Check User-Agent
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (strpos($userAgent, 'Java/17.0.12') === false) {
    generateXmlResponse('error', 'Accès non autorisé. User-Agent non valide.');
}

// Vérification des paramètres reçus dans l'URL
if (isset($_GET['token']) && isset($_GET['finalScore'])) {
    $server_token = mysqli_real_escape_string($con, $_GET['token']);
    $total_score = (int) $_GET['finalScore'];

    // Préparation de la requête SQL pour insérer les données
    $sql = "INSERT INTO fake_player_risk (server_token, total_score, created_at) VALUES ('$server_token', $total_score, NOW())";

    // Exécution de la requête
    if (mysqli_query($con, $sql)) {
        echo "Données insérées avec succès.";
    } else {
        echo "Erreur lors de l'insertion des données : " . mysqli_error($con);
    }
} else {
    echo "Paramètres manquants.";
}

// Fermeture de la connexion
mysqli_close($con);
?>