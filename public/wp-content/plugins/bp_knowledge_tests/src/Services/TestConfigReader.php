<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Domain\TestConfig;
use BpKnowledgeTests\Services\Catalog\ConfigCatalog;

final class TestConfigReader
{
    public function __construct(private ConfigCatalog $config)
    {
    }

    public function read(): TestConfig
    {
        return new TestConfig(
            $this->threshold('bronze_threshold', 70.0),
            $this->threshold('silver_threshold', 80.0),
            $this->threshold('gold_threshold', 90.0),
            $this->threshold('lock_threshold', 95.0),
        );
    }

    private function threshold(string $name, float $default): float
    {
        $raw = $this->config->get($name);
        if ($raw === null || $raw === '') {
            return $default;
        }

        return (float) $raw;
    }
}
