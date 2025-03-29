<?php

// Assurez-vous que $con, $user_id, $no_connect_user_id, et $no_connect_ip sont définis dans la page principale

/**
 * Ajoute une notification pour un utilisateur.
 *
 * @param mysqli $con Connexion à la base de données.
 * @param int $user_id ID de l'utilisateur à qui la notification est destinée.
 * @param string $message Contenu de la notification.
 * @param string $type Type de notification (par exemple, 'Attention', 'Succès', 'Erreur', 'Email').
 * @param string|null $no_connect_user_id ID utilisateur non connecté (facultatif).
 * @param string|null $no_connect_ip Adresse IP de l'utilisateur non connecté (facultatif).
 * @return void
 */
function addNotification($con, $user_id, $message, $type, $no_connect_user_id = null, $no_connect_ip = null)
{
    // Vérifier qu'il n'y a pas déjà une notification identique
    $query = $con->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND message = ? AND no_connect_ip = ? AND no_connect_user_id = ? AND statut = ? AND view = 0");
    $query->bind_param('issss', $user_id, $message, $no_connect_ip, $no_connect_user_id, $type);
    $query->execute();
    $result = $query->get_result()->fetch_row()[0];

    // Ajouter la notification si aucune identique n'est trouvée
    if ($result == 0) {
        $query = $con->prepare("INSERT INTO notifications (user_id, no_connect_ip, no_connect_user_id, message, created_at, statut, view) VALUES (?, ?, ?, ?, NOW(), ?, 0)");
        $query->bind_param('issss', $user_id, $no_connect_ip, $no_connect_user_id, $message, $type);
        $query->execute();
    }
}

/**
 * Récupère les notifications non lues d'un utilisateur.
 *
 * @param mysqli $con Connexion à la base de données.
 * @param int $user_id ID de l'utilisateur.
 * @param string|null $no_connect_user_id ID utilisateur non connecté.
 * @param string|null $no_connect_ip Adresse IP de l'utilisateur non connecté.
 * @return array Notifications non lues.
 */
if (!function_exists('getUnreadNotifications')) {
    function getUnreadNotifications($con, $user_id, $no_connect_user_id, $no_connect_ip)
    {
        $query = $con->prepare("SELECT * FROM notifications WHERE (user_id = ? OR (no_connect_user_id = ? AND no_connect_ip = ?)) AND view = 0");
        $query->bind_param('iss', $user_id, $no_connect_user_id, $no_connect_ip);
        $query->execute();
        return $query->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

/**
 * Marque une notification comme lue.
 *
 * @param mysqli $con Connexion à la base de données.
 * @param int $notificationId ID de la notification à marquer comme lue.
 * @return void
 */
function markNotificationAsRead($con, $notificationId)
{
    $query = $con->prepare("UPDATE notifications SET view = 1 WHERE id = ?");
    $query->bind_param('i', $notificationId);
    $query->execute();
}

// Marquer les notifications comme lues lorsque l'ID est passé via une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $notificationId = intval($_POST['id']);
    markNotificationAsRead($con, $notificationId);
    echo json_encode(['success' => true]);
    exit;
}


/**
 * Récupère les informations d'un utilisateur basé sur l'ID de la session.
 *
 * @param mysqli $con La connexion à la base de données.
 * @return array|null Les informations de l'utilisateur ou null si l'utilisateur n'est pas trouvé.
 */
function getAccountUserInfo($con)
{
    // Vérifier si l'ID de l'utilisateur est défini dans la session
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        $query = "
            SELECT u.*, g.nom AS grade_nom, 
                   COALESCE(SUM(money.montant), 0) AS total_money
            FROM users u 
            LEFT JOIN grade g ON u.acc_grade = g.id 
            LEFT JOIN money ON u.id = money.user_id
            LEFT JOIN company c ON u.id = c.user_id
            WHERE u.id = ?
            GROUP BY u.id, c.id";

        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Nettoyer les informations récupérées
            $user_info = [
                'acc_email' => trim($user['acc_email'] ?? ''),
                'acc_username' => trim($user['acc_username'] ?? ''),
                'acc_date' => trim($user['acc_date'] ?? ''),
                'acc_grade' => trim($user['grade_nom'] ?? ''),
                'acc_logo' => isset($user['acc_logo']) ? trim($user['acc_logo'] ?? '') : null,
                'acc_type' => trim($user['acc_type'] ?? ''),
                'acc_affiliate_id' => trim($user['acc_affiliate_id'] ?? ''),
                'acc_last_edit' => trim($user['acc_last_edit'] ?? ''),
                'acc_money' => $user['total_money'],
                'acc_url' => getAvatarUrl($user['acc_logo'] ?? '', $user['acc_type'] ?? '')
            ];

            return $user_info;

        } else {
            // Rediriger vers la page de déconnexion si l'utilisateur n'est pas trouvé
            header('Location: /account/account-logout.php');
            exit;
        }
    }

    // Retourner null si l'utilisateur n'est pas défini dans la session
    return null;
}

