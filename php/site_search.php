<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/news_public.php';

function site_search_normalize(string $value): string
{
    $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = function_exists('mb_strtolower')
        ? mb_strtolower($value, 'UTF-8')
        : strtolower($value);

    if (function_exists('iconv')) {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii !== false) {
            $value = strtolower($ascii);
        }
    }

    $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
    return trim($normalized ?? $value);
}

function site_search_anchor_id(string $term): string
{
    return 'term-' . substr(hash('sha256', site_search_normalize($term)), 0, 16);
}

function site_search_settings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $defaults = [
        'enabled' => true,
        'include_news' => true,
        'include_guides' => true,
        'include_documents' => true,
        'include_glossary' => true,
    ];
    $saved = app_data_get('site_search_settings', []);
    $settings = $defaults;
    if (is_array($saved)) {
        foreach ($defaults as $key => $default) {
            if (array_key_exists($key, $saved)) {
                $settings[$key] = (bool) $saved[$key];
            }
        }
    }

    return $settings;
}

function site_search_save_settings(array $settings): bool
{
    $saved = [];
    foreach (['enabled', 'include_news', 'include_guides', 'include_documents', 'include_glossary'] as $key) {
        $saved[$key] = !empty($settings[$key]);
    }

    return app_data_set('site_search_settings', $saved);
}

function site_search_catalog(): array
{
    $catalog = [
        [
            'type' => 'guide',
            'title' => 'Guida ai rischi e ai comportamenti corretti',
            'description' => 'Indicazioni su cosa fare prima, durante e dopo i principali rischi naturali e antropici.',
            'content' => 'meteo temporali fulmini neve gelo terremoto alluvione allagamento incendio boschivo vulcano frana valanga siccita crisi idrica ciclone vento maremoto tsunami rischio nucleare radiologico industriale blackout',
            'url' => '/rischi/',
        ],
        [
            'type' => 'guide',
            'title' => 'Terremoto',
            'description' => 'Comportamenti da adottare in casa e all’aperto in caso di sisma.',
            'content' => 'scossa sismica edificio sicurezza proteggersi evacuazione',
            'url' => '/rischi/#terremoto',
        ],
        [
            'type' => 'guide',
            'title' => 'Alluvione e allagamenti',
            'description' => 'Come comportarsi in caso di pioggia intensa, allagamento o rischio idrogeologico.',
            'content' => 'acqua piena sottopasso fiume torrente esondazione idrogeologico',
            'url' => '/rischi/#alluvione',
        ],
        [
            'type' => 'guide',
            'title' => 'Incendio boschivo',
            'description' => 'Indicazioni di prevenzione e sicurezza in caso di incendio.',
            'content' => 'fuoco fumo vegetazione bosco roghi evacuazione',
            'url' => '/rischi/#incendio',
        ],
        [
            'type' => 'guide',
            'title' => 'Temporali, fulmini e vento forte',
            'description' => 'Precauzioni in caso di temporali, fulmini, vento intenso e fenomeni meteo avversi.',
            'content' => 'meteo pioggia grandine ciclone tromba aria',
            'url' => '/rischi/#meteo',
        ],
        [
            'type' => 'guide',
            'title' => 'Preparare un piano familiare di emergenza',
            'description' => 'Compila e stampa un piano personalizzato per la tua famiglia. I dati restano sul tuo dispositivo.',
            'content' => 'contatti punto incontro farmaci documenti animali disabilita bambini anziani',
            'url' => '/rischi/scheda-famiglia.php',
        ],
        [
            'type' => 'guide',
            'title' => 'Prepararsi alle emergenze e ai kit di sicurezza',
            'description' => 'Consigli per preparare casa e famiglia, organizzare i kit e affrontare un’evacuazione.',
            'content' => 'zaino kit emergenza evacuazione acqua documenti prevenzione preparazione casa',
            'url' => '/rischi/',
        ],
        [
            'type' => 'guide',
            'title' => 'Mappa di sicurezza',
            'description' => 'Consulta la mappa con i punti e i riferimenti utili sul territorio.',
            'content' => 'mappa sede area territorio punto raccolta sicurezza',
            'url' => '/mappa.php',
        ],
        [
            'type' => 'guide',
            'title' => 'Bollettino allerte meteo',
            'description' => 'Consulta lo stato delle allerte e le informazioni meteo per il territorio.',
            'content' => 'allerta gialla arancione rossa verde criticita bollettino previsioni meteo',
            'url' => '/allerte/',
        ],
        [
            'type' => 'guide',
            'title' => 'Contatti e numeri utili',
            'description' => 'Trova i numeri per le emergenze e i contatti del gruppo comunale.',
            'content' => '112 emergenza telefono contatti vigili fuoco polizia soccorso',
            'url' => '/#numeri-utili',
        ],
    ];

    $documents = [
        [
            'title' => 'Piano di Emergenza Comunale',
            'description' => 'Documento PDF con il piano comunale per la gestione delle emergenze.',
            'content' => 'piano protezione civile territorio scenari procedure comunale',
            'path' => 'assets/pdf/piano_emergenza_comunale.pdf',
        ],
        [
            'title' => 'Regolamento comunale di Protezione Civile',
            'description' => 'Regolamento del Gruppo Comunale di Protezione Civile in formato PDF.',
            'content' => 'regolamento volontari organizzazione comune',
            'path' => 'assets/pdf/regolamento_protezione_civile.pdf',
        ],
    ];

    foreach ($documents as $document) {
        if (is_file(dirname(__DIR__) . '/' . $document['path'])) {
            $catalog[] = [
                'type' => 'document',
                'title' => $document['title'],
                'description' => $document['description'],
                'content' => $document['content'],
                'url' => '/' . $document['path'],
            ];
        }
    }

    return $catalog;
}

