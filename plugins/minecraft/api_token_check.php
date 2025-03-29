<?php



// Inclusion des fichiers de configuration et des fonctions API
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/plugins/minecraft/api_functions.php';



// Fonction pour générer une réponse XML
function generateXmlResponse($status, $message)
{
    $xml = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    $xml->addChild('status', htmlspecialchars($status, ENT_QUOTES, 'UTF-8'));
    $xml->addChild('message', htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    header('Content-Type: application/xml');
    echo $xml->asXML();
    exit;
}

// Vérifier si le token et le jeu sont fournis
if (empty($_GET['token'])) {
    generateXmlResponse('error', 'Token non fourni.');
}
if (empty($_GET['games'])) {
    generateXmlResponse('error', 'Jeu non fourni.');
}

$token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');
$games = htmlspecialchars($_GET['games'], ENT_QUOTES, 'UTF-8');

// Connexion à la base de données
if (!isset($con)) {
    generateXmlResponse('error', 'Connexion à la base de données échouée.');
}

// Gestion pour Minecraft
if ($games == 1) {
    $server_id_minecraft = verifyTokenMinecraft($con, $token);

    if ($server_id_minecraft) {
        // Obtenir et vérifier les paramètres Minecraft
        $serverAddress = isset($_GET['ip']) ? htmlspecialchars($_GET['ip'], ENT_QUOTES, 'UTF-8') : null;
        $serverPort = isset($_GET['port']) ? intval($_GET['port']) : null;
        $serverVersion = isset($_GET['serverVersion']) ? htmlspecialchars($_GET['serverVersion'], ENT_QUOTES, 'UTF-8') : null;
        $pluginVersion = isset($_GET['pluginVersion']) ? htmlspecialchars($_GET['pluginVersion'], ENT_QUOTES, 'UTF-8') : null;
        $games = isset($_GET['games']) ? intval(htmlspecialchars($_GET['games'], ENT_QUOTES, 'UTF-8')) : null;

        if ($serverAddress && $serverPort && $serverVersion && $pluginVersion) {
            $imageName = fetchMinecraftServerLogo($serverAddress, $serverPort, $outputDirectory);
            $directoryImage = "/dashboard/img/servers/" . $imageName;

            $stmt = $con->prepare("UPDATE servers SET adresse_ip = ?, port = ?, server_version = ?, plugin_version = ?, logo_url =
?, games = ? WHERE id = ?");
            $stmt->bind_param(
                'sisssii',
                $serverAddress,
                $serverPort,
                $serverVersion,
                $pluginVersion,
                $directoryImage,
                $games,
                $server_id_minecraft
            );
            $stmt->execute();
            $stmt->close();
        }

        // Vérifier et mettre à jour activate_date
        $stmt = $con->prepare("SELECT activate_date FROM servers WHERE id = ?");
        $stmt->bind_param('i', $server_id_minecraft);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($activate_date);
        $stmt->fetch();
        $stmt->close();

        if (is_null($activate_date)) {
            $current_datetime = date('Y-m-d H:i:s');
            $stmt = $con->prepare("UPDATE servers SET activate_date = ? WHERE id = ?");
            $stmt->bind_param('si', $current_datetime, $server_id_minecraft);
            $stmt->execute();
            $stmt->close();
        }

        generateXmlResponse('success', 'Token est valide pour Minecraft.');
    } else {
        generateXmlResponse('error', 'Token invalide pour Minecraft.');
    }
}


// Fermer la connexion à la base de données
$con->close();

?>