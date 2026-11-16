<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGuideVerification extends AbstractMigration
{
    public function change(): void
    {
        $this->table('migration_guides')
            ->addColumn('verification_status', 'enum', ['values' => ['draft', 'documented', 'tested', 'verified'], 'default' => 'documented'])
            ->addColumn('tested_by', 'string', ['limit' => 180, 'null' => true])
            ->addColumn('tested_at', 'date', ['null' => true])
            ->addColumn('evidence_url', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('reviewed_by', 'string', ['limit' => 180, 'null' => true])
            ->addColumn('price_checked_at', 'date', ['null' => true])
            ->update();
    }
}
