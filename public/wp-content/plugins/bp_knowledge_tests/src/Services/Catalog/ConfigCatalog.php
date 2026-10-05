<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Catalog;

use BpKnowledgeTests\Infrastructure\Repository\ConfigRepository;

final class ConfigCatalog
{
    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->config->getAllAsMap();
    }

    public function get(string $name): ?string
    {
        return $this->config->getValue($name);
    }

    public function set(string $name, string $value): void
    {
        $this->config->setValue($name, $value);
    }
}
