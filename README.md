# Bienvenue sur le GITHUB du site **adnow.online**

Adnow.online est une agence de publicité spécialisée dans les serveurs de jeux. Le site est **open source**, ce qui signifie que tout le monde peut proposer des modifications. Certaines de ces modifications, si elles sont pertinentes et approuvées, peuvent être intégrées au site officiel. Ainsi, vous pouvez développer de nouvelles fonctionnalités, les partager, et peut-être voir votre nom crédité sur le site officiel !

---

![image](https://github.com/user-attachments/assets/b0f0c615-6052-44cb-baa3-f9806d5b63e8)

---

## 🌍 Langue
Le site est principalement construit en **français** 🇫🇷, mais il peut être facilement traduit grâce au plugin de traduction intégré directement dans l'interface du site 🌐.

---

## ⚠️ Notice importante

Il est **strictement interdit** de :

- Se faire passer pour le **propriétaire**, le **créateur**, ou le **diffuseur** du site.
- Utiliser le site ou son code source dans le but de **générer un bénéfice personnel**.

Cependant, les **modifications** sont **autorisées et encouragées** dans le but d’**améliorer les fonctionnalités** du site. Chaque fonctionnalité acceptée et ajoutée au site principal sera **créditée à son auteur**.

---

## 🔧 Configuration initiale

### 1️⃣ Authentification OAuth2 🔐

Configurez **OAuth2** pour intégrer en toute sécurité les services suivants :

- **Google** 🌐
- **Meta** 📘
- **Twitch** 🎮
- **Discord** 💬

Modifiez le fichier de configuration :

📂 **`/auth/auth-form-update.php`**

```php
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
```

### 2️⃣ Plugins et Bots 🤖

Assurez-vous que **tous les plugins et bots** pointent correctement vers le site pour garantir une interaction fluide entre les différentes parties du système.

Pour le **plugin Minecraft**, vous pouvez télécharger le **code source** via le **GitHub** suivant :

➡️ **[Lien du dépôt GitHub](#)**

Pour le **bot discord**, vous pouvez télécharger le **code source** via le **GitHub** suivant :

➡️ **[Lien du dépôt GitHub](#)**

### 3️⃣ Intégration Stripe 💳

Configurez **Stripe** via un webhook pour une gestion automatisée et sécurisée des paiements.

📂 **Modifier le fichier `/dashboard/z-stripe.php`**

```php
// Définir la clé API secrète Stripe
\Stripe\Stripe::setApiKey('YOUR_KEY_API');

// Clé secrète du webhook Stripe
$endpoint_secret = 'YOUR_WEBHOOK_STRIPE'; // Remplacez par votre secret de webhook Stripe
```

📂 **Modifier le fichier `/dashboard/stripe-checkout.php`**

```php
\Stripe\Stripe::setApiKey('YOUR_KEY_API');
```

---

## 🛡️ Sécurité et robustesse du code

Le site peut actuellement présenter quelques **failles de sécurité**. Il est donc fortement recommandé de :

- **Vérifier** l’ensemble du code source pour identifier et corriger d’éventuelles vulnérabilités 🔍.
- **Améliorer** la robustesse du système en appliquant les meilleures pratiques de sécurité 🔒.

---

## ⚠️ Remarques sur le développement

À ce stade, des erreurs de pointage ou d’implémentation peuvent être présentes. Cependant, ces erreurs **ne compromettent pas** la qualité de la base du projet. Elles sont généralement **simples à corriger** et permettent d’établir une fondation solide pour les futurs développements :

- **Gestion de la publicité** 📊
- **Création de contenus publicitaires créatifs** 🎨

---




## 🤝 Contribution

N'hésitez pas à **contribuer** ou à **poser des questions** sur le projet via les **issues** ou en envoyant des **pull requests** ! 🚀

---

## Images du projet


![image](https://github.com/user-attachments/assets/b0f0c615-6052-44cb-baa3-f9806d5b63e8)

---

![image](https://github.com/user-attachments/assets/51651748-65c8-417f-9b48-099817635a98)

---

![image](https://github.com/user-attachments/assets/3899a9f0-06a0-4ecc-bc11-430f7fffb690)

---

![image](https://github.com/user-attachments/assets/715bdefa-bace-4ba3-957a-b36b16b26483)

---

![image](https://github.com/user-attachments/assets/f786a044-52db-4d57-b8f9-fbde2df24287)

---

![image](https://github.com/user-attachments/assets/096d3b3f-edec-42bf-9dc5-8c5d19c40535)

---



