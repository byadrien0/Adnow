<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/plugins/minecraft/api_functions.php';



// Function to generate XML response
function generateXmlResponse($status, $message = '')
{
    $xml = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    $xml->addAttribute('status', $status);
    if (!empty($message)) {
        $xml->addChild('message', htmlspecialchars($message, ENT_XML1, 'UTF-8'));
    }
    header('Content-Type: text/xml');
    echo $xml->asXML();
    exit;
}

// Function to handle SQL query errors
function handleSqlError($con)
{
    error_log('SQL Error: ' . $con->error);
    generateXmlResponse('error', 'Une erreur SQL est survenue.');
}

include_once "/var/www/html/adnow/plugins/api_functions.php";

try {
    // Check if token is provided
    if (!isset($_GET['token'])) {
        generateXmlResponse('error', 'Token non fourni.');
    }

    $token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');

    // Check for database connection error
    if ($con->connect_error) {
        generateXmlResponse('error', 'Échec de la connexion à la base de données.');
    }

    // Retrieve server ID
    $server_id = verifyTokenMinecraft($con, $token);

    if (empty($server_id)) {
        generateXmlResponse('error', 'Serveur non trouvé ou token invalide.');
    }

    // Check if server is subscribed to any campaign
    $sql_check_subscription = "SELECT COUNT(*) FROM campaigns_diffusions WHERE server_id = ?";
    $stmt_check_subscription = $con->prepare($sql_check_subscription);
    if ($stmt_check_subscription === false) {
        handleSqlError($con);
    }
    $stmt_check_subscription->bind_param("i", $server_id);
    $stmt_check_subscription->execute();
    $stmt_check_subscription->bind_result($subscription_count);
    $stmt_check_subscription->fetch();
    $stmt_check_subscription->close();

    if ($subscription_count == 0) {
        generateXmlResponse('no-campaigns', 'Le serveur n\'est pas inscrit à aucune campagne.');
    }

    // Fetch server info
    $serverInfo = getServerInfo($con, $token);

    // SQL query for campaigns
    $sql_campaigns = "
        SELECT 
            c.*, 
            COALESCE(impression_count.impressions_total, 0) AS impressions_realisees,
            s.id AS server_id,
            s.nom AS server_name,
            CASE 
                WHEN c.date_debut > NOW() THEN 'not start' 
                WHEN c.impression_total - COALESCE(impression_count.impressions_total, 0) <= 0 THEN 'finish'
                ELSE 'start' 
            END AS campagne_info,
            CASE 
                WHEN c.date_debut > NOW() THEN DATEDIFF(c.date_debut, NOW())  
                WHEN c.impression_total - COALESCE(impression_count.impressions_total, 0) <= 0 THEN -1  
                ELSE 0  
            END AS days_remaining,
            c.impression_total - COALESCE(impression_count.impressions_total, 0) AS impressions_restantes,
            c.chat_msg_minecraft,
            c.link_to,
            c.link_code,
            c.diff_per_hour,
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM campaigns_messages cm 
                    WHERE cm.campaign_id = c.campaign_id 
                    AND cm.server_id = s.id 
                    AND cm.created_at > NOW() - INTERVAL (60 / c.diff_per_hour) MINUTE
                ) THEN 'non valide'
                ELSE 'valide'
            END AS validation_status
        FROM 
            campaigns c
            LEFT JOIN (
                SELECT campaign_id, COUNT(*) AS impressions_total
                FROM campaigns_impression
                WHERE server_id = ?
                GROUP BY campaign_id
            ) AS impression_count ON c.campaign_id = impression_count.campaign_id
            LEFT JOIN servers_minecraft s ON s.id = ?
        WHERE 
            s.id = ?
            AND EXISTS (
                SELECT 1
                FROM campaigns_diffusions cd
                WHERE cd.campaigns_id = c.campaign_id
                AND cd.server_id = ?
            )
        GROUP BY 
            c.campaign_id  
        HAVING
            validation_status = 'valide'
        ORDER BY 
            FIELD(campagne_info, 'start', 'not start', 'finish'),
            days_remaining ASC,
            impressions_restantes ASC
        LIMIT 1;
    ";

    $stmt_campaigns = $con->prepare($sql_campaigns);
    if ($stmt_campaigns === false) {
        handleSqlError($con);
    }

    $stmt_campaigns->bind_param("iiii", $server_id, $server_id, $server_id, $server_id);
    $stmt_campaigns->execute();
    $result_campaigns = $stmt_campaigns->get_result();

    // Initialize XML document
    $xml = new SimpleXMLElement('<campaigns/>');

    // Process campaign data
    if ($result_campaigns->num_rows > 0) {
        while ($row_campaigns = $result_campaigns->fetch_assoc()) {
            $playersOnline = $serverInfo[0]['playersOnline'] ?? 0; // Default to 0 if not available
            if ($playersOnline === 'Information non disponible') {
                $playersOnline = 0;
            }

            if (is_numeric($playersOnline)) {
                $campaignId = $row_campaigns['campaign_id'];

                // Insert impressions into the table
                $insertSql = "INSERT INTO campaigns_impression (pseudonyme, server_id, campaign_id, app, created_at) VALUES ('', ?, ?, 'minecraft', NOW())";
                $insertStmt = $con->prepare($insertSql);
                if ($insertStmt === false) {
                    handleSqlError($con);
                }
                $insertStmt->bind_param("ii", $server_id, $campaignId);
                $insertStmt->execute();
                $insertStmt->close();

                // Insert into campaigns_messages
                $insertStmt = $con->prepare("INSERT INTO campaigns_messages (campaign_id, server_id, app) VALUES (?, ?, 'minecraft')");
                if ($insertStmt === false) {
                    handleSqlError($con);
                }
                $insertStmt->bind_param("ii", $campaignId, $server_id);
                $insertStmt->execute();
                $insertStmt->close();

                // Construct the URL
                $url = "https://adnow.online/?s=" . $row_campaigns["server_id"] . "&c=" . $row_campaigns["link_code"];
                $escaped_url = htmlspecialchars($url, ENT_XML1, 'UTF-8');

                // Add campaign to XML
                $campaign = $xml->addChild('campaign');
                $campaign->addChild('campaign_id', $row_campaigns["campaign_id"]);
                $campaign->addChild('server_id', $row_campaigns["server_id"]);
                $campaign->addChild('server_name', htmlspecialchars($row_campaigns["server_name"], ENT_XML1, 'UTF-8'));
                $campaign->addChild('campagne_info', $row_campaigns["campagne_info"]);
                $campaign->addChild('days_remaining', $row_campaigns["days_remaining"]);
                $campaign->addChild('validation_status', $row_campaigns["validation_status"]);
                $campaign->addChild('impressions_restantes', $row_campaigns["impressions_restantes"]);
                $campaign->addChild('link_to', $row_campaigns["link_to"]);
                $campaign->addChild('link_code', $row_campaigns["link_code"]);
                $campaign->addChild('chat_msg_minecraft', htmlspecialchars($row_campaigns["chat_msg_minecraft"], ENT_XML1, 'UTF-8') . " " . $escaped_url);
            } else {
                error_log("La valeur des impressions est invalide pour la campagne ID: " . $row_campaigns['campaign_id']);
            }
        }
    } else {
        // No campaigns available
        $xml->addAttribute('status', 'no-campaigns');
        $xml->addAttribute('message', 'Aucune campagne disponible.');
    }

    // Close database connections
    $stmt_campaigns->close();
    $con->close();

    // Output XML
    header('Content-Type: text/xml');
    echo $xml->asXML();

} catch (Exception $e) {
    // Handle unexpected errors
    generateXmlResponse('error', 'Une erreur inattendue est survenue : ' . htmlspecialchars($e->getMessage(), ENT_XML1, 'UTF-8'));
}
?>