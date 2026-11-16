<?php

declare(strict_types=1);

namespace Desarrimage;

use PDO;

final class CatalogRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function stats(): array
    {
        return $this->database->query(
            "SELECT
                (SELECT COUNT(*) FROM products WHERE region = 'other') AS source_products,
                (SELECT COUNT(*) FROM products WHERE region != 'other') AS alternatives,
                (SELECT COUNT(*) FROM migrations) AS migrations,
                (SELECT COUNT(*) FROM categories) AS categories"
        )->fetch();
    }

    public function categories(): array
    {
        return $this->database->query(
            'SELECT id, slug, name, description FROM categories ORDER BY name'
        )->fetchAll();
    }

    public function products(?string $search, ?string $category, ?string $region): array
    {
        $conditions = [];
        $parameters = [];

        if ($search !== null && $search !== '') {
            $conditions[] = '(p.name LIKE :search_name OR p.vendor LIKE :search_vendor OR p.description LIKE :search_description)';
            $parameters['search_name'] = '%' . $search . '%';
            $parameters['search_vendor'] = '%' . $search . '%';
            $parameters['search_description'] = '%' . $search . '%';
        }
        if ($category !== null && $category !== '') {
            $conditions[] = 'c.slug = :category';
            $parameters['category'] = $category;
        }
        if (in_array($region, ['fr', 'europe', 'other'], true)) {
            $conditions[] = 'p.region = :region';
            $parameters['region'] = $region;
        }

        $where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $statement = $this->database->prepare(
            "SELECT p.id, p.slug, p.name, p.vendor, p.country_code, p.country_name,
                p.region, p.description, p.website, p.pricing, p.license_name,
                c.slug AS category_slug, c.name AS category_name,
                COUNT(m.id) AS alternative_count
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN migrations m ON m.source_product_id = p.id
            {$where}
            GROUP BY p.id, c.slug, c.name
            ORDER BY p.region = 'other' DESC, alternative_count DESC, p.name"
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function product(string $slug): ?array
    {
        $statement = $this->database->prepare(
            'SELECT p.*, c.slug AS category_slug, c.name AS category_name
            FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug = :slug'
        );
        $statement->execute(['slug' => $slug]);
        $product = $statement->fetch();

        if ($product === false) {
            return null;
        }

        $statement = $this->database->prepare(
            'SELECT m.id, m.difficulty, m.duration, m.summary, m.verified_at,
                t.slug AS target_slug, t.name AS target_name, t.vendor AS target_vendor,
                t.country_code AS target_country_code, t.country_name AS target_country_name,
                t.region AS target_region, t.description AS target_description,
                t.website AS target_website, t.pricing AS target_pricing,
                t.license_name AS target_license_name,
                c.slug AS target_category_slug, c.name AS target_category_name
            FROM migrations m
            JOIN products t ON t.id = m.target_product_id
            JOIN categories c ON c.id = t.category_id
            WHERE m.source_product_id = :source
            ORDER BY t.name'
        );
        $statement->execute(['source' => $product['id']]);
        $migrations = $statement->fetchAll();

        foreach ($migrations as &$migration) {
            $migration['target'] = [
                'slug' => $migration['target_slug'],
                'name' => $migration['target_name'],
                'vendor' => $migration['target_vendor'],
                'country_code' => $migration['target_country_code'],
                'country_name' => $migration['target_country_name'],
                'region' => $migration['target_region'],
                'description' => $migration['target_description'],
                'website' => $migration['target_website'],
                'pricing' => $migration['target_pricing'],
                'license_name' => $migration['target_license_name'],
                'category_slug' => $migration['target_category_slug'],
                'category_name' => $migration['target_category_name'],
            ];
            foreach (array_keys($migration) as $key) {
                if (str_starts_with($key, 'target_')) {
                    unset($migration[$key]);
                }
            }
            $migration['advantages'] = $this->comparisons((int) $migration['id'], 'advantage');
            $migration['disadvantages'] = $this->comparisons((int) $migration['id'], 'disadvantage');
            $migration['steps'] = $this->steps((int) $migration['id']);
            $migration['sources'] = $this->sources((int) $migration['id']);
        }
        unset($migration);

        $product['migrations'] = $migrations;

        return $product;
    }

    private function comparisons(int $migrationId, string $kind): array
    {
        $statement = $this->database->prepare(
            'SELECT content FROM migration_comparisons
            WHERE migration_id = :migration AND kind = :kind ORDER BY position'
        );
        $statement->execute(['migration' => $migrationId, 'kind' => $kind]);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function steps(int $migrationId): array
    {
        $statement = $this->database->prepare(
            'SELECT position, title, body FROM migration_steps
            WHERE migration_id = :migration ORDER BY position'
        );
        $statement->execute(['migration' => $migrationId]);

        return $statement->fetchAll();
    }

    private function sources(int $migrationId): array
    {
        $statement = $this->database->prepare(
            'SELECT label, url FROM sources WHERE migration_id = :migration ORDER BY id'
        );
        $statement->execute(['migration' => $migrationId]);

        return $statement->fetchAll();
    }
}
