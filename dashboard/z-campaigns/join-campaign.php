<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit();
}

// Vérifier si le paramètre 'app' est défini dans le GET et correspond à un type valide
if (isset($_GET['app']) && in_array($_GET['app'], ['minecraft', 'discord'])) {
    $app_type = $_GET['app'];
} else {
    addNotification($con, $user_id, "Le type d'application doit être spécifié", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/");
    exit();
}

// Vérifier si le paramètre 'server_id' est présent dans le GET
if (!isset($_GET['server_id'])) {
    addNotification($con, $user_id, "Un serveur spécifique doit être sélectionné pour ajouter la campagne.", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/");
    exit();
}
$server_id = (int) $_GET['server_id'];

// Vérifier si le serveur sélectionné appartient à l'utilisateur et correspond à l'application spécifiée
$sql_check_server = ($app_type === 'minecraft') ?
    "SELECT id FROM servers_minecraft WHERE id = ? AND user_id = ?" :
    "SELECT id FROM servers_discord WHERE id = ? AND user_id = ?";
$stmt_check_server = $con->prepare($sql_check_server);
$stmt_check_server->bind_param("ii", $server_id, $user_id);
$stmt_check_server->execute();
$stmt_check_server->store_result();

if ($stmt_check_server->num_rows === 0) {
    $stmt_check_server->close();
    addNotification($con, $user_id, "Le serveur sélectionné n'existe pas ou ne vous appartient pas. $server_id $user_id", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/");
    exit();
}
$stmt_check_server->close();

// Vérification de l'existence de la campagne
if (isset($_GET['campagne'])) {
    $campaign_id = htmlspecialchars($_GET['campagne']);

    $verif = "
        SELECT c.campaign_id, c.date_debut, c.impression_total, COALESCE(SUM(d.diffusion_id), 0) AS impressions_realisees, c.users_id AS creator_id
        FROM campaigns c
        LEFT JOIN campaigns_diffusions d ON c.campaign_id = d.campaigns_id
        WHERE c.campaign_id = ?
    ";
    $find_campaign = $con->prepare($verif);
    $find_campaign->bind_param("i", $campaign_id);
    $find_campaign->execute();
    $find_campaign->store_result();

    if ($find_campaign->num_rows === 0) {
        $find_campaign->close();
        addNotification($con, $user_id, "Aucune correspondance n'a été trouvée.", "Erreur", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/");
        exit();
    }

    $find_campaign->bind_result($campaign_id, $date_debut, $impression_total, $impressions_realisees, $creator_id);
    $find_campaign->fetch();
    $find_campaign->close();

    if ($creator_id == $user_id) {
        addNotification($con, $user_id, "Vous ne pouvez pas rejoindre votre propre campagne.", "Erreur", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/");
        exit();
    }

    if (!$date_debut || strtotime($date_debut) > time()) {
        addNotification($con, $user_id, "La campagne n'existe pas ou n'a pas encore commencé.", "Erreur", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/");
        exit();
    }

    $impressions_restantes = $impression_total - $impressions_realisees;
    if ($impressions_restantes <= 0) {
        addNotification($con, $user_id, "La campagne est terminée, vous ne pouvez pas la rejoindre.", "Erreur", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/");
        exit();
    }

    // Vérification si la campagne est déjà associée au serveur sélectionné
    $sql_check_existing = "SELECT diffusion_id FROM campaigns_diffusions WHERE campaigns_id = ? AND server_id = ? AND app = ?";
    $stmt_check_existing = $con->prepare($sql_check_existing);
    $stmt_check_existing->bind_param("iis", $campaign_id, $server_id, $app_type);
    $stmt_check_existing->execute();
    $stmt_check_existing->store_result();

    if ($stmt_check_existing->num_rows > 0) {
        $stmt_check_existing->close();
        addNotification($con, $user_id, "La campagne est déjà associée à ce serveur.", "Erreur", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/");
        exit();
    }
    $stmt_check_existing->close();

    // Insérer la campagne pour le serveur sélectionné
    $sql_insert = "INSERT INTO campaigns_diffusions (campaigns_id, server_id, app) VALUES (?, ?, ?)";
    $stmt_insert = $con->prepare($sql_insert);
    $stmt_insert->bind_param("iis", $campaign_id, $server_id, $app_type);

    if ($stmt_insert->execute()) {
        addNotification($con, $user_id, "La campagne a été ajoutée au serveur sélectionné.", "Succès", $no_connect_user_id, $no_connect_ip);
    } else {
        addNotification($con, $user_id, "Erreur lors de l'ajout de la campagne au serveur sélectionné.", "Erreur", $no_connect_user_id, $no_connect_ip);
    }
    $stmt_insert->close();

    header("Location: /dashboard/");
    exit();
} else {
    addNotification($con, $user_id, "Aucune correspondance n'a été trouvée.", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/");
    exit();
}

$con->close();
?>