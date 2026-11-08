<?php
/**
 * Template sito Protezione Civile
 * Copyright (C) 2026 [Il tuo nome/pseudonimo]
 *
 * Questo programma è software libero: puoi ridistribuirlo e/o modificarlo
 * secondo i termini della GNU Affero General Public License v3, come
 * pubblicata dalla Free Software Foundation.
 *
 * Distribuito SENZA ALCUNA GARANZIA. Testo completo della licenza nel
 * file LICENSE incluso in questo repository, o su:
 * https://www.gnu.org/licenses/agpl-3.0.html
 */

require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site   = $config['site'];

date_default_timezone_set('Europe/Rome');

// Placeholder facoltativo: se valorizzato in config.php, mostra una stima di rientro
$rientro_previsto = $site['manutenzione']['rientro_previsto'] ?? null;

// Step corrente del processo di manutenzione (1, 2 o 3).
// Configurabile in config.php; di default si assume "aggiornamento in corso".
$step_attuale = $site['manutenzione']['fase_attuale'];

$step_labels = [
    1 => 'Controllo di sicurezza',
    2 => 'Miglioramento servizio',
    3 => 'Messa online',
];

http_response_code(503);      // Service Unavailable, corretto per manutenzione
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Sito in manutenzione — <?= htmlspecialchars($site['nome_breve']) ?></title>
    <meta name="theme-color" content="#003B7A">

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">

    <style>
        /* Stili specifici della pagina di manutenzione.
           Usa solo le variabili già definite in style.css. */

        html, body {
            height: 100%;
        }

        body.maintenance-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(circle at 85% 12%, rgba(255,194,0,.10) 0%, transparent 45%),
                linear-gradient(160deg, #00142e 0%, var(--pc-blue) 65%, var(--pc-blue-mid) 100%);
        }

        .maintenance-card {
            width: 100%;
            max-width: 880px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .maintenance-main {
            background: #fff;
            padding: 56px 48px;
        }

        @media (max-width: 767.98px) {
            .maintenance-main { padding: 40px 28px; }
        }

        .maintenance-eyebrow {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
        }

        .maintenance-eyebrow .eyebrow-rule {
            width: 28px;
            height: 3px;
            background: var(--pc-yellow-text);
            border-radius: 2px;
            flex-shrink: 0;
        }

        .maintenance-eyebrow span {
            font-family: var(--font-display);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--pc-blue);
        }

        .maintenance-main h1 {
            font-family: var(--font-display);
            font-size: clamp(1.7rem, 3.6vw, 2.3rem);
            font-weight: 800;
            color: var(--pc-blue-text);
            letter-spacing: 0.01em;
            line-height: 1.18;
            margin-bottom: 16px;
        }

        .maintenance-main p.lead {
            color: #4a5568;
            font-size: 1.02rem;
            line-height: 1.65;
            margin-bottom: 0;
        }

        /* Stepper: rappresenta le fasi reali del processo di manutenzione */
        .maintenance-stepper {
            display: flex;
            margin-top: 34px;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            position: relative;
            text-align: left;
        }

        .step-line {
            position: absolute;
            top: 13px;
            left: -100%;
            width: 100%;
            height: 2px;
            background: #dde3ec;
            z-index: 0;
        }

        .step:first-child .step-line {
            display: none;
        }

        .step.is-done .step-line,
        .step.is-current .step-line {
            background: var(--pc-yellow-dk);
        }

        .step-marker {
            position: relative;
            z-index: 1;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef1f5;
            border: 2px solid #dde3ec;
            font-family: var(--font-display);
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        .step.is-done .step-marker {
            background: var(--pc-yellow);
            border-color: var(--pc-yellow-dk);
            color: var(--pc-blue-text);
        }

        .step.is-current .step-marker {
            background: #fff;
            border-color: var(--pc-blue);
            color: var(--pc-blue);
            box-shadow: 0 0 0 4px rgba(0,59,122,.12);
        }

        @media (prefers-reduced-motion: no-preference) {
            .step.is-current .step-marker {
                animation: pulse 2.2s ease-in-out infinite;
            }
        }

        .step-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.35;
            color: #94a3b8;
            max-width: 110px;
        }

        .step.is-done .step-label,
        .step.is-current .step-label {
            color: var(--pc-blue-text);
        }

        .maintenance-eta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f0f5ff;
            border: 1px solid #dde3ec;
            border-radius: var(--radius-md);
            font-size: 0.92rem;
            color: var(--pc-blue-text);
            font-weight: 600;
        }

        .maintenance-side {
            background: #0D2347;
            padding: 40px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-top: 4px solid var(--pc-yellow);
        }

        @media (min-width: 768px) {
            .maintenance-side {
                border-top: none;
                border-left: 4px solid var(--pc-yellow);
            }
        }

        .maintenance-side .footer-emergency-label {
            color: #ff8787;
        }

        .maintenance-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 28px;
            font-size: 0.88rem;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            transition: color var(--transition), gap var(--transition);
        }

        .maintenance-back:hover {
            color: var(--pc-yellow);
        }

        .maintenance-side hr {
            border-color: rgba(255,255,255,.1);
            margin: 24px 0;
        }

        .maintenance-side .side-note {
            font-size: 0.82rem;
            color: rgba(255,255,255,.55);
            line-height: 1.6;
        }
    </style>
