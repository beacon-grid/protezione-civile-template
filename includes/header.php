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
 */

require_once __DIR__ . '/../php/config.php';
$config = app_config();
$emergency_helper = __DIR__ . '/../php/emergency_helper.php';
if (is_readable($emergency_helper)) {
    require_once $emergency_helper;
    if (emergency_should_redirect()) {
        header('Location: /emergency.php', true, 302);
        exit;
    }
}
$site = $site ?? $config['site'];

// Valori di default (Home)
$meta_title       = $meta_title       ?? $site['meta_title'];
$meta_description = $meta_description ?? $site['meta_description'];
$meta_url         = $meta_url         ?? $site['urls']['home'] . $_SERVER['REQUEST_URI'];
$meta_robots      = $meta_robots      ?? 'index, follow';
date_default_timezone_set('Europe/Rome');

$is_admin = $is_admin ?? false;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <script src="/js/banda-check-config.js"></script>
    <script src="/js/banda-check.js"></script>
    <script src="/js/alert-bar.js"></script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="geo.region" content="IT-<?= htmlspecialchars($site['address']['province']) ?>">
    <meta name="geo.placename" content="<?= htmlspecialchars($site['address']['city']) ?>">
    <meta name="theme-color" content="<?= htmlspecialchars($site['colore_tema']) ?>">
    <title><?= htmlspecialchars($meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="keywords"    content="<?= htmlspecialchars($site['meta_keywords']) ?>">
    <meta name="author"      content="<?= htmlspecialchars($site['meta_author']) ?>">
    <meta name="robots"      content="<?= htmlspecialchars($meta_robots) ?>">
    <link rel="canonical"    href="<?= htmlspecialchars($meta_url) ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= htmlspecialchars($meta_url) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($meta_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta property="og:image"       content="<?= htmlspecialchars($site['og_image']) ?>">
    <meta property="og:image:alt"   content="<?= htmlspecialchars($site['nome_breve'] . ' - logo del gruppo') ?>">
    <meta property="og:locale"      content="it_IT">
    <meta property="og:site_name"   content="<?= htmlspecialchars($site['nome_breve']) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= htmlspecialchars($meta_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta name="twitter:image"       content="<?= htmlspecialchars($site['og_image']) ?>">

    <link rel="icon" type="image/x-icon" href="/assets/img/logo.png">

    <link href="/css/bootstrap.min.css"        rel="stylesheet">
    <link href="/css/bootstrap-icons.min.css"  rel="stylesheet">
    <link href="/css/style.css"                rel="stylesheet">
    <link href="/css/glossario.css"            rel="stylesheet">
    
    <!-- Structured Data JSON-LD: Organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "parentOrganization": {
            "@type": "GovernmentOrganization",
            "name": <?= json_encode('Comune di ' . $site['nome_comune']) ?>
        },
        "name":          <?= json_encode($site['nome_ente']) ?>,
        "alternateName": <?= json_encode($site['nome_breve']) ?>,
        "description":   <?= json_encode('Sito informativo dei volontari di protezione civile di ' . $site['nome_comune'] . ' (' . $site['address']['province'] . '), gestito autonomamente da volontari del gruppo') ?>,
        "url":           <?= json_encode($site['urls']['home']) ?>,
        "logo":          <?= json_encode($site['og_image']) ?>,
        "image":         <?= json_encode($site['og_image']) ?>,
        "address": {
            "@type":           "PostalAddress",
            "streetAddress":   <?= json_encode($site['address']['street']) ?>,
            "addressLocality": <?= json_encode($site['address']['city']) ?>,
            "addressRegion":   <?= json_encode($site['address']['region']) ?>,
            "postalCode":      <?= json_encode($site['address']['postal_code']) ?>,
            "addressCountry":  <?= json_encode($site['address']['country']) ?>
        },
        "telephone": <?= json_encode($site['phones']['sede']) ?>,
        "email":     <?= json_encode($site['emails']['primary']) ?>,
        "contactPoint": {
            "@type":       "ContactPoint",
            "contactType": "Informazioni",
            "telephone":   <?= json_encode($site['phones']['sede']) ?>,
            "email":       <?= json_encode($site['emails']['primary']) ?>
        },
        "areaServed": {
            "@type": "City",
            "name":  <?= json_encode($site['address']['city']) ?>,
            "containedInPlace": {
                "@type": "State",
                "name":  <?= json_encode($site['address']['province_name']) ?>
            }
        },
        "knowsAbout": [
            {
                "@type":       "DefinedTerm",
                "name":        "Protezione Civile",
                "description": "Sistema integrato di enti e soggetti pubblici e privati che svolgono attività di previsione, prevenzione e mitigazione dei rischi, gestione delle emergenze e superamento dei loro effetti.",
                "url":         "https://www.protezionecivile.gov.it",
                "sameAs":      "https://www.wikidata.org/wiki/Q206936"
            },
            {
                "@type":       "DefinedTerm",
                "name":        "Gestione dell'Emergenza",
                "description": "Discipline di gestione e prevenzione di disastri sia naturali che antropici.",
                "url":         "https://www.protezionecivile.gov.it/it/emergenze",
                "sameAs":      "https://www.wikidata.org/wiki/Q1460420"
            },
            {
                "@type":       "DefinedTerm",
                "name":        "Volontariato di Protezione Civile",
                "description": "Organizzazioni di volontari integrate nel sistema nazionale italiano di protezione civile per attività di prevenzione, preparazione e soccorso.",
                "url":         "https://www.protezionecivile.gov.it/it/volontariato",
                "keywords": [
                    "volontariato protezione civile",
                    "organizzazioni di soccorso",
                    "primo intervento emergenza",
                    <?= json_encode('protezione civile ' . $site['nome_comune']) ?>,
                    <?= json_encode('volontari ' . $site['nome_comune']) ?>,
                    <?= json_encode('protezione civile ' . $site['address']['province_name']) ?>
                ]
            },
            {
                "@type":       "DefinedTerm",
                "name":        <?= json_encode('Protezione Civile Locale - ' . $site['nome_comune']) ?>,
                "description": <?= json_encode('Gruppo volontari di protezione civile operativo in ' . $site['nome_comune'] . ', ' . $site['address']['province'] . ' per prevenzione, preparazione e soccorso nelle emergenze locali.') ?>,
                "url":         <?= json_encode($site['urls']['home']) ?>,
                "areaServed": {
                    "@type": "City",
                    "name":  <?= json_encode($site['address']['city']) ?>
                },
                "keywords": [
                    <?= json_encode('protezione civile ' . $site['nome_comune']) ?>,
                    <?= json_encode('volontari protezione civile ' . $site['nome_comune']) ?>,
                    <?= json_encode('emergenza ' . $site['nome_comune']) ?>,
                    <?= json_encode('soccorso provincia ' . $site['address']['province_name']) ?>
                ]
            }
        ]
    }
    </script>
