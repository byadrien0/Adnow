<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit(); // Arrêter l'exécution du script
}

// Récupérer l'historique des retraits
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM withdrawal WHERE user_id_withdrawal = ? ORDER BY created_at_withdrawal DESC";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$withdrawals = $result->fetch_all(MYSQLI_ASSOC);

// Déterminer la date du dernier retrait
$last_withdrawal_date = !empty($withdrawals) ? date('d F Y', strtotime($withdrawals[0]['created_at_withdrawal'])) : 'Aucun retrait effectué';

// Fermer la connexion
$stmt->close();
$con->close();

// Pour la méthode de retrait
$payment_method = "Virement bancaire";
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
                                        <div class="page-header-icon"><i data-feather="user"></i></div>
                                        Paramètres du compte - Retrait
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- Main page content-->
                <div class="container-xl px-4 mt-4">
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/includes/account-navbar.php'; ?>
                    <hr class="mt-0 mb-4" />

                    <!-- Alerte de solde minimum -->
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <small>La solde minimum de retrait est de 15€.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <!-- Carte de solde disponible -->
                            <div class="card h-100 border-start-lg border-start-primary">
                                <div class="card-body">
                                    <div class="small text-muted">Solde disponible</div>
                                    <div class="h3"><?php echo htmlspecialchars($acc_money); ?>€</div>
                                    <a class="text-arrow-icon small" href="/dashboard/account-withdrawals-updated.php">
                                        Retirer des fonds
                                        <i data-feather="arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <!-- Carte de retrait récent -->
                            <div class="card h-100 border-start-lg border-start-secondary">
                                <div class="card-body">
                                    <div class="small text-muted">Dernier retrait le</div>
                                    <div class="h3"><?php echo htmlspecialchars($last_withdrawal_date); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <!-- Carte d'options de retrait -->
                            <div class="card h-100 border-start-lg border-start-success">
                                <div class="card-body">
                                    <div class="small text-muted">Méthode de retrait</div>
                                    <div class="h3 d-flex align-items-center">
                                        <?php echo htmlspecialchars($payment_method); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Méthodes de retrait -->
                    <div class="card card-header-actions mb-4">
                        <div class="card-header">
                            Méthodes de Retrait
                        </div>
                        <div class="card-body px-0">
                            <!-- Méthode de retrait unique affichée ici -->
                            <div class="d-flex align-items-center justify-content-between px-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-wallet fa-2x cc-color-wallet"></i>
                                    <div class="ms-4">
                                        <div class="small"><?php echo htmlspecialchars($payment_method); ?> -
                                            Informations sur le compte</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historique des retraits -->
                    <div class="card mb-4" id="withdrawal-history">
                        <div class="card-header">Historique des Retraits</div>
                        <div class="card-body p-0">
                            <!-- Tableau d'historique des retraits -->
                            <div class="table-responsive table-withdrawal-history">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="border-gray-200" scope="col">ID de Transaction</th>
                                            <th class="border-gray-200" scope="col">Date</th>
                                            <th class="border-gray-200" scope="col">Montant</th>
                                            <th class="border-gray-200" scope="col">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($withdrawals as $withdrawal): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($withdrawal['withdrawal_id']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($withdrawal['created_at_withdrawal'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($withdrawal['amount_withdrawal']); ?>€</td>
                                                <td>
                                                    <?php
                                                    switch ($withdrawal['status_withdrawal']) {
                                                        case 'pending':
                                                            echo '<span class="badge bg-light text-dark">En attente</span>';
                                                            break;
                                                        case 'approved':
                                                            echo '<span class="badge bg-success">Complété</span>';
                                                            break;
                                                        case 'rejected':
                                                            echo '<span class="badge bg-danger">Rejeté</span>';
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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
</body>

</html>