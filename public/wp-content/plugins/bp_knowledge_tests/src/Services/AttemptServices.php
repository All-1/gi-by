<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use BpKnowledgeTests\Infrastructure\Repository\AchievementRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptQuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\FinishedExplanationRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\UserAchievementRepository;
use BpKnowledgeTests\Services\Attempt\AttemptAchievementService;
use BpKnowledgeTests\Services\Attempt\CompleteAttempt;
use BpKnowledgeTests\Services\Attempt\MaterialsService;
use BpKnowledgeTests\Services\Attempt\RetakeEligibilityPolicy;
use BpKnowledgeTests\Services\Attempt\RetakeEligibilityQuery;
use PersonalAccount\Core\Container;

final class AttemptServices
{
    public readonly MaterialsService $materials;
    public readonly AttemptAchievementService $achievements;
    public readonly CompleteAttempt $complete;
    public readonly RetakeEligibilityQuery $retake;

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
        $achievementRepository = new AchievementRepository($db);
        $userAchievementRepository = new UserAchievementRepository($db, $dbUtilities);

        $this->materials = new MaterialsService(
            $attemptRepository,
            $attemptQuestionRepository,
            $questionRepository,
            $explanationRepository,
        );

        $this->achievements = new AttemptAchievementService(
            $domain,
            $achievementRepository,
            $userAchievementRepository,
            $attemptRepository,
        );

        $this->complete = new CompleteAttempt(
            $catalog,
            $domain,
            $attemptRepository,
            $attemptQuestionRepository,
            $this->materials,
            $this->achievements,
            $clock,
        );

        $this->retake = new RetakeEligibilityQuery(
            $attemptRepository,
            $this->materials,
            $domain,
            new RetakeEligibilityPolicy($domain->classifier),
            $clock,
        );
    }
}
