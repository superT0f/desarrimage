<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMigrationGuides extends AbstractMigration
{
    public function change(): void
    {
        $this->table('migration_guides')
            ->addColumn('source_product_id', 'integer', ['signed' => false])
            ->addColumn('slug', 'string', ['limit' => 120])
            ->addColumn('title', 'string', ['limit' => 180])
            ->addColumn('summary', 'text')
            ->addColumn('users', 'integer', ['signed' => false])
            ->addColumn('data_gb', 'integer', ['signed' => false])
            ->addColumn('shared_drives', 'integer', ['signed' => false])
            ->addColumn('native_documents_percentage', 'integer', ['signed' => false])
            ->addColumn('window_days', 'integer', ['signed' => false])
            ->addColumn('day_rate_cents', 'integer', ['signed' => false])
            ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'EUR'])
            ->addColumn('tax_label', 'string', ['limit' => 20, 'default' => 'HT'])
            ->addColumn('status', 'enum', ['values' => ['draft', 'published'], 'default' => 'draft'])
            ->addColumn('verified_at', 'date', ['null' => true])
            ->addColumn('updated_at', 'date')
            ->addIndex(['source_product_id'], ['unique' => true])
            ->addIndex(['slug'], ['unique' => true])
            ->addForeignKey('source_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_comparison_criteria')
            ->addColumn('guide_id', 'integer', ['signed' => false])
            ->addColumn('position', 'integer')
            ->addColumn('name', 'string', ['limit' => 180])
            ->addIndex(['guide_id', 'position'], ['unique' => true])
            ->addForeignKey('guide_id', 'migration_guides', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_comparison_values')
            ->addColumn('criterion_id', 'integer', ['signed' => false])
            ->addColumn('target_product_id', 'integer', ['signed' => false])
            ->addColumn('content', 'text')
            ->addIndex(['criterion_id', 'target_product_id'], ['unique' => true])
            ->addIndex(['target_product_id'])
            ->addForeignKey('criterion_id', 'guide_comparison_criteria', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('target_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_costs')
            ->addColumn('guide_id', 'integer', ['signed' => false])
            ->addColumn('target_product_id', 'integer', ['signed' => false])
            ->addColumn('recurring_monthly_cents', 'integer', ['signed' => false])
            ->addColumn('recurring_label', 'string', ['limit' => 255])
            ->addColumn('migration_days', 'decimal', ['precision' => 5, 'scale' => 1, 'signed' => false])
            ->addColumn('annual_operation_days', 'decimal', ['precision' => 5, 'scale' => 1, 'signed' => false])
            ->addColumn('notes', 'text', ['null' => true])
            ->addIndex(['guide_id', 'target_product_id'], ['unique' => true])
            ->addIndex(['target_product_id'])
            ->addForeignKey('guide_id', 'migration_guides', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('target_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_assumptions')
            ->addColumn('guide_id', 'integer', ['signed' => false])
            ->addColumn('position', 'integer')
            ->addColumn('content', 'text')
            ->addIndex(['guide_id', 'position'], ['unique' => true])
            ->addForeignKey('guide_id', 'migration_guides', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_risks')
            ->addColumn('guide_id', 'integer', ['signed' => false])
            ->addColumn('target_product_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('position', 'integer')
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('probability', 'enum', ['values' => ['faible', 'moyenne', 'elevee']])
            ->addColumn('impact', 'enum', ['values' => ['faible', 'moyen', 'eleve']])
            ->addColumn('mitigation', 'text')
            ->addIndex(['guide_id', 'position'], ['unique' => true])
            ->addIndex(['target_product_id'])
            ->addForeignKey('guide_id', 'migration_guides', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('target_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_timeline')
            ->addColumn('guide_id', 'integer', ['signed' => false])
            ->addColumn('position', 'integer')
            ->addColumn('phase', 'string', ['limit' => 180])
            ->addColumn('start_day', 'integer', ['signed' => false])
            ->addColumn('duration_days', 'integer', ['signed' => false])
            ->addColumn('description', 'text')
            ->addIndex(['guide_id', 'position'], ['unique' => true])
            ->addForeignKey('guide_id', 'migration_guides', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_decisions')
            ->addColumn('guide_id', 'integer', ['signed' => false])
            ->addColumn('target_product_id', 'integer', ['signed' => false])
            ->addColumn('recommendation', 'text')
            ->addIndex(['guide_id', 'target_product_id'], ['unique' => true])
            ->addIndex(['target_product_id'])
            ->addForeignKey('guide_id', 'migration_guides', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('target_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('guide_sources')
            ->addColumn('guide_id', 'integer', ['signed' => false])
            ->addColumn('target_product_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('position', 'integer')
            ->addColumn('kind', 'enum', ['values' => ['documentation', 'pricing', 'migration', 'security'], 'default' => 'documentation'])
            ->addColumn('label', 'string', ['limit' => 180])
            ->addColumn('url', 'string', ['limit' => 500])
            ->addIndex(['guide_id', 'position'], ['unique' => true])
            ->addIndex(['target_product_id'])
            ->addForeignKey('guide_id', 'migration_guides', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('target_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
