<?php

function verifyTokenMinecraft($con, $token)
{
    $stmt = $con->prepare("SELECT id FROM servers_minecraft WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($server_id_minecraft);
    $stmt->fetch();
    $stmt->close();
    return $server_id_minecraft;
}

// Fonction pour convertir un tableau en XML
function array_to_xml($data, &$xml_data)
{
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if (is_numeric($key)) {
                $key = 'item' . $key; // S'assurer que les clés numériques sont traitées correctement
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key", htmlspecialchars("$value"));
        }
    }
}

function getServerInfo($con, $token)
{
    // Préparer et exécuter la requête pour récupérer les adresses IP et les ports
    $sql_servers = "SELECT adresse_ip, port FROM servers_minecraft WHERE token = ?";
    $stmt_servers = $con->prepare($sql_servers);
    $stmt_servers->bind_param('s', $token);
    $stmt_servers->execute();
    $result_servers = $stmt_servers->get_result();

    $serversInfo = []; // Tableau pour stocker les informations des serveurs

    if ($result_servers->num_rows > 0) {
        while ($row_servers = $result_servers->fetch_assoc()) {
            $host = $row_servers['adresse_ip'];
            $port = $row_servers['port'];

            // Vérifie si le port est défini et non nul
            if (!empty($port)) {
                // Si le port est défini, on l'ajoute à l'URL avec les deux points
                $url = "https://api.mcsrvstat.us/2/$host:$port";
            } else {
                // Si le port n'est pas défini, on n'ajoute pas les deux points
                $url = "https://api.mcsrvstat.us/2/$host";
            }

            // Initialiser cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // Exécuter la requête cURL
            $response = curl_exec($ch);

            // Vérifier les erreurs
            if ($response === FALSE) {
                echo 'Erreur cURL pour le serveur ' . htmlspecialchars($host) . ':' . htmlspecialchars($port) . ' : ' . curl_error($ch) . '<br>';
                curl_close($ch);
                continue;
            }

            // Fermer cURL
            curl_close($ch);

            // Convertir la réponse JSON en tableau PHP
            $data = json_decode($response, true);

            // Initialiser les valeurs par défaut
            $playersOnline = 0;
            $serverVersion = 'N/A';
            $serverStatus = 'Offline';

            // Vérifier si la réponse JSON est valide
            if ($data && isset($data['online'])) {
                if ($data['online']) {
                    $playersOnline = $data['players']['online'] ?? 0;
                    $serverVersion = $data['version'] ?? 'N/A';
                    $serverStatus = 'Online';
                }
            }

            // Ajouter les informations du serveur dans le tableau
            $serversInfo[] = [
                'host' => $host,
                'port' => $port,
                'status' => $serverStatus,
                'playersOnline' => $playersOnline,
                'serverVersion' => $serverVersion
            ];
        }
    }

    // Fermer les requêtes
    $stmt_servers->close();

    // Retourner les informations sous forme de tableau associatif
    return $serversInfo;
}



?>