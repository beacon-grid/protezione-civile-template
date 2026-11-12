<?php
declare(strict_types=1);

/**
 * Esportazione PDF del fascicolo "Guida ai rischi" (Protezione Civile).
 *
 * GET ?sezioni=id1,id2,...  -> esporta solo le schede indicate
 * GET (senza parametro)     -> esporta tutte le schede
 *
 * Requisiti: PHP 8.0+, mPDF (Composer), estensioni dom e gd.
 */

const CACHE_TTL         = 7 * 86400;
const CACHE_MAX_FILES   = 20;
const CACHE_VERSION     = 'risk-booklet-v7';
const MAX_PARAM_LENGTH  = 500;
const MAX_SECTIONS      = 30;
const LOGO_MAX_PX       = 220;
const DEFAULT_BRAND     = '#003B7A';
const ACCENT            = '#ffc200';
const PDF_FONT          = 'freesans'; // incluso in mPDF

/**
 * Glifi delle icone (griglia 24x24, tratto bianco). Vengono inseriti
 * in un quadrato arrotondato colorato da iconImg().
 */
const ICON_GLYPHS = [
    'meteo'        => '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9z"/><path d="M12.5 11l-2.5 4h4l-2.5 4"/>',
    'neve'         => '<path d="M12 2v20M3.3 7l17.4 10M3.3 17L20.7 7"/><path d="M9.5 3.5L12 6l2.5-2.5M9.5 20.5L12 18l2.5 2.5"/>',
    'terremoto'    => '<path d="M2 12h4l2.5-7 4 14 3-9 1.5 2H22"/>',
    'alluvione'    => '<path d="M2 8c2-2 3-2 5 0s3 2 5 0 3-2 5 0 3 2 5 0"/><path d="M2 13c2-2 3-2 5 0s3 2 5 0 3-2 5 0 3 2 5 0"/><path d="M2 18c2-2 3-2 5 0s3 2 5 0 3-2 5 0 3 2 5 0"/>',
    'incendio'     => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.4-.5-2-1-3-1-2.1-.2-4 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.2.4-2.3 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
    'vulcanico'    => '<path d="M3 21l6-11h6l6 11z"/><path d="M12 2.5V6M7.5 4.5L9 7M16.5 4.5L15 7"/>',
    'frana'        => '<path d="M3 7l18 11"/><circle cx="8.5" cy="12" r="2"/><circle cx="14" cy="15" r="1.6"/><circle cx="14.5" cy="8.5" r="1.4"/>',
    'valanga'      => '<path d="M2 20l6.5-12 3.5 6 2-3L22 20z"/><path d="M8.5 8l-1.5 3"/>',
    'crisi-idrica' => '<path d="M12 3s6.5 6.5 6.5 11a6.5 6.5 0 0 1-13 0C5.5 9.5 12 3 12 3z"/><path d="M8.5 15a3.5 3.5 0 0 0 3.5 3.5"/>',
    'ciclone'      => '<path d="M3 8h11a3 3 0 1 0-3-3"/><path d="M3 12h16a3 3 0 1 1-3 3"/><path d="M3 16h8a2.5 2.5 0 1 1-2.5 2.5"/>',
    'maremoto'     => '<path d="M2 19c2-2 3-2 5 0s3 2 5 0 3-2 5 0 3 2 5 0"/><path d="M3 14c1-6 6-10 12-9-3 1-3 4 0 5 2 1 4 1 6 .5"/>',
    'nucleare'     => '<circle cx="12" cy="12" r="1.7" fill="#ffffff" stroke="none"/>'
        . '<path fill="#ffffff" stroke="none" d="M7.5 4.2A9 9 0 0 1 16.5 4.2L13.75 9A3.5 3.5 0 0 0 10.25 9Z"/>'
        . '<path fill="#ffffff" stroke="none" transform="rotate(120 12 12)" d="M7.5 4.2A9 9 0 0 1 16.5 4.2L13.75 9A3.5 3.5 0 0 0 10.25 9Z"/>'
        . '<path fill="#ffffff" stroke="none" transform="rotate(240 12 12)" d="M7.5 4.2A9 9 0 0 1 16.5 4.2L13.75 9A3.5 3.5 0 0 0 10.25 9Z"/>',
    'industriale'  => '<path d="M3 21V10l6 4v-4l6 4V5h4v16z"/><path d="M7 18h2M12 18h2"/>',
    'blackout'     => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/><path d="M3 3l18 18"/>',
    // Icone della pagina guida
    'g-plan'       => '<path d="M9 3h6v3H9z"/><path d="M7 5H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-1"/><path d="M9 12h6M9 16h6"/>',
    'g-kit'        => '<path d="M3 8h18v12H3z"/><path d="M9 8V5h6v3"/><path d="M12 11v6M9 14h6"/>',
    'g-map'        => '<path d="M12 21s7-6 7-12a7 7 0 0 0-14 0c0 6 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/>',
    'g-check'      => '<path d="M4 12.5l5.5 5.5L20 6.5"/>',
    'g-alert'      => '<path d="M12 3L2 20h20z"/><path d="M12 10v5M12 17.5v.5"/>',
    'g-phone'      => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
];

/* -------------------------------------------------------------------------
 * Helper
 * ---------------------------------------------------------------------- */

