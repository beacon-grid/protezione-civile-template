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

// Chiave di ordinamento senza accenti e senza maiuscole/minuscole
$sortKey = static function (string $text): string {
    if (class_exists('Normalizer')) {
        $decomposed = Normalizer::normalize($text, Normalizer::FORM_D);
        if (is_string($decomposed)) {
            $text = preg_replace('/\p{Mn}+/u', '', $decomposed) ?? $text;
        }
    }
    return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
};

usort($entries, static function (array $a, array $b) use ($sortKey): int {
    return strcmp($sortKey((string) $a['termine']), $sortKey((string) $b['termine']));
});

// Raggruppa per iniziale
$groups = [];
foreach ($entries as $entry) {
    $key = $sortKey(trim((string) $entry['termine']));
    $initial = function_exists('mb_substr') ? mb_substr($key, 0, 1, 'UTF-8') : substr($key, 0, 1);
    $initial = function_exists('mb_strtoupper') ? mb_strtoupper($initial, 'UTF-8') : strtoupper($initial);
    if (!preg_match('/^[A-Z]$/', $initial)) {
        $initial = '#';
    }
    $groups[$initial][] = $entry;
}
$letterId = static fn(string $letter): string => 'lettera-' . ($letter === '#' ? 'altro' : strtolower($letter));

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
        <div class="site-search__workspace">
            <div class="site-search__results-heading">
                <p class="mb-0 text-secondary">
                    <?= count($entries) === 1 ? 'Una definizione' : number_format(count($entries), 0, ',', '.') . ' definizioni' ?>
                </p>
                <?php if ($glossarySearchEnabled): ?>
                    <a class="site-search__reset" href="/cerca?tipo=glossary">Cerca nel glossario</a>
                <?php endif; ?>
            </div>

            <?php if ($groups): ?>
                <nav class="site-glossary-index" aria-label="Indice alfabetico del glossario">
                    <ul class="list-unstyled">
                        <?php foreach (array_keys($groups) as $letter): ?>
                            <li>
                                <a href="#<?= $letterId((string) $letter) ?>">
                                    <?= htmlspecialchars((string) $letter, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <?php foreach ($groups as $letter => $items): ?>
                    <section class="site-glossary-group" aria-labelledby="<?= $letterId((string) $letter) ?>">
                        <h2 class="site-glossary-letter" id="<?= $letterId((string) $letter) ?>">
                            <?= htmlspecialchars((string) $letter, ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                        <dl class="site-glossary-list">
                            <?php foreach ($items as $entry): ?>
                                <?php $term = (string) $entry['termine']; ?>
                                <div class="site-glossary-entry" id="<?= htmlspecialchars(site_search_anchor_id($term), ENT_QUOTES, 'UTF-8') ?>">
                                    <dt><?= htmlspecialchars($term, ENT_QUOTES, 'UTF-8') ?></dt>
                                    <dd><?= htmlspecialchars((string) $entry['definizione'], ENT_QUOTES, 'UTF-8') ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0">Nessuna definizione disponibile al momento.</p>
            <?php endif; ?>
        </div>

        <p class="site-search__notice mt-4">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            Se un termine non è presente, consulta le <a href="/rischi/">guide ai rischi</a><?php if ($searchSettings['enabled']): ?> o usa la <a href="/cerca">ricerca nel sito</a><?php endif; ?>.
        </p>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>