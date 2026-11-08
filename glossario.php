<?php

require_once __DIR__ . '/php/site_search.php';
require_once __DIR__ . '/php/config.php';
$config = app_config();
$site = $config['site'];

$meta_title = 'Glossario di Protezione Civile';
$meta_description = 'Consulta il glossario dei principali termini di Protezione Civile.';
$meta_url = $site['urls']['home'] . '/glossario';
include __DIR__ . '/includes/header.php';

$searchSettings = site_search_settings();
$enabled = !empty(app_glossary_load_settings()['enabled']);
$entries = $enabled ? app_glossary_load_entries() : [];
usort($entries, static function (array $a, array $b): int {
    return strcasecmp((string) $a['termine'], (string) $b['termine']);
});
$glossarySearchEnabled = $searchSettings['enabled'] && $searchSettings['include_glossary'];
?>
<link href="/css/site-search.css" rel="stylesheet">

<main class="site-search no-glossary container py-4 py-lg-5" id="main-content" tabindex="-1">
    <header class="site-search__hero mb-4 mb-lg-5">
        <div class="site-search__hero-content">
            <span class="site-search__eyebrow"><i class="bi bi-book" aria-hidden="true"></i> Parole della Protezione Civile</span>
            <h1 class="site-search__title">Glossario</h1>
            <p class="site-search__intro mb-0">Significato dei termini più utilizzati nella prevenzione e nella gestione delle emergenze.</p>
        </div>
        <div class="site-search__hero-icon" aria-hidden="true"><i class="bi bi-journal-text"></i></div>
    </header>

    <?php if (!$enabled): ?>
        <div class="site-search__empty" role="status">
            <h2 class="h5">Glossario temporaneamente non disponibile</h2>
            <p class="mb-0">Consulta le <a href="/rischi/">guide ai rischi</a><?php if ($searchSettings['enabled']): ?> oppure usa la <a href="/cerca">ricerca nel sito</a><?php endif; ?>.</p>
        </div>
    <?php else: ?>
        <div class="site-search__results-heading">
            <p class="mb-0 text-secondary">
                <?= count($entries) === 1 ? 'Una definizione' : number_format(count($entries), 0, ',', '.') . ' definizioni' ?>
            </p>
            <?php if ($glossarySearchEnabled): ?>
                <a class="site-search__reset" href="/cerca?tipo=glossary">Cerca nel glossario</a>
            <?php endif; ?>
        </div>

        <dl class="site-glossary-list">
            <?php foreach ($entries as $entry): ?>
                <?php $term = (string) $entry['termine']; ?>
                <div class="site-glossary-entry" id="<?= htmlspecialchars(site_search_anchor_id($term), ENT_QUOTES, 'UTF-8') ?>">
                    <dt><?= htmlspecialchars($term, ENT_QUOTES, 'UTF-8') ?></dt>
                    <dd><?= htmlspecialchars((string) $entry['definizione'], ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>

        <p class="site-search__notice mt-4">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            Se un termine non è presente, consulta le <a href="/rischi/">guide ai rischi</a><?php if ($searchSettings['enabled']): ?> o usa la <a href="/cerca">ricerca nel sito</a><?php endif; ?>.
        </p>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
