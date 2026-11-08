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


/**
 * includes/news_section.php
 * Sostituisce il blocco news statico in homepage con layout asimmetrico e filtri
 */

require_once __DIR__ . '/news_public.php';

// Peschiamo un numero leggermente maggiore di news per gestire la separazione
$all_published = news_published(6);

$evidenza_items = array_filter($all_published, fn($n) => !empty($n['in_evidenza']));
$normal_items = array_filter($all_published, fn($n) => empty($n['in_evidenza']));

// Componiamo il set finale inserendo prima quella/e in evidenza (massimo 4 news totali a schermo)
$news_items = array_merge($evidenza_items, $normal_items);
$news_items = array_slice($news_items, 0, 4);

$categorie = news_categorie();
?>

<section id="news" aria-labelledby="news-title" class="py-5 bg-white">
    <div class="container">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h2 id="news-title" class="section-title mb-0">
                Ultime Notizie
            </h2>
            <div class="d-flex flex-wrap gap-2" role="group" aria-label="Filtra notizie per categoria">
                <?php foreach ($categorie as $key => $cat): ?>
                    <a href="news/?cat=<?= urlencode($key) ?>" 
                       class="filtro-cat-btn filtro-cat-btn-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                       <?= htmlspecialchars($cat['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($news_items)): ?>
            <p class="text-center text-muted py-4">
                Nessuna news disponibile al momento.
            </p>
        <?php else: ?>

        <div class="row g-4">

            <?php 
            $is_first = true;
            foreach ($news_items as $n): 
                $title = $n['titolo'] ?? '';
                $summary = $n['sommario'] ?? '';
                $excerpt = mb_substr($summary, 0, 120);
                $in_evidenza = !empty($n['in_evidenza']);

                $date = !empty($n['data']) ? date('d/m/Y', strtotime($n['data'])) : '';
                $imgAlt = $title . (!empty($summary) ? ' - ' . mb_substr($summary, 0, 80) : '');

                // Se la prima notizia è in evidenza, facciamo un layout asimmetrico grande
                if ($is_first && $in_evidenza): 
                    $is_first = false;
            ?>
                <div class="col-12">
                    <article class="card news-card border-primary shadow-sm h-100 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-lg-6">
                                <?php if (!empty($n['immagine'])): ?>
                                    <img src="<?= htmlspecialchars($n['immagine'], ENT_QUOTES, 'UTF-8') ?>"
                                         class="img-fluid h-100 w-100 object-fit-cover"
                                         style="min-height: 280px;"
                                         alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-light h-100 w-100"
                                         style="min-height: 280px;" role="img"
                                         aria-label="Immagine non disponibile - <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-newspaper text-muted" style="font-size:4rem;opacity:.3;" aria-hidden="true"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-6">
                                <div class="card-body d-flex flex-column h-100 p-4">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <small class="text-muted fw-semibold">
                                            <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>
                                        </small>
                                        <span class="cat-badge cat-badge-<?= htmlspecialchars($n['categoria'], ENT_QUOTES, 'UTF-8') ?> ms-auto">
                                            <?= news_categoria_label($n['categoria']) ?>
                                        </span>
                                        <span class="badge bg-warning text-dark px-2 py-1 fs-7 rounded d-flex align-items-center gap-1" title="In evidenza">
                                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                                            <span>In Evidenza</span>
                                        </span>
                                    </div>

                                    <h3 class="h3 mb-3">
                                        <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                                    </h3>

                                    <p class="text-muted flex-grow-1 mb-4">
                                        <?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <a href="news/detail.php?id=<?= urlencode($n['id']) ?>"
                                       class="btn btn-primary fw-semibold align-self-start px-4"
                                       aria-label="Leggi articolo principale: <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
                                        Leggi l'articolo <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php 
                else: 
                    // Layout per le notizie normali o successive (Card Verticali standard in griglia)
            ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card news-card h-100 shadow-sm border-0 bg-light">
                        <?php if (!empty($n['immagine'])): ?>
                            <img src="<?= htmlspecialchars($n['immagine'], ENT_QUOTES, 'UTF-8') ?>"
                                 class="card-img-top object-fit-cover"
                                 style="height:200px;"
                                 alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-secondary-subtle"
                                 style="height:200px;" role="img"
                                 aria-label="Immagine non disponibile - <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi bi-newspaper text-muted" style="font-size:2.5rem;opacity:.3;" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <small class="text-muted">
                                    <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>
                                </small>
                                <span class="cat-badge cat-badge-<?= htmlspecialchars($n['categoria'], ENT_QUOTES, 'UTF-8') ?> ms-auto">
                                    <?= news_categoria_label($n['categoria']) ?>
                                </span>
                                <?php if ($in_evidenza): ?>
                                    <span class="ms-1" title="In evidenza">
                                        <i class="bi bi-star-fill text-warning" aria-hidden="true"></i>
                                        <span class="visually-hidden">Notizia in evidenza</span>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="h5 card-title fw-bold text-dark mb-2">
                                <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <p class="text-muted small flex-grow-1 mb-3">
                                <?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?>...
                            </p>

                            <a href="news/detail.php?id=<?= urlencode($n['id']) ?>"
                               class="btn btn-sm btn-outline-primary fw-semibold mt-auto align-self-start"
                               aria-label="Leggi tutto: <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
                                Leggi tutto <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>

        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="news/" class="btn btn-outline-primary px-4 py-2 fw-semibold shadow-sm">
                    Vedi tutte le notizie <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <?php endif; ?>

    </div>
