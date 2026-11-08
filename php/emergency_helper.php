<?php

require_once __DIR__ . '/database.php';

function emergency_load_status(): array {
    $data = app_data_get('emergency_status', []);
    if (!is_array($data)) {
        return ['active' => false, 'type' => '', 'title' => '', 'description' => '', 'updated_at' => null];
    }
    return [
        'active' => !empty($data['active']),
        'type' => trim((string) ($data['type'] ?? '')),
        'title' => trim((string) ($data['title'] ?? '')),
        'description' => trim((string) ($data['description'] ?? '')),
        'updated_at' => $data['updated_at'] ?? null,
    ];
}

function emergency_save_status(array $status): bool {
    $payload = [
        'active' => !empty($status['active']),
        'type' => trim((string) ($status['type'] ?? '')),
        'title' => trim((string) ($status['title'] ?? '')),
        'description' => trim((string) ($status['description'] ?? '')),
        'updated_at' => $status['updated_at'] ?? date('Y-m-d H:i:s'),
    ];
    try {
        return app_data_set('emergency_status', $payload);
    } catch (Throwable $exception) {
        error_log('[emergency] Salvataggio dello stato non riuscito: ' . $exception->getMessage());
        return false;
    }
}

function emergency_register_bypass(): void {
    if (headers_sent()) {
        return;
    }

    setcookie('emergency_bypass', '1', [
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function emergency_bypass_requested(): bool {
    return ($_GET['emergency_bypass'] ?? '') === '1'
        || ($_COOKIE['emergency_bypass'] ?? '') === '1';
}

function emergency_should_redirect(): bool {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($path === '/emergency.php' || $path === '/emergency.html') return false;
    if (str_starts_with($path, '/admin/') || str_starts_with($path, '/data/') || str_starts_with($path, '/errors/')) return false;
    if (($_GET['emergency_bypass'] ?? '') === '1') {
        emergency_register_bypass();
        return false;
    }
    if (emergency_bypass_requested()) return false;
    return emergency_load_status()['active'];
}
