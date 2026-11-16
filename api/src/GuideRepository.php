<?php

declare(strict_types=1);

namespace Desarrimage;

use PDO;

final class GuideRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function summaries(): array
    {
        $guides = $this->database->query(
            "SELECT g.id, g.slug, g.title, g.summary, g.users, g.data_gb, g.updated_at,
                p.slug AS source_slug, p.name AS source_name, p.vendor AS source_vendor
            FROM migration_guides g
            JOIN products p ON p.id = g.source_product_id
            WHERE g.status = 'published'
            ORDER BY g.updated_at DESC, g.title"
        )->fetchAll();

        foreach ($guides as &$guide) {
            $guide['source'] = [
                'slug' => $guide['source_slug'],
                'name' => $guide['source_name'],
                'vendor' => $guide['source_vendor'],
            ];
            unset($guide['source_slug'], $guide['source_name'], $guide['source_vendor']);
            $guide['targets'] = $this->targets((int) $guide['id']);
        }
        unset($guide);

        return $guides;
    }

    public function find(string $slug): ?array
    {
        $statement = $this->database->prepare(
            "SELECT g.*, p.slug AS source_slug, p.name AS source_name,
                p.vendor AS source_vendor, p.website AS source_website
            FROM migration_guides g
            JOIN products p ON p.id = g.source_product_id
            WHERE g.slug = :slug AND g.status = 'published'"
        );
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        $guideId = (int) $row['id'];
        $dayRate = (int) $row['day_rate_cents'];
        $guide = [
            'slug' => $row['slug'],
            'title' => $row['title'],
            'summary' => $row['summary'],
            'updated_at' => $row['updated_at'],
            'verified_at' => $row['verified_at'],
            'verification' => [
                'status' => $row['verification_status'],
                'tested_by' => $row['tested_by'],
                'tested_at' => $row['tested_at'],
                'evidence_url' => $row['evidence_url'],
                'reviewed_by' => $row['reviewed_by'],
                'price_checked_at' => $row['price_checked_at'],
            ],
            'source' => [
                'slug' => $row['source_slug'],
                'name' => $row['source_name'],
                'vendor' => $row['source_vendor'],
                'website' => $row['source_website'],
            ],
            'scenario' => [
                'users' => (int) $row['users'],
                'data_gb' => (int) $row['data_gb'],
                'shared_drives' => (int) $row['shared_drives'],
                'native_documents_percentage' => (int) $row['native_documents_percentage'],
                'window_days' => (int) $row['window_days'],
                'day_rate_cents' => $dayRate,
                'currency' => $row['currency'],
                'tax_label' => $row['tax_label'],
            ],
        ];
        $guide['targets'] = $this->targets($guideId);
        $guide['comparison'] = $this->comparison($guideId);
        $guide['costs'] = $this->costs($guideId, $dayRate);
        $guide['assumptions'] = $this->assumptions($guideId);
        $guide['risks'] = $this->risks($guideId);
        $guide['timeline'] = $this->timeline($guideId);
        $guide['decisions'] = $this->decisions($guideId);
        $guide['sources'] = $this->sources($guideId);

        return $guide;
    }

    private function targets(int $guideId): array
    {
        $statement = $this->database->prepare(
            'SELECT t.slug, t.name, t.vendor, t.country_code, t.country_name, t.region, t.website
            FROM migration_guides g
            JOIN migrations m ON m.source_product_id = g.source_product_id
            JOIN products t ON t.id = m.target_product_id
            WHERE g.id = :guide ORDER BY t.name'
        );
        $statement->execute(['guide' => $guideId]);

        return $statement->fetchAll();
    }

    private function comparison(int $guideId): array
    {
        $statement = $this->database->prepare(
            'SELECT id, position, name AS criterion FROM guide_comparison_criteria
            WHERE guide_id = :guide ORDER BY position'
        );
        $statement->execute(['guide' => $guideId]);
        $criteria = $statement->fetchAll();

        $valueStatement = $this->database->prepare(
            'SELECT v.content, p.slug AS target_slug
            FROM guide_comparison_values v
            JOIN products p ON p.id = v.target_product_id
            WHERE v.criterion_id = :criterion'
        );
        foreach ($criteria as &$criterion) {
            $valueStatement->execute(['criterion' => $criterion['id']]);
            $criterion['values'] = [];
            foreach ($valueStatement->fetchAll() as $value) {
                $criterion['values'][$value['target_slug']] = $value['content'];
            }
            unset($criterion['id']);
        }
        unset($criterion);

        return $criteria;
    }

    private function costs(int $guideId, int $dayRate): array
    {
        $statement = $this->database->prepare(
            'SELECT c.*, p.slug AS target_slug, p.name AS target_name
            FROM guide_costs c JOIN products p ON p.id = c.target_product_id
            WHERE c.guide_id = :guide ORDER BY p.name'
        );
        $statement->execute(['guide' => $guideId]);
        $costs = [];

        foreach ($statement->fetchAll() as $row) {
            $migrationCost = (int) round((float) $row['migration_days'] * $dayRate);
            $operationCost = (int) round((float) $row['annual_operation_days'] * $dayRate);
            $monthly = (int) $row['recurring_monthly_cents'];
            $costs[] = [
                'target' => ['slug' => $row['target_slug'], 'name' => $row['target_name']],
                'recurring_monthly_cents' => $monthly,
                'recurring_label' => $row['recurring_label'],
                'migration_days' => (float) $row['migration_days'],
                'annual_operation_days' => (float) $row['annual_operation_days'],
                'migration_cost_cents' => $migrationCost,
                'annual_operation_cost_cents' => $operationCost,
                'year_one_total_cents' => $monthly * 12 + $migrationCost + $operationCost,
                'year_two_total_cents' => $monthly * 12 + $operationCost,
                'notes' => $row['notes'],
            ];
        }

        return $costs;
    }

    private function assumptions(int $guideId): array
    {
        $statement = $this->database->prepare(
            'SELECT content FROM guide_assumptions WHERE guide_id = :guide ORDER BY position'
        );
        $statement->execute(['guide' => $guideId]);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function risks(int $guideId): array
    {
        $statement = $this->database->prepare(
            'SELECT r.*, p.slug AS target_slug, p.name AS target_name
            FROM guide_risks r
            LEFT JOIN products p ON p.id = r.target_product_id
            WHERE r.guide_id = :guide ORDER BY r.position'
        );
        $statement->execute(['guide' => $guideId]);
        $risks = [];

        foreach ($statement->fetchAll() as $row) {
            $probability = ['faible' => 1, 'moyenne' => 2, 'elevee' => 3][$row['probability']];
            $impact = ['faible' => 1, 'moyen' => 2, 'eleve' => 3][$row['impact']];
            $score = $probability * $impact;
            $risks[] = [
                'target' => $row['target_slug'] !== null
                    ? ['slug' => $row['target_slug'], 'name' => $row['target_name']]
                    : null,
                'title' => $row['title'],
                'probability' => $row['probability'],
                'impact' => $row['impact'],
                'score' => $score,
                'level' => $score >= 6 ? 'eleve' : ($score >= 3 ? 'modere' : 'faible'),
                'mitigation' => $row['mitigation'],
            ];
        }

        return $risks;
    }

    private function timeline(int $guideId): array
    {
        $statement = $this->database->prepare(
            'SELECT position, phase, start_day, duration_days, description
            FROM guide_timeline WHERE guide_id = :guide ORDER BY position'
        );
        $statement->execute(['guide' => $guideId]);

        return $statement->fetchAll();
    }

    private function decisions(int $guideId): array
    {
        $statement = $this->database->prepare(
            'SELECT d.recommendation, p.slug AS target_slug, p.name AS target_name, p.region AS target_region
            FROM guide_decisions d JOIN products p ON p.id = d.target_product_id
            WHERE d.guide_id = :guide ORDER BY p.name'
        );
        $statement->execute(['guide' => $guideId]);
        $decisions = [];

        foreach ($statement->fetchAll() as $row) {
            $decisions[] = [
                'target' => ['slug' => $row['target_slug'], 'name' => $row['target_name'], 'region' => $row['target_region']],
                'recommendation' => $row['recommendation'],
            ];
        }

        return $decisions;
    }

    private function sources(int $guideId): array
    {
        $statement = $this->database->prepare(
            'SELECT s.kind, s.label, s.url, p.slug AS target_slug, p.name AS target_name
            FROM guide_sources s
            LEFT JOIN products p ON p.id = s.target_product_id
            WHERE s.guide_id = :guide ORDER BY s.position'
        );
        $statement->execute(['guide' => $guideId]);
        $sources = [];

        foreach ($statement->fetchAll() as $row) {
            $sources[] = [
                'target' => $row['target_slug'] !== null
                    ? ['slug' => $row['target_slug'], 'name' => $row['target_name']]
                    : null,
                'kind' => $row['kind'],
                'label' => $row['label'],
                'url' => $row['url'],
            ];
        }

        return $sources;
    }
}
