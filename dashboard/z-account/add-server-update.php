<?php
// Inclusion du fichier de configuration
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Fonction pour générer un identifiant unique de 60 caractères
function generateUniqueId()
{
    return bin2hex(random_bytes(30)); // Génère une chaîne de 60 caractères hexadécimaux
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Générer le token et l'id_v2
    $token = generateRandomToken();
    $id_v2 = generateUniqueId();

    // Assainir les données d'entrée
    $name = isset($_POST["inputServerName"]) ? trim($_POST["inputServerName"]) : '';
    $category = isset($_POST["inputServerCategory"]) ? trim($_POST["inputServerCategory"]) : '';
    $website = isset($_POST["inputServerWebsite"]) ? trim($_POST["inputServerWebsite"]) : '';
    $platform = isset($_POST["inputPlatform"]) ? trim($_POST["inputPlatform"]) : ''; // Récupérer la plateforme

    // Validation des données
    if (empty($name) || empty($website) || empty($category) || empty($platform)) {
        addNotification($con, $_SESSION['user_id'], "Veuillez remplir tous les champs correctement.", "Erreur");
        header("Location: /dashboard/z-account/add-server.php");
        exit();
    }

    // Vérifier combien de serveurs l'utilisateur a déjà ajoutés ce mois-ci pour Minecraft
    $currentMonth = date('Y-m'); // Format YYYY-MM
    if ($platform === 'minecraft') {
        if ($stmt = $con->prepare("SELECT COUNT(*) FROM servers_minecraft WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?")) {
            $stmt->bind_param("is", $_SESSION['user_id'], $currentMonth);
            $stmt->execute();
            $stmt->bind_result($serverCount);
            $stmt->fetch();
            $stmt->close();

            if ($serverCount >= 2) {
                addNotification($con, $_SESSION['user_id'], "Vous avez atteint le nombre maximum de serveurs autorisés pour ce mois.", "Erreur");
                header("Location: /dashboard/index.php");
                exit();
            }
        } else {
            addNotification($con, $_SESSION['user_id'], "Impossible de vérifier le nombre de serveurs ajoutés ce mois-ci.", "Erreur");
            header("Location: /dashboard/index.php");
            exit();
        }
    }

    // Vérifier combien de serveurs Discord l'utilisateur a déjà ajoutés ce mois-ci
    if ($platform === 'discord') {
        if ($stmt = $con->prepare("SELECT COUNT(*) FROM servers_discord WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?")) {
            $stmt->bind_param("is", $_SESSION['user_id'], $currentMonth);
            $stmt->execute();
            $stmt->bind_result($discordServerCount);
            $stmt->fetch();
            $stmt->close();

            if ($discordServerCount >= 20) {
                addNotification($con, $_SESSION['user_id'], "Vous avez atteint le nombre maximum de serveurs Discord autorisés pour ce mois.", "Erreur");
                header("Location: /dashboard/z-discord/server-list.php");
                exit();
            }
        } else {
            addNotification($con, $_SESSION['user_id'], "Impossible de vérifier le nombre de serveurs Discord ajoutés ce mois-ci.", "Erreur");
            header("Location: /dashboard/z-discord/server-list.php");
            exit();
        }
    }

    // Préparer l'insertion dans la table servers ou servers_discord
    if ($platform === 'discord') {
        if ($stmt = $con->prepare("INSERT INTO servers_discord (server_name, category, website, user_id, token, id_v2) VALUES (?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param("sssiss", $name, $category, $website, $_SESSION['user_id'], $token, $id_v2);
            if ($stmt->execute()) {
                // Notification de succès
                addNotification($con, $_SESSION['user_id'], "Le serveur Discord a été ajouté avec succès.", "Succès");
                header("Location: /dashboard/z-discord/server-list.php");
                exit();
            } else {
                addNotification($con, $_SESSION['user_id'], "Une erreur est survenue lors de l'ajout du serveur Discord.", "Erreur");
                header("Location: /dashboard/z-account/add-server.php");
                exit();
            }
            $stmt->close();
        } else {
            addNotification($con, $_SESSION['user_id'], "Impossible de préparer la requête pour Discord.", "Erreur");
            header("Location: /dashboard/z-account/add-server.php");
            exit();
        }
    } elseif ($platform === 'minecraft') {
        if ($stmt = $con->prepare("INSERT INTO servers_minecraft (nom, category, website, user_id, token, id_v2) VALUES (?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param("sssiss", $name, $category, $website, $_SESSION['user_id'], $token, $id_v2);
            if ($stmt->execute()) {
                // Notification de succès
                addNotification($con, $_SESSION['user_id'], "Le serveur Minecraft a été ajouté avec succès.", "Succès");
                header("Location: /dashboard/z-minecraft/server-list.php");
                exit();
            } else {
                addNotification($con, $_SESSION['user_id'], "Une erreur est survenue lors de l'ajout du serveur Minecraft.", "Erreur");
                header("Location: /dashboard/z-account/add-server.php");
                exit();
            }
            $stmt->close();
        } else {
            addNotification($con, $_SESSION['user_id'], "Impossible de préparer la requête pour Minecraft.", "Erreur");
            header("Location: /dashboard/z-account/add-server.php");
            exit();
        }
    }

    // Fermer la connexion à la base de données
    $con->close();
}
?>