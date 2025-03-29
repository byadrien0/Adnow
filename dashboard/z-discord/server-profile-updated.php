<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit(); // Arrêter l'exécution du script
}


// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serverId'], $_POST['serverCategory'], $_POST['serverWebsite'])) {
    // Récupération des données du formulaire
    $serverId = $_POST['serverId'];
    $serverCategory = $_POST['serverCategory'];
    $serverWebsite = $_POST['serverWebsite'];

    // Préparation de la requête SQL pour la mise à jour
    $sql = "UPDATE servers_discord SET category = ?, website = ? WHERE id = ?";

    // Préparation de la déclaration
    $stmt = $con->prepare($sql);

    // Vérification de la préparation
    if ($stmt === false) {
        addNotification($con, $user_id, "Erreur de préparation de la déclaration: " . $con->error, "Erreur", $no_connect_user_id, $no_connect_ip);
        die("Erreur de préparation de la déclaration: " . $con->error);
    }

    // Définition des valeurs pour la déclaration
    $stmt->bind_param("ssi", $serverCategory, $serverWebsite, $serverId);

    // Exécution de la déclaration
    if ($stmt->execute()) {
        addNotification($con, $user_id, "Les données ont été mises à jour avec succès.", "Succès", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/account-server-discord-profile.php?id=$serverId");
        exit();
    } else {
        addNotification($con, $user_id, "Erreur lors de la mise à jour des données: " . $stmt->error, "Erreur", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/account-server-discord-profile.php?id=$serverId");
        exit();
    }

    // Fermeture de la déclaration
    $stmt->close();
} else {
    addNotification($con, $user_id, "Les données du formulaire ne sont pas complètes.", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/account-server-discord-profile.php?id=$serverId");
    exit();
}

// Fermeture de la connexion
$con->close();
?>