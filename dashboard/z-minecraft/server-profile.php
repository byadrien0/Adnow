<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Rediriger vers la page de connexion
    exit(); // Arrêter l'exécution du script
}

// Récupérer l'ID du serveur depuis l'URL
$serverId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialiser les variables pour les informations du serveur
$serverName = "";
$serverCategory = "";
$serverIp = "";
$serverPort = "";
$pluginVersion = "";
$serverVersion = "";
$serverStatus = "";
$serverToken = "";
$serverWebsite = "";

// Fonction pour éviter les problèmes avec htmlspecialchars() en cas de null
function safe_htmlspecialchars($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Vérifier si l'ID du serveur est valide
if ($serverId > 0) {

    // Préparer la requête SQL pour obtenir les informations du serveur
    $sql = "SELECT * FROM servers_minecraft WHERE id = ? AND user_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $serverId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérifier si le serveur existe et appartient à l'utilisateur
    if ($result->num_rows > 0) {
        $server = $result->fetch_assoc();
        $serverId = safe_htmlspecialchars($server['id']);
        $serverName = safe_htmlspecialchars($server['nom']);
        $serverCategory = safe_htmlspecialchars($server['category']);
        $serverIp = safe_htmlspecialchars($server['adresse_ip']);
        $serverPort = safe_htmlspecialchars($server['port']);
        $pluginVersion = !empty(safe_htmlspecialchars($server['plugin_version'])) ? safe_htmlspecialchars($server['plugin_version']) : "0.0";
        $serverVersion = safe_htmlspecialchars($server['server_version']);
        $serverStatus = safe_htmlspecialchars($server['activate_date']); // Juste un exemple, ajustez selon votre logique
        $serverToken = safe_htmlspecialchars($server['token']);
        $serverWebsite = safe_htmlspecialchars($server['website']);
        $serverGames = safe_htmlspecialchars($server['games']);
    } else {
        // Si le serveur n'existe pas ou n'appartient pas à l'utilisateur, rediriger vers /dashboard/index.php
        header("Location: /dashboard/index.php");
        exit(); // Arrêter l'exécution du script
    }


} else {
    // Si l'ID du serveur n'est pas valide, rediriger vers /dashboard/index.php
    header("Location: /dashboard/index.php");
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
    <style>
        .token-container {
            position: relative;
        }

        .token-container input {
            width: calc(100% - 30px);
            display: inline-block;
        }

        .token-container button {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            border: none;
            background: transparent;
            cursor: pointer;
        }
    </style>
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
                                        <div class="page-header-icon"><i class="fas fa-cogs"></i></div>
                                        Paramètres du serveur - <?php echo $serverName; ?>
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
                                <div class="card-header">Photo du Serveur</div>
                                <div class="card-body text-center">
                                    <?php if (!empty($serverStatus)) { ?>
                                        <!-- Profile picture image -->
                                        <img class="img-account-profile rounded-circle mb-2"
                                            src="<?php echo $server['logo_url'] ?: 'assets/img/illustrations/profiles/profile-1.png'; ?>"
                                            alt="" />
                                    <?php } ?>
                                    <div class="small font-italic text-muted mb-4">
                                        <?php if (empty($serverStatus)) { ?>
                                            L'image s'affichera une fois que votre serveur sera connecté.
                                        <?php } else { ?>
                                            Icône de votre serveur.
                                        <?php } ?>
                                    </div>
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
                            <!-- Account details card -->
                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">Informations Générales</div>
                                <div class="card-body">

                                    <?php if (empty($serverStatus)) { ?>

                                        <div class="alert alert-info mb-4" role="alert">
                                            <svg class="svg-inline--fa fa-circle-info" aria-hidden="true" focusable="false"
                                                data-prefix="fas" data-icon="circle-info" role="img"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                                <path fill="currentColor"
                                                    d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z">
                                                </path>
                                            </svg><!-- <i class="fa fa-info-circle"></i> Font Awesome fontawesome.com -->
                                            <small>Pour configurer votre serveur, installez d'abord le plugin à la dernière
                                                version en <a
                                                    href="/plugins/minecraft/releases/AdNow-1.0.2-SNAPSHOT.jar">cliquant
                                                    ici</a>, puis ajoutez le token du serveur
                                                dans le fichier <code>config.yml</code>.</small>

                                        </div>

                                    <?php } ?>

                                    <div class="row">

                                        <?php if (!empty($serverStatus)) { ?>

                                            <?php


                                            // URL pour récupérer la version du plugin
                                            $url = "https://adnow.online/plugins/minecraft/get_plugin_version.php";

                                            // Récupérer le contenu XML de l'URL
                                            $xmlContent = file_get_contents($url);

                                            if ($xmlContent === FALSE) {
                                                die('Erreur lors de la récupération des données XML.');
                                            }

                                            // Parser le contenu XML
                                            $xml = new SimpleXMLElement($xmlContent);

                                            // Récupérer la version depuis le XML
                                            $remoteVersion = (string) $xml->version;

                                            ?>

                                            <?php if ($pluginVersion !== $remoteVersion) { ?>

                                                <div class="alert alert-danger mb-4" role="alert">
                                                    <svg class="svg-inline--fa fa-circle-info" aria-hidden="true"
                                                        focusable="false" data-prefix="fas" data-icon="circle-info" role="img"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                        data-fa-i2svg="">
                                                        <path fill="currentColor"
                                                            d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z">
                                                        </path>
                                                    </svg>
                                                    <!-- <i class="fa fa-info-circle"></i> Font Awesome fontawesome.com -->
                                                    <small>
                                                        Votre plugin est obsolète (<?= $pluginVersion ?>). Veuillez le mettre à
                                                        jour (<?= $remoteVersion ?>) pour continuer à
                                                        utiliser les services de notre site.
                                                        <a href="/plugins/minecraft/releases/AdNow-1.0.2-SNAPSHOT.jar"
                                                            class="alert-link">Cliquez ici pour
                                                            télécharger la version la plus récente.</a>
                                                    </small>
                                                </div>

                                            <?php } ?>


                                            <!-- Colonne 1 -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Version du
                                                        Serveur :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?php echo $serverVersion; ?>
                                                    </p>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-plug"></i> Version du plugin
                                                        :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?php echo $pluginVersion; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- Colonne 2 -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Jeux
                                                        :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?php echo ($serverGames == 1) ? "Minecraft Java Edition" : "What are you doing here?"; ?>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label"><i class="fas fa-hourglass-start"></i> Serveur
                                                        relié le :</label>
                                                    <p class="form-control-plaintext bg-light p-2 rounded">
                                                        <?php echo $serverStatus; ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"><i class="fas fa-server"></i> SERVEUR IP + PORT
                                                    :</label>
                                                <p class="form-control-plaintext bg-light p-2 rounded">
                                                    <?php echo $serverIp . ':' . $serverPort; ?>
                                                </p>
                                            </div>


                                        <?php } ?>

                                        <div class="mb-3 token-container">
                                            <label class="form-label"><i class="fas fa-key"></i> Token du Serveur
                                                :</label>
                                            <input id="tokenInput" class="form-control-plaintext bg-light p-2 rounded"
                                                type="password" value="<?php echo $serverToken; ?>" readonly />
                                            <button type="button" onclick="toggleToken()" id="toggleButton"
                                                style="margin-top: 17px"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-4">
                                <div class="card-header">Détails du Serveur</div>
                                <div class="card-body">
                                    <form method="POST" action="/dashboard/z-minecraft/server-profile-update.php">
                                        <!-- Form Group (username) -->
                                        <div class="mb-3">
                                            <label class="small mb-1" for="inputServerName">Nom du Serveur</label>
                                            <input class="form-control" id="inputServerName" name="serverName"
                                                type="text" placeholder="Entrez le nom du serveur"
                                                value="<?php echo $serverName; ?>" />
                                        </div>
                                        <!-- Form Row -->
                                        <div class="row gx-3 mb-3">
                                            <!-- Form Group (first name) -->
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputServerCategory">Catégorie</label>
                                                <input class="form-control" id="inputServerCategory"
                                                    name="serverCategory" type="text"
                                                    placeholder="Entrez la catégorie du serveur"
                                                    value="<?php echo $serverCategory; ?>" />
                                            </div>
                                            <!-- Form Group (last name) -->
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputServerWebsite">Website</label>
                                                <input class="form-control" id="inputServerWebsite" name="serverWebsite"
                                                    type="url" placeholder="Entrez l'URL du site web du serveur"
                                                    value="<?php echo $serverWebsite; ?>" />
                                            </div>
                                        </div>
                                        <input id="serverId" name="serverId" type="number"
                                            value="<?php echo $serverId; ?>" hidden />

                                        <!-- Save changes button -->
                                        <button class="btn btn-primary" type="submit">Enregistrer les
                                            modifications</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    if ($serverStatus) {
                        $query = "
                        SELECT 
                            c.campaign_id, 
                            c.budget, 
                            c.objectif_cpv, 
                            c.impression_total, 
                            c.description, 
                            c.date_debut, 
                            c.logo_url, 
                            c.nom, 
                            COALESCE(COUNT(i.id), 0) AS impressions_realisees, 
                            c.impression_total - COALESCE(COUNT(i.id), 0) AS impressions_restantes, 
                            CASE 
                                WHEN c.date_debut > NOW() THEN 'not start'
                                WHEN c.impression_total - COALESCE(COUNT(i.id), 0) <= 0 THEN 'finish'
                                ELSE 'start'
                            END AS campagne_info, 
                            CASE 
                                WHEN c.date_debut > NOW() THEN DATEDIFF(c.date_debut, NOW()) 
                                WHEN c.impression_total - COALESCE(COUNT(i.id), 0) <= 0 THEN -1 
                                ELSE 0  
                            END AS days_remaining,
                            CASE 
                                WHEN EXISTS (
                                    SELECT 1 
                                    FROM campaigns_diffusions cd
                                    WHERE cd.campaigns_id = c.campaign_id
                                    AND cd.server_id = ?
                                    AND cd.app = 'minecraft'
                                ) THEN 'yes'
                                ELSE 'no'
                            END AS is_server_registered
                        FROM 
                            campaigns c
                            LEFT JOIN campaigns_impression i ON c.campaign_id = i.campaign_id
                        WHERE 
                            c.app_minecraft = 'yes'
                        GROUP BY 
                            c.campaign_id
                        ORDER BY 
                            CASE 
                                WHEN c.date_debut > NOW() THEN 1 
                                WHEN c.impression_total - COALESCE(COUNT(i.id), 0) <= 0 THEN 2 
                                ELSE 0 
                            END, 
                            c.date_debut ASC, 
                            impressions_restantes ASC;
                        ";

                        // Préparation de la requête
                        $stmt = mysqli_prepare($con, $query);
                        if (!$stmt) {
                            die('Erreur préparation requête : ' . mysqli_error($con));
                        }

                        // Lier les paramètres
                        mysqli_stmt_bind_param($stmt, 'i', $serverId);

                        // Exécution de la requête
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if (!$result) {
                            die('Erreur exécution requête : ' . mysqli_error($con));
                        }

                        // Traitement des résultats
                        $campaign_details = [];
                        while ($row = mysqli_fetch_assoc($result)) {
                            $is_registered = $row['is_server_registered'] === 'yes';
                            $campaign_details[] = [
                                'campaign_id' => $row['campaign_id'],
                                'nom' => $row['nom'] ?? 'Nom non disponible',
                                'description' => $row['description'],
                                'logo_url' => $row['logo_url'] ?? 'default-logo.png',
                                'impressions_restantes' => $row['impressions_restantes'],
                                'objectif_cpv' => $row['objectif_cpv'],
                                'campagne_info' => $row['campagne_info'],
                                'days_remaining' => $row['days_remaining'],
                                'is_registered' => $is_registered,
                            ];
                        }
                        ?>

                        <!-- Affichage des campagnes -->
                        <div class="card mb-4">
                            <div class="card-header">Campagnes disponibles</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-center">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Logo</th>
                                                <th>Nom</th>
                                                <th>Status</th>
                                                <th>CPM</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($campaign_details)): ?>
                                                <?php foreach ($campaign_details as $campaign): ?>
                                                    <tr class="align-middle">
                                                        <td style="width: 120px;">
                                                            <img src="<?php echo htmlspecialchars($campaign['logo_url']); ?>"
                                                                class="img-fluid" alt="Logo de la campagne"
                                                                style="max-height: 50px;">
                                                        </td>
                                                        <td>
                                                            <h5 class="mb-1"><?php echo htmlspecialchars($campaign['nom']); ?></h5>
                                                        </td>
                                                        <td>
                                                            <?php if ($campaign['campagne_info'] === 'not start'): ?>
                                                                <p class="text-muted mb-1">Début dans
                                                                    <strong><?php echo htmlspecialchars($campaign['days_remaining']); ?>
                                                                        jour(s)</strong>
                                                                </p>
                                                            <?php elseif ($campaign['campagne_info'] === 'finish'): ?>
                                                                <p class="text-muted mb-1">Terminée</p>
                                                            <?php else: ?>
                                                                <p class="text-muted mb-1">Impressions :
                                                                    <strong><?php echo htmlspecialchars($campaign['impressions_restantes']); ?></strong>
                                                                </p>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="fw-bold"><?php echo number_format($campaign['objectif_cpv'], 2, ',', ' '); ?>€</span>
                                                        </td>
                                                        <td>
                                                            <?php if ($campaign['is_registered']): ?>
                                                                <a href="#" class="btn btn-secondary btn-sm disabled">Déjà inscrit</a>
                                                            <?php else: ?>
                                                                <a href="/dashboard/z-campaigns/join-campaign.php?campagne=<?php echo $campaign['campaign_id']; ?>&app=discord&server_id=<?php echo $serverId; ?>"
                                                                    class="btn btn-success btn-sm">Rejoindre</a>
                                                            <?php endif; ?>
                                                            <a href="/dashboard/z-campaigns/info-campaign.php?campaign_id=<?php echo $campaign['campaign_id']; ?>"
                                                                class="btn btn-info btn-sm">Détails</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="alert alert-warning text-center mb-0">
                                                            Aucune campagne trouvée.
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                    ?>


                </div>
            </main>
        </div>
    </div>
    <script data-cfasync="false" src="cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="js/scripts.js"></script>
    <script>
        function toggleToken() {
            var tokenInput = document.getElementById('tokenInput');
            var toggleButton = document.getElementById('toggleButton');
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                tokenInput.type = 'password';
                toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
            }
        }
    </script>
</body>

</html>