/** Interrompe la richiesta con un messaggio testuale e, se serve, un log. */
function fail(int $status, string $message, ?string $log = null): void
{
    if ($log !== null) {
        error_log('[stampa_rischi] ' . $log);
    }
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($message);
}

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Schiarisce un colore esadecimale mescolandolo col bianco (0 = originale, 1 = bianco). */
function tint(string $hex, float $amount): string
{
    $hex = ltrim($hex, '#');
    $out = '#';
    foreach ([0, 2, 4] as $offset) {
        $channel = hexdec(substr($hex, $offset, 2));
        $out    .= sprintf('%02x', (int) round($channel + (255 - $channel) * $amount));
    }
    return $out;
}

/** Icona SVG (quadrato arrotondato colorato + glifo bianco) come data URI. */
function iconDataUri(string $key, string $color, bool $inverted = false): string
{
    $glyph = ICON_GLYPHS[$key] ?? '<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 16.5v.5"/>';
    // Versione "invertita": quadrato bianco con glifo colorato (per fondi colorati).
    $background = $inverted ? '#ffffff' : $color;
    $stroke     = $inverted ? $color : '#ffffff';
    if ($inverted) {
        $glyph = str_replace('#ffffff', $color, $glyph);
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48">'
        . '<rect x="0" y="0" width="48" height="48" rx="11" ry="11" fill="' . $background . '"/>'
        . '<g transform="translate(10 10) scale(1.1667)" fill="none" stroke="' . $stroke . '" stroke-width="2" '
        . 'stroke-linecap="round" stroke-linejoin="round">' . $glyph . '</g></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function iconImg(string $key, string $color, int $px): string
{
    return '<img src="' . iconDataUri($key, $color) . '" width="' . $px . '" height="' . $px . '" alt="">';
}

function buildPdfFilename(string $siteName): string
{
    $asciiName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $siteName);
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $asciiName === false ? $siteName : $asciiName) ?? '');
    $slug = trim($slug, '-');
    if ($slug === '') {
        $slug = 'comune';
    }

    return 'guida-rischi-protezione-civile-' . $slug . '.pdf';
}

function sendPdfHeaders(int $size, bool $fromCache, string $filename): void
{
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $size);
    header('Cache-Control: private, max-age=3600');
    header('X-Content-Type-Options: nosniff');
    header('X-PDF-Cache: ' . ($fromCache ? 'HIT' : 'MISS'));
}

function sendPdfFile(string $path, string $filename): void
{
    $size = filesize($path);
    if ($size === false || $size === 0) {
        fail(500, 'Non è stato possibile generare il fascicolo PDF.', 'File PDF vuoto o non leggibile: ' . $path);
    }
    sendPdfHeaders($size, true, $filename);
    if (readfile($path) === false) {
        error_log('[stampa_rischi] Invio del file PDF non riuscito: ' . $path);
    }
    exit;
}

/** Prepara (e verifica) la directory di cache. Restituisce '' se non utilizzabile. */
function prepareCacheDirectory(): string
{
    $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . '/protezione-civile-pdf-' . substr(hash('sha256', __DIR__), 0, 12);

    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        error_log('[stampa_rischi] Impossibile creare la cache PDF: ' . $directory);
        return '';
    }
    if (is_link($directory) || !is_writable($directory)) {
        error_log('[stampa_rischi] Directory cache PDF non sicura o non scrivibile: ' . $directory);
        return '';
    }
    return $directory;
}

/** Salva il PDF in cache (scrittura atomica) e pulisce i file scaduti o in eccesso. */
function storeInCache(string $directory, string $cacheFile, string $pdfData): void
{
    $remaining = [];
    foreach (glob($directory . '/*.pdf') ?: [] as $file) {
        $modifiedAt = filemtime($file);
        if ($modifiedAt !== false && $modifiedAt < time() - CACHE_TTL) {
            if (!unlink($file)) {
                error_log('[stampa_rischi] Impossibile rimuovere un PDF scaduto dalla cache.');
            }
        } else {
            $remaining[$file] = $modifiedAt ?: 0;
        }
    }

    asort($remaining);
    while (count($remaining) >= CACHE_MAX_FILES) {
        $oldest = array_key_first($remaining);
        if ($oldest === null || !unlink($oldest)) {
            error_log('[stampa_rischi] Impossibile contenere la dimensione della cache PDF.');
            break;
        }
        unset($remaining[$oldest]);
    }

    $temporaryFile = tempnam($directory, 'pdf-');
    if ($temporaryFile === false) {
        error_log('[stampa_rischi] Impossibile creare un file temporaneo per la cache PDF.');
        return;
    }
    if (file_put_contents($temporaryFile, $pdfData, LOCK_EX) !== strlen($pdfData)
        || !rename($temporaryFile, $cacheFile)
    ) {
        error_log('[stampa_rischi] Scrittura del PDF in cache non riuscita.');
        if (is_file($temporaryFile) && !unlink($temporaryFile)) {
            error_log('[stampa_rischi] Impossibile rimuovere il file temporaneo PDF.');
        }
    }
}

