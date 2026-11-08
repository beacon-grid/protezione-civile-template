<?php
// ── galleria_helper_admin.php ──────────────────────────────────────────
// Funzioni di supporto per la gestione della cartella
// assets/img/galleria (upload, rinomina, eliminazione, listing).
//
// ⚠️ PRESUPPUNTI DI SICUREZZA:
// - Questo file è un helper dietro autenticazione admin (usato SOLO in admin/galleria.php)
// - Verifica CSRF, autenticazione e ruolo admin sono implementate dall'endpoint che lo include
// - Funzioni come galleria_delete() e galleria_rename() NON fanno controlli di permessi
//   perché presuppongono di essere sempre chiamate dopo il controllo di autorizzazione
// - Non renderizzare l'output di queste funzioni direttamente senza sanitizzazione
//   (già fatto in admin/galleria.php, ma attenzione a eventuali riutilizzi in altri file)


define('GALLERIA_DIR', __DIR__ . '/../assets/img/galleria');
define('GALLERIA_URL_BASE', '../assets/img/galleria');
define('GALLERIA_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif']);
define('GALLERIA_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif']);
define('GALLERIA_MAX_SIZE', 5 * 1024 * 1024); // 5 MB per file

/**
 * Verifica "a bassa quota" per i file AVIF: alcune build di PHP non
 * riconoscono ancora questo formato con getimagesize()/finfo, quindi
 * come ultima spiaggia controlliamo i magic bytes del box ISOBMFF
 * "ftyp....avif" / "ftyp....avis" nei primi byte del file.
 *
 * ⚠️ NOTA DI SICUREZZA: Questo controlla solo i magic bytes (primi 32 byte),
 * NON valida la struttura ISOBMFF completa. È un fallback pragmatico accettabile
 * perché:
 * - È dietro autenticazione admin (require_login + require_role)
 * - È preceduto da controlli estensione (whitelist) e MIME type
 * - Un file malformato causerebbe errore al rendering, non esecuzione codice
 *
 * Se necessitasse validazione più rigorosa (es. endpoint pubblico), considera
 * una libreria specializzata o il rifiuto dei file AVIF non riconosciuti da
 * getimagesize().
 */
function galleria_looks_like_avif(string $path): bool
{
    $handle = @fopen($path, 'rb');
    if (!$handle) {
        return false;
    }
    $header = fread($handle, 32);
    fclose($handle);
    if ($header === false || strlen($header) < 12) {
        return false;
    }
    // Byte 4-7 devono essere "ftyp", byte 8-11 il brand (avif / avis / mif1 con avif tra i brand compatibili)
    return substr($header, 4, 4) === 'ftyp'
        && (str_contains($header, 'avif') || str_contains($header, 'avis'));
}

/**
 * Ritorna il path assoluto della cartella galleria, creandola se non esiste.
 */
function galleria_dir_path(): string
{
    if (!is_dir(GALLERIA_DIR)) {
        @mkdir(GALLERIA_DIR, 0755, true);
    }
    return GALLERIA_DIR;
}

/**
 * Elenca tutte le immagini presenti nella cartella galleria,
 * ordinate dalla più recente alla più vecchia.
 */
function galleria_list(): array
{
    $dir = galleria_dir_path();
    $files = [];

    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }
        $path = $dir . '/' . $f;
        if (!is_file($path)) {
            continue;
        }
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, GALLERIA_ALLOWED_EXT, true)) {
            continue;
        }

        $dim = @getimagesize($path);

        $files[] = [
            'filename' => $f,
            'url'      => GALLERIA_URL_BASE . '/' . rawurlencode($f),
            'size'     => filesize($path),
            'width'    => $dim[0] ?? null,
            'height'   => $dim[1] ?? null,
            'mtime'    => filemtime($path),
        ];
    }

    usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);

    return $files;
}

/**
 * Formatta una dimensione in byte in formato leggibile (KB/MB).
 */
function galleria_format_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

/**
 * Ripulisce un nome file da path traversal e caratteri non sicuri.
 */
function galleria_sanitize_basename(string $name): string
{
    $name = basename($name);
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
    $name = trim($name, '._-');
    return $name === '' ? 'file' : $name;
}

/**
 * Se il nome esiste già nella cartella, aggiunge un suffisso -1, -2, ...
 * finché non trova un nome libero.
 */
function galleria_unique_filename(string $filename): string
{
    $dir = galleria_dir_path();
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $base = pathinfo($filename, PATHINFO_FILENAME);

    $candidate = $filename;
    $i = 1;
    while (file_exists($dir . '/' . $candidate)) {
        $candidate = $base . '-' . $i . ($ext !== '' ? '.' . $ext : '');
        $i++;
    }
    return $candidate;
}

