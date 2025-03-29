<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit(); // Arrêter l'exécution du script
}

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

// Fermer la connexion
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
    <meta property="og:image" content="https://www.adnow.online/dashboard/assets/og-image.png">
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
                                        Gestion de vos serveurs
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
                            <!-- Dashboard example card 1-->
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
                                            <h5>Création & Liaison</h5>
                                            <div class="text-muted small">Connectez vos serveurs de jeu en un temps
                                                record.</div>
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
                                                class="feather feather-edit feather-xl text-primary mb-3">
                                                <path d="M12 20h9"></path>
                                                <path
                                                    d="M16.24 4.76a1.5 1.5 0 0 1 2.12 2.12l-12 12a1.5 1.5 0 0 1-2.12-2.12l12-12z">
                                                </path>
                                                <path d="M14.5 5.5L9 11l-2-2 5.5-5.5L14.5 5.5z"></path>
                                            </svg>
                                            <h5>Modification Instantanée</h5>
                                            <div class="text-muted small">Apportez des modifications à vos serveurs en
                                                quelques secondes.</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/data-report.svg" alt="..."
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
                                            <h5>Collecte d'Informations</h5>
                                            <div class="text-muted small">Recueillez des données sur vos serveurs pour
                                                optimiser vos serveurs.</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/statistics.svg" alt="..."
                                            style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>

                    <!-- Server Cards -->
                    <div class="row">
                        <?php if (isset($servers) && is_array($servers) && !empty($servers)): ?>
                            <?php foreach ($servers as $server): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card position-relative">
                                        <div class="card-body text-center">
                                            <div class="position-relative">
                                                <?php
                                                $logoUrl = !empty($server['logo_url']) ? $server['logo_url'] : 'https://www.svgrepo.com/show/349453/minecraft.svg';
                                                ?>
                                                <img src="<?php echo htmlspecialchars($logoUrl); ?>"
                                                    class="avatar rounded-circle mx-auto d-block" alt="Logo"
                                                    style="width: 100px; height: 100px; object-fit: cover;">
                                                <h5 class="card-title mt-2"><?php echo htmlspecialchars($server['nom']); ?>
                                                </h5>
                                                <div class="position-absolute top-0 start-0 p-2">
                                                    <small
                                                        class="text-muted"><?php echo htmlspecialchars((new DateTime($server['created_at']))->format('d M Y')); ?></small>
                                                </div>
                                                <p class="text-muted mb-1">
                                                    <?php
                                                    if (!empty($server['games'])) {
                                                        switch ($server['games']) {
                                                            case 1:
                                                                echo 'Minecraft Java Edition';
                                                                break;
                                                            case 2:
                                                                echo 'Discord';
                                                                break;
                                                            default:
                                                                echo 'Non défini';
                                                        }
                                                    } else {
                                                        echo 'Non défini';
                                                    }
                                                    ?>
                                                </p>

                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <?php if (!empty($server['activate_date'])): ?>
                                                        <span class="badge bg-success">Activé</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Désactivé</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <a class="btn btn-outline-primary"
                                                href="/dashboard/z-minecraft/server-profile.php?id=<?php echo htmlspecialchars($server['id']); ?>">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    Aucun serveur trouvé.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script data-cfasync="false" src="cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="/dashboard/assets/demo/chart-area-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-bar-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-pie-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
    <script src="js/litepicker.js"></script>
    <script defer
        src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"8ae0190f4f846988","version":"2024.7.0","serverTiming":{"name":{"cfL4":true}},"token":"6e2c2575ac8f44ed824cef7899ba8463","b":1}'
        crossorigin="anonymous"></script>
</body>

</html>