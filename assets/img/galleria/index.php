<?php
require_once __DIR__ . '/../../../php/config.php';
$config = app_config();
$site = $config['site'] ?? [];

$galleryDir = __DIR__;
$images = [];

if (is_dir($galleryDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($galleryDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $extension = strtolower(pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true)) {
                $filename = $fileInfo->getFilename();
                // Didascalia leggibile: il nome file (senza estensione) con
                // underscore trasformati in spazi, così "tramonto_al_mare.jpg"
                // diventa "tramonto al mare" invece di restare con i "_".
                $rawTitle = pathinfo($filename, PATHINFO_FILENAME);
                $title = trim(preg_replace('/\s+/', ' ', str_replace('_', ' ', $rawTitle)));
                $images[] = [
                    'src'   => '/assets/img/galleria/' . $filename,
                    'title' => $title !== '' ? $title : $rawTitle,
                    'mtime' => $fileInfo->getMTime(),
                ];
            }
        }
    }
}

usort($images, function ($a, $b) {
    return strcasecmp($a['title'], $b['title']);
});

// Dati immagini pronti per il lightbox (evita di rileggere il DOM in JS)
$imagesJson = json_encode($images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

include __DIR__ . '/../../../includes/header.php';
?>

<main id="main-content" class="container my-5" tabindex="-1">
    <div class="row text-center mb-5">
        <div class="col-12">
            <h1 class="display-4 fw-bold" style="color: var(--pc-blue);">Galleria fotografica</h1>
            <p class="lead text-secondary">Scopri le immagini raccolte nel nostro archivio.</p>
            <hr class="w-25 mx-auto" style="border-top: 3px solid var(--pc-yellow-text); opacity: 1;" aria-hidden="true">
        </div>
    </div>

    <?php if (!empty($images)): ?>
        <div class="gallery-toolbar" role="toolbar" aria-label="Strumenti galleria">
            <div class="gallery-search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input type="search" id="gallerySearch" class="gallery-search-input"
                       placeholder="Cerca per nome…" aria-label="Cerca immagini per nome">
            </div>

            <div class="gallery-toolbar-right">
                <label class="gallery-sort-label" for="gallerySort">Ordina per</label>
                <select id="gallerySort" class="gallery-sort-select" aria-label="Ordina immagini">
                    <option value="name-asc">Nome (A-Z)</option>
                    <option value="name-desc">Nome (Z-A)</option>
                    <option value="date-desc">Più recenti</option>
                    <option value="date-asc">Meno recenti</option>
                </select>

                <div class="gallery-view-switch" role="group" aria-label="Modalità di visualizzazione">
                    <button type="button" class="gallery-view-btn is-active" id="galleryViewGrid" aria-label="Vista a griglia" aria-pressed="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>
                    </button>
                    <button type="button" class="gallery-view-btn" id="galleryViewList" aria-label="Vista a elenco" aria-pressed="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="3.5" cy="6" r="1.3" fill="currentColor"/><circle cx="3.5" cy="12" r="1.3" fill="currentColor"/><circle cx="3.5" cy="18" r="1.3" fill="currentColor"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <p class="gallery-results-count" id="galleryResultsCount" role="status"></p>

        <div class="gallery-grid" id="galleryGrid">
            <?php foreach ($images as $index => $image): ?>
                <figure class="gallery-item" data-index="<?= $index ?>" data-title="<?= htmlspecialchars(mb_strtolower($image['title'])) ?>" tabindex="0"
                        aria-label="Apri immagine: <?= htmlspecialchars($image['title']) ?>">
                    <div class="gallery-item-inner">
                        <img src="<?= htmlspecialchars($image['src']) ?>"
                             alt="Immagine della galleria: <?= htmlspecialchars($image['title']) ?>"
                             loading="lazy"
                             decoding="async">
                        <div class="gallery-overlay">
                            <span class="gallery-overlay-title"><?= htmlspecialchars($image['title']) ?></span>
                            <span class="gallery-overlay-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <path d="M15.5 15.5L21 21M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10 7v6M7 10h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <figcaption class="gallery-item-caption"><?= htmlspecialchars($image['title']) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>

        <p class="gallery-no-results d-none" id="galleryNoResults">Nessuna immagine trovata per questa ricerca.</p>
    <?php else: ?>
        <div class="alert alert-info" role="status">
            Al momento non sono presenti immagini nella galleria.
        </div>
    <?php endif; ?>
</main>

<!-- Lightbox professionale -->
<div class="pc-lightbox" id="pcLightbox" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="pcLightboxTitle">
    <div class="pc-lightbox-backdrop" data-close></div>

    <button type="button" class="pc-lb-btn pc-lb-close" data-close aria-label="Chiudi galleria">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>

    <button type="button" class="pc-lb-btn pc-lb-prev" id="pcLbPrev" aria-label="Immagine precedente">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <button type="button" class="pc-lb-btn pc-lb-next" id="pcLbNext" aria-label="Immagine successiva">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <div class="pc-lightbox-stage" id="pcLbStage">
        <div class="pc-lightbox-spinner" id="pcLbSpinner" aria-hidden="true"></div>
        <div class="pc-lightbox-imgwrap" id="pcLbImgWrap">
            <img class="pc-lightbox-image" id="pcLbImage" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="" draggable="false">
        </div>

        <div class="pc-lightbox-toolbar" role="toolbar" aria-label="Strumenti immagine">
            <button type="button" class="pc-lb-mini-btn pc-lb-icon-btn" id="pcLbZoomOut" aria-label="Riduci zoom" title="Riduci zoom">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M21 21l-4.35-4.35M8 11h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
            <span class="pc-lb-zoom-level" id="pcLbZoomLevel">100%</span>
            <button type="button" class="pc-lb-mini-btn pc-lb-icon-btn" id="pcLbZoomIn" aria-label="Aumenta zoom" title="Aumenta zoom">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="M21 21l-4.35-4.35M11 8v6M8 11h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
            <span class="pc-lb-sep" aria-hidden="true"></span>
            <button type="button" class="pc-lb-mini-btn pc-lb-icon-btn" id="pcLbRotate" aria-label="Ruota immagine" title="Ruota immagine">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 4v5h5M20 20v-5h-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 15a8 8 0 1 0 1-9.5L4 9M18.5 9a8 8 0 0 0-13-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button type="button" class="pc-lb-mini-btn pc-lb-icon-btn" id="pcLbReset" aria-label="Ripristina visualizzazione" title="Adatta allo schermo (reset zoom e rotazione)">
                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
            </button>
            <span class="pc-lb-sep" aria-hidden="true"></span>
            <button type="button" class="pc-lb-mini-btn pc-lb-icon-btn" id="pcLbDownload" aria-label="Scarica immagine" title="Scarica immagine">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <button type="button" class="pc-lb-mini-btn pc-lb-icon-btn" id="pcLbFullscreen" aria-label="Apri immagine in una nuova scheda" title="Apri in una nuova scheda">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 4H4v5M15 4h5v5M4 15v5h5M20 15v5h-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>

    <div class="pc-lightbox-footer">
        <h2 class="pc-lightbox-title" id="pcLightboxTitle">Nessuna immagine selezionata</h2>
        <span class="pc-lightbox-counter" id="pcLbCounter">1 / 1</span>
    </div>
</div>

<link rel="stylesheet" href="/css/galleria.css">

<script>
window.galleryImages = <?= $imagesJson ?: '[]' ?>;
</script>

<script src="/js/galleria.js"></script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>