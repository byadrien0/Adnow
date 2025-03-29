<?php

// Connexion à la base de données MySQL
define('DB_SERVER', 'adnow.online');
define('DB_USER', 'byadrien');
define('DB_PASS', '7PemWiuBtX4uMEJnneyO');
define('DB_NAME', 'adnow');

$con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if (!$con) {
    trigger_error("Failed to connect to MySQL: " . mysqli_connect_error(), E_USER_ERROR);
}

?>