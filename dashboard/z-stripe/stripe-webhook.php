<?php

// Inclure la configuration de la base de données
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config';

// Inclure Stripe PHP SDK
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/stripe/stripe-php/init.php';


// Définir la clé API secrète Stripe
\Stripe\Stripe::setApiKey('YOUR_KEY_API');

// Clé secrète du webhook Stripe
$endpoint_secret = 'YOUR_WEBHOOK_STRIPE'; // Remplacez par votre secret de webhook Stripe

// Lire le payload brut de la requête entrante
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    // Vérifier la signature du webhook Stripe
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    // Payload invalide
    http_response_code(400);
    echo 'Invalid payload';
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Signature invalide
    http_response_code(400);
    echo 'Invalid signature';
    exit();
}

// Traiter l'événement
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object; // Contient les informations de la session de paiement

        // Extraire les métadonnées
        $userId = filter_var($session->metadata->user_id, FILTER_SANITIZE_NUMBER_INT);
        $budget = $session->amount_total / 100; // Montant total en euros
        $sessionId = filter_var($session->id, FILTER_SANITIZE_SPECIAL_CHARS);

        $objectif_cpv = filter_var($session->metadata->cpm, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $impression_total = filter_var($session->metadata->impressions, FILTER_SANITIZE_NUMBER_INT);
        $logoPath = filter_var($session->metadata->logo_path, FILTER_SANITIZE_URL);

        $link_to = filter_var($session->metadata->ad_link, FILTER_SANITIZE_URL);
        $campaign_description = filter_var($session->metadata->campaign_description, FILTER_SANITIZE_SPECIAL_CHARS);
        $campaign_name = filter_var($session->metadata->campaign_name, FILTER_SANITIZE_SPECIAL_CHARS);
        $date_debut = filter_var($session->metadata->campaign_start_date, FILTER_SANITIZE_SPECIAL_CHARS);
        $diff_per_hour = filter_var($session->metadata->diff_per_hour, FILTER_SANITIZE_NUMBER_INT);
        $app_discord = $session->metadata->discord;
        $chat_msg_discord = $session->metadata->discord_message;
        $app_minecraft = $session->metadata->minecraft;
        $chat_msg_minecraft = $session->metadata->minecraft_message;


        // Récupérer le PaymentIntent pour obtenir les détails du moyen de paiement
        $paymentIntentId = filter_var($session->payment_intent, FILTER_SANITIZE_SPECIAL_CHARS);
        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
        $paymentMethodId = filter_var($paymentIntent->payment_method, FILTER_SANITIZE_SPECIAL_CHARS);
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $paymentMethodType = filter_var($paymentMethod->type, FILTER_SANITIZE_SPECIAL_CHARS); // Type de moyen de paiement


        // Définir la longueur souhaitée du token, par exemple, 60
        $tokenLength = 11;

        // Appeler la fonction pour générer le token et l'assigner à la variable $token
        $token = generateRandomToken($tokenLength);

        // Préparer la requête SQL pour insérer les données dans la table campaigns
        $sql = "INSERT INTO campaigns (nom, logo_url, diff_per_hour, link_to, users_id, date_debut, budget, objectif_cpv, impression_total, description, link_code, app_discord, chat_msg_discord, app_minecraft, chat_msg_minecraft)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Préparer la déclaration
        $stmt = $con->prepare($sql);

        // Vérifier la préparation de la requête
        if ($stmt === false) {
            die("Erreur de préparation de la requête : " . htmlspecialchars($con->error));
        }

        // Lier les paramètres
        $stmt->bind_param(
            'ssisisdisssssss', // Types : string, string, string, string, integer, string, integer, string, double, double, integer, string, string
            $campaign_name, // Chaîne de caractères
            $logoPath, // Chaîne de caractères
            $diff_per_hour, // Entier
            $link_to, // Chaîne de caractères
            $userId, // Entier
            $date_debut, // Chaîne de caractères (datetime)
            $budget, // Nombre à virgule flottante
            $objectif_cpv, // Nombre à virgule flottante
            $impression_total, // Entier
            $campaign_description, // Chaîne de caractères
            $token, // Chaîne de caractères
            $app_discord,
            $chat_msg_discord,
            $app_minecraft,
            $chat_msg_minecraft
        );

        // Exécuter la requête
        if (!$stmt->execute()) {
            echo "Erreur lors de l'insertion : " . htmlspecialchars($stmt->error);
        } else {
            // Récupérer l'ID de la campagne insérée
            $campaignId = $con->insert_id;

            // Préparer la requête pour mettre à jour la table purchase
            $stmt_update = $con->prepare("
                UPDATE purchase 
                SET price = ?, payment_status = 'paid', payment_method = ?, payment_method_type = ?, purchase_item_id = ?
                WHERE session_id = ?
            ");

            // Vérifier la préparation de la requête
            if ($stmt_update === false) {
                http_response_code(500);
                echo 'Error preparing update statement: ' . htmlspecialchars($con->error);
                exit();
            }

            // Lier les paramètres
            $stmt_update->bind_param('dssss', $budget, $paymentMethodId, $paymentMethodType, $campaignId, $sessionId);

            // Exécuter la requête
            if (!$stmt_update->execute()) {
                http_response_code(500);
                echo 'Error updating database: ' . htmlspecialchars($stmt_update->error);
                exit();
            }

            $stmt_update->close();
            echo "Données insérées et mises à jour avec succès !";
        }

        $stmt->close();

        break;

    default:
        // Type d'événement non traité
        echo 'Event type not handled';
        break;
}

// Fermer la connexion à la base de données
$con->close();

// Répondre avec succès à Stripe
http_response_code(200);
?>