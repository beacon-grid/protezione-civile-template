<?php
/**
 * Proxy con cache per i frame radar in tempo reale.
 *
 * Fonte: LibreWXR (https://librewxr.net), progetto open source self-hostabile,
 * API compatibile con RainViewer v2. Per l'Italia usa come sorgente nativa
 * Radar-DPC (Dipartimento della Protezione Civile): composito nazionale a
 * 24 radar (11 DPC diretti + 13 di reti regionali/Enav/Aeronautica Militare),
 * aggiornato ogni 5 minuti — molto più accurato del mosaico europeo generico
 * su cui si basava la vecchia integrazione RainViewer.
 *
 * Di default puntiamo all'istanza pubblica di LibreWXR. Per traffico più
 * alto o per non dipendere da un'istanza di terzi, si può self-hostare
 * LibreWXR (Docker, vedi https://github.com/JoshuaKimsey/LibreWXR) e
 * cambiare solo la costante LIBREWXR_BASE_URL qui sotto.
 *
 * Licenza dati: CC-BY-4.0 sull'aggregato LibreWXR; le tile che includono
 * dati radar italiani ereditano la clausola share-alike CC-BY-SA 4.0 di
 * DPC (va citato "Radar-DPC" come fonte in UI, oltre a LibreWXR).
 *
 * Il radar è un dato globale rispetto alla mappa (non serve lat/lng per
 * scaricarlo: il frontend richiede i tile con il proprio bbox), quindi
 * qui non c'è cache per posizione: c'è un'unica cache condivisa da tutti
 * gli utenti dell'app.
 *
 * Uso:
 *   get_radar.php   -> restituisce host + lista frame passati/nowcast,
 *                      pronti per essere usati come tile layer (es. Leaflet)
 */

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/php/database.php';

const CACHE_TTL_RADAR = 300; // 5 minuti, coerente col passo di aggiornamento di Radar-DPC
const USER_AGENT_GENERICO = 'BollettinoProtezioneCivile/1.0';

// Cambia questa costante per puntare a una tua istanza self-hosted di LibreWXR.
const LIBREWXR_BASE_URL = 'https://api.librewxr.net';

function chiamaUrlEsterno(string $url, string $userAgent): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_USERAGENT => $userAgent,
        ]);
        $risposta = curl_exec($ch);
        $codice = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($risposta !== false && $codice === 200) ? $risposta : null;
    }

    $contesto = stream_context_create([
        'http' => ['timeout' => 8, 'header' => "User-Agent: {$userAgent}\r\n"],
    ]);
    $risposta = @file_get_contents($url, false, $contesto);
    return $risposta !== false ? $risposta : null;
}

function serviConCache(string $chiaveCache, int $ttl, string $url, string $userAgent): ?array
{
    $key = 'radar:' . $chiaveCache;
    $contenuto = app_cache_read($key, $ttl);
    if ($contenuto !== null) {
        return json_decode($contenuto, true);
    }

    $risposta = chiamaUrlEsterno($url, $userAgent);

    if ($risposta === null) {
        $contenuto = app_cache_read($key);
        if ($contenuto !== null) {
            return json_decode($contenuto, true);
        }
        return null;
    }

    app_cache_write($key, $risposta);
    return json_decode($risposta, true);
}

$datiRadar = serviConCache(
    'librewxr_frames',
    CACHE_TTL_RADAR,
    LIBREWXR_BASE_URL . '/public/weather-maps.json',
    USER_AGENT_GENERICO
);

if (!$datiRadar) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Servizio radar non raggiungibile']);
    exit;
}

// Stesso formato che usavi con RainViewer: host + path di ogni frame
// passato e, se disponibile, il nowcast (previsione radar a brevissimo termine).
$risposta = [
    'fonte' => 'librewxr (radar Italia: DPC)',
    'host' => $datiRadar['host'] ?? LIBREWXR_BASE_URL,
    'frame_passati' => $datiRadar['radar']['past'] ?? [],
    'frame_nowcast' => $datiRadar['radar']['nowcast'] ?? [],
    'color_schemes' => $datiRadar['radar']['colorSchemes'] ?? [],
];

echo json_encode($risposta);