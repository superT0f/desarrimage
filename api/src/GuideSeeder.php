<?php

declare(strict_types=1);

namespace Desarrimage;

use InvalidArgumentException;
use PDO;

final class GuideSeeder
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function seed(array $guide, array $productIds): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO migration_guides
                (source_product_id, slug, title, summary, users, data_gb, shared_drives,
                native_documents_percentage, window_days, day_rate_cents, currency, tax_label,
                status, verified_at, updated_at, verification_status, tested_by, tested_at,
                evidence_url, reviewed_by, price_checked_at)
            VALUES
                (:source, :slug, :title, :summary, :users, :data_gb, :shared_drives,
                :native_documents_percentage, :window_days, :day_rate_cents, :currency, :tax_label,
                :status, :verified_at, :updated_at, :verification_status, :tested_by, :tested_at,
                :evidence_url, :reviewed_by, :price_checked_at)
            ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary),
                users = VALUES(users), data_gb = VALUES(data_gb), shared_drives = VALUES(shared_drives),
                native_documents_percentage = VALUES(native_documents_percentage),
                window_days = VALUES(window_days), day_rate_cents = VALUES(day_rate_cents),
                currency = VALUES(currency), tax_label = VALUES(tax_label), status = VALUES(status),
                verified_at = VALUES(verified_at), updated_at = VALUES(updated_at),
                verification_status = VALUES(verification_status), tested_by = VALUES(tested_by),
                tested_at = VALUES(tested_at), evidence_url = VALUES(evidence_url),
                reviewed_by = VALUES(reviewed_by), price_checked_at = VALUES(price_checked_at)'
        );
        $statement->execute([
            'source' => $this->productId($guide['source'], $productIds),
            'slug' => $guide['slug'],
            'title' => $guide['title'],
            'summary' => $guide['summary'],
            'users' => $guide['users'],
            'data_gb' => $guide['data_gb'],
            'shared_drives' => $guide['shared_drives'],
            'native_documents_percentage' => $guide['native_documents_percentage'],
            'window_days' => $guide['window_days'],
            'day_rate_cents' => $guide['day_rate_cents'],
            'currency' => $guide['currency'],
            'tax_label' => $guide['tax_label'],
            'status' => $guide['status'],
            'verified_at' => $guide['verified_at'],
            'updated_at' => $guide['updated_at'],
            'verification_status' => $guide['verification_status'],
            'tested_by' => $guide['tested_by'],
            'tested_at' => $guide['tested_at'],
            'evidence_url' => $guide['evidence_url'],
            'reviewed_by' => $guide['reviewed_by'],
            'price_checked_at' => $guide['price_checked_at'],
        ]);

        $findGuide = $this->database->prepare('SELECT id FROM migration_guides WHERE slug = :slug');
        $findGuide->execute(['slug' => $guide['slug']]);
        $guideId = (int) $findGuide->fetchColumn();

        foreach (['guide_assumptions', 'guide_costs', 'guide_decisions', 'guide_risks', 'guide_sources', 'guide_timeline', 'guide_comparison_criteria'] as $table) {
            $this->database->prepare("DELETE FROM {$table} WHERE guide_id = :guide")
                ->execute(['guide' => $guideId]);
        }

        $this->seedComparisons($guideId, $guide['comparison'], $productIds);
        $this->seedCosts($guideId, $guide['costs'], $productIds);
        $this->seedAssumptions($guideId, $guide['assumptions']);
        $this->seedRisks($guideId, $guide['risks'], $productIds);
        $this->seedTimeline($guideId, $guide['timeline']);
        $this->seedDecisions($guideId, $guide['decisions'], $productIds);
        $this->seedSources($guideId, $guide['sources'], $productIds);
    }

    private function seedComparisons(int $guideId, array $rows, array $productIds): void
    {
        $criterionStatement = $this->database->prepare(
            'INSERT INTO guide_comparison_criteria (guide_id, position, name) VALUES (:guide, :position, :name)'
        );
        $valueStatement = $this->database->prepare(
            'INSERT INTO guide_comparison_values (criterion_id, target_product_id, content)
            VALUES (:criterion, :target, :content)'
        );

        foreach ($rows as $index => $row) {
            $criterionStatement->execute(['guide' => $guideId, 'position' => $index + 1, 'name' => $row['criterion']]);
            $criterionId = (int) $this->database->lastInsertId();
            foreach ($row['values'] as $slug => $content) {
                $valueStatement->execute([
                    'criterion' => $criterionId,
                    'target' => $this->productId($slug, $productIds),
                    'content' => $content,
                ]);
            }
        }
    }

    private function seedCosts(int $guideId, array $costs, array $productIds): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO guide_costs
                (guide_id, target_product_id, recurring_monthly_cents, recurring_label,
                migration_days, annual_operation_days, notes)
            VALUES (:guide, :target, :monthly, :label, :migration_days, :operation_days, :notes)'
        );
        foreach ($costs as $slug => $cost) {
            $statement->execute([
                'guide' => $guideId,
                'target' => $this->productId($slug, $productIds),
                'monthly' => $cost['recurring_monthly_cents'],
                'label' => $cost['recurring_label'],
                'migration_days' => $cost['migration_days'],
                'operation_days' => $cost['annual_operation_days'],
                'notes' => $cost['notes'],
            ]);
        }
    }

    private function seedAssumptions(int $guideId, array $assumptions): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO guide_assumptions (guide_id, position, content) VALUES (:guide, :position, :content)'
        );
        foreach ($assumptions as $index => $content) {
            $statement->execute(['guide' => $guideId, 'position' => $index + 1, 'content' => $content]);
        }
    }

    private function seedRisks(int $guideId, array $risks, array $productIds): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO guide_risks
                (guide_id, target_product_id, position, title, probability, impact, mitigation)
            VALUES (:guide, :target, :position, :title, :probability, :impact, :mitigation)'
        );
        foreach ($risks as $index => $risk) {
            $statement->execute([
                'guide' => $guideId,
                'target' => isset($risk['target']) ? $this->productId($risk['target'], $productIds) : null,
                'position' => $index + 1,
                'title' => $risk['title'],
                'probability' => $risk['probability'],
                'impact' => $risk['impact'],
                'mitigation' => $risk['mitigation'],
            ]);
        }
    }

    private function seedTimeline(int $guideId, array $timeline): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO guide_timeline
                (guide_id, position, phase, start_day, duration_days, description)
            VALUES (:guide, :position, :phase, :start_day, :duration_days, :description)'
        );
        foreach ($timeline as $index => $phase) {
            $statement->execute([
                'guide' => $guideId,
                'position' => $index + 1,
                'phase' => $phase['phase'],
                'start_day' => $phase['start_day'],
                'duration_days' => $phase['duration_days'],
                'description' => $phase['description'],
            ]);
        }
    }

    private function seedDecisions(int $guideId, array $decisions, array $productIds): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO guide_decisions (guide_id, target_product_id, recommendation)
            VALUES (:guide, :target, :recommendation)'
        );
        foreach ($decisions as $slug => $recommendation) {
            $statement->execute([
                'guide' => $guideId,
                'target' => $this->productId($slug, $productIds),
                'recommendation' => $recommendation,
            ]);
        }
    }

    private function seedSources(int $guideId, array $sources, array $productIds): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO guide_sources (guide_id, target_product_id, position, kind, label, url)
            VALUES (:guide, :target, :position, :kind, :label, :url)'
        );
        foreach ($sources as $index => $source) {
            $statement->execute([
                'guide' => $guideId,
                'target' => isset($source['target']) ? $this->productId($source['target'], $productIds) : null,
                'position' => $index + 1,
                'kind' => $source['kind'],
                'label' => $source['label'],
                'url' => $source['url'],
            ]);
        }
    }

    private function productId(string $slug, array $productIds): int
    {
        if (!isset($productIds[$slug])) {
            throw new InvalidArgumentException("Produit inconnu dans le guide : {$slug}");
        }

        return $productIds[$slug];
    }
}