/** Ridimensiona il logo (max LOGO_MAX_PX) mantenendo la trasparenza. Restituisce i byte PNG. */
function optimizeLogo(string $logoPath): string
{
    $contents = file_get_contents($logoPath);
    if ($contents === false) {
        fail(500, 'Impossibile caricare il logo del fascicolo PDF.', 'Lettura del logo non riuscita: ' . $logoPath);
    }

    $source = @imagecreatefromstring($contents);
    if ($source === false) {
        fail(500, 'Il logo del fascicolo PDF non è in un formato supportato.', 'Logo non supportato: ' . $logoPath);
    }

    $sourceWidth  = imagesx($source);
    $sourceHeight = imagesy($source);
    $scale        = min(1, LOGO_MAX_PX / max($sourceWidth, $sourceHeight));
    $width        = max(1, (int) round($sourceWidth * $scale));
    $height       = max(1, (int) round($sourceHeight * $scale));

    $target = imagecreatetruecolor($width, $height);
    if ($target === false) {
        imagedestroy($source);
        fail(500, 'Impossibile preparare il logo del fascicolo PDF.', 'Ridimensionamento del logo non riuscito.');
    }

    imagealphablending($target, false);
    imagesavealpha($target, true);
    imagefill($target, 0, 0, imagecolorallocatealpha($target, 255, 255, 255, 127));
    imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
    imagedestroy($source);

    ob_start();
    $encoded = imagepng($target, null, 7);
    $png     = ob_get_clean();
    imagedestroy($target);

    if (!$encoded || !is_string($png) || $png === '') {
        fail(500, 'Impossibile preparare il logo del fascicolo PDF.', 'Codifica del logo non riuscita.');
    }
    return $png;
}

/** Aggiunge un anello giallo al logo della copertina, preservandone la trasparenza. */
function addCoverLogoAccent(string $logoPng): string
{
    $source = @imagecreatefromstring($logoPng);
    if ($source === false) {
        fail(500, 'Impossibile preparare il logo del fascicolo PDF.', 'Impossibile riaprire il logo ottimizzato.');
    }

    $sourceWidth  = imagesx($source);
    $sourceHeight = imagesy($source);
    $padding      = 5;
    $canvas       = imagecreatetruecolor($sourceWidth + 2 * $padding, $sourceHeight + 2 * $padding);
    if ($canvas === false) {
        imagedestroy($source);
        fail(500, 'Impossibile preparare il logo del fascicolo PDF.', 'Creazione del bordo del logo non riuscita.');
    }

    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
    $accent = imagecolorallocate($canvas, 255, 194, 0);
    imagefilledellipse($canvas, intdiv($sourceWidth + 2 * $padding, 2), intdiv($sourceHeight + 2 * $padding, 2),
        $sourceWidth + 2 * $padding - 2, $sourceHeight + 2 * $padding - 2, $accent);
    imagealphablending($canvas, true);
    imagecopy($canvas, $source, $padding, $padding, 0, 0, $sourceWidth, $sourceHeight);
    imagedestroy($source);

    ob_start();
    $encoded = imagepng($canvas, null, 7);
    $png     = ob_get_clean();
    imagedestroy($canvas);

    if (!$encoded || !is_string($png) || $png === '') {
        fail(500, 'Impossibile preparare il logo del fascicolo PDF.', 'Codifica del logo con bordo non riuscita.');
    }
    return $png;
}

/**
 * Converte una sezione del sito (markup Bootstrap) in HTML semplice, adatto a mPDF.
 */
function prepareSection(
    DOMDocument $document,
    DOMXPath $xpath,
    DOMElement $source,
    string $id,
    string $brandColor
): string {
    $clone   = $source->cloneNode(true);
    $section = $document->createElement('div');
    $section->setAttribute('class', 'risk-section risk-theme-' . $id);
    $section->setAttribute('id', $id);
    while ($clone->firstChild !== null) {
        $section->appendChild($clone->firstChild);
    }

    // Rimuove icone e cerchi decorativi del sito.
    $toRemove = [];
    foreach ($xpath->query('.//i | .//*[contains(concat(" ", normalize-space(@class), " "), " rounded-circle ")]', $section) as $node) {
        $toRemove[] = $node;
    }
    foreach ($toRemove as $node) {
        $node->parentNode?->removeChild($node);
    }

    // Elimina gli stili inline (il layout PDF è governato dal CSS del documento).
    foreach ($xpath->query('.//*[@style]', $section) as $node) {
        $node->removeAttribute('style');
    }

    // Traduce le classi Bootstrap in classi semantiche per il PDF.
    foreach ($xpath->query('.//div[@class]', $section) as $div) {
        $classes = preg_split('/\s+/', trim($div->getAttribute('class'))) ?: [];

        if (in_array('p-4', $classes, true)) {
            $div->setAttribute('class', 'risk-main-card');
        } elseif (in_array('row', $classes, true)) {
            $div->setAttribute('class', 'risk-advice-list');
        } elseif (array_filter($classes, static fn(string $c): bool => str_starts_with($c, 'col-')) !== []) {
            $div->setAttribute('class', 'risk-advice-column');
        } elseif (in_array('p-3', $classes, true)) {
            $div->setAttribute('class', 'risk-advice-card');
        } elseif (in_array('d-flex', $classes, true) && in_array('align-items-center', $classes, true)) {
            $div->setAttribute('class', 'risk-heading');
        } else {
            $div->removeAttribute('class');
        }
    }

    // Raggruppa titolo + testo + elenco di ogni card per evitare interruzioni di pagina.
    $cards = [];
    foreach ($xpath->query('.//div[@class="risk-advice-card"]', $section) as $card) {
        $cards[] = $card;
    }
    foreach ($cards as $card) {
        $heading = $xpath->query('./h3', $card)->item(0);
        if (!$heading instanceof DOMElement) {
            continue;
        }

        $next = $heading->nextElementSibling;
        $group = $document->createElement('div');
        $group->setAttribute('class', 'risk-advice-title-group');
        $card->insertBefore($group, $heading);
        $group->appendChild($heading);

        if ($next instanceof DOMElement && strtolower($next->tagName) === 'p') {
            $group->appendChild($next);
        }
    }

    // Intestazione della scheda con icona e titolo.
    $banner = $xpath->query('.//div[@class="risk-heading"]', $section)->item(0);
    if ($banner instanceof DOMElement) {
        $textNodes = [];
        foreach ($banner->childNodes as $child) {
            $textNodes[] = $child;
        }

        $table = $document->createElement('table');
        $table->setAttribute('class', 'risk-title-table');
        $table->setAttribute('cellpadding', '0');
        $table->setAttribute('cellspacing', '0');
        $row = $document->createElement('tr');

        $iconCell = $document->createElement('td');
        $iconCell->setAttribute('class', 'risk-title-icon');
        $img = $document->createElement('img');
        $img->setAttribute('src', iconDataUri($id, $brandColor, true));
        $img->setAttribute('width', '60');
        $img->setAttribute('height', '60');
        $img->setAttribute('alt', '');
        $iconCell->appendChild($img);

        $textCell = $document->createElement('td');
        $textCell->setAttribute('class', 'risk-title-text');
        foreach ($textNodes as $child) {
            $textCell->appendChild($child);
        }

        $row->appendChild($iconCell);
        $row->appendChild($textCell);
        $table->appendChild($row);
        $banner->appendChild($table);
    }

    $markup = $document->saveHTML($section);
    if ($markup === false) {
        fail(500, 'Impossibile preparare il fascicolo PDF.', 'Impossibile serializzare la sezione: ' . $id);
    }
    return $markup;
}

