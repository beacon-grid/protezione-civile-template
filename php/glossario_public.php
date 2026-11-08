<?php

require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');

$type = $_GET['type'] ?? '';
if ($type === 'entries') {
    echo json_encode(app_glossary_load_entries(), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($type === 'settings') {
    echo json_encode(app_glossary_load_settings(), JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Tipo di dato non valido.'], JSON_UNESCAPED_UNICODE);