/**
 * Gestisce l'upload di un singolo file (uno degli elementi di $_FILES).
 * Ritorna ['ok' => bool, 'msg' => string, 'filename' => ?string]
 */
function galleria_upload_one(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'msg' => 'Nessun file inviato.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'msg' => 'Errore durante il caricamento di "' . $file['name'] . '".'];
    }
    if ($file['size'] > GALLERIA_MAX_SIZE) {
        return ['ok' => false, 'msg' => '"' . $file['name'] . '" supera la dimensione massima di 5MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, GALLERIA_ALLOWED_EXT, true)) {
        return ['ok' => false, 'msg' => '"' . $file['name'] . '": formato non consentito.'];
    }

    // Verifica che sia realmente un'immagine (non solo estensione).
    // Per l'AVIF, getimagesize()/finfo non sono affidabili su tutte le
    // build di PHP: se falliscono, ripieghiamo su un controllo dei
    // magic bytes prima di respingere il file.
    $dim = @getimagesize($file['tmp_name']);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $recognizedByStandardChecks = ($dim !== false) && in_array($mime, GALLERIA_ALLOWED_MIME, true);

    if (!$recognizedByStandardChecks) {
        if ($ext === 'avif' && galleria_looks_like_avif($file['tmp_name'])) {
            // File AVIF valido ma non riconosciuto dalle funzioni PHP disponibili: si procede,
            // semplicemente senza dimensioni (width/height resteranno null).
            $dim = false;
        } else {
            return ['ok' => false, 'msg' => '"' . $file['name'] . '" non è un\'immagine valida.'];
        }
    }

    $safeName = galleria_sanitize_basename($file['name']);
    $safeName = galleria_unique_filename($safeName);
    $dest = galleria_dir_path() . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'msg' => 'Errore nel salvataggio di "' . $file['name'] . '".'];
    }
    @chmod($dest, 0644);

    return ['ok' => true, 'msg' => '"' . $safeName . '" caricata.', 'filename' => $safeName];
}

/**
 * Normalizza l'array $_FILES di un input multiplo (name="immagini[]")
 * in una lista di singoli file, ciascuno nel formato classico di $_FILES.
 */
function galleria_normalize_files(array $filesInput): array
{
    $out = [];
    if (!isset($filesInput['name'])) {
        return $out;
    }
    if (!is_array($filesInput['name'])) {
        return [$filesInput];
    }
    foreach ($filesInput['name'] as $i => $name) {
        $out[] = [
            'name'     => $name,
            'type'     => $filesInput['type'][$i] ?? '',
            'tmp_name' => $filesInput['tmp_name'][$i] ?? '',
            'error'    => $filesInput['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $filesInput['size'][$i] ?? 0,
        ];
    }
    return $out;
}

/**
 * Rinomina un file esistente mantenendone l'estensione originale.
 * Ritorna ['ok' => bool, 'msg' => string, 'filename' => ?string]
 */
function galleria_rename(string $oldName, string $newNameRaw): array
{
    $dir = galleria_dir_path();
    $oldName = galleria_sanitize_basename($oldName);
    $oldPath = $dir . '/' . $oldName;

    if (!is_file($oldPath)) {
        return ['ok' => false, 'msg' => 'File non trovato.'];
    }

    $ext = pathinfo($oldName, PATHINFO_EXTENSION);
    $newBase = pathinfo(galleria_sanitize_basename($newNameRaw), PATHINFO_FILENAME);

    if ($newBase === '') {
        return ['ok' => false, 'msg' => 'Nome non valido.'];
    }

    $newName = $newBase . ($ext !== '' ? '.' . $ext : '');

    if ($newName === $oldName) {
        return ['ok' => true, 'msg' => 'Nessuna modifica effettuata.', 'filename' => $oldName];
    }

    $newName = galleria_unique_filename($newName);
    $newPath = $dir . '/' . $newName;

    if (!rename($oldPath, $newPath)) {
        return ['ok' => false, 'msg' => 'Errore durante la rinomina.'];
    }

    return ['ok' => true, 'msg' => 'File rinominato in "' . $newName . '".', 'filename' => $newName];
}

/**
 * Elimina un file dalla galleria.
 */
function galleria_delete(string $filename): bool
{
    $dir = galleria_dir_path();
    $filename = galleria_sanitize_basename($filename);
    $path = $dir . '/' . $filename;
    if (!is_file($path)) {
        return false;
    }
    return unlink($path);
}