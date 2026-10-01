<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Bootstrap;

use BpKnowledgeTests\Infrastructure\SchemaDefiner;
use BpKnowledgeTests\Infrastructure\SchemaVersionRepository;
use BpKnowledgeTests\Infrastructure\Seeder;
use BpKnowledgeTests\Plugin;

final class PluginBootstrap
{
    public function __construct(private \wpdb $wpdb)
    {
    }

    public function compose(): Plugin
    {
        return new Plugin();
    }

    public function activate(): void
    {
        $schemaVersion = new SchemaVersionRepository();
        $schemaDefiner = new SchemaDefiner($this->wpdb);
        $seeder = new Seeder($this->wpdb);

        $installed = $schemaVersion->current();
        if ($installed >= SchemaVersionRepository::TARGET_VERSION) {
            return;
        }

        if ($installed < 1) {
            $schemaDefiner->createTables();
            $seeder->seedDefaults();
        }

        if ($installed < 2) {
            $schemaDefiner->installForeignKeys();
        }

        $schemaVersion->markUpToDate();
    }
}
