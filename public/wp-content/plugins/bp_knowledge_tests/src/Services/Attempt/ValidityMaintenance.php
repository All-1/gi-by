<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Infrastructure\ClockInterface;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\TestRepository;
use BpKnowledgeTests\Infrastructure\Repository\UserAchievementRepository;
use BpKnowledgeTests\Services\CatalogServices;

final class ValidityMaintenance
{
    public function __construct(
        private AttemptRepository $attempts,
        private TestRepository $tests,
        private UserAchievementRepository $userAchievements,
        private AttemptValidityPolicy $policy,
        private ClockInterface $clock,
    ) {
    }

    public function refreshForUser(int $userId): void
    {
        foreach ($this->attempts->listDistinctTestIdsForUser($userId) as $testId) {
            $this->refreshForUserAndTest($userId, $testId);
        }
    }

    public function refreshForUserAndTest(int $userId, int $testId): bool
    {
        $attempt = $this->attempts->findLatestByUserAndTest($userId, $testId);
        if ($attempt === null || $attempt->status !== 'passed') {
            return false;
        }

        if ($this->tests->findById($testId) === null) {
            return false;
        }

        if (!$this->policy->passedResultExpired($attempt, $this->clock->now())) {
            return false;
        }

        $this->attempts->updateStatus($attempt->id, 'outdated');
        $this->userAchievements->deleteForUserAndTest($userId, $testId);

        return true;
    }
}
