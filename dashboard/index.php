<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit();
}

$revenu_total = 0;
$total_impressions = 0;
$total_clics = 0;

// Obtenir les informations des serveurs pour l'utilisateur
$sql = "SELECT * FROM servers_minecraft WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$servers_result = $stmt->get_result();
$servers = [];

while ($row = $servers_result->fetch_assoc()) {
    $servers[] = $row;
}

// Obtenir les informations des serveurs Discord pour l'utilisateur
$sql = "SELECT * FROM servers_discord WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$servers_result = $stmt->get_result();

while ($row = $servers_result->fetch_assoc()) {
    $servers[] = $row;
}

// Obtenir les server_ids pour l'utilisateur
$server_ids = array_column($servers, 'id');
if (!empty($server_ids)) {
    $server_ids_placeholder = implode(',', array_fill(0, count($server_ids), '?'));

    // Obtenir les clics de campagnes
    $sql = "SELECT COUNT(*) AS total_clics, server_id FROM campaigns_clicks WHERE server_id IN ($server_ids_placeholder) GROUP BY server_id";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($server_ids)), ...$server_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $campaign_clics = [];
    while ($row = $result->fetch_assoc()) {
        $total_clics += $row['total_clics'];
    }

    // Obtenir les impressions de campagnes
    $sql = "SELECT COUNT(*) AS total_impressions, campaign_id FROM campaigns_impression WHERE server_id IN ($server_ids_placeholder) GROUP BY campaign_id";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($server_ids)), ...$server_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $campaign_impressions = [];
    while ($row = $result->fetch_assoc()) {
        $campaign_id = $row['campaign_id'];
        if (!isset($campaign_impressions[$campaign_id])) {
            $campaign_impressions[$campaign_id] = 0;
        }
        $campaign_impressions[$campaign_id] += $row['total_impressions'];
        $total_impressions += $row['total_impressions'];
    }

    // Calculer les revenus
    foreach ($campaign_impressions as $campaign_id => $total_impressions_for_campaign) {
        $sql = "SELECT objectif_cpv FROM campaigns WHERE campaign_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $objectif_cpv = $row['objectif_cpv'] ?? 0;

        $revenu = ($total_impressions_for_campaign / 1000) * $objectif_cpv;
        $revenu_total += $revenu;
    }

    // Obtenir les campagnes souscrites
    $sql = "SELECT DISTINCT campaign_id FROM campaigns_impression WHERE server_id IN ($server_ids_placeholder)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($server_ids)), ...$server_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $campaign_ids = [];

    while ($row = $result->fetch_assoc()) {
        $campaign_ids[] = $row['campaign_id'];
    }

    if (!empty($campaign_ids)) {
        $campaign_ids_placeholder = implode(',', array_fill(0, count($campaign_ids), '?'));

        $sql = "SELECT campaign_id, nom, impression_total, objectif_cpv, date_debut, logo_url FROM campaigns WHERE campaign_id IN ($campaign_ids_placeholder)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($campaign_ids)), ...$campaign_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        $campaign_details = [];
        while ($row = $result->fetch_assoc()) {
            $campaign_id = $row['campaign_id'];
            $impressions_diffusees = $campaign_impressions[$campaign_id] ?? 0;
            $impressions_restantes = $row['impression_total'] - $impressions_diffusees;

            $status = $impressions_restantes > 0 ? 'Active' : 'Inactive';

            $row['impression_restante'] = max($impressions_restantes, 0);
            $row['status'] = $status;
            $campaign_details[] = $row;
        }
    }

    // Générer les données mensuelles pour les impressions
    $impressions_mensuelles = array_fill(0, 12, 0); // Tableau pour les impressions mensuelles (Jan-Déc)
    $sql = "SELECT MONTH(created_at) AS mois, COUNT(*) AS impressions_count 
            FROM campaigns_impression 
            WHERE server_id IN ($server_ids_placeholder) 
            GROUP BY mois";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($server_ids)), ...$server_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $mois = (int) $row['mois'] - 1; // Mois 1-12 à index 0-11
        $impressions_mensuelles[$mois] = (int) $row['impressions_count'];
    }

    // Convertir les données d'impressions pour le JavaScript
    $impressions_data = json_encode($impressions_mensuelles);

    $sql = "SELECT server_id, DATE(created_at) AS date, COUNT(*) AS message_count 
            FROM campaigns_messages 
            WHERE server_id IN ($server_ids_placeholder) 
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
            GROUP BY server_id, DATE(created_at)
            ORDER BY server_id, DATE(created_at)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($server_ids)), ...$server_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $message_data = [];
    while ($row = $result->fetch_assoc()) {
        $server_id = $row['server_id'];
        $date = $row['date'];
        $message_count = $row['message_count'];

        if (!isset($message_data[$server_id])) {
            $message_data[$server_id] = [
                'dates' => [],
                'counts' => []
            ];
        }

        $message_data[$server_id]['dates'][] = $date;
        $message_data[$server_id]['counts'][] = $message_count;
    }

    $sql = "SELECT DATE(created_at) AS creation_date, COUNT(*) AS server_count 
            FROM servers_minecraft 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
            GROUP BY DATE(created_at)";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $server_creation_data = [];
    while ($row = $result->fetch_assoc()) {
        $server_creation_data[$row['creation_date']] = $row['server_count'];
    }
}

