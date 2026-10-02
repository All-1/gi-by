<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Bootstrap;

use BpKnowledgeTests\Application\CatalogServices;
use BpKnowledgeTests\Infrastructure\SchemaDefiner;
use BpKnowledgeTests\Infrastructure\SchemaVersionRepository;
use BpKnowledgeTests\Infrastructure\Seeder;
use BpKnowledgeTests\Plugin;
use PersonalAccount\Core\Container;

final class PluginBootstrap
{
    public function __construct(private \wpdb $wpdb)
    {
    }

    public function compose(Container $servicesContainer): Plugin
    {
        return new Plugin(new CatalogServices($servicesContainer));
    }

    public function run(): void
    {
        global $servicesContainer;
        if (!isset($servicesContainer) || !$servicesContainer instanceof Container) {
            throw new \RuntimeException(
                'bp_knowledge_tests requires wordpress_framework (global $servicesContainer).'
            );
        }

        $this->compose($servicesContainer)->boot();
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
