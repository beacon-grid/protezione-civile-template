<?php
/**
 * Proxy con cache per la rilevazione "temporale in corso" a un punto specifico.
 *
 * A differenza di get_meteo.php (previsione da modello) e get_radar.php
 * (frame per la mappa), questo file risponde alla domanda puntuale:
 * "in QUESTO punto, ora, il radar vede un fenomeno intenso?".
 *
 * Come funziona:
 *  - riusa i metadata già scaricati da get_radar.php (stessa cache SQLite condivisa,
 *    stessa chiave 'librewxr_frames': se quel file ha già aggiornato la
 *    cache negli ultimi 5 minuti, qui non scatta nessuna nuova chiamata di rete)
 *  - calcola quale singolo tile 256x256 della mappa contiene le coordinate
 *    richieste (matematica slippy-map, nessuna libreria esterna)
 *  - scarica SOLO quel tile (non il mosaico nazionale) e ne legge un pixel
 *    con GD, che è quasi sempre presente su hosting condiviso (a differenza
 *    di GDAL/shell_exec, che spesso non lo è)
 *  - richiede lo schema colore "Black and White" (scala di grigi): la
 *    luminosità del pixel è direttamente proporzionale alla riflettività,
 *    quindi non serve conoscere la tabella colori esatta di un altro schema
 *    per stabilire una soglia affidabile
 *
 * ATTENZIONE: le soglie di luminosità qui sotto sono un punto di partenza
 * ragionevole ma NON calibrato su dati reali italiani. Da tarare osservando
 * il valore restituito durante un temporale noto e un giorno di pioggia
 * leggera, poi aggiustare LUMINOSITA_SOGLIA_TEMPORALE / _PIOGGIA di conseguenza.
 *
 * Uso:
 *   get_temporale.php?lat=45.07&lng=7.68
 *   get_temporale.php?city=Torino
 */

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/php/database.php';

const CACHE_TTL_RADAR = 300;      // 5 minuti, coerente con get_radar.php
const CACHE_TTL_GEOCODIFICA = 2592000;
const USER_AGENT_GENERICO = 'BollettinoProtezioneCivile/1.0';
const LIBREWXR_BASE_URL = 'https://api.librewxr.net';

const ZOOM = 8;                   // ~0.6 km/pixel in Italia: sufficiente per un comune
const TILE_SIZE = 256;
const COLOR_SCHEME_GRIGI = 0;     // "Black and White" nella lista colorSchemes di LibreWXR

// Da tarare: luminosità 0-255 del pixel (canale rosso, essendo scala di grigi
// R=G=B). Più alto = eco più intensa.
const LUMINOSITA_SOGLIA_TEMPORALE = 180;
const LUMINOSITA_SOGLIA_PIOGGIA = 80;

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

function serviConCache(string $chiaveCache, int $ttl, string $url, string $userAgent): ?string
{
    $key = 'radar:' . $chiaveCache;
    $contenuto = app_cache_read($key, $ttl);
    if ($contenuto !== null) {
        return $contenuto;
    }

    $risposta = chiamaUrlEsterno($url, $userAgent);

    if ($risposta === null) {
        return app_cache_read($key);
    }

    app_cache_write($key, $risposta);
    return $risposta;
}

/**
 * Converte lat/lng in coordinate tile (slippy map, standard OSM/Leaflet) a
 * un dato zoom, restituendo anche la posizione frazionaria del punto
 * dentro il tile (per sapere quale pixel leggere).
 */
