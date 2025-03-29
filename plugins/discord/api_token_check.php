<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Fonction pour générer une réponse XML
function generateXmlResponse($status, $message, $icon_url = null, $name = null)
{
    $xml = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
$xml->addChild('status', htmlspecialchars($status, ENT_QUOTES, 'UTF-8'));
$xml->addChild('message', htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

// Ajouter l'URL de l'icône et le nom si disponibles
if ($icon_url !== null) {
$xml->addChild('icon_url', htmlspecialchars($icon_url, ENT_QUOTES, 'UTF-8'));
}
if ($name !== null) {
$xml->addChild('name', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
}

header('Content-Type: application/xml');
echo $xml->asXML();
exit;
}

// Vérifier si le token est fourni
if (empty($_GET['token'])) {
generateXmlResponse('error', 'Token non fourni.');
}

// Vérifier si le guildId est fourni
if (empty($_GET['guildId'])) {
generateXmlResponse('error', 'GuildID non fourni.');
}

$token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');
$guildId = htmlspecialchars($_GET['guildId'], ENT_QUOTES, 'UTF-8');

// Connexion à la base de données
if (!isset($con)) {
generateXmlResponse('error', 'Connexion à la base de données échouée.');
}

// Fonction pour vérifier le token
function verifyTokenDiscord($con, $token)
{
$server_id_discord = null;
$stmt = $con->prepare("SELECT id FROM servers_discord WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->bind_result($server_id_discord);
$stmt->fetch();
$stmt->close();
return $server_id_discord;
}

$bot_token = "DISCORD_BOT_TOKEN";

// Fonction pour obtenir l'URL du logo et le nom du serveur Discord
function fetchDiscordServerDetails($server_id, $bot_token)
{
// Utilisation de l'URL de base correcte de Discord
$url = "https://discord.com/api/v10/guilds/$server_id";
$headers = [
"Authorization: Bot $bot_token", // Token du bot
"Content-Type: application/json"
];

// Initialisation de cURL pour faire la requête API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// URL de l'image par défaut si aucune icône n'est trouvée
$default_logo_url = "https://archive.org/download/discordprofilepictures/discordblue.png";
$server_name = 'Nom non disponible';

// Si la requête est réussie
if ($http_code === 200) {
$data = json_decode($response, true);
// Déboguer la réponse API
error_log("API Discord response: " . print_r($data, true));

$server_name = isset($data['name']) ? $data['name'] : $server_name; // Récupérer le nom du serveur
if (isset($data['icon']) && !empty($data['icon'])) {
$icon_url = "https://cdn.discordapp.com/icons/$server_id/{$data['icon']}.png";
} else {
$icon_url = $default_logo_url;
}
return [$icon_url, $server_name];
}

// Gérer les erreurs et retourner les valeurs par défaut
return [$default_logo_url, $server_name];
}

// Vérifier le token
$server_id_discord = verifyTokenDiscord($con, $token);

if ($server_id_discord) {
// Vérifier si la colonne activate_date est NULL
$stmt = $con->prepare("SELECT activate_date FROM servers_discord WHERE id = ?");
$stmt->bind_param('i', $server_id_discord);
$stmt->execute();
$stmt->bind_result($activate_date);
$stmt->fetch();
$stmt->close();

if (is_null($activate_date)) {
// Récupérer l'URL du logo et le nom du serveur
list($icon_url, $server_name) = fetchDiscordServerDetails($guildId, $bot_token);

// Préparer la requête SQL pour la mise à jour
$stmt = $con->prepare("UPDATE servers_discord SET server_id = ?, icon_url = ?, server_name = ?, activate_date = NOW()
WHERE id = ?");
if (!$stmt) {
generateXmlResponse('error', 'Erreur lors de la préparation de la requête.');
}

// Lier les paramètres et exécuter la requête
$stmt->bind_param('sssi', $guildId, $icon_url, $server_name, $server_id_discord);

if ($stmt->execute()) {
// Enregistrement dans la base de données pour signaler la mise à jour
$stmt->close();
generateXmlResponse('success', 'Mise à jour réussie.', $icon_url, $server_name);
} else {
generateXmlResponse('error', 'Erreur lors de la mise à jour: ' . $stmt->error);
}
} else {
generateXmlResponse('info', 'Aucune mise à jour effectuée, activate_date n\'est pas NULL.');
}
} else {
generateXmlResponse('error', 'Token invalide pour Discord.');
}

// Fermer la connexion à la base de données
$con->close();

?>