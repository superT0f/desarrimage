<?php

declare(strict_types=1);

use Desarrimage\Env;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->safeLoad();

return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => Env::get('DESARRIMAGE_DB_HOST', '127.0.0.1'),
            'port' => Env::get('DESARRIMAGE_DB_PORT', '3306'),
            'name' => Env::get('DESARRIMAGE_DB_NAME', 'desarrimage'),
            'user' => Env::get('DESARRIMAGE_DB_USER', 'desarrimage'),
            'pass' => Env::get('DESARRIMAGE_DB_PASS', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    'version_order' => 'creation',
];