/**
 * Génère une chaîne de caractères aléatoire
 *
 * @param int $length Longueur de la chaîne à générer
 * @return string Chaîne aléatoire générée
 */
function generateRandomString($length = 12)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Gère les utilisateurs non connectés en définissant un cookie et une session
 *
 * @return array Contient l'ID utilisateur non connecté et l'adresse IP
 */
function handleNonConnectedUser()
{
    if (!isset($_SESSION['user_id'])) {
        if (!isset($_COOKIE['no_connect_user_id'])) {
            $no_connect_user_id = generateRandomString();
            setcookie('no_connect_user_id', $no_connect_user_id, [
                'expires' => time() + 3600, // Expire dans 1 heure
                'path' => '/',
                'domain' => '', // Spécifiez votre domaine si nécessaire
                'secure' => true, // Transmission uniquement via HTTPS
                'httponly' => true, // Accessible uniquement via HTTP(S), pas par les scripts JavaScript
                'samesite' => 'Strict', // Empêche l'envoi du cookie avec les requêtes cross-site
            ]);

            $_SESSION['no_connect_user_id'] = $no_connect_user_id;
        } else {
            $no_connect_user_id = $_COOKIE['no_connect_user_id'];
        }

        $ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
            (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);

        $_SESSION['no_connect_ip'] = $ip;

        return [
            'no_connect_user_id' => $no_connect_user_id,
            'no_connect_ip' => $ip
        ];
    } else {
        return [
            'no_connect_user_id' => null,
            'no_connect_ip' => null
        ];
    }
}

/**
 * Vérifie le parrainage et insère les données dans la table affiliates
 *
 * @param mysqli $con Connexion à la base de données
 * @return void
 */
