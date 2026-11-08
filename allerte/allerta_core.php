<?php

require_once dirname(__DIR__) . '/php/database.php';

/**
 * Scarica una pagina via cURL. Ritorna [html, httpCode, errore].
 */
function scaricaPagina(string $url): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
    $html = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$html, $httpCode, $error];
}

/**
 * Parser primario: tabella "Tipologia di rischi" nella home.
 */
function estraiDaTabellaHome(string $html): ?array {
    if (!preg_match('/<table[^>]*>.*?Tipologia di rischi.*?<\/table>/is', $html, $match)) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($match[0], 'HTML-ENTITIES', 'UTF-8'));
    $rows = $dom->getElementsByTagName('tr');

    $rischi = [];
    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');
        if ($cols->length >= 3) {
            $tipoRischio = trim((string) $cols->item(0)->nodeValue);
            $statoOggi   = strtoupper(trim($cols->item(1)->nodeValue));
            $statoDomani = strtoupper(trim($cols->item(2)->nodeValue));

            $rischi[] = [
                'rischio' => preg_replace('/\s*\(\d+\).*/', '', $tipoRischio),
                'oggi'    => $statoOggi ?: 'VERDE',
                'domani'  => $statoDomani ?: 'VERDE'
            ];
        }
    }

    return !empty($rischi) ? $rischi : null;
}

/**
 * Parser di fallback: pagina dedicata /civil-defence-alerts.
 */
function estraiDaPaginaDedicata(string $html): ?array {
    $pattern = '/card-body alert-content[^>]*>\s*([^<]+?)\s*<.*?text-state[^>]*>\s*Codice\s+(\w+)/is';
    preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

    if (empty($matches) || count($matches) < 2) {
        return null;
    }

    $rischi = [];
    for ($i = 0; $i + 1 < count($matches); $i += 2) {
        $nomeOggi   = trim($matches[$i][1]);
        $nomeDomani = trim($matches[$i + 1][1]);

        if ($nomeOggi !== $nomeDomani) {
            continue;
        }

        $rischi[] = [
            'rischio' => preg_replace('/\s*\(\d+\).*/', '', $nomeOggi),
            'oggi'    => strtoupper(trim($matches[$i][2])),
            'domani'  => strtoupper(trim($matches[$i + 1][2]))
        ];
    }

    return !empty($rischi) ? $rischi : null;
}

/**
 * Calcola il livello di allerta massimo tra tutti i rischi (basato su "oggi").
 */
function calcolaAllertaMassima(array $rischi): string {
    $priorita = ['VERDE' => 0, 'GIALLO' => 1, 'ARANCIONE' => 2, 'ROSSO' => 3];
    $massima = 'VERDE';
    $livelloMassimo = 0;

    foreach ($rischi as $r) {
        $livello = $priorita[$r['oggi']] ?? 0;
        if ($livello > $livelloMassimo) {
            $livelloMassimo = $livello;
            $massima = $r['oggi'];
        }
    }

    return $massima;
}

/**
 * Scrive la cache dell'allerta nel database.
 */
function scriviCacheSicura(array $output): bool {
    return app_data_set('alert_cache', $output);
}

/**
 * Legge la cache dell'allerta dal database.
 */
function leggiCacheSicura(): ?array {
    $data = app_data_get('alert_cache');
    return is_array($data) ? $data : null;
}

/**
 * Orchestratore completo: scarica, parsa (con fallback), calcola, scrive cache.
 * Usato SOLO dal cron/sync — l'API non lo chiama più direttamente.
 *
 * Ritorna ['ok' => bool, 'output' => array|null, 'errore' => string|null].
 */
function eseguiSincronizzazione(array $site): array {
    $rischi = null;
    $fonteUsata = null;
    $erroreLog = [];

    [$htmlHome, $httpCodeHome, $curlErrorHome] = scaricaPagina($site['urls']['comune']);

    if ($htmlHome !== false && $httpCodeHome >= 200 && $httpCodeHome < 300) {
        $rischi = estraiDaTabellaHome($htmlHome);
        if ($rischi !== null) {
            $fonteUsata = 'home';
        }
    } else {
        $erroreLog[] = sprintf('curl home: %s HTTP %s', $curlErrorHome ?: 'errore sconosciuto', $httpCodeHome);
    }

    $urlDedicata = $site['urls']['comune_allerta_dedicata'] ?? null;
    if ($rischi === null && $urlDedicata) {
        [$htmlDedicata, $httpCodeDedicata, $curlErrorDedicata] = scaricaPagina($urlDedicata);

        if ($htmlDedicata !== false && $httpCodeDedicata >= 200 && $httpCodeDedicata < 300) {
            $rischi = estraiDaPaginaDedicata($htmlDedicata);
            if ($rischi !== null) {
                $fonteUsata = 'pagina_dedicata';
            }
        } else {
            $erroreLog[] = sprintf('curl dedicata: %s HTTP %s', $curlErrorDedicata ?: 'errore sconosciuto', $httpCodeDedicata);
        }
    }

    if ($rischi === null) {
        return [
            'ok' => false,
            'output' => null,
            'errore' => 'Parsing fallito su tutte le fonti disponibili. ' . implode(' | ', $erroreLog)
        ];
    }

    $output = [
        'status' => 'success',
        'data_fresca' => true,
        'ultimo_aggiornamento' => date('d/m/Y H:i:s'),
        'fonte' => $fonteUsata,
        'allerta_globale' => calcolaAllertaMassima($rischi),
        'dettagli' => $rischi
    ];

    if (!scriviCacheSicura($output)) {
        return [
            'ok' => false,
            'output' => $output,
            'errore' => 'Dati recuperati correttamente ma scrittura della cache fallita (controlla i permessi).'
        ];
    }

    return ['ok' => true, 'output' => $output, 'errore' => null];
}