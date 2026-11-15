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

// Fogli specifici caricati nell'head comune del sito.
$page_stylesheets = [
    '/css/rischi-prevenzione.css',
    '/css/rischi-modali.css',
];

// Header del sito (menu e alert-bar)
include('../includes/header.php');
?>

<main id="main-content" tabindex="-1">

    <header class="pg-hero">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h1 class="display-4 fw-bold pg-title">Sei preparato?</h1>
                    <p class="lead text-secondary">
                        Scopri cosa fare prima, durante e dopo un'emergenza e prepara la tua casa e la tua famiglia.
                    </p>
                    <hr class="w-25 mx-auto pg-title-rule" aria-hidden="true">
                </div>
            </div>

            <div class="pg-feature-panel">
                <nav class="pg-topic-panel" aria-label="In questa pagina">
                    <p class="pg-topic-title">In questa pagina</p>
                    <div class="pg-topic-links">
                        <a class="pg-topic-link" href="#rischi">
                            <span class="pg-topic-link-label">
                                <i class="bi bi-cloud-lightning-rain" aria-hidden="true"></i>
                                Cosa fare in caso di rischio
                            </span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a class="pg-topic-link" href="#prevenzione">
                            <span class="pg-topic-link-label">
                                <i class="bi bi-backpack2" aria-hidden="true"></i>
                                Preparati in anticipo
                            </span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </nav>

                <aside class="pg-emergency" aria-labelledby="emergency-title">
                    <p class="pg-emergency-title" id="emergency-title">
                        <span class="pg-emergency-badge" aria-hidden="true">
                            <i class="bi bi-telephone-fill"></i>
                        </span>
                        In caso di emergenza, chiama subito
                    </p>
                    <a class="pg-emergency-link" href="tel:112" aria-label="Chiama il 112, numero unico di emergenza">
                        <strong>112</strong>
                        <span class="pg-emergency-meta">
                            Numero unico di emergenza
                            <small>Gratuito · attivo 24 ore su 24</small>
                        </span>
                        <i class="bi bi-arrow-right-circle-fill pg-emergency-go" aria-hidden="true"></i>
                    </a>
                </aside>
            </div>
        </div>
    </header>

    <section id="rischi" class="pg-section" aria-labelledby="rischi-title">
        <div class="container">
            <div class="pg-section-head">
                <h2 id="rischi-title">Cosa fare, rischio per rischio</h2>
                <p>Scegli un rischio dall'elenco: trovi le indicazioni per prima, durante e dopo l'evento.</p>
            </div>
            <?php
            // Menu + contenuti dei rischi (vedi rischi.php)
            include('rischi.php');
            ?>
        </div>
    </section>

    <section id="prevenzione" class="pg-section pg-prep" aria-labelledby="prevenzione-title">
        <div class="container">
            <div class="pg-section-head">
                <p class="pg-section-kicker">Cultura della Protezione Civile</p>
                <h2 id="prevenzione-title">Preparati prima che serva</h2>
                <p>Un kit pronto e un piano condiviso in famiglia fanno la differenza nelle prime ore.</p>
            </div>

            <div class="pg-prep-grid">

                <div class="pg-card pg-card-kit">
                    <h3>Kit di emergenza</h3>
                    <p>Tre kit per tre situazioni diverse, con liste complete e consigli di manutenzione.</p>
                    <ul class="pg-kit-list">
                        <li><i class="bi bi-backpack2" aria-hidden="true"></i><span>Zaino per evacuare<small>Devi uscire in pochi minuti</small></span></li>
                        <li><i class="bi bi-house-door" aria-hidden="true"></i><span>Kit casa 72 ore<small>Resti in casa senza utenze</small></span></li>
                        <li><i class="bi bi-car-front" aria-hidden="true"></i><span>Kit automobile<small>Sei bloccato in strada</small></span></li>
                    </ul>
                    <button type="button" class="pg-card-btn" data-bs-toggle="modal" data-bs-target="#modalKit">
                        Scopri i kit <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="pg-card">
                    <h3>Piano familiare</h3>
                    <p>Punti di incontro, un contatto fuori zona e compiti assegnati: decidete insieme prima dell'emergenza.</p>
                    <button type="button" class="pg-card-btn" data-bs-toggle="modal" data-bs-target="#modalPiano">
                        Crea il tuo piano <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="pg-card">
                    <h3>Persone con necessità specifiche</h3>
                    <p>Anziani, bambini, persone con disabilità e animali domestici: come assisterli in emergenza.</p>
                    <button type="button" class="pg-card-btn" data-bs-toggle="modal" data-bs-target="#modalFragili">
                        Leggi la guida <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>

            </div>
        </div>
    </section>

    <?php
    // Finestre modali (kit, piano, necessità specifiche)
    include('prevenzione.php');
    ?>

</main>
<?php
// Footer del sito (chiude body ed html)
include('../includes/footer.php');
?>