<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Tests\Support;

use BpKnowledgeTests\Plugin;
use BpKnowledgeTests\Services\AttemptServices;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\SimpleUtilities;

final class CompleteAttemptTestFixture
{
    private MemoryDBWorker $db;

    private Container $container;

    public function __construct()
    {
        $this->container = new Container();
        $this->container->set('SimpleUtilities', new SimpleUtilities($this->container));
        $dbUtilities = new DBUtilities($this->container);
        $this->container->set('DBUtilities', $dbUtilities);
        $this->db = new MemoryDBWorker($dbUtilities);
        $this->container->set('DBWorker', $this->db);
        $this->seedConfig();
        $this->seedMedals();
    }

    public function createPlugin(): Plugin
    {
        $catalog = new CatalogServices($this->container);
        $domain = new DomainServices($catalog);
        $attempts = new AttemptServices($this->container, $catalog, $domain);

        return new Plugin($catalog, $domain, $attempts);
    }

    public function memoryDb(): MemoryDBWorker
    {
        return $this->db;
    }

    /**
     * @return array{testId: int, questionId: int}
     */
    public function seedCatalog(Plugin $plugin): array
    {
        $testId = $plugin->catalog()->tests->create('Phase 2 integration', null);
        $questionId = $plugin->catalog()->questions->create(
            $testId,
            'Sample question?',
            'Read chapter 1',
            'https://example.test/doc',
        );
        for ($i = 0; $i < 10; ++$i) {
            $plugin->catalog()->answers->create($questionId, 'Option ' . $i, true);
        }

        return ['testId' => $testId, 'questionId' => $questionId];
    }

    private function seedConfig(): void
    {
        $defaults = [
            'bronze_threshold' => '70',
            'silver_threshold' => '80',
            'gold_threshold' => '90',
            'lock_threshold' => '95',
            'retake_delay_days' => '1',
            'new_test_notification_dismiss_limit' => '10',
            'failed_test_notification_dismiss_limit' => '10',
            'new_test_notification_mode' => 'bottom',
            'failed_test_notification_mode' => 'bottom',
        ];
        foreach ($defaults as $name => $value) {
            $this->db->seedRow('KnowledgeTestConfig', ['name' => $name, 'value' => $value]);
        }
    }

    private function seedMedals(): void
    {
        $tiers = [
            ['name' => 'Bronze', 'value' => 'bronze'],
            ['name' => 'Silver', 'value' => 'silver'],
            ['name' => 'Gold', 'value' => 'gold'],
            ['name' => 'Lock', 'value' => 'lock'],
        ];
        foreach ($tiers as $tier) {
            $this->db->seedRow('KnowledgeTestAchievements', $tier);
        }
    }
}
