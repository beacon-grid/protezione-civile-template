<?php

require_once __DIR__ . '/php/site_search.php';
require_once __DIR__ . '/php/config.php';
$config = app_config();
$site = $config['site'];

$query = trim((string) ($_GET['q'] ?? ''));
if (function_exists('mb_substr')) {
    $query = mb_substr($query, 0, 100, 'UTF-8');
} else {
    $query = substr($query, 0, 100);
}

$typeLabels = [
    'all' => 'Tutto il sito',
    'news' => 'Notizie',
    'guide' => 'Guide pratiche',
    'document' => 'Documenti PDF',
    'glossary' => 'Glossario (parole e significati)',
];
$typeIcons = [
    'news' => 'bi-newspaper',
    'guide' => 'bi-info-circle',
    'document' => 'bi-file-earmark-pdf',
    'glossary' => 'bi-book',
];

$meta_title = 'Cerca nel sito';
$meta_description = 'Cerca notizie, guide, documenti e definizioni nel sito della Protezione Civile.';
$meta_url = $site['urls']['home'] . '/cerca';
$meta_robots = 'noindex, follow';
$skip_glossary_enhancement = true;
include __DIR__ . '/includes/header.php';

$searchSettings = site_search_settings();
if (!$searchSettings['enabled']) {
    header('Location: /', true, 302);
    exit;
}

$allowedTypes = ['all'];
foreach ([
    'news' => 'include_news',
    'guide' => 'include_guides',
    'document' => 'include_documents',
] as $searchType => $settingKey) {
    if ($searchSettings[$settingKey]) {
        $allowedTypes[] = $searchType;
    }
}
if ($searchSettings['include_glossary'] && !empty(app_glossary_load_settings()['enabled'])) {
    $allowedTypes[] = 'glossary';
}
$type = (string) ($_GET['tipo'] ?? 'all');
if (!in_array($type, $allowedTypes, true)) {
    $type = 'all';
}

$categories = news_categorie();
$category = (string) ($_GET['categoria'] ?? '');
if (!$searchSettings['include_news'] || !isset($categories[$category]) || !in_array($type, ['all', 'news'], true)) {
    $category = '';
}
$year = (int) ($_GET['anno'] ?? 0);
if (!$searchSettings['include_news'] || $year < 0 || !in_array($type, ['all', 'news'], true)) {
    $year = 0;
}
if (($category !== '' || $year > 0) && $type === 'all' && $searchSettings['include_news']) {
    $type = 'news';
}
$canFilterNews = $searchSettings['include_news'] && in_array($type, ['all', 'news'], true);

$searchItems = site_search_items();
$typeLabels = array_intersect_key($typeLabels, array_flip($allowedTypes));
$availableYears = [];
foreach ($searchItems as $item) {
    if ($item['type'] !== 'news') {
        continue;
    }
    $availableNewsYear = (int) substr((string) ($item['date'] ?? ''), 0, 4);
    if ($availableNewsYear > 0) {
        $availableYears[$availableNewsYear] = $availableNewsYear;
    }
}
krsort($availableYears);
if ($year > 0 && !isset($availableYears[$year])) {
    $year = 0;
}

$search = site_search_execute($query, $type, $category, $year);
$results = $search['items'];
$total = $search['total'];
$queryTokens = preg_split('/\s+/u', site_search_normalize($query)) ?: [];
$queryHasSearchableToken = (bool) array_filter(
    $queryTokens,
    static fn(string $token): bool => (function_exists('mb_strlen')
        ? mb_strlen($token, 'UTF-8')
        : strlen($token)) >= 2
);
$queryTooShort = $query !== '' && !$queryHasSearchableToken;
?>
<link href="/css/site-search.css" rel="stylesheet">

