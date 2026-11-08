<?php

function app_config(bool $required = true): array {
    static $config = null;
    if (is_array($config)) {
        return $config;
    }

    $dataConfig = dirname(__DIR__) . '/data/config.php';
    $legacyConfig = dirname(__DIR__) . '/config.php';
    $configPath = is_file($dataConfig)
        ? $dataConfig
        : (is_file($legacyConfig) ? $legacyConfig : null);

    if ($configPath === null) {
        if ($required) {
            throw new RuntimeException('Configurazione del sito non trovata in data/config.php.');
        }
        return [];
    }

    $loadedConfig = require $configPath;
    if (!is_array($loadedConfig)) {
        throw new UnexpectedValueException('Il file di configurazione deve restituire un array.');
    }

    $config = $loadedConfig;
    return $config;
}
