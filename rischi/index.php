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

// Includiamo l'header del sito (che contiene già il menu e l'alert-bar verde)
include('../includes/header.php'); 
?>
<link href="../css/style.css" rel="stylesheet">
<script>
document.addEventListener("DOMContentLoaded", function() {
    const customStyle = document.createElement('style');
    customStyle.textContent = `
        /* Evita che il menu copra i titoli */
        .scroll-margin {
            scroll-margin-top: 130px;
        }
        /* Sezioni rischio */
        .risk-section {
            display: none;
            animation: fadeIn 0.25s ease;
        }
        .risk-section.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(customStyle);
});
</script>

<main id="main-content" tabindex="-1">

    <div class="py-5 bg-light border-bottom">
        <div class="container text-center py-3">
            <span class="text-uppercase fw-bold text-danger small tracking-wider">
                Cosa fare in caso di emergenza
            </span>

            <h1 class="display-5 fw-bold text-dark mt-1 mb-3">
                Sei Preparato? Guida ai Rischi
            </h1>

            <p class="text-secondary mx-auto mb-0" style="max-width: 700px;">
                La conoscenza di un rischio è il primo passo per proteggere se stessi e la propria famiglia.
                Leggi e tieni a portata di mano le buone pratiche di protezione civile.
            </p>
        </div>
    </div>

    <?php 
    // Includiamo i rischi (Assicurati che all'interno di rischi.php i titoli inizino con tag coerenti, es. h2 o h3)
    include('rischi.php'); 
    ?>

    <div class="py-5 bg-light border-top">
        <div class="container">
            <div class="text-center mb-5">
                <span class="text-uppercase fw-bold text-primary small tracking-wider">Cultura della Protezione Civile</span>
                <h2 class="fw-bold text-dark mt-1 h3">Strumenti Utili per la Famiglia</h2>
                <p class="text-secondary mx-auto" style="max-width: 600px;">
                    Oltre a conoscere i singoli rischi, prepara la tua casa e i tuoi cari seguendo queste linee guida fondamentali.
                </p>
            </div>

            <script>
            (function() {
                const css = `
                    .pc-prev-cards {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 16px;
                        padding: 1rem 0;
                    }
                    .pc-prev-card {
                        background: #ffffff;
                        border: 0.5px solid rgba(0,0,0,0.08);
                        border-radius: 12px;
                        padding: 1.5rem 1.25rem 1.25rem;
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                    }
                    .pc-prev-icon {
                        width: 48px;
                        height: 48px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                    }
                    .pc-prev-icon--kit     { background: #E6F1FB; color: #185FA5; }
                    .pc-prev-icon--piano   { background: #E1F5EE; color: #0F6E56; }
                    .pc-prev-icon--fragili { background: #FCEBEB; color: #A32D2D; }
                    .pc-prev-card h3 {
                        font-size: 1rem;
                        font-weight: 600;
                        margin: 0;
                        color: #1a1a1a;
                    }
                    .pc-prev-card p {
                        font-size: 0.875rem;
                        color: #6b7280;
                        margin: 0;
                        line-height: 1.55;
                        flex: 1;
                    }
                    .pc-prev-btn {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 0.875rem;
                        font-weight: 600;
                        padding: 7px 14px;
                        border-radius: 8px;
                        border: 1px solid;
                        cursor: pointer;
                        background: transparent;
                        transition: background 0.15s ease;
                        width: fit-content;
                        margin-top: 4px;
                        text-decoration: none;
                    }
                    .pc-prev-btn--kit     { color: #185FA5; border-color: #B5D4F4; }
                    .pc-prev-btn--kit:hover     { background: #E6F1FB; }
                    .pc-prev-btn--piano   { color: #0F6E56; border-color: #9FE1CB; }
                    .pc-prev-btn--piano:hover   { background: #E1F5EE; }
                    .pc-prev-btn--fragili { color: #A32D2D; border-color: #F7C1C1; }
                    .pc-prev-btn--fragili:hover { background: #FCEBEB; }
                `;
                const style = document.createElement('style');
                style.textContent = css;
                document.head.appendChild(style);
            })();
            </script>

            <div class="pc-prev-cards">

                <div class="pc-prev-card">
                    <div class="pc-prev-icon pc-prev-icon--kit" aria-hidden="true">
                        <i class="bi bi-backpack2 fs-4"></i>
                    </div>
                    <h3>Kit di emergenza</h3>
                    <p>Cosa tenere sempre pronto in casa o nello zaino per le prime 72 ore di un'emergenza.</p>
                    <button class="pc-prev-btn pc-prev-btn--kit" data-bs-toggle="modal" data-bs-target="#modalKit">
                        Scopri il kit <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="pc-prev-card">
                    <div class="pc-prev-icon pc-prev-icon--piano" aria-hidden="true">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <h3>Piano familiare</h3>
                    <p>Come organizzarsi in famiglia: punti di incontro, numeri utili e divisione dei compiti.</p>
                    <button class="pc-prev-btn pc-prev-btn--piano" data-bs-toggle="modal" data-bs-target="#modalPiano">
                        Crea il tuo piano <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="pc-prev-card">
                    <div class="pc-prev-icon pc-prev-icon--fragili" aria-hidden="true">
                        <i class="bi bi-heart-pulse fs-4"></i>
                    </div>
                    <h3>Necessità specifiche</h3>
                    <p>Guida per assistere anziani, bambini, persone con disabilità o animali domestici.</p>
                    <button class="pc-prev-btn pc-prev-btn--fragili" data-bs-toggle="modal" data-bs-target="#modalFragili">
                        Leggi la guida <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <?php 
    // Includiamo i popup modal
    include('prevenzione.php'); 
    ?>

</main> 
<?php 
// Includiamo il footer del sito (che chiude i tag body ed html)
include('../includes/footer.php'); 
?>