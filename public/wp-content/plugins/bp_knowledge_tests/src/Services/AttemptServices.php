<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use BpKnowledgeTests\Infrastructure\Repository\AttemptQuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\FinishedExplanationRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Services\Attempt\MaterialsService;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class AttemptServices
{
    public readonly MaterialsService $materials;

    public function __construct(Container $servicesContainer)
    {
        $db = $servicesContainer->get('DBWorker');
        $dbUtilities = $servicesContainer->get('DBUtilities');
        $mapper = new RecordMapper();
        $clock = new DatabaseClock();

        $attemptRepository = new AttemptRepository($db, $mapper);
        $attemptQuestionRepository = new AttemptQuestionRepository($db, $mapper);
        $questionRepository = new QuestionRepository($db, $dbUtilities, $clock, $mapper);
        $explanationRepository = new FinishedExplanationRepository($db, $dbUtilities, $mapper);

        $this->materials = new MaterialsService(
            $attemptRepository,
            $attemptQuestionRepository,
            $questionRepository,
            $explanationRepository,
        );
    }
}
