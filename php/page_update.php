<?php

/**
 * Usa le date dei file sorgente come stima automatica dell'ultimo aggiornamento.
 * Un deploy che riscrive i timestamp dei file può influire sul risultato.
 */
function site_page_last_updated_timestamp(): ?int
{
    $documentRoot = realpath(dirname(__DIR__));
    if ($documentRoot === false) {
        return null;
    }

    $files = get_included_files();
    $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if ($scriptFilename !== '') {
        $files[] = $scriptFilename;
    }

    $latestTimestamp = null;
    foreach (array_unique($files) as $file) {
        $realPath = realpath($file);
        if ($realPath === false || !str_starts_with($realPath, $documentRoot . DIRECTORY_SEPARATOR)) {
            continue;
        }

        $relativePath = substr($realPath, strlen($documentRoot) + 1);
        if (
            !str_ends_with($relativePath, '.php')
            || str_starts_with($relativePath, 'admin/')
            || str_starts_with($relativePath, 'php/')
            || str_starts_with($relativePath, 'vendor/')
            || in_array($relativePath, ['includes/header.php', 'includes/footer.php'], true)
        ) {
            continue;
        }

        $timestamp = filemtime($realPath);
        if ($timestamp !== false && ($latestTimestamp === null || $timestamp > $latestTimestamp)) {
            $latestTimestamp = $timestamp;
        }
    }

    $scriptPath = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
    if ($scriptPath === $documentRoot . DIRECTORY_SEPARATOR . 'glossario.php') {
        require_once __DIR__ . '/database.php';
        $glossaryTimestamp = app_glossary_last_updated_timestamp();
        if ($glossaryTimestamp !== null && ($latestTimestamp === null || $glossaryTimestamp > $latestTimestamp)) {
            $latestTimestamp = $glossaryTimestamp;
        }
    }

    return $latestTimestamp;
}

function site_page_should_show_updated_at(): bool
{
    $documentRoot = realpath(dirname(__DIR__));
    $scriptFilename = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
    if ($documentRoot === false || $scriptFilename === false) {
        return true;
    }

    if (!str_starts_with($scriptFilename, $documentRoot . DIRECTORY_SEPARATOR)) {
        return true;
    }

    $relativePath = substr($scriptFilename, strlen($documentRoot) + 1);
    return !in_array($relativePath, ['index.php', 'allerte/index.php'], true)
        && !str_starts_with($relativePath, 'news/');
}
