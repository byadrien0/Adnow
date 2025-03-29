<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit();
}

// Activer le rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtenir le campaign_id depuis l'URL
$campaign_id = $_GET['id'] ?? 0; // Par défaut à 0 si non défini pour éviter des erreurs

// Obtenir le link_code de la campagne
$sql_link_code = "SELECT link_code, nom, impression_total FROM campaigns WHERE campaign_id = ?";
$stmt_link_code = $con->prepare($sql_link_code);
$stmt_link_code->bind_param("i", $campaign_id);
$stmt_link_code->execute();
$link_code_result = $stmt_link_code->get_result();
$link_code_row = $link_code_result->fetch_assoc();

// Vérifier si aucun résultat n'est trouvé
if (!$link_code_row) {
    header("Location: /dashboard/account-campaigns-list.php");
    exit();
}

// Si le résultat existe, continuer avec le reste du code
$link_code = $link_code_row['link_code'] ?? '';
$nom = $link_code_row['nom'] ?? 'Campagne Inconnue';
$impression_total = $link_code_row['impression_total'] ?? 0;

// Obtenir les statistiques d'impression mensuelles
$sql_monthly_impressions = "SELECT MONTH(created_at) as month, COUNT(*) as total_impressions FROM campaigns_impression WHERE campaign_id = ? GROUP BY MONTH(created_at)";
$stmt_monthly_impressions = $con->prepare($sql_monthly_impressions);
$stmt_monthly_impressions->bind_param("i", $campaign_id);
$stmt_monthly_impressions->execute();
$monthly_impressions_result = $stmt_monthly_impressions->get_result();

// Convertir les résultats pour le graphique
$monthly_impressions = array_fill(0, 12, 0);
while ($row = $monthly_impressions_result->fetch_assoc()) {
    $monthly_impressions[$row['month'] - 1] = $row['total_impressions'] ?? 0;
}

// Obtenir les impressions totales
$sql_total_impressions = "SELECT COUNT(*) as total_impressions FROM campaigns_impression WHERE campaign_id = ?";
$stmt_total_impressions = $con->prepare($sql_total_impressions);
$stmt_total_impressions->bind_param("i", $campaign_id);
$stmt_total_impressions->execute();
$total_impressions_result = $stmt_total_impressions->get_result();
$total_impressions = $total_impressions_result->fetch_assoc()['total_impressions'] ?? 0;

// Obtenir les clics totaux
$sql_total_clicks = "SELECT COUNT(*) as total_clicks FROM campaigns_clicks WHERE link_code = ?";
$stmt_total_clicks = $con->prepare($sql_total_clicks);
$stmt_total_clicks->bind_param("s", $link_code);
$stmt_total_clicks->execute();
$total_clicks_result = $stmt_total_clicks->get_result();
$total_clicks = $total_clicks_result->fetch_assoc()['total_clicks'] ?? 0;

// Obtenir le nombre de serveurs inscrits
$sql_total_servers = "SELECT COUNT(DISTINCT CONCAT(server_id, '_', app)) as total_servers FROM campaigns_impression WHERE campaign_id = ?";
$stmt_total_servers = $con->prepare($sql_total_servers);
$stmt_total_servers->bind_param("i", $campaign_id);
$stmt_total_servers->execute();
$total_servers_result = $stmt_total_servers->get_result();
$total_servers = $total_servers_result->fetch_assoc()['total_servers'] ?? 0;

// Calculer le taux de conversion, en évitant la division par zéro
$conversion_rate = ($total_impressions > 0) ? ($total_clicks / $total_impressions) * 100 : 0;

// Préparer les données pour le graphique en secteurs
$chart_data = [];
$chart_labels = [];
$colors = ["blue", "purple", "green", "red"];

// Obtenir les serveurs avec le plus d'impressions (limité à 10)
$sql_top_servers = "
    SELECT server_id, app, COUNT(*) as impressions 
    FROM campaigns_impression 
    WHERE campaign_id = ? 
    GROUP BY server_id, app
    ORDER BY impressions DESC 
    LIMIT 10";
