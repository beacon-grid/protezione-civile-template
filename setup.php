<?php
/**
 * Template sito Protezione Civile
 * Copyright (C) 2026 beacon-grid
 *
 * Questo programma è software libero: puoi ridistribuirlo e/o modificarlo
 * secondo i termini della GNU Affero General Public License v3, come
 * pubblicata dalla Free Software Foundation.
 *
 * Distribuito SENZA ALCUNA GARANZIA. Testo completo della licenza nel
 * file LICENSE incluso in questo repository, o su:
 * https://www.gnu.org/licenses/agpl-3.0.html



 * =====================================================================
 * SETUP.PHP — Configuratore guidato per il sito Protezione Civile
 * =====================================================================
 * Cosa fa:
 *  - Alla prima installazione, guida l'utente in una procedura a step
 *    per compilare tutti i dati richiesti da data/config.php.
 *  - Al termine, genera data/config.php e crea un
 *    file "setup.lock" per impedire di rieseguire il wizard.
 *
 * Sicurezza:
 *  - Se la configurazione esiste già, il wizard si blocca subito.
 *  - Se setup.lock esiste, il wizard si blocca subito.
 *  - A fine installazione ti consigliamo di ELIMINARE o RINOMINARE
 *    questo file (setup.php), per sicurezza extra.
 * =====================================================================
 */

session_start();
require_once __DIR__ . '/php/totp.php';

define('CONFIG_PATH', __DIR__ . '/data/config.php');
define('LEGACY_CONFIG_PATH', __DIR__ . '/config.php');
define('LOCK_PATH', __DIR__ . '/setup.lock');

