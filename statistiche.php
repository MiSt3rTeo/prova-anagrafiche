<?php
// File: c:\laragon\www\anagrafiche\statistiche.php

require_once 'db_connection.php';

// --- 1. Dati per Grafico Sesso ---
$sesso_data = [];
try {
    $stmt = $pdo->query("SELECT sesso, COUNT(*) as count FROM anagrafiche WHERE sesso IS NOT NULL AND sesso != '' GROUP BY sesso");
    $sesso_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $sesso_data['labels'] = array_keys($sesso_counts);
    $sesso_data['values'] = array_values($sesso_counts);
} catch (\PDOException $e) {
    // Gestisci errore, per ora semplice die
    die("Errore recupero dati sesso: " . $e->getMessage());
}

// --- 2. Dati per Grafico Range Età ---
$eta_ranges = [
    '0-6' => 0, '7-12' => 0, '13-18' => 0, '19-30' => 0,
    '31-45' => 0, '46-60' => 0, '61-80' => 0, '81+' => 0
];
try {
    $stmt = $pdo->query("SELECT data_nascita FROM anagrafiche WHERE data_nascita IS NOT NULL");
    $date_nascita = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $oggi = new DateTime();

    foreach ($date_nascita as $dn_str) {
        if ($dn_str) {
            $data_nascita_dt = new DateTime($dn_str);
            $eta = $oggi->diff($data_nascita_dt)->y;

            if ($eta <= 6) $eta_ranges['0-6']++;
            elseif ($eta <= 12) $eta_ranges['7-12']++;
            elseif ($eta <= 18) $eta_ranges['13-18']++;
            elseif ($eta <= 30) $eta_ranges['19-30']++;
            elseif ($eta <= 45) $eta_ranges['31-45']++;
            elseif ($eta <= 60) $eta_ranges['46-60']++;
            elseif ($eta <= 80) $eta_ranges['61-80']++;
            else $eta_ranges['81+']++;
        }
    }
} catch (\PDOException $e) {
    die("Errore recupero dati età: " . $e->getMessage());
}
$eta_data['labels'] = array_keys($eta_ranges);
$eta_data['values'] = array_values($eta_ranges);

// --- 3. Dati per Grafico Professioni ---
$professione_data = [];
try {
    $stmt = $pdo->query("SELECT professione, COUNT(*) as count FROM anagrafiche WHERE professione IS NOT NULL AND professione != '' GROUP BY professione ORDER BY count DESC LIMIT 15"); // Limita per leggibilità
    $professione_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $professione_data['labels'] = array_keys($professione_counts);
    $professione_data['values'] = array_values($professione_counts);
} catch (\PDOException $e) {
    die("Errore recupero dati professione: " . $e->getMessage());
}

