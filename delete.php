<?php
// File: delete.php

require_once 'db_connection.php';

// Verifica se è stato passato un ID valido nell'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_anagrafica = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id_anagrafica) {
        try {
            // Prepara la query DELETE
            $sql = "DELETE FROM anagrafiche WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            // Associa l'ID al placeholder
            $stmt->bindParam(':id', $id_anagrafica, PDO::PARAM_INT);

            // Esegui la query
            $stmt->execute();

            // Reindirizza alla homepage dopo l'eliminazione
            header('Location: index.php');
            exit;

        } catch (\PDOException $e) {
            die("Errore durante l'eliminazione dell'anagrafica: " . $e->getMessage());
        }
    } else {
        die("ID anagrafica non valido per l'eliminazione.");
    }
} else {
    // Se qualcuno tenta di accedere a delete.php senza ID
    echo "ID anagrafica mancante.";
    // Oppure reindirizza a index.php
    // header('Location: index.php');
    // exit;
}
?>