// ---------------------------------------------------------------------
// GATE DI SICUREZZA: blocca se già installato
// ---------------------------------------------------------------------
if (file_exists(CONFIG_PATH) || file_exists(LEGACY_CONFIG_PATH) || file_exists(LOCK_PATH)) {
    http_response_code(403);
    ?>
    <!doctype html>
    <html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Setup già completato</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f2f4f7;color:#1c2b3a;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;padding:24px}
            .box{background:#fff;border-radius:14px;padding:36px 40px;max-width:520px;box-shadow:0 8px 28px rgba(0,59,122,.12);border-top:6px solid #003B7A}
            h1{font-size:1.3rem;margin:0 0 12px}
            p{line-height:1.6;color:#41576b}
            code{background:#eef2f6;padding:2px 6px;border-radius:4px}
        </style>
    </head>
    <body>
        <div class="box">
            <h1>⚠️ Il sito è già configurato</h1>
            <p>È stato trovato un file di configurazione (<code>data/config.php</code> o il precedente <code>config.php</code>) e/o <code>setup.lock</code>: il wizard di installazione non può essere rieseguito per evitare di sovrascrivere la configurazione esistente.</p>
            <p>Se devi modificare i dati, aggiorna <code>data/config.php</code>. Se vuoi davvero rieseguire il setup da zero (sconsigliato su un sito già in produzione), elimina manualmente il file di configurazione e <code>setup.lock</code> via FTP/hosting.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ---------------------------------------------------------------------
// HELPER
// ---------------------------------------------------------------------
function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['setup'])) {
    $_SESSION['setup'] = [];
}
$S = &$_SESSION['setup'];

$TOTAL_STEPS = 8;
$STEP_TITLES = [
    1 => 'Ente e sito',
    2 => 'Indirizzo e dati comune',
    3 => 'Contatti ed emergenze',
    4 => 'Chiave di sicurezza',
    5 => 'Statistiche e organigramma',
    6 => 'Punti mappa',
    7 => 'Galleria e social',
    8 => 'Riepilogo e generazione',
];

// Icone curate per il menu a discesa dell'organigramma (persone/ruoli)
$ICONE_ORGANIGRAMMA = [
    'bi-person-fill'        => 'Persona — Volontario',
    'bi-person-badge-fill'  => 'Persona con badge — Sindaco/Coordinatore',
    'bi-person-fill-gear'   => 'Persona con ingranaggio — Coordinatore tecnico',
    'bi-person-vcard-fill'  => 'Tessera personale — Caposquadra',
    'bi-star-fill'          => 'Stella — Ruolo apicale',
    'bi-shield-fill-check'  => 'Scudo con spunta — Responsabile sicurezza',
    'bi-broadcast'          => 'Antenna — Responsabile radio',
    'bi-headset'            => 'Cuffie — Sala operativa/Comunicazione',
    'bi-megaphone-fill'     => 'Megafono — Comunicazione',
    'bi-truck-front-fill'   => 'Mezzo — Autista soccorso',
    'bi-tools'              => 'Attrezzi — Logistica/Manutenzione',
    'bi-heart-pulse-fill'   => 'Battito — Referente sanitario',
];

// Icone curate per il menu a discesa dei punti mappa (sedi/luoghi)
$ICONE_SEDI = [
    'bi-geo-alt-fill'          => 'Segnaposto generico',
    'bi-shield-fill-check'     => 'Scudo con spunta — Sede protezione civile',
    'bi-shield-fill'           => 'Scudo — Sede operativa',
    'bi-building-fill'         => 'Edificio — Sede comunale',
    'bi-house-door-fill'       => 'Casa — Punto di riferimento',
    'bi-hospital-fill'         => 'Ospedale — Punto sanitario',
    'bi-truck-front-fill'      => 'Mezzo — Rimessa automezzi',
    'bi-cone-striped'          => 'Cono stradale — Punto di blocco/controllo',
    'bi-signpost-split-fill'   => 'Segnale — Riferimento stradale',
];

// Colori curati per il menu a discesa delle icone (organigramma)
$COLORI_ICONE = [
    'text-primary'   => 'Blu istituzionale',
    'text-success'   => 'Verde',
    'text-danger'    => 'Rosso',
    'text-warning'   => 'Giallo/Arancio',
    'text-info'       => 'Azzurro',
    'text-secondary' => 'Grigio',
    'text-dark'      => 'Nero',
];

/**
 * Restituisce l'etichetta leggibile per un'icona, cercandola nella lista
 * curata indicata; se non trovata (es. valore salvato manualmente in
 * passato) mostra semplicemente il nome della classe come etichetta.
 */
function iconaLabel(array $lista, string $valore, string $fallbackValore): string {
    $v = $valore !== '' ? $valore : $fallbackValore;
    return $lista[$v] ?? $v;
}

/**
 * Stampa un menu a tendina Bootstrap per la scelta di un'ICONA, con
 * l'anteprima grafica dell'icona sia nel pulsante che in ogni voce
 * dell'elenco (l'utente vede subito come sarà l'icona, non deve
 * indovinare il nome della classe bootstrap-icons).
 *
 * $name        nome campo per il form (es. "organigramma[3][icona]")
 * $options     array [classe_css => etichetta] già curato
 * $current     valore attualmente salvato (può essere vuoto)
 * $default     icona da usare se $current è vuoto
 */
function renderIconPicker(string $name, array $options, string $current, string $default): void {
    $valore = $current !== '' ? $current : $default;
    $label  = $options[$valore] ?? $valore;
    ?>
    <div class="dropdown picker-dropdown">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle picker-btn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi <?= h($valore) ?> picker-btn-icon"></i>
            <span class="picker-btn-label"><?= h($label) ?></span>
        </button>
        <ul class="dropdown-menu picker-menu">
            <?php foreach ($options as $classe => $etichetta): ?>
                <li><button type="button" class="dropdown-item picker-item icon-picker-item" data-value="<?= h($classe) ?>"><i class="bi <?= h($classe) ?>"></i><span><?= h($etichetta) ?></span></button></li>
            <?php endforeach; ?>
        </ul>
        <input type="hidden" class="icon-picker-value" name="<?= h($name) ?>" value="<?= h($valore) ?>">
    </div>
    <?php
}

/**
 * Stampa un menu a tendina Bootstrap per la scelta di una CLASSE COLORE
 * (es. text-primary), con un pallino colorato di anteprima sia nel
 * pulsante che in ogni voce, così l'utente vede il colore reale invece
 * di dover interpretare il nome della classe CSS.
 */
function renderColorPicker(string $name, array $options, string $current, string $default): void {
    $valore = $current !== '' ? $current : $default;
    $label  = $options[$valore] ?? ($valore !== '' ? $valore : 'Nessuno');
    ?>
    <div class="dropdown picker-dropdown">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle picker-btn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-circle-fill picker-btn-icon <?= h($valore) ?>"></i>
            <span class="picker-btn-label"><?= h($label) ?></span>
        </button>
        <ul class="dropdown-menu picker-menu">
            <?php foreach ($options as $classe => $etichetta): ?>
                <li><button type="button" class="dropdown-item picker-item color-picker-item" data-value="<?= h($classe) ?>"><i class="bi bi-circle-fill <?= h($classe) ?>"></i><span><?= h($etichetta) ?></span></button></li>
            <?php endforeach; ?>
        </ul>
        <input type="hidden" class="color-picker-value" name="<?= h($name) ?>" value="<?= h($valore) ?>">
    </div>
    <?php
}

$step = isset($_GET['step']) ? max(1, min($TOTAL_STEPS, (int) $_GET['step'])) : 1;

$detectedScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$detectedHome = $detectedScheme . ($_SERVER['HTTP_HOST'] ?? 'tuosito.it');

$errors = [];

// ---------------------------------------------------------------------
// GESTIONE POST
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'next';
    $postedStep = (int) ($_POST['current_step'] ?? $step);

    switch ($postedStep) {
        case 1:
            $S['nome_comune']       = trim($_POST['nome_comune'] ?? '');
            $S['colore_tema']       = trim($_POST['colore_tema'] ?? '#003B7A');
            $S['nome_ente']         = trim($_POST['nome_ente'] ?? '');
            $S['nome_breve']        = trim($_POST['nome_breve'] ?? '');
            $S['meta_description']  = trim($_POST['meta_description'] ?? '');
            $S['meta_keywords']     = trim($_POST['meta_keywords'] ?? '');
            $S['meta_author']       = trim($_POST['meta_author'] ?? '');
            $S['og_image']          = trim($_POST['og_image'] ?? '');
            $S['url_home']          = trim($_POST['url_home'] ?? '');
            if ($action !== 'prev' && $S['nome_comune'] === '') {
                $errors[] = 'Il nome del comune è obbligatorio.';
            }
            break;

        case 2:
            $S['url_comune']         = trim($_POST['url_comune'] ?? '');
            $S['url_comune_root']    = trim($_POST['url_comune_root'] ?? '');
            $S['url_allerte']        = trim($_POST['url_allerte'] ?? '');
            $S['street']             = trim($_POST['street'] ?? '');
            $S['postal_code']        = trim($_POST['postal_code'] ?? '');
            $S['city']               = trim($_POST['city'] ?? '');
            $S['province']           = trim($_POST['province'] ?? '');
            $S['province_name']      = trim($_POST['province_name'] ?? '');
            $S['region']             = trim($_POST['region'] ?? '');
            $S['country']            = 'IT';
            $S['cf']                 = trim($_POST['cf'] ?? '');
            $S['piva']               = trim($_POST['piva'] ?? '');
            $S['ipa']                = trim($_POST['ipa'] ?? '');
            break;

        case 3:
            $S['phone_sede']    = trim($_POST['phone_sede'] ?? '');
            $S['phone_sala']    = trim($_POST['phone_sala'] ?? '');
            $S['phone_polizia'] = trim($_POST['phone_polizia'] ?? '');
            $S['phone_emergency'] = trim($_POST['phone_emergency'] ?? '112');
            $S['emergencies']   = $_POST['emergencies'] ?? [];
            $extraEmergency     = trim($_POST['emergency_extra'] ?? '');
            $extraEmergencyLabel = trim($_POST['emergency_extra_label'] ?? '');
            if ($extraEmergency !== '') {
                $S['emergencies'][] = $extraEmergency;
                if ($extraEmergencyLabel !== '') {
                    $S['emergency_labels'][$extraEmergency] = $extraEmergencyLabel;
                }
            }
            $S['email_primary'] = trim($_POST['email_primary'] ?? '');
            break;

        case 4:
            $S['chiave_sync'] = trim($_POST['chiave_sync'] ?? '');
            if ($action !== 'prev' && $S['chiave_sync'] === '') {
                $errors[] = 'La chiave di sicurezza non può essere vuota.';
            }
            break;

        case 5:
            $S['volontari_attivi'] = (int) ($_POST['volontari_attivi'] ?? 0);
            $S['mezzi_soccorso']   = (int) ($_POST['mezzi_soccorso'] ?? 0);
            $S['organigramma'] = rebuildRepeater($_POST, 'organigramma', ['ruolo', 'nome', 'icona', 'icona_classe']);
            break;

        case 6:
            $S['sedi']       = rebuildRepeater($_POST, 'sedi', ['lat', 'lng', 'colore', 'icona', 'titolo', 'indirizzo', 'desc', 'phone']);
            $S['attesa']     = rebuildRepeater($_POST, 'attesa', ['lat', 'lng', 'cod', 'loc']);
            $S['accoglienza']= rebuildRepeater($_POST, 'accoglienza', ['lat', 'lng', 'cod', 'loc']);
            break;

        case 7:
            $S['galleria'] = rebuildRepeater($_POST, 'galleria', ['src', 'alt', 'didascalia']);
            $S['fb']       = trim($_POST['fb'] ?? '');
            $S['ig']       = trim($_POST['ig'] ?? '');
            $S['wa']       = trim($_POST['wa'] ?? '');
            $S['content_license'] = trim($_POST['content_license'] ?? 'Tutti i diritti riservati');
            break;

        case 8:
            $S['manutenzione_attiva'] = isset($_POST['manutenzione_attiva']);
            if ($action === 'finish') {
                $S['superadmin_username'] = trim($_POST['superadmin_username'] ?? '');
                $S['superadmin_password'] = $_POST['superadmin_password'] ?? '';
                $S['superadmin_password_confirm'] = $_POST['superadmin_password_confirm'] ?? '';
                $S['admin_username'] = trim($_POST['admin_username'] ?? '');
                $S['admin_password'] = $_POST['admin_password'] ?? '';
                $S['admin_password_confirm'] = $_POST['admin_password_confirm'] ?? '';

                validateSetupAccount($S['superadmin_username'], $S['superadmin_password'], $S['superadmin_password_confirm'], 'superadmin', $errors, true);
                if ($S['admin_username'] !== '' || $S['admin_password'] !== '' || $S['admin_password_confirm'] !== '') {
                    validateSetupAccount($S['admin_username'], $S['admin_password'], $S['admin_password_confirm'], 'admin', $errors, false);
                }

                if ($S['admin_username'] !== '' && $S['admin_username'] === $S['superadmin_username']) {
                    $errors[] = 'Username superadmin e admin devono essere diversi.';
                }

                if (!empty($errors)) {
                    break;
                }

                $result = generateConfigFile($S);
                if ($result === true) {
                    header('Location: setup.php?step=9');
                    exit;
                } else {
                    $errors[] = $result; // messaggio di errore scrittura file
                }
            }
            break;
    }

    if (empty($errors)) {
        if ($action === 'prev') {
            $step = max(1, $postedStep - 1);
        } elseif ($action === 'next') {
            $step = min($TOTAL_STEPS, $postedStep + 1);
        } else {
            $step = $postedStep;
        }
        if ($action !== 'finish') {
            header('Location: setup.php?step=' . $step);
            exit;
        }
    } else {
        $step = $postedStep;
    }
}

/**
 * Ricostruisce un array di righe ripetute (sedi, organigramma, galleria...)
 * a partire da $_POST['nome_sezione'][indice]['campo'].
 */
function rebuildRepeater(array $post, string $key, array $fields): array {
    $rows = [];
    if (!isset($post[$key]) || !is_array($post[$key])) {
        return $rows;
    }
    foreach ($post[$key] as $row) {
        if (!is_array($row)) continue;
        $clean = [];
        $hasContent = false;
        foreach ($fields as $f) {
            $val = trim($row[$f] ?? '');
            $clean[$f] = $val;
            if ($val !== '') $hasContent = true;
        }
        if ($hasContent) $rows[] = $clean;
    }
    return $rows;
}

function validateSetupAccount(string $username, string $password, string $confirmation, string $role, array &$errors, bool $required): void {
    if (!$required && $username === '' && $password === '' && $confirmation === '') {
        return;
    }
    if (!preg_match('/^[a-z0-9._-]{3,32}$/', $username)) {
        $errors[] = "Username {$role} non valido: usa 3-32 caratteri minuscoli, numeri, punto, trattino o underscore.";
    }
    if (strlen($password) < 10 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "La password {$role} deve avere almeno 10 caratteri, una maiuscola e un numero.";
    }
    if ($password !== $confirmation) {
        $errors[] = "Le password {$role} non coincidono.";
    }
}

/**
 * Genera data/config.php a partire dai dati raccolti nel wizard.
 * Ritorna true se ok, altrimenti una stringa con l'errore.
 */
function generateConfigFile(array $S) {
    $nomeComune = $S['nome_comune'] ?? 'Comune';
    $nomeEnte   = $S['nome_ente'] ?: ('Gruppo Comunale di Protezione Civile di ' . $nomeComune);
    $nomeBreve  = $S['nome_breve'] ?: ('Protezione Civile ' . $nomeComune);

    $mapSedi = array_map(function ($r) {
        $entry = [
            'coords'    => [(float) $r['lat'], (float) $r['lng']],
            'colore'    => $r['colore'] ?: '#003B7A',
            'icona'     => $r['icona'] ?: 'bi-geo-alt-fill',
            'titolo'    => $r['titolo'],
            'indirizzo' => $r['indirizzo'],
            'desc'      => $r['desc'],
        ];
        if (!empty($r['phone'])) $entry['phone'] = $r['phone'];
        return $entry;
    }, $S['sedi'] ?? []);

    $firstSedeCoords = $mapSedi[0]['coords'] ?? ['', ''];

    $mapAttesa = [];
    foreach (($S['attesa'] ?? []) as $r) {
        $mapAttesa[] = [
            'coords' => [(float) $r['lat'], (float) $r['lng']],
            'num'    => count($mapAttesa) + 1,
            'cod'    => $r['cod'],
            'loc'    => $r['loc'],
        ];
    }

    $mapAccoglienza = [];
    foreach (($S['accoglienza'] ?? []) as $r) {
        $mapAccoglienza[] = [
            'coords' => [(float) $r['lat'], (float) $r['lng']],
            'num'    => count($mapAccoglienza) + 1,
            'cod'    => $r['cod'],
            'loc'    => $r['loc'],
        ];
    }

    $galleria = array_map(function ($r) {
        return [
            'src'        => $r['src'],
            'alt'        => $r['alt'],
            'didascalia' => $r['didascalia'],
        ];
    }, $S['galleria'] ?? []);

    $organigramma = array_map(function ($r) {
        return [
            'ruolo'        => $r['ruolo'],
            'nome'         => $r['nome'],
            'icona'        => $r['icona'] ?: 'bi-person',
            'icona_classe' => $r['icona_classe'],
        ];
    }, $S['organigramma'] ?? []);

    $emergencies = array_values(array_unique(array_filter($S['emergencies'] ?? ['112', '115', '118', '1515'])));
    
    
    $configArray = [
        'site' => [
            'colore_tema'      => $S['colore_tema'] ?: '#003B7A',
            'nome_comune'      => $nomeComune,
            'nome_ente'        => $nomeEnte,
            'nome_breve'       => $nomeBreve,
            'meta_title'       => $nomeBreve . ' | Gruppo Comunale',
            'meta_description' => $S['meta_description'] ?: ('Sito ufficiale del ' . $nomeEnte . '.'),
            'meta_keywords'    => $S['meta_keywords'] ?: ('protezione civile, ' . $nomeComune . ', emergenze, allerta meteo, volontari'),
            'meta_author'      => $S['meta_author'] ?: $nomeEnte,
            'og_image'         => $S['og_image'],
            'urls' => [
                'home'                    => $S['url_home'],
                'comune'                  => $S['url_comune'],
                'comune_root'             => $S['url_comune_root'],
                'allerte_meteo_regionale' => $S['url_allerte'],
            ],
            'security' => [
                'forza_aggiornamento_token' => '__SECURITY_TOKEN_PLACEHOLDER__',
            ],
            'address' => [
                'street'        => $S['street'],
                'postal_code'   => $S['postal_code'],
                'city'          => $S['city'] ?: $nomeComune,
                'province'      => $S['province'],
                'province_name' => $S['province_name'],
                'region'        => $S['region'],
                'country'       => $S['country'] ?: 'IT',
                'lat'           => $firstSedeCoords[0],
                'lng'           => $firstSedeCoords[1],
            ],
            'comune' => [
                'cf'   => $S['cf'],
                'piva' => $S['piva'],
                'ipa'  => $S['ipa'],
            ],
            'phones' => [
                'sede'                     => $S['phone_sede'],
                'sala_operativa_regionale' => $S['phone_sala'],
                'polizia_locale'           => $S['phone_polizia'],
                'emergency'                => $S['phone_emergency'] ?: '112',
            ],
            'emergencies' => $emergencies,
            'emergency_labels' => $S['emergency_labels'] ?? [],
            'emails' => [
                'primary' => $S['email_primary'],
            ],
            'stats' => [
                'volontari_attivi' => (int) ($S['volontari_attivi'] ?? 0),
                'mezzi_soccorso'   => (int) ($S['mezzi_soccorso'] ?? 0),
            ],
            'map_points' => [
                'sedi'        => $mapSedi,
                'attesa'      => $mapAttesa,
                'accoglienza' => $mapAccoglienza,
            ],
            'galleria'     => $galleria,
            'organigramma' => $organigramma,
            'socials' => [
                'facebook'  => $S['fb'],
                'instagram' => $S['ig'],
                'whatsapp'  => $S['wa'],
            ],
            'manutenzione' => [
                'attiva'           => !empty($S['manutenzione_attiva']),
                'fase_attuale'     => 1,
                'rientro_previsto' => '',
            ],
            'content_license' => $S['content_license'] ?? 'Tutti i diritti riservati',

        ],
    ];

    $exported = var_export($configArray, true);
    $exported = str_replace("'__SECURITY_TOKEN_PLACEHOLDER__'", "hash_hmac('sha256', date('Y-m-d'), \$chiaveSyncAlert)", $exported);

    $exported = preg_replace(
        "/'nome_comune' => '" . preg_quote(addslashes($nomeComune), '/') . "',/",
        "'nome_comune' => \$nome_comune,",
        $exported,
        1
    );

    $chiaveEscaped = addslashes($S['chiave_sync'] ?? '');
    $nomeComuneEscaped = addslashes($nomeComune);
    $timestamp = date('Y-m-d H:i:s');


    $fileContent = "<?php\n"
        . "// Generato automaticamente dal wizard di setup il {$timestamp}\n"
        . "\$nome_comune = '{$nomeComuneEscaped}';\n"
        . "\$chiaveSyncAlert = '{$chiaveEscaped}'; // usata per generare il token di sicurezza giornaliero\n"
        . "\$config = {$exported};\n\n"
        . "if ((\$config['site']['manutenzione']['attiva'] ?? false) === true) {\n"
        . "    \$pagina_corrente = parse_url(\$_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';\n"
        . "    \$pagina_corrente = rtrim(\$pagina_corrente, '/') ?: '/';\n"
        . "    \$esclusi = ['/errors/manutenzione.php', '/news.php', '/news'];\n"
        . "    \$area_admin = \$pagina_corrente === '/admin' || str_starts_with(\$pagina_corrente, '/admin/');\n"
        . "    if (!in_array(\$pagina_corrente, \$esclusi, true) && !\$area_admin) {\n"
        . "        header('Location: /errors/manutenzione.php');\n"
        . "        header('Retry-After: 3600'); // opzionale, in secondi\n"
        . "        exit;\n"
        . "    }\n"
        . "}\n\n"
        . "return \$config;\n";

    $users = buildSetupUsers($S);
    if (!extension_loaded('pdo_sqlite')) {
        return 'Attiva l’estensione PHP pdo_sqlite prima di completare il setup.';
    }
    require_once __DIR__ . '/php/database.php';
    app_db();

    if (file_put_contents(CONFIG_PATH, $fileContent) === false) {
        return 'Impossibile scrivere data/config.php. Controlla che la cartella data/ sia scrivibile dal processo PHP.';
    }

    if (!app_users_replace_all($users['admin_records'], $users['superadmin_records'])) {
        @unlink(CONFIG_PATH);
        return 'Impossibile inizializzare gli account nel database SQLite. Controlla che l’estensione pdo_sqlite sia attiva e che la cartella data/ sia scrivibile.';
    }
    $_SESSION['setup_result'] = ['accounts' => $users['summary']];

    // Blocchiamo il wizard per il futuro
    @file_put_contents(LOCK_PATH, "Installazione completata il {$timestamp}\n");

    // Puliamo la sessione
    unset($_SESSION['setup']);

    return true;
}

function buildSetupUsers(array $S): array {
    $accounts = [
        [
            'username' => $S['superadmin_username'],
            'password_hash' => password_hash($S['superadmin_password'], PASSWORD_DEFAULT),
            'role' => 'superadmin',
        ],
    ];
    if ($S['admin_username'] !== '') {
        $accounts[] = [
            'username' => $S['admin_username'],
            'password_hash' => password_hash($S['admin_password'], PASSWORD_DEFAULT),
            'role' => 'admin',
        ];
    }

    $adminRecords = [];
    $superadminRecords = [];
    $summary = [];
    foreach ($accounts as $account) {
        $secret = totp_generate_secret();
        $record = [
            'username' => $account['username'],
            'password_hash' => $account['password_hash'],
            'totp_secret' => $secret,
            'role' => $account['role'],
            'active' => true,
            'must_change_password' => true,
            'created_at' => date('Y-m-d'),
        ];
        if ($account['role'] === 'superadmin') {
            $superadminRecords[] = $record;
        } else {
            $adminRecords[] = $record;
        }
        $summary[] = [
            'username' => $account['username'],
            'role' => $account['role'],
            'totp_secret' => $secret,
        ];
    }
    return [
        'admin_records' => $adminRecords,
        'superadmin_records' => $superadminRecords,
        'summary' => $summary,
    ];
}

// ---------------------------------------------------------------------
// STEP 9: pagina di successo
// ---------------------------------------------------------------------
if ($step === 9 || (isset($_GET['step']) && $_GET['step'] === '9')) {
    $setupResult = $_SESSION['setup_result'] ?? ['accounts' => []];
    unset($_SESSION['setup_result']);
    ?>
    <!doctype html>
    <html lang="it">
    <head>
        <meta charset="utf-8">
        <title>Installazione completata</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f2f4f7;color:#1c2b3a;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;padding:24px}
            .box{background:#fff;border-radius:14px;padding:36px 40px;max-width:560px;box-shadow:0 8px 28px rgba(0,59,122,.12);border-top:6px solid #2e7d32}
            h1{font-size:1.4rem;margin:0 0 12px}
            p{line-height:1.6;color:#41576b}
            code{background:#eef2f6;padding:2px 6px;border-radius:4px}
            a.btn{display:inline-block;margin-top:18px;background:#003B7A;color:#fff;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:600}
        </style>
    </head>
    <body>
        <div class="box">
            <h1>✅ Configurazione completata</h1>
            <p><code>data/config.php</code> è stato creato e gli account sono stati salvati nel database <code>data/site.sqlite</code>. Il wizard è ora bloccato (file <code>setup.lock</code> presente) e non potrà essere rieseguito.</p>
            <?php if (!empty($setupResult['accounts'])): ?>
                <div class="alert" style="background:#fff8e1;border:1px solid #f0c36d;border-radius:8px;padding:14px;margin:18px 0;">
                    <strong>Account creati e segreti 2FA</strong>
                    <p style="margin:8px 0 10px;">Conserva questi segreti in un luogo sicuro. Al primo accesso ogni account dovrà cambiare la password.</p>
                    <?php foreach ($setupResult['accounts'] as $account): ?>
                        <div style="margin-top:10px;"><strong><?= h($account['role']) ?> — <?= h($account['username']) ?></strong><br><code><?= h($account['totp_secret']) ?></code></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p><strong>Ultimo passo consigliato:</strong> per sicurezza, elimina o rinomina questo file <code>setup.php</code> dal tuo hosting/FTP.</p>
            <a class="btn" href="./">Vai al sito</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Pre-carica valori di default sensati al primo accesso allo step 1
if ($step === 1 && !isset($S['url_home'])) {
    $S['url_home'] = $detectedHome;
}
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Setup — <?= h($STEP_TITLES[$step] ?? '') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/css/bootstrap.min.css">
<link rel="stylesheet" href="/css/bootstrap-icons.min.css">
<link rel="stylesheet" href="/css/leaflet.css">
<script src="/js/bootstrap.bundle.min.js"></script>
<style>
    .icon-preview-box{ width:42px;height:38px;display:flex;align-items:center;justify-content:center;background:#f8f9fb;border:1px solid #ced4da;border-left:none;border-radius:0 6px 6px 0;font-size:1.1rem; }
    .color-swatch-box{ width:38px;height:38px;display:flex;align-items:center;justify-content:center;background:#f8f9fb;border:1px solid #ced4da;border-left:none;border-radius:0 6px 6px 0; }
    #map-picker-container{ height:340px;border-radius:10px;overflow:hidden; }
    .field-locked{ background:#eef2f6 !important; cursor:not-allowed; }
    body{ background:#f2f4f7; }
    .brand-bar{ background:#003B7A; color:#fff; }
    .step-pill{ width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:600; }
    .step-pill.active{ background:#003B7A;color:#fff; }
    .step-pill.done{ background:#2e7d32;color:#fff; }
    .step-pill.todo{ background:#e1e6ec;color:#7a8494; }
    .step-label{ font-size:.72rem;color:#7a8494; }
    .step-label.active{ color:#003B7A;font-weight:600; }
    .card{ border:none;border-radius:14px;box-shadow:0 6px 24px rgba(0,59,122,.08); }
    .repeater-row{ background:#f8f9fb;border:1px solid #e4e8ee;border-radius:10px;padding:14px;margin-bottom:12px;position:relative; }
    .remove-row{ position:absolute;top:8px;right:8px; }
    .required::after{ content:" *"; color:#c0392b; }

    /* Menu a tendina con anteprima (icone e colori) */
    .picker-dropdown{ position:relative; }
    .picker-btn{ width:100%;text-align:left;display:flex;align-items:center;gap:8px;background:#fff; }
    .picker-btn .picker-btn-icon{ font-size:1.05rem;width:20px;text-align:center;flex:0 0 auto; }
    .picker-btn .picker-btn-label{ overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.92rem; }
    .picker-menu{ max-height:280px;overflow-y:auto;min-width:100%; }
    .picker-item{ display:flex;align-items:center;gap:10px; }
    .picker-item i.bi{ font-size:1.05rem;width:20px;text-align:center;flex:0 0 auto; }
    .picker-item .swatch{ width:15px;height:15px;border-radius:50%;flex:0 0 auto; }
</style>
</head>
<body>

<div class="brand-bar py-3 mb-4">
    <div class="container">
        <h1 class="h5 mb-0"><i class="bi bi-shield-fill-check me-2"></i>Setup guidato — Configurazione sito Protezione Civile</h1>
    </div>
</div>

<div class="container pb-5" style="max-width:820px;">

    <!-- Indicatore step -->
    <div class="d-flex justify-content-between mb-4 flex-wrap gap-2">
        <?php foreach ($STEP_TITLES as $n => $title): ?>
            <div class="text-center" style="flex:1;min-width:70px;">
                <div class="step-pill <?= $n === $step ? 'active' : ($n < $step ? 'done' : 'todo') ?> mx-auto mb-1">
                    <?= $n < $step ? '<i class="bi bi-check-lg"></i>' : $n ?>
                </div>
                <div class="step-label <?= $n === $step ? 'active' : '' ?>"><?= h($title) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card p-4">
    <form method="post" action="setup.php">
        <input type="hidden" name="current_step" value="<?= (int) $step ?>">

        <?php if ($step === 1): ?>
            <h2 class="h5 mb-3">Dati dell'ente e del sito</h2>
            <div class="mb-3">
                <label class="form-label required">Nome del comune</label>
                <input type="text" id="nome_comune" name="nome_comune" class="form-control" required value="<?= h($S['nome_comune'] ?? '') ?>">
                <div class="form-text"><i class="bi bi-magic"></i> Appena scrivi il nome del comune, i campi sottostanti vengono proposti automaticamente (puoi comunque modificarli liberamente).</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Colore tema</label>
                <input type="color" name="colore_tema" class="form-control form-control-color" value="<?= h($S['colore_tema'] ?? '#003B7A') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Nome ente completo</label>
                <input type="text" id="nome_ente" name="nome_ente" class="form-control" value="<?= h($S['nome_ente'] ?? '') ?>" placeholder="Es. Gruppo Comunale di Protezione Civile di Esempio">
                <div class="form-text">Se lo lasci vuoto, verrà generato automaticamente dal nome del comune.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Nome breve</label>
                <input type="text" id="nome_breve" name="nome_breve" class="form-control" value="<?= h($S['nome_breve'] ?? '') ?>" placeholder="Es. Protezione Civile Esempio">
            </div>
            <div class="mb-3">
                <label class="form-label">Descrizione meta (SEO)</label>
                <textarea id="meta_description" name="meta_description" class="form-control" rows="2"><?= h($S['meta_description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Parole chiave meta</label>
                <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" value="<?= h($S['meta_keywords'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Autore meta</label>
                <input type="text" id="meta_author" name="meta_author" class="form-control" value="<?= h($S['meta_author'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">URL immagine social (og:image)</label>
                <input type="url" name="og_image" class="form-control" value="<?= h($S['og_image'] ?? '') ?>" placeholder="https://protezionecivile.comune.esempio.it/assets/img/logo.png">
            </div>
            <div class="mb-3">
                <label class="form-label">URL home del sito</label>
                <input type="url" name="url_home" class="form-control" value="<?= h($S['url_home'] ?? $detectedHome) ?>" placeholder="https://protezionecivile.comune.esempio.it">
                <div class="form-text">Inserisci l'indirizzo completo, compreso <code>https://</code>.</div>
            </div>
            <script>
            (function(){
                const comuneEl = document.getElementById('nome_comune');
                const targets = {
                    nome_ente: c => 'Gruppo Comunale di Protezione Civile di ' + c,
                    nome_breve: c => 'Protezione Civile ' + c,
                    meta_keywords: c => 'protezione civile, ' + c + ', emergenze, allerta meteo, volontari',
                };
                // meta_description e meta_author dipendono dal Nome ente (che a sua volta dipende dal comune)
                function nomeEnteCorrente(c){
                    const el = document.getElementById('nome_ente');
                    return (el.value.trim() !== '' ) ? el.value.trim() : 'Gruppo Comunale di Protezione Civile di ' + c;
                }
                function aggiornaCampo(id, valoreGenerato){
                    const el = document.getElementById(id);
                    if (!el) return;
                    const autoPrec = el.dataset.autoValue || '';
                    if (el.value.trim() === '' || el.value === autoPrec) {
                        el.value = valoreGenerato;
                        el.dataset.autoValue = valoreGenerato;
                    }
                }
                function aggiornaTutto(){
                    const c = comuneEl.value.trim();
                    if (c === '') return;
                    Object.keys(targets).forEach(id => aggiornaCampo(id, targets[id](c)));
                    aggiornaCampo('meta_author', nomeEnteCorrente(c));
                    aggiornaCampo('meta_description', 'Sito ufficiale del ' + nomeEnteCorrente(c) + '.');
                }
                comuneEl.addEventListener('input', aggiornaTutto);
                document.getElementById('nome_ente').addEventListener('input', function(){
                    const c = comuneEl.value.trim();
                    if (c === '') return;
                    aggiornaCampo('meta_author', nomeEnteCorrente(c));
                    aggiornaCampo('meta_description', 'Sito ufficiale del ' + nomeEnteCorrente(c) + '.');
                });
            })();
            </script>

        <?php elseif ($step === 2): ?>
            <h2 class="h5 mb-3">Link istituzionali</h2>
            <div class="alert alert-info small" role="note">
                Usa URL completi che iniziano con <code>https://</code>. Esempio: <code>https://www.comune.esempio.it/it</code>.
            </div>
            <div class="mb-3">
                <label class="form-label">URL sito del comune</label>
                <input type="url" name="url_comune" class="form-control" value="<?= h($S['url_comune'] ?? '') ?>" placeholder="https://www.comune.esempio.it/it">
                <div class="form-text">Pagina del Comune usata per recuperare le allerte, non l'indirizzo del tuo sito.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">URL radice sito del comune</label>
                <input type="url" name="url_comune_root" class="form-control" value="<?= h($S['url_comune_root'] ?? '') ?>" placeholder="https://www.comune.esempio.it">
            </div>
            <div class="mb-3">
                <label class="form-label">URL allerte meteo regionali</label>
                <input type="url" name="url_allerte" class="form-control" value="<?= h($S['url_allerte'] ?? '') ?>" placeholder="https://www.allerta.meteo.regione.esempio.it">
            </div>
            <hr class="my-4">
            <h2 class="h5 mb-3">Indirizzo sede</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label">Via/Piazza</label>
                    <input type="text" name="street" class="form-control" value="<?= h($S['street'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CAP</label>
                    <input type="text" name="postal_code" class="form-control" value="<?= h($S['postal_code'] ?? '') ?>">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Città</label>
                    <input type="text" name="city" class="form-control" value="<?= h($S['city'] ?? $S['nome_comune'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Regione</label>
                    <select id="region_select" name="region" class="form-select">
                        <option value="">— Seleziona regione —</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Provincia</label>
                    <select id="province_select" class="form-select" disabled>
                        <option value="">— Prima seleziona la regione —</option>
                    </select>
                    <input type="hidden" id="province_name_input" name="province_name" value="<?= h($S['province_name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sigla provincia</label>
                    <input type="text" id="province_sigla_input" name="province" maxlength="2" class="form-control" value="<?= h($S['province'] ?? '') ?>" readonly>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">
                    Nazione (codice)
                    <i class="bi bi-info-circle text-muted" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus hover" data-bs-placement="right" title="Campo non modificabile" data-bs-content="Questo configuratore è pensato esclusivamente per i gruppi di Protezione Civile italiani, quindi il campo è fissato su IT."></i>
                </label>
                <input type="text" name="country" class="form-control field-locked" value="IT" readonly>
            </div>
            <script>
            const REGIONI_PROVINCE = {
                "Abruzzo": [["AQ","L'Aquila"],["TE","Teramo"],["PE","Pescara"],["CH","Chieti"]],
                "Basilicata": [["PZ","Potenza"],["MT","Matera"]],
                "Calabria": [["CS","Cosenza"],["CZ","Catanzaro"],["RC","Reggio Calabria"],["KR","Crotone"],["VV","Vibo Valentia"]],
                "Campania": [["NA","Napoli"],["SA","Salerno"],["AV","Avellino"],["BN","Benevento"],["CE","Caserta"]],
                "Emilia-Romagna": [["BO","Bologna"],["MO","Modena"],["PR","Parma"],["RE","Reggio Emilia"],["PC","Piacenza"],["FE","Ferrara"],["RA","Ravenna"],["FC","Forlì-Cesena"],["RN","Rimini"]],
                "Friuli-Venezia Giulia": [["TS","Trieste"],["UD","Udine"],["PN","Pordenone"],["GO","Gorizia"]],
                "Lazio": [["RM","Roma"],["LT","Latina"],["FR","Frosinone"],["VT","Viterbo"],["RI","Rieti"]],
                "Liguria": [["GE","Genova"],["IM","Imperia"],["SP","La Spezia"],["SV","Savona"]],
                "Lombardia": [["MI","Milano"],["BG","Bergamo"],["BS","Brescia"],["CO","Como"],["CR","Cremona"],["LC","Lecco"],["LO","Lodi"],["MN","Mantova"],["MB","Monza e della Brianza"],["PV","Pavia"],["SO","Sondrio"],["VA","Varese"]],
                "Marche": [["AN","Ancona"],["PU","Pesaro e Urbino"],["MC","Macerata"],["FM","Fermo"],["AP","Ascoli Piceno"]],
                "Molise": [["CB","Campobasso"],["IS","Isernia"]],
                "Piemonte": [["TO","Torino"],["AL","Alessandria"],["AT","Asti"],["BI","Biella"],["CN","Cuneo"],["NO","Novara"],["VB","Verbano-Cusio-Ossola"],["VC","Vercelli"]],
                "Puglia": [["BA","Bari"],["BT","Barletta-Andria-Trani"],["BR","Brindisi"],["FG","Foggia"],["LE","Lecce"],["TA","Taranto"]],
                "Sardegna": [["CA","Cagliari"],["SS","Sassari"],["NU","Nuoro"],["OR","Oristano"],["SU","Sud Sardegna"]],
                "Sicilia": [["PA","Palermo"],["AG","Agrigento"],["CL","Caltanissetta"],["CT","Catania"],["EN","Enna"],["ME","Messina"],["RG","Ragusa"],["SR","Siracusa"],["TP","Trapani"]],
                "Toscana": [["FI","Firenze"],["AR","Arezzo"],["GR","Grosseto"],["LI","Livorno"],["LU","Lucca"],["MS","Massa-Carrara"],["PI","Pisa"],["PT","Pistoia"],["PO","Prato"],["SI","Siena"]],
                "Trentino-Alto Adige": [["TN","Trento"],["BZ","Bolzano"]],
                "Umbria": [["PG","Perugia"],["TR","Terni"]],
                "Valle d'Aosta": [["AO","Aosta"]],
                "Veneto": [["VE","Venezia"],["VR","Verona"],["VI","Vicenza"],["TV","Treviso"],["PD","Padova"],["RO","Rovigo"],["BL","Belluno"]]
            };
            (function(){
                const regionSelect = document.getElementById('region_select');
                const provinceSelect = document.getElementById('province_select');
                const provinceNameInput = document.getElementById('province_name_input');
                const provinceSiglaInput = document.getElementById('province_sigla_input');
                const regioneSalvata = <?= json_encode($S['region'] ?? '') ?>;
                const provinciaSalvata = <?= json_encode($S['province_name'] ?? '') ?>;

                Object.keys(REGIONI_PROVINCE).forEach(function(reg){
                    const opt = document.createElement('option');
                    opt.value = reg;
                    opt.textContent = reg;
                    if (reg === regioneSalvata) opt.selected = true;
                    regionSelect.appendChild(opt);
                });

                function popolaProvince(regione, provinciaDaSelezionare){
                    provinceSelect.innerHTML = '';
                    const lista = REGIONI_PROVINCE[regione] || [];
                    if (lista.length === 0) {
                        provinceSelect.disabled = true;
                        provinceSelect.innerHTML = '<option value="">— Prima seleziona la regione —</option>';
                        return;
                    }
                    provinceSelect.disabled = false;
                    const optVuota = document.createElement('option');
                    optVuota.value = '';
                    optVuota.textContent = '— Seleziona provincia —';
                    provinceSelect.appendChild(optVuota);
                    lista.forEach(function(p){
                        const opt = document.createElement('option');
                        opt.value = p[1];
                        opt.dataset.sigla = p[0];
                        opt.textContent = p[1] + ' (' + p[0] + ')';
                        if (p[1] === provinciaDaSelezionare) opt.selected = true;
                        provinceSelect.appendChild(opt);
                    });
                    sincronizzaCampiNascosti();
                }

                function sincronizzaCampiNascosti(){
                    const selected = provinceSelect.options[provinceSelect.selectedIndex];
                    if (selected && selected.value !== '') {
                        provinceNameInput.value = selected.value;
                        provinceSiglaInput.value = selected.dataset.sigla || '';
                    } else {
                        provinceNameInput.value = '';
                        provinceSiglaInput.value = '';
                    }
                }

                regionSelect.addEventListener('change', function(){
                    popolaProvince(this.value, '');
                });
                provinceSelect.addEventListener('change', sincronizzaCampiNascosti);

                if (regioneSalvata) {
                    popolaProvince(regioneSalvata, provinciaSalvata);
                }
            })();
            </script>
            <hr class="my-4">
            <h2 class="h5 mb-3">Dati fiscali del comune</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Codice Fiscale ente</label>
                    <input type="text" name="cf" class="form-control" value="<?= h($S['cf'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Partita IVA</label>
                    <input type="text" name="piva" class="form-control" value="<?= h($S['piva'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Codice IPA</label>
                    <input type="text" name="ipa" class="form-control" value="<?= h($S['ipa'] ?? '') ?>">
                </div>
            </div>

        <?php elseif ($step === 3): ?>
            <h2 class="h5 mb-3">Telefoni</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Sede protezione civile</label>
                    <input type="text" name="phone_sede" class="form-control" value="<?= h($S['phone_sede'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <?php $regioneCorrente = trim($S['region'] ?? ''); ?>
                    <label class="form-label">Sala operativa <?= $regioneCorrente !== '' ? 'Regione ' . h($regioneCorrente) : 'regionale di Protezione Civile' ?></label>
                    <input type="text" name="phone_sala" class="form-control" value="<?= h($S['phone_sala'] ?? '') ?>" placeholder="Numero verde/sala operativa della tua regione">
                    <?php if ($regioneCorrente === ''): ?>
                        <div class="form-text">Torna allo step 2 e seleziona la regione per vedere l'etichetta specifica.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Polizia locale</label>
                    <input type="text" name="phone_polizia" class="form-control" value="<?= h($S['phone_polizia'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Numero emergenza principale mostrato</label>
                    <input type="text" name="phone_emergency" class="form-control" value="<?= h($S['phone_emergency'] ?? '112') ?>">
                </div>
            </div>
            <hr class="my-4">
            <h2 class="h5 mb-3">Numeri di emergenza da elencare</h2>
            <?php $checked = $S['emergencies'] ?? ['112', '115', '118', '1515']; ?>
            <div class="d-flex flex-wrap gap-3 mb-2">
                <?php foreach ([
                    '112'  => 'Numero unico emergenze',
                    '113'  => 'Polizia di Stato',
                    '115'  => 'Vigili del Fuoco',
                    '117'  => 'Guardia di Finanza',
                    '118'  => 'Emergenza sanitaria',
                    '1515' => 'Incendi boschivi',
                    '1522' => 'Antiviolenza e stalking',
                    '1530' => 'Guardia Costiera',
                ] as $num => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="emergencies[]" value="<?= $num ?>" id="em<?= $num ?>" <?= in_array($num, $checked) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="em<?= $num ?>"><?= $num ?> — <?= $label ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Altro numero (opzionale)</label>
                <input type="text" name="emergency_extra" class="form-control" placeholder="Es. 03012345678">
            </div>
            <div class="mb-3">
                <label class="form-label">Etichetta altro numero</label>
                <input type="text" name="emergency_extra_label" class="form-control" placeholder="Es. Centro antiveleni">
                <div class="form-text">L'etichetta verrà mostrata sotto il numero nella home.</div>
            </div>
            <hr class="my-4">
            <div class="mb-3">
                <label class="form-label">Email principale</label>
                <input type="email" name="email_primary" class="form-control" value="<?= h($S['email_primary'] ?? '') ?>">
            </div>

        <?php elseif ($step === 4): ?>
            <h2 class="h5 mb-3">Chiave di sicurezza</h2>
            <p class="text-muted">Questa chiave viene usata per generare automaticamente un codice che cambia ogni giorno, usato per confermare gli aggiornamenti delle allerte. Non è una password che dovrai digitare: va inserita una sola volta ora.</p>
            <div class="mb-3">
                <label class="form-label required">Chiave segreta</label>
                <div class="input-group">
                    <input type="text" name="chiave_sync" id="chiave_sync" class="form-control" required value="<?= h($S['chiave_sync'] ?? '') ?>">
                    <button type="button" class="btn btn-outline-secondary" onclick="generaChiave()"><i class="bi bi-shuffle"></i> Genera casuale</button>
                </div>
                <div class="form-text">Conservala in un posto sicuro: se la perdi e la sostituisci, eventuali link basati sul vecchio codice smetteranno di funzionare.</div>
            </div>
            <script>
            function generaChiave(){
                const arr = new Uint8Array(24);
                crypto.getRandomValues(arr);
                const hex = Array.from(arr).map(b => b.toString(16).padStart(2,'0')).join('');
                document.getElementById('chiave_sync').value = hex;
            }
            </script>

        <?php elseif ($step === 5): ?>
            <h2 class="h5 mb-3">Statistiche mostrate sul sito</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Volontari attivi</label>
                    <input type="number" min="0" name="volontari_attivi" class="form-control" value="<?= h($S['volontari_attivi'] ?? 0) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mezzi di soccorso</label>
                    <input type="number" min="0" name="mezzi_soccorso" class="form-control" value="<?= h($S['mezzi_soccorso'] ?? 0) ?>">
                </div>
            </div>
            <hr class="my-4">
            <h2 class="h5 mb-3">Organigramma</h2>
            <div id="rep-organigramma" data-counter="<?= count($S['organigramma'] ?? []) ?>">
                <?php foreach (($S['organigramma'] ?? []) as $i => $r): ?>
                    <div class="repeater-row">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label">Ruolo</label><input class="form-control" list="ruoli-suggeriti" name="organigramma[<?= $i ?>][ruolo]" value="<?= h($r['ruolo']) ?>" placeholder="Es. Caposquadra"></div>
                            <div class="col-md-4"><label class="form-label">Nome</label><input class="form-control" name="organigramma[<?= $i ?>][nome]" value="<?= h($r['nome']) ?>"></div>
                            <div class="col-md-2"><label class="form-label">Icona</label><?php renderIconPicker("organigramma[{$i}][icona]", $ICONE_ORGANIGRAMMA, $r['icona'], 'bi-person-fill'); ?></div>
                            <div class="col-md-2"><label class="form-label">Colore icona</label><?php renderColorPicker("organigramma[{$i}][icona_classe]", $COLORI_ICONE, $r['icona_classe'], 'text-primary'); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-2" onclick="addRow('rep-organigramma','tpl-organigramma')"><i class="bi bi-plus-lg"></i> Aggiungi membro</button>
            <template id="tpl-organigramma">
                <div class="repeater-row">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Ruolo</label><input class="form-control" list="ruoli-suggeriti" name="organigramma[__INDEX__][ruolo]" placeholder="Es. Caposquadra"></div>
                        <div class="col-md-4"><label class="form-label">Nome</label><input class="form-control" name="organigramma[__INDEX__][nome]"></div>
                        <div class="col-md-2"><label class="form-label">Icona</label><?php renderIconPicker('organigramma[__INDEX__][icona]', $ICONE_ORGANIGRAMMA, '', 'bi-person-fill'); ?></div>
                        <div class="col-md-2"><label class="form-label">Colore icona</label><?php renderColorPicker('organigramma[__INDEX__][icona_classe]', $COLORI_ICONE, '', 'text-primary'); ?></div>
                    </div>
                </div>
            </template>

            <datalist id="ruoli-suggeriti">
                <option value="Sindaco">
                <option value="Coordinatore">
                <option value="Vice Coordinatore">
                <option value="Caposquadra">
                <option value="Responsabile Logistica">
                <option value="Responsabile Comunicazione">
                <option value="Responsabile Radio">
                <option value="Autista soccorso">
                <option value="Volontario">
            </datalist>

        <?php elseif ($step === 6): ?>
            <h2 class="h5 mb-3">Sedi operative</h2>
            <p class="text-muted small">Coordinate in formato decimale (es. 45.2907, 10.2696) — puoi ricavarle da Google Maps: tasto destro sul punto → copia coordinate.</p>
            <div id="rep-sedi" data-counter="<?= count($S['sedi'] ?? []) ?>">
                <?php foreach (($S['sedi'] ?? []) as $i => $r): ?>
                    <div class="repeater-row">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                        <div class="row g-2">
                            <div class="col-md-3"><label class="form-label">Latitudine</label><input type="number" step="any" class="form-control" id="sedi-lat-<?= $i ?>" name="sedi[<?= $i ?>][lat]" value="<?= h($r['lat']) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Longitudine</label><input type="number" step="any" class="form-control" id="sedi-lng-<?= $i ?>" name="sedi[<?= $i ?>][lng]" value="<?= h($r['lng']) ?>"></div>
                            <div class="col-md-4"><label class="form-label">Titolo</label><input class="form-control" name="sedi[<?= $i ?>][titolo]" value="<?= h($r['titolo']) ?>"></div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-pick-map" data-lat="sedi-lat-<?= $i ?>" data-lng="sedi-lng-<?= $i ?>"><i class="bi bi-map"></i> Mappa</button>
                            </div>
                            <div class="col-md-3"><label class="form-label">Colore marker</label><input type="color" class="form-control form-control-color" name="sedi[<?= $i ?>][colore]" value="<?= h($r['colore'] ?: '#003B7A') ?>"></div>
                            <div class="col-md-5"><label class="form-label">Indirizzo</label><input class="form-control" name="sedi[<?= $i ?>][indirizzo]" value="<?= h($r['indirizzo']) ?>"></div>
                            <div class="col-md-4"><label class="form-label">Icona</label><?php renderIconPicker("sedi[{$i}][icona]", $ICONE_SEDI, $r['icona'], 'bi-geo-alt-fill'); ?></div>
                            <div class="col-md-4"><label class="form-label">Telefono (opz.)</label><input class="form-control" name="sedi[<?= $i ?>][phone]" value="<?= h($r['phone'] ?? '') ?>"></div>
                            <div class="col-12"><label class="form-label">Descrizione</label><input class="form-control" name="sedi[<?= $i ?>][desc]" value="<?= h($r['desc']) ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-4" onclick="addRow('rep-sedi','tpl-sedi')"><i class="bi bi-plus-lg"></i> Aggiungi sede</button>
            <template id="tpl-sedi">
                <div class="repeater-row">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label">Latitudine</label><input type="number" step="any" class="form-control" id="sedi-lat-__INDEX__" name="sedi[__INDEX__][lat]"></div>
                        <div class="col-md-3"><label class="form-label">Longitudine</label><input type="number" step="any" class="form-control" id="sedi-lng-__INDEX__" name="sedi[__INDEX__][lng]"></div>
                        <div class="col-md-4"><label class="form-label">Titolo</label><input class="form-control" name="sedi[__INDEX__][titolo]"></div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-pick-map" data-lat="sedi-lat-__INDEX__" data-lng="sedi-lng-__INDEX__"><i class="bi bi-map"></i> Mappa</button>
                        </div>
                        <div class="col-md-3"><label class="form-label">Colore marker</label><input type="color" class="form-control form-control-color" name="sedi[__INDEX__][colore]" value="#003B7A"></div>
                        <div class="col-md-5"><label class="form-label">Indirizzo</label><input class="form-control" name="sedi[__INDEX__][indirizzo]"></div>
                        <div class="col-md-4"><label class="form-label">Icona</label><?php renderIconPicker('sedi[__INDEX__][icona]', $ICONE_SEDI, '', 'bi-geo-alt-fill'); ?></div>
                        <div class="col-md-4"><label class="form-label">Telefono (opz.)</label><input class="form-control" name="sedi[__INDEX__][phone]"></div>
                        <div class="col-12"><label class="form-label">Descrizione</label><input class="form-control" name="sedi[__INDEX__][desc]"></div>
                    </div>
                </div>
            </template>

            <h2 class="h5 mb-1">Punti di attesa</h2>
            <p class="form-text mb-3">Il numero progressivo viene assegnato automaticamente in base all'ordine dei punti.</p>
            <div id="rep-attesa" data-counter="<?= count($S['attesa'] ?? []) ?>">
                <?php foreach (($S['attesa'] ?? []) as $i => $r): ?>
                    <div class="repeater-row">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                        <div class="row g-2">
                            <div class="col-md-3"><label class="form-label">Latitudine</label><input type="number" step="any" class="form-control" id="attesa-lat-<?= $i ?>" name="attesa[<?= $i ?>][lat]" value="<?= h($r['lat']) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Longitudine</label><input type="number" step="any" class="form-control" id="attesa-lng-<?= $i ?>" name="attesa[<?= $i ?>][lng]" value="<?= h($r['lng']) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Codice</label><input class="form-control" name="attesa[<?= $i ?>][cod]" value="<?= h($r['cod']) ?>"></div>
                            <div class="col-md-2"><label class="form-label">Località</label><input class="form-control" name="attesa[<?= $i ?>][loc]" value="<?= h($r['loc']) ?>"></div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-pick-map" data-lat="attesa-lat-<?= $i ?>" data-lng="attesa-lng-<?= $i ?>" title="Scegli sulla mappa"><i class="bi bi-map"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-4" onclick="addRow('rep-attesa','tpl-attesa')"><i class="bi bi-plus-lg"></i> Aggiungi punto di attesa</button>
            <template id="tpl-attesa">
                <div class="repeater-row">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label">Latitudine</label><input type="number" step="any" class="form-control" id="attesa-lat-__INDEX__" name="attesa[__INDEX__][lat]"></div>
                        <div class="col-md-3"><label class="form-label">Longitudine</label><input type="number" step="any" class="form-control" id="attesa-lng-__INDEX__" name="attesa[__INDEX__][lng]"></div>
                        <div class="col-md-3"><label class="form-label">Codice</label><input class="form-control" name="attesa[__INDEX__][cod]"></div>
                        <div class="col-md-2"><label class="form-label">Località</label><input class="form-control" name="attesa[__INDEX__][loc]"></div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-pick-map" data-lat="attesa-lat-__INDEX__" data-lng="attesa-lng-__INDEX__" title="Scegli sulla mappa"><i class="bi bi-map"></i></button>
                        </div>
                    </div>
                </div>
            </template>

            <h2 class="h5 mb-1">Punti di accoglienza e ricovero</h2>
            <p class="form-text mb-3">Il numero progressivo viene assegnato automaticamente in base all'ordine dei punti.</p>
            <div id="rep-accoglienza" data-counter="<?= count($S['accoglienza'] ?? []) ?>">
                <?php foreach (($S['accoglienza'] ?? []) as $i => $r): ?>
                    <div class="repeater-row">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                        <div class="row g-2">
                            <div class="col-md-3"><label class="form-label">Latitudine</label><input type="number" step="any" class="form-control" id="accoglienza-lat-<?= $i ?>" name="accoglienza[<?= $i ?>][lat]" value="<?= h($r['lat']) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Longitudine</label><input type="number" step="any" class="form-control" id="accoglienza-lng-<?= $i ?>" name="accoglienza[<?= $i ?>][lng]" value="<?= h($r['lng']) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Codice</label><input class="form-control" name="accoglienza[<?= $i ?>][cod]" value="<?= h($r['cod']) ?>"></div>
                            <div class="col-md-2"><label class="form-label">Località</label><input class="form-control" name="accoglienza[<?= $i ?>][loc]" value="<?= h($r['loc']) ?>"></div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-pick-map" data-lat="accoglienza-lat-<?= $i ?>" data-lng="accoglienza-lng-<?= $i ?>" title="Scegli sulla mappa"><i class="bi bi-map"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-2" onclick="addRow('rep-accoglienza','tpl-accoglienza')"><i class="bi bi-plus-lg"></i> Aggiungi punto di accoglienza</button>
            <template id="tpl-accoglienza">
                <div class="repeater-row">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label">Latitudine</label><input type="number" step="any" class="form-control" id="accoglienza-lat-__INDEX__" name="accoglienza[__INDEX__][lat]"></div>
                        <div class="col-md-3"><label class="form-label">Longitudine</label><input type="number" step="any" class="form-control" id="accoglienza-lng-__INDEX__" name="accoglienza[__INDEX__][lng]"></div>
                        <div class="col-md-3"><label class="form-label">Codice</label><input class="form-control" name="accoglienza[__INDEX__][cod]"></div>
                        <div class="col-md-2"><label class="form-label">Località</label><input class="form-control" name="accoglienza[__INDEX__][loc]"></div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-pick-map" data-lat="accoglienza-lat-__INDEX__" data-lng="accoglienza-lng-__INDEX__" title="Scegli sulla mappa"><i class="bi bi-map"></i></button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Modale selezione coordinate su mappa -->
            <div class="modal fade" id="mapPickerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-map"></i> Scegli il punto sulla mappa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-2">Clicca (o trascina il segnaposto) sul punto esatto: le coordinate verranno compilate automaticamente.</p>
                            <div id="map-picker-container"></div>
                            <div class="mt-2 small text-muted">Coordinate selezionate: <span id="map-picker-coords">—</span></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                            <button type="button" class="btn btn-primary" id="map-picker-confirm" style="background:#003B7A;border-color:#003B7A;">Usa queste coordinate</button>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
            <script>
            (function(){
                let leafletMap = null;
                let marker = null;
                let targetLatId = null;
                let targetLngId = null;
                const modalEl = document.getElementById('mapPickerModal');
                const bsModal = new bootstrap.Modal(modalEl);
                const coordsLabel = document.getElementById('map-picker-coords');

                function aggiornaLabel(lat, lng){
                    coordsLabel.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
                }

                function posizionaMarker(lat, lng){
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                    } else {
                        marker = L.marker([lat, lng], { draggable: true }).addTo(leafletMap);
                        marker.on('dragend', function(){
                            const p = marker.getLatLng();
                            aggiornaLabel(p.lat, p.lng);
                        });
                    }
                    aggiornaLabel(lat, lng);
                }

                document.addEventListener('click', function(e){
                    const btn = e.target.closest('.btn-pick-map');
                    if (!btn) return;
                    targetLatId = btn.dataset.lat;
                    targetLngId = btn.dataset.lng;
                    const latEl = document.getElementById(targetLatId);
                    const lngEl = document.getElementById(targetLngId);
                    const latVal = parseFloat(latEl.value);
                    const lngVal = parseFloat(lngEl.value);
                    const centro = (!isNaN(latVal) && !isNaN(lngVal)) ? [latVal, lngVal] : [41.8719, 12.5674];
                    const zoom = (!isNaN(latVal) && !isNaN(lngVal)) ? 15 : 5;

                    bsModal.show();
                    setTimeout(function(){
                        if (!leafletMap) {
                            leafletMap = L.map('map-picker-container').setView(centro, zoom);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(leafletMap);
                            leafletMap.on('click', function(ev){
                                posizionaMarker(ev.latlng.lat, ev.latlng.lng);
                            });
                        } else {
                            leafletMap.invalidateSize();
                            leafletMap.setView(centro, zoom);
                        }
                        if (!isNaN(latVal) && !isNaN(lngVal)) {
                            posizionaMarker(latVal, lngVal);
                        } else if (marker) {
                            leafletMap.removeLayer(marker);
                            marker = null;
                            coordsLabel.textContent = '—';
                        }
                    }, 200);
                });

                document.getElementById('map-picker-confirm').addEventListener('click', function(){
                    if (!marker) { bsModal.hide(); return; }
                    const p = marker.getLatLng();
                    document.getElementById(targetLatId).value = p.lat.toFixed(6);
                    document.getElementById(targetLngId).value = p.lng.toFixed(6);
                    bsModal.hide();
                });
            })();
            </script>

        <?php elseif ($step === 7): ?>
            <h2 class="h5 mb-3">Galleria immagini</h2>
            <p class="text-muted small">Indica solo il nome del file immagine (deve trovarsi nella cartella <code>/galleria/</code> del sito): il caricamento vero e proprio dei file va fatto via FTP/hosting.</p>
            <div id="rep-galleria" data-counter="<?= count($S['galleria'] ?? []) ?>">
                <?php foreach (($S['galleria'] ?? []) as $i => $r): ?>
                    <div class="repeater-row">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label">Nome file</label><input class="form-control" name="galleria[<?= $i ?>][src]" value="<?= h($r['src']) ?>" placeholder="evento-1.jpg"></div>
                            <div class="col-md-4"><label class="form-label">Testo alternativo</label><input class="form-control" name="galleria[<?= $i ?>][alt]" value="<?= h($r['alt']) ?>"></div>
                            <div class="col-md-4"><label class="form-label">Didascalia</label><input class="form-control" name="galleria[<?= $i ?>][didascalia]" value="<?= h($r['didascalia']) ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-4" onclick="addRow('rep-galleria','tpl-galleria')"><i class="bi bi-plus-lg"></i> Aggiungi immagine</button>
            <template id="tpl-galleria">
                <div class="repeater-row">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Nome file</label><input class="form-control" name="galleria[__INDEX__][src]" placeholder="evento-1.jpg"></div>
                        <div class="col-md-4"><label class="form-label">Testo alternativo</label><input class="form-control" name="galleria[__INDEX__][alt]"></div>
                        <div class="col-md-4"><label class="form-label">Didascalia</label><input class="form-control" name="galleria[__INDEX__][didascalia]"></div>
                    </div>
                </div>
            </template>

            <h2 class="h5 mb-3">Social</h2>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label"><i class="bi bi-facebook"></i> Facebook</label><input type="url" class="form-control" name="fb" value="<?= h($S['fb'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label"><i class="bi bi-instagram"></i> Instagram</label><input type="url" class="form-control" name="ig" value="<?= h($S['ig'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label"><i class="bi bi-whatsapp"></i> WhatsApp</label><input type="text" class="form-control" name="wa" value="<?= h($S['wa'] ?? '') ?>" placeholder="https://wa.me/393331234567"></div>
            </div>
            
            <hr class="my-4">
            <h2 class="h5 mb-3">Licenza dei contenuti</h2>
            <p class="text-muted small">
                Riguarda i testi, le foto e i materiali pubblicati sul sito (news, comunicati) —
                distinta dalla licenza del codice sorgente (AGPL-3.0). Non copre in nessun caso
                loghi/stemmi ufficiali dell'ente, sempre soggetti alla normativa vigente.
            </p>
            <div class="mb-3">
                <label class="form-label">Licenza applicata ai contenuti</label>
                <select name="content_license" class="form-select">
                    <?php
                    $licenzaCorrente = $S['content_license'] ?? 'Tutti i diritti riservati';
                    $opzioniLicenza = [
                        'CC BY 4.0'          => 'CC BY 4.0 — riuso libero anche commerciale, con citazione della fonte',
                        'CC BY-NC-SA 4.0'    => 'CC BY-NC-SA 4.0 — riuso non commerciale, con citazione, stessa licenza a valle',
                        'Tutti i diritti riservati' => 'Tutti i diritti riservati — nessun riuso senza autorizzazione',
                    ];
                    foreach ($opzioniLicenza as $valore => $etichetta): ?>
                        <option value="<?= h($valore) ?>" <?= $licenzaCorrente === $valore ? 'selected' : '' ?>>
                            <?= h($etichetta) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        <?php elseif ($step === 8): ?>
            <h2 class="h5 mb-3">Riepilogo</h2>
            <p class="text-muted">Controlla i dati principali e crea gli account iniziali prima di generare <code>data/config.php</code>. Se qualcosa non va, torna indietro con "Precedente".</p>
            <table class="table table-sm">
                <tr><th>Comune</th><td><?= h($S['nome_comune'] ?? '') ?></td></tr>
                <tr><th>Ente</th><td><?= h($S['nome_ente'] ?? '') ?></td></tr>
                <tr><th>Email</th><td><?= h($S['email_primary'] ?? '') ?></td></tr>
                <tr><th>Telefono sede</th><td><?= h($S['phone_sede'] ?? '') ?></td></tr>
                <tr><th>Sedi mappate</th><td><?= count($S['sedi'] ?? []) ?></td></tr>
                <tr><th>Punti di attesa</th><td><?= count($S['attesa'] ?? []) ?></td></tr>
                <tr><th>Punti di accoglienza</th><td><?= count($S['accoglienza'] ?? []) ?></td></tr>
                <tr><th>Foto in galleria</th><td><?= count($S['galleria'] ?? []) ?></td></tr>
                <tr><th>Membri organigramma</th><td><?= count($S['organigramma'] ?? []) ?></td></tr>
                <tr><th>Chiave di sicurezza</th><td><?= empty($S['chiave_sync']) ? '⚠️ non impostata' : '✅ impostata' ?></td></tr>
                <tr><th>Licenza contenuti</th><td><?= h($S['content_license'] ?? 'Tutti i diritti riservati') ?></td></tr>

            </table>
            <hr class="my-4">
            <h2 class="h5 mb-3"><i class="bi bi-shield-lock me-2"></i>Account superadmin</h2>
            <p class="text-muted small">Obbligatorio. Il segreto 2FA verrà generato automaticamente e mostrato una sola volta al termine.</p>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label required" for="superadmin_username">Username</label>
                    <input type="text" class="form-control" id="superadmin_username" name="superadmin_username" required pattern="[a-z0-9._-]{3,32}" value="<?= h($S['superadmin_username'] ?? 'superadmin') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label required" for="superadmin_password">Password</label>
                    <input type="password" class="form-control" id="superadmin_password" name="superadmin_password" required minlength="10" autocomplete="new-password">
                </div>
                <div class="col-md-4">
                    <label class="form-label required" for="superadmin_password_confirm">Conferma password</label>
                    <input type="password" class="form-control" id="superadmin_password_confirm" name="superadmin_password_confirm" required minlength="10" autocomplete="new-password">
                </div>
            </div>
            <h2 class="h5 mb-3"><i class="bi bi-person-badge me-2"></i>Account admin <span class="text-muted fw-normal">(opzionale)</span></h2>
            <p class="text-muted small">Lascia vuoti tutti i campi se non vuoi creare subito un account admin.</p>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label" for="admin_username">Username</label>
                    <input type="text" class="form-control" id="admin_username" name="admin_username" pattern="[a-z0-9._-]{3,32}" value="<?= h($S['admin_username'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="admin_password">Password</label>
                    <input type="password" class="form-control" id="admin_password" name="admin_password" minlength="10" autocomplete="new-password">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="admin_password_confirm">Conferma password</label>
                    <input type="password" class="form-control" id="admin_password_confirm" name="admin_password_confirm" minlength="10" autocomplete="new-password">
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="manutenzione_attiva" id="manutenzione_attiva" <?= !empty($S['manutenzione_attiva']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="manutenzione_attiva">
                    Metti subito il sito in modalità manutenzione dopo la generazione
                </label>
            </div>
            <div class="alert alert-warning">
                Alla conferma verranno creati <code>data/config.php</code>, gli account iniziali e i record utente nel database; il wizard verrà bloccato permanentemente (file <code>setup.lock</code>).
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mt-4">
            <?php if ($step > 1): ?>
                <button type="submit" name="action" value="prev" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Precedente</button>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <?php if ($step < $TOTAL_STEPS): ?>
                <button type="submit" name="action" value="next" class="btn btn-primary" style="background:#003B7A;border-color:#003B7A;">Avanti <i class="bi bi-arrow-right"></i></button>
            <?php else: ?>
                <button type="submit" name="action" value="finish" class="btn btn-success"><i class="bi bi-check-circle"></i> Genera configurazione</button>
            <?php endif; ?>
        </div>
    </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el){
        new bootstrap.Popover(el);
    });
});
</script>
<script>
function addRow(containerId, tplId) {
    const container = document.getElementById(containerId);
    let counter = parseInt(container.dataset.counter || '0', 10);
    const tpl = document.getElementById(tplId).content.cloneNode(true);
    const row = tpl.querySelector('.repeater-row');
    row.innerHTML = row.innerHTML.replaceAll('__INDEX__', counter);
    container.appendChild(row);
    container.dataset.counter = counter + 1;
}
document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-row')) {
        e.target.closest('.repeater-row').remove();
    }
});
</script>
<script>
// Gestione dei menu a tendina con anteprima (icone e colori):
// alla scelta di una voce, aggiorna il pulsante (icona/testo mostrati)
// e l'input nascosto che verrà effettivamente inviato col form.
// Delegato su document così funziona anche per le righe aggiunte dinamicamente.
document.addEventListener('click', function (e) {
    const item = e.target.closest('.icon-picker-item, .color-picker-item');
    if (!item) return;

    const dropdown = item.closest('.picker-dropdown');
    if (!dropdown) return;

    const valore = item.dataset.value;
    const etichetta = item.querySelector('span') ? item.querySelector('span').textContent : valore;
    const btn = dropdown.querySelector('.picker-btn');
    const btnIcon = btn.querySelector('.picker-btn-icon');
    const btnLabel = btn.querySelector('.picker-btn-label');

    if (item.classList.contains('icon-picker-item')) {
        const hidden = dropdown.querySelector('.icon-picker-value');
        hidden.value = valore;
        // ricrea la classe dell'icona sul pulsante (rimuove la vecchia bi-*, mantiene bi e picker-btn-icon)
        btnIcon.className = 'bi ' + valore + ' picker-btn-icon';
    } else {
        const hidden = dropdown.querySelector('.color-picker-value');
        hidden.value = valore;
        // il pallino di anteprima nel pulsante è un'icona bi-circle-fill colorata dalla classe text-*
        btnIcon.className = 'bi bi-circle-fill picker-btn-icon ' + valore;
    }
    btnLabel.textContent = etichetta;
});
</script>
</body>
</html>