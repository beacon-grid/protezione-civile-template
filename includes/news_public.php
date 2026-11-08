<?php
require_once __DIR__ . '/../php/database.php';

function news_load(): array {
    return app_news_load();
}

function news_published(int $limit = 0): array {
    $items = array_filter(news_load(), fn($n) => !empty($n['pubblicato'] ?? false));

    usort($items, function ($a, $b) {
        return strtotime($b['data'] ?? '') <=> strtotime($a['data'] ?? '');
    });

    return $limit > 0 ? array_slice($items, 0, $limit) : $items;
}

function news_find($id): ?array {
    return app_news_find((string) $id);
}

function news_categoria_label(string $cat): string {
    return news_categorie()[$cat]['label'] ?? ucfirst($cat);
}

function news_categoria_color(string $cat): string {
    return news_categorie()[$cat]['color'] ?? 'secondary';
}

function news_categorie(): array {
    static $cats = null;

    if ($cats === null) {
        $cats = [
            'esercitazione' => ['label' => 'Esercitazione', 'color' => 'primary'],
            'prevenzione'   => ['label' => 'Prevenzione',   'color' => 'success'],
            'formazione'    => ['label' => 'Formazione',    'color' => 'info'],
            'allerta'       => ['label' => 'Allerta',       'color' => 'danger'],
            'evento'        => ['label' => 'Evento',        'color' => 'warning'],
            'comunicato'    => ['label' => 'Comunicato',    'color' => 'secondary'],
        ];
    }

    return $cats;
}