function checkAndInsertParrainage($con)
{
    if (isset($_SESSION['parrainage_id'])) {
        $parrainage_id = $_SESSION['parrainage_id'];

        $sql = "SELECT acc_affiliate_id FROM users WHERE acc_affiliate_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $parrainage_id);
        $stmt->execute();
        $stmt->store_result();

        $parrainage_valeur = ($stmt->num_rows > 0);
        $stmt->close();

        if ($parrainage_valeur && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $sql = "INSERT INTO affiliates (user_id, affiliate_id) VALUES (?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("is", $user_id, $parrainage_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * Fonction pour obtenir l'URL de l'avatar de l'utilisateur
 *
 * @param string $ex_acc_logo Chemin ou URL de l'avatar
 * @param string $ex_acc_type Type de compte (google, discord, twitch, meta)
 * @return string URL de l'avatar
 */
function getAvatarUrl($ex_acc_logo, $ex_acc_type)
{
    $defaultAvatarUrl = '/styles/img/profile/avatar-default.png';

    // Vérifie si l'image spécifiée existe et est valide pour les liens externes
    if (!empty($ex_acc_logo) && filter_var($ex_acc_logo, FILTER_VALIDATE_URL) && @getimagesize($ex_acc_logo)) {
        return $ex_acc_logo;
    }

    // Si c'est un chemin relatif, construisez le chemin absolu à partir du répertoire racine
    $baseDir = $_SERVER['DOCUMENT_ROOT'];
    $localPath = $baseDir . $ex_acc_logo;

    // Vérifie si l'image spécifiée existe et est valide pour les chemins locaux
    if (!empty($ex_acc_logo) && !filter_var($ex_acc_logo, FILTER_VALIDATE_URL) && file_exists($localPath)) {
        return $ex_acc_logo;
    }

    // Liste des URLs d'avatars par type de compte
    $acc_urls = [
        "google" => $ex_acc_logo,
        "discord" => $ex_acc_logo,
        "twitch" => $ex_acc_logo,
        "meta" => $ex_acc_logo,
    ];

    // Vérifie si $ex_acc_type est vide ou non valide
    if (empty($ex_acc_type) || !array_key_exists($ex_acc_type, $acc_urls) || empty($acc_urls[$ex_acc_type])) {
        return $defaultAvatarUrl;
    }

    // Vérifie si l'URL de l'avatar par type de compte existe et est valide
    $avatarUrl = $acc_urls[$ex_acc_type];
    if (filter_var($avatarUrl, FILTER_VALIDATE_URL) && @getimagesize($avatarUrl)) {
        return $avatarUrl;
    } elseif (!filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
        $localAvatarPath = $baseDir . $avatarUrl;
        if (file_exists($localAvatarPath)) {
            return $avatarUrl;
        }
    }

    return $defaultAvatarUrl;
}

/**
 * Génère un token aléatoire
 *
 * @param int $length Longueur du token à générer
 * @return string Token généré
 */
function generateRandomToken($length = 60)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $characters[rand(0, $charactersLength - 1)];
    }
    return $token;
}

/**
 * Fonction pour formater les nombres en K (milliers) et M (millions)
 *
 * @param int $number Nombre à formater
 * @return string Nombre formaté
 */
function format_number($number)
{
    if ($number >= 1000000) {
        return number_format($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, 1) . 'K';
    } else {
        return $number;
    }
}

/**
 * Fonction pour formater le temps restant
 *
 * @param int $diff Temps en secondes
 * @return string Temps formaté
 */
function format_timing($diff)
{
    $jours = floor($diff / (60 * 60 * 24));
    $heures = floor(($diff % (60 * 60 * 24)) / (60 * 60));
    $minutes = floor(($diff % (60 * 60)) / 60);
    $secondes = $diff % 60;

    if ($jours > 0) {
        return "Dans $jours jour" . ($jours > 1 ? "s" : "");
    } elseif ($heures > 0) {
        return "Dans $heures heure" . ($heures > 1 ? "s" : "");
    } elseif ($minutes > 0) {
        return "Dans $minutes minute" . ($minutes > 1 ? "s" : "");
    } else {
        return "Dans $secondes seconde" . ($secondes > 1 ? "s" : "");
    }
}

/**
 * Fonction pour générer un nombre aléatoire unique
 *
 * @param int $min Valeur minimale
 * @param int $max Valeur maximale
 * @param int &$last Dernier nombre généré
 * @return int Nombre aléatoire unique
 */
function unique_rand($min, $max, &$last)
{
    do {
        $number = rand($min, $max);
    } while ($number == $last);

    $last = $number;

    return $number;
}

/**
 * Fonction de gestion des erreurs personnalisée
 *
 * Cette fonction est appelée lorsqu'une erreur PHP survient. Elle enregistre les détails de l'erreur
 * dans la base de données uniquement si elle n'a pas été enregistrée dans les 15 dernières minutes,
 * et empêche l'exécution du gestionnaire d'erreurs interne de PHP.
 *
 * @param int $errno Niveau d'erreur (voir les constantes de niveau d'erreur PHP)
 * @param string $errstr Message d'erreur
 * @param string $errfile Fichier où l'erreur a été générée
 * @param int $errline Ligne où l'erreur a été générée
 * @return bool Toujours retourner true pour indiquer que l'erreur a été gérée
 */
function errorHandler($errno, $errstr, $errfile, $errline)
{
    global $con; // Utiliser la connexion existante à la base de données

    // Vérifier si la connexion est encore valide
    if (!$con || !$con->query('SELECT 1')) {
        // Optionnel: Enregistrer l'erreur ailleurs ou envoyer une notification
        // Ne pas tenter de journaliser dans la base de données si la connexion est fermée
        return true;
    }

    // Échapper les valeurs pour éviter les injections SQL
    $errno = $con->real_escape_string($errno);
    $errstr = $con->real_escape_string($errstr);
    $errfile = $con->real_escape_string($errfile);
    $errline = $con->real_escape_string($errline);

    // Vérifier si l'erreur a déjà été enregistrée dans les 15 dernières minutes
    $query = "
        SELECT COUNT(*) 
        FROM erreurs 
        WHERE errstr = ? 
          AND errfile = ? 
          AND errline = ? 
          AND created_at >= NOW() - INTERVAL 15 MINUTE
    ";
    $stmt = $con->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ssi', $errstr, $errfile, $errline);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row()[0];
        $stmt->close();

        // Si l'erreur n'a pas été enregistrée récemment, l'ajouter à la base de données
        if ($result == 0) {
            $query = "
                INSERT INTO erreurs (errno, errstr, errfile, errline, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ";
            $stmt = $con->prepare($query);
            if ($stmt) {
                $stmt->bind_param('issi', $errno, $errstr, $errfile, $errline);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Empêcher PHP d'exécuter le gestionnaire d'erreurs interne
    return true;
}



// Fonction pour récupérer le logo du serveur Minecraft et l'enregistrer
/**
 * Fonction pour récupérer le logo du serveur Minecraft et l'enregistrer
 *
 * Cette fonction effectue une requête à l'API de statut du serveur Minecraft, décode les données
 * de la favicon en base64, et enregistre l'image dans le répertoire spécifié.
 *
 * @param string $ip Adresse IP du serveur Minecraft
 * @param int $port Port du serveur Minecraft
 * @param string $outputDirectory Répertoire où enregistrer le fichier image
 * @return void
 */
function fetchMinecraftServerLogo($ip, $port, $outputDirectory)
{
    // URL de l'API pour obtenir les informations du serveur
    $url = "https://api.mcsrvstat.us/2/" . urlencode($ip) . ":" . urlencode($port);

    // Effectuer une requête GET à l'API
    $response = @file_get_contents($url);

    if ($response === FALSE) {
        die("Erreur lors de la récupération des données depuis l'API.");
    }

    // Décoder la réponse JSON
    $data = json_decode($response, true);

    // Variable pour le nom de l'image
    $imageName = 'pack__1_.png'; // Valeur par défaut

    // Vérifier si l'API a renvoyé un statut 'online' et une favicon
    if (isset($data['online']) && $data['online'] && !empty($data['icon'])) {
        // Extraire le contenu de la favicon (base64)
        $faviconBase64 = $data['icon'];

        // Supprimer le préfixe "data:image/png;base64,"
        list(, $faviconData) = explode(',', $faviconBase64);

        // Générer un nom de fichier unique aléatoire
        $randomFileName = uniqid('logo_', true) . '.png';

        // Définir le chemin complet du fichier de sortie
        $outputFile = $outputDirectory . DIRECTORY_SEPARATOR . $randomFileName;

        // Créer le dossier de sortie si nécessaire
        if (!is_dir($outputDirectory)) {
            if (!mkdir($outputDirectory, 0777, true)) {
                die("Erreur: Impossible de créer le dossier de sortie.");
            }
        }

        // Décoder les données base64 et les écrire dans un fichier
        if (file_put_contents($outputFile, base64_decode($faviconData)) !== false) {
            $imageName = $randomFileName; // Mettre à jour le nom de l'image si l'enregistrement réussit
        }
    }

    // Retourner le nom de l'image enregistrée
    return $imageName;

}


?>