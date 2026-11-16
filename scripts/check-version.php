<?php

declare(strict_types=1);

$version = $argv[1] ?? null;
$pattern = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?(?:\+[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?$/';

if ($version === null || preg_match($pattern, $version) !== 1) {
    fwrite(STDERR, "Version SemVer invalide. Exemple attendu : 0.2.0, 1.0.0-rc.1.\n");
    exit(1);
}

if (($argv[2] ?? null) === '--validate-only') {
    echo "Version {$version} valide.\n";
    exit(0);
}

$root = dirname(__DIR__);
$package = json_decode(file_get_contents($root . '/client/package.json'), true, 512, JSON_THROW_ON_ERROR);
$lock = json_decode(file_get_contents($root . '/client/package-lock.json'), true, 512, JSON_THROW_ON_ERROR);

if ($package['version'] !== $version || $lock['version'] !== $version || $lock['packages']['']['version'] !== $version) {
    fwrite(STDERR, "Version désynchronisée entre .version, package.json et package-lock.json.\n");
    exit(1);
}

echo "Version {$version} synchronisée.\n";