/** Indice della copertina: griglia a due colonne con icona, numero e titolo di ogni rischio. */
function buildIndex(array $ids, array $titles, array $riskColors, string $brandColor): string
{
    $html = '<table class="index" cellpadding="0" cellspacing="0">';
    $count = count($ids);

    for ($i = 0; $i < $count; $i += 2) {
        $html .= '<tr>';
        for ($j = $i; $j < $i + 2; $j++) {
            if ($j >= $count) {
                $html .= '<td class="index-cell index-empty">&nbsp;</td>';
                continue;
            }
            $color = $riskColors[$ids[$j]] ?? $brandColor;
            $html .= '<td class="index-cell">'
                . '<table cellpadding="0" cellspacing="0"><tr>'
                . '<td class="index-icon">' . iconImg($ids[$j], $color, 30) . '</td>'
                . '<td class="index-text"><div class="index-n">SCHEDA ' . sprintf('%02d', $j + 1) . '</div>'
                . '<div class="index-t">' . esc($titles[$j]) . '</div></td>'
                . '</tr></table></td>';
        }
        $html .= '</tr>';
    }

    return $html . '</table>';
}

/* -------------------------------------------------------------------------
 * Dipendenze
 * ---------------------------------------------------------------------- */

$autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!is_readable($autoloadPath)) {
    fail(503, 'Esportazione PDF temporaneamente non disponibile.', 'Autoloader Composer non trovato: ' . $autoloadPath);
}
require_once $autoloadPath;
if (!class_exists(\Mpdf\Mpdf::class)) {
    fail(503, 'Esportazione PDF non configurata. Contattare il gestore del sito.', 'mPDF non installato nel vendor Composer.');
}

require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site   = $config['site'];
$siteNameRaw = (string) $site['nome_comune'];
$pdfFilename = buildPdfFilename($siteNameRaw);

/* -------------------------------------------------------------------------
 * Selezione delle sezioni
 * ---------------------------------------------------------------------- */

if (isset($_GET['sezioni']) && !is_string($_GET['sezioni'])) {
    fail(400, 'Selezione delle sezioni non valida.');
}
if (isset($_GET['sezioni']) && strlen($_GET['sezioni']) > MAX_PARAM_LENGTH) {
    fail(400, 'Selezione delle sezioni troppo lunga.');
}

$hasSelection      = array_key_exists('sezioni', $_GET);
$requestedSections = $hasSelection
    ? array_values(array_unique(array_filter(
        array_map('trim', explode(',', $_GET['sezioni'])),
        static fn(string $id): bool => $id !== ''
    )))
    : [];

if ($hasSelection && $requestedSections === []) {
    fail(400, 'Seleziona almeno una sezione da esportare.');
}
if (count($requestedSections) > MAX_SECTIONS) {
    fail(400, 'Troppe sezioni selezionate.');
}

/* -------------------------------------------------------------------------
 * Markup condiviso -> DOM
 * ---------------------------------------------------------------------- */

$is_print_view = true;
ob_start();
include __DIR__ . '/contenuti_rischi.php';
$risksMarkup = ob_get_clean();

$document        = new DOMDocument('1.0', 'UTF-8');
$previousLibxml  = libxml_use_internal_errors(true);
$documentLoaded  = $document->loadHTML(
    '<!doctype html><html lang="it"><head><meta charset="UTF-8"></head><body>' . $risksMarkup . '</body></html>',
    LIBXML_NONET | LIBXML_HTML_NODEFDTD
);
libxml_clear_errors();
libxml_use_internal_errors($previousLibxml);

if (!$documentLoaded) {
    fail(500, 'Impossibile preparare il fascicolo PDF.', 'Impossibile analizzare il markup delle sezioni rischio.');
}

$xpath       = new DOMXPath($document);
$allSections = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " risk-section ")]');
if ($allSections === false || $allSections->length === 0) {
    fail(500, 'Nessuna sezione rischio disponibile per il fascicolo.', 'Nessuna sezione rischio trovata nel markup condiviso.');
}

$sectionsById = [];
foreach ($allSections as $section) {
    $id = $section->getAttribute('id');
    if ($id !== '') {
        $sectionsById[$id] = $section;
    }
}

