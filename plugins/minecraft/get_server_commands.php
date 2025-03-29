<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/plugins/minecraft/api_functions.php';

// Récupérer le token du serveur depuis une requête GET ou POST
$token = isset($_GET['token']) ? $_GET['token'] : (isset($_POST['token']) ? $_POST['token'] : '');



if (empty($token)) {
    $response = array('status' => 'error', 'message' => 'Token du serveur manquant.');
    $xml_data = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    array_to_xml($response, $xml_data);
    header('Content-Type: text/xml');
    echo $xml_data->asXML();
    exit;
}

if ($con->connect_error) {
    $response = array('status' => 'error', 'message' => 'Failed to connect to database: ' . $con->connect_error);
    $xml_data = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    array_to_xml($response, $xml_data);
    header('Content-Type: text/xml');
    echo $xml_data->asXML();
    exit;
}

// Récupérer l'ID du serveur
$server_id = getServerIdByToken($con, $token);

if (empty($server_id)) {
    $response = array('status' => 'error', 'message' => 'Serveur non trouvé ou token invalide.');
    $xml_data = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    array_to_xml($response, $xml_data);
    header('Content-Type: text/xml');
    echo $xml_data->asXML();
    exit;
}

// Récupérer les commandes non exécutées pour le serveur spécifié
$sql = "SELECT id, command FROM commands WHERE executed = 0 AND server_id = ? ORDER BY command_order ASC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $server_id);
$stmt->execute();
$result = $stmt->get_result();

// Mettre à jour la colonne executed à 1 pour la commande récupérée
$updateSql = "UPDATE commands SET executed = 1 WHERE id = ?";
$updateStmt = $con->prepare($updateSql);

// Initialisation du document XML
$xml = new SimpleXMLElement('<commands/>');

// Récupérer les données de la base de données et les stocker dans un document XML
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $command = $xml->addChild('command', htmlspecialchars($row["command"]));

        // Mettre à jour la commande récupérée à executed = 1
        $commandId = $row["id"];
        $updateStmt->bind_param("i", $commandId);
        $updateStmt->execute();
    }
} else {
    // S'il n'y a pas de commandes disponibles, ajoute un commentaire indiquant qu'il n'y a pas de commandes
    $xml->addAttribute('status', 'no-commands');
    $xml->addAttribute('message', 'Aucune commande disponible.');
}

// Fermer les statements
$updateStmt->close();
$stmt->close();
$con->close();

// Renvoyer les commandes sous forme de réponse XML
header('Content-Type: text/xml');
echo $xml->asXML();
?>