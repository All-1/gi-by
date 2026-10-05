<?php

declare(strict_types=1);

namespace BpKnowledgeTests;

use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;
use BpKnowledgeTests\Infrastructure\Admin\DevToolsMenu;

final class Plugin
{
    public const VERSION = '0.1.0';

    public function __construct(
        private CatalogServices $catalog,
        private DomainServices $domain,
    ) {
    }

    public function boot(): void
    {
        if (is_admin()) {
            (new DevToolsMenu($this))->register();
        }
    }

    public function catalog(): CatalogServices
    {
        return $this->catalog;
    }

    public function domain(): DomainServices
    {
        return $this->domain;
    }

    public function version(): string
    {
        return self::VERSION;
    }
}
