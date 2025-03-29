🚀 Présentation du projet
Le dépôt Adnow contient tous les fichiers nécessaires pour lancer le site adnow.online 🎉. Ce projet a pour objectif de fournir une plateforme de gestion de publicité et de création de contenus publicitaires, alliant technologies modernes et architecture modulable.

Note sur la langue : Le site est principalement construit en français 🇫🇷, mais il peut être facilement traduit grâce au plugin de traduction intégré directement dans l'interface du site 🌐.

🔧 Configuration initiale
Pour que le site fonctionne correctement, plusieurs étapes de configuration sont nécessaires :

Authentification OAuth2 🔐
Configurez OAuth2 pour intégrer en toute sécurité les services suivants :

Google 🌐

Meta 📘

Twitch 🎮

Discord 💬

Modifier le fichier de configuration : /auth/auth-form-update.php
Voici comment configurer les détails pour chaque service :


// Configuration OAuth2 Discord
$client_id_discord = 'YOUR_CLIENT_ID';
$client_secret_discord = 'YOUR_CLIENT_SECRET';
$redirect_uri_discord = 'https://VOTRE_URL/auth/auth-form-update.php?selected_provider=discord';

// Configuration OAuth2 Google
$client_id_google = 'YOUR_CLIENT_ID';
$client_secret_google = 'YOUR_CLIENT_SECRET';
$redirect_uri_google = 'https://VOTRE_URL/auth/auth-form-update.php?selected_provider=google';

// Configuration OAuth2 Twitch
$client_id_twitch = 'YOUR_CLIENT_ID';
$client_secret_twitch = 'YOUR_CLIENT_SECRET';
$redirect_uri_twitch = 'https://VOTRE_URL/auth/auth-form-update.php?selected_provider=twitch';

// Configuration OAuth2 Meta
$client_id_meta = 'YOUR_CLIENT_ID';
$client_secret_meta = 'YOUR_CLIENT_SECRET';
$redirect_uri_meta = 'https://VOTRE_URL/auth/auth-form-update.php?selected_provider=meta';
Plugins et Bots 🤖
Assurez-vous que tous les plugins et bots pointent correctement vers le site pour garantir une interaction fluide entre les différentes parties du système.

Intégration Stripe 💳
Configurez Stripe via un webhook pour une gestion automatisée et sécurisée des paiements dans votre système. Modifiez les fichiers suivants pour compléter l'intégration.

Modifier le fichier /dashboard/z-stripe.php :

// Définir la clé API secrète Stripe
\Stripe\Stripe::setApiKey('YOUR_KEY_API');

// Clé secrète du webhook Stripe
$endpoint_secret = 'YOUR_WEBHOOK_STRIPE'; // Remplacez par votre secret de webhook Stripe
Modifier le fichier /dashboard/stripe-checkout.php :

\Stripe\Stripe::setApiKey('YOUR_KEY_API');
🛡️ Sécurité et robustesse du code
Le site peut actuellement présenter quelques failles de sécurité. Il est donc fortement recommandé de :

Vérifier l’ensemble du code source pour identifier et corriger d’éventuelles vulnérabilités 🔍.

Améliorer la robustesse du système en appliquant les meilleures pratiques de sécurité 🔒.

⚠️ Remarques sur le développement
À ce stade, des erreurs de pointage ou d’implémentation peuvent être présentes. Cependant, ces erreurs ne compromettent en rien la qualité de la base du projet. Elles sont généralement simples à corriger et permettent d’établir une fondation solide pour les futurs développements :

Gestion de la publicité 📊.

Création de contenus publicitaires créatifs 🎨.

N'hésitez pas à contribuer ou à poser des questions sur le projet via les issues ou en envoyant des pull requests ! 🚀