if (array_diff($requestedSections, array_keys($sectionsById)) !== []) {
    fail(400, 'La selezione contiene una sezione rischio non valida.');
}

// Gli id selezionati mantengono l'ordine originale delle schede.
$selectedIds = $hasSelection
    ? array_values(array_filter(array_keys($sectionsById), static fn(string $id): bool => in_array($id, $requestedSections, true)))
    : array_keys($sectionsById);

$selectedTitles = [];
foreach ($selectedIds as $id) {
    $heading          = $xpath->query('.//h2', $sectionsById[$id])->item(0);
    $selectedTitles[] = $heading instanceof DOMElement ? trim($heading->textContent) : $id;
}

/* -------------------------------------------------------------------------
 * Impostazioni grafiche
 * ---------------------------------------------------------------------- */

$riskColors = [
    'meteo'        => '#0d6efd',
    'neve'         => '#17a2b8',
    'terremoto'    => '#dc3545',
    'alluvione'    => '#0d6efd',
    'incendio'     => '#d97706',
    'vulcanico'    => '#6f42c1',
    'frana'        => '#a0522d',
    'valanga'      => '#17a2b8',
    'crisi-idrica' => '#0d6efd',
    'ciclone'      => '#198754',
    'maremoto'     => '#0a58ca',
    'nucleare'     => '#b18400',
    'industriale'  => '#6c757d',
    'blackout'     => '#d8660a',
];

$brandColor = (string) ($site['colore_tema'] ?? DEFAULT_BRAND);
if (!preg_match('/^#[0-9a-f]{6}$/i', $brandColor)) {
    $brandColor = DEFAULT_BRAND;
}
$logoPath = realpath(__DIR__ . '/../assets/img/logo.png');
if ($logoPath === false || !is_readable($logoPath)) {
    fail(500, 'Impossibile caricare il logo del fascicolo PDF.', 'Logo non trovato o non leggibile: ' . __DIR__ . '/../assets/img/logo.png');
}

/* -------------------------------------------------------------------------
 * Cache
 * ---------------------------------------------------------------------- */

$cacheDirectory = prepareCacheDirectory();
$cacheKey       = hash('sha256', implode('|', [
    CACHE_VERSION,
    date('Y-m-d'),
    implode(',', $selectedIds),
    hash_file('sha256', __FILE__),
    hash_file('sha256', __DIR__ . '/contenuti_rischi.php'),
    hash_file('sha256', $logoPath),
    hash('sha256', serialize($site)),
    \Mpdf\Mpdf::VERSION,
]));
$cacheFile = $cacheDirectory !== '' ? $cacheDirectory . '/' . $cacheKey . '.pdf' : '';

if ($cacheFile !== '' && is_file($cacheFile) && filemtime($cacheFile) >= time() - CACHE_TTL) {
    sendPdfFile($cacheFile, $pdfFilename);
}

/* -------------------------------------------------------------------------
 * Contenuto
 * ---------------------------------------------------------------------- */

$totalSections = count($selectedIds);
$sectionMarkup = [];
foreach ($selectedIds as $id) {
    $sectionMarkup[$id] = prepareSection(
        $document,
        $xpath,
        $sectionsById[$id],
        $id,
        $brandColor
    );
}

$siteName     = esc($siteNameRaw);
$title        = 'Guida ai rischi - Protezione Civile ' . $siteNameRaw;
$logoContents = optimizeLogo($logoPath);
$coverLogoContents = addCoverLogoAccent($logoContents);
$logo         = '<img src="var:protezioneCivileLogo" width="42" height="42" alt="">';
$coverLogo    = '<img src="var:protezioneCivileCoverLogo" width="100" height="100" alt="">';
$indexMarkup  = buildIndex($selectedIds, $selectedTitles, $riskColors, $brandColor);

$footer = '
<table class="document-footer" cellpadding="0" cellspacing="0">
    <tr>
        <td>Protezione Civile &middot; Comune di ' . $siteName . '</td>
        <td class="page-number">Pagina {PAGENO} di {nb}</td>
    </tr>
</table>';

// Pagina 1: copertina con indice.
$cover = '
<div class="cover">
    <div class="cover-hero-shell">
        <table class="cover-hero" cellpadding="0" cellspacing="0">
            <tr>
                <td class="cover-title-cell">
                    <div class="cover-pill">Protezione Civile &middot; Guida pratica</div>
                    <h1>Guida ai rischi</h1>
                    <div class="cover-rule"></div>
                    <p class="cover-subtitle" style="color: #ffffff;">Prepararsi, riconoscere i pericoli,<br>proteggere le persone.</p>
                </td>
                <td class="cover-emblem"><div class="cover-logo-frame">' . $coverLogo . '</div></td>
            </tr>
        </table>
    </div>

    <div class="cover-meta">
        <table class="cover-meta-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="meta-cell meta-territory">
                    <div class="meta-label">Territorio</div>
                    <div class="meta-value">Comune di ' . $siteName . '</div>
                </td>
                <td class="meta-cell meta-count">
                    <div class="meta-label">Schede</div>
                    <div class="meta-value meta-big">' . $totalSections . '</div>
                </td>
                <td class="meta-cell meta-date">
                    <div class="meta-label">Aggiornato al</div>
                    <div class="meta-value">' . date('d/m/Y') . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="cover-emergency">
        <div class="cover-number">112</div>
        <div class="cover-emergency-body">
            <div class="cover-emergency-label">PERICOLO IMMEDIATO</div>
            <div class="cover-emergency-title">Chiama il Numero Unico di Emergenza</div>
            <div class="cover-emergency-copy">La chiamata è gratuita. Rispondi con calma alle domande dell’operatore e segui le sue indicazioni.</div>
        </div>
        <div class="emergency-clear"></div>
    </div>

    <div class="section-label">In questa guida</div>
    ' . $indexMarkup . '
