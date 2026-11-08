<?php

require_once __DIR__ . '/database.php';

function glossary_load_entries(): array {
    return app_glossary_load_entries();
}

function glossary_save_entries(array $entries): bool {
    $normalized = [];
    foreach ($entries as $item) {
        if (!is_array($item)) {
            continue;
        }
        $term = trim((string)($item['termine'] ?? ''));
        $def = trim((string)($item['definizione'] ?? ''));
        if ($term === '' || $def === '') {
            continue;
        }
        $normalized[] = [
            'termine' => $term,
            'definizione' => $def,
        ];
    }

    return app_glossary_save_entries($normalized);
}

function glossary_load_settings(): array {
    return app_glossary_load_settings();
}

function glossary_save_settings(array $settings): bool {
    $payload = [
        'enabled' => !empty($settings['enabled']),
    ];

    try {
        return app_glossary_save_settings($payload);
    } catch (Throwable $exception) {
        error_log('[glossario] Salvataggio delle impostazioni non riuscito: ' . $exception->getMessage());
        return false;
    }
}
