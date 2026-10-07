<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Bootstrap;

use BpKnowledgeTests\Services\AttemptServices;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;
use BpKnowledgeTests\Infrastructure\SchemaDefiner;
use BpKnowledgeTests\Infrastructure\SchemaVersionRepository;
use BpKnowledgeTests\Infrastructure\Seeder;
use BpKnowledgeTests\Plugin;
use PersonalAccount\Core\Container;

final class PluginBootstrap
{
    public function __construct(
        private \wpdb $wpdb, 
        private Container $servicesContainer
    ) {
    }

    public function compose(): Plugin
    {
        $catalog = new CatalogServices($this->servicesContainer);
        $domain = new DomainServices($catalog);
        $attempts = new AttemptServices($this->servicesContainer, $catalog, $domain);

        return new Plugin($catalog, $domain, $attempts);
    }

    public function run(): void
    {
        $this->compose()->boot();
    }

    public function activate(): void
    {
        $schemaVersion = new SchemaVersionRepository();
        $schemaDefiner = new SchemaDefiner($this->wpdb, $this->servicesContainer);
        $seeder = new Seeder($this->wpdb);

        $installed = $schemaVersion->current();

        if ($installed < 1) {
            $schemaDefiner->createTables();
            $seeder->seedDefaults();
        }

        if ($installed < 2) {
            $schemaDefiner->installForeignKeys();
        }

        if ($installed < 3) {
            $seeder->seedMedalTiersIfMissing();
            $seeder->seedRankDisplayNamesIfMissing();
        }

        if ($installed < 4) {
            $schemaDefiner->upgradeAnswersIsCorrectColumn();
        }

        $schemaVersion->markUpToDate();
    }

}