// --- 4. Dati per Heatmap Comuni ---
$comuni_counts = []; // Inizializza per evitare errori se la query fallisce o non ci sono dati
try {
    $stmt = $pdo->query("SELECT comune, COUNT(*) as count FROM anagrafiche WHERE comune IS NOT NULL AND comune != '' GROUP BY comune");
    $comuni_counts = $stmt->fetchAll(PDO::FETCH_ASSOC); // Array di {comune: 'nome', count: N}
} catch (\PDOException $e) {
    die("Errore recupero dati comuni: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche Pazienti</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- Leaflet.heat plugin -->
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .chart-container { width: 45%; margin: 20px; display: inline-block; vertical-align: top; }
        .chart-container-full { width: 90%; margin: 20px auto; }
        #map { height: 500px; width: 90%; margin: 20px auto; border: 1px solid #ccc; }
        h1, h2 { text-align: center; }
        .back-link { display: block; margin-top: 20px; text-align: center; }
        .map-message { text-align:center; margin-top: 50px; }
    </style>
</head>
<body>
    <h1>Statistiche Pazienti</h1>

    <div class="chart-container">
        <h2>Distribuzione per Sesso</h2>
        <canvas id="sessoChart"></canvas>
    </div>

    <div class="chart-container">
        <h2>Distribuzione per Età</h2>
        <canvas id="etaChart"></canvas>
    </div>

    <div class="chart-container-full">
        <h2>Pazienti per Professione (Top 15)</h2>
        <canvas id="professioneChart"></canvas>
    </div>

    <div class="chart-container-full">
        <h2>Distribuzione Geografica Pazienti (Heatmap)</h2>
        <div id="map"></div>
    </div>

    <a href="index.php" class="back-link">Torna all'elenco</a>

    <script>
        // Dati PHP passati a JavaScript
        const sessoData = <?php echo json_encode($sesso_data); ?>;
        const etaData = <?php echo json_encode($eta_data); ?>;
        const professioneData = <?php echo json_encode($professione_data); ?>;
        const comuniPerHeatmap = <?php echo json_encode($comuni_counts); ?>;

        // Grafico Sesso
        new Chart(document.getElementById('sessoChart'), {
            type: 'pie',
            data: {
                labels: sessoData.labels,
                datasets: [{
                    label: 'Sesso',
                    data: sessoData.values,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });

        // Grafico Età
        new Chart(document.getElementById('etaChart'), {
            type: 'pie',
            data: {
                labels: etaData.labels,
                datasets: [{
                    label: 'Range Età',
                    data: etaData.values,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#C9CBCF', '#7BC225'
                    ]
                }]
            }
        });

        // Grafico Professioni
        new Chart(document.getElementById('professioneChart'), {
            type: 'bar', // Grafico a barre orizzontali si ottiene con 'bar' e indexAxis: 'y'
            data: {
                labels: professioneData.labels,
                datasets: [{
                    label: 'Numero Pazienti',
                    data: professioneData.values,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                indexAxis: 'y', // Per renderlo a barre orizzontali
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Leaflet Heatmap
        let map;
        let heatmapDataPoints = [];

        function initLeafletMap() {
            const mapDiv = document.getElementById('map');
            let messageElement = mapDiv.querySelector('p.map-message-overlay'); // Cerca un elemento messaggio esistente

            // Se non esiste, crealo e aggiungilo a mapDiv
            if (!messageElement) {
                messageElement = document.createElement('p');
                messageElement.className = 'map-message map-message-overlay'; // Usa la classe esistente per lo stile base
                // Puoi aggiungere stili specifici qui se vuoi sovrapporlo meglio, es:
                // messageElement.style.position = 'absolute'; messageElement.style.top = '10px'; messageElement.style.left = '10px'; messageElement.style.zIndex = '1000'; messageElement.style.backgroundColor='white'; messageElement.style.padding='5px';
                mapDiv.appendChild(messageElement);
            }

            map = L.map(mapDiv).setView([41.8719, 12.5674], 6); // Centro Italia, zoom 6
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            if (comuniPerHeatmap.length === 0) {
                console.log("Nessun dato comune per la heatmap.");
                messageElement.textContent = 'Nessun dato comune disponibile per la heatmap.';
                return;
            }

            let processedCount = 0;
            const totalComuni = comuniPerHeatmap.length;
            messageElement.textContent = `Caricamento dati geografici per ${totalComuni} comuni... (potrebbe richiedere tempo)`;

            comuniPerHeatmap.forEach((item, index) => {
                // Ritardo progressivo per rispettare la policy di Nominatim (circa 1 richiesta/sec)
                // Per un'app in produzione, considerare una coda di richieste più robusta o geocodifica offline.
                setTimeout(() => {
                    geocodeNominatim(item.comune, item.count, () => {
                        processedCount++;
                        messageElement.textContent = `Geocodifica in corso: ${processedCount}/${totalComuni} comuni processati...`;
                        if (processedCount === totalComuni) {
                            if (heatmapDataPoints.length > 0) {
                                L.heatLayer(heatmapDataPoints, {
                                    radius: 30, // Raggio dei punti della heatmap
                                    blur: 20,   // Sfocatura
                                    maxZoom: 12, // Zoom massimo a cui la heatmap è visibile
                                    // gradient: {0.4: 'blue', 0.65: 'lime', 1: 'red'} // Esempio di gradiente personalizzato
                                }).addTo(map);
                                if (messageElement) messageElement.remove(); // Rimuovi il messaggio quando la heatmap è pronta
                                // Potrebbe essere necessario invalidare la dimensione della mappa se il div era nascosto o ridimensionato
                                map.invalidateSize();
                            } else {
                                console.log("Nessun punto valido per la heatmap dopo geocoding con Nominatim.");
                                messageElement.textContent = 'Geocodifica non riuscita o nessun comune trovato per la heatmap.';
                                messageElement.style.color = 'orange'; // Mantieni il messaggio di errore visibile
                            }
                        }
                    });
                }, index * 1200); // Ritardo di 1.2 secondi tra le richieste
            });
        }

        async function geocodeNominatim(address, weight, callback) {
            const query = encodeURIComponent(address + ', Italia'); // Aggiungo ', Italia' per precisione
            // Documentazione API Nominatim: https://nominatim.org/release-docs/develop/api/Search/
            // Policy di utilizzo: https://operations.osmfoundation.org/policies/nominatim/
            // È importante fornire un User-Agent personalizzato per applicazioni serie.
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}&limit=1&countrycodes=it`;

            try {
                const response = await fetch(url, {
                    headers: {
                        // Per un'app reale, imposta un User-Agent descrittivo:
                        // 'User-Agent': 'MiaAppAnagrafiche/1.0 (contatto@tuodominio.it)'
                    }
                });
                if (!response.ok) {
                    console.error(`Errore HTTP da Nominatim: ${response.status} per l'indirizzo: ${address}`);
                    if (response.status === 429) { // Too Many Requests
                        console.warn("Raggiunto limite richieste Nominatim. Riprova più tardi o riduci la frequenza.");
                    }
                    if (callback) callback();
                    return;
                }
                const data = await response.json();
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    // Leaflet.heat si aspetta un array di [latitudine, longitudine, intensità (opzionale)]
                    heatmapDataPoints.push([lat, lon, parseInt(weight)]);
                    console.log(`Geocodificato (Nominatim): ${address} -> Lat: ${lat}, Lon: ${lon}`);
                } else {
                    console.warn(`Nominatim non ha trovato risultati per: ${address}`);
                }
            } catch (error) {
                console.error(`Errore durante la geocodifica con Nominatim per ${address}:`, error);
            } finally {
                if (callback) callback();
            }
        }

        // Inizializza la mappa dopo che il DOM è pronto e i grafici sono stati creati
        // (o almeno dopo che i dati PHP sono disponibili per JavaScript)
        document.addEventListener('DOMContentLoaded', function() {
            initLeafletMap();
        });

    </script>
</body>
</html>