</head>
<body>

<a class="visually-hidden-focusable" href="#main-content">Salta al contenuto principale</a>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top" aria-label="Menu di navigazione principale">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="/" tabindex="-1">
            <img src="/assets/img/logo.png"
                 alt="<?= htmlspecialchars($site['nome_breve'] . ' - logo del gruppo') ?>"
                 height="60"
                 class="d-inline-block align-top me-2"
                 onerror="this.style.display='none'; document.getElementById('fallback-icon').style.display='inline-block';">
            <i id="fallback-icon"
               class="bi bi-shield-fill text-warning me-2 fs-3"
               style="display:none;"
               aria-hidden="true"></i>

            <div class="d-flex flex-column" style="line-height:1.2;">
                <span class="fw-bold text-uppercase" style="letter-spacing:.5px;">Protezione Civile</span>
                <span class="text-warning fw-semibold">
                    Gruppo Comunale
                    <span class="d-block d-sm-inline"><?= htmlspecialchars($site['nome_comune']) ?></span>
                </span>
            </div>
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menu"
                aria-controls="menu"
                aria-expanded="false"
                aria-label="Espandi o comprimi la navigazione">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto text-center text-lg-start align-items-center">

                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/chi-siamo.php">Chi Siamo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/#rischi">Prevenzione</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/news/">Notizie</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle"
                       href="#"
                       id="navbarDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        Servizi e Mappe
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm"
                        aria-labelledby="navbarDropdown"
                        data-bs-popper="static">
                        <li>
                            <a class="dropdown-item" href="/mappa.php">
                                <i class="bi bi-map-fill text-primary me-2" aria-hidden="true"></i>
                                Mappa Sicurezza
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/area-formazione">
                                <i class="bi bi-controller text-warning me-2" aria-hidden="true"></i>
                                Impara Giocando (Scuole)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/glossario">
                                <i class="bi bi-book text-primary me-2" aria-hidden="true"></i>
                                Glossario
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a class="nav-link btn btn-outline-warning btn-sm px-3 py-1 href-emergency nav-emergency"
                       href="/#numeri-utili"
                       aria-label="Numeri utili per le emergenze">
                        <i class="bi bi-telephone-fill me-1" aria-hidden="true"></i>
                        Emergenze
                    </a>
                </li>

                <li class="nav-item d-none d-lg-block" aria-hidden="true">
                    <span class="nav-link pe-none opacity-25">|</span>
                </li>

                <?php if (!$is_admin): ?>
                <li class="nav-item">
                    <a class="nav-link nav-link-admin d-flex align-items-center gap-1"
                       href="/admin/"
                       aria-label="Accesso all'area riservata amministratore"
                       title="Area Riservata">
                        <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                        <span class="d-lg-none">Area Riservata</span>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<section class="alert-bar align-items-center justify-content-between px-3"
         aria-label="Stato delle allerte meteo e link esterni">
    <a href="<?= htmlspecialchars($site['urls']['comune']) ?>"
       class="link-comune small d-flex align-items-center gap-1"
       style="color:rgba(255,255,255,.88) !important;">
        <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
        Torna a: Sito Comune
    </a>

    <div id="box-allerta"
         class="stato-allerta mx-auto text-uppercase fw-bold p-2 text-center"
         aria-live="polite"
         aria-atomic="true">
        <i id="icona-allerta" class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>
        Stato Allerta: <span id="testo-allerta">Controllo in corso...</span>
    </div>
    <div class="d-none d-lg-block" style="width:90px;" aria-hidden="true"></div>
</section>