<main class="site-search container py-4 py-lg-5" id="main-content" tabindex="-1">
    <header class="site-search__hero mb-4 mb-lg-5">
        <div class="site-search__hero-content">
            <span class="site-search__eyebrow"><i class="bi bi-compass" aria-hidden="true"></i> Ricerca nel sito</span>
            <h1 class="site-search__title">Cerca quello che ti serve</h1>
            <p class="site-search__intro mb-0">
                Scrivi una parola o un argomento. Cercheremo in tutte le pagine del sito.
            </p>
        </div>
        <div class="site-search__hero-icon" aria-hidden="true"><i class="bi bi-search"></i></div>
    </header>

    <section class="site-search__panel mb-4" aria-labelledby="search-form-title">
        <h2 class="visually-hidden" id="search-form-title">Modulo di ricerca</h2>
        <form action="/cerca" method="get" role="search">
            <label class="form-label fw-semibold" for="search-query">Che cosa vuoi trovare?</label>
            <div class="site-search__input-row">
                <div class="site-search__input-wrap">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        class="form-control form-control-lg"
                        type="search"
                        id="search-query"
                        name="q"
                        value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="100"
                        aria-describedby="search-help"
                        placeholder="Per esempio: alluvione">
                </div>
                <button class="btn btn-primary btn-lg site-search__submit" type="submit">
                    Cerca <span class="visually-hidden">nel sito</span>
                    <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                </button>
            </div>
            <p class="form-text mt-2 mb-0" id="search-help">
                Puoi scrivere una o più parole. Prova, per esempio:
                <a href="/cerca?q=alluvione">alluvione</a>,
                <a href="/cerca?q=terremoto">terremoto</a> o
                <a href="/cerca?q=piano+emergenza">piano di emergenza</a>.
            </p>

            <div class="site-search__filters-wrap mt-4">
                <details <?= $category !== '' || $year > 0 || $type !== 'all' ? 'open' : '' ?>>
                    <summary>Filtri facoltativi <span>— puoi ignorarli</span></summary>
                    <p class="site-search__filter-help">Categoria e anno servono solo per trovare una notizia precisa.</p>
                    <div class="site-search__filters">
                        <div>
                            <label class="form-label fw-semibold" for="search-type">Dove cercare</label>
                            <select class="form-select" id="search-type" name="tipo">
                                <?php foreach ($typeLabels as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= $type === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="search-category">Categoria della notizia</label>
                            <select class="form-select" id="search-category" name="categoria" <?= $canFilterNews ? '' : 'disabled' ?>>
                                <option value="">Tutte le categorie</option>
                                <?php foreach ($categories as $key => $data): ?>
                                    <option value="<?= htmlspecialchars($key) ?>" <?= $category === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($data['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" for="search-year">Anno della notizia</label>
                            <select class="form-select" id="search-year" name="anno" <?= $canFilterNews ? '' : 'disabled' ?>>
                                <option value="0">Tutti gli anni</option>
                                <?php foreach ($availableYears as $availableYear): ?>
                                    <option value="<?= $availableYear ?>" <?= $year === $availableYear ? 'selected' : '' ?>>
                                        <?= $availableYear ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary site-search__filter-submit mt-3" type="submit">
                        Applica i filtri
                    </button>
                </details>
            </div>
        </form>
    </section>

    <?php if ($query === ''): ?>
        <section class="site-search__start" aria-labelledby="search-start-title">
            <h2 class="h4 mb-3" id="search-start-title">Oppure vai direttamente a</h2>
            <p class="text-secondary">Scegli una pagina senza fare una ricerca.</p>
            <div class="site-search__quick-links">
                <a href="/allerte/" class="site-search__quick-link">
                    <i class="bi bi-cloud-lightning-rain" aria-hidden="true"></i><span>Allerte meteo</span>
                </a>
                <a href="/rischi/" class="site-search__quick-link">
                    <i class="bi bi-shield-exclamation" aria-hidden="true"></i><span>Cosa fare in caso di rischio</span>
                </a>
                <a href="/news/" class="site-search__quick-link">
                    <i class="bi bi-newspaper" aria-hidden="true"></i><span>Notizie</span>
                </a>
                <a href="/glossario" class="site-search__quick-link">
                    <i class="bi bi-book" aria-hidden="true"></i><span>Significato delle parole</span>
                </a>
            </div>
        </section>
    <?php else: ?>
        <section aria-labelledby="search-results-title">
            <div class="site-search__results-heading">
                <div>
                    <h2 class="h4 mb-1" id="search-results-title">Risultati della ricerca</h2>
                    <p class="mb-0 text-secondary" role="status" aria-live="polite" aria-atomic="true">
                        <?php if ($queryTooShort): ?>
                            Scrivi almeno due lettere o numeri per cercare.
                        <?php elseif ($total === 0): ?>
                            Non abbiamo trovato risultati per <strong>“<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>”</strong>.
                        <?php else: ?>
                            <?= $total === 1 ? 'Abbiamo trovato un risultato' : 'Abbiamo trovato ' . number_format($total, 0, ',', '.') . ' risultati' ?>
                            per <strong>“<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>”</strong>.
                        <?php endif; ?>
                    </p>
                </div>
                <a class="site-search__reset" href="/cerca">Fai una nuova ricerca</a>
            </div>

            <?php if ($queryTooShort): ?>
                <div class="site-search__empty" role="note">
                    <h3 class="h5">La parola è troppo corta</h3>
                    <p class="mb-0">Aggiungi qualche lettera e prova di nuovo.</p>
                </div>
            <?php elseif ($results): ?>
                <ol class="site-search__results list-unstyled">
                    <?php foreach ($results as $result): ?>
                        <li>
                            <article class="site-search__result">
                                <div class="site-search__result-meta">
                                    <span class="site-search__type">
                                        <i class="bi <?= htmlspecialchars($typeIcons[$result['type']] ?? 'bi-file-text') ?>" aria-hidden="true"></i>
                                        <?= htmlspecialchars($typeLabels[$result['type']] ?? 'Contenuto') ?>
                                    </span>
                                    <?php if (!empty($result['category']) && isset($categories[$result['category']])): ?>
                                        <span class="site-search__category"><?= htmlspecialchars($categories[$result['category']]['label']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($result['date'])): ?>
                                        <time datetime="<?= htmlspecialchars($result['date']) ?>"><?= htmlspecialchars(date('d/m/Y', strtotime($result['date']))) ?></time>
                                    <?php endif; ?>
                                </div>
                                <h3 class="site-search__result-title">
                                    <a href="<?= htmlspecialchars($result['url'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($result['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h3>
                                <p class="site-search__result-description mb-0">
                                    <?= htmlspecialchars($result['description'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <?php if ($total > count($results)): ?>
                    <p class="site-search__limit-note" role="note">
                        Mostriamo i primi <?= count($results) ?> risultati. Aggiungi una parola o scegli un filtro per restringere la ricerca.
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <div class="site-search__empty" role="status">
                    <span class="site-search__empty-icon" aria-hidden="true"><i class="bi bi-search"></i></span>
                    <h3 class="h5">Prova con parole diverse</h3>
                    <p class="mb-3">Controlla come hai scritto la parola oppure usa un termine più semplice, per esempio “alluvione” o “terremoto”.</p>
                    <a class="btn btn-outline-primary" href="/cerca">Cancella la ricerca</a>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <aside class="site-search__notice mt-5" aria-label="Informazioni sulla ricerca">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <p class="mb-0">
            Cerchiamo nelle notizie pubblicate, nelle guide, nei documenti e nel glossario. Nei PDF cerchiamo il titolo, non il testo delle pagine.
        </p>
    </aside>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