</section>

<script>
(function () {
    const css = `
    .object-fit-cover { object-fit: cover; }
    .fs-7 { font-size: 0.75rem; }
    .news-card { transition: transform .2s ease-in-out, box-shadow .2s ease-in-out; }
    .news-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }

    /* ── Colori categoria custom (WCAG AA) ── */
    .cat-badge {
        display: inline-block;
        padding: .25em .65em;
        font-size: .68rem;
        font-weight: 700;
        border-radius: .35em;
        color: #fff;
        letter-spacing: .02em;
    }
    .cat-badge-esercitazione { background-color: #1a5ba8; }
    .cat-badge-prevenzione   { background-color: #1e7e4a; }
    .cat-badge-formazione    { background-color: #0e6b7a; }
    .cat-badge-allerta       { background-color: #b91c1c; }
    .cat-badge-evento        { background-color: #92600a; }
    .cat-badge-comunicato    { background-color: #4a5568; }

    .filtro-cat-btn {
        font-size: .815rem;
        font-weight: 600;
        padding: .35rem .85rem;
        border-radius: 20px;
        border: 2px solid;
        background: #fff;
        cursor: pointer;
        transition: background .15s, color .15s;
        text-decoration: none;
        display: inline-block;
    }
    .filtro-cat-btn:focus-visible { outline: 3px solid #fbbf24; outline-offset: 2px; }

    .filtro-cat-btn-esercitazione { color: #1a5ba8; border-color: #1a5ba8; }
    .filtro-cat-btn-prevenzione   { color: #1e7e4a; border-color: #1e7e4a; }
    .filtro-cat-btn-formazione    { color: #0e6b7a; border-color: #0e6b7a; }
    .filtro-cat-btn-allerta       { color: #b91c1c; border-color: #b91c1c; }
    .filtro-cat-btn-evento        { color: #92600a; border-color: #92600a; }
    .filtro-cat-btn-comunicato    { color: #4a5568; border-color: #4a5568; }

    .filtro-cat-btn-esercitazione:hover { background: #1a5ba8; color: #fff; }
    .filtro-cat-btn-prevenzione:hover   { background: #1e7e4a; color: #fff; }
    .filtro-cat-btn-formazione:hover    { background: #0e6b7a; color: #fff; }
    .filtro-cat-btn-allerta:hover       { background: #b91c1c; color: #fff; }
    .filtro-cat-btn-evento:hover        { background: #92600a; color: #fff; }
    .filtro-cat-btn-comunicato:hover    { background: #4a5568; color: #fff; }
    `;
    const s = document.createElement('style');
    s.textContent = css;
    document.head.appendChild(s);
})();
</script>