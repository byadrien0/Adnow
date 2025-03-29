<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit();
}

$serverId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];

$serverName = $serverCategory = $serverIcon = $serverToken = $serverWebsite = "";
$serverStatus = $serverRegion = $serverMemberCount = $serverDescription = "";
$serverCreationDate = $serverLogo = ""; // Nouvelle variable pour l'URL du logo

// Fonction pour éviter les problèmes avec htmlspecialchars() en cas de null
function safe_htmlspecialchars($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Remplacez le token par une variable d'environnement
$discordToken = getenv('DISCORD_BOT_TOKEN');

if ($serverId > 0) {
    $sql = "SELECT * FROM servers_discord WHERE id = ? AND user_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $serverId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $server = $result->fetch_assoc();
        $server_discord_Id = safe_htmlspecialchars($server['server_id']);
        $serverName = safe_htmlspecialchars($server['server_name']);
        $serverCategory = safe_htmlspecialchars($server['category']);
        $serverIcon = safe_htmlspecialchars($server['icon_url']);
        $serverToken = safe_htmlspecialchars($server['token']);
        $serverWebsite = safe_htmlspecialchars($server['website']);
        $serverCreationDate = safe_htmlspecialchars($server['created_at']);
        $activate_date = $server['activate_date'];
        $serverStatus = $server['activate_date'];

        // Récupérer les informations via l'API Discord avec 'with_counts=true'
        $discordApiUrl = "https://discord.com/api/v10/guilds/{$server_discord_Id}?with_counts=true";
        $headers = [
            "Authorization: Bot $discordToken",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $discordApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $serverDescription = "Erreur de cURL: " . curl_error($ch);
        } else {
            // Gérer différents codes HTTP
            switch ($httpCode) {
                case 200:
                    $serverInfo = json_decode($response, true);
                    error_log("Réponse complète de l'API : " . print_r($serverInfo, true)); // Débogage - réponse complète

                    // Vérification de l'existence du champ 'member_count'
                    if (isset($serverInfo['approximate_member_count'])) {
                        $serverMemberCount = safe_htmlspecialchars($serverInfo['approximate_member_count']);
                    } elseif (isset($serverInfo['member_count'])) {
                        $serverMemberCount = safe_htmlspecialchars($serverInfo['member_count']);
                    } else {
                        $serverMemberCount = 'Nombre de membres inconnu';
                        error_log("Champ 'member_count' ou 'approximate_member_count' introuvable dans la réponse : " . print_r($serverInfo, true));
                    }

                    if (isset($serverInfo['region'])) {
                        $serverRegion = safe_htmlspecialchars($serverInfo['region']);
                    } else {
                        $serverRegion = 'Région inconnue';
                    }

                    if (isset($serverInfo['description'])) {
                        $serverDescription = safe_htmlspecialchars($serverInfo['description']);
                    } else {
                        $serverDescription = 'Aucune description disponible';
                    }

                    // Récupérer l'URL du logo
                    if (isset($serverInfo['icon'])) {
                        $iconHash = safe_htmlspecialchars($serverInfo['icon']);
                        $serverLogo = "https://cdn.discordapp.com/icons/{$server_discord_Id}/{$iconHash}.png"; // Construire l'URL complète
                    } else {
                        $serverLogo = 'Aucun logo disponible';
                    }
                    break;

                case 401:
                    $serverDescription = "Erreur d'authentification : le token est invalide ou le bot n'est pas membre du serveur.";
                    break;

                case 404:
                    $serverDescription = "Serveur non trouvé : vérifiez l'ID du serveur.";
                    break;

                case 403:
                    $serverDescription = "Accès interdit : vérifiez les permissions du bot.";
                    break;

                case 500:
                    $serverDescription = "Erreur interne du serveur Discord. Veuillez réessayer plus tard.";
                    break;

                default:
                    $serverDescription = "Erreur de récupération des données du serveur Discord. Code HTTP : $httpCode.";
                    break;
            }
        }

        curl_close($ch);
    } else {
        header("Location: /dashboard/index.php");
        exit();
    }


} else {
    header("Location: /dashboard/index.php");
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
                                    <?php if (!empty($activate_date)) { ?>
                                    <!-- Profile picture image -->
                                    <img class="img-account-profile rounded-circle mb-2"
                                        src="<?php echo $serverLogo ?: 'assets/img/illustrations/profiles/profile-1.png'; ?>"
                                        alt="" />
                                    <?php } ?>
                                    <div class="small font-italic text-muted mb-4">
                                        <?php if (empty($activate_date)) { ?>
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
                                    <?php if (empty($activate_date)) { ?>
                                    <div class="alert alert-info mb-4" role="alert">
                                        <svg class="svg-inline--fa fa-circle-info" aria-hidden="true" focusable="false"
                                            data-prefix="fas" data-icon="circle-info" role="img"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z">
                                            </path>
                                        </svg>
                                        <small>Pour configurer votre serveur, commencez par installer le bot en <a
                                                href="https://discord.com/oauth2/authorize?client_id=1284105909898973236&scope=bot%20applications.commands&permissions=8"
                                                target="_blank">cliquant ici</a>, puis ajoutez executer la commande
                                            /token {VOTRE TOKEN}.</small>
                                    </div>

                                    <?php } ?>

                                    <div class="row">

                                        <?php if (!empty($activate_date)) { ?>

                                        <div class="alert alert-info mb-4" role="alert">
                                            <svg class="svg-inline--fa fa-circle-info" aria-hidden="true"
                                                focusable="false" data-prefix="fas" data-icon="circle-info" role="img"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                                data-fa-i2svg="">
                                                <path fill="currentColor"
                                                    d="M256 512A256 256 256 0 1 0 256 0a256 256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z">
                                                </path>
                                            </svg>
                                            <small>Pour commencer à diffuser des annonces, rendez-vous dans le canal où
                                                vous souhaitez les publier, puis exécutez la commande /setchannel.
                                                Choisissez le canal textuel dans lequel vous souhaitez diffuser les
                                                annonces.</small>
                                        </div>

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


                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="fas fa-plug"></i> Version du plugin
                                                :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <?php echo $remoteVersion; ?>
                                            </p>
                                        </div>

                                        <!-- Colonne 2 -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="fas fa-gamepad"></i> Jeux :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <?php echo "Discord"; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="fas fa-hourglass-start"></i> Serveur
                                                relié le :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <?php echo $serverStatus; ?>
                                            </p>
                                        </div>

                                        <!-- Colonne 3 -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="fas fa-user-friends"></i> Nombre de
                                                Membres :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <?php echo $serverMemberCount; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label"><i class="fas fa-globe"></i> Région du Serveur
                                                :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <?php echo $serverRegion; ?>
                                            </p>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label"><i class="fas fa-server"></i> Description du
                                                Serveur Discord :</label>
                                            <p class="form-control-plaintext bg-light p-2 rounded">
                                                <?php echo $serverDescription; ?>
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

                            <!-- Server details form -->
                            <div class="card mb-4">
                                <div class="card-header">Détails du Serveur</div>
                                <div class="card-body">
                                    <form method="POST" action="/dashboard/account-server-discord-profile-updated.php">
                                        <!-- Form Row -->
                                        <div class="row gx-3 mb-3">
                                            <!-- Server Category -->
                                            <div class="col-md-6 mb-3">
                                                <label class="small mb-1" for="inputServerCategory">Catégorie</label>
                                                <input class="form-control" id="inputServerCategory"
                                                    name="serverCategory" type="text"
                                                    placeholder="Entrez la catégorie du serveur"
                                                    value="<?php echo $serverCategory; ?>" />
                                            </div>

                                            <!-- Server Website -->
                                            <div class="col-md-6 mb-3">
                                                <label class="small mb-1" for="inputServerWebsite">Website</label>
                                                <input class="form-control" id="inputServerWebsite" name="serverWebsite"
                                                    type="url" placeholder="Entrez l'URL du site web du serveur"
                                                    value="<?php echo $serverWebsite; ?>" />
                                            </div>
                                        </div>

                                        <!-- Hidden server ID -->
                                        <input id="serverId" name="serverId" type="number"
                                            value="<?php echo $serverId; ?>" hidden />

                                        <!-- Save button -->
                                        <button class="btn btn-primary" type="submit">Enregistrer les
                                            modifications</button>
                                    </form>
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
                                        AND cd.app = 'discord'
                                    ) THEN 'yes'
                                    ELSE 'no'
                                END AS is_server_registered
                            FROM 
                                campaigns c
                                LEFT JOIN campaigns_impression i ON c.campaign_id = i.campaign_id
                            WHERE 
                                c.app_discord = 'yes'
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
                                                    <h5 class="mb-1"><?php echo htmlspecialchars($campaign['nom']); ?>
                                                    </h5>
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
                                                    <a href="#" class="btn btn-secondary btn-sm disabled">Déjà
                                                        inscrit</a>
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