<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/plugins/minecraft/api_functions.php';


// Vérifier que la méthode est GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Récupérer les données GET
    $token = isset($_GET['token']) ? $con->real_escape_string($_GET['token']) : '';
    $player_names = isset($_GET['player_names']) ? $_GET['player_names'] : '';
    $campaign_id = isset($_GET['campaign_id']) ? $_GET['campaign_id'] : '';

    function verifyTokenDiscord($con, $token)
    {
        $server_id_discord = null; // Initialize the variable to a default value
        $stmt = $con->prepare("SELECT id FROM servers_discord WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($server_id_discord);
        $stmt->fetch();
        $stmt->close();
        return $server_id_discord;
    }


    // Valider le token et obtenir l'ID du serveur
    $server_id = verifyTokenDiscord($con, $token);

    if (empty($server_id)) {
        // Token invalide ou serveur non trouvé
        echo 'error: Serveur non trouvé ou token invalide.';
        exit;
    }

    // Valider le token
    if ($server_id) {
        // Échapper les noms des joueurs pour éviter les problèmes avec les caractères spéciaux
        $player_names = htmlspecialchars($player_names, ENT_QUOTES, 'UTF-8');

        // Diviser les noms des joueurs en un tableau
        $players = explode(',', $player_names);

        // Préparer et exécuter les requêtes d'insertion
        foreach ($players as $player) {
            $player = trim($player);
            if (!empty($player)) {
                // Préparer la requête
                if ($stmt = $con->prepare('INSERT INTO campaigns_impression (pseudonyme, server_id, app, campaign_id) VALUES (?, ?, "discord", ?)')) {
                    $stmt->bind_param('sii', $player, $server_id, $campaign_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo 'error: Erreur lors de la préparation de la requête.';
                    exit;
                }
            }
        }

        // Répondre au client avec succès
        echo 'success: Les noms des joueurs ont été envoyés avec succès.';
    } else {
        // Token invalide
        echo 'error: Token invalide.';
    }
} else {
    // Méthode non autorisée
    http_response_code(405);
    echo 'error: Method not allowed';
}

// Fermer la connexion
$con->close();
?>