<nav class="sidenav shadow-right sidenav-light">
    <div class="sidenav-menu">
        <div class="nav accordion" id="accordionSidenav">

            <!-- Sidenav Menu Heading (Speed) -->
            <div class="sidenav-menu-heading">Rapidité</div>

            <!-- Sidenav Link (Home) -->
            <a class="nav-link" href="/">
                <div class="nav-link-icon"><i class="fas fa-home"></i></div>
                Accueil
            </a>

            <!-- Sidenav Link (Dashboard) -->
            <a class="nav-link" href="/dashboard/">
                <div class="nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                Tableau de bord
            </a>

            <!-- Nouvelle Catégorie (Management) -->
            <div class="sidenav-menu-heading">Gestion</div>

            <!-- Sidenav Accordion (Servers) -->
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseServers" aria-expanded="false" aria-controls="collapseServers">
                <div class="nav-link-icon"><i class="fas fa-server"></i></div>
                Mes Serveurs
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseServers" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav">
                    <a class="nav-link" href="/dashboard/z-minecraft/server-list.php">Minecraft</a>
                    <a class="nav-link" href="/dashboard/z-discord/server-list.php">
                        Discord
                        <span class="badge bg-primary-soft text-primary ms-auto">Nouveau</span>
                    </a>
                    <a class="nav-link" href="/dashboard/z-account/add-server.php">Ajouter un Serveur</a>
                </nav>
            </div>

            <!-- Nouvelle Catégorie (Campaign) -->
            <div class="sidenav-menu-heading">Campagne</div>

            <!-- Sidenav Accordion (Campaign) -->
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseCampaign" aria-expanded="false" aria-controls="collapseCampaign">
                <div class="nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                Campagnes
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseCampaign" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav">
                    <a class="nav-link" href="/dashboard/z-campaigns/campaign-create.php">Créer une campagne</a>
                    <a class="nav-link" href="/dashboard/z-campaigns/campaigns-list.php">Mes campagne créée</a>
                </nav>
            </div>

            <!-- Nouvelle Catégorie (Account) -->
            <div class="sidenav-menu-heading">Compte</div>

            <!-- Sidenav Accordion (Account) -->
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseAccount" aria-expanded="true" aria-controls="collapseAccount">
                <div class="nav-link-icon"><i class="fas fa-user"></i></div>
                Compte
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse show" id="collapseAccount" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav">
                    <a class="nav-link" href="/dashboard/z-account/profile.php">Profil</a>
                    <a class="nav-link" href="/dashboard/z-account/withdrawals.php">Retraits</a>
                    <a class="nav-link" href="/dashboard/z-account/invoices.php">Factures</a>
                </nav>
            </div>

        </div>
    </div>

    <!-- Sidenav Footer -->
    <div class="sidenav-footer">
        <div class="sidenav-footer-content">
            <div class="sidenav-footer-subtitle">Connecté en tant que :</div>
            <div class="sidenav-footer-title"><?php echo htmlspecialchars($acc_username); ?></div>
        </div>
    </div>
</nav>