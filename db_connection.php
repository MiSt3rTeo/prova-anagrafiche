<?php
// File: db_connection.php

$db_host = 'localhost';      // Solitamente 'localhost' o '127.0.0.1' con Laragon
$db_name = 'anagrafiche'; // Il nome del database che hai creato
$db_user = 'root';           // L'utente standard di Laragon
$db_pass = '';               // La password standard di Laragon è vuota
$charset = 'utf8mb4';

// Opzioni PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Trasforma errori SQL in eccezioni PHP
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Restituisce righe come array associativi
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa prepared statements nativi del DB
];

// Data Source Name (DSN)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

try {
    // Crea l'oggetto PDO (la connessione al database)
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // In caso di errore di connessione, mostra un messaggio e interrompi lo script
    // In un'app reale, potresti voler loggare l'errore invece di mostrarlo all'utente
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
    // die("Errore di connessione al database: " . $e->getMessage()); // Alternativa più semplice
}

// Ora la variabile $pdo contiene la connessione al database ed è disponibile
// per gli script che includono questo file.
?>