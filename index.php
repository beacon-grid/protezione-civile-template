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

// Carica l'intestazione e la navigazione
include('includes/header.php'); 
require_once __DIR__ . '/php/site_search.php';
$searchSettings = site_search_settings();
?>
<?php if ($searchSettings['enabled']): ?>
<link href="/css/site-search.css" rel="stylesheet">
<?php endif; ?>
<main id="main-content" tabindex="-1">
    <section class="hero d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center g-5">
                
                <div class="col-lg-7 text-center text-lg-start animate__animated animate__fadeInLeft">
                    <span class="badge bg-warning text-dark text-uppercase fw-bold px-3 py-2 mb-3 shadow-sm animate__animated animate__flash animate__delay-1s">
                        <i class="bi bi-shield-fill-x me-1" aria-hidden="true"></i> Presidio Territoriale Attivo
                    </span>
                    
                    <h1 class="display-4 fw-bold text-white text-uppercase tracking-wide mb-3">
                        Gruppo Comunale<br>
                        <span class="text-warning">Protezione Civile</span>
                    </h1>
                    
                    <p class="lead text-white fs-5 mb-4 border-start border-warning border-3 ps-3 d-none d-sm-block" style="--bs-text-opacity: .85;">
                        Operiamo sul territorio di <?= htmlspecialchars($site['address']['city']) ?> per la previsione, prevenzione, 
                        gestione delle emergenze e il supporto continuo alla cittadinanza.
                    </p>
                    <p class="lead text-white fs-6 mb-4 d-block d-sm-none" style="--bs-text-opacity: .85;">
                        Prevenzione e supporto continuo alla cittadinanza di <?= htmlspecialchars($site['address']['city']) ?>.
                    </p>
                    
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                        <a href="/allerte/" class="btn btn-warning btn-lg px-4 py-3 fw-bold text-uppercase shadow">
                            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i> Bollettino Allerte
                        </a>
                        <a href="chi-siamo.php" class="btn btn-outline-light btn-lg px-4 py-3 fw-semibold text-uppercase transition">
                            <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i> Scopri il Gruppo
                        </a>
                    </div>
                </div>

                <div class="col-lg-5 d-none d-lg-block animate__animated animate__fadeInRight">
                    <div class="card bg-white bg-opacity-10 text-white border border-white border-opacity-25 rounded-3 shadow blur-effect">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold text-warning border-bottom border-white border-opacity-25 pb-2 mb-3">
                                <i class="bi bi-clock-history me-2" aria-hidden="true"></i>In Evidenza h24
                            </h2>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="bi bi-geo-alt-fill text-warning me-3 fs-4" aria-hidden="true"></i>
                                    <div>
                                        <strong class="d-block">Sede Operativa:</strong>
                                        <span class="text-white small" style="--bs-text-opacity: .85;"><?= htmlspecialchars($site['address']['street']) ?> — <?= htmlspecialchars(strtoupper($site['address']['city'])) ?></span>
                                    </div>
                                </li>
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="bi bi-telephone-fill text-danger me-3 fs-4" style="color:#FFAB00 !important" aria-hidden="true"></i>
                                    <div>
                                        <strong class="d-block text-danger" style="color:#FFAB00 !important">Emergenze Nazionali:</strong>
                                        <span class="fw-bold fs-5" ><?= htmlspecialchars($site['phones']['emergency']) ?></span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start">
                                    <i class="bi bi-layers-fill text-warning me-3 fs-4" aria-hidden="true"></i>
                                    <div>
                                        <strong class="d-block">Piano di Emergenza:</strong>
                                        <span class="text-white small" style="--bs-text-opacity: .85;">Disponibile per il <a href="assets/pdf/piano_emergenza_comunale.pdf" class="text-white text-decoration-underline">download</a> in PDF</span>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php if ($searchSettings['enabled']): ?>
    <section class="site-search-home py-4 py-lg-5" aria-labelledby="home-search-title">
        <div class="container">
            <div class="site-search-home__panel">
                <div class="site-search-home__intro">
                    <span class="site-search-home__icon" aria-hidden="true"><i class="bi bi-search"></i></span>
                    <div>
                        <h2 class="h4 mb-1" id="home-search-title">Cerchi un’informazione?</h2>
                        <p class="mb-0">Trova rapidamente notizie, guide, documenti e definizioni.</p>
                    </div>
                </div>
                <form action="/cerca" method="get" role="search" class="site-search-home__form">
                    <label class="visually-hidden" for="home-search-query">Cerca nel sito</label>
                    <input
                        class="form-control form-control-lg"
                        type="search"
                        id="home-search-query"
                        name="q"
                        maxlength="100"
                        placeholder="Es. alluvione, piano di emergenza"
                        aria-label="Cerca nel sito">
                    <button class="btn btn-primary btn-lg" type="submit">
                        Cerca <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include('includes/news_section.php'); ?>

    <section id="rischi" class="bg-light">
        <div class="container">
            <h2 class="text-center section-title">Cosa fare in caso di</h2>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card service-card h-100 text-center p-4 shadow-sm border-0">
                        <div class="service-icon"><i class="bi bi-house-exclamation" aria-hidden="true"></i></div>
                        <h3 class="mt-3 fs-4 fw-bold">Terremoto</h3>
                        <p class="text-muted small mb-0">Informazioni e comportamenti corretti in caso di sisma.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card service-card h-100 text-center p-4 shadow-sm border-0">
                        <div class="service-icon"><i class="bi bi-water" aria-hidden="true"></i></div>
                        <h3 class="mt-3 fs-4 fw-bold">Alluvione</h3>
                        <p class="text-muted small mb-0">Procedure di sicurezza in caso di rischio idrogeologico.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card service-card h-100 text-center p-4 shadow-sm border-0">
                        <div class="service-icon"><i class="bi bi-fire" aria-hidden="true"></i></div>
                        <h3 class="mt-3 fs-4 fw-bold">Incendio</h3>
                        <p class="text-muted small mb-0">Norme di comportamento e prevenzione incendi.</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="rischi/" class="btn btn-outline-primary px-4 py-2 fw-semibold">
                        Approfondisci tutti i rischi <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <section id="numeri-utili" class="bg-light pt-0">
        <div class="container">
            <h2 class="text-center section-title">Numeri Utili</h2>
            <?php
            $emergencyLabels = [
                '112' => ['label' => 'Numero unico emergenze', 'icon' => 'bi-exclamation-triangle-fill', 'class' => 'text-danger', 'color' => '#b02a37'],
                '113' => ['label' => 'Polizia di Stato', 'icon' => 'bi-shield-fill', 'class' => 'text-primary', 'color' => '#0d6efd'],
                '115' => ['label' => 'Vigili del Fuoco', 'icon' => 'bi-fire', 'class' => 'text-danger'],
                '117' => ['label' => 'Guardia di Finanza', 'icon' => 'bi-shield-lock-fill', 'class' => 'text-secondary', 'color' => '#495057'],
                '118' => ['label' => 'Emergenza sanitaria', 'icon' => 'bi-heart-pulse-fill', 'class' => 'text-success'],
                '1515' => ['label' => 'Incendi boschivi', 'icon' => 'bi-tree-fill', 'class' => 'text-success'],
                '1522' => ['label' => 'Antiviolenza e stalking', 'icon' => 'bi-telephone-fill', 'class' => 'text-dark', 'color' => '#a61e4d'],
                '1530' => ['label' => 'Guardia Costiera', 'icon' => 'bi-life-preserver', 'class' => 'text-primary'],
            ];
            foreach (($site['emergency_labels'] ?? []) as $customNumber => $customLabel) {
                if (is_string($customLabel) && trim($customLabel) !== '') {
                    if (!isset($emergencyLabels[(string) $customNumber])) {
                        $emergencyLabels[(string) $customNumber] = [
                            'label' => trim($customLabel),
                            'icon' => 'bi-telephone-fill',
                            'class' => 'text-primary',
                        ];
                    } else {
                        $emergencyLabels[(string) $customNumber]['label'] = trim($customLabel);
                    }
                }
            }
            $emergencyNumbers = array_values(array_filter(array_map('strval', $site['emergencies'] ?? []), static fn($number) => trim($number) !== ''));
            ?>
            <div class="row text-center g-4">
                <?php foreach ($emergencyNumbers as $number):
                    $emergencyConfig = $emergencyLabels[$number] ?? [
                        'label' => 'Numero utile',
                        'icon' => 'bi-telephone-fill',
                        'class' => 'text-primary',
                    ];
                    $telNumber = preg_replace('/[^+0-9]/', '', $number);
                ?>
                    <div class="col-6 col-md-3">
                        <a href="tel:<?= htmlspecialchars($telNumber, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-dark emergency-compact-link">
                            <div class="card p-4 h-100 border-0 shadow-sm">
                                <h3 class="fw-bold mb-1 <?= htmlspecialchars($emergencyConfig['class'], ENT_QUOTES, 'UTF-8') ?> fs-2"<?= !empty($emergencyConfig['color']) ? ' style="color: ' . htmlspecialchars($emergencyConfig['color'], ENT_QUOTES, 'UTF-8') . ';"' : '' ?>>
                                    <i class="bi <?= htmlspecialchars($emergencyConfig['icon'], ENT_QUOTES, 'UTF-8') ?> me-2" aria-hidden="true"></i><?= htmlspecialchars($number, ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <p class="mb-0 fw-semibold text-secondary"><?= htmlspecialchars($emergencyConfig['label'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php 
// Carica la chiusura della pagina e i file JS
include('includes/footer.php'); 
?>