<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/plugins/minecraft/api_functions.php';



// Fonction pour convertir un tableau en XML
function arrayToXml($data, &$xmlData)
{
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $key = is_numeric($key) ? 'item' . $key : $key; // Gestion des clés numériques
            $subnode = $xmlData->addChild($key);
            arrayToXml($value, $subnode);
        } else {
            $xmlData->addChild($key, htmlspecialchars($value));
        }
    }
}

// Définir le type de contenu en XML
header('Content-Type: application/xml; charset=utf-8');

// Récupérer les données envoyées par le plugin
$token = $_GET['token'] ?? '';
$ipAddress = $_GET['ipAddress'] ?? '';
$port = $_GET['port'] ?? '';
$serverVersion = $_GET['serverVersion'] ?? '';
$pluginVersion = $_GET['pluginVersion'] ?? '';

// Vérification des données
if (empty($token) || empty($ipAddress) || empty($port) || empty($serverVersion) || empty($pluginVersion)) {
    $response = ['status' => 'error', 'message' => 'Missing token, ipAddress, or port.'];
    $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response></response>');
    arrayToXml($response, $xmlData);
    echo $xmlData->asXML();
    exit;
}

// Initialiser la réponse par défaut
$response = ['status' => 'success', 'message' => 'Operation completed successfully.'];

try {
    if ($con->connect_error) {
        throw new Exception('Failed to connect to database: ' . $con->connect_error);
    }

    // Vérifier si le token existe
    $serverId = getServerIdByToken($con, $token);

    if ($serverId === NULL) {
        $response = ['status' => 'error', 'message' => 'Invalid token.'];
    } else {
        // Vérifier l'état du serveur
        $stmt = $con->prepare("SELECT adresse_ip, port, plugin_version, server_version, activate_date FROM servers_minecraft WHERE id = ?");
        $stmt->bind_param("i", $serverId);
        $stmt->execute();
        $result = $stmt->get_result();
        $server = $result->fetch_assoc();

        if (!$server) {
            throw new Exception('Fetch server failed: ' . $stmt->error);
        }

        // Vérifier si les champs sont déjà définis
        if (!empty($server['adresse_ip']) || !empty($server['port']) || !empty($server['activate_date']) || !empty($server['server_version']) || !empty($server['plugin_version'])) {
            $response = ['status' => 'error', 'message' => 'Server already set.'];
        } else {
            // Mettre à jour l'entrée correspondante avec les nouvelles valeurs
            $stmt = $con->prepare("UPDATE servers_minecraft SET adresse_ip = ?, port = ?, server_version = ?, plugin_version = ?, activate_date = NOW() WHERE id = ?");
            $stmt->bind_param("ssssi", $ipAddress, $port, $serverVersion, $pluginVersion, $serverId);
            $stmt->execute();

        }
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
}

// Convertir le tableau en format XML
$xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response></response>');
arrayToXml($response, $xmlData);
echo $xmlData->asXML();

// Fermer la connexion à la base de données
$con->close();
?>