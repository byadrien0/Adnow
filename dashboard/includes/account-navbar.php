<?php
// Détermine le nom du fichier de la page actuelle
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Account page navigation -->
<nav class="nav nav-borders">
    <a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"
        href="/dashboard/z-account/profile.php">Profil</a>
    <a class="nav-link <?php echo ($current_page == 'withdrawals.php') ? 'active' : ''; ?>"
        href="/dashboard/z-account/withdrawals.php">Retrait</a>
    <a class="nav-link <?php echo ($current_page == 'invoices.php') ? 'active' : ''; ?>"
        href="/dashboard/z-account/invoices.php">Factures</a>
</nav>