# Welcome to the GITHUB of **adnow.online**

Adnow.online is an advertising agency specializing in game servers. The site is **open source**, which means that anyone can propose modifications. Some of these modifications, if relevant and approved, may be integrated into the official site. This way, you can develop new features, share them, and perhaps see your name credited on the official site!

Original site: [https://adnow.online/](https://adnow.online/)

---

![image](https://github.com/user-attachments/assets/b0f0c615-6052-44cb-baa3-f9806d5b63e8)

---

## 🌍 Language
The site is primarily built in **French** 🇫🇷, but it can be easily translated thanks to the built-in translation plugin directly integrated into the site's interface 🌐.

---

## ⚠️ Important Notice

It is **strictly forbidden** to:

- Impersonate the **owner**, **creator**, or **distributor** of the site.
- Use the site or its source code for **personal profit**.

However, **modifications** are **allowed and encouraged** to **improve the site's features**. Each accepted and added feature will be **credited to its author**.

---

## 🔧 Initial Setup

### 1️⃣ OAuth2 Authentication 🔐

Set up **OAuth2** to securely integrate the following services:

- **Google** 🌐
- **Meta** 📘
- **Twitch** 🎮
- **Discord** 💬

Modify the configuration file:

📂 **`/auth/auth-form-update.php`**

```php
// OAuth2 Configuration for Discord
$client_id_discord = 'YOUR_CLIENT_ID';
$client_secret_discord = 'YOUR_CLIENT_SECRET';
$redirect_uri_discord = 'https://YOUR_URL/auth/auth-form-update.php?selected_provider=discord';

// OAuth2 Configuration for Google
$client_id_google = 'YOUR_CLIENT_ID';
$client_secret_google = 'YOUR_CLIENT_SECRET';
$redirect_uri_google = 'https://YOUR_URL/auth/auth-form-update.php?selected_provider=google';

// OAuth2 Configuration for Twitch
$client_id_twitch = 'YOUR_CLIENT_ID';
$client_secret_twitch = 'YOUR_CLIENT_SECRET';
$redirect_uri_twitch = 'https://YOUR_URL/auth/auth-form-update.php?selected_provider=twitch';

// OAuth2 Configuration for Meta
$client_id_meta = 'YOUR_CLIENT_ID';
$client_secret_meta = 'YOUR_CLIENT_SECRET';
$redirect_uri_meta = 'https://YOUR_URL/auth/auth-form-update.php?selected_provider=meta';
```

### 2️⃣ Plugins and Bots 🤖

Ensure that **all plugins and bots** correctly point to the site to guarantee smooth interaction between the different parts of the system.

For the **Minecraft plugin**, you can download the **source code** via the following **GitHub**:

➡️ **[GitHub Repository Link](#)**

For the **Discord bot**, you can download the **source code** via the following **GitHub**:

➡️ **[GitHub Repository Link](#)**

### 3️⃣ Stripe Integration 💳

Set up **Stripe** via a webhook for automated and secure payment management.

📂 **Modify the file `/dashboard/z-stripe.php`**

```php
// Set Stripe secret API key
\Stripe\Stripe::setApiKey('YOUR_KEY_API');

// Stripe Webhook Secret Key
$endpoint_secret = 'YOUR_WEBHOOK_STRIPE'; // Replace with your Stripe webhook secret
```

📂 **Modify the file `/dashboard/stripe-checkout.php`**

```php
\Stripe\Stripe::setApiKey('YOUR_KEY_API');
```

---

## 🗄️ Database Setup

A **blank database** with all necessary tables is available in the root directory of the project.  

📂 **File:** `adnow.sql`  

To set up the database, simply import this SQL file into your MySQL server:  

```sh
mysql -u YOUR_USERNAME -p YOUR_DATABASE_NAME < adnow.sql

---

## 🛡️ Code Security and Robustness

The site may currently have some **security vulnerabilities**. It is therefore highly recommended to:

- **Review** the entire source code to identify and fix potential vulnerabilities 🔍.
- **Enhance** system robustness by applying best security practices 🔒.

---

## ⚠️ Development Notes

At this stage, there may be some pointing or implementation errors. However, these errors **do not compromise** the overall quality of the project. They are generally **easy to fix** and help establish a solid foundation for future developments:

- **Ad management** 📊
- **Creation of creative advertisements** 🎨

---

## 🤝 Contribution

Feel free to **contribute** or **ask questions** about the project via **issues** or by submitting **pull requests**! 🚀

---

## Project Images

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

![image](https://github.com/user-attachments/assets/9f5d784a-1674-47b8-afef-ddc40b8e9b93)