// Calculer le taux de conversion
$taux_conversion = $total_impressions > 0 ? ($total_clics / $total_impressions) * 100 : 0;

$con->close();
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
    <link href="css/styles.css" rel="stylesheet" />
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
                                        Tableaux de bord
                                    </h1>
                                    <div class="page-header-subtitle">Analysé vos données les plus essentielles en un
                                        coup d'oeil</div>
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
                                            <h5>Interface Intuitive et Efficace</h5>
                                            <div class="text-muted small">Accédez à vos données en toute simplicité et
                                                efficacité.</div>
                                        </div>
                                        <img src="assets/img/illustrations/browser-stats.svg" alt="..."
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
                                            <h5>Sécurité Avancée</h5>
                                            <div class="text-muted small">Nous veillons à la sécurité des serveurs
                                                diffusant les campagnes.</div>
                                        </div>
                                        <img src="assets/img/illustrations/processing.svg" alt="..."
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
                                            <h5>Découvrez le Moderne</h5>
                                            <div class="text-muted small">Passez aux dernières innovations publicitaires
                                                et adoptez la modernité.</div>
                                        </div>
                                        <img src="assets/img/illustrations/windows.svg" alt="..." style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Custom page header alternative example -->
                    <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-4">
                        <div class="me-4 mb-3 mb-sm-0">
                            <?php echo "<div class='small'><span class='fw-500 text-primary'>" . (new DateTime())->format('l') . "</span> · " . (new DateTime())->format('F d, Y') . " · " . (new DateTime())->format('h:i A') . "</div>"; ?>
                        </div>
                    </div>
                    <!-- Cards with statistics -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-dollar-sign fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">Revenu Total</h5>
                                        <p class="card-text fs-5 mb-0">
                                            <?php echo number_format($revenu_total, 4, ',', ' '); ?> €
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-eye fa-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">Total des Impressions</h5>
                                        <p class="card-text fs-5 mb-0">
                                            <?php echo number_format($total_impressions, 0, ',', ' '); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-click fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">Total des Clics</h5>
                                        <p class="card-text fs-5 mb-0">
                                            <?php echo number_format($total_clics, 0, ',', ' '); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 border-light shadow-sm">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-chart-line fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0">Taux de Conversion</h5>
                                        <p class="card-text fs-5 mb-0">
                                            <?php echo number_format($taux_conversion, 2, ',', ' '); ?>%
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques et contenu omis pour la concision -->
                    <div class="row">
                        <div class="col-xl-6 col-md-12 mb-4">
                            <div class="card h-100">
                                <div class="card-header">Graphique des Impressions et Clics</div>
                                <div class="card-body">
                                    <?php if ($total_impressions == 0 && $total_clics == 0): ?>
                                        <p>Aucune donnée encore disponible.</p>
                                    <?php else: ?>
                                        <canvas id="impressionsClicsChart"></canvas>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-12 mb-4">
                            <div class="card h-100">
                                <div class="card-header">Répartition des Revenus par Campagne</div>
                                <div class="card-body">
                                    <?php if (empty($campaign_details)): ?>
                                        <p>Aucune donnée encore disponible.</p>
                                    <?php else: ?>
                                        <canvas id="revenusChart"></canvas>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 mb-4">
                            <div class="card mb-4">
                                <div class="card-header">Impressions Mensuelles</div>
                                <div class="card-body">
                                    <?php if (empty($campaign_details)): ?>
                                        <p>Aucune donnée encore disponible.</p>
                                    <?php else: ?>
                                        <div class="chart-area">
                                            <canvas id="myAreaChart" width="756" height="240"
                                                style="display: block; width: 756px; height: 240px;"></canvas>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer small text-muted">Mise à jour le
                                    <?php echo (new DateTime())->format('F d, Y'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Autres sections si nécessaire -->
                </div>
            </main>
        </div>

    </div>



    <script data-cfasync="false" src="cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
    <script src="js/litepicker.js"></script>
    <script defer
        src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"8ae0190f4f846988","version":"2024.7.0","serverTiming":{"name":{"cfL4":true}},"token":"6e2c2575ac8f44ed824cef7899ba8463","b":1}'
        crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                // Configuration du graphique des impressions et des clics
                var ctx1 = document.getElementById('impressionsClicsChart');
                if (ctx1) {
                    ctx1 = ctx1.getContext('2d');
                    var impressionsClicsChart = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: ['Impressions', 'Clics'],
                            datasets: [{
                                label: 'Nombre Total',
                                data: [<?php echo $total_impressions; ?>,
                                    <?php echo $total_clics; ?>
                                ],
                                backgroundColor: ['rgba(54, 162, 235, 0.5)',
                                    'rgba(255, 99, 132, 0.5)'
                                ],
                                borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Configuration du graphique des revenus par campagne
                var ctx2 = document.getElementById('revenusChart');
                if (ctx2) {
                    ctx2 = ctx2.getContext('2d');
                    var revenusData = <?php echo json_encode(array_map(function ($campaign) {
                        return [
                            'nom' => $campaign['nom'],
                            'revenu' => ($campaign['impression_restante'] / 1000) * $campaign['objectif_cpv']
                        ];
                    }, $campaign_details ?? [])); ?>;

                    var revenusLabels = revenusData.map(item => item.nom);
                    var revenusValues = revenusData.map(item => item.revenu);

                    var revenusChart = new Chart(ctx2, {
                        type: 'pie',
                        data: {
                            labels: revenusLabels,
                            datasets: [{
                                data: revenusValues,
                                backgroundColor: ['rgba(255, 99, 132, 0.5)',
                                    'rgba(54, 162, 235, 0.5)', 'rgba(75, 192, 192, 0.5)',
                                    'rgba(153, 102, 255, 0.5)', 'rgba(255, 159, 64, 0.5)'
                                ],
                                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });
                }

                // Fonction pour obtenir la plage de dates
                function getDateRange() {
                    var startDate = new Date();
                    startDate.setDate(1);
                    startDate.setHours(0, 0, 0, 0);

                    var dates = [];
                    var endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0);

                    while (startDate <= endDate) {
                        dates.push(startDate.toISOString().split('T')[0]);
                        startDate.setDate(startDate.getDate() + 1);
                    }

                    return dates;
                }

                // Fonction pour générer une couleur aléatoire
                function getRandomColor() {
                    var letters = '0123456789ABCDEF';
                    var color = '#';
                    for (var i = 0; i < 6; i++) {
                        color += letters[Math.floor(Math.random() * 16)];
                    }
                    return color;
                }

            } catch (error) {
                console.error("Une erreur est survenue dans le script :", error);
            }
        });
    </script>

    <script>
        // Passer les données PHP au JavaScript
        var impressionsData = <?php echo $impressions_data; ?>;

        // Configurer le graphique
        var ctx = document.getElementById("myAreaChart").getContext('2d');
        var myLineChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Impressions",
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
                    data: impressionsData
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
                            // Inclure un signe dollar dans les ticks
                            callback: function (value) {
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
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || "";
                            return datasetLabel + " : " + number_format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        // Fonction pour le formatage des nombres
        function number_format(number, decimals, dec_point, thousands_sep) {
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
    </script>

</body>

</html>