<?php

if (!defined('ADMIN_APP_INIT')) {
    http_response_code(403);
    exit;
}
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/database.php';
/**
 * news_helper_admin.php — Funzioni per leggere e salvare le news nel database
 */

/* ── Legge tutte le news ─────────────────────────────── */
function news_load(): array {
    return app_news_load();
}

/* ── Salva tutte le news in una singola scrittura atomica ─────────── */
function news_save(array $items): bool {
    return app_news_save($items);
}

/* ── Genera un ID univoco (collision-safe) ─────────────────────────────── */
function news_new_id(): string {
    return bin2hex(random_bytes(16));
}

/* ── Trova una singola news per ID ───────────────────── */
function news_find(string $id): ?array {
    return app_news_find($id);
}

/* ── Crea o aggiorna una news ─────────────────────────── */
function news_upsert(array $data): bool {
    return app_news_upsert($data);
}

/* ── Elimina una news per ID ─────────────────────────── */
function news_delete(string $id): bool {
    return app_news_delete($id);
}

/* ── Restituisce solo le news pubblicate, ordinate per data ── */
function news_published(int $limit = 0): array {
    $items = array_filter(news_load(), fn($n) => !empty($n['pubblicato']));
    usort($items, fn($a, $b) => strcmp($b['data'], $a['data']));
    return $limit > 0 ? array_slice($items, 0, $limit) : $items;
}

/* ── Etichette e colori categorie ────────────────────────────────────────
   'color' → nome Bootstrap (usato nel pannello admin)
   'hex'   → colore custom WCAG AA conforme (usato nei template pubblici)
   ─────────────────────────────────────────────────────────────────────── */
function news_categorie(): array {
    return [
        'esercitazione' => ['label' => 'Esercitazione', 'color' => 'primary',   'hex' => '#1a5ba8'],
        'prevenzione'   => ['label' => 'Prevenzione',   'color' => 'success',   'hex' => '#1e7e4a'],
        'formazione'    => ['label' => 'Formazione',    'color' => 'info',      'hex' => '#0e6b7a'],
        'allerta'       => ['label' => 'Allerta',       'color' => 'danger',    'hex' => '#b91c1c'],
        'evento'        => ['label' => 'Evento',        'color' => 'warning',   'hex' => '#92600a'],
        'comunicato'    => ['label' => 'Comunicato',    'color' => 'secondary', 'hex' => '#4a5568'],
    ];
}

function news_categoria_label(string $cat): string {
    return news_categorie()[$cat]['label'] ?? ucfirst($cat);
}

function news_categoria_color(string $cat): string {
    return news_categorie()[$cat]['color'] ?? 'secondary';
}

/* ── Restituisce il colore hex custom della categoria ── */
function news_categoria_hex(string $cat): string {
    return news_categorie()[$cat]['hex'] ?? '#4a5568';
}

function news_category_exists(string $category): bool {
    return array_key_exists($category, news_categorie());
}

function news_validate_image_path(string $path): ?string {
    $path = str_replace('\\', '/', trim($path));
    if ($path === '') {
        return '';
    }

    if (strpos($path, 'assets/img/news/') !== 0) {
        return null;
    }

    if (strpos($path, '..') !== false || strpos($path, '//') !== false) {
        return null;
    }

    if (preg_match('~[<>"\\\':?&]~', $path)) {
        return null;
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return null;
    }

    $fullPath = dirname(__DIR__) . '/' . $path;
    $real = realpath($fullPath);
    $baseDir = realpath(dirname(__DIR__) . '/assets/img/news');

    if ($real === false || $baseDir === false || strpos($real, $baseDir) !== 0) {
        error_log('news_validate_image_path: realpath check fallito — ' . $fullPath);
        return null;
    }

    if (!file_exists($real) || !is_file($real)) {
        error_log('news_validate_image_path: file non esiste — ' . $real);
        return null;
    }

    return $path;
}

function news_clean_html(string $content): string {
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', 'p,br,strong,b,em,i,ul,ol,li,a[href],h2,h3,h4,blockquote');
    $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
    $config->set('HTML.TargetBlank', true);
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($content);
}

function news_validate(array $data): array {
    $errors = [];

    if ($data['titolo'] === '') {
        $errors[] = 'Il titolo è obbligatorio.';
    } elseif (mb_strlen($data['titolo']) > 120) {
        $errors[] = 'Il titolo non può superare 120 caratteri.';
    }

    if ($data['sommario'] === '') {
        $errors[] = 'Il sommario è obbligatorio.';
    } elseif (mb_strlen($data['sommario']) > 180) {
        $errors[] = 'Il sommario non può superare 180 caratteri.';
    }

    if ($data['contenuto'] === '') {
        $errors[] = 'Il contenuto è obbligatorio.';
    }

    if (!news_category_exists($data['categoria'])) {
        $errors[] = 'Categoria non valida.';
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['data'])
        || !checkdate((int)substr($data['data'],5,2), (int)substr($data['data'],8,2), (int)substr($data['data'],0,4))
    ) {
        $errors[] = 'La data deve essere valida.';
    }

    if ($data['immagine'] !== '' && news_validate_image_path($data['immagine']) === null) {
        $errors[] = 'Percorso immagine non valido. Usa solo immagini in assets/img/news/.';
    }

    if ($data['immagine'] !== '' && trim($data['immagine_alt']) === '') {
        $errors[] = 'Il testo alternativo per l\'immagine è obbligatorio quando è presente un\'immagine.';
    } elseif (trim($data['immagine_alt']) !== '' && mb_strlen($data['immagine_alt']) > 140) {
        $errors[] = 'Il testo alternativo non può superare 140 caratteri.';
    }

    return $errors;
}

/* ── Sanifica input utente ──────────────────────────────
   Il testo viene sanificato in output nei template con
   htmlspecialchars(); doppio encoding causa &amp; &lt; ecc.
   ───────────────────────────────────────────────────── */
function news_sanitize(array $post): array {
    $categoria = trim($post['categoria'] ?? 'comunicato');
    if (!news_category_exists($categoria)) {
        $categoria = 'comunicato';
    }

    $data = trim($post['data'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || strtotime($data) === false) {
        $data = date('Y-m-d');
    }

    $imagePath = trim($post['immagine'] ?? '');
    if ($imagePath !== '' && news_validate_image_path($imagePath) === null) {
        $imagePath = '';
    }

    $imageAlt = trim(strip_tags($post['immagine_alt'] ?? ''));
    if ($imageAlt === '' && $imagePath !== '') {
        $imageAlt = trim($post['titolo'] ?? '');
    }

    return [
        'id'            => preg_replace('/[^a-zA-Z0-9_-]/', '', trim($post['id'] ?? '')),
        'titolo'        => trim($post['titolo'] ?? ''),
        'data'          => $data,
        'categoria'     => $categoria,
        'sommario'      => trim($post['sommario'] ?? ''),
        'contenuto'     => news_clean_html(trim($post['contenuto'] ?? '')),
        'immagine'      => $imagePath,
        'immagine_alt'  => $imageAlt,
        'in_evidenza'   => !empty($post['in_evidenza']),
        'pubblicato'    => !empty($post['pubblicato']),
    ];
}