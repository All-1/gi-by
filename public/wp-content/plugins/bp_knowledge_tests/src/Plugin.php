<?php

declare(strict_types=1);

namespace BpKnowledgeTests;

use BpKnowledgeTests\Services\AttemptServices;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;
use BpKnowledgeTests\Services\ReportingServices;
use BpKnowledgeTests\Infrastructure\Admin\AdminAppMount;
use BpKnowledgeTests\Infrastructure\Admin\AdminCatalogMenu;
use BpKnowledgeTests\Infrastructure\Http\AdminRestApi;
use BpKnowledgeTests\Infrastructure\Security\DealerTestsCapability;

final class Plugin
{
    public const VERSION = '0.1.0';

    public function __construct(
        private CatalogServices $catalog,
        private DomainServices $domain,
        private AttemptServices $attempts,
        private ReportingServices $reporting,
        private DealerTestsCapability $dealerTestsCapability,
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
        }
    }

    public function dealerTestsCapability(): DealerTestsCapability
    {
        return $this->dealerTestsCapability;
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

    public function reporting(): ReportingServices
    {
        return $this->reporting;
    }

    public function version(): string
    {
        return self::VERSION;
    }
}
