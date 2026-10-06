<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Domain\AttemptResultClassifier;
use BpKnowledgeTests\Domain\QuestionSelectionGrader;

final class DomainServices
{
    public readonly ScoringService $scoring;
    public readonly TestConfigReader $config;
    public readonly AttemptResultClassifier $classifier;
    public readonly QuestionSelectionGrader $selection;
    public function __construct(private CatalogServices $catalog)
    {
        $this->scoring = new ScoringService();
        $this->config = new TestConfigReader($catalog->config);
        $this->classifier = new AttemptResultClassifier();
        $this->selection = new QuestionSelectionGrader();
    }
}
