<?php
// File: index.php

// Include il file di connessione al database
require_once 'db_connection.php';

// Prepara e esegui la query per ottenere tutte le anagrafiche
// Ordiniamo per cognome e poi per nome
try {
    $stmt = $pdo->query('SELECT id, nome, cognome, sesso, data_nascita, codice_fiscale, email FROM anagrafiche ORDER BY cognome, nome');
    // Non è necessario $stmt->execute() per query semplici senza parametri
} catch (\PDOException $e) {
    die("Errore durante il recupero delle anagrafiche: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Anagrafiche</title>
    <!-- Link opzionale al CSS -->
    <link rel="stylesheet" href="style.css">
    <style>
        /* Stili base se non usi style.css */
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
        .actions a { margin-right: 10px; }
        .add-button { display: inline-block; padding: 10px 15px; background-color: #28a745; color: white; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <h1>Elenco Anagrafiche</h1>

    <a href="form.php" class="add-button">Aggiungi Nuova Anagrafica</a>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Cognome</th>
                <th>Sesso</th>
                <th>Data di Nascita</th>
                <th>Codice Fiscale</th>
                <th>Email</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($stmt->rowCount() > 0): ?>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['cognome']); ?></td>
                    <td><?php echo htmlspecialchars($row['sesso']); ?></td>
                    <td><?php echo htmlspecialchars($row['data_nascita'] ? date('d/m/Y', strtotime($row['data_nascita'])) : ''); ?></td>
                    <td><?php echo htmlspecialchars($row['codice_fiscale']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td class="actions">
                        <a href="form.php?id=<?php echo $row['id']; ?>">Modifica</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questa anagrafica?');">Elimina</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Nessuna anagrafica trovata.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>