$stmt_top_servers = $con->prepare($sql_top_servers);
$stmt_top_servers->bind_param("i", $campaign_id);
$stmt_top_servers->execute();
$top_servers_result = $stmt_top_servers->get_result();

$top_servers = [];
while ($row = $top_servers_result->fetch_assoc()) {
    $top_servers[] = $row;
}

// Obtenir les noms des serveurs
$server_names = [];
foreach ($top_servers as $server) {
    $server_id = $server['server_id'];
    $app = $server['app'];
    if ($app == 'minecraft') {
        $sql_server_name = "SELECT nom FROM servers_minecraft WHERE id = ?";
    } elseif ($app == 'discord') {
        $sql_server_name = "SELECT server_name FROM servers_discord WHERE id = ?";
    } else {
        $server_names[$server_id . '_' . $app] = 'Anonyme';
        continue;
    }
    $stmt_server_name = $con->prepare($sql_server_name);
    $stmt_server_name->bind_param("i", $server_id);
    $stmt_server_name->execute();
    $server_name_result = $stmt_server_name->get_result();
    $server_name_row = $server_name_result->fetch_assoc();
    $name_field = ($app == 'minecraft') ? 'nom' : 'server_name';
    $server_names[$server_id . '_' . $app] = $server_name_row[$name_field] ?? 'Anonyme';
}

// Récupérer les impressions des autres serveurs
$total_other_impressions = 0;
if (count($top_servers) > 0) {
    // Construire les paramètres dynamiquement
    $server_params = [];
    $types = '';
    $params = [$campaign_id];

    foreach ($top_servers as $server) {
        $server_params[] = "(server_id = ? AND app = ?)";
        $types .= "is";
        $params[] = $server['server_id'];
        $params[] = $server['app'];
    }

    $placeholders = implode(' OR ', $server_params);
    $sql_other_servers = "
        SELECT COUNT(*) as impressions 
        FROM campaigns_impression 
        WHERE campaign_id = ? 
        AND NOT ($placeholders)";
    $types = "i" . $types;
    $stmt_other_servers = $con->prepare($sql_other_servers);
    $stmt_other_servers->bind_param($types, ...$params);
    $stmt_other_servers->execute();
    $other_servers_result = $stmt_other_servers->get_result();
    $total_other_impressions = $other_servers_result->fetch_assoc()['impressions'] ?? 0;
}

// Préparer les données pour le graphique en secteurs
foreach ($top_servers as $index => $server) {
    $key = $server['server_id'] . '_' . $server['app'];
    $server_name = $server_names[$key] ?? 'Anonyme';
    $server_name = htmlspecialchars(mb_strimwidth($server_name, 0, 25, "...")); // Limiter le nombre de caractères
    $chart_data[] = $server['impressions'] ?? 0;
    $chart_labels[] = $server_name;
}

$chart_data[] = $total_other_impressions;
$chart_labels[] = "Autres";

// Calculer les pourcentages des serveurs Minecraft et Discord
$sql_minecraft_servers = "SELECT COUNT(DISTINCT server_id) as minecraft_servers FROM campaigns_impression WHERE campaign_id = ? AND app = 'minecraft'";
$stmt_minecraft_servers = $con->prepare($sql_minecraft_servers);
$stmt_minecraft_servers->bind_param("i", $campaign_id);
$stmt_minecraft_servers->execute();
$minecraft_servers_result = $stmt_minecraft_servers->get_result();
$minecraft_servers = $minecraft_servers_result->fetch_assoc()['minecraft_servers'] ?? 0;

$sql_discord_servers = "SELECT COUNT(DISTINCT server_id) as discord_servers FROM campaigns_impression WHERE campaign_id = ? AND app = 'discord'";
$stmt_discord_servers = $con->prepare($sql_discord_servers);
$stmt_discord_servers->bind_param("i", $campaign_id);
$stmt_discord_servers->execute();
$discord_servers_result = $stmt_discord_servers->get_result();
$discord_servers = $discord_servers_result->fetch_assoc()['discord_servers'] ?? 0;

