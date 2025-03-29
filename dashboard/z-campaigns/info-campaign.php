<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    addNotification($con, $user_id, "Vous devez être connecté pour accéder à cette page.", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit();
}

// Vérifier si un identifiant de campagne est passé en GET
$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;

if ($campaign_id <= 0) {
    addNotification($con, $user_id, "ID de campagne invalide.", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/");
    exit();
}

// Étape 1 : Sélectionner la campagne spécifique par rapport à l'ID fourni
$campaign_query = "
    SELECT 
        c.campaign_id, 
        c.nom AS campaign_name, 
        c.budget AS budget, 
        c.objectif_cpv AS cost_per_view,
        c.date_debut AS start_date,
        c.logo_url AS logo_url,
        c.impression_total AS total_impressions,
        c.description AS description,
        c.chat_msg_minecraft AS chat_msg_minecraft,
        c.chat_msg_discord AS chat_msg_discord,
        c.link_to AS link_to,
        c.diff_per_hour AS impressions_per_hour,
        c.link_code AS link_code,
        c.app_discord AS app_discord,
        c.app_minecraft AS app_minecraft,
        IFNULL(COUNT(i.id), 0) AS campaign_impressions,
        COUNT(DISTINCT s.id) AS total_servers
    FROM 
        campaigns c
    LEFT JOIN 
        campaigns_impression i ON c.campaign_id = i.campaign_id
    LEFT JOIN 
        (
            SELECT id FROM servers_minecraft
            UNION ALL
            SELECT id FROM servers_discord
        ) s ON s.id IN (
            SELECT server_id 
            FROM campaigns_impression 
            WHERE campaign_id = c.campaign_id
        )
    WHERE 
        c.campaign_id = ?
    GROUP BY 
        c.campaign_id;
";

if ($stmt = mysqli_prepare($con, $campaign_query)) {
    mysqli_stmt_bind_param($stmt, 'i', $campaign_id);
    mysqli_stmt_execute($stmt);
    $campaign_result = mysqli_stmt_get_result($stmt);
    $campaign_info = mysqli_fetch_assoc($campaign_result);
} else {
    addNotification($con, $user_id, "Erreur de préparation de la requête des campagnes.", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/account-campagne-list.php");
    exit();
}

if ($campaign_info) {
    $campaign_name = htmlspecialchars($campaign_info['campaign_name'] ?? '');
    $budget = htmlspecialchars($campaign_info['budget'] ?? '');
    $cost_per_view = htmlspecialchars($campaign_info['cost_per_view'] ?? 0);
    $start_date = new DateTime($campaign_info['start_date'] ?? 'now');
    $logo_url = htmlspecialchars($campaign_info['logo_url'] ?? '');
    $total_impressions = htmlspecialchars($campaign_info['total_impressions'] ?? 0);
    $description = htmlspecialchars($campaign_info['description'] ?? '');
    $link_to = htmlspecialchars($campaign_info['link_to'] ?? '');
    $impressions_per_hour = htmlspecialchars($campaign_info['impressions_per_hour'] ?? '');
    $link_code = htmlspecialchars($campaign_info['link_code'] ?? '');

    $app_discord = $campaign_info['app_discord'] === 'yes';
    $app_minecraft = $campaign_info['app_minecraft'] === 'yes';
    $chat_msg_discord = $app_discord ? htmlspecialchars($campaign_info['chat_msg_discord'] ?? '') : null;
    $chat_msg_minecraft = $app_minecraft ? htmlspecialchars($campaign_info['chat_msg_minecraft'] ?? '') : null;

    $campaign_impressions = intval($campaign_info['campaign_impressions'] ?? 0);
    $total_servers = intval($campaign_info['total_servers'] ?? 0);

    // Étape 2 : Sélectionner les serveurs Minecraft et Discord de l'utilisateur
    $servers_query = "
        SELECT id, 'minecraft' AS server_type
        FROM servers_minecraft
        WHERE user_id = ?
        UNION ALL
        SELECT id, 'discord' AS server_type
        FROM servers_discord
        WHERE user_id = ?;
    ";

    if ($servers_stmt = mysqli_prepare($con, $servers_query)) {
        mysqli_stmt_bind_param($servers_stmt, 'ii', $_SESSION['user_id'], $_SESSION['user_id']);
        mysqli_stmt_execute($servers_stmt);
        $servers_result = mysqli_stmt_get_result($servers_stmt);

        $user_server_ids = [];
        while ($server_row = mysqli_fetch_assoc($servers_result)) {
            $user_server_ids[] = $server_row['id'];
        }
    } else {
        addNotification($con, $user_id, "Erreur de préparation de la requête des serveurs.", "Erreur", $no_connect_user_id, $no_connect_ip);
        header("Location: /dashboard/account-campagne-list.php");
        exit();
    }

    if (!empty($user_server_ids)) {
        // Préparer les placeholders pour les IDs de serveurs
        $placeholders = implode(',', array_fill(0, count($user_server_ids), '?'));

        // Étape 3 : Vérifier l'inscription à la campagne
        $inscrit_query = "
            SELECT COUNT(*) as inscrit 
            FROM campaigns_impression 
            WHERE campaign_id = ? AND server_id IN ($placeholders);
        ";

        if ($inscrit_stmt = mysqli_prepare($con, $inscrit_query)) {
            // Préparer les paramètres pour mysqli_stmt_bind_param
            $params = array_merge([$campaign_id], $user_server_ids);
            $types = str_repeat('i', count($params));

            // Préparer les références des paramètres
            $refs = [];
            foreach ($params as $key => $value) {
                $refs[$key] = &$params[$key];
            }

            // Appeler mysqli_stmt_bind_param avec call_user_func_array
            array_unshift($refs, $types);
            call_user_func_array([$inscrit_stmt, 'bind_param'], $refs);

            mysqli_stmt_execute($inscrit_stmt);
            $inscrit_result = mysqli_stmt_get_result($inscrit_stmt);
            $inscrit_row = mysqli_fetch_assoc($inscrit_result);
            $estInscrit = $inscrit_row['inscrit'] > 0;
        } else {
            addNotification($con, $user_id, "Erreur de préparation de la requête d'inscription.", "Erreur", $no_connect_user_id, $no_connect_ip);
            header("Location: /dashboard/account-campagne-list.php");
            exit();
        }

        if ($estInscrit) {
            // Étape 4 : Inclure les clics des serveurs Discord et Minecraft
            $user_clicks_query = "
                SELECT COUNT(*) AS user_server_clicks
                FROM campaigns_clicks
                WHERE link_code = ? AND server_id IN ($placeholders);
            ";

            if ($user_clicks_stmt = mysqli_prepare($con, $user_clicks_query)) {
                // Préparer les paramètres pour mysqli_stmt_bind_param
                $params = array_merge([$link_code], $user_server_ids);
                $types = 's' . str_repeat('i', count($user_server_ids));

                // Préparer les références des paramètres
                $refs = [];
                foreach ($params as $key => $value) {
                    $refs[$key] = &$params[$key];
                }

                // Appeler mysqli_stmt_bind_param avec call_user_func_array
                array_unshift($refs, $types);
                call_user_func_array([$user_clicks_stmt, 'bind_param'], $refs);

                mysqli_stmt_execute($user_clicks_stmt);
                $user_clicks_result = mysqli_stmt_get_result($user_clicks_stmt);
                $user_clicks_row = mysqli_fetch_assoc($user_clicks_result);

                $user_server_clicks = intval($user_clicks_row['user_server_clicks'] ?? 0);
                $impressions_restantes = $total_impressions - $campaign_impressions;
                $cost_per_impression = $cost_per_view / 1000;
                $montant_gere = $campaign_impressions * $cost_per_impression;
                $tdc = ($campaign_impressions > 0) ? ($user_server_clicks / $campaign_impressions) * 100 : 0;
            } else {
                addNotification($con, $user_id, "Erreur de préparation de la requête des clics des serveurs de l'utilisateur.", "Erreur", $no_connect_user_id, $no_connect_ip);
                header("Location: /dashboard/account-campagne-list.php");
                exit();
            }
        }
    } else {
        $estInscrit = false;
    }
} else {
    addNotification($con, $user_id, "Aucune campagne trouvée pour l'ID spécifié.", "Erreur", $no_connect_user_id, $no_connect_ip);
    header("Location: /dashboard/account-campagne-list.php");
    exit();
}
?>





<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNow - Trouvez des Sponsors pour Votre Serveur de Jeu</title>

    <!-- Meta SEO -->
    <meta name="title" content="AdNow - Trouvez des Sponsors pour Votre Serveur de Jeu">
    <meta name="description"
        content="Trouvez des sponsors pour votre serveur de jeu avec AdNow. Explorez des opportunités pour monétiser et améliorer votre expérience de jeu grâce à notre plateforme.">
    <meta name="robots" content="index, follow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="Français">
    <meta name="author" content="Équipe AdNow">

    <!-- Notif -->
    <link href="/styles/css/notification.css" rel="stylesheet">
    <script src="/styles/js/notification.js"></script>

    <!-- Cookie -->
    <link href="/styles/css/cookie.css" rel="stylesheet">
    <script src="/styles/js/cookie.js"></script>

    <!-- Partage sur les réseaux sociaux -->
    <meta property="og:title" content="AdNow - Trouvez des Sponsors pour Votre Serveur de Jeu">
    <meta property="og:site_name" content="AdNow">
    <meta property="og:url" content="https://www.adnow.online/">
    <meta property="og:description"
        content="AdNow vous aide à trouver des sponsors pour votre serveur de jeu. Découvrez des moyens de monétiser et d'améliorer votre communauté de jeu avec notre plateforme facile à utiliser.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://www.adnow.online/assets/og-image.png">
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@AdNow" />
    <meta name="twitter:creator" content="@AdNow" />
    <link href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" rel="stylesheet" />
    <link href="/dashboard/css/styles.css" rel="stylesheet" />
    <link rel="apple-touch-icon" sizes="180x180" href="/styles/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/styles/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/styles/img/favicon-16x16.png">
    <script data-search-pseudo-elements="" defer=""
        src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous">
    </script>
</head>

<body class="nav-fixed">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/includes/nav-top.php'; ?>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/includes/nav-left.php'; ?>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
                    <div class="container-xl px-4">
                        <div class="page-header-content">
                            <div class="row align-items-center justify-content-between pt-3">
                                <div class="col-auto mb-3">
                                    <h1 class="page-header-title">
                                        <div class="page-header-icon">
                                            <svg class="svg-inline--fa fa-bullhorn" aria-hidden="true" focusable="false"
                                                data-prefix="fas" data-icon="bullhorn" role="img"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                data-fa-i2svg="">
                                                <path fill="currentColor"
                                                    d="M480 32c0-12.9-7.8-24.6-19.8-29.6s-25.7-2.2-34.9 6.9L381.7 53c-48 48-113.1 75-181 75H192 160 64c-35.3 0-64 28.7-64 64v96c0 35.3 28.7 64 64 64l0 128c0 17.7 14.3 32 32 32h64c17.7 0 32-14.3 32-32V352l8.7 0c67.9 0 133 27 181 75l43.6 43.6c9.2 9.2 22.9 11.9 34.9 6.9s19.8-16.6 19.8-29.6V300.4c18.6-8.8 32-32.5 32-60.4s-13.4-51.6-32-60.4V32zm-64 76.7V240 371.3C357.2 317.8 280.5 288 200.7 288H192V192h8.7c79.8 0 156.5-29.8 215.3-83.3z">
                                                </path>
                                            </svg>
                                        </div>
                                        Visualisation de Campagne - <?= htmlspecialchars($campaign_name) ?>
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- Main page content -->
                <div class="container-xl px-4 mt-4">
                    <hr class="mt-0 mb-4" />
                    <div class="row">
                        <div class="col-xl-4">
                            <!-- Profile picture card -->
                            <div class="card mb-4 mb-xl-0">
                                <div class="card-header bg-primary text-white">Logo de la campagne</div>
                                <div class="card-body text-center">
                                    <!-- Profile picture image -->
                                    <img class="img-account-profile rounded-circle mb-2 border border-3 border-primary"
                                        src="<?= htmlspecialchars($logo_url) ?>" alt="Logo de la campagne" />
                                    <!-- Profile picture help block -->
                                    <div class="small font-italic text-muted mb-4">
                                        <?= htmlspecialchars($campaign_name) ?>
                                    </div>
                                    <!-- Profile picture upload button -->
                                    <a href="<?= htmlspecialchars($link_to) ?>" class="btn btn-success"
                                        target="_blank">Visitez le site web</a>
                                </div>
                            </div>


                            <br>

                            <div class="card mb-4">
                                <div class="card-body text-center p-5">
                                    <img class="img-fluid mb-5"
                                        src="/dashboard/assets/img/illustrations/data-report.svg">
                                    <h4>Lancez Votre Campagne</h4>
                                    <p class="mb-4">Prêt à donner vie à vos idées ? Créez dès maintenant votre propre
                                        campagne et commencez à atteindre vos objectifs comme jamais auparavant !</p>
                                    <a class="btn btn-primary p-3"
                                        href="/dashboard/account-campaign-create.php">Démarrer Maintenant</a>
                                </div>
                            </div>

                        </div>
                        <div class="col-xl-8">
                            <!-- Informations générales -->
                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">Informations Générales</div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Colonne 1 -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-award"></i> TYPE DE GAIN
                                                    :</label>
                                                <p class="form-control-plaintext bg-light p-2 rounded">vues</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-calendar-day"></i> Date de
                                                    début :</label>
                                                <p class="form-control-plaintext bg-light p-2 rounded">
                                                    <?= htmlspecialchars($start_date->format('Y-m-d')) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <!-- Colonne 2 -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-target"></i> C.P.M :</label>
                                                <p class="form-control-plaintext bg-light p-2 rounded">
                                                    <?= htmlspecialchars($cost_per_view) ?> €
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-clock"></i> Affichage par
                                                    heure :</label>
                                                <p class="form-control-plaintext bg-light p-2 rounded">
                                                    <?= htmlspecialchars($impressions_per_hour) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><i class="fas fa-info-circle"></i> Description de
                                                la campagne :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <?= htmlspecialchars($description) ?>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><i class="fas fa-link"></i> Lien :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <a href="<?= htmlspecialchars($link_to) ?>" class="text-primary"
                                                    target="_blank">
                                                    <?= htmlspecialchars($link_to) ?>
                                                </a>
                                            </p>
                                        </div>



                                    </div>
                                </div>
                            </div>

                            <div class="card card-header-actions">
                                <div class="card-header bg-secondary text-white">Textes affichés selon les différentes
                                    plateformes de diffusion</div>
                                <div class="card-body">


                                    <!-- Affichage des messages de chat pour les applications avec icônes -->
                                    <?php if ($app_discord): ?>
                                        <div class="card card-header-actions mb-3">
                                            <div class="card-header">
                                                <span>Publicité sur Discord</span>
                                                <div>
                                                    <button class="btn btn-blue btn-icon me-2">
                                                        <!-- Icône Discord SVG -->
                                                        <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"
                                                            fill="#000000">
                                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                                stroke-linejoin="round"></g>
                                                            <g id="SVGRepo_iconCarrier">
                                                                <circle cx="512" cy="512" r="512" style="fill:#000000">
                                                                </circle>
                                                                <path
                                                                    d="M689.43 349a422.21 422.21 0 0 0-104.22-32.32 1.58 1.58 0 0 0-1.68.79 294.11 294.11 0 0 0-13 26.66 389.78 389.78 0 0 0-117.05 0 269.75 269.75 0 0 0-13.18-26.66 1.64 1.64 0 0 0-1.68-.79A421 421 0 0 0 334.44 349a1.49 1.49 0 0 0-.69.59c-66.37 99.17-84.55 195.9-75.63 291.41a1.76 1.76 0 0 0 .67 1.2 424.58 424.58 0 0 0 127.85 64.63 1.66 1.66 0 0 0 1.8-.59 303.45 303.45 0 0 0 26.15-42.54 1.62 1.62 0 0 0-.89-2.25 279.6 279.6 0 0 1-39.94-19 1.64 1.64 0 0 1-.16-2.72c2.68-2 5.37-4.1 7.93-6.22a1.58 1.58 0 0 1 1.65-.22c83.79 38.26 174.51 38.26 257.31 0a1.58 1.58 0 0 1 1.68.2c2.56 2.11 5.25 4.23 8 6.24a1.64 1.64 0 0 1-.14 2.72 262.37 262.37 0 0 1-40 19 1.63 1.63 0 0 0-.87 2.28 340.72 340.72 0 0 0 26.13 42.52 1.62 1.62 0 0 0 1.8.61 423.17 423.17 0 0 0 128-64.63 1.64 1.64 0 0 0 .67-1.18c10.68-110.44-17.88-206.38-75.7-291.42a1.3 1.3 0 0 0-.63-.63zM427.09 582.85c-25.23 0-46-23.16-46-51.6s20.38-51.6 46-51.6c25.83 0 46.42 23.36 46 51.6.02 28.44-20.37 51.6-46 51.6zm170.13 0c-25.23 0-46-23.16-46-51.6s20.38-51.6 46-51.6c25.83 0 46.42 23.36 46 51.6.01 28.44-20.17 51.6-46 51.6z"
                                                                    style="fill:#ffffff"></path>
                                                            </g>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text bg-light p-2 rounded">
                                                    <?= htmlspecialchars($chat_msg_discord) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($app_minecraft): ?>
                                        <div class="card card-header-actions mb-3">
                                            <div class="card-header">
                                                <span>Publicité sur Minecraft</span>
                                                <div>
                                                    <button class="btn btn-green btn-icon me-2">
                                                        <!-- Icône Minecraft SVG -->
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                                            aria-label="Minecraft" role="img" viewBox="0 0 512 512"
                                                            stroke-linecap="square" fill="none">
                                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round"
                                                                stroke-linejoin="round"></g>
                                                            <g id="SVGRepo_iconCarrier">
                                                                <rect width="512" height="512" rx="15%" fill="#111"></rect>
                                                                <g id="a" transform="matrix(19 11 0 22 76 142)">
                                                                    <path fill="#432" d="M.5.5h9v9h-9"></path>
                                                                    <path stroke="#864" d="M2 8v1h2V8h5V7 H7V5"></path>
                                                                    <path stroke="#643"
                                                                        d="M1 5zM2 9zM1 8V7h2V6h1M5 9h2V8H6V4M7 6h1v1M9 9zM9 4v1">
                                                                    </path>
                                                                    <path stroke="#a75" d="M1 7h1M4 7h1M9 6z"></path>
                                                                    <path stroke="#555" d="M5 5z"></path>
                                                                    <path stroke="#593" d="M4 4V1h4v2H7V2H4v1H2v1"></path>
                                                                    <path stroke="#6a4" d="M2 1h1M6 1zM7 2zM9 1v1"></path>
                                                                    <path stroke="#7c5" d="M5 3zM3 2h1"></path>
                                                                    <path stroke="#9c6" d="M1 1v1h1M8 1z"></path>
                                                                </g>
                                                                <use xlink:href="#a" transform="matrix(-1 0 0 1 513 0)"
                                                                    opacity=".5"></use>
                                                                <g transform="matrix(-19 11-19-11 447 159)">
                                                                    <path fill="#7b4" d="M.5.5h9v9h-9"></path>
                                                                    <path stroke="#8c5"
                                                                        d="M1 1zM3 1zM4 7zM3 4v2H1v2h3v1h2V7M2 3h4V1H5v1h3M7 4v1H4M9 4v2H8v3">
                                                                    </path>
                                                                    <path stroke="#ad7"
                                                                        d="M1 3v2M1 7zM1 9zM3 3zM4 4zM5 1zM5 3zM5 5v1M5 8v1M7 2v1M8 7h1">
                                                                    </path>
                                                                </g>
                                                            </g>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text bg-light p-2 rounded">
                                                    <?= htmlspecialchars($chat_msg_minecraft) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>


                                    <!-- Affichage d'un message si aucune application n'est activée -->
                                    <?php if (!$app_discord && !$app_minecraft): ?>
                                        <div class="mb-3">
                                            <label class="form-label"><i class="fas fa-info-circle"></i> Message dans le
                                                chat :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded text-muted">
                                                Aucun message configuré pour cette campagne.
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>

                            <br>


                            <?php if ($estInscrit): ?>
                                <!-- Détails plus précis -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary text-white">Vos Détails Précis</div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Colonne 1 -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-money-bill"></i> GAGNÉ
                                                        :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?= htmlspecialchars(number_format($montant_gere, 2)) ?> €
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-eye"></i> NOMBRE D'AFFICHAGE
                                                        :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?= htmlspecialchars($campaign_impressions) ?>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-mouse-pointer"></i> CHAT
                                                        CLICKS :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?= htmlspecialchars($user_server_clicks) ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- Colonne 2 -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-percentage"></i> TAUX DE
                                                        CONVERSION (TDC) :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?= htmlspecialchars(number_format($tdc, 2)) ?>%
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-server"></i> Vos serveurs
                                                        reliés :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?= htmlspecialchars(count($user_server_ids)) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
    <script data-cfasync="false" src="cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="/dashboard/js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="/dashboard/assets/demo/chart-area-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-bar-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-pie-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
    <script src="/dashboard/js/litepicker.js"></script>
</body>

</html>