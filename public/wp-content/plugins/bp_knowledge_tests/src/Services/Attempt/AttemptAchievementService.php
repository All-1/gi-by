<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Infrastructure\Repository\AchievementRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\UserAchievementRepository;
use BpKnowledgeTests\Services\DomainServices;

final class AttemptAchievementService
{
    public function __construct(
        private DomainServices $domain,
        private AchievementRepository $achievements,
        private UserAchievementRepository $userAchievements,
        private AttemptRepository $attempts,
    ) {
    }

    public function applyAfterComplete(int $userId, int $testId, float $score): void
    {
        $classification = $this->domain->classifier->classify($score, $this->domain->config->read());
        $tierValue = $this->domain->classifier->achievementTierValue($classification);
        if ($tierValue === null) {
            $this->userAchievements->deleteForUserAndTest($userId, $testId);

            return;
        }

        $medalId = $this->achievements->findIdByValue($tierValue);
        if ($medalId === null) {
            throw new \RuntimeException('Achievement tier not found: ' . $tierValue);
        }

        $this->userAchievements->upsertForUserAndTest($userId, $testId, $medalId);
    }

    public function recalculateForUserAndTest(int $userId, int $testId): void
    {
        $attempt = $this->attempts->findLatestByUserAndTest($userId, $testId);
        if ($attempt === null || $attempt->status === 'outdated') {
            return;
        }

        $this->applyAfterComplete($userId, $testId, $attempt->score);
    }
}
