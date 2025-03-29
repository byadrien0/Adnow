<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/notification.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/cookie.php'; ?>



<nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white"
    id="sidenavAccordion">
    <!-- Sidenav Toggle Button-->
    <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle"><svg
            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="feather feather-menu">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg></button>

    <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="index.html">DASHBOARD</a>
    <!-- Navbar Search Input-->

    <!-- Navbar Items-->
    <ul class="navbar-nav align-items-center ms-auto">


        <li class="nav-item dropdown no-caret d-none d-md-block me-3">
            <a class="nav-link dropdown-toggle" id="navbarDropdownDocs" href="#" role="button" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <div class="fw-500">Ressources</div>
                <svg class="svg-inline--fa fa-chevron-down dropdown-arrow" aria-hidden="true" focusable="false"
                    data-prefix="fas" data-icon="chevron-down" role="img" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 320 512" data-fa-i2svg="">
                    <path fill="currentColor"
                        d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z">
                    </path>
                </svg>
            </a>
            <div class="dropdown-menu dropdown-menu-end py-0 me-sm-n15 me-lg-0 o-hidden animated--fade-in-up"
                aria-labelledby="navbarDropdownDocs">

                <a class="dropdown-item py-3"
                    href="https://docs.google.com/document/d/1y3eie80A2LUjSF5B97TzRJy83rdBIfdMHO0aRbNTepA/edit?usp=drive_link"
                    target="_blank">
                    <div class="icon-stack bg-primary-soft text-primary me-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-file-text">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-gray-500">Conditions Générales de Vente</div>
                        Informez-vous sur vos droits lors des ventes
                    </div>
                </a>

                <a class="dropdown-item py-3"
                    href="https://docs.google.com/document/d/1FM5ierbuKTZB0H4m4XQPhGfU_jIFpqdlVEm3q6Yb8dQ/edit?usp=sharing"
                    target="_blank">
                    <div class="icon-stack bg-primary-soft text-primary me-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-file-text">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-gray-500">Mentions Légales</div>
                        Découvrez notre identité et notre fonctionnement
                    </div>
                </a>

                <a class="dropdown-item py-3"
                    href="https://docs.google.com/document/d/1UnWb-RH-oNOeHlOxrc_dUHcQdRpeRJNmVvuDfXDJAg8/edit?usp=sharing"
                    target="_blank">
                    <div class="icon-stack bg-primary-soft text-primary me-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-file-text">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-gray-500">Politique de Confidentialité</div>
                        Apprenez comment nous protégeons vos données
                    </div>
                </a>

                <a class="dropdown-item py-3"
                    href="https://docs.google.com/document/d/1FM5ierbuKTZB0H4m4XQPhGfU_jIFpqdlVEm3q6Yb8dQ/edit?usp=sharing"
                    target="_blank">
                    <div class="icon-stack bg-primary-soft text-primary me-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-file-text">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-gray-500">Conditions Générales d'Utilisation</div>
                        Consultez nos conditions d'utilisation
                    </div>
                </a>

                <a class="dropdown-item py-3" href="https://www.example.com/cookies-policy" target="_blank">
                    <div class="icon-stack bg-primary-soft text-primary me-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-info">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="8"></line>
                        </svg>
                    </div>
                    <div>
                        <div class="small text-gray-500">Politique de Cookies</div>
                        Informez-vous sur notre utilisation des cookies
                    </div>
                </a>

            </div>
        </li>



        <!-- User Dropdown-->
        <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
            <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage"
                href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false"><img class="img-fluid" src="<?php echo "$acc_url"; ?>"></a>
            <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                aria-labelledby="navbarDropdownUserImage">
                <h6 class="dropdown-header d-flex align-items-center">
                    <img class="dropdown-user-img" src="<?php echo "$acc_url"; ?>">
                    <div class="dropdown-user-details">
                        <div class="dropdown-user-details-name"><?php echo htmlspecialchars($acc_username); ?></div>
                        <div class="dropdown-user-details-email"><?php echo htmlspecialchars($acc_email); ?></div>
                    </div>
                </h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/dashboard/z-account/logout.php">
                    <div class="dropdown-item-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg></div>
                    Déconnexion
                </a>
            </div>
        </li>
    </ul>
</nav>