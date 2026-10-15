<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Bootstrap;

use BpKnowledgeTests\Services\AttemptServices;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;
use BpKnowledgeTests\Infrastructure\SchemaDefiner;
use BpKnowledgeTests\Infrastructure\SchemaVersionRepository;
use BpKnowledgeTests\Infrastructure\Security\CatalogCapability;
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
        add_action('init', [CatalogCapability::class, 'registerCaps']);
        $this->installSchemaIfNeeded();
        $this->compose()->boot();
    }

    public function activate(): void
    {
        CatalogCapability::registerCaps();
        $this->installSchemaIfNeeded();
    }

    private function installSchemaIfNeeded(): void
    {
        $schemaVersion = new SchemaVersionRepository();
        $schemaDefiner = new SchemaDefiner($this->wpdb, $this->servicesContainer);
        $seeder = new Seeder($this->wpdb);

        $installed = $schemaVersion->current();
        if (!$this->coreTableExists()) {
            $installed = 0;
        }

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

        $schemaVersion->markUpToDate();
    }

    private function coreTableExists(): bool
    {
        $table = 'gi_new_tests';
        $found = $this->wpdb->get_var(
            $this->wpdb->prepare('SHOW TABLES LIKE %s', $this->wpdb->esc_like($table)),
        );

        return is_string($found) && $found === $table;
    }

}
