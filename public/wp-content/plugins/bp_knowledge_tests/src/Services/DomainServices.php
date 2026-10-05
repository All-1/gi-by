<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Domain\AttemptResultClassifier;

final class DomainServices
{
    public readonly ScoringService $scoring;
    public readonly TestConfigReader $config;
    public readonly AttemptResultClassifier $classifier;

    public function __construct(CatalogServices $catalog)
    {
        $this->scoring = new ScoringService();
        $this->config = new TestConfigReader($catalog->config);
        $this->classifier = new AttemptResultClassifier();
    }
}
