<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Infrastructure\ClockInterface;
use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use BpKnowledgeTests\Infrastructure\Repository\AchievementRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptQuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\FinishedExplanationRepository;
use BpKnowledgeTests\Infrastructure\Repository\NotificationRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\TestRepository;
use BpKnowledgeTests\Infrastructure\Repository\UserAchievementRepository;
use BpKnowledgeTests\Services\Attempt\AttemptAchievementService;
use BpKnowledgeTests\Services\Attempt\AttemptValidityPolicy;
use BpKnowledgeTests\Services\Attempt\CompleteAttempt;
use BpKnowledgeTests\Services\Attempt\CriticalUpdateTest;
use BpKnowledgeTests\Services\Attempt\MaterialsService;
use BpKnowledgeTests\Services\Attempt\NotificationRetakePolicy;
use BpKnowledgeTests\Services\Attempt\NotificationUiQuery;
use BpKnowledgeTests\Services\Attempt\RetakeEligibilityPolicy;
use BpKnowledgeTests\Services\Attempt\RetakeEligibilityQuery;
use BpKnowledgeTests\Services\Attempt\UserTestStandingQuery;
use BpKnowledgeTests\Services\Attempt\ValidityMaintenance;
use PersonalAccount\Core\Container;

final class AttemptServices
{
    public readonly MaterialsService $materials;
    public readonly AttemptAchievementService $achievements;
    public readonly CompleteAttempt $complete;
    public readonly RetakeEligibilityQuery $retake;
    public readonly ValidityMaintenance $validity;
    public readonly CriticalUpdateTest $criticalUpdate;
    public readonly NotificationUiQuery $notificationUi;
    public readonly UserTestStandingQuery $standing;

    public function __construct(
        Container $servicesContainer,
        CatalogServices $catalog,
        DomainServices $domain,
    ) {
        $shared = $this->sharedInfrastructure($servicesContainer);
        $stores = $this->repositories($shared);
        $this->assignUseCases($stores, $catalog, $domain, $shared['clock']);
    }

    private function sharedInfrastructure(Container $servicesContainer): array
    {
        return [
            'db' => $servicesContainer->get('DBWorker'),
            'dbUtilities' => $servicesContainer->get('DBUtilities'),
            'mapper' => new RecordMapper(),
            'clock' => new DatabaseClock(),
        ];
    }

    private function repositories(array $shared): array
    {
        return [
            'attempts' => new AttemptRepository($shared['db'], $shared['dbUtilities'], $shared['mapper']),
            'attemptQuestions' => new AttemptQuestionRepository($shared['db'], $shared['dbUtilities'], $shared['mapper']),
            'questions' => new QuestionRepository($shared['db'], $shared['dbUtilities'], $shared['clock'], $shared['mapper']),
            'explanations' => new FinishedExplanationRepository($shared['db'], $shared['dbUtilities'], $shared['mapper']),
            'achievementCatalog' => new AchievementRepository($shared['db']),
            'userAchievements' => new UserAchievementRepository($shared['db'], $shared['dbUtilities']),
            'tests' => new TestRepository($shared['db'], $shared['dbUtilities'], $shared['clock'], $shared['mapper']),
            'notifications' => new NotificationRepository($shared['db'], $shared['dbUtilities'], $shared['mapper']),
        ];
    }

    private function assignUseCases(
        array $stores,
        CatalogServices $catalog,
        DomainServices $domain,
        ClockInterface $clock,
    ): void {
        $this->materials = new MaterialsService(
            $stores['attempts'],
            $stores['attemptQuestions'],
            $stores['questions'],
            $stores['explanations'],
        );
        $this->achievements = new AttemptAchievementService(
            $domain,
            $stores['achievementCatalog'],
            $stores['userAchievements'],
            $stores['attempts'],
            $catalog,
        );
        $this->complete = new CompleteAttempt(
            $catalog,
            $domain,
            $stores['attempts'],
            $stores['attemptQuestions'],
            $this->materials,
            $this->achievements,
            $stores['notifications'],
            $clock,
        );
        $this->retake = new RetakeEligibilityQuery(
            $stores['attempts'],
            $this->materials,
            $stores['notifications'],
            new NotificationRetakePolicy(),
            $domain,
            new RetakeEligibilityPolicy($domain->classifier),
            $clock,
        );
        $this->validity = new ValidityMaintenance(
            $stores['attempts'],
            $stores['tests'],
            $stores['userAchievements'],
            new AttemptValidityPolicy(),
            $clock,
        );
        $this->criticalUpdate = new CriticalUpdateTest(
            $stores['tests'],
            $stores['attempts'],
            $stores['userAchievements'],
        );
        $this->notificationUi = new NotificationUiQuery(
            $catalog,
            $stores['attempts'],
            $stores['notifications'],
            $domain,
            $this->materials,
        );
        $this->standing = new UserTestStandingQuery(
            $stores['attempts'],
            $stores['userAchievements'],
            $stores['achievementCatalog'],
            $domain->config,
        );
    }
}
