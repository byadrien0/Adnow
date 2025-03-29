<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';


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

// Function to generate XML response
function generateXmlResponse($status, $message = '', $data = [])
{
    $xml = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
    $xml->addAttribute('status', $status);
    if (!empty($message)) {
        $xml->addAttribute('message', $message);
    }
    if (!empty($data)) {
        $campaignsXml = $xml->addChild('campaigns');
        foreach ($data as $campaign) {
            $campaignXml = $campaignsXml->addChild('campaign');
            foreach ($campaign as $key => $value) {
                $campaignXml->addChild($key, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
            }
        }
    }
    header('Content-Type: text/xml');
    echo $xml->asXML();
    exit;
}

// Function to handle SQL query errors
function handleSqlError($con)
{
    generateXmlResponse('error', 'Une erreur SQL est survenue.');
}

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
    $server_id = verifyTokenDiscord($con, $token);

    if (empty($server_id)) {
        generateXmlResponse('error', 'Serveur non trouvé ou token invalide.');
    }

    // Check if server exists in the servers_discord table
    $sql_check_server = "SELECT server_name FROM servers_discord WHERE id = ?";
    $stmt_check_server = $con->prepare($sql_check_server);
    if ($stmt_check_server === false) {
        handleSqlError($con);
    }
    $stmt_check_server->bind_param("i", $server_id);
    $stmt_check_server->execute();
    $result_check_server = $stmt_check_server->get_result();

    if ($result_check_server->num_rows == 0) {
        generateXmlResponse('error', 'Serveur non trouvé.');
    }

    // Check if server is subscribed to any campaign
    $sql_check_subscription = "SELECT campaigns_id FROM campaigns_diffusions WHERE server_id = ?";
    $stmt_check_subscription = $con->prepare($sql_check_subscription);
    if ($stmt_check_subscription === false) {
        handleSqlError($con);
    }
    $stmt_check_subscription->bind_param("i", $server_id);
    $stmt_check_subscription->execute();
    $result_check_subscription = $stmt_check_subscription->get_result();

    $campaigns = [];

    // Process each campaign
    while ($row = $result_check_subscription->fetch_assoc()) {
        $campaign_id = $row['campaigns_id'];

        // Fetch campaign details
        $sql_campaign_details = "
            SELECT 
                c.campaign_id,
                c.nom AS campaign_name,
                c.logo_url,
                c.link_to,
                c.link_code,
                COALESCE(impression_count.impressions_total, 0) AS impressions_realisees,
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
                c.chat_msg_discord,
                c.diff_per_hour
            FROM 
                campaigns c
                LEFT JOIN (
                    SELECT campaign_id, COUNT(*) AS impressions_total
                    FROM campaigns_impression
                    WHERE server_id = ?
                    GROUP BY campaign_id
                ) AS impression_count ON c.campaign_id = impression_count.campaign_id
            WHERE 
                c.campaign_id = ?
            LIMIT 1;
        ";

        $stmt_campaign_details = $con->prepare($sql_campaign_details);
        if ($stmt_campaign_details === false) {
            handleSqlError($con);
        }
        $stmt_campaign_details->bind_param("ii", $server_id, $campaign_id);
        $stmt_campaign_details->execute();
        $result_campaign_details = $stmt_campaign_details->get_result();

        if ($result_campaign_details->num_rows > 0) {
            while ($campaign = $result_campaign_details->fetch_assoc()) {
                // Calculate the minimum interval between diffusions
                $diff_per_hour = $campaign['diff_per_hour'];
                $interval_minutes = 60 / $diff_per_hour; // Interval in minutes

                // Calculate the time since the last diffusion
                $current_time = new DateTime();
                $sql_last_diffusion = "SELECT created_at FROM campaigns_messages WHERE campaign_id = ? AND server_id = ? AND app = 'discord' ORDER BY created_at DESC LIMIT 1";
                $stmt_last_diffusion = $con->prepare($sql_last_diffusion);
                if ($stmt_last_diffusion === false) {
                    handleSqlError($con);
                }
                $stmt_last_diffusion->bind_param("ii", $campaign_id, $server_id);
                $stmt_last_diffusion->execute();
                $result_last_diffusion = $stmt_last_diffusion->get_result();

                $last_diffusion_time = null;
                if ($result_last_diffusion->num_rows > 0) {
                    $row_last_diffusion = $result_last_diffusion->fetch_assoc();
                    $last_diffusion_time = new DateTime($row_last_diffusion['created_at']);
                }

                if ($last_diffusion_time !== null) {
                    $time_since_last_diffusion = $current_time->diff($last_diffusion_time);
                    $minutes_since_last_diffusion = ($time_since_last_diffusion->days * 24 * 60) + ($time_since_last_diffusion->h * 60) + $time_since_last_diffusion->i;

                    if ($minutes_since_last_diffusion < $interval_minutes) {
                        continue; // Skip to the next campaign
                    }
                }

                // Insert into campaigns_messages
                if (!empty($campaign_id) && $campaign['campagne_info'] === 'start') {
                    $insertStmt = $con->prepare("INSERT INTO campaigns_messages (campaign_id, server_id, app) VALUES (?, ?, 'discord')");
                    if ($insertStmt === false) {
                        handleSqlError($con);
                    }
                    $insertStmt->bind_param("ii", $campaign_id, $server_id);
                    $insertStmt->execute();
                    $insertStmt->close();
                }

                // Add campaign to array
                $campaigns[] = [
                    'campaign_id' => $campaign["campaign_id"],
                    'server_id' => $server_id,
                    'server_name' => $result_check_server->fetch_assoc()["server_name"],
                    'campagne_info' => $campaign["campagne_info"],
                    'days_remaining' => $campaign["days_remaining"],
                    'impressions_restantes' => $campaign["impressions_restantes"],
                    'chat_msg_discord' => $campaign["chat_msg_discord"],
                    'link_to' => $campaign["link_to"],
                    'link_code' => $campaign["link_code"],
                    'validation_status' => 'valide'
                ];
            }
        }
    }

    if (empty($campaigns)) {
        // No campaigns available
        generateXmlResponse('no-campaigns', 'Aucune campagne disponible.');
    } else {
        // Output campaigns
        generateXmlResponse('success', '', $campaigns);
    }

    // Close database connections
    $stmt_check_server->close();
    $stmt_check_subscription->close();
    $con->close();

} catch (Exception $e) {
    // Handle unexpected errors
    generateXmlResponse('error', 'Une erreur inattendue est survenue : ' . htmlspecialchars($e->getMessage(), ENT_XML1, 'UTF-8'));
}

?>