</div>
<pagebreak />';

// Pagina 2: cosa fare e come prepararsi.
$callSteps = [
    ['Dove sei', 'Comune, via, numero civico o un punto di riferimento. Se non lo sai, descrivi cosa vedi.'],
    ['Cosa succede', 'Spiega in modo semplice l’emergenza e da quando è iniziata.'],
    ['Chi è coinvolto', 'Indica quante persone sono in pericolo o ferite e come stanno.'],
    ['Quali pericoli ci sono', 'Per esempio fumo, fiamme, acqua, traffico o sostanze pericolose.'],
];
$stepsHtml = '<div class="steps">';
foreach (array_chunk($callSteps, 2, true) as $pair) {
    $stepsHtml .= '<div class="steps-row">';
    foreach ($pair as $index => [$stepTitle, $stepText]) {
        $stepsHtml .= '<div class="step-card"><table cellpadding="0" cellspacing="0"><tr>'
            . '<td class="step-num-cell"><div class="step-num">' . ($index + 1) . '</div></td>'
            . '<td class="step-body"><div class="step-title">' . esc($stepTitle) . '</div>'
            . '<div class="step-text">' . esc($stepText) . '</div></td>'
            . '</tr></table></div>';
    }
    $stepsHtml .= '<div class="steps-clear"></div></div>';
}
$stepsHtml .= '</div>';

$afterItems = [
    'Segui le istruzioni dell’operatore e tieni libero il telefono.',
    'Se puoi farlo senza rischi, allontanati dalla zona pericolosa e avvisa chi è vicino.',
    'Non rientrare in edifici o aree evacuate finché non è autorizzato.',
    'Per aggiornamenti, affidati ai canali ufficiali del Comune e delle autorità.',
];
$afterHtml = '<div class="after"><table cellpadding="0" cellspacing="0">';
foreach ($afterItems as $item) {
    $afterHtml .= '<tr><td class="after-icon">' . iconImg('g-check', '#0f8060', 20) . '</td>'
        . '<td class="after-text">' . esc($item) . '</td></tr>';
}
$afterHtml .= '</table></div>';

$prepareCards = [
    ['g-plan', 'Fai un piano', 'Concorda con la famiglia come contattarvi e dove ritrovarvi.'],
    ['g-kit', 'Prepara l’essenziale', 'Tieni pronti acqua, torcia, radio, farmaci e documenti.'],
    ['g-map', 'Conosci il territorio', 'Informati su allerte, percorsi sicuri e indicazioni locali.'],
];
$prepareHtml = '<div class="prepare">';
foreach ($prepareCards as [$iconKey, $cardTitle, $cardText]) {
    $prepareHtml .= '<div class="prepare-card">'
        . '<div class="prepare-icon">' . iconImg($iconKey, $brandColor, 34) . '</div>'
        . '<div class="prepare-title">' . esc($cardTitle) . '</div>'
        . '<div class="prepare-text">' . esc($cardText) . '</div></div>';
}
$prepareHtml .= '<div class="prepare-clear"></div></div>';

$guide = '
<div class="guide">
    <div class="section-label">Prima di tutto</div>
    <h1 class="guide-title">Cosa fare in caso di emergenza</h1>

    <table class="guide-call-title" cellpadding="0" cellspacing="0">
        <tr>
            <td class="gct-icon">' . iconImg('g-phone', $brandColor, 30) . '</td>
            <td class="gct-text">Cosa dire quando chiami il 112</td>
        </tr>
    </table>
    ' . $stepsHtml . '
    <div class="guide-important">Non riattaccare finché l’operatore non ti dice che puoi farlo.</div>

    <table class="guide-call-title" cellpadding="0" cellspacing="0">
        <tr>
            <td class="gct-icon">' . iconImg('g-alert', '#0f8060', 30) . '</td>
            <td class="gct-text">Dopo la chiamata</td>
        </tr>
    </table>
    ' . $afterHtml . '

    <div class="section-label section-label-spaced">Prepararsi prima aiuta</div>
    ' . $prepareHtml . '
</div>';

$styles = '
body { font-family: ' . PDF_FONT . ', sans-serif; color: #212529; font-size: 9.5pt; line-height: 1.45; }

/* The cover carries the visual identity; interior pages stay restrained and print-friendly. */
.document-header { width: 100%; border-bottom: .3mm solid #dee2e6; }
.document-header td { background-color: #ffffff; padding: 1.5mm 2mm; vertical-align: middle; }
.document-logo { width: 12mm; text-align: left; }
.document-header-copy { padding-left: 1mm; }
.document-kicker { color: #6c757d; font-size: 6.5pt; text-transform: uppercase; letter-spacing: .5px; }
.document-header-title { color: ' . $brandColor . '; font-size: 9pt; font-weight: bold; }
.document-footer { width: 100%; border-top: .3mm solid #dee2e6; color: #6c757d; font-size: 7.5pt; padding-top: 1.5mm; }
.page-number { text-align: right; }

.section-label { color: ' . $brandColor . '; font-size: 8pt; font-weight: bold; letter-spacing: .7px; text-transform: uppercase;
    border-bottom: .6mm solid ' . ACCENT . '; padding-bottom: 1.5mm; margin: 0 0 3mm; }
.section-label-spaced { margin-top: 6mm; }

/* Cover */
.cover { color: #212529; }
.cover-hero-shell { background-color: ' . DEFAULT_BRAND . '; border: 1mm solid ' . DEFAULT_BRAND . ';
    border-radius: 3mm; margin-bottom: 5mm; }
.cover-hero { width: 100%; border-collapse: separate; border-spacing: 0; }
.cover-title-cell { background-color: transparent; padding: 11mm 4mm 11mm 9mm; vertical-align: middle; }
.cover-pill { display: inline-block; background-color: ' . ACCENT . '; color: #001a3a; border: .3mm solid ' . ACCENT . '; border-radius: 6mm;
    margin-bottom: 5mm; padding: 1.4mm 3mm; font-size: 7pt; font-weight: bold; letter-spacing: .7px; text-transform: uppercase; }
.cover h1 { color: #ffffff; font-size: 34pt; font-weight: bold; line-height: 1.05; margin: 0 0 3mm; }
.cover-rule { background-color: ' . ACCENT . '; width: 18mm; height: 1.2mm; margin: 0 0 3mm; border-radius: 1mm; }
.cover .cover-title-cell .cover-subtitle { color: #ffffff !important; font-size: 11pt; line-height: 1.4; margin: 0; }
.cover-emblem { width: 49mm; background-color: transparent; padding: 8mm 8mm 8mm 2mm; vertical-align: middle; text-align: center; }
.cover-meta { width: 100%; background-color: #ffffff; border: .35mm solid #d5dce5; border-left: 1.2mm solid ' . $brandColor . '; border-radius: 3mm; margin: 0 0 4mm; }
.cover-meta-table { width: 100%; }
.meta-cell { padding: 3mm 4mm; vertical-align: middle; }
.meta-territory { width: 45%; border-right: .4mm solid #d5dce5; }
.meta-count { width: 22%; border-right: .4mm solid #d5dce5; text-align: center; }
.meta-date { width: 33%; text-align: center; }
.meta-label { color: #6c757d; font-size: 6.5pt; font-weight: bold; letter-spacing: .5px; text-transform: uppercase; margin-bottom: .8mm; }
.meta-value { color: #212529; font-size: 9.5pt; font-weight: bold; }
.meta-big { color: ' . $brandColor . '; font-size: 16pt; line-height: 1.05; }
.cover-emergency { width: 100%; background-color: #f8d7da; border: .35mm solid #f5c2c7; border-radius: 3mm; padding: 2mm; margin: 0 0 5mm; }
.cover-number { float: left; width: 30mm; background-color: #D62828; color: #ffffff; font-size: 25pt; font-weight: bold; text-align: center;
    padding: 3.5mm 2mm; border-radius: 3mm; }
.cover-emergency-body { margin-left: 35mm; padding: 2mm; }
.cover-emergency-label { color: #842029; font-size: 6.5pt; font-weight: bold; letter-spacing: .6px; }
.cover-emergency-title { color: #842029; font-size: 11pt; font-weight: bold; margin: .5mm 0; }
.cover-emergency-copy { color: #58151c; font-size: 8.5pt; }

.index { width: 100%; }
.index-cell { width: 50%; padding: 1.8mm 3mm 1.8mm 0; border-bottom: .25mm solid #dee2e6; vertical-align: middle; }
.index-cell table { width: 100%; }
.index-icon { width: 11mm; vertical-align: middle; }
.index-text { vertical-align: middle; }
.index-n { color: #6c757d; font-size: 6pt; font-weight: bold; letter-spacing: .5px; }
.index-t { color: #212529; font-size: 9pt; font-weight: bold; }
.index-empty { border: 0; }

/* Quick-start page */
.guide { color: #212529; }
.guide-title { color: ' . $brandColor . '; font-size: 20pt; margin: 0 0 5mm; }
.guide-call-title { width: 100%; margin: 0 0 2.5mm; border-bottom: .5mm solid #dee2e6; }
.gct-icon { width: 11mm; padding-bottom: 2mm; vertical-align: middle; }
.gct-text { color: ' . $brandColor . '; font-size: 12pt; font-weight: bold; vertical-align: middle; padding-bottom: 2mm; }
.steps { width: 100%; margin: 0 0 1mm; }
.steps-row { width: 100%; }
.step-card { float: left; width: 42%; background-color: #f8f9fa; border: .3mm solid #dee2e6; border-left: 1mm solid ' . $brandColor . '; border-radius: 3mm; padding: 3mm; margin: 0 1% 2mm; }
.steps-clear, .prepare-clear { clear: both; height: 0; }
.step-card table { width: 100%; }
.step-num-cell { width: 9mm; vertical-align: top; }
.step-num { color: ' . $brandColor . '; font-size: 12pt; font-weight: bold; text-align: center; }
.step-body { vertical-align: top; }
.step-title { color: #212529; font-size: 10pt; font-weight: bold; margin-bottom: .5mm; }
.step-text { color: #495057; font-size: 8.5pt; line-height: 1.4; }
.guide-important { background-color: #fff3cd; border: .3mm solid #ffecb5; border-left: 1.3mm solid ' . ACCENT . '; border-radius: 3mm;
    color: #664d03; font-size: 8.5pt; font-weight: bold; margin: 2mm 0 5mm; padding: 2.5mm 3mm; }
.after { width: 100%; background-color: #d1e7dd; border: .3mm solid #a3cfbb; border-radius: 3mm; padding: 1mm; }
.after table { width: 100%; }
.after-icon { width: 10mm; padding: 2mm 0 2mm 3mm; vertical-align: top; }
.after-text { color: #0f5132; font-size: 9pt; padding: 2mm 3mm 2mm 1mm; vertical-align: top; }
.prepare { width: 100%; }
.prepare-card { float: left; width: 26%; background-color: #ffffff; border: .35mm solid #dee2e6; border-left: 1mm solid ' . $brandColor . '; border-radius: 3mm; padding: 3mm; margin: 0 1% 2mm; }
.prepare-icon { margin-bottom: 1.5mm; }
.prepare-title { color: ' . $brandColor . '; font-size: 9.5pt; font-weight: bold; margin-bottom: .8mm; }
.prepare-text { color: #495057; font-size: 8pt; line-height: 1.4; }

/* Risk sheets */
.risk-section { page-break-inside: auto; }
.risk-main-card { background-color: #ffffff; padding: 0; }
.risk-heading { margin: 0 0 4mm; padding: 2mm; background-color: ' . tint($brandColor, 0.96) . ';
    border: .35mm solid ' . tint($brandColor, 0.76) . '; border-left: 1.2mm solid ' . $brandColor . '; border-radius: 3mm; }
.risk-title-table { width: 100%; color: #212529; background-color: transparent; }
.risk-title-icon { width: 15mm; padding: 1mm 2mm 1mm 0; vertical-align: middle; }
.risk-title-text { padding: 1mm 2mm; vertical-align: middle; color: #212529; }
.risk-section .risk-title-text .badge { display: block; color: ' . $brandColor . '; background-color: transparent; font-size: 7pt; font-weight: bold; letter-spacing: .5px; text-transform: uppercase; margin: 0 0 .8mm; padding: 0; }
.risk-section .risk-title-text h2 { color: ' . $brandColor . '; font-size: 21pt; line-height: 1.1; margin: 0; }
.risk-advice-list { display: block; padding: 0; }
.risk-advice-column { display: block; margin-bottom: 4mm; page-break-inside: auto; }
.risk-advice-card { background-color: #ffffff; border: 0; border-top: .25mm solid #dee2e6;
    border-radius: 0; padding: 2.5mm 0 0; page-break-inside: auto; }
.risk-advice-column:first-child .risk-advice-card { border-top: 0; padding-top: 0; }
.risk-advice-title-group { page-break-inside: avoid; }
.risk-section h3 { color: ' . $brandColor . '; font-size: 10.5pt; margin: 0 0 1.5mm; page-break-after: avoid; }
.risk-section p { margin: 0 0 1.5mm; }
.risk-section p.small { color: #495057; font-size: 8.5pt; font-weight: bold; margin: 2mm 0 .8mm; page-break-after: avoid; }
.risk-section ul { list-style-position: outside; margin: 0 0 1.5mm; padding-left: 5mm; }
.risk-section li { margin: 0 0 1mm; orphans: 2; widows: 2; line-height: 1.4; }
.risk-section strong { color: #212529; }
';

/* -------------------------------------------------------------------------
 * Generazione PDF
 * ---------------------------------------------------------------------- */

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode'          => 'utf-8',
        'format'        => 'A4',
        'default_font'  => PDF_FONT,
        'margin_left'   => 18,
        'margin_right'  => 18,
        'margin_top'    => 14,
        'margin_bottom' => 14,
        'margin_header' => 0,
        'margin_footer' => 0,
        'tempDir'       => sys_get_temp_dir(),
    ]);
    $mpdf->SetTitle($title);
    $mpdf->SetAuthor('Protezione Civile - Comune di ' . $siteNameRaw);
    $mpdf->imageVars['protezioneCivileLogo'] = $logoContents;
    $mpdf->imageVars['protezioneCivileCoverLogo'] = $coverLogoContents;
    $mpdf->AliasNbPages();

    $mpdf->WriteHTML($styles, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($cover . $guide, \Mpdf\HTMLParserMode::HTML_BODY);

    // Ogni scheda conserva il proprio titolo anche nelle pagine di continuazione.
    foreach ($selectedIds as $position => $id) {
        $header = '
<table class="document-header" cellpadding="0" cellspacing="0">
    <tr>
        <td class="document-logo">' . $logo . '</td>
        <td class="document-header-copy">
            <div class="document-kicker">Protezione Civile &middot; Guida ai rischi</div>
            <div class="document-header-title">' . esc($selectedTitles[$position]) . '</div>
        </td>
    </tr>
</table>';
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);

        if ($position === 0) {
            $mpdf->AddPageByArray([
                'mgl' => 18,
                'mgr' => 18,
                'mgt' => 30,
                'mgb' => 20,
                'mgh' => 7,
                'mgf' => 8,
            ]);
        } else {
            $mpdf->AddPage();
        }

        $mpdf->WriteHTML($sectionMarkup[$id], \Mpdf\HTMLParserMode::HTML_BODY);
    }

    $pdfData = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    if (!is_string($pdfData) || !str_starts_with($pdfData, '%PDF-')) {
        throw new RuntimeException('mPDF non ha prodotto un documento PDF valido.');
    }

    if ($cacheDirectory !== '') {
        storeInCache($cacheDirectory, $cacheFile, $pdfData);
    }

    sendPdfHeaders(strlen($pdfData), false, $pdfFilename);
    echo $pdfData;
} catch (\Throwable $exception) {
    fail(500, 'Non è stato possibile generare il fascicolo PDF.', 'Generazione PDF non riuscita: ' . $exception->getMessage());
}