<?php
// File: form.php

require_once 'db_connection.php';

// Inizializza le variabili
$anagrafica = [
    'id' => null,
    'nome' => '',
    'cognome' => '',
    'sesso' => '',
    'data_nascita' => '',
    'codice_fiscale' => ''
];
$page_title = 'Aggiungi Nuova Anagrafica';
$action = 'add'; // Azione predefinita: aggiunta

// Verifica se è stata passata un ID per la modifica
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_anagrafica = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id_anagrafica) {
        try {
            // Prepara e esegui la query per ottenere i dati dell'anagrafica da modificare
            $sql = "SELECT * FROM anagrafiche WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id_anagrafica, PDO::PARAM_INT);
            $stmt->execute();
            $anagrafica_db = $stmt->fetch();

            // Se l'anagrafica esiste, popola l'array $anagrafica
            if ($anagrafica_db) {
                $anagrafica = $anagrafica_db;
                $page_title = 'Modifica Anagrafica';
                $action = 'edit'; // Cambia azione in modifica
            } else {
                // Anagrafica non trovata, potresti reindirizzare o mostrare un errore
                die("Anagrafica non trovata.");
            }
        } catch (\PDOException $e) {
            die("Errore durante il recupero dell'anagrafica: " . $e->getMessage());
        }
    } else {
         die("ID anagrafica non valido.");
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Link opzionale al CSS -->
    <link rel="stylesheet" href="style.css">
     <style>
        /* Stili base se non usi style.css */
        body { font-family: sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding e border nella larghezza totale */
        }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .back-link { display: block; margin-top: 20px; }
    </style>
</head>
<body>

    <h1><?php echo $page_title; ?></h1>

    <!-- Il form invia i dati a save.php usando il metodo POST -->
    <form action="save.php" method="post">

        <!-- Campo nascosto per inviare l'ID in caso di modifica -->
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($anagrafica['id']); ?>">
        <?php endif; ?>

         <!-- Campo nascosto per specificare l'azione (non strettamente necessario se distinguiamo solo dalla presenza dell'ID) -->
         <input type="hidden" name="action" value="<?php echo $action; ?>">

        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($anagrafica['nome']); ?>" required>
        </div>

        <div class="form-group">
            <label for="cognome">Cognome:</label>
            <input type="text" id="cognome" name="cognome" value="<?php echo htmlspecialchars($anagrafica['cognome']); ?>" required>
        </div>

        <div class="form-group">
            <label for="sesso">Sesso:</label>
            <select id="sesso" name="sesso">
                <option value="">-- Seleziona --</option>
                <option value="Maschio" <?php echo ($anagrafica['sesso'] === 'Maschio') ? 'selected' : ''; ?>>Maschio</option>
                <option value="Femmina" <?php echo ($anagrafica['sesso'] === 'Femmina') ? 'selected' : ''; ?>>Femmina</option>
                <option value="Altro" <?php echo ($anagrafica['sesso'] === 'Altro') ? 'selected' : ''; ?>>Altro</option>
            </select>
        </div>

        <div class="form-group">
            <label for="data_nascita">Data di Nascita:</label>
            <input type="date" id="data_nascita" name="data_nascita" value="<?php echo htmlspecialchars($anagrafica['data_nascita']); ?>">
        </div>

        <div class="form-group">
            <label for="codice_fiscale">Codice Fiscale:</label>
            <input type="text" id="codice_fiscale" name="codice_fiscale" value="<?php echo htmlspecialchars($anagrafica['codice_fiscale']); ?>" pattern="[A-Za-z]{6}[0-9]{2}[A-Za-z]{1}[0-9]{2}[A-Za-z]{1}[0-9]{3}[A-Za-z]{1}" title="Inserisci un codice fiscale valido (es. RSSMRA80A01H501A)">
            <!-- Nota: Il pattern è una validazione HTML base, non sostituisce la validazione lato server -->
        </div>

        <button type="submit">Salva Anagrafica</button>
    </form>

    <a href="index.php" class="back-link">Torna all'elenco</a>

</body>
</html>