$minecraft_percentage = ($total_servers > 0) ? ($minecraft_servers / $total_servers) * 100 : 0;
$discord_percentage = ($total_servers > 0) ? ($discord_servers / $total_servers) * 100 : 0;

// Pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $items_per_page;

// Obtenir les diffusions avec pagination
$sql_diffusions = "
    SELECT 
        COALESCE(sm.nom, sd.server_name) as server_name,
        d.date_diffusion,
        d.app
    FROM campaigns_diffusions d
    LEFT JOIN servers_minecraft sm ON d.server_id = sm.id AND d.app = 'minecraft'
    LEFT JOIN servers_discord sd ON d.server_id = sd.id AND d.app = 'discord'
    WHERE d.campaigns_id = ?
    ORDER BY d.date_diffusion DESC
    LIMIT ? OFFSET ?";
$stmt_diffusions = $con->prepare($sql_diffusions);
$stmt_diffusions->bind_param("iii", $campaign_id, $items_per_page, $offset);
$stmt_diffusions->execute();
$diffusions_result = $stmt_diffusions->get_result();

// Compter le nombre total de diffusions
$sql_count_diffusions = "
    SELECT COUNT(*) as total_count 
    FROM campaigns_diffusions
    WHERE campaigns_id = ?";
$stmt_count_diffusions = $con->prepare($sql_count_diffusions);
$stmt_count_diffusions->bind_param("i", $campaign_id);
$stmt_count_diffusions->execute();
$count_result = $stmt_count_diffusions->get_result();
$total_count = $count_result->fetch_assoc()['total_count'] ?? 0;
$total_pages = ceil($total_count / $items_per_page);
?>




