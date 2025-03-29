<?php
// Code pour récupérer la version du plugin et l'envoyer en réponse en XML
$version = "1.0.1"; // Version actuelle du plugin, vous pouvez la remplacer par votre vraie version

// Crée le XML
$xml = new SimpleXMLElement('<plugin/>');
$xml->addChild('version', $version);

// Envoie l'en-tête de réponse XML
header('Content-Type: application/xml');
echo $xml->asXML();
?>