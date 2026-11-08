<?php
/**
 * ATTENZIONE - PLACEHOLDER DA COMPILARE
 * ======================================
 * Questa pagina, così come distribuita nel template, NON riporta un audit
 * di accessibilità reale. Percentuali, date di verifica ed esempi di
 * non conformità sono segnaposto a scopo dimostrativo.
 *
 * Prima di pubblicare il sito:
 * 1. Esegui una verifica reale (vedi ACCESSIBILITY.md nel repository per
 *    gli strumenti consigliati: WAVE, Lighthouse, Axe, screen reader, ecc.)
 * 2. Sostituisci i valori contrassegnati con [DA COMPILARE] con i risultati
 *    effettivi della tua verifica.
 * 3. Se il sito è gestito da un ente pubblico, questa pagina NON sostituisce la Dichiarazione di accessibilità ufficiale prevista dalla normativa AGID.
*     La dichiarazione deve essere generata tramite il portale AGID: https://form.agid.gov.it/
*     Una volta generata, questa pagina deve essere sostituita con il relativo link alla dichiarazione ufficiale AGID, ad esempio:
*     https://form.agid.gov.it/a_b123/comune_di_esempio_-_sito_PC/dichiarazione.
 */
include('includes/header.php');
?>

<main class="container my-5" id="main-content" tabindex="-1">

    <div class="row text-center mb-5">
        <div class="col-12">
            <h1 class="display-5 fw-bold" style="color: var(--pc-blue);">
                <i class="bi bi-universal-access-circle me-2" aria-hidden="true"></i>Dichiarazione di Accessibilità
            </h1>
            <p class="lead text-muted">Impegno per l'accessibilità secondo WCAG 2.1 Livello AA e Legge Stanca 4/2004</p>
            <hr class="w-25 mx-auto" style="border-top: 3px solid var(--pc-yellow); opacity: 1;" aria-hidden="true">
        </div>
    </div>

    <div class="row g-4">

        <!-- Colonna principale -->
        <div class="col-lg-8">

            <!-- Impegno -->
            <section class="card mb-4 border-0 shadow-sm" aria-labelledby="sec-impegno">
                <div class="card-header bg-primary-subtle border-0">
                    <h2 class="card-title mb-0" id="sec-impegno">
                        <i class="bi bi-heart-fill text-danger me-2" aria-hidden="true"></i>Impegno per l'Accessibilità
                    </h2>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Il <strong><?= htmlspecialchars($site['nome_ente']) ?></strong> è impegnato a
                        rendere questo sito accessibile e usabile per tutte le persone, incluse quelle con disabilità.
                    </p>
                    <p class="card-text">
                        Il sito è stato sviluppato tenendo conto della <strong>Legge Stanca 4/2004</strong>
                        (Disposizioni per favorire l'accesso dei soggetti disabili agli strumenti informatici)
                        e delle <strong>Linee Guida AGID</strong> per i siti della Pubblica Amministrazione.
                    </p>
                    <p class="card-text text-muted small mb-0">
                        <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>
                        Ultimo aggiornamento di questa pagina: <time datetime="[DA COMPILARE]">[DA COMPILARE — data ultima modifica]</time>
                    </p>
                </div>
            </section>

            <!-- Stato di Conformità -->
            <section class="card mb-4 border-0 shadow-sm" aria-labelledby="sec-stato">
                <div class="card-header bg-warning-subtle border-0">
                    <h2 class="card-title mb-0" id="sec-stato">
                        <i class="bi bi-clipboard-check me-2" aria-hidden="true"></i>Stato di Conformità
                    </h2>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Questo sito segue le indicazioni delle Web Content Accessibility Guidelines (WCAG) 2.1
                        livello AA e della Legge 4/2004 (Legge Stanca), ma <strong>non ha ancora ricevuto una
                        verifica di conformità formale</strong>. Le informazioni riportate in questa pagina
                        andranno aggiornate dopo una verifica effettiva del sito pubblicato, comprensivo delle
                        eventuali personalizzazioni (loghi, contenuti, immagini) apportate dall'ente.
                    </p>

                    <div class="alert alert-warning small mb-0" role="note">
                        <strong>Nota per chi gestisce questo sito:</strong> sostituisci questo paragrafo con
                        l'esito reale della verifica di accessibilità, indicando se il sito è pienamente
                        conforme, parzialmente conforme o non conforme, e con quali limitazioni — così come
                        richiesto dal modello di dichiarazione AGID.
                    </div>
                </div>
            </section>

            <!-- Implementazioni corrette -->
            <section class="card mb-4 border-0 shadow-sm" aria-labelledby="sec-implementazioni">
                <div class="card-header border-0 text-white"
                     style="background: linear-gradient(135deg, var(--pc-blue), var(--pc-blue-mid));">
                    <h2 class="card-title mb-0" id="sec-implementazioni">
                        <i class="bi bi-check2-circle me-2" aria-hidden="true"></i>Caratteristiche di Accessibilità del Template
                    </h2>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Struttura semantica HTML</strong>
                                <p class="text-muted small mb-0">Gerarchia heading e uso di elementi <code>&lt;nav&gt;</code>, <code>&lt;main&gt;</code>, <code>&lt;footer&gt;</code>, <code>&lt;section&gt;</code> nel markup del template.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Navigazione da tastiera e skip link</strong>
                                <p class="text-muted small mb-0">Link "Salta al contenuto principale" visibile al focus; stili di focus definiti sugli elementi interattivi del template.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Palette colori pensata per il contrasto</strong>
                                <p class="text-muted small mb-0">I colori di default del template sono stati scelti puntando a un contrasto adeguato. Se personalizzi loghi o colori, verifica il contrasto con uno strumento come WAVE o il color contrast checker di WebAIM.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Testi alternativi</strong>
                                <p class="text-muted small mb-0">Attributo <code>alt</code> previsto sulle immagini del template; <code>aria-hidden="true"</code> sulle icone decorative. Le immagini caricate tramite pannello admin richiedono un <code>alt</code> descrittivo inserito manualmente da chi pubblica il contenuto.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>ARIA sui componenti dinamici</strong>
                                <p class="text-muted small mb-0"><code>aria-labelledby</code> sui modal; <code>aria-expanded</code>, <code>aria-controls</code> sugli accordion; <code>aria-live="polite"</code> sull'alert bar delle allerte meteo.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Form accessibili</strong>
                                <p class="text-muted small mb-0">Etichette <code>&lt;label for&gt;</code> associate agli input; filtri news in <code>&lt;nav aria-label&gt;</code> con <code>aria-current</code> sul filtro attivo.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Sanificazione del contenuto pubblicato</strong>
                                <p class="text-muted small mb-0">Il contenuto degli articoli inserito tramite l'editor viene filtrato con HTML Purifier per ridurre il rischio di markup malformato che comprometta la struttura della pagina.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Responsive design</strong>
                                <p class="text-muted small mb-0">Layout pensato per desktop, tablet e mobile, inclusa la navigazione adattiva su schermi stretti.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Non conformità note -->
            <section class="card mb-4 border-0 shadow-sm" aria-labelledby="sec-residue">
                <div class="card-header bg-light border-0">
                    <h2 class="card-title mb-0" id="sec-residue">
                        <i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>Limiti Noti
                    </h2>
                </div>
                <div class="card-body">
                    <p class="card-text small text-muted">
                        Esempi di limiti noti nel template base, da verificare e integrare con quelli
                        specifici del proprio sito dopo la personalizzazione:
                    </p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-circle text-warning mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Attributo <code>aria-invalid</code> sui campi form in errore</strong>
                                <p class="text-muted small mb-0">I messaggi di errore sono visibili, ma manca l'indicazione programmatica <code>aria-invalid="true"</code> sul campo coinvolto.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-circle text-warning mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>Qualità degli alt text sui contenuti caricati</strong>
                                <p class="text-muted small mb-0">La qualità descrittiva degli <code>alt</code> sulle immagini degli articoli dipende dai testi inseriti da chi amministra il sito, non dal template.</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex gap-3 py-3">
                            <i class="bi bi-circle text-warning mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <div>
                                <strong>[DA COMPILARE]</strong>
                                <p class="text-muted small mb-0">Aggiungi qui eventuali limiti reali riscontrati durante la tua verifica di accessibilità.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Segnalazioni -->
            <section class="card mb-4 border-0 shadow-sm" aria-labelledby="sec-segnala">
                <div class="card-header border-0 text-white"
                     style="background: linear-gradient(135deg, #c0392b, #922b21);">
                    <h2 class="card-title mb-0" id="sec-segnala">
                        <i class="bi bi-chat-dots me-2" aria-hidden="true"></i>Segnala un Problema
                    </h2>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        Se trovi <strong>barriere di accessibilità</strong> durante la navigazione,
                        puoi contattarci tramite i seguenti canali.
                    </p>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <p class="fw-semibold small text-primary mb-1">
                                <i class="bi bi-envelope me-1" aria-hidden="true"></i>Email
                            </p>
                            <a href="mailto:<?= htmlspecialchars($site['emails']['primary']) ?>"
                               class="btn btn-outline-primary btn-sm w-100">
                                <?= htmlspecialchars($site['emails']['primary']) ?>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <p class="fw-semibold small text-primary mb-1">
                                <i class="bi bi-telephone me-1" aria-hidden="true"></i>Telefono
                            </p>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/[^+0-9]/', '', $site['phones']['sede'])) ?>"
                               class="btn btn-outline-primary btn-sm w-100">
                                <?= htmlspecialchars($site['phones']['sede']) ?>
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 small" role="note">
                        <strong>Informazioni utili da includere nella segnalazione:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Descrizione del problema riscontrato</li>
                            <li>Pagina interessata (URL o titolo)</li>
                            <li>Dispositivo e browser utilizzati</li>
                            <li>Tecnologia assistiva in uso, se applicabile</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Risorse -->
            <section class="card mb-4 border-0 shadow-sm" aria-labelledby="sec-risorse">
                <div class="card-header bg-light border-0">
                    <h2 class="card-title mb-0" id="sec-risorse">
                        <i class="bi bi-link-45deg me-2" aria-hidden="true" style="color: var(--pc-blue);"></i>Risorse Utili
                    </h2>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item py-3">
                            <a href="https://www.agid.gov.it/it/design/accessibilita"
                               target="_blank" rel="noopener noreferrer">
                                <strong>AGID — Accessibilità e Inclusione Digitale</strong>
                                <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>
                                <span class="visually-hidden">(apre in una nuova finestra)</span>
                            </a>
                            <p class="text-muted small mb-0">Linee guida ufficiali per i siti della Pubblica Amministrazione italiana</p>
                        </li>
                        <li class="list-group-item py-3">
                            <a href="https://www.w3.org/WAI/WCAG21/quickref/"
                               target="_blank" rel="noopener noreferrer">
                                <strong>W3C WCAG 2.1 Quick Reference</strong>
                                <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>
                                <span class="visually-hidden">(apre in una nuova finestra)</span>
                            </a>
                            <p class="text-muted small mb-0">Guida rapida ai criteri di accessibilità — Standard internazionale</p>
                        </li>
                        <li class="list-group-item py-3">
                            <a href="https://www.nvaccess.org/"
                               target="_blank" rel="noopener noreferrer">
                                <strong>NVDA — Screen Reader Gratuito</strong>
                                <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>
                                <span class="visually-hidden">(apre in una nuova finestra)</span>
                            </a>
                            <p class="text-muted small mb-0">Software gratuito per testare l'accessibilità con screen reader</p>
                        </li>
                        <li class="list-group-item py-3">
                            <a href="https://form.agid.gov.it/"
                               target="_blank" rel="noopener noreferrer">
                                <strong>Modello ufficiale di Dichiarazione di Accessibilità AGID</strong>
                                <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>
                                <span class="visually-hidden">(apre in una nuova finestra)</span>
                            </a>
                            <p class="text-muted small mb-0">Da compilare separatamente se il sito è gestito da un ente pubblico</p>
                        </li>
                    </ul>
                </div>
            </section>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-lg-top" style="top: 80px;">
                <div class="card-header border-0"
                     style="background: var(--pc-yellow); color: var(--pc-blue);">
                    <h3 class="card-title mb-0 h6">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Status Verifica
                    </h3>
                </div>
                <div class="card-body">

                    <p class="fw-semibold small text-primary mb-1">WCAG 2.1 Livello AA</p>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-secondary">[DA COMPILARE]</span>
                        <span class="text-muted small">Verifica non ancora effettuata</span>
                    </div>

                    <p class="fw-semibold small text-primary mb-1">Legge Stanca 4/2004</p>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-secondary">[DA COMPILARE]</span>
                        <span class="text-muted small">Verifica non ancora effettuata</span>
                    </div>

                    <p class="fw-semibold small text-primary mb-1">Validazione HTML W3C</p>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-secondary">[DA COMPILARE]</span>
                        <span class="text-muted small">Esegui la validazione dopo la personalizzazione</span>
                    </div>

                    <hr>

                    <p class="fw-semibold small text-primary mb-2">Tecnologie assistive testate durante lo sviluppo del template</p>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="bi bi-dash-circle text-secondary me-1" aria-hidden="true"></i>
                            Screen reader (NVDA, JAWS, VoiceOver)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-dash-circle text-secondary me-1" aria-hidden="true"></i>
                            Navigazione da tastiera (TAB, INVIO, frecce)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-dash-circle text-secondary me-1" aria-hidden="true"></i>
                            Zoom browser fino al 200%
                        </li>
                        <li>
                            <i class="bi bi-dash-circle text-secondary me-1" aria-hidden="true"></i>
                            Modalità ad alto contrasto del sistema
                        </li>
                    </ul>
                    <p class="text-muted small mt-2 mb-0">
                        Aggiorna questa lista con l'esito reale dei test dopo la pubblicazione del tuo sito.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center">
                    <h3 class="card-title h6 mb-3">
                        <i class="bi bi-question-circle me-1" aria-hidden="true"></i>Hai domande?
                    </h3>
                    <a href="mailto:<?= htmlspecialchars($site['emails']['primary']) ?>"
                       class="btn btn-primary btn-sm w-100 mb-2">
                        <i class="bi bi-envelope me-1" aria-hidden="true"></i>Contattaci
                    </a>
                    <a href="/" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-house me-1" aria-hidden="true"></i>Torna alla home
                    </a>
                </div>
            </div>

        </div>

    </div>

</main>

<?php include('includes/footer.php'); ?>