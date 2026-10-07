<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use BpKnowledgeTests\Infrastructure\Repository\AttemptQuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\FinishedExplanationRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Services\Attempt\CompleteAttempt;
use BpKnowledgeTests\Services\Attempt\MaterialsService;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class AttemptServices
{
    public readonly MaterialsService $materials;
    public readonly CompleteAttempt $complete;

    public function __construct(
        Container $servicesContainer,
        CatalogServices $catalog,
        DomainServices $domain,
    ) {
        $db = $servicesContainer->get('DBWorker');
        $dbUtilities = $servicesContainer->get('DBUtilities');
        $mapper = new RecordMapper();
        $clock = new DatabaseClock();

        $attemptRepository = new AttemptRepository($db, $dbUtilities, $mapper);
        $attemptQuestionRepository = new AttemptQuestionRepository($db, $dbUtilities, $mapper);
        $questionRepository = new QuestionRepository($db, $dbUtilities, $clock, $mapper);
        $explanationRepository = new FinishedExplanationRepository($db, $dbUtilities, $mapper);

        $this->materials = new MaterialsService(
            $attemptRepository,
            $attemptQuestionRepository,
            $questionRepository,
            $explanationRepository,
        );

        $this->complete = new CompleteAttempt(
            $catalog,
            $domain,
            $attemptRepository,
            $attemptQuestionRepository,
            $this->materials,
            $clock,
        );
    }
}
