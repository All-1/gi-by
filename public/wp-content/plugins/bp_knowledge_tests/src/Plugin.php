<?php

declare(strict_types=1);

namespace BpKnowledgeTests;

use BpKnowledgeTests\Services\AttemptServices;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;
use BpKnowledgeTests\Infrastructure\Admin\AdminAppMount;
use BpKnowledgeTests\Infrastructure\Admin\AdminCatalogMenu;
use BpKnowledgeTests\Infrastructure\Admin\DevToolsMenu;
use BpKnowledgeTests\Infrastructure\Http\AdminRestApi;

final class Plugin
{
    public const VERSION = '0.1.0';

    public function __construct(
        private CatalogServices $catalog,
        private DomainServices $domain,
        private AttemptServices $attempts,
    ) {
    }

    public function boot(): void
    {
        $adminRestApi = new AdminRestApi($this);
        $adminAppMount = new AdminAppMount($this);
        
        $adminRestApi->register();
        $adminAppMount->register();
        if (is_admin()) {
            (new AdminCatalogMenu($this))->register();
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

    public function attempts(): AttemptServices
    {
        return $this->attempts;
    }

    public function version(): string
    {
        return self::VERSION;
    }
}
