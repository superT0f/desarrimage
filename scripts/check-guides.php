<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$catalog = json_decode(file_get_contents($root . '/data/catalog.json'), true, 512, JSON_THROW_ON_ERROR);
$products = array_column($catalog['products'], null, 'slug');
$targetsBySource = [];
foreach ($catalog['migrations'] as $migration) {
    $targetsBySource[$migration['source']][$migration['target']] = true;
}

$required = [
    'slug', 'source', 'title', 'summary', 'users', 'data_gb', 'shared_drives',
    'native_documents_percentage', 'window_days', 'day_rate_cents', 'currency',
    'tax_label', 'status', 'verified_at', 'updated_at', 'verification_status',
    'tested_by', 'tested_at', 'evidence_url', 'reviewed_by', 'price_checked_at', 'comparison', 'costs',
    'assumptions', 'risks', 'timeline', 'decisions', 'sources',
];
$slugs = [];
$guideCount = 0;

foreach (glob($root . '/data/guides/*.json') ?: [] as $file) {
    $guide = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $guideCount++;
    foreach ($required as $key) {
        if (!array_key_exists($key, $guide)) {
            throw new RuntimeException("{$file} : champ manquant {$key}");
        }
    }
    if (isset($slugs[$guide['slug']])) {
        throw new RuntimeException("{$file} : slug dupliqué {$guide['slug']}");
    }
    $slugs[$guide['slug']] = true;
    if (!isset($products[$guide['source']])) {
        throw new RuntimeException("{$file} : source inconnue {$guide['source']}");
    }
    if (!in_array($guide['status'], ['draft', 'published'], true)) {
        throw new RuntimeException("{$file} : statut invalide");
    }
    if (!in_array($guide['verification_status'], ['draft', 'documented', 'tested', 'verified'], true)) {
        throw new RuntimeException("{$file} : niveau de vérification invalide");
    }
    if ($guide['currency'] !== 'EUR' || $guide['day_rate_cents'] <= 0 || $guide['window_days'] <= 0) {
        throw new RuntimeException("{$file} : scénario financier invalide");
    }

    $targets = $targetsBySource[$guide['source']] ?? [];
    if ($targets === []) {
        throw new RuntimeException("{$file} : aucune destination dans le catalogue");
    }
    $assertTargets = static function (array $slugsToCheck, string $section) use ($targets, $file): void {
        $unknown = array_diff($slugsToCheck, array_keys($targets));
        if ($unknown !== []) {
            throw new RuntimeException("{$file} : destination inconnue dans {$section} : " . implode(', ', $unknown));
        }
    };
    $assertTargets(array_keys($guide['costs']), 'costs');
    $assertTargets(array_keys($guide['decisions']), 'decisions');

    foreach ($guide['comparison'] as $row) {
        if (!isset($row['criterion'], $row['values']) || !is_array($row['values'])) {
            throw new RuntimeException("{$file} : ligne de comparaison invalide");
        }
        $assertTargets(array_keys($row['values']), 'comparison');
    }
    foreach ($guide['costs'] as $slug => $cost) {
        if (!isset($cost['recurring_monthly_cents'], $cost['recurring_label'], $cost['migration_days'], $cost['annual_operation_days'], $cost['notes'])
            || $cost['recurring_monthly_cents'] < 0
            || !is_numeric($cost['migration_days'])
            || !is_numeric($cost['annual_operation_days'])
            || $cost['migration_days'] < 0
            || $cost['annual_operation_days'] < 0) {
            throw new RuntimeException("{$file} : coût invalide pour {$slug}");
        }
    }
    foreach ($guide['sources'] as $source) {
        if (!isset($source['kind'], $source['label'], $source['url']) || !str_starts_with($source['url'], 'https://')) {
            throw new RuntimeException("{$file} : source invalide");
        }
        if (isset($source['target'])) {
            $assertTargets([$source['target']], 'sources');
        }
    }
}

echo "{$guideCount} guide(s) validé(s).\n";
