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
                <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
                    <div class="container-xl px-4">
                        <div class="page-header-content">
                            <div class="row align-items-center justify-content-between pt-3">
                                <div class="col-auto mb-3">
                                    <h1 class="page-header-title">
                                        <div class="page-header-icon"><i data-feather="user"></i></div>
                                        Paramètres du compte - Profil
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- Contenu principal -->
                <div class="container-xl px-4 mt-4">
                    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/includes/account-navbar.php'; ?>
                    <hr class="mt-0 mb-4" />
                    <div class="row">
                        <div class="col-xl-4">
                            <!-- Carte de la photo de profil -->
                            <div class="card mb-4 mb-xl-0">
                                <div class="card-header">Photo de profil</div>
                                <div class="card-body text-center">
                                    <!-- Image de la photo de profil -->
                                    <img class="img-account-profile rounded-circle mb-2"
                                        src="<?php echo htmlspecialchars($acc_url); ?>" alt="" />
                                </div>
                            </div>

                            <br>

                            <div class="card mb-4">
                                <div class="card-header">Supprimer le compte</div>
                                <div class="card-body">
                                    <p style="text-align: justify;">
                                        La suppression de votre compte est une action permanente et irréversible.
                                        Si vous souhaitez procéder à la suppression de votre compte, veuillez contacter
                                        l'adresse e-mail suivante :
                                        <a
                                            href="mailto:adriendechocqueuse@icloud.com">adriendechocqueuse@icloud.com</a>.
                                    </p>
                                </div>
                            </div>

                        </div>
                        <div class="col-xl-8">
                            <!-- Carte des détails du compte -->
                            <div class="card mb-4">
                                <div class="card-header">Détails du compte</div>
                                <div class="card-body">
                                    <form action="#" method="POST">
                                        <!-- Ligne de formulaire -->
                                        <div class="row gx-3 mb-3">
                                            <!-- Champ adresse -->
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="acc_address">Nom d'utilisateur</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-user"></i>
                                                    </span>
                                                    <input class="form-control" id="acc_username" name="acc_username"
                                                        type="text" placeholder="Entrez votre nom d'utilisateur"
                                                        value="<?php echo htmlspecialchars($acc_username); ?>"
                                                        readonly />
                                                </div>
                                            </div>
                                            <!-- Champ email -->
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="acc_email">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-envelope"></i>
                                                    </span>
                                                    <input class="form-control" id="acc_email" name="acc_email"
                                                        type="email" placeholder="Entrez votre adresse e-mail"
                                                        value="<?php echo htmlspecialchars($acc_email); ?>" readonly />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Nouvelle section d'information -->
                                        <div class="alert alert-info mb-4" role="alert">
                                            <i class="fa fa-info-circle"></i>
                                            <small>Pour modifier une information liée à votre compte, veuillez envoyer
                                                un message à l'adresse suivante : adriendechocqueuse@icloud.com. Notre
                                                équipe
                                                traitera votre demande dans les plus brefs délais.</small>
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
    <script src="/dashboard/js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="/dashboard/assets/demo/chart-area-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-bar-demo.js"></script>
    <script src="/dashboard/assets/demo/chart-pie-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>

</body>

</html>