</head>
<body class="maintenance-body">

<div class="maintenance-card row g-0">

    <div class="maintenance-main col-md-7">
        <div class="maintenance-eyebrow">
            <span class="eyebrow-rule" aria-hidden="true"></span>
            <span>Manutenzione programmata</span>
        </div>

        <h1>Il sito è temporaneamente offline per manutenzione</h1>
        <p class="lead">
            Stiamo aggiornando <?= htmlspecialchars($site['nome_ente']) ?> per migliorare
            il servizio. Le pagine informative torneranno disponibili a breve: non è in corso
            nessuna emergenza, questa interruzione riguarda solo il sito web.
        </p>

        <div class="maintenance-stepper" role="list" aria-label="Fasi dell'intervento di manutenzione">
            <?php foreach ($step_labels as $n => $label):
                $stato = $n < $step_attuale ? 'is-done' : ($n === $step_attuale ? 'is-current' : '');
            ?>
                <div class="step <?= $stato ?>" role="listitem">
                    <span class="step-line" aria-hidden="true"></span>
                    <span class="step-marker" aria-hidden="true">
                        <?php if ($stato === 'is-done'): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <?= $n ?>
                        <?php endif; ?>
                    </span>
                    <span class="step-label">
                        <?= htmlspecialchars($label) ?>
                        <?php if ($stato === 'is-current'): ?>
                            <span class="visually-hidden"> (fase attuale)</span>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($rientro_previsto): ?>
            <div class="maintenance-eta">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                Rientro previsto: <?= htmlspecialchars($rientro_previsto) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="maintenance-side col-md-5">
        <span class="footer-emergency-label">In caso di emergenza reale</span>
        <a href="tel:112" class="footer-emergency-number">
            <i class="bi bi-telephone-fill" aria-hidden="true"></i> 112
        </a>
        <small class="footer-emergency-sub">
            Numero Unico di Emergenza Europeo — sempre attivo, indipendentemente
            dallo stato di questo sito
        </small>

        <hr>

        <p class="side-note">
            Per segnalazioni non urgenti o informazioni, puoi contattarci a:<br>
            <a href="mailto:<?= htmlspecialchars($site['emails']['primary']) ?>"
               style="color: var(--pc-yellow); text-decoration: underline;">
                <?= htmlspecialchars($site['emails']['primary']) ?>
            </a>
        </p>

        <?php if (!empty($site['urls']['comune'])): ?>
            <a href="<?= htmlspecialchars($site['urls']['comune']) ?>" class="maintenance-back">
                <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
                Torna al sito del Comune
            </a>
        <?php endif; ?>
    </div>

</div>

</body>
</html>