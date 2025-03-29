<?php

include $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/plugins/minecraft/api_functions.php';



if ($con->connect_error) {
    // Message d'échec de connexion à la base de données
    $response = array(
        'status' => 'error',
        'message' => 'Failed to connect to database: ' . $con->connect_error
    );
} else {
    // Message de réussite de la connexion à la base de données
    $response = array(
        'status' => 'success',
        'message' => 'Database connection established'
    );
}

// Convertir le tableau en format XML
$xml_data = new SimpleXMLElement('<?xml version="1.0"?><response></response>');
array_to_xml($response, $xml_data);
echo $xml_data->asXML();

// Fermer la connexion
$con->close();
?>