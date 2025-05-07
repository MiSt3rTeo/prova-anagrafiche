-- Script per creare la tabella 'anagrafiche'

-- Se la tabella esiste già, puoi decidere di eliminarla prima (ATTENZIONE: questo cancella tutti i dati esistenti)
-- DROP TABLE IF EXISTS anagrafiche;

CREATE TABLE IF NOT EXISTS `anagrafiche` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `cognome` VARCHAR(100) NOT NULL,
  `sesso` VARCHAR(10) DEFAULT NULL,
  `data_nascita` DATE DEFAULT NULL,
  `codice_fiscale` VARCHAR(16) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `comune` VARCHAR(255) DEFAULT NULL,
  `provincia` VARCHAR(2) DEFAULT NULL,
  `professione` VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY `idx_codice_fiscale_unique` (`codice_fiscale`) -- Assicura che il codice fiscale sia unico
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Descrizione della tabella (opzionale, per verifica)
-- DESCRIBE anagrafiche;
