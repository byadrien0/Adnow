<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Redirige vers la page de connexion
    exit(); // Termine l'exécution du script

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
                                        <div class="page-header-icon"><i data-feather="arrow-right-circle"></i></div>
                                        Configuration de la Campagne
                                    </h1>
                                    <div class="page-header-subtitle">Veuillez entrer les détails de votre campagne et
                                        de votre entreprise.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- Contenu principal -->
                <div class="container-xl px-4 mt-n10">
                    <!-- Exemple de carte de wizard avec navigation -->
                    <div class="card">
                        <div class="card-header border-bottom">
                            <!-- Navigation du wizard -->
                            <div class="nav nav-pills nav-justified flex-column flex-xl-row nav-wizard" id="cardTab"
                                role="tablist">
                                <!-- Étape 1 -->
                                <a class="nav-item nav-link active" id="wizard1-tab" href="#wizard1"
                                    data-bs-toggle="tab" role="tab" aria-controls="wizard1" aria-selected="true">
                                    <div class="wizard-step-icon">1</div>
                                    <div class="wizard-step-text">
                                        <div class="wizard-step-text-name">Informations liées</div>
                                        <div class="wizard-step-text-details">Informations sur la personne ou
                                            l'entreprise</div>
                                    </div>
                                </a>
                                <!-- Étape 2 -->
                                <a class="nav-item nav-link" id="wizard2-tab" href="#wizard2" data-bs-toggle="tab"
                                    role="tab" aria-controls="wizard2" aria-selected="false">
                                    <div class="wizard-step-icon">2</div>
                                    <div class="wizard-step-text">
                                        <div class="wizard-step-text-name">Message et lien</div>
                                        <div class="wizard-step-text-details">Informations pour la publication</div>
                                    </div>
                                </a>
                                <!-- Étape 3 -->
                                <a class="nav-item nav-link" id="wizard3-tab" href="#wizard3" data-bs-toggle="tab"
                                    role="tab" aria-controls="wizard3" aria-selected="false">
                                    <div class="wizard-step-icon">3</div>
                                    <div class="wizard-step-text">
                                        <div class="wizard-step-text-name">Validation et Paiement</div>
                                        <div class="wizard-step-text-details">Accepter le contrat et procéder au
                                            paiement</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="cardTabContent">

                                <!-- Étape 1 -->
                                <div class="tab-pane py-5 py-xl-10 fade show active" id="wizard1" role="tabpanel"
                                    aria-labelledby="wizard1-tab">
                                    <div class="row justify-content-center">
                                        <div class="col-xxl-6 col-xl-8">
                                            <h3 class="text-primary">Étape 1</h3>
                                            <h5 class="card-title mb-4">Informations sur votre personne
                                            </h5>

                                            <!-- Boîte d'information -->
                                            <div class="alert alert-info" role="alert">Les informations fournies sont
                                                celles précédemment soumises dans la section de profil.<a
                                                    href="/dashboard/z-account/profile.php" class="alert-link">Cliquez
                                                    ici pour les vérifier.</a>
                                            </div>

                                            <hr class="my-4" />
                                            <div class="d-flex justify-content-between">
                                                <button class="btn btn-light" id="prevButton1"
                                                    type="button">Précédent</button>
                                                <button class="btn btn-primary" id="nextButton1"
                                                    type="button">Suivant</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Étape 2 -->
                                <div class="tab-pane py-5 py-xl-10 fade" id="wizard2" role="tabpanel"
                                    aria-labelledby="wizard2-tab">
                                    <div class="row justify-content-center">
                                        <div class="col-xxl-6 col-xl-8">
                                            <h3 class="text-primary">Étape 2</h3>
                                            <h5 class="card-title mb-4">Entrez les détails de votre campagne
                                                publicitaire</h5>
                                            <form>
                                                <!-- Champ pour le nom de la campagne -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputCampaignName">Nom de la
                                                        campagne</label>
                                                    <input class="form-control" id="inputCampaignName" type="text"
                                                        placeholder="Entrez le nom de la campagne" />
                                                </div>

                                                <!-- Champ pour la date et l'heure de début de la campagne -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputCampaignStartDate">Date et Heure
                                                        de début de la campagne</label>
                                                    <input class="form-control" id="inputCampaignStartDate"
                                                        type="datetime-local" />
                                                </div>

                                                <!-- Champ pour le lien cliquable -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputAdLink">Lien cliquable par les
                                                        joueurs</label>
                                                    <input class="form-control" id="inputAdLink" type="url"
                                                        placeholder="Entrez le lien cliquable"
                                                        value="https://www.votresitepublicitaire.com" />
                                                </div>

                                                <!-- Champ pour la description de la campagne -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputCampaignDescription">Description
                                                        de la campagne</label>
                                                    <textarea class="form-control" id="inputCampaignDescription"
                                                        rows="4"
                                                        placeholder="Décrivez la campagne et ce qu'elle promeut ici..."></textarea>
                                                </div>

                                                <div id="checkboxSolid">
                                                    <div class="card mb-4">
                                                        <div class="card-header">Sélection des Jeux pour Diffusion des
                                                            Publicités</div>
                                                        <div class="card-body">
                                                            <p class="text-muted fw-500">
                                                                Sélectionnez les jeux où vous souhaitez que les
                                                                publicités soient diffusées :
                                                            </p>
                                                            <div class="sbp-preview mb-4">
                                                                <div class="sbp-preview-content">

                                                                    <!-- Checkbox pour Minecraft -->
                                                                    <div class="form-check form-check-solid">
                                                                        <input class="form-check-input"
                                                                            id="selectMinecraft" type="checkbox"
                                                                            value="minecraft">
                                                                        <label class="form-check-label"
                                                                            for="selectMinecraft">Minecraft</label>
                                                                        <div id="minecraftError" class="text-danger"
                                                                            style="display:none;">Veuillez cocher cette
                                                                            option pour continuer.</div>
                                                                    </div>
                                                                    <!-- Champ de message pour Minecraft (caché par défaut) -->
                                                                    <div class="mb-3" id="minecraftMessageContainer"
                                                                        style="display: none;">
                                                                        <label class="small mb-1"
                                                                            for="inputMinecraftMessage">Message pour
                                                                            Minecraft</label>
                                                                        <textarea class="form-control"
                                                                            id="inputMinecraftMessage" rows="3"
                                                                            placeholder="Entrez le message à publier sur Minecraft"></textarea>
                                                                        <div id="minecraftMessageError"
                                                                            class="text-danger" style="display: none;">
                                                                            Veuillez entrer un message pour Minecraft.
                                                                        </div>
                                                                    </div>


                                                                    <!-- Checkbox pour Discord -->
                                                                    <div class="form-check form-check-solid">
                                                                        <input class="form-check-input"
                                                                            id="selectDiscord" type="checkbox"
                                                                            value="discord">
                                                                        <label class="form-check-label"
                                                                            for="selectDiscord">Discord</label>
                                                                        <div id="discordError" class="text-danger"
                                                                            style="display:none;">Veuillez cocher cette
                                                                            option pour continuer.</div>
                                                                    </div>
                                                                    <!-- Champ de message pour Discord (caché par défaut) -->
                                                                    <div class="mb-3" id="discordMessageContainer"
                                                                        style="display: none;">
                                                                        <label class="small mb-1"
                                                                            for="inputDiscordMessage">Message pour
                                                                            Discord</label>
                                                                        <textarea class="form-control"
                                                                            id="inputDiscordMessage" rows="3"
                                                                            placeholder="Entrez le message à publier sur Discord"></textarea>
                                                                        <div id="discordMessageError"
                                                                            class="text-danger" style="display: none;">
                                                                            Veuillez entrer un message pour Discord.
                                                                        </div>
                                                                    </div>


                                                                </div>
                                                                <!-- Texte explicatif -->
                                                                <div class="sbp-preview-text mt-3">
                                                                    Utilisez ces cases à cocher pour choisir les
                                                                    plateformes de diffusion des publicités. Les options
                                                                    sélectionnées détermineront les jeux où les annonces
                                                                    seront visibles.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <!-- Champ pour le téléchargement du logo -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputLogoUpload">Téléchargez le logo
                                                        de la campagne (1024x1024 px)</label>
                                                    <input class="form-control" id="inputLogoUpload" type="file"
                                                        accept="image/*" />
                                                </div>

                                                <hr class="my-4" />

                                                <!-- Boutons de navigation -->
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-light" id="prevButton2"
                                                        type="button">Précédent</button>
                                                    <button class="btn btn-primary" id="nextButton2"
                                                        type="button">Suivant</button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>

                                <!-- Étape 3 -->
                                <div class="tab-pane py-5 py-xl-10 fade" id="wizard3" role="tabpanel"
                                    aria-labelledby="wizard3-tab">
                                    <div class="row justify-content-center">
                                        <div class="col-xxl-6 col-xl-8">
                                            <h3 class="text-primary">Étape 3</h3>
                                            <h5 class="card-title mb-4">Validation des conditions et paiement</h5>
                                            <form id="payment-form">
                                                <!-- Champ pour le nombre d'affichage par heure -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputdiff_per_hour">Nombre
                                                        d'affichage par heure et par serveur de la publicité</label>
                                                    <input class="form-control" id="inputdiff_per_hour" type="number"
                                                        value="1" min="1" max="20" />
                                                </div>

                                                <!-- Champ pour le CPM -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputCPM">CPM</label>
                                                    <input class="form-control" id="inputCPM" type="number" value="2.00"
                                                        min="2.00" step="0.01" />
                                                </div>

                                                <!-- Champ pour les impressions totales -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="inputImpressions">Impressions
                                                        Totales</label>
                                                    <input class="form-control" id="inputImpressions" type="number"
                                                        value="5000" min="5000" />
                                                </div>

                                                <!-- Champ pour le coût total -->
                                                <div class="mb-3">
                                                    <label class="small mb-1" for="totalCost">Coût Total</label>
                                                    <input class="form-control" id="totalCost" type="text" readonly />
                                                </div>

                                                <input type="hidden" id="stripePaymentLink" value="" />
                                                <div class="form-check">
                                                    <input class="form-check-input" id="checkSecurity" type="checkbox"
                                                        checked="" disabled="" />
                                                    <label class="form-check-label" for="checkSecurity">Vous acceptez
                                                        les conditions générales de vente</label>
                                                </div>

                                                <hr class="my-4" />

                                                <!-- Boutons de navigation -->
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-light" id="prevButton3"
                                                        type="button">Précédent</button>
                                                    <button class="btn btn-primary" id="pay-button"
                                                        type="button">Payer</button>
                                                </div>
                                            </form>
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

    <script src="/dashboard/js/form-pay.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/dashboard/js/scripts.js"></script>
    <script src="https://js.stripe.com/v3/"></script>


</body>

</html>