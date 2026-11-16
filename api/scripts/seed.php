<?php

declare(strict_types=1);

use Desarrimage\Database;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
$catalogPath = dirname(__DIR__, 2) . '/data/catalog.json';
$catalog = json_decode(file_get_contents($catalogPath), true, 512, JSON_THROW_ON_ERROR);
$database = Database::connect();
$database->beginTransaction();

try {
    $categoryStatement = $database->prepare(
        'INSERT INTO categories (slug, name, description) VALUES (:slug, :name, :description)
        ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description)'
    );
    $categoryIds = [];
    foreach ($catalog['categories'] as $category) {
        $categoryStatement->execute($category);
        $statement = $database->prepare('SELECT id FROM categories WHERE slug = :slug');
        $statement->execute(['slug' => $category['slug']]);
        $categoryIds[$category['slug']] = (int) $statement->fetchColumn();
    }

    $productStatement = $database->prepare(
        'INSERT INTO products
            (category_id, slug, name, vendor, country_code, country_name, region, description, website, pricing, license_name)
        VALUES
            (:category_id, :slug, :name, :vendor, :country_code, :country_name, :region, :description, :website, :pricing, :license_name)
        ON DUPLICATE KEY UPDATE category_id = VALUES(category_id), name = VALUES(name), vendor = VALUES(vendor),
            country_code = VALUES(country_code), country_name = VALUES(country_name), region = VALUES(region),
            description = VALUES(description), website = VALUES(website), pricing = VALUES(pricing),
            license_name = VALUES(license_name)'
    );
    $productIds = [];
    $findProduct = $database->prepare('SELECT id FROM products WHERE slug = :slug');
    foreach ($catalog['products'] as $product) {
        $product['category_id'] = $categoryIds[$product['category']];
        unset($product['category']);
        $productStatement->execute($product);
        $findProduct->execute(['slug' => $product['slug']]);
        $productIds[$product['slug']] = (int) $findProduct->fetchColumn();
    }

    $migrationStatement = $database->prepare(
        'INSERT INTO migrations
            (source_product_id, target_product_id, difficulty, duration, summary, verified_at)
        VALUES (:source, :target, :difficulty, :duration, :summary, :verified_at)
        ON DUPLICATE KEY UPDATE difficulty = VALUES(difficulty), duration = VALUES(duration),
            summary = VALUES(summary), verified_at = VALUES(verified_at)'
    );
    $findMigration = $database->prepare(
        'SELECT id FROM migrations WHERE source_product_id = :source AND target_product_id = :target'
    );

    foreach ($catalog['migrations'] as $migration) {
        $ids = [
            'source' => $productIds[$migration['source']],
            'target' => $productIds[$migration['target']],
        ];
        $migrationStatement->execute([
            ...$ids,
            'difficulty' => $migration['difficulty'],
            'duration' => $migration['duration'],
            'summary' => $migration['summary'],
            'verified_at' => $migration['verified_at'],
        ]);
        $findMigration->execute($ids);
        $migrationId = (int) $findMigration->fetchColumn();

        foreach (['migration_comparisons', 'migration_steps', 'sources'] as $table) {
            $database->prepare("DELETE FROM {$table} WHERE migration_id = :migration")
                ->execute(['migration' => $migrationId]);
        }

        $comparisonStatement = $database->prepare(
            'INSERT INTO migration_comparisons (migration_id, kind, position, content)
            VALUES (:migration, :kind, :position, :content)'
        );
        foreach (['advantages' => 'advantage', 'disadvantages' => 'disadvantage'] as $key => $kind) {
            foreach ($migration[$key] as $index => $content) {
                $comparisonStatement->execute([
                    'migration' => $migrationId,
                    'kind' => $kind,
                    'position' => $index + 1,
                    'content' => $content,
                ]);
            }
        }

        $stepStatement = $database->prepare(
            'INSERT INTO migration_steps (migration_id, position, title, body)
            VALUES (:migration, :position, :title, :body)'
        );
        foreach ($migration['steps'] as $index => $step) {
            $stepStatement->execute([
                'migration' => $migrationId,
                'position' => $index + 1,
                'title' => $step['title'],
                'body' => $step['body'],
            ]);
        }

        $sourceStatement = $database->prepare(
            'INSERT INTO sources (migration_id, label, url) VALUES (:migration, :label, :url)'
        );
        foreach ($migration['sources'] as $source) {
            $sourceStatement->execute(['migration' => $migrationId, ...$source]);
        }
    }

    $database->commit();
    printf(
        "Catalogue importé : %d catégories, %d produits, %d migrations.\n",
        count($catalog['categories']),
        count($catalog['products']),
        count($catalog['migrations'])
    );
} catch (Throwable $exception) {
    $database->rollBack();
    throw $exception;
}
