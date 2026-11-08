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

include('../includes/header.php'); 
?>

<main id="main-content" tabindex="-1">

    <section id="hub-giochi"
             class="py-5 bg-light"
             aria-labelledby="hub-title">

        <div class="row justify-content-center text-center">
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm p-5">

                    <div class="mb-4">
                        <i class="bi bi-tools text-warning"
                           style="font-size: 5rem;"
                           aria-hidden="true"></i>
                    </div>

                    <h1 id="hub-title"
                        class="fw-bold mb-3"
                        style="color: var(--pc-blue, #005baa);">
                        Area in Fase di Realizzazione
                    </h1>

                    <h2 class="lead text-muted h3">
                        Stiamo lavorando per rendere disponibile l'Area Ludico-Didattica del Gruppo Comunale di Protezione Civile.
                    </h2>

                    <h2 class="text-muted h6 mt-3">
                        A breve saranno disponibili giochi interattivi, attività educative e materiali didattici dedicati alla cultura della prevenzione e della sicurezza.
                    </h2>

                    <div class="alert alert-warning mt-4 mb-0"
                         role="status">
                        <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>
                        Contenuti attualmente non disponibili. Torna a visitarci prossimamente.
                    </div>

                </div>

            </div>
        </div>

    </section>

</main>

<?php include('../includes/footer.php'); ?>