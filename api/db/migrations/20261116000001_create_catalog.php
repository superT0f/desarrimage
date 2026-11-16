<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCatalog extends AbstractMigration
{
    public function change(): void
    {
        $this->table('categories')
            ->addColumn('slug', 'string', ['limit' => 80])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('description', 'text')
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $this->table('products')
            ->addColumn('category_id', 'integer', ['signed' => false])
            ->addColumn('slug', 'string', ['limit' => 100])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('vendor', 'string', ['limit' => 120])
            ->addColumn('country_code', 'string', ['limit' => 2])
            ->addColumn('country_name', 'string', ['limit' => 80])
            ->addColumn('region', 'enum', ['values' => ['fr', 'europe', 'other']])
            ->addColumn('description', 'text')
            ->addColumn('website', 'string', ['limit' => 500])
            ->addColumn('pricing', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('license_name', 'string', ['limit' => 120, 'null' => true])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['category_id'])
            ->addForeignKey('category_id', 'categories', 'id', ['delete' => 'RESTRICT'])
            ->create();

        $this->table('migrations')
            ->addColumn('source_product_id', 'integer', ['signed' => false])
            ->addColumn('target_product_id', 'integer', ['signed' => false])
            ->addColumn('difficulty', 'enum', ['values' => ['facile', 'intermediaire', 'avancee']])
            ->addColumn('duration', 'string', ['limit' => 100])
            ->addColumn('summary', 'text')
            ->addColumn('verified_at', 'date', ['null' => true])
            ->addIndex(['source_product_id', 'target_product_id'], ['unique' => true])
            ->addIndex(['target_product_id'])
            ->addForeignKey('source_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('target_product_id', 'products', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('migration_steps')
            ->addColumn('migration_id', 'integer', ['signed' => false])
            ->addColumn('position', 'integer')
            ->addColumn('title', 'string', ['limit' => 180])
            ->addColumn('body', 'text')
            ->addIndex(['migration_id', 'position'], ['unique' => true])
            ->addForeignKey('migration_id', 'migrations', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('migration_comparisons')
            ->addColumn('migration_id', 'integer', ['signed' => false])
            ->addColumn('kind', 'enum', ['values' => ['advantage', 'disadvantage']])
            ->addColumn('position', 'integer')
            ->addColumn('content', 'text')
            ->addIndex(['migration_id', 'kind', 'position'], ['unique' => true])
            ->addForeignKey('migration_id', 'migrations', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('sources')
            ->addColumn('migration_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('label', 'string', ['limit' => 180])
            ->addColumn('url', 'string', ['limit' => 500])
            ->addIndex(['migration_id'])
            ->addForeignKey('migration_id', 'migrations', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
