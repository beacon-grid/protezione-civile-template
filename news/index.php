<?php
require_once '../includes/news_public.php';
include('../includes/header.php');

// ── Filtri via GET ──────────────────────────────────────────────────────────
$anno_corrente = (int)date('Y');
$cat_filter    = trim($_GET['cat']  ?? '');
// Se ?anno non è passato, usa l'anno corrente come default
// Se ?anno=0 è passato esplicitamente, mostra tutti gli anni
$year_filter   = isset($_GET['anno']) ? (int)$_GET['anno'] : $anno_corrente;
$page          = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 12;

$all_news  = news_published();
$categorie = news_categorie();

// ── Anni disponibili (ricavati dalle news pubblicate) ───────────────────────
$anni_disponibili = array_unique(array_map(
    fn($n) => (int)date('Y', strtotime($n['data'])),
    $all_news
));
rsort($anni_disponibili); // dal più recente

// ── Applica filtro categoria ────────────────────────────────────────────────
if ($cat_filter && isset($categorie[$cat_filter])) {
    $filtered = array_values(array_filter($all_news, fn($n) => $n['categoria'] === $cat_filter));
} else {
    $filtered   = array_values($all_news);
    $cat_filter = '';
}

// ── Applica filtro anno ─────────────────────────────────────────────────────
if ($year_filter) {
    $filtered = array_values(array_filter(
        $filtered,
        fn($n) => (int)date('Y', strtotime($n['data'])) === $year_filter
    ));
}

// ── Paginazione ─────────────────────────────────────────────────────────────
$total       = count($filtered);
$total_pages = max(1, (int)ceil($total / $per_page));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per_page;
$page_news   = array_slice($filtered, $offset, $per_page);

$filtri_attivi = $cat_filter !== '' || $year_filter !== $anno_corrente;

// Helper: costruisce query string mantenendo i filtri attivi
// anno=0 viene passato esplicitamente per "tutti gli anni"
function build_url(array $params): string {
    $base = [
        'cat'  => $_GET['cat']  ?? '',
        'anno' => $_GET['anno'] ?? '',
    ];
    $merged = array_merge($base, $params);
    // Rimuovi valori vuoti tranne anno=0 che è significativo ("tutti gli anni")
    $clean = array_filter($merged, fn($v) => $v !== '');
    return './' . ($clean ? '?' . http_build_query($clean) : '');
}
?>

<script>
(function () {
    const css = `
    .filtri-card {
        background: #f4f7fb;
        border: 1px solid #d0dcea;
        border-radius: 12px;
        padding: 1.25rem 1.5rem 1rem;
        margin-bottom: 1.25rem;
    }
    .filtri-label {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--pc-blue-mid, #1a5ba8);
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .filtri-attivi-bar {
        background: #fff3cd;
        border: 1px solid #ffe08a;
        border-radius: 8px;
        padding: .65rem 1rem;
        font-size: .9rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }
    .filtri-attivi-bar .azzera-btn {
        margin-left: auto;
        font-size: .85rem;
        font-weight: 700;
        color: #7a4500;
        text-decoration: underline;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        white-space: nowrap;
    }
    .filtri-attivi-bar .azzera-btn:hover {
        color: #c0392b;
    }

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
        font-size: .875rem;
        font-weight: 600;
        padding: .4rem 1rem;
        border-radius: 6px;
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

    .filtro-cat-btn-esercitazione.attivo,
    .filtro-cat-btn-esercitazione:hover { background: #1a5ba8; color: #fff; }
    .filtro-cat-btn-prevenzione.attivo,
    .filtro-cat-btn-prevenzione:hover   { background: #1e7e4a; color: #fff; }
    .filtro-cat-btn-formazione.attivo,
    .filtro-cat-btn-formazione:hover    { background: #0e6b7a; color: #fff; }
    .filtro-cat-btn-allerta.attivo,
    .filtro-cat-btn-allerta:hover       { background: #b91c1c; color: #fff; }
    .filtro-cat-btn-evento.attivo,
    .filtro-cat-btn-evento:hover        { background: #92600a; color: #fff; }
    .filtro-cat-btn-comunicato.attivo,
    .filtro-cat-btn-comunicato:hover    { background: #4a5568; color: #fff; }
    `;
    const s = document.createElement('style');
    s.textContent = css;
    document.head.appendChild(s);
})();
</script>