function site_search_items(): array
{
    static $items = null;
    if ($items !== null) {
        return $items;
    }

    $settings = site_search_settings();
    if (!$settings['enabled']) {
        return $items = [];
    }

    $items = [];
    if ($settings['include_guides'] || $settings['include_documents']) {
        foreach (site_search_catalog() as $item) {
            if (($item['type'] === 'guide' && $settings['include_guides'])
                || ($item['type'] === 'document' && $settings['include_documents'])) {
                $items[] = $item;
            }
        }
    }

    if ($settings['include_news']) {
        foreach (news_published() as $news) {
            $items[] = [
                'type' => 'news',
                'title' => (string) ($news['titolo'] ?? ''),
                'description' => (string) ($news['sommario'] ?? ''),
                'content' => (string) ($news['contenuto'] ?? ''),
                'url' => '/news/detail.php?id=' . rawurlencode((string) ($news['id'] ?? '')),
                'category' => (string) ($news['categoria'] ?? ''),
                'date' => (string) ($news['data'] ?? ''),
            ];
        }
    }

    if ($settings['include_glossary'] && !empty(app_glossary_load_settings()['enabled'])) {
        foreach (app_glossary_load_entries() as $entry) {
            $term = (string) ($entry['termine'] ?? '');
            if ($term === '') {
                continue;
            }
            $items[] = [
                'type' => 'glossary',
                'title' => $term,
                'description' => (string) ($entry['definizione'] ?? ''),
                'content' => (string) ($entry['definizione'] ?? ''),
                'url' => '/glossario.php#' . site_search_anchor_id($term),
            ];
        }
    }

    return $items;
}

function site_search_execute(string $query, string $type = 'all', string $category = '', int $year = 0): array
{
    $normalizedQuery = site_search_normalize($query);
    if ($normalizedQuery === '') {
        return ['items' => [], 'total' => 0];
    }

    $tokens = array_values(array_unique(array_filter(
        preg_split('/\s+/u', $normalizedQuery) ?: [],
        static fn(string $token): bool => (function_exists('mb_strlen')
            ? mb_strlen($token, 'UTF-8')
            : strlen($token)) >= 2
    )));
    if ($tokens === []) {
        return ['items' => [], 'total' => 0];
    }

    $matches = [];
    foreach (site_search_items() as $item) {
        if ($type !== 'all' && $item['type'] !== $type) {
            continue;
        }
        if ($category !== '' && $item['type'] === 'news' && ($item['category'] ?? '') !== $category) {
            continue;
        }
        if ($year > 0 && $item['type'] === 'news' && (int) substr((string) ($item['date'] ?? ''), 0, 4) !== $year) {
            continue;
        }
        if ($category !== '' && $item['type'] !== 'news') {
            continue;
        }
        if ($year > 0 && $item['type'] !== 'news') {
            continue;
        }

        $title = site_search_normalize((string) $item['title']);
        $description = site_search_normalize((string) $item['description']);
        $content = site_search_normalize((string) $item['content']);
        $searchable = $title . ' ' . $description . ' ' . $content;
        $score = 0;
        $allTokensFound = true;

        foreach ($tokens as $token) {
            if (strpos($searchable, $token) === false) {
                $allTokensFound = false;
                break;
            }
            $score += strpos($title, $token) !== false ? 8 : 0;
            $score += strpos($description, $token) !== false ? 4 : 0;
            $score += strpos($content, $token) !== false ? 1 : 0;
        }

        if (!$allTokensFound) {
            continue;
        }
        if (strpos($title, $normalizedQuery) !== false) {
            $score += 6;
        } elseif (strpos($searchable, $normalizedQuery) !== false) {
            $score += 2;
        }

        $item['score'] = $score;
        $matches[] = $item;
    }

    usort($matches, static function (array $a, array $b): int {
        return ($b['score'] <=> $a['score'])
            ?: strcasecmp((string) $a['title'], (string) $b['title']);
    });

    $total = count($matches);
    return ['items' => array_slice($matches, 0, 50), 'total' => $total];
}
