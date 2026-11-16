<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$catalog = json_decode(file_get_contents($root . '/data/catalog.json'), true, 512, JSON_THROW_ON_ERROR);
$categories = array_column($catalog['categories'], 'name', 'slug');
$counts = array_count_values(array_column($catalog['migrations'], 'source'));
$products = [];

foreach ($catalog['products'] as $product) {
    if ($product['region'] !== 'other') {
        continue;
    }
    $product['category_name'] = $categories[$product['category']];
    $product['category_slug'] = $product['category'];
    $product['alternative_count'] = $counts[$product['slug']] ?? 0;
    unset($product['category']);
    $products[] = $product;
}

$snapshot = [
    'stats' => [
        'source_products' => count($products),
        'alternatives' => count(array_filter($catalog['products'], fn (array $product): bool => $product['region'] !== 'other')),
        'migrations' => count($catalog['migrations']),
        'categories' => count($catalog['categories']),
    ],
    'products' => $products,
];

$destination = $root . '/client/public/catalog-snapshot.json';
file_put_contents(
    $destination,
    json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL
);
echo "Instantané généré dans {$destination}.\n";
