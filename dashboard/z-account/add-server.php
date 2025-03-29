<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit(); // Arrêter l'exécution du script
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
                <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
                    <div class="container-xl px-4">
                        <div class="page-header-content pt-4">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mt-4">
                                    <h1 class="page-header-title">
                                        <div class="page-header-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-activity">
                                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                            </svg>
                                        </div>
                                        Créez votre serveur
                                    </h1>
                                    <div class="page-header-subtitle">Remplissez les informations ci-dessous pour
                                        ajouter votre serveur à notre plateforme.</div>
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
                                            <h5>Ajoutez votre serveur</h5>
                                            <div class="text-muted small">Remplissez le formulaire pour inscrire votre
                                                serveur sur notre plateforme.</div>
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
                                            <h5>Obtenez plus de visibilité auprès des marques</h5>
                                            <div class="text-muted small">Ajoutez votre serveur et rejoignez des
                                                campagnes pour qu'il soit découvert par plus de sponsors.</div>
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
                                            <h5>Suivez vos statistiques</h5>
                                            <div class="text-muted small">Suivez les performances et les visites de
                                                votre serveur grâce à notre tableau de bord.</div>
                                        </div>
                                        <img src="/dashboard/assets/img/illustrations/statistics.svg" alt="..."
                                            style="width: 8rem">
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Contenu principal -->
                    <div class="container-xl px-4 mt-4">
                        <hr class="mt-0 mb-4" />
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card mb-4 mb-xl-0">
                                    <div class="card-body h-100 p-5">
                                        <div class="row align-items-center">
                                            <div class="col-xl-8 col-xxl-12">
                                                <div
                                                    class="text-center text-xl-start text-xxl-center mb-4 mb-xl-0 mb-xxl-4">
                                                    <h1 class="text-primary">Enregistrez votre serveur</h1>
                                                    <p class="text-gray-700 mb-0">Fournissez les informations requises
                                                        sur
                                                        votre serveur et profitez-en.</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-xxl-12 text-center"><img class="img-fluid"
                                                    src="/dashboard/assets/img/illustrations/at-work.svg"
                                                    style="max-width: 26rem">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="card mb-4 mb-xl-0">
                                    <div class="card-header">Information(s)</div>
                                    <div class="card-body text-center">

                                        <div class="card-body">
                                            <p style="text-align: justify;">Nous vous demandons de nous fournir
                                                certaines
                                                informations sur votre serveur. Vous pourrez les modifier ultérieurement
                                                dans la
                                                page de profil du serveur.</p>
                                        </div>
                                    </div>
                                </div>
                                <br>
                            </div>

                            <div class="col-xl-8">
                                <div class="alert alert-info mb-4" role="alert">
                                    <svg class="svg-inline--fa fa-circle-info" aria-hidden="true" focusable="false"
                                        data-prefix="fas" data-icon="circle-info" role="img"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                        <path fill="currentColor"
                                            d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z">
                                        </path>
                                    </svg><!-- <i class="fa fa-info-circle"></i> Font Awesome fontawesome.com -->
                                    <small>Pour prévenir les abus, vous pouvez créer jusqu'à deux serveurs par personne
                                        par plateforme
                                        chaque mois.</small>
                                </div>

                                <!-- Carte des détails du compte -->
                                <div class="card mb-4">
                                    <div class="card-header">Détails du serveur</div>
                                    <div class="card-body">
                                        <form action="/dashboard/z-account/add-server-update.php" method="POST">
                                            <!-- Champ du nom du serveur -->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputServerName">Nom du Serveur</label>
                                                <input class="form-control" id="inputServerName" name="inputServerName"
                                                    type="text" placeholder="Entrez le nom du serveur"
                                                    value="Exemple Valeria Faction" />
                                            </div>

                                            <!-- Champ catégorie du serveur -->
                                            <div class="mb-3">
                                                <label class="form-label" for="inputServerCategory">Catégorie du
                                                    Serveur</label>
                                                <input class="form-control" id="inputServerCategory"
                                                    name="inputServerCategory" type="text"
                                                    placeholder="Entrez la catégorie du serveur" value="Serveur RP" />
                                            </div>

                                            <!-- Champ site web du serveur -->
                                            <div class="mb-3">
                                                <label class="form-label" for="inputServerWebsite">Site réunissant la
                                                    comunauté (Site internet, discord, TeamSpeak, ...)</label>
                                                <input class="form-control" id="inputServerWebsite"
                                                    name="inputServerWebsite" type="url"
                                                    placeholder="Entrez l'URL du site web du serveur"
                                                    value="https://www.exemple.com" />
                                            </div>

                                            <!-- Sélecteur pour Discord ou Minecraft -->
                                            <div class="mb-3">
                                                <label class="form-label" for="inputPlatform">Choisissez une
                                                    plateforme</label>
                                                <select class="form-control" id="inputPlatform" name="inputPlatform">
                                                    <option value="minecraft">Minecraft</option>
                                                    <option value="discord">Discord</option>
                                                </select>
                                            </div>

                                            <hr class="my-4" />
                                            <div class="d-flex justify-content-between">
                                                <button class="btn btn-primary" type="submit">Créer</button>
                                            </div>
                                        </form>

                                    </div>
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
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="/dashboard/assets/demo/chart-area-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-bar-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-pie-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
    <script defer
        src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"8ae0190f4f846988","version":"2024.7.0","serverTiming":{"name":{"cfL4":true}},"token":"6e2c2575ac8f44ed824cef7899ba8463","b":1}'
        crossorigin="anonymous"></script>
</body>

</html>