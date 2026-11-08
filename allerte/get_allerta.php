<?php
require_once dirname(__DIR__) . '/allerte/allerta_core.php';

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Europe/Rome');

require_once dirname(__DIR__) . '/php/config.php';
$config = app_config();
$site = $config['site'];

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $site['urls']['home']) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}

$tempoCache     = 1200; // soglia oltre la quale il dato è considerato "non fresco"
$minIntervallo  = 60;   // secondi minimi tra due tentativi di sync automatico (anche se falliscono)

/**
 * Calcola età e freschezza della cache. Ritorna [cacheAge|null, fresca (bool)].
 */
function calcolaFreschezza(?array $cachedData, int $tempoCache): array {
    if ($cachedData === null || empty($cachedData['ultimo_aggiornamento'])) {
        return [null, false];
    }
    $date = DateTime::createFromFormat('d/m/Y H:i:s', $cachedData['ultimo_aggiornamento']);
    if ($date === false) {
        return [null, false];
    }
    $cacheAge = time() - $date->getTimestamp();
    $fresca = ($cacheAge >= 0 && $cacheAge <= $tempoCache);
    return [$cacheAge, $fresca];
}

/**
 * Prova a lanciare una sincronizzazione, protetta da lock esclusivo non bloccante
 * e da un intervallo minimo tra tentativi. Se un altro processo sta già
 * sincronizzando, o è passato troppo poco tempo dall'ultimo tentativo,
 * non fa nulla e ritorna false (si serve comunque la cache esistente).
 */
function provaTriggerSync(int $minIntervallo, callable $sincronizza): bool {
    $owner = app_lock_acquire('alert_sync');
    if ($owner === null) {
        return false; // un altro processo sta già sincronizzando
    }

    if (!app_cooldown_try_start('alert_sync', time(), $minIntervallo)) {
        app_lock_release('alert_sync', $owner);
        return false; // troppo presto per riprovare
    }

    try {
        $sincronizza();
    } finally {
        app_lock_release('alert_sync', $owner);
    }
    return true;
}

$cachedData = leggiCacheSicura();
[$cacheAge, $dataFresca] = calcolaFreschezza($cachedData, $tempoCache);

if (!$dataFresca) {
    provaTriggerSync($minIntervallo, function () use ($site) {
        $risultato = eseguiSincronizzazione($site);
        if (!$risultato['ok']) {
            error_log('[get_allerta] Sync automatico fallito: ' . ($risultato['errore'] ?? ''));
        }
    });
    // Ricarica la cache: potrebbe essere stata aggiornata da noi o da un altro processo nel frattempo
    $cachedData = leggiCacheSicura();
    [$cacheAge, $dataFresca] = calcolaFreschezza($cachedData, $tempoCache);
}

if ($cachedData === null) {
    echo json_encode([
        'status' => 'error',
        'data_fresca' => false,
        'message' => 'Dati non disponibili. Il servizio di sincronizzazione potrebbe non essere ancora partito.',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$cachedData['cache_age_seconds'] = $cacheAge;
$cachedData['data_fresca'] = $dataFresca;

if (!$dataFresca) {
    $cachedData['status'] = 'degraded';
    $cachedData['message'] = 'Dato più vecchio del previsto: il servizio di sincronizzazione potrebbe essere in ritardo.';
}

echo json_encode($cachedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);