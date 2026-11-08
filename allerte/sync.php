<!--sync.php-->

<?php
require_once dirname(__DIR__) . '/allerte/allerta_core.php';

date_default_timezone_set('Europe/Rome');

require_once dirname(__DIR__) . '/php/config.php';
$config = app_config();
$site = $config['site'];

$rateLimitSecondi = 300; // 5 minuti, solo per i trigger manuali via browser

$isCli = php_sapi_name() === 'cli';
$autorizzato = $isCli;
$messaggioAuth = '';

// Se chiamato da browser/HTTP, richiede il token configurato
if (!$isCli) {
    $tokenRicevuto    = $_GET['token'] ?? '';          // token hash ricevuto dall'URL
    $tokenConfigurato = getenv('FORZA_AGGIORNAMENTO_TOKEN') 
                        ?: ($site['security']['forza_aggiornamento_token'] ?? '');

    $tokenConfiguratoHash = hash('sha256', $tokenConfigurato); // hash del token in chiaro da config

    if ($tokenConfigurato && hash_equals($tokenConfiguratoHash, $tokenRicevuto)) {
        $autorizzato = true;
    } else {
        http_response_code(403);
        $messaggioAuth = 'Token scaduto, non valido o assente.';
    }
}

// Rate limit solo sui trigger manuali via browser, non sul cron pianificato
if (!$isCli && $autorizzato) {
    $rateData = app_data_get('alert_force_rate', []);
    $ultimaForzatura = is_array($rateData) ? ($rateData['last_update'] ?? null) : null;

    if ($ultimaForzatura && (time() - $ultimaForzatura) < $rateLimitSecondi) {
        $autorizzato = false;
        // Modificato lo stato d'errore a 429 (Too Many Requests) o mantenuto blocco grafico
        $messaggioAuth = 'Aggiornamento manuale troppo frequente. Riprova dopo qualche minuto.';
    } elseif (!app_data_set('alert_force_rate', ['last_update' => time()])) {
        $autorizzato = false;
        $messaggioAuth = 'Impossibile registrare il limite di frequenza dell’aggiornamento.';
    }
}

// --------------------------------------------------------------------------
// CONTROLLO DI AUTORIZZAZIONE BLOCCANTE E REINTEGRO INTERFACCIA 403
// --------------------------------------------------------------------------
if (!$autorizzato) {
    if ($isCli) {
        // Output CLI pulito in caso di errore privilegi o rate-limit (molto raro su CLI)
        echo "STATUS: ERRORE | Riferimento: error_auth | Nota: $messaggioAuth\n";
        exit;
    } else {
        // Carica la pagina 403 elegante e accessibile che abbiamo strutturato
        $errorPagePath = $_SERVER['DOCUMENT_ROOT'] . '/errors/403.php';
        if (file_exists($errorPagePath)) {
            require_once $errorPagePath;
        } else {
            // Fallback d'emergenza stringato se il file non fosse nella posizione indicata
            echo 'Accesso negato: ' . htmlspecialchars($messaggioAuth);
        }
        exit;
    }
}

// Se lo script arriva qui, significa che l'utente è VERIFICATO e AUTORIZZATO
$risultato = eseguiSincronizzazione($site);
$statoSincro = $risultato['ok'] ? 'success' : 'error_sync';
$messaggioErrore = $risultato['errore'] ?? '';
$allertaMassima = $risultato['output']['allerta_globale'] ?? '';
$ultimoAggiornamento = $risultato['output']['ultimo_aggiornamento'] ?? 'N/D';

if (!$risultato['ok']) {
    error_log('[sync_allerta] ' . $messaggioErrore);
}

// --------------------------------------------------------------------------
// OUTPUT INTERFACCIA GRAFICA DI SUCCESSO / ERRORE DI SINCRONIZZAZIONE
// --------------------------------------------------------------------------
if (!$isCli) {
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sincronizzazione Allerte</title>
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <link href="/css/bootstrap-icons.min.css" rel="stylesheet">
        <style>
            :root {
                --pc-blue: #003B7A;
                --alert-giallo: #C9A600;
                --alert-verde: #1A7A3C;
                --alert-arancio: #D45D00;
                --alert-rosso: #B81C1C;
            }
            body {
                background-color: #f4f6f9;
                font-family: 'Source Sans 3', sans-serif;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }
            .sync-card {
                background: white;
                border-radius: 14px;
                box-shadow: 0 6px 24px rgba(0,0,0,.08);
                padding: 40px;
                text-align: center;
                max-width: 450px;
                width: 100%;
                border-top: 5px solid var(--pc-blue);
            }
            .icon-circle {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                font-size: 2.5rem;
            }
            .badge-stato {
                padding: 6px 16px;
                border-radius: 8px;
                font-weight: bold;
                display: inline-block;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>

    <div class="sync-card">
        <?php if ($statoSincro === 'success'): ?>
            <div class="icon-circle" style="background: rgba(26, 122, 60, 0.1); color: var(--alert-verde);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h3 style="color: #121E2B; font-weight: 700;">Sincronizzazione Riuscita!</h3>
            <p class="text-muted small">I dati del portale sono ora allineati con il feed della Protezione Civile Regionale.</p>

            <div class="p-3 my-3 bg-light rounded text-start small border">
                <div class="mb-1"><strong>Data/Ora:</strong> <?= htmlspecialchars($ultimoAggiornamento) ?></div>
                <div><strong>Stato rilevato:</strong>
                    <?php
                    $colori = ['VERDE'=>'var(--alert-verde)', 'GIALLO'=>'var(--alert-giallo)', 'ARANCIONE'=>'var(--alert-arancio)', 'ROSSO'=>'var(--alert-rosso)'];
                    $col = $colori[$allertaMassima] ?? '#6c757d';
                    $txt = ($allertaMassima === 'GIALLO') ? '#121E2B' : '#ffffff';
                    ?>
                    <span class="badge-stato" style="background-color: <?= $col ?>; color: <?= $txt ?>;">
                        <?= htmlspecialchars($allertaMassima) ?>
                    </span>
                </div>
            </div>
        <?php else: ?>
            <div class="icon-circle" style="background: rgba(184, 28, 28, 0.1); color: var(--alert-rosso);">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <h3 class="text-danger" style="font-weight: 700;">Sincronizzazione Fallita</h3>
            <p class="text-muted small">Si è verificato un problema durante il recupero o il salvataggio dei dati. Il dettaglio tecnico è stato registrato nei log.</p>
            
            <?php if (!empty($messaggioErrore) && $messaggioErrore !== $messaggioAuth): ?>
                <div class="alert alert-danger p-2 small text-start"><?= htmlspecialchars($messaggioErrore) ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <a href="javascript:history.back()" class="btn btn-sm mt-2 w-100 text-white" style="background-color: var(--pc-blue); border-radius: 8px; padding: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Torna al Pannello
        </a>
    </div>

    </body>
    </html>
    <?php
} else {
    // OUTPUT CLI PULITO PER I CRONJOB INFRASETTIMANALI
    echo "STATUS: OK | Allerta globale: $allertaMassima | Data: $ultimoAggiornamento\n";
}