<main class="container my-5" id="main-content" tabindex="-1">

    <div class="row text-center mb-5">
        <div class="col-12">
            <h1 class="display-4 fw-bold" style="color: var(--pc-blue);">Notizie &amp; Comunicati</h1>
            <p class="lead text-muted">Aggiornamenti, attività e comunicazioni del gruppo comunale.</p>
            <hr class="w-25 mx-auto" style="border-top: 3px solid var(--pc-yellow-text); opacity: 1;" aria-hidden="true">
        </div>
    </div>

    <!-- ── Filtri ───────────────────────────────────────────────────────── -->
    <?php if (!empty($anni_disponibili)): ?>
    <div class="filtri-card mb-4">
        <h2 class="h6 fw-bold text-center mb-4" style="color: var(--pc-blue);">
            <i class="bi bi-funnel" aria-hidden="true"></i>
            Filtra notizie
        </h2>

        <!-- Categoria -->
        <div class="mb-4">
            <p class="filtri-label">
                <i class="bi bi-tag" aria-hidden="true"></i>
                Mostra solo questa categoria:
            </p>
            <nav class="d-flex flex-wrap justify-content-center gap-2" aria-label="Filtra notizie per categoria">
                <a href="<?= build_url(['cat' => '', 'page' => '']) ?>"
                   class="btn btn-sm fw-semibold <?= $cat_filter === '' ? 'btn-primary' : 'btn-outline-secondary' ?>"
                   <?= $cat_filter === '' ? 'aria-current="page"' : '' ?>>
                    Tutte
                </a>
                <?php foreach ($categorie as $key => $cat): ?>
                    <a href="<?= build_url(['cat' => $key, 'page' => '']) ?>"
                       class="filtro-cat-btn filtro-cat-btn-<?= $key ?> <?= $cat_filter === $key ? 'attivo' : '' ?>"
                       <?= $cat_filter === $key ? 'aria-current="page"' : '' ?>>
                        <?= htmlspecialchars($cat['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <hr class="my-0 mb-3" style="border-color: #d0dcea;">

        <!-- Anno -->
        <div>
            <p class="filtri-label">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                Mostra solo questo anno:
            </p>
            <nav class="d-flex flex-wrap justify-content-center gap-2" aria-label="Filtra notizie per anno">
                <a href="<?= build_url(['anno' => '0', 'page' => '']) ?>"
                   class="btn btn-sm btn-outline-secondary fw-semibold <?= $year_filter === 0 ? 'active' : '' ?>"
                   <?= $year_filter === 0 ? 'aria-current="page"' : '' ?>>
                    Tutti gli anni
                </a>
                <?php foreach ($anni_disponibili as $anno): ?>
                    <a href="<?= build_url(['anno' => $anno, 'page' => '']) ?>"
                       class="btn btn-sm btn-outline-secondary fw-semibold <?= $year_filter === $anno ? 'active' : '' ?>"
                       <?= $year_filter === $anno ? 'aria-current="page"' : '' ?>>
                        <?= $anno ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

    </div>
    <?php endif; ?>

    <!-- ── Barra filtri attivi ──────────────────────────────────────────── -->
    <?php if ($filtri_attivi): ?>
    <div class="filtri-attivi-bar mb-4" role="status" aria-live="polite">
        <i class="bi bi-funnel-fill text-warning" aria-hidden="true"></i>
        <span>
            Stai vedendo:
            <?php if ($cat_filter): ?>
                <strong><?= htmlspecialchars($categorie[$cat_filter]['label']) ?></strong>
            <?php endif; ?>
            <?php if ($cat_filter && $year_filter): ?> del <?php endif; ?>
            <?php if ($year_filter): ?>
                <strong><?= $year_filter ?></strong>
            <?php endif; ?>
            — <?= $total ?> <?= $total === 1 ? 'articolo trovato' : 'articoli trovati' ?>
        </span>
        <a href="./" class="azzera-btn" aria-label="Rimuovi i filtri e torna alle notizie di <?= $anno_corrente ?>">
            ✕ Azzera filtri
        </a>
    </div>
    <?php else: ?>
    <!-- ── Conteggio risultati (senza filtri) ───────────────────────────── -->
    <?php if ($total > 0): ?>
    <p class="text-muted small text-center mb-4">
        <?= $total ?> <?= $total === 1 ? 'articolo trovato' : 'articoli trovati' ?>
        — pagina <?= $page ?> di <?= $total_pages ?>
    </p>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ── Griglia news ─────────────────────────────────────────────────── -->
    <?php if (empty($page_news)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-newspaper fs-1 d-block mb-3 opacity-25" aria-hidden="true"></i>
            <p class="mb-0">Nessuna news trovata<?= ($cat_filter || $year_filter) ? ' per i filtri selezionati' : '' ?>.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($page_news as $n): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card news-card h-100">
                    <?php if (!empty($n['immagine'])): ?>
                        <?php $cardImageAlt = !empty($n['immagine_alt']) ? $n['immagine_alt'] : ($n['titolo'] . ' — ' . substr($n['sommario'], 0, 60)); ?>
                        <img src="../<?= htmlspecialchars($n['immagine']) ?>"
                             class="card-img-top"
                             alt="<?= htmlspecialchars($cardImageAlt) ?>"
                             loading="lazy"
                             decoding="async"
                             width="800"
                             height="450">
                    <?php else: ?>
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                             style="height:200px;"
                             role="img"
                             aria-label="Immagine non disponibile — <?= htmlspecialchars($n['titolo']) ?>">
                            <i class="bi bi-newspaper text-muted" style="font-size:3rem;opacity:.3;" aria-hidden="true"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <small class="text-muted">
                                <time datetime="<?= date('Y-m-d', strtotime($n['data'])) ?>">
                                    <?= date('d/m/Y', strtotime($n['data'])) ?>
                                </time>
                            </small>

                            <!-- Badge con colore custom WCAG AA -->
                            <span class="cat-badge cat-badge-<?= htmlspecialchars($n['categoria']) ?> ms-auto">
                                <?= news_categoria_label($n['categoria']) ?>
                            </span>

                            <?php if (!empty($n['in_evidenza'])): ?>
                                <i class="bi bi-star-fill text-warning-on-light" aria-hidden="true"></i>
                                <span class="visually-hidden">Articolo in evidenza</span>
                            <?php endif; ?>
                        </div>

                        <h2 class="h5 mt-1 mb-2"><?= htmlspecialchars($n['titolo']) ?></h2>
                        <p class="text-muted small flex-grow-1 mb-3"><?= htmlspecialchars($n['sommario']) ?></p>

                        <a href="detail.php?id=<?= urlencode($n['id']) ?>"
                           class="btn btn-sm btn-outline-primary fw-semibold mt-auto align-self-start"
                           aria-label="Leggi articolo: <?= htmlspecialchars($n['titolo']) ?>">
                            Leggi tutto <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Paginazione ───────────────────────────────────────────────── -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center" aria-label="Navigazione pagine">
            <ul class="pagination">

                <!-- Precedente -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="<?= $page > 1 ? build_url(['page' => $page - 1]) : '#' ?>"
                       <?= $page <= 1 ? 'aria-disabled="true" tabindex="-1"' : 'aria-label="Pagina precedente"' ?>>
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        PRECEDENTE
                    </a>
                </li>

                <?php
                $range = 2;
                $start = max(1, $page - $range);
                $end   = min($total_pages, $page + $range);

                if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= build_url(['page' => 1]) ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="<?= build_url(['page' => $p]) ?>"
                           <?= $p === $page ? 'aria-current="page"' : '' ?>>
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= build_url(['page' => $total_pages]) ?>"><?= $total_pages ?></a>
                    </li>
                <?php endif; ?>

                <!-- Successiva -->
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="<?= $page < $total_pages ? build_url(['page' => $page + 1]) : '#' ?>"
                       <?= $page >= $total_pages ? 'aria-disabled="true" tabindex="-1"' : 'aria-label="Pagina successiva"' ?>>
                       SUCCESSIVA 
                       <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>

            </ul>
        </nav>
        <?php endif; ?>

    <?php endif; ?>

</main>

<?php include('../includes/footer.php'); ?>