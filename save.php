<?php
// File: save.php

require_once 'db_connection.php';

// Verifica che la richiesta sia di tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera i dati dal form (fare validazione/sanificazione più robusta in produzione!)
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // Sarà null o false se non presente/valido
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING); // 'add' or 'edit'
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $cognome = filter_input(INPUT_POST, 'cognome', FILTER_SANITIZE_STRING);
    $sesso = filter_input(INPUT_POST, 'sesso', FILTER_SANITIZE_STRING);
    // Per la data, non usare sanitize string, controlla se è una data valida
    $data_nascita = $_POST['data_nascita']; // Validare formato yyyy-mm-dd
    // Se vuota, imposta a NULL per il database
    if (empty($data_nascita)) {
        $data_nascita = null;
    } elseif (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$data_nascita)) {
         die("Formato data non valido. Usa YYYY-MM-DD."); // Validazione semplice
    }

    $codice_fiscale = filter_input(INPUT_POST, 'codice_fiscale', FILTER_SANITIZE_STRING);
    // Potresti aggiungere validazione più specifica per il codice fiscale qui

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);

    // ---- Validazione Base ----
    if (empty($nome) || empty($cognome)) {
        die("Nome e Cognome sono obbligatori.");
    }
    // Aggiungere altre validazioni se necessario...

    try {
        // Se c'è un ID e l'azione è 'edit', aggiorna il record esistente
        if ($id && $action === 'edit') {
            $sql = "UPDATE anagrafiche
                    SET nome = :nome,
                        cognome = :cognome,
                        sesso = :sesso,
                        data_nascita = :data_nascita,
                        codice_fiscale = :codice_fiscale,
                        email = :email
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Altrimenti (nessun ID valido o azione 'add'), inserisci un nuovo record
        } else {
            $sql = "INSERT INTO anagrafiche (nome, cognome, sesso, data_nascita, codice_fiscale, email)
                    VALUES (:nome, :cognome, :sesso, :data_nascita, :codice_fiscale, :email)";
            $stmt = $pdo->prepare($sql);
        }

        // Associa i valori ai placeholder (comune sia per INSERT che per UPDATE)
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':cognome', $cognome, PDO::PARAM_STR);
        $stmt->bindParam(':sesso', $sesso, PDO::PARAM_STR);
        $stmt->bindParam(':data_nascita', $data_nascita); // PDO gestisce NULL
        $stmt->bindParam(':codice_fiscale', $codice_fiscale, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);


        // Esegui la query
        $stmt->execute();

        // Reindirizza alla homepage dopo il salvataggio
        header('Location: index.php');
        exit; // Termina lo script dopo il reindirizzamento

    } catch (\PDOException $e) {
        // Gestione errore più specifica: es. codice fiscale duplicato?
        if ($e->getCode() == 23000) { // Codice errore SQL per violazione constraint (es. UNIQUE)
             die("Errore durante il salvataggio: possibile duplicato (es. Codice Fiscale già esistente?). Dettagli: " . $e->getMessage());
        } else {
             die("Errore durante il salvataggio dell'anagrafica: " . $e->getMessage());
        }
    }

} else {
    // Se qualcuno tenta di accedere a save.php direttamente via GET
    echo "Metodo non supportato.";
    // Oppure reindirizza a index.php
    // header('Location: index.php');
    // exit;
}
?>