function coordinateTile(float $lat, float $lng, int $zoom): array
{
    $n = 2 ** $zoom;
    $xTileF = ($lng + 180) / 360 * $n;

    $latRad = deg2rad($lat);
    $yTileF = (1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $n;

    $xTile = (int) floor($xTileF);
    $yTile = (int) floor($yTileF);

    $pixelX = (int) floor(($xTileF - $xTile) * TILE_SIZE);
    $pixelY = (int) floor(($yTileF - $yTile) * TILE_SIZE);

    return [$xTile, $yTile, $pixelX, $pixelY];
}

function classificaIntensita(int $luminosita, int $alpha): array
{
    // In GD, alpha 127 = totalmente trasparente, 0 = totalmente opaco
    if ($alpha >= 120) {
        return ['livello' => 'nessuna_precipitazione', 'temporale' => false];
    }
    if ($luminosita >= LUMINOSITA_SOGLIA_TEMPORALE) {
        return ['livello' => 'temporale', 'temporale' => true];
    }
    if ($luminosita >= LUMINOSITA_SOGLIA_PIOGGIA) {
        return ['livello' => 'pioggia', 'temporale' => false];
    }
    return ['livello' => 'pioggia_debole', 'temporale' => false];
}

$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
$citta = trim((string) filter_input(INPUT_GET, 'city', FILTER_UNSAFE_RAW));

if (($lat === null || $lat === false) && $citta !== '') {
    $chiaveGeo = 'geo_' . md5(mb_strtolower($citta));
    $urlGeo = 'https://geocoding-api.open-meteo.com/v1/search?' . http_build_query([
        'name' => $citta, 'count' => 1, 'language' => 'it', 'format' => 'json', 'country' => 'IT',
    ]);
    $rispostaGeo = serviConCache($chiaveGeo, CACHE_TTL_GEOCODIFICA, $urlGeo, USER_AGENT_GENERICO);
    $datiGeo = $rispostaGeo ? json_decode($rispostaGeo, true) : null;
    $risultato = $datiGeo['results'][0] ?? null;

    if (!$risultato) {
        http_response_code(502);
        echo json_encode(['status' => 'error', 'message' => 'Comune non trovato']);
        exit;
    }

    $lat = (float) $risultato['latitude'];
    $lng = (float) $risultato['longitude'];
}

if ($lat === null || $lat === false || $lng === null || $lng === false) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parametri mancanti: fornire lat+lng oppure city']);
    exit;
}

if (!function_exists('imagecreatefromstring')) {
    echo json_encode([
        'status' => 'unavailable',
        'livello' => 'non_disponibile',
        'temporale' => false,
        'message' => 'Rilevamento radar locale non disponibile',
    ]);
    exit;
}

// Risultato finale cache-ato per punto arrotondato (evita di ri-scaricare
// lo stesso tile per visitatori diversi entro la finestra di 5 minuti)
$chiaveRisultato = 'temporale_' . md5(round($lat, 3) . '_' . round($lng, 3));
$cacheKeyRisultato = 'radar:' . $chiaveRisultato;
$cacheEsistente = app_cache_read($cacheKeyRisultato, CACHE_TTL_RADAR);
if ($cacheEsistente !== null) {
    echo $cacheEsistente;
    exit;
}

// Metadata frame radar (condivisi con get_radar.php: stessa chiave cache)
$rispostaMeta = serviConCache(
    'librewxr_frames',
    CACHE_TTL_RADAR,
    LIBREWXR_BASE_URL . '/public/weather-maps.json',
    USER_AGENT_GENERICO
);
$datiMeta = $rispostaMeta ? json_decode($rispostaMeta, true) : null;

$ultimoFrame = $datiMeta['radar']['past'][count($datiMeta['radar']['past'] ?? []) - 1] ?? null;
$host = $datiMeta['host'] ?? LIBREWXR_BASE_URL;

if (!$ultimoFrame) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Nessun frame radar disponibile']);
    exit;
}

[$xTile, $yTile, $pixelX, $pixelY] = coordinateTile($lat, $lng, ZOOM);

$urlTile = sprintf(
    '%s%s/%d/%d/%d/%d/%d/1_0.png',
    $host,
    $ultimoFrame['path'],
    TILE_SIZE,
    ZOOM,
    $xTile,
    $yTile,
    COLOR_SCHEME_GRIGI
);

$immagineRaw = chiamaUrlEsterno($urlTile, USER_AGENT_GENERICO);

if ($immagineRaw === null) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Tile radar non raggiungibile']);
    exit;
}

$immagine = @imagecreatefromstring($immagineRaw);

if ($immagine === false) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Tile radar non decodificabile']);
    exit;
}

$coloreIndice = imagecolorat($immagine, $pixelX, $pixelY);
$coloreRgba = imagecolorsforindex($immagine, $coloreIndice);
imagedestroy($immagine);

$luminosita = $coloreRgba['red']; // scala di grigi: R=G=B
$classificazione = classificaIntensita($luminosita, $coloreRgba['alpha']);

$risultato = array_merge($classificazione, [
    'luminosita_pixel' => $luminosita,
    'frame_time' => $ultimoFrame['time'] ?? null,
    'coordinate' => ['lat' => $lat, 'lng' => $lng],
    'fonte' => 'librewxr (radar Italia: DPC)',
]);

$jsonRisultato = json_encode($risultato);
if ($jsonRisultato === false) {
    throw new RuntimeException('Impossibile codificare il risultato radar.');
}
app_cache_write($cacheKeyRisultato, $jsonRisultato);
echo $jsonRisultato;