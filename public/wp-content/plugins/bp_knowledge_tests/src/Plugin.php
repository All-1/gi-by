<?php

declare(strict_types=1);

namespace BpKnowledgeTests;

use BpKnowledgeTests\Application\CatalogServices;

final class Plugin
{
    public const VERSION = '0.1.0';

    public function __construct(private CatalogServices $catalog)
    {
    }

    public function boot(): void
    {
        // Admin UI and WS hooks register here in later phases.
    }

    public function catalog(): CatalogServices
    {
        return $this->catalog;
    }

    public function version(): string
    {
        return self::VERSION;
    }
}
