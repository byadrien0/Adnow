<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

try {
    // Vérifier si le paramètre GET "code" est défini et le valider
    if (isset($_GET['code'])) {
        // Nettoyer le paramètre 'code' pour éviter les injections XSS
        $code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Vérifier si le paramètre GET "server_id" est défini et le valider (optionnel)
        $server_id = '';
        if (isset($_GET['server_id'])) {
            $server_id = filter_input(INPUT_GET, 'server_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        // Préparer et exécuter une requête pour récupérer le lien
        $stmt = $con->prepare("SELECT link_to FROM campaigns WHERE link_code = ?");
        if ($stmt === false) {
            throw new Exception('Erreur lors de la préparation de la requête.');
        }
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $link_to = $row['link_to'];

            // Enregistrer l'adresse IP de l'utilisateur et le code
            $user_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
            if ($user_ip === false) {
                throw new Exception('Adresse IP invalide.');
            }
            $click_time = date("Y-m-d H:i:s");

            // Préparer une requête pour vérifier les clics récents avec le même code et server_id
            $query = "SELECT id FROM campaigns_clicks WHERE link_code = ? AND user_ip = ? AND click_time >= (NOW() - INTERVAL 15 MINUTE)";
            if (!empty($server_id)) {
                $query .= " AND server_id = ?";
            }

            $stmt_check = $con->prepare($query);
            if ($stmt_check === false) {
                throw new Exception('Erreur lors de la préparation de la requête de vérification.');
            }

            // Bind parameters conditionally based on the presence of server_id
            if (!empty($server_id)) {
                $stmt_check->bind_param("sss", $code, $user_ip, $server_id);
            } else {
                $stmt_check->bind_param("ss", $code, $user_ip);
            }

            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows == 0) {
                // Préparer l'insertion avec ou sans server_id
                if (!empty($server_id)) {
                    $stmt_insert = $con->prepare("INSERT INTO campaigns_clicks (link_code, user_ip, click_time, server_id) VALUES (?, ?, ?, ?)");
                    if ($stmt_insert === false) {
                        throw new Exception('Erreur lors de la préparation de la requête d\'insertion.');
                    }
                    $stmt_insert->bind_param("ssss", $code, $user_ip, $click_time, $server_id);
                } else {
                    $stmt_insert = $con->prepare("INSERT INTO campaigns_clicks (link_code, user_ip, click_time) VALUES (?, ?, ?)");
                    if ($stmt_insert === false) {
                        throw new Exception('Erreur lors de la préparation de la requête d\'insertion.');
                    }
                    $stmt_insert->bind_param("sss", $code, $user_ip, $click_time);
                }
                $stmt_insert->execute();
                $stmt_insert->close();
            }

            // Rediriger l'utilisateur vers le lien trouvé
            $redirect_url = htmlspecialchars($link_to, ENT_QUOTES, 'UTF-8');

            header("Location: " . $redirect_url);
            exit;
        } else {
            header("Location: /index.php");
            exit();
        }

        // Fermer les statements préparés
        $stmt->close();
        $stmt_check->close();
    } else {
        header("Location: /index.php");
        exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: /error.php");
    exit();
} finally {
    // Fermer la connexion
    if (isset($con) && $con) {
        $con->close();
    }
}
?>