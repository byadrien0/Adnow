<?php
// Inclusion du fichier de configuration
include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    addNotification($con, $_SESSION['user_id'] ?? null, "Vous devez être connecté pour effectuer un retrait.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
    header("Location: /index.php");
    exit();
}



// Vérifier les données de facturation selon le type d'utilisateur
if ($acc_user_type === 'individual') {
    $stmt = $con->prepare("SELECT * FROM personal_bank_account WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_personal = $stmt->get_result();

    if ($result_personal->num_rows === 0) {
        addNotification($con, $user_id, "Manque de données de facturation pour un compte individuel.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
        header("Location: /index.php");
        exit();
    }

    $personal_data = $result_personal->fetch_assoc();
    $required_fields_personal = ['first_name', 'last_name', 'address', 'city', 'iban', 'bic', 'currency'];
    foreach ($required_fields_personal as $field) {
        if (empty($personal_data[$field])) {
            addNotification($con, $user_id, "Manque de données de facturation pour un compte individuel. Le champ '$field' est vide.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
            header("Location: /index.php");
            exit();
        }
    }

} elseif ($acc_user_type === 'company') {
    $stmt = $con->prepare("SELECT * FROM business_account_details WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_company = $stmt->get_result();

    if ($result_company->num_rows === 0) {
        addNotification($con, $user_id, "Manque de données de facturation pour un compte entreprise.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
        header("Location: /index.php");
        exit();
    }

    $company_data = $result_company->fetch_assoc();
    $required_fields_company = ['company_name', 'company_registration', 'company_address', 'company_email', 'bank_name', 'iban', 'currency'];
    foreach ($required_fields_company as $field) {
        if (empty($company_data[$field])) {
            addNotification($con, $user_id, "Manque de données de facturation pour un compte entreprise. Le champ '$field' est vide.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
            header("Location: /index.php");
            exit();
        }
    }
} else {
    addNotification($con, $user_id, "Type d'utilisateur inconnu ou invalide.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
    header("Location: /index.php");
    exit();
}

// Vérifier si $acc_money est supérieur ou égal à 15
if ($acc_money < 15) {
    addNotification($con, $user_id, "Le montant minimum pour un retrait est de 15€. Votre solde actuel est insuffisant.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
    header("Location: /index.php");
    exit();
}

// Préparer les données pour l'insertion dans la table withdrawal
$stmt = $con->prepare("INSERT INTO withdrawal (user_id_withdrawal, amount_withdrawal, payment_method_withdrawal) VALUES (?, ?, ?)");
$stmt->bind_param("ids", $user_id, $acc_money, $payment_method_withdrawal);
$payment_method_withdrawal = "Carte bancaire";

// Exécuter la requête de retrait
if ($stmt->execute()) {
    addNotification($con, $user_id, "Votre demande de retrait a été enregistrée avec succès.", "Succès", $no_connect_user_id ?? null, $no_connect_ip ?? null);

    // Insérer une transaction dans la table money pour représenter le retrait
    $stmt = $con->prepare("INSERT INTO money (user_id, montant, reason) VALUES (?, ?, ?)");
    $montant = -$acc_money;
    $reason = 'withdrawal';
    $stmt->bind_param("ids", $user_id, $montant, $reason);

    if ($stmt->execute()) {
        addNotification($con, $user_id, "Votre retrait a été traité avec succès.", "Succès", $no_connect_user_id ?? null, $no_connect_ip ?? null);
    } else {
        addNotification($con, $user_id, "Erreur lors de l'enregistrement de la transaction de retrait dans la table money.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
    }

    header("Location: /index.php");
    exit();
} else {
    addNotification($con, $user_id, "Impossible d'enregistrer votre demande de retrait. Veuillez réessayer plus tard.", "Erreur", $no_connect_user_id ?? null, $no_connect_ip ?? null);
    header("Location: /index.php");
    exit();
}

// Fermer la connexion à la base de données
$con->close();
?>