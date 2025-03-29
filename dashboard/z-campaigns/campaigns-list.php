<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit(); // Arrêter l'exécution du script
}

$user_id = $_SESSION['user_id'];

// Obtenir les informations des campagnes pour l'utilisateur
$sql = "SELECT * FROM campaigns WHERE users_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$campaigns_result = $stmt->get_result();
$campaigns = [];

while ($row = $campaigns_result->fetch_assoc()) {
    $campaigns[] = $row;
}

// Fermer la connexion
$con->close();
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"
        crossorigin="anonymous"></script>
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
                                        Gestion de vos campagnes publicitaires
                                    </h1>
                                    <div class="page-header-subtitle">Gérer les informations de vos serveurs en temsp
                                        réel</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main page content-->
                <div class="container-xl px-4 mt-n10">
                    <div class="row">
                        <div class="col-xl-4 mb-4">
                            <!-- Card 1: Lancer une Campagne -->
                            <a class="card lift h-100" href="#!">
                                <div class="card-body d-flex justify-content-center flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="me-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-box feather-xl text-primary mb-3">
                                                <path d="M12 2L2 7v10l10 5 10-5V7z"></path>
                                                <path d="M12 22v-6"></path>
                                                <path d="M22 7v10l-10 5-10-5V7"></path>
                                            </svg>
                                            <h5>Créez Votre Campagne</h5>
                                            <div class="text-muted small">Lancez des campagnes publicitaires
                                                engageantes. C'est simple et rapide!</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/browser-stats.svg"
                                            alt="Créez Votre Campagne" style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-4 mb-4">
                            <!-- Card 2: Modifiez à Tout Moment -->
                            <a class="card lift h-100" href="#!">
                                <div class="card-body d-flex justify-content-center flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="me-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-edit feather-xl text-primary mb-3">
                                                <path d="M12 20h9"></path>
                                                <path
                                                    d="M16.24 4.76a1.5 1.5 0 0 1 2.12 2.12l-12 12a1.5 1.5 0 0 1-2.12-2.12l12-12z">
                                                </path>
                                                <path d="M14.5 5.5L9 11l-2-2 5.5-5.5L14.5 5.5z"></path>
                                            </svg>
                                            <h5>Modifications Instantanées</h5>
                                            <div class="text-muted small">Ajustez vos campagnes en temps réel pour
                                                maximiser leur impact. Tout est sous votre contrôle!</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/data-report.svg"
                                            alt="Modifications Instantanées" style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-4 mb-4">
                            <!-- Card 3: Suivi et Optimisation -->
                            <a class="card lift h-100" href="#!">
                                <div class="card-body d-flex justify-content-center flex-column">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="me-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-database feather-xl text-primary mb-3">
                                                <path
                                                    d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6z">
                                                </path>
                                                <path
                                                    d="M4 12a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2z">
                                                </path>
                                                <path
                                                    d="M4 18a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2z">
                                                </path>
                                            </svg>
                                            <h5>Suivi et Optimisation</h5>
                                            <div class="text-muted small">Surveillez les performances de vos campagnes
                                                et ajustez-les pour obtenir les meilleurs résultats.</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/statistics.svg"
                                            alt="Suivi et Optimisation" style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>


                    <!-- Campaign Cards -->
                    <div class="row">
                        <?php if (isset($campaigns) && is_array($campaigns) && !empty($campaigns)): ?>
                            <?php foreach ($campaigns as $campaign): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card shadow-sm border-0 rounded-lg mb-3">
                                        <div class="card-header bg-white p-0 position-relative">
                                            <?php
                                            $logoUrl = !empty($campaign['logo_url']) ? $campaign['logo_url'] : 'https://www.svgrepo.com/show/210651/empty-set-slash.svg';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($logoUrl); ?>"
                                                class="card-img-top rounded-top logo-opacity" alt="Logo de la campagne"
                                                style="object-fit: cover; height: 150px; filter: blur(2px);">
                                        </div>
                                        <div class="card-body py-3 px-4">
                                            <h5 class="card-title text-primary mb-3">
                                                <?php echo htmlspecialchars($campaign['nom']); ?>
                                            </h5>
                                            <p class="card-text text-muted small mb-3">
                                                <?php echo htmlspecialchars(!empty($campaign['description']) ? $campaign['description'] : 'Pas de description'); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span
                                                    class="badge bg-secondary"><?php echo htmlspecialchars((new DateTime($campaign['date_debut']))->format('d M Y')); ?></span>
                                                <span class="text-success fw-bold">Budget:
                                                    <?php echo htmlspecialchars($campaign['budget']) . ' €'; ?></span>
                                            </div>
                                        </div>
                                        <!-- Bouton Vos Statistiques avec plus de marge en bas -->
                                        <div class="card-footer bg-white border-0 p-2 pt-0 mb-3">
                                            <a class="btn btn-outline-primary btn-lg d-block"
                                                href="/dashboard/z-campaigns/my-campaigns.php?id=<?php echo htmlspecialchars($campaign['campaign_id']); ?>"
                                                style="margin: 0 auto; width: calc(100% - 20px);">
                                                <i class="fas fa-chart-line"></i> Vos statistiques
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    Aucune campagne trouvée.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script data-cfasync="false" src="cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="/dashboard/js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="/dashboard/assets/demo/chart-area-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-bar-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-pie-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
    <script src="/dashboard/js/litepicker.js"></script>

</body>

</html>