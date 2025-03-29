<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si le paramètre GET "c" est défini
if (isset($_GET['c'])) {
    // Nettoyer le code pour éviter les injections XSS
    $code = htmlspecialchars($_GET['c'], ENT_QUOTES, 'UTF-8');

    // Initialiser la variable server_id à une chaîne vide par défaut
    $server_id = '';

    // Vérifier si le paramètre GET "s" est défini
    if (isset($_GET['s'])) {
        // Nettoyer le code pour éviter les injections XSS
        $server_id = htmlspecialchars($_GET['s'], ENT_QUOTES, 'UTF-8');
    }

    // Redirection vers l'URL avec le code et le server_id (si défini)
    if (!empty($server_id)) {
        header("Location: /dashboard/link-to.php?server_id=$server_id&code=$code");
    } else {
        header("Location: /dashboard/link-to.php?code=$code");
    }
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

    <!-- Notif -->
    <link href="/styles/css/notification.css" rel="stylesheet">
    <script src="/styles/js/notification.js"></script>

    <!-- Cookie -->
    <link href="/styles/css/cookie.css" rel="stylesheet">
    <script src="/styles/js/cookie.js"></script>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/styles/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/styles/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/styles/img/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link href="./styles/css/css.css" rel="stylesheet">
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header class="fixed w-full">
        <nav class="bg-white border-gray-200 py-2.5 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 mx-auto">
                <a href="#" class="flex items-center">
                    <span
                        class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">adnow.online</span>
                </a>
                <div class="flex items-center lg:order-2">

                    <!-- <a href="#" class="text-gray-800 dark:text-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 sm:mr-2 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Log in</a> -->
                    <?php if (isset($_SESSION['user_id'])) { ?> <a href="/dashboard/"
                            class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 sm:mr-2 lg:mr-0 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800">TABLEAU
                            DE BORD</a> <?php } ?>
                </div>

            </div>
        </nav>
    </header>

    <?php

    include $_SERVER['DOCUMENT_ROOT'] . '/includes/notification.php';

    include $_SERVER['DOCUMENT_ROOT'] . '/includes/cookie.php';

    ?>


    <!-- Start block -->
    <section class="bg-white dark:bg-gray-900">
        <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
            <div class="mr-auto place-self-center lg:col-span-7">
                <h1
                    class="max-w-2xl mb-4 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-6xl dark:text-white">
                    La première <br>plateforme publicitaire pour serveurs de jeux.</h1>
                <p class="max-w-2xl mb-6 font-light text-gray-500 lg:mb-8 md:text-lg lg:text-xl dark:text-gray-400">Une
                    plateforme publicitaire spécialement conçue pour les serveurs de jeux, qui permet aux propriétaires
                    de diffuser des annonces directement aux joueurs via des outils faciles à utiliser.</p>

                <?php if (!isset($_SESSION['user_id'])) { ?>

                    <!-- Ajout du texte explicatif avec taille réduite -->
                    <p
                        class="max-w-2xl mb-6 text-sm font-medium text-gray-700 lg:mb-8 md:text-base lg:text-lg dark:text-gray-300">
                        Cliquez sur un des boutons ci-dessous pour vous connecter avec le moyen de connexion de votre choix
                        :
                    </p>

                    <div class="space-y-4 sm:flex sm:space-y-0 sm:space-x-4">
                        <a href="/auth/auth-form-update.php?provider=twitch"
                            class="inline-flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-center text-gray-900 border border-gray-200 rounded-lg sm:w-auto hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 dark:text-white dark:border-gray-700 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                            <i class="fab fa-twitch text-gray-500 dark:text-gray-400 text-xl mr-2"></i> Twitch
                        </a>
                        <a href="/auth/auth-form-update.php?provider=meta"
                            class="inline-flex items-center justify-center w-full px-5 py-3 mb-2 mr-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:w-auto focus:outline-none hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                            <i class="fab fa-facebook text-gray-500 dark:text-gray-400 text-xl mr-2"></i> Meta
                        </a>
                        <a href="/auth/auth-form-update.php?provider=google"
                            class="inline-flex items-center justify-center w-full px-5 py-3 mb-2 mr-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:w-auto focus:outline-none hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                            <i class="fab fa-google text-gray-500 dark:text-gray-400 text-xl mr-2"></i> Google
                        </a>
                        <a href="/auth/auth-form-update.php?provider=discord"
                            class="inline-flex items-center justify-center w-full px-5 py-3 mb-2 mr-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:w-auto focus:outline-none hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                            <i class="fab fa-discord text-gray-500 dark:text-gray-400 text-xl mr-2"></i> Discord
                        </a>
                    </div>

                <?php } ?>
            </div>

            <div class="hidden lg:mt-0 lg:col-span-5 lg:flex">
                <img src="./styles/img/hero.png" alt="hero image">
            </div>
        </div>
    </section>
    <!-- End block -->

    <!-- Start block -->
    <section class="bg-gray-50 dark:bg-gray-800">
        <div class="max-w-screen-xl px-4 py-8 mx-auto space-y-12 lg:space-y-20 lg:py-24 lg:px-6">
            <!-- Row -->
            <div class="items-center gap-8 lg:grid lg:grid-cols-2 xl:gap-16">
                <div class="text-gray-500 sm:text-lg dark:text-gray-400">
                    <h2 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Monétisez
                        votre serveur facilement</h2>
                    <p class="mb-8 font-light lg:text-xl">Maximisez vos revenus grâce à nos outils de gestion de
                        publicité pour Minecraft et Discord. Automatisez la diffusion des annonces sur vos serveurs et
                        commencez à générer des revenus dès aujourd'hui.</p>
                    <!-- List -->
                    <ul role="list" class="pt-8 space-y-5 border-t border-gray-200 my-7 dark:border-gray-700">
                        <li class="flex space-x-3">
                            <!-- Icon -->
                            <svg class="flex-shrink-0 w-5 h-5 text-purple-500 dark:text-purple-400" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span
                                class="text-base font-medium leading-tight text-gray-900 dark:text-white">Automatisation
                                de la diffusion publicitaire</span>
                        </li>
                        <li class="flex space-x-3">
                            <!-- Icon -->
                            <svg class="flex-shrink-0 w-5 h-5 text-purple-500 dark:text-purple-400" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900 dark:text-white">Revenus
                                passifs grâce à la publicité</span>
                        </li>
                        <li class="flex space-x-3">
                            <!-- Icon -->
                            <svg class="flex-shrink-0 w-5 h-5 text-purple-500 dark:text-purple-400" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900 dark:text-white">Gestion
                                simplifiée des annonces</span>
                        </li>
                    </ul>
                    <p class="mb-8 font-light lg:text-xl">Augmentez vos revenus en tirant parti de nos solutions
                        automatisées pour la gestion des publicités sur vos serveurs. Facile à configurer et à gérer,
                        idéal pour maximiser votre rentabilité.</p>
                </div>
                <img class="hidden w-full mb-4 rounded-lg lg:mb-0 lg:flex" src="./styles/img/feature-1.png"
                    alt="dashboard feature image">
            </div>
            <!-- Row -->
            <div class="items-center gap-8 lg:grid lg:grid-cols-2 xl:gap-16">
                <img class="hidden w-full mb-4 rounded-lg lg:mb-0 lg:flex" src="./styles/img/feature-2.png"
                    alt="feature image 2">
                <div class="text-gray-500 sm:text-lg dark:text-gray-400">
                    <h2 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Maximisez vos
                        profits avec nos outils</h2>
                    <p class="mb-8 font-light lg:text-xl">Nos outils vous permettent de gérer facilement les publicités
                        sur vos serveurs Minecraft et Discord. Profitez d'une automatisation complète et commencez à
                        générer des revenus sans effort.</p>
                    <!-- List -->
                    <ul role="list" class="pt-8 space-y-5 border-t border-gray-200 my-7 dark:border-gray-700">
                        <li class="flex space-x-3">
                            <!-- Icon -->
                            <svg class="flex-shrink-0 w-5 h-5 text-purple-500 dark:text-purple-400" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900 dark:text-white">Rapports
                                détaillés et analyses</span>
                        </li>
                        <li class="flex space-x-3">
                            <!-- Icon -->
                            <svg class="flex-shrink-0 w-5 h-5 text-purple-500 dark:text-purple-400" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900 dark:text-white">Templates et
                                options personnalisées</span>
                        </li>
                        <li class="flex space-x-3">
                            <!-- Icon -->
                            <svg class="flex-shrink-0 w-5 h-5 text-purple-500 dark:text-purple-400" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900 dark:text-white">Gestion
                                automatisée des campagnes</span>
                        </li>
                    </ul>
                    <p class="font-light lg:text-xl">Transformez votre serveur en une source de revenus passive avec
                        notre système automatisé de gestion des publicités. Configurez une fois et commencez à générer
                        des profits.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- End block -->

    <!-- Start block -->
    <section class="bg-white dark:bg-gray-900">
        <div
            class="items-center max-w-screen-xl px-4 py-8 mx-auto lg:grid lg:grid-cols-4 lg:gap-16 xl:gap-24 lg:py-24 lg:px-6">
            <div class="col-span-2 mb-8">
                <p class="text-lg font-medium text-purple-600 dark:text-purple-500">Excellence Numérique</p>
                <h2 class="mt-3 mb-4 text-3xl font-extrabold tracking-tight text-gray-900 md:text-3xl dark:text-white">
                    Partenaire de choix en 2024</h2>
                <p class="font-light text-gray-500 sm:text-xl dark:text-gray-400">Notre technologie innovante et nos
                    solutions personnalisées sont conçues pour faire avancer votre entreprise dans le paysage numérique.
                    Nous nous engageons à atteindre nos objectifs pour 2024 et à vous offrir des performances
                    exceptionnelles.</p>
                <p class="text-sm font-light text-gray-500 dark:text-gray-400 mt-4">Les chiffres indiqués ci-dessous
                    reflètent nos ambitions et objectifs pour 2024 à 2026. Nous sommes déterminés à vous fournir des
                    résultats alignés avec ces objectifs.</p>
                <div class="pt-6 mt-6 space-y-4 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <a href="#"
                            class="inline-flex items-center text-base font-medium text-purple-600 hover:text-purple-800 dark:text-purple-500 dark:hover:text-purple-700">
                            Devenir Partenaire
                            <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                    <div>
                        <a href="#"
                            class="inline-flex items-center text-base font-medium text-purple-600 hover:text-purple-800 dark:text-purple-500 dark:hover:text-purple-700">
                            Lancer sa campagne
                            <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-span-2 space-y-8 md:grid md:grid-cols-2 md:gap-12 md:space-y-0">
                <div>
                    <svg class="w-10 h-10 mb-2 text-purple-600 md:w-12 md:h-12 dark:text-purple-500" fill="currentColor"
                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <h3 class="mb-2 text-2xl font-bold dark:text-white">99.9% de Disponibilité</h3>
                    <p class="font-light text-gray-500 dark:text-gray-400">Assurant un service continu et fiable pour
                        vos besoins numériques.</p>
                </div>
                <div>
                    <svg class="w-10 h-10 mb-2 text-purple-600 md:w-12 md:h-12 dark:text-purple-500" fill="currentColor"
                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z">
                        </path>
                    </svg>
                    <h3 class="mb-2 text-2xl font-bold dark:text-white">50K+ Utilisateurs</h3>
                    <p class="font-light text-gray-500 dark:text-gray-400">Un nombre croissant d'utilisateurs engagés
                        avec notre solution.</p>
                </div>
                <div>
                    <svg class="w-10 h-10 mb-2 text-purple-600 md:w-12 md:h-12 dark:text-purple-500" fill="currentColor"
                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <h3 class="mb-2 text-2xl font-bold dark:text-white">20+ Pays</h3>
                    <p class="font-light text-gray-500 dark:text-gray-400">Présents dans plusieurs pays pour soutenir
                        votre croissance internationale.</p>
                </div>
                <div>
                    <svg class="w-10 h-10 mb-2 text-purple-600 md:w-12 md:h-12 dark:text-purple-500" fill="currentColor"
                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z">
                        </path>
                    </svg>
                    <h3 class="mb-2 text-2xl font-bold dark:text-white">11.5M Impressions</h3>
                    <p class="font-light text-gray-500 dark:text-gray-400">Impressions générées par notre plateforme.
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- End block -->

    <!-- Start block -->
    <section class="bg-gray-50 dark:bg-gray-800">
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-16 lg:px-6">
            <div class="max-w-screen-sm mx-auto text-center">
                <h2 class="mb-4 text-3xl font-extrabold leading-tight tracking-tight text-gray-900 dark:text-white">Nous
                    contacter</h2>
                <p class="mb-6 font-light text-gray-500 dark:text-gray-400 md:text-lg">Pour toute question ou demande,
                    n'hésitez pas à nous contacter. Cliquez sur le bouton ci-dessous pour nous envoyer un message.</p>
                <a href="https://discord.gg/byadrien-1002589501874511902" target="_blank"
                    class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none dark:focus:ring-purple-800">Nous
                    contacter</a>
            </div>
        </div>
    </section>

    <!-- End block -->
    <footer class="bg-white dark:bg-gray-800">
        <div class="max-w-screen-xl p-4 py-6 mx-auto lg:py-16 md:p-8 lg:p-10">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-3 lg:grid-cols-5">
                <div>
                    <h3 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Légalité</h3>
                    <ul class="text-gray-500 dark:text-gray-400">
                        <li class="mb-4">
                            <a href="https://docs.google.com/document/d/13b5UiZO4hrcD_mHpLRtm5rOm2GKVLV19w2oGcFuGBhU/edit?usp=drive_link"
                                class="hover:underline">Politique de Cookies</a>
                        </li>
                        <li class="mb-4">
                            <a href="https://docs.google.com/document/d/1UnWb-RH-oNOeHlOxrc_dUHcQdRpeRJNmVvuDfXDJAg8/edit?usp=drive_link"
                                class="hover:underline">Conditions Générales d'Utilisation</a>
                        </li>
                        <li class="mb-4">
                            <a href="https://docs.google.com/document/d/1ShLUfh9z_Xv5O-JuxI93wdnNdarYxWbMdBZfTk654mQ/edit?usp=drive_link"
                                class="hover:underline">Politique de Confidentialité</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Légalité</h3>
                    <ul class="text-gray-500 dark:text-gray-400">
                        <li class="mb-4">
                            <a href="https://docs.google.com/document/d/1FM5ierbuKTZB0H4m4XQPhGfU_jIFpqdlVEm3q6Yb8dQ/edit?usp=drive_link"
                                class="hover:underline">Mentions Légales</a>
                        </li>
                        <li class="mb-4">
                            <a href="https://docs.google.com/document/d/1y3eie80A2LUjSF5B97TzRJy83rdBIfdMHO0aRbNTepA/edit?usp=drive_link"
                                class="hover:underline">Conditions Générales de Vente</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Réseaux sociaux</h3>
                    <ul class="text-gray-500 dark:text-gray-400">
                        <li class="mb-4">
                            <a href="https://www.youtube.com/@ByAdrien" class="hover:underline">Youtube</a>
                        </li>
                        <li class="mb-4">
                            <a href="https://discord.gg/byadrien-1002589501874511902"
                                class="hover:underline">Discord</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Page des Systèmes
                    </h3>
                    <ul class="text-gray-500 dark:text-gray-400">
                        <li class="mb-4">
                            <a href="https://www.spigotmc.org/resources/adnow-1-7-10-1-21-1-monetize-your-minecraft-server-with-web-dashboard.119523/"
                                class="hover:underline">Spigot</a>
                        </li>
                        <li class="mb-4">
                            <a href="https://legacy.curseforge.com/minecraft/bukkit-plugins/adnow-monetize-your-minecraft-server"
                                class="hover:underline">CurseForge</a>
                        </li>
                        <li class="mb-4">
                            <a href="https://discord.com/application-directory/1284105909898973236"
                                class="hover:underline">DiscordBot</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Centre d'aide</h3>
                    <ul class="text-gray-500 dark:text-gray-400">
                        <li class="mb-4">
                            <a href="https://discord.gg/byadrien-1002589501874511902" class="hover:underline">Serveur
                                Discord</a>
                        </li>
                        <li class="mb-4">
                            <a href="email:adriendechocqueuse@icloud.com" class="hover:underline">Adresse E-mail</a>
                        </li>
                    </ul>
                </div>

            </div>
            <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8">
            <div class="text-center">

                <span class="block text-sm text-center text-gray-500 dark:text-gray-400">© 2024 WeeklyTech. All Rights
                    Reserved. Built with <a href="https://flowbite.com"
                        class="text-purple-600 hover:underline dark:text-purple-500">Flowbite</a> and <a
                        href="https://tailwindcss.com"
                        class="text-purple-600 hover:underline dark:text-purple-500">Tailwind CSS</a>.
                </span>

            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/flowbite@1.4.1/dist/flowbite.js"></script>
</body>

</html>