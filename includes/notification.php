<!-- HTML pour afficher les notifications -->
<div id="unique-notifications-container">
    <?php
    foreach ($notifications as $notification) {
        $iconMapping = [
            'Attention' => 'fa-exclamation-triangle',
            'Succès' => 'fa-check-circle',
            'Erreur' => 'fa-times-circle',
            'Email' => 'fa-envelope'
        ];

        $alertClassMapping = [
            'Attention' => 'unique-information_attention',
            'Succès' => 'unique-success',
            'Erreur' => 'unique-failure',
            'Email' => 'unique-verify_email'
        ];

        $statut = $notification['statut'];

        // Affichage de chaque notification sous forme de carte
        echo '<div class="unique-notification_card">';
        echo '<div class="unique-notification alert ' . htmlspecialchars($alertClassMapping[$statut] ?? '') . '" data-id="' . htmlspecialchars($notification['id']) . '">';
        echo '<div class="d-flex align-items-center">';
        echo '<div class="alert_icon me-3">';
        echo '<i class="fa ' . htmlspecialchars($iconMapping[$statut] ?? '') . '"></i>';
        echo '</div>';
        echo '<p>' . htmlspecialchars($notification['message']) . '</p>';
        echo '</div>';
        echo '<span class="close" aria-label="Close">';
        echo '<i class="la la-close"></i>';
        echo '</span>';
        echo '</div>';
        echo '</div>';
    }
    ?>
</div>


<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/langage.php'; ?>