<?php

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php"); // Redirige vers la page de connexion
    exit(); // Termine l'exécution du script
}

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/stripe/stripe-php/init.php';

\Stripe\Stripe::setApiKey('YOUR_KEY_API');

header('Content-Type: application/json');

$response = [];

try {
    // Debug : Affichez toutes les données POST reçues pour confirmer leur contenu
    error_log("Données POST reçues : " . print_r($_POST, true));

    // Ajouter `discord` et `minecraft` aux champs requis pour la validation
    $requiredFields = ['cpm', 'impressions', 'ad_link', 'campaign_description', 'campaign_name', 'campaign_start_date', 'diff_per_hour', 'discord', 'minecraft'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field])) {
            $response['error'] = "Donnée manquante pour le champ requis : $field";
            echo json_encode($response);
            exit();
        }
    }

    $data = $_POST;
    $cpm = floatval($data['cpm']);
    $impressions = intval($data['impressions']);
    $adLink = $data['ad_link'];
    $campaignDescription = $data['campaign_description'];
    $campaignName = $data['campaign_name'];
    $campaignStartDate = $data['campaign_start_date'];
    $diff_per_hour = $data['diff_per_hour'];
    $discord = $data['discord'];
    $discordMessage = isset($data['discord_message']) ? $data['discord_message'] : null;
    $minecraft = $data['minecraft'];
    $minecraftMessage = isset($data['minecraft_message']) ? $data['minecraft_message'] : null;

    // Valider que la case "Discord" est bien cochée (attendu "yes" pour continuer)
    $app_discord = $discord === 'yes' ? 'yes' : 'no';

    // Valider que la case "Minecraft" est bien cochée (attendu "yes" pour continuer)
    $app_minecraft = $minecraft === 'yes' ? 'yes' : 'no';

    $logoPath = null;

    // Gestion de l'upload du logo de campagne
    if (isset($_FILES['logo_upload']) && $_FILES['logo_upload']['error'] === UPLOAD_ERR_OK) {
        $logoTmpName = $_FILES['logo_upload']['tmp_name'];
        $logoName = basename($_FILES['logo_upload']['name']);
        $fileExtension = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));

        // Vérifier les extensions d'image autorisées
        $allowedExtensions = ['png', 'jpg', 'jpeg'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            $response['error'] = 'Le fichier doit être une image PNG, JPG ou JPEG.';
            echo json_encode($response);
            exit();
        }

        $randomFileName = bin2hex(random_bytes(8)) . '.' . $fileExtension;
        $logoPath = '/dashboard/img/campaigns/' . $randomFileName;
        $logo_directory = '/dashboard/img/campaigns/' . $randomFileName;

        if (!is_writable('/dashboard/img/campaigns/')) {
            $response['error'] = 'Le répertoire de destination n\'est pas accessible en écriture.';
            echo json_encode($response);
            exit();
        }

        if (!move_uploaded_file($logoTmpName, $logoPath)) {
            $response['error'] = 'Erreur lors du déplacement du fichier.';
            echo json_encode($response);
            exit();
        }

        $response['message'] = 'Le fichier a été téléchargé avec succès.';
    } else {
        $response['error'] = 'Logo non téléchargé ou erreur.';
        echo json_encode($response);
        exit();
    }

    // Vérification des valeurs pour la diffusion et le CPM
    if ($diff_per_hour < 1 || $diff_per_hour > 20) {
        $response['error'] = 'Le nombre d\'affichages doit être compris entre 1 et 20.';
        echo json_encode($response);
        exit();
    }

    if ($impressions < 5000) {
        $response['error'] = 'Le nombre d\'impressions doit être d\'au moins 5000.';
        echo json_encode($response);
        exit();
    }

    if ($cpm < 2.00) {
        $response['error'] = 'Le CPM doit être d\'au moins 2,00 €.';
        echo json_encode($response);
        exit();
    }

    // Calcul du coût total
    $cost = ($cpm * $impressions) / 1000;
    $totalCost = $cost * 1.90;
    $amount = round($totalCost * 100);

    // Créer la session de paiement avec Stripe
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Campagne Publicitaire',
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]
        ],
        'mode' => 'payment',
        'success_url' => 'https://adnow.online/dashboard/z-campaigns/campaigns-list.php',
        'cancel_url' => 'https://adnow.online/dashboard/',
        'metadata' => [
            'user_id' => $_SESSION['user_id'],
            'ad_link' => $adLink,
            'campaign_description' => $campaignDescription,
            'logo_path' => $logo_directory,
            'campaign_name' => $campaignName,
            'campaign_start_date' => $campaignStartDate,
            'cpm' => $cpm,
            'impressions' => $impressions,
            'diff_per_hour' => $diff_per_hour,
            'discord' => $app_discord,
            'discord_message' => $discordMessage,
            'minecraft' => $app_minecraft,
            'minecraft_message' => $minecraftMessage // Inclure le message Minecraft dans les métadonnées
        ],
    ]);

    if (!$session) {
        $response['error'] = 'Erreur lors de la création de la session Stripe.';
        echo json_encode($response);
        exit();
    }

    $response['sessionId'] = $session->id;

    // Enregistrement de la commande en base de données
    $stmt = $con->prepare("
        INSERT INTO purchase (user_id, purchase_date, category, price, payment_status, session_id)
        VALUES (?, NOW(), 'campaign', ?, 'pending', ?)
    ");
    $stmt->bind_param('dds', $_SESSION['user_id'], $totalCost, $session->id);
    $stmt->execute();
    $stmt->close();

    echo json_encode($response);

    $con->close();
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'General Error: ' . $e->getMessage()]);
}

?>