<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNow - Gérer vos Campagnes</title>
    <!-- Meta SEO -->
    <meta name="title" content="AdNow - Gérer vos Campagnes">
    <meta name="description"
        content="Gérez vos campagnes de publicité avec AdNow. Suivez vos performances, ajustez vos budgets, et optimisez vos annonces pour atteindre vos objectifs.">
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
    <meta property="og:title" content="AdNow - Gérer vos Campagnes">
    <meta property="og:site_name" content="AdNow">
    <meta property="og:url" content="https://www.adnow.online/">
    <meta property="og:description"
        content="Gérez vos campagnes de publicité avec AdNow. Suivez vos performances, ajustez vos budgets, et optimisez vos annonces pour atteindre vos objectifs.">
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
                <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
                    <div class="container-xl px-4">
                        <div class="page-header-content pt-4">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mt-4">
                                    <h1 class="page-header-title">
                                        <div class="page-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-activity">
                                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                            </svg></div>
                                        <?= $nom ?>
                                    </h1>
                                    <div class="page-header-subtitle">Analysé les données de la diffusion de votre
                                        camapgne</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- Main page content-->
                <div class="container-xl px-4 mt-n10">
                    <div class="row">
                        <div class="col-xl-4 mb-4">
                            <!-- Dashboard example card 1-->
                            <a class="card lift h-100" href="#!">
                                <div class="card-body d-flex justify-content-center flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="me-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-package feather-xl text-primary mb-3">
                                                <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                                <path
                                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                                                </path>
                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                            </svg>
                                            <h5>Interface rapide et clair</h5>
                                            <div class="text-muted small">feuillté vos données en quelque clique et
                                                rapidement</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/browser-stats.svg" alt="..."
                                            style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-4 mb-4">
                            <!-- Dashboard example card 2-->
                            <a class="card lift h-100" href="#!">
                                <div class="card-body d-flex justify-content-center flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="me-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-book feather-xl text-secondary mb-3">
                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                                <path
                                                    d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z">
                                                </path>
                                            </svg>
                                            <h5>Sécurité renforcé</h5>
                                            <div class="text-muted small">Nous controlons les serveurs qui diffsue les
                                                campagnes</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/processing.svg" alt="..."
                                            style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-4 mb-4">
                            <!-- Dashboard example card 3-->
                            <a class="card lift h-100" href="#!">
                                <div class="card-body d-flex justify-content-center flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="me-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-layout feather-xl text-green mb-3">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                                <line x1="9" y1="21" x2="9" y2="9"></line>
                                            </svg>
                                            <h5>"Vive la nouveauté"</h5>
                                            <div class="text-muted small">C'est finis les anciens affichabes
                                                publicitaires et soyées dans la modernité</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/windows.svg" alt="..."
                                            style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Afficher les impressions réalisées -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-start border-primary">
                                <div class="card-body d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-primary fw-bold small mb-1">Impression</div>
                                        <div class="h5">
                                            <?php echo number_format($total_impressions); ?>/<?php echo number_format(num: $impression_total); ?>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <i class="fas fa-eye fa-2x text-gray-200"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Afficher les clics réalisés -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-start border-secondary">
                                <div class="card-body d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-secondary fw-bold small mb-1">Clics Réalisés</div>
                                        <div class="h5"><?php echo number_format($total_clicks); ?></div>
                                    </div>
                                    <div class="ms-2">
                                        <i class="fas fa-mouse-pointer fa-2x text-gray-200"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Afficher le nombre de serveurs inscrits -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-start border-success">
                                <div class="card-body d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-success fw-bold small mb-1">Serveurs inscrits</div>
                                        <div class="h5"><?php echo number_format($total_servers); ?></div>
                                    </div>
                                    <div class="ms-2">
                                        <i class="fas fa-server fa-2x text-gray-200"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Afficher le taux de conversion -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-start border-info">
                                <div class="card-body d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="text-info fw-bold small mb-1">Taux de Conversion</div>
                                        <div class="h5"><?php echo number_format($conversion_rate, 2) . '%'; ?></div>
                                    </div>
                                    <div class="ms-2">
                                        <i class="fas fa-percentage fa-2x text-gray-200"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xxl-8">
                            <!-- Carte avec onglets -->
                            <div class="card mb-4">
                                <div class="card-header border-bottom">
                                    <!-- Navigation des onglets -->
                                    <ul class="nav nav-tabs card-header-tabs" id="dashboardNav" role="tablist">
                                        <li class="nav-item me-1" role="presentation">
                                            <a class="nav-link active" id="overview-pill" href="#overview"
                                                data-bs-toggle="tab" role="tab" aria-controls="overview"
                                                aria-selected="true">Impression(s)</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="activities-pill" href="#activities"
                                                data-bs-toggle="tab" role="tab" aria-controls="activities"
                                                aria-selected="false" tabindex="-1">Logs des Serveurs</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="card-body">
                                    <div class="tab-content" id="dashboardNavContent">
                                        <!-- Onglet des impressions -->
                                        <div class="tab-pane fade show active" id="overview" role="tabpanel"
                                            aria-labelledby="overview-pill">
                                            <div class="chart-area mb-4 mb-lg-0" style="height: 20rem">
                                                <canvas id="myAreaChart" width="756" height="320"></canvas>
                                            </div>
                                        </div>
                                        <!-- Onglet des logs des serveurs -->
                                        <div class="tab-pane fade" id="activities" role="tabpanel"
                                            aria-labelledby="activities-pill">
                                            <div
                                                class="datatable-wrapper datatable-loading no-footer sortable searchable fixed-columns">
                                                <div class="datatable-container">
                                                    <table id="datatablesSimple" class="datatable-table">
                                                        <thead>
                                                            <tr>
                                                                <th data-sortable="true"><a href="#"
                                                                        class="datatable-sorter">Date</a></th>
                                                                <th data-sortable="true"><a href="#"
                                                                        class="datatable-sorter">Événement</a></th>
                                                                <th data-sortable="true"><a href="#"
                                                                        class="datatable-sorter">Heure</a></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php while ($row = $diffusions_result->fetch_assoc()): ?>
                                                                <tr>
                                                                    <!-- Affichage de la date -->
                                                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['date_diffusion']))); ?>
                                                                    </td>

                                                                    <!-- Affichage des informations de la campagne -->
                                                                    <td>
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                            height="24" viewBox="0 0 24 24" fill="none"
                                                                            stroke="currentColor" stroke-width="2"
                                                                            stroke-linecap="round" stroke-linejoin="round"
                                                                            class="feather feather-zap me-2 text-green">
                                                                            <polygon
                                                                                points="13 2 3 14 12 14 11 22 21 10 12 10 13 2">
                                                                            </polygon>
                                                                        </svg>
                                                                        "<?php echo htmlspecialchars(mb_strimwidth($row['server_name'] ?: 'Serveur Anonyme', 0, 25, "...")); ?>"
                                                                        a rejoint votre campagne via
                                                                        <?php echo ucfirst(htmlspecialchars($row['app'])); ?>
                                                                        !
                                                                    </td>

                                                                    <!-- Affichage de l'heure -->
                                                                    <td><?php echo htmlspecialchars(date('H:i', strtotime($row['date_diffusion']))); ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endwhile; ?>

                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="datatable-bottom">
                                                    <div class="datatable-info">Affichage de
                                                        <?php echo ($offset + 1); ?> à
                                                        <?php echo min($offset + $items_per_page, $total_count); ?> sur
                                                        <?php echo $total_count; ?> éléments
                                                    </div>
                                                    <nav class="datatable-pagination">
                                                        <ul class="datatable-pagination-list">
                                                            <?php if ($page > 1): ?>
                                                                <li class="datatable-pagination-list-item">
                                                                    <a href="?id=<?php echo $campaign_id; ?>&page=<?php echo $page - 1; ?>"
                                                                        class="datatable-pagination-list-item-link">‹</a>
                                                                </li>
                                                            <?php endif; ?>
                                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                                <li
                                                                    class="datatable-pagination-list-item <?php echo ($i == $page) ? 'datatable-active' : ''; ?>">
                                                                    <a href="?id=<?php echo $campaign_id; ?>&page=<?php echo $i; ?>"
                                                                        class="datatable-pagination-list-item-link"><?php echo $i; ?></a>
                                                                </li>
                                                            <?php endfor; ?>
                                                            <?php if ($page < $total_pages): ?>
                                                                <li class="datatable-pagination-list-item">
                                                                    <a href="?id=<?php echo $campaign_id; ?>&page=<?php echo $page + 1; ?>"
                                                                        class="datatable-pagination-list-item-link">›</a>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </nav>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-xxl-12">
                                <!-- Project tracker card example-->
                                <div class="card card-header-actions mb-4">
                                    <div class="card-header">
                                        Répartition des Serveurs par jeux
                                    </div>
                                    <div class="card-body">
                                        <!-- Minecraft -->
                                        <div class="d-flex align-items-center justify-content-between small mb-1">
                                            <div class="fw-bold">Minecraft</div>
                                            <div class="small"><?php echo round($minecraft_percentage); ?>%</div>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                style="width: <?php echo $minecraft_percentage; ?>%"
                                                aria-valuenow="<?php echo $minecraft_percentage; ?>" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                        <!-- Discord -->
                                        <div class="d-flex align-items-center justify-content-between small mb-1">
                                            <div class="fw-bold">Discord</div>
                                            <div class="small"><?php echo round($discord_percentage); ?>%</div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: <?php echo $discord_percentage; ?>%"
                                                aria-valuenow="<?php echo $discord_percentage; ?>" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-xxl-4">
                            <div class="row">
                                <div class="col-xl-6 col-xxl-12">
                                    <!-- Graphique en secteurs avec légende -->
                                    <div class="card h-100">
                                        <div class="card-header">Top 10 des Impressions par Serveurs</div>
                                        <div class="card-body">
                                            <div class="chart-pie mb-4">
                                                <canvas id="myPieChart" width="756" height="240"></canvas>
                                            </div>
                                            <div class="list-group list-group-flush">
                                                <?php
                                                // Calculer le total des impressions pour le pourcentage
                                                $total_impressions_pie = array_sum($chart_data);
                                                $total_impressions_pie = $total_impressions_pie ?: 1; // Évite la division par zéro
                                                
                                                $colors = ["blue", "purple", "green", "red"]; // Couleurs pour chaque segment
                                                
                                                foreach ($chart_labels as $index => $label) {
                                                    $impressions = $chart_data[$index];
                                                    $percentage = ($impressions / $total_impressions_pie) * 100;
                                                    $color = $colors[$index % count($colors)];
                                                    echo "
                                    <div class='list-group-item d-flex align-items-center justify-content-between small px-0 py-2'>
                                        <div class='me-3'>
                                            <svg class='svg-inline--fa fa-circle fa-sm me-1 text-{$color}' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'>
                                                <path fill='currentColor' d='M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0 0 114.6 0 256s114.6 256 256 256z'></path>
                                            </svg>
                                            " . htmlspecialchars($label) . "
                                        </div>
                                        <div class='fw-500 text-dark'>" . number_format($percentage, 2) . "%</div>
                                    </div>
                                ";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>
            </main>
        </div>
    </div>

    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="js/scripts.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="js/datatables/datatables-simple-demo.js"></script>

    <script>
        // Set new default font family and font color to mimic Bootstrap's default styling
        Chart.defaults.global.defaultFontFamily = "Metropolis",
            '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = "#858796";

        // Préparez les labels du graphique
        var chartLabels = <?php
        $chart_labels = [];
        foreach ($top_servers as $server) {
            $chart_labels[] = isset($server_names[$server['server_id']]) ? $server_names[$server['server_id']] : 'Anonyme';
        }
        $chart_labels[] = 'Autres'; // Ajouter le label pour les "Autres"
        echo json_encode($chart_labels);
        ?>;

        // Pie Chart Example
        var ctx = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: chartLabels,
                datasets: [{
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: [
                        "rgba(0, 97, 242, 1)", // For top server 1
                        "rgba(0, 172, 105, 1)", // For top server 2
                        "rgba(88, 0, 232, 1)", // For top server 3
                        "rgba(0, 255, 0, 1)" // For 'Autres'
                    ],
                    hoverBackgroundColor: [
                        "rgba(0, 97, 242, 0.9)",
                        "rgba(0, 172, 105, 0.9)",
                        "rgba(88, 0, 232, 0.9)",
                        "rgba(0, 255, 0, 0.9)"
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)"
                }]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: "#dddfeb",
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80
            }
        });
    </script>


    <script>
        // Set new default font family and font color to mimic Bootstrap's default styling
        (Chart.defaults.global.defaultFontFamily = "Metropolis"),
            '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = "#858796";

        function number_format(number, decimals, dec_point, thousands_sep) {
            // *     example: number_format(1234.56, 2, ',', ' ');
            // *     return: '1 234,56'
            number = (number + "").replace(",", "").replace(" ", "");
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = typeof thousands_sep === "undefined" ? "," : thousands_sep,
                dec = typeof dec_point === "undefined" ? "." : dec_point,
                s = "",
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return "" + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || "").length < prec) {
                s[1] = s[1] || "";
                s[1] += new Array(prec - s[1].length + 1).join("0");
            }
            return s.join(dec);
        }

        // Area Chart Example
        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Impression",
                    lineTension: 0.3,
                    backgroundColor: "rgba(0, 97, 242, 0.05)",
                    borderColor: "rgba(0, 97, 242, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(0, 97, 242, 1)",
                    pointBorderColor: "rgba(0, 97, 242, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(0, 97, 242, 1)",
                    pointHoverBorderColor: "rgba(0, 97, 242, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: [
                        <?= implode(',', $monthly_impressions); ?>
                    ] // Utilisation des données d'impression dynamiques
                }]
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: "date"
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            // Include a dollar sign in the ticks
                            callback: function (value, index, values) {
                                return number_format(value);
                            }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }]
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: "#6e707e",
                    titleFontSize: 14,
                    borderColor: "#dddfeb",
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: "index",
                    caretPadding: 10,
                    callbacks: {
                        label: function (tooltipItem, chart) {
                            var datasetLabel =
                                chart.datasets[tooltipItem.datasetIndex].label || "";
                            return datasetLabel + ": " + number_format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>