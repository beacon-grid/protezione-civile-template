<?php
/**
 * Proxy con cache lato server per Open-Meteo.
 *
 * Evita che ogni visitatore generi una chiamata diretta a Open-Meteo:
 * il risultato viene salvato in SQLite e riservito a tutti gli utenti
 * finché non scade la cache.
 *
 * Uso:
 *   get_meteo.php?lat=45.07&lng=7.68   -> previsione diretta
 *   get_meteo.php?city=Torino          -> geocodifica + previsione (cache separata e più lunga per la geocodifica)
 */

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/php/database.php';

const CACHE_TTL_METEO = 600;            // 10 minuti
const CACHE_TTL_GEOCODIFICA = 2592000;  // 30 giorni: la posizione di un comune non cambia

/**
 * Esegue una richiesta HTTP GET verso un URL esterno.
 */
function chiamaUrlEsterno(string $url): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_USERAGENT => 'BollettinoProtezioneCivile/1.0',
        ]);
        $risposta = curl_exec($ch);
        $codice = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($risposta !== false && $codice === 200) ? $risposta : null;
    }

    // Fallback se cURL non è disponibile
    $contesto = stream_context_create([
        'http' => ['timeout' => 8, 'header' => "User-Agent: BollettinoProtezioneCivile/1.0\r\n"],
    ]);
    $risposta = @file_get_contents($url, false, $contesto);
    return $risposta !== false ? $risposta : null;
}

/**
 * Restituisce il contenuto dalla cache se valido, altrimenti lo scarica,
 * lo salva e lo restituisce. In caso di errore di rete, se esiste una
 * cache scaduta la usa comunque come fallback piuttosto che fallire.
 */

function serviConCache(string $chiaveCache, int $ttl, string $url): ?array
{
    $contenuto = app_cache_read('meteo:' . $chiaveCache, $ttl);
    if ($contenuto !== null) {
        return json_decode($contenuto, true);
    }

    $risposta = chiamaUrlEsterno($url);

    if ($risposta === null) {
        $contenuto = app_cache_read('meteo:' . $chiaveCache);
        if ($contenuto !== null) {
            return json_decode($contenuto, true);
        }
        return null;
    }

    app_cache_write('meteo:' . $chiaveCache, $risposta);
    return json_decode($risposta, true);
}

$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
$citta = trim((string) filter_input(INPUT_GET, 'city', FILTER_UNSAFE_RAW));

// Se non abbiamo coordinate dirette, proviamo a risolverle dalla città (con cache lunga)
if (($lat === null || $lat === false) && $citta !== '') {
    $chiaveGeo = 'geo_' . md5(mb_strtolower($citta));
    $urlGeo = 'https://geocoding-api.open-meteo.com/v1/search?' . http_build_query([
        'name' => $citta,
        'count' => 1,
        'language' => 'it',
        'format' => 'json',
        'country' => 'IT',
    ]);

    $datiGeo = serviConCache($chiaveGeo, CACHE_TTL_GEOCODIFICA, $urlGeo);
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

$chiaveMeteo = 'meteo_' . md5(round($lat, 3) . '_' . round($lng, 3));
$urlMeteo = 'https://api.open-meteo.com/v1/forecast?' . http_build_query([
    'latitude' => $lat,
    'longitude' => $lng,
    'current' => 'temperature_2m,apparent_temperature,precipitation,relative_humidity_2m,wind_speed_10m,wind_gusts_10m,wind_direction_10m,surface_pressure,cloud_cover,weather_code',
    'hourly' => 'temperature_2m,precipitation_probability,weather_code',
    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_gusts_10m_max,uv_index_max,sunrise,sunset',
    'forecast_days' => 2,
    'timezone' => 'Europe/Rome',
]);

$datiMeteo = serviConCache($chiaveMeteo, CACHE_TTL_METEO, $urlMeteo);

if (!$datiMeteo) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Servizio meteo non raggiungibile']);
    exit;
}

echo json_encode($datiMeteo);