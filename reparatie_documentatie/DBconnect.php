<?php
// Database configuratie
$dbHost = 'localhost'; // Verander dit indien nodig
$dbUsername = 'root'; // Verander dit indien nodig
$dbPassword = ''; // Verander dit indien nodig
$dbName = 'reparatie'; // Naam van de database

// Verbinding maken met de database via PDO
try {
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    // Stel de foutmodus in op uitzonderingen
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Verbinding mislukt: " . $e